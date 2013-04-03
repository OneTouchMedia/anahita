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
abstract class LibBaseDispatcherAbstract extends KDispatcherAbstract
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
        
        $this->unregisterCallback('after.dispatch' , array($this, 'forward'));
        
        $this->registerCallback('after.post' ,       array($this, 'forward'));
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
        parent::_initialize($config);
        
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
        $component   = $this->getController()->getIdentifier()->package;
        $application = $this->getController()->getIdentifier()->application;
        $this->getService('koowa:loader')->loadIdentifier('com://'.$application.'/'.$component.'.aliases');
        
        //Execute the component method
        $method = strtolower(KRequest::method());
        $result = $this->execute($method, $context);
        
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
        return $this->getController()->execute('delete', $context);
    }
        
	/**
	 * Set the default controller to LibBaseControllerResource
	 * 
	 * @see KDispatcherAbstract::setController()
	 */
	public function setController($controller)
	{
		parent::setController($controller);
		
		if ( !$this->_controller instanceof KControllerAbstract ) {
            $default = 'Com'.ucfirst($this->getIdentifier()->package).'ControllerDefault';
			register_default(array('identifier'=>$this->_controller, 'default'=>array($default,'LibBaseControllerService')));
		}
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
	    $data  = $context->data;
        
	    $context->append(array(
            'html_redirect' => KRequest::get('server.HTTP_REFERER', 'url')
	    ));	    	    
	    
	    $context->append(array(
	        'render_after_post'  => is_string($context->result),
	        'forward_after_post' => ($this->format == 'json' || KRequest::type() == 'AJAX') && 
	                        ($context->status == KHttpResponse::RESET_CONTENT || $context->status == KHttpResponse::CREATED),
	        'auto_redirect_after_post'     => KRequest::type() == 'HTTP' 
	                                && $this->format == 'html' 
	                                && !$this->getController()->getRedirect()->location          
	    ));

	    if ( $context->render_after_post ) 
	    {
	        $this->getController()->getRedirect()->code = null;
	        return $context->result;
	    }
	    elseif ( $context->forward_after_post === true )
	    {
	        //don't do any redirect
	        $this->getController()->getRedirect()->code = null;
	        
	        $context->result = $this->getController()
	                //set the view to single
	                ->view($this->getController()->getIdentifier()->name)
    	            //render the item
	                ->execute('display', $context);
	            ;
	       return $context->result;
	    }
	    elseif ( $context->auto_redirect_after_post &&
	            $context->html_redirect ) {
	        $this->getController()->setRedirect($context->html_redirect); 
	    }
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
	    $redirect = $this->getController()->getRedirect();
	    
	    //if the redirect code i
	    if ( $redirect->location && 
	            $redirect->code >= 300 && $redirect->code < 400 ) 
	    {
    	    $context->status              = $redirect->code;
    	    $context->status_message      = $redirect->message;
    	    $context->headers['Location'] = (string)$redirect->location;    	    
	    }
	    
	    //if there's a content set the content type
	    if(is_string($context->result)) {
	        header('Content-Type: '.$this->getController()->getView()->mimetype);
	    }
	    
        //Headers
	    if($context->headers)
	    {
	        foreach($context->headers as $name => $value) {
	            header($name.' : '.$value);
	        }
	    }

	    //Status
        if($context->status) {
            header(KHttpResponse::getHeader($context->status, $context->status_message, KRequest::protocol()));
        }
                
	    if(is_string($context->result)) {
		     return $context->result;
		}
	}
	
}