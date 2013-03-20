<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Lib_Base
 * @subpackage Controller
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id: view.php 13650 2012-04-11 08:56:41Z asanieyan $
 * @link       http://www.anahitapolis.com
 */

/**
 * View Controller. This conroller doesn't require domain entities
 *
 * @category   Anahita
 * @package    Lib_Base
 * @subpackage Controller
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class LibBaseControllerResource extends LibBaseControllerAbstract
{
    /**
     * View object or identifier (APP::com.COMPONENT.view.NAME.FORMAT)
     *
     * @var string|object
     */
    protected $_view;
                
    /**
     * Redirect options
     *
     * @var KConfig
     */
    protected $_redirect;
    
    /**
     * Constructor.
     *
     * @param   object  An optional KConfig object with configuration options.
     */
    public function __construct( KConfig $config)
    {            
        parent::__construct($config);
        
        $this->_redirect = new KConfig();
        
        //set the view
        $this->_view     = $config->view;
                
        //register display as get so $this->display() return
        //$this->get()
        $this->registerActionAlias('display', 'get');
        
        // Mixin the toolbar
        if($config->dispatch_events) {
            $this->mixin(new KMixinToolbar($config->append(array('mixer' => $this))));
        }
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
    	$permission       = clone $this->getIdentifier();
    	$permission->path = array($permission->path[0], 'permission');
    	register_default(array('identifier'=>$permission, 'prefix'=>$this));
    	    	
        $config->append(array(
            'behaviors' => array($permission),
            'request'   => array('format' => 'html'),
        ))->append(array(
            'view'      => $config->request->get ? $config->request->get : ($config->request->view ? $config->request->view : $this->getIdentifier()->name)
        ));
        
        parent::_initialize($config);
    }
    
    /**
     * Get action
     * 
     * @param KCommandContext $context Context parameter
     * 
     * @return string
     */    
    protected function _actionGet(KCommandContext $context)
    {
        $action = null;
        
        if ( $this->_request->get ) {
            $action = strtolower('get'.$this->_request->get);
        }        
        else {
            $action = KInflector::isPlural($this->view) ? 'browse' : 'read';
        }
       
        $result = null;
           
        if ( in_array($action, $this->getActions()) ) {
            $result = $this->execute($action, $context);
        }
        
        //if the result of the previous actions is not
        //string and not false then display the view
        if ( !is_string($result) && $result !== false ) {
            $result = $this->getView()->display();
        }
 
        return (string) $result;
    }   
    
    /**
     * Get the view object attached to the controller
     * 
     * @return LibBaseViewAbstract
     */
    public function getView()
    {
        if(!$this->_view instanceof LibBaseViewAbstract)
        {
            //Make sure we have a view identifier
            if(!($this->_view instanceof KServiceIdentifier)) {
                $this->setView($this->_view);
            }
            
            //Create the view
            $config = array(                
                'media_url' => KRequest::root().'/media',
                'base_url'  => KRequest::url()->getUrl(KHttpUrl::BASE),
                'state'     => $this->getState()                
            );

            $this->_view = $this->getService($this->_view, $config);
            
            //Set the layout
            if(isset($this->_state->layout)) {
                $this->_view->setLayout($this->_state->layout);
            }
        }
        
        return $this->_view;
    }
    
    /**
     * Method to set a view object attached to the controller
     *
     * @param mixed $view An object that implements KObjectIdentifiable, an object that 
     * implements KIndentifierInterface or valid identifier string
     *                  
     * @throws KDatabaseRowsetException If the identifier is not a view identifier
     * 
     * @return KControllerAbstract
     */
    public function setView($view)
    {
        if(!($view instanceof ComBaseViewAbstract))
        {
            if(is_string($view) && strpos($view, '.') === false ) 
            {
                $identifier          = clone $this->getIdentifier();
                $identifier->path    = array('view', $view);
                $identifier->name    = pick($this->format, 'html');
            }
            else $identifier = $this->getIdentifier($view);
            
            register_default(array('identifier'=>$identifier, 'prefix'=>$this, 'name'=>array('View'.ucfirst($identifier->name),'ViewDefault')));
            
            $view = $identifier;
        }
        
        $this->_view = $view;
                
        return $this;
    }
            
    /**
     * Set a URL for browser redirection.
     *
     * @param array $redriect Redirect options
     *              ['url'=>'string','message'=>'string','type'=>'string']
     * 
     * @return void
     */
    public function setRedirect($redirect)
    {        
    	if ( is_string($redirect) ) {
    		$redirect = array('url'=>$redirect);
    	}
    	
        $redirect = new KConfig($redirect);
        
        $redirect->append(array(
            'message'  => '',
            'type'     => null
        ));

        $route = $redirect->url;
        
        if ( !is_array($route) ) {
        	$parts = array();
        	parse_str(trim($route), $parts);
        	$route = $parts;
        }        
        
        $parts = $route;    	
        $route = array();
	    
	    //Check to see if there is component information in the route if not add it
	    if(!isset($parts['option'])) {
	        $route['option'] = 'com_'.$this->getIdentifier()->package;
	    }

	    //Add the view information to the route if it's not set
	    if(!isset($parts['view'])) {
	    	$route['view'] = $this->_request->view ? $this->_request->view : $this->getIdentifier()->name;
	    }	    

	    //Add the format information to the route only if it's not 'html'
	    if(!isset($parts['format']) && $this->_request->format ) {
	    	$route['format'] = $this->_request->format;
	    }	    
	    
	    if ( $route['format'] == 'html' ) {
	    	unset($route['format']);
	    }

	    $parts = array_merge($route, $parts);
	    
        $redirect->url = $this->getService('application')
        					->getRouter()
        					->build('index.php?'.http_build_query($parts));
        
        $this->_redirect = $redirect;
        
        return $this;
    }  
            
    /**
     * Returns an array with the redirect url, the message and the message type
     *
     * @return KConfig Named array containing url, message and messageType, or null 
     * if no redirect was set
     */
    public function getRedirect()
    {
        return $this->_redirect;
    }

    /**
     * Renders the controller's view by passing the $data to the view
     *
     * @param KCommandContext $context Context 
     *
     * @return string
     */
    public function render(KCommandContext $context)
    {        
        return (string) $this->getView()->display();
    }
    
    /**
     * Set the state property of the controller
     * 
     * @param string $key   The property name
     * @param string $value The property value
     * 
     * @return void
     */   
    public function __set($key, $value)
    {
        if ( $key == 'view' ) {
            $this->_view = $value;    
        }
        
        return parent::__set($key, $value);        
    }
    
    /**
     * Executes a GET request and display the view
     * 
     * @return string
     */
    public function __toString()
    {
        try {
           return $this->display();
        } catch(Exception $e) {
            trigger_error('Exception in '.get_class($this).' : '.$e->getMessage(), E_USER_WARNING);
            throw $e;
        }
    }
    
    /**
     * Get a toolbar by identifier
     *
     * @return KControllerToolbarAbstract
     */
    public function getToolbar($toolbar, $config = array())
    {
        if ( is_string($toolbar) )
        {
            if ( strpos($toolbar,'.') === false )
            {
                $identifier       = clone $this->getIdentifier();
                $identifier->path = array('controller','toolbar');
                $identifier->name = $toolbar;
                register_default(array('identifier'=>$identifier, 'prefix'=>$this));
                $toolbar = $identifier;
            }
        }       
        
        return parent::getToolbar($toolbar, $config);      
    }     
}