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
     * Application
     * 
     * @var JApplication
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
        $this->registerCallback('after.run',   array($this, 'dispatch'));

        //befire authorizing route        
        $this->registerCallback('before.authorize', array($this, 'route'));
        
        //before dispatching then authorizer
        $this->registerCallback('before.dispatch', array($this, 'authorize'));
        
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
        KLoader::loadIdentifier('com://admin/application.application');
        
        jimport('joomla.application.component.helper');
        jimport('joomla.document.renderer');
        require_once(JPATH_LIBRARIES.'/joomla/document/renderer.php');                        
        require_once(JPATH_BASE.'/includes/toolbar.php');
        
        $this->_application = JFactory::getApplication('administrator');
       	
        $this->getService()->set('application', $this->_application);
       	
        global $mainframe;
        
        $mainframe = $this->_application; 
                 
        $error_reporting =  $this->_application->getCfg('error_reporting');
        
        define('JDEBUG', $this->_application->getCfg('debug'));
        
        //taken from nooku application dispatcher
        if ($error_reporting > 0 )
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
        
        if ( !empty($component) && JComponentHelper::isEnabled($component) ) 
        {
            $result = JComponentHelper::renderComponent($component);
            
            //legacy. joomla event
            $this->_application->triggerEvent('onAfterDispatch', array($result));
        }
        else {
            $context->setError(new KDispatcherException(JText::_('Component Not Found'), KHttpResponse::NOT_FOUND));
            return false;
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
        //old school of rendering for the backend for now        
        $component  = $this->option;
        $template   = $this->_application->getTemplate();
        $file       = $this->tmpl;

        if($component == 'com_login') {
            $file = 'login';
        }

        $config = array(
            'template'  => $template,
            'file'      => $file.'.php',
            'directory' => JPATH_THEMES
        );
        
        $document =& JFactory::getDocument();
        $document->addScript( JURI::root(true).'/administrator/includes/joomla.javascript.js');
        $document->setTitle( htmlspecialchars_decode($this->_application->getCfg('sitename' )). ' - ' .JText::_( 'Administration' ));
        $document->setDescription( $this->_application->getCfg('MetaDesc') );
        
        $document->setBuffer($context->result, 'component');
                
        $context->result = $document->render($this->_application->getCfg('caching'), $config);
        
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
       
        //set the request        
        $this->setRequest(KRequest::get('get','raw'));

        $this->_request->option = KRequest::get('request.option','cmd');
        
        //set the tmpl to the default        
        $this->_request->tmpl = KRequest::get('get.tmpl','cmd','index');
    }
    
    /**
     * Only admin can login in the backend
     * 
     * @param KCommandContext $context Command chain context
     * 
     * @return boolean
     */
    protected function _actionAuthorize(KCommandContext $context)
    {
        $component = strtolower($this->option);
        
        $user =& JFactory::getUser();
        
        if (!$user->authorize('login', 'administrator')) {
            $component = 'com_login';
        }

        if( empty($component) ) {
            $component = 'com_cpanel';
        }
        
        KRequest::set('get.option', $component);
        
        $this->option = $component;
    }
        
    /**
     * Callback to handle both JError and Exception
     * 
     * @param KCommandContext $context Command chain context
     * caller => KObject, data => mixed
     * 
     * @return KException
     */
    protected function _actionError($context)
    {
        $error = $context->data;
           
        //if JException then conver it to KException
        if ( $context->data instanceof KException ) {
            $error = new JException($context->data->getMessage(),$context->data->getCode());
        }
        if ( $error instanceof Exception ) {
        	$error = new JException($error->getMessage(),$error->getCode());
        }
        JError::customErrorPage($error);
        exit(0);
    }    
}