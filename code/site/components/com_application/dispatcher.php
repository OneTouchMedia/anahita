<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Com_Application
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id: resource.php 11985 2012-01-12 10:53:20Z asanieyan $
 * @link       http://www.anahitapolis.com
 */

/**
 * Application Dispatcher
 *
 * @category   Anahita
 * @package    Com_Application
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class ComApplicationDispatcher extends KControllerAbstract implements KServiceInstantiatable
{
    /**
     * Site Application
     * 
     * @var JSite
     */
    protected $_application;
    
    /**
     * Force creation of a singleton
     *
     * @param KConfigInterface  $config    An optional KConfig object with configuration options
     * @param KServiceInterface $container A KServiceInterface object
     *
     * @return KServiceInstantiatable
     */
    public static function getInstance(KConfigInterface $config, KServiceInterface $container)
    {
        if (!$container->has($config->service_identifier))
        {
            $classname = $config->service_identifier->classname;
            $instance  = new $classname($config);
            $container->set($config->service_identifier, $instance);
            
            //Add the service alias to allow easy access to the singleton
            $container->setAlias('application', $config->service_identifier);
        }
    
        return $container->get($config->service_identifier);
    }  
    
    /** 
     * Constructor.
     *
     * @param KConfig $config An optional KConfig object with configuration options.
     * 
     * @return void
     */ 
    public function __construct(KConfig $config)
    {
        parent::__construct($config);
        
        //loads the application
        $this->registerCallback('before.run', array($this, 'loadApplication'));
        //parse route
        $this->registerCallback('after.run',   array($this, 'route'));
        //authorize after routing
        $this->registerCallback('after.route', array($this, 'authorize'));        
        //dispatch the component
        $this->registerCallback('after.authorize', array($this, 'dispatch'));
        //render the result
        $this->registerCallback('after.dispatch', array($this, 'render'));
    }
        
    /**
    * Initializes the default configuration for the object
    *
    * Called from {@link __construct()} as a first step of object instantiation.
    *
    * @param KConfig $config An optional KConfig object with configuration options.
    *
    * @return void
    */
    protected function _initialize(KConfig $config)
    {
        $config->append(array(
        
        ));   

        parent::_initialize($config);
    }
  
    /**
     * Run the application dispatcher
     * 
     * @param KCommandContext $context Command chain context
     * 
     * @return boolean
     */
    protected function _actionRun(KCommandContext $context)
    {        
        $error_reporting =  $this->_application->getCfg('error_reporting');
        
        //taken from nooku application dispatcher
        if ($error_reporting > 0)
        {
            //Development mode
            if($error_reporting == 1) {
                error_reporting( E_ALL | E_STRICT | ~E_DEPRECATED );
                ini_set( 'display_errors', 1 );

            }

            //Production mode
            if($error_reporting == 2) {
                error_reporting( E_ERROR | E_WARNING | E_PARSE );
                ini_set( 'display_errors', 0 );
            }
        }
        
        define('JDEBUG', $this->_application->getCfg('debug'));
        
        //set the default timezone to UTC
        date_default_timezone_set('UTC');
        
        KRequest::root(str_replace('/'.$this->_application->getName(), '', KRequest::base()));
    }
   
    /**
     * Dispatches the component
     * 
     * @param KCommandContext $context Command chain context
     * 
     * @return boolean
     */        
    protected function _actionDispatch(KCommandContext $context)
    {        
        $component = $this->option;
        
        if ( !empty($component) ) 
        {
            //render the component
            $result = JComponentHelper::renderComponent($component);
            
            //legacy. joomla event
            $this->_application->triggerEvent('onAfterDispatch', $result);
        }
        else {
            throw new KDispatcherException(JText::_('Component Not Found'), KHttpResponse::NOT_FOUND);
        }
        
        return $result;
    }
    
    /**
     * Renders the output
     * 
     * @param KCommandContext $context Command chain context
     * 
     * @return boolean
     */        
    protected function _actionRender(KCommandContext $context)
    {
        $template = $this->_application->getTemplate();
        $page     = $this->getService('tmpl://site/'.$template.'.controller.page');
        $result   = $page->setContent($context->result)                                                
                         ->display();
                      
        JResponse::setBody($result);
        
        $this->_application->triggerEvent('onAfterRender');
        
        print JResponse::toString();
        exit(0);
    }
    
    /**
     * 
     */
    protected $_theme;
    
    /**
     * Returns the template object
     * 
     * @return 
     */
    public function getTheme()
    {
        if ( !isset($this->_theme) ) 
        {
            $template = $this->_application->getTemplate();            
            $this->_theme = $this->getService('tmpl://site/'.$template.'.theme');
        }
        
        return $this->_theme;
    }
    
    /**
     * Parses the route
     * 
     * @param KCommandContext $context Command chain context
     * 
     * @return boolean
     */        
    protected function _actionRoute(KCommandContext $context)
    {
        //route the application
        $this->_application->route();
                
        // trigger the onAfterRoute events
        $this->_application->triggerEvent('onAfterRoute');
                
        //set the request
        $this->setRequest( KRequest::get('get','raw') );
    }    
    
    /**
     * Authorize a request
     * 
     * @param KCommandContext $context Command chain context
     * 
     * @return boolean
     */
    protected function _actionAuthorize(KCommandContext $context)
    {
        //authorize
    }
    
    /**
     * Loads the JApplication object. 
     * 
     * @return void
     */
    public function loadApplication()
    {
        //load the JSite        
        KLoader::loadIdentifier('com://site/application.application');
        jimport('joomla.application.component.helper');                       
        $this->_application = JFactory::getApplication('site');
        global $mainframe;
        $mainframe = $this->_application;
        $this->_application->initialise();
        JPluginHelper::importPlugin('system');
        $this->_application->triggerEvent('onAfterInitialise');        
    }
}