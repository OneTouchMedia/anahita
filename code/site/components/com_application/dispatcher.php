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
        $this->registerCallback('before.run',  array($this, 'load'));                       
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
            'request'=> array('tmpl'=>'default')
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
        
        //before dispatching then authorizer
        $this->registerCallback('before.dispatch', array($this, 'route'));
        
        //render the result
        $this->registerCallback('after.dispatch', array($this, 'render'));
        
        //render after an error
        $this->registerCallback('after.error',  array($this, 'render'));
        
        //initialize the application and load system plugins
        $this->_application->initialise();
                     
        JPluginHelper::importPlugin('system');
        
        $this->_application->triggerEvent('onAfterInitialise');
        
        $this->dispatch();
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
            $result  = JComponentHelper::renderComponent($component);
            
            //legacy. joomla event
            $this->_application->triggerEvent('onAfterDispatch', array($result));
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
    	$base_url = $this->getService('koowa:http.url', array('url'=>JURI::base()));
    	    	
        $config = array(
            'base_url'  => $base_url,
            'template'  => $this->_application->getTemplate(),
            'request'   => $this->_request
        );
        
        $name    = $context->result instanceof KException ? 'error' : 'page';
        
        $view    = $this->getService('com://site/application.controller.'.$name, $config)
                        ->getView();
        
        //set the content type if already not set
        //the content-type is set in the component dispatcher
        //however when there's an exception through a system, no content-type is set
        //to make a general assumption, we always check if content-type is not set
        //then set it according to the page format.
        //@TODO this can be done better. 
        $content_type_sent = false;
        
        foreach(headers_list() as $header) {
           if ( strpos($header, 'Content-Type') === 0 ) {                    
                $content_type_sent = true;
                break;
           }
        }
        
        if ( !$content_type_sent ) {
            header('Content-Type: '.$view->mimetype);
        }
                
        $view->layout($this->tmpl);
        
        $context->result = $view->content($context->result)->display();
        
                              
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
        $url  = clone KRequest::url();
        $this->_application->getRouter()->parse($url);
        JRequest::set($url->query, 'get', false);
                            
        // trigger the onAfterRoute events
        $this->_application->triggerEvent('onAfterRoute');

        //globally set ItemId
        global $Itemid;
        
        $Itemid = KRequest::get('get.Itemid','int', 0);
        
        //set the request        
        $this->setRequest(KRequest::get('get','raw'));
        
        $this->_request->append(array(        
            'tmpl'   => KRequest::type() == 'AJAX' ? 'raw' : 'default',
        ));
    }
    
    /**
     * Loads the application
     * 
     * @return void
     */
    protected function _actionLoad($context)
    {        
        //legacy register error handling
        JError::setErrorHandling( E_ERROR, 'callback', array($this, 'error'));
        
        //register exception handler
        set_exception_handler(array($this, 'error')); 
                
        //load the JSite
        $this->getService('koowa:loader')->loadIdentifier('com://site/application.application');        
                      
        jimport('joomla.application.component.helper');
        
        //no need to create session when using CLI (command line interface)
        
        $this->_application = JFactory::getApplication('site', array('session'=>PHP_SAPI !== 'cli'));        

        global $mainframe;
        
        $mainframe = $this->_application;
         
        $error_reporting =  $this->_application->getCfg('error_reporting');
        
        define('JDEBUG', $this->_application->getCfg('debug'));
        
        //taken from nooku application dispatcher
        if ($error_reporting > 0)
        {
            error_reporting( $error_reporting );
            ini_set('display_errors',1);
            ini_set('display_startup_errors',1);
        }
                
        $this->getService()->set('application', $this->_application);
        
        //set the session handler to none for
        if ( PHP_SAPI == 'cli' ) {
            JFactory::getConfig()->setValue('config.session_handler','none');
            JFactory::getConfig()->setValue('config.cache_handler','file');
        }
                        
        //set the default timezone to UTC
        date_default_timezone_set('UTC');              
        
        KRequest::root(str_replace('/'.$this->_application->getName(), '', KRequest::base()));
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
        if ( $context->data instanceof JException ) {
            $error = new KException($context->data->getMessage(),$context->data->getCode());
        }
        
        //if cli just print the error and exit
        if ( PHP_SAPI == 'cli' ) 
        {
            print "\n";
            print $error."\n";
            print debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            exit(0);
        }
                
        $this->tmpl = 'error';
        
        if ( !headers_sent() ) {
            header(KHttpResponse::getHeader($error->getCode(), KRequest::protocol()));
        }
                               
        return $error;
    }
}