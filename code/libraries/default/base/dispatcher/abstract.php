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
        
        //by default set the view to the package name
        if ( !$config->request->view ) {
            $config->request->view = $this->getIdentifier()->package;
        }
        
        //set the controller to whatever the view is 
        $config->controller = $config->request->view;
    }
        
    /**
     * @see KDispatcherAbstract::_actionDispatch()
     */
    protected function _actionDispatch(KCommandContext $context)
    {
	    $action        = KRequest::get('post.action', 'cmd', strtolower(KRequest::method()));
        	    
	    $context->data = new KConfig();
	    	    
	    if(KRequest::method() != KHttpRequest::GET) {
            $context->data = KRequest::get(strtolower(KRequest::method()), 'raw', array());
        }
        
        $result = $this->getController()->execute($action, $context);
        	    
    	return $result;
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
			register_default(array('identifier'=>$this->_controller, 'default'=>'LibBaseControllerService'));
		}
	}
		
	/**
	 *  In Default App the forward condition is set to
	 * 	if context->result is a string or false don't forward
	 *  if request is HTTP or AJAX with context->redirect_ajax is set then redirect
	 *  ignore AJAX reuqest (in socialengien we never forward an ajax request)
	 * 
	 */
	public function _actionForward(KCommandContext $context)
	{
        //only forward for HTML formats
        if ( $this->format != 'html' ) {
            return $context->result;    
        }
               	    
		$redirect = $this->getController()->getRedirect();
        
        $redirect->append(array(
            'type' => 'success',
            'url'  => (string)KRequest::referrer()
        ));
		
		//if a the result of disatched is string then
		//dispaly the returned value	
		if ( is_string($context->result) ) 
		{
			if ( KRequest::type() == 'HTTP') {
				JFactory::getApplication()->enqueueMessage($redirect['message'], $redirect['type']);
			} else {
				//set the redirect message in the header
				JResponse::setHeader('Redirect-Message', 	   $redirect['message']);
				JResponse::setHeader('Redirect-Message-Type',  $redirect['type']);				
			}
			//set the status to ok if there's a result
			$context->status = KHttpResponse::OK;
			return $context->result;
		}
				
		if (KRequest::type() == 'HTTP') {
			JFactory::getApplication()
					->redirect($redirect['url'], $redirect['message'], $redirect['type']);
		} else {
			JResponse::setHeader('Redirect-Message', 	   $redirect['message']);
			JResponse::setHeader('Redirect-Message-Type',  $redirect['type']);			
		}
	}

	/**
	 * Renders a controller view
	 * 
	 * @return string
	 */
	protected function _actionRender(KCommandContext $context)
	{
		if ( $context->result === false )
			return false;
			
		return parent::_actionRender($context);
	}
	
}