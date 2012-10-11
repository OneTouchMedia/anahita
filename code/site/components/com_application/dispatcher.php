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
        
        //parse route
        $this->registerCallback('after.run',   array($this, 'route'));
        //authorize after routing
        $this->registerCallback('after.route', array($this, 'authorize'));        
        //dispatch the component
        $this->registerCallback('after.authorize', array($this, 'dispatch'));
        //render the result
        $this->registerCallback('after.dispatch', array($this, 'render'));
        
        //legacy register error handling
        JError::setErrorHandling( E_ERROR, 'callback', array($this, 'error'));
        //register exception handler
        set_exception_handler(array($this, 'error'));        
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
        //load the JSite
        KLoader::loadIdentifier('com://site/application.application');
        
        jimport('joomla.application.component.helper');
        
        $this->_application = JFactory::getApplication('site');
        
        global $mainframe;
        
        $mainframe = $this->_application; 
                 
        $error_reporting =  $this->_application->getCfg('error_reporting');
        
        define('JDEBUG', $this->_application->getCfg('debug'));
        
        //taken from nooku application dispatcher
        if ($error_reporting > 0)
        {
            error_reporting( $error_reporting );
            ini_set( 'display_errors', 1 );
        }
                        
        //set the default timezone to UTC
        date_default_timezone_set('UTC');
        
        KRequest::root(str_replace('/'.$this->_application->getName(), '', KRequest::base()));
        
        //initialize the application and load system plugins
        $this->_application->initialise();
        JPluginHelper::importPlugin('system');
        $this->_application->triggerEvent('onAfterInitialise');        
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
            $item    = JMenu::getInstance('site')->getActive();
                         
            switch(true) 
            {
                //don't render if home menu or front page                            
                case isset($item) && $item->alias == 'home' :
                case $component   == 'com_content' && $this->view == 'frontpage' :
                    $result = '';
                    break;
                default :
                    $result = JComponentHelper::renderComponent($component);    
            }
            
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
        $config = array(
            'base_url'  => KRequest::base(),
            'template'  => $this->_application->getTemplate() 
        );
        
        if ( $context->getError() ) {
            $context->result = $this->getService('com://site/application.controller.error', $config)
                 ->getView()->error($context->getError())->display();
                 
            $context->setError(null);            
        }
        else 
        {
            $context->result = $this->getService('com://site/application.controller.page', $config)
                 ->getView()->content($context->result)->display();
        }
                      
        JResponse::setBody($context->result);
        
        $this->_application->triggerEvent('onAfterRender');
        
        print JResponse::toString();
        
        exit(0);
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

        //globally set ItemId
        global $Itemid;
        
        $Itemid = KRequest::get('get.Itemid','int',0);
        
        //JRequest::set($request, 'get');
        
        //set the request
        $this->setRequest(KRequest::get('get','raw'));
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
     * Callback to handle both JError and Exception
     * 
     * @param KCommandContext $context Command chain context
     * caller => KObject, data => mixed
     * 
     * @return void;
     */
    protected function _actionError($context)
    {
        $error = $context->data;
                
        //if JException then conver it to KException
        if ( $context->data instanceof JException ) {
            $error = new KException($context->data->getMessage(),$context->data->getCode());
        }
        
        $context = $this->getCommandContext();
        $context->setError($error);
        $this->execute('render', $context);
        exit(0);
    }
}