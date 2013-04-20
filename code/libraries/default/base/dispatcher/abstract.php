<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Lib_Base
 * @subpackage Dispatcher
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id: view.php 13650 2012-04-11 08:56:41Z asanieyan $
 * @link       http://www.anahitapolis.com
 */

/**
 * Abstract Base Dispatcher
 *
 * @category   Anahita
 * @package    Lib_Base
 * @subpackage Dispatcher
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
abstract class LibBaseDispatcherAbstract extends LibBaseControllerAbstract
{
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
        
		$this->_controller = $config->controller;

	    $this->registerCallback('after.dispatch', array($this, 'render'));
    }
    
    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param 	object 	An optional KConfig object with configuration options.
     * @return 	void
     */
    protected function _initialize(KConfig $config)
    {
        $config->append(array(
                'controller' => $this->getIdentifier()->package,
                'request'	 => KRequest::get('get', 'string'),
        ))->append(array (
                'request' 	 => array('format' => KRequest::format() ? KRequest::format() : 'html')
        ));
    
        parent::_initialize($config);        
    
        //prevent rendering layouts starting with _
        if ( strpos($config->request->layout, '_') === 0 ) {
            unset($config->request->layout);
        }
    }
        
    /**
     * @see KDispatcherAbstract::_actionDispatch()
     */
    protected function _actionDispatch(KCommandContext $context)
    {        
        //Load the component aliases
        $component   = $this->getIdentifier()->package;
        $application = $this->getIdentifier()->application;
        $this->getService('koowa:loader')->loadIdentifier('com://'.$application.'/'.$component.'.aliases');
                
        //if a command line the either do get or 
        //post depending if there are any action
        if ( PHP_SAPI == 'cli' ) {
            $method = KRequest::get('post.action', 'cmd', 'get');
        } 
        
        else {
            $method = strtolower(KRequest::method());
        }
        try {
            $result = $this->execute($method, $context);
        } catch(KException $exception) 
        {
            $this->getController()->getResponse()
                ->setContentType($this->getController()->getView()->mimetype);
            
            $this->getController()->getResponse()
                ->setStatus($exception->getCode(), $exception->getMessage());
            
            $this->getController()->getResponse()->sendHeaders();
            
            //if redirect then end the request 
            if ( $this->getController()->getResponse()->isRedirect() ) {
                exit(0);
            }
            
            throw $exception;
        }
        
    	return $result;
    }
    
    /**
     * Get action
     * 
     * @param KCommandContext $context
     * 
     * @return void
     */
    protected function _actionGet(KCommandContext $context)
    {
        $result = $this->getController()->execute('get', $context);
        return $result;
    }
    
    /**
     * Get action
     *
     * @param KCommandContext $context
     *
     * @return void
     */
    protected function _actionPost(KCommandContext $context)
    {
        $context->append(array(
           'data' => KRequest::get('post', 'raw', array())     
        ));
                
        //backward compatiblity
        if ( $context->data['action'] ) {
            $context->data['_action'] = $context->data['action'];
        }
        
        $action        = 'post';
        if ( $context->data['_action'] ) 
        {
            $action = $context->data['_action'];
            if(in_array($action, array('browse', 'read', 'display'))) {
                throw new KControllerException('Action: '.$action.' not allowed');
            }
        }
                
        if ( KRequest::format() != 'html' || KRequest::type () == 'AJAX' ) {
            $this->registerCallback('after.post', array($this, 'forward'));
        } else {
            $this->getController()
                ->getResponse()
                ->setRedirect(KRequest::get('server.HTTP_REFERER', 'url'));            
        }
        
        return $this->getController()->execute($action, $context);
    }    
    
    /**
     * Get action
     *
     * @param KCommandContext $context
     *
     * @return void
     */
    protected function _actionDelete(KCommandContext $context)
    {
        $redirect = KRequest::get('server.HTTP_REFERER', 'url');
        
        $this->getController()
            ->getResponse()
            ->setRedirect($redirect);
                
        $result = $this->getController()->execute('delete', $context);
        
        return $result;        
    }

    /**
     * Method to get a controller object
     *
     * @return	KControllerAbstract
     */
    public function getController()
    {
        if(!($this->_controller instanceof KControllerAbstract))
        {
            //Make sure we have a controller identifier
            if(!($this->_controller instanceof KServiceIdentifier)) {
                $this->setController($this->_controller);
            }
    
            $config = array(
                    'response'     => $this->getResponse(),
                    'request' 	   => $this->_request,
                    'dispatched'   => true
            );
    
            $this->_controller = $this->getService($this->_controller, $config);
        }
    
        return $this->_controller;
    }
    
    /**
     * Method to set a controller object attached to the dispatcher
     *
     * @param	mixed	An object that implements KObjectServiceable, KServiceIdentifier object
     * 					or valid identifier string
     * @throws	KDispatcherException	If the identifier is not a controller identifier
     * @return	KDispatcherAbstract
     */
    public function setController($controller)
    {
        if(!($controller instanceof KControllerAbstract))
        {
            if(is_string($controller) && strpos($controller, '.') === false )
            {
                // Controller names are always singular
                if(KInflector::isPlural($controller)) {
                    $controller = KInflector::singularize($controller);
                }
    
                $identifier			= clone $this->getIdentifier();
                $identifier->path	= array('controller');
                $identifier->name	= $controller;
            }
            else $identifier = $this->getIdentifier($controller);
    
            if($identifier->path[0] != 'controller') {
                throw new KDispatcherException('Identifier: '.$identifier.' is not a controller identifier');
            }
    
            $default = 'Com'.ucfirst($this->getIdentifier()->package).'ControllerDefault';
            register_default(array('identifier'=>$identifier, 'default'=>array($default)));
                        
            $controller = $identifier;
        }
    
        $this->_controller = $controller;
    
        return $this;
    }
	
	/**
	 * Renders a controller view
	 *
	 * @param KCommandContext $context The context parameter
	 * 
	 * @return string
	 */	
	protected function _actionForward(KCommandContext $context)
	{	    	    
	    $response = $this->getController()->getResponse();
	    
	    if ( !$response->getContent() )
	    {
	        if ( in_array($response->getStatusCode(), array(201, 205)) )
	        {
	            //set the view to single
	            //render the item
	            $view   = $this->getController()->getIdentifier()->name;
	            $response->setContent($this->getController()->view($view)->execute('display', $context));
	            if ( $response->getStatusCode() == 205 ) {
	                $response->setStatus(200);
	            }
	        }	        
	    }
	    
	    return $context->result;
	}
	
	/**
	 * Renders a controller view
	 * 
	 * @param KCommandContext $context The context parameter
	 * 
	 * @return string
	 */
	protected function _actionRender(KCommandContext $context)
	{
	    $response = $this->getController()->getResponse();
	    
        $response->setContentType($this->getController()->getView()->mimetype);
	    
	    $content = $response->getContent();
	    
	    $response->sendHeaders();
	    
	    if ( $response->isRedirect() ) {
	        exit(0);    
	    }
	    
	    return $content; 	    
	}
	
}