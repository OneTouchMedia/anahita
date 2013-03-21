<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Com_People
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id: resource.php 11985 2012-01-12 10:53:20Z asanieyan $
 * @link       http://www.anahitapolis.com
 */

/**
 * People Dispatcher
 *
 * @category   Anahita
 * @package    Com_People
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class ComPeopleDispatcher extends ComBaseDispatcher
{
    /**
     * The URL to redirect the user if they're logged in
     *
     * @var string
     */
    protected $_login_redirect_url;
    
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
    
        $this->_login_redirect_url = $config->login_redirect_url;
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
        //you can set the redirect url for when a user is logged in
        //as follow
        //KService::setConfig('com://site/people.dispatcher', array(
        // 'login_redirect_url' => 'mynewurl'
        //));
        $config->append(array(
                'login_redirect_url' => 'option=com_dashboard&view=dashboard'
        ));
    
        parent::_initialize($config);
    }
        
	/**
	 * (non-PHPdoc)
	 * @see ComBaseDispatcher::_handleDispatchException()
	 */
    protected function _handleDispatchException(KCommandContext $context, KException $exception)
    {		
		//if a session throws exception then check the code
		//and generate a correct message
		if ( $this->getController()->getIdentifier()->name == 'session' ) 
		{
			switch($exception->getCode()) 
			{
				case KHttpResponse::UNAUTHORIZED :
					$message = 'COM-PEOPLE-AUTHENTICATION-FAILED';break;
				case KHttpResponse::FORBIDDEN : 
					$message = 'COM-PEOPLE-AUTHENTICATION-PERSON-BLOCKED';break;
				default : 
					$message = 'COM-PEOPLE-AUTHENTICATION-FAILED-UNKOWN';break;
			}
			
			$this->getService('application')
					->redirect($this->_login_url, JText::_($message));
							
			return false;
		}
		elseif ( $this->getController()->getIdentifier()->name == 'person' ) 
		{
			//if the registration is closed
			if ( $exception->getCode() == KHttpResponse::METHOD_NOT_ALLOWED )
			{
				if ( $context->action == 'read' && $this->getRequest()->layout == 'add' )
				{
				    if ( JFactory::getUser()->id ) {
				        $url = $this->getService('application.router')->build('index.php?'.get_viewer()->getURL());
				        $this->getService('application')->redirect($url);
				    }
					else {
					    $this->getService('application')
							->redirect($this->_login_url, JText::_('COM-PEOPLE-REGISTRATION-CLOSED'), 'error');
					}
					return false;					
				}
			}
		}
		
		return parent::_handleDispatchException($context, $exception);		
	}
	
	/**
	 * (non-PHPdoc)
	 * @see ComBaseDispatcher::_actionDispatch()
	 */
	protected function _actionDispatch(KCommandContext $context)
	{
	    if ( $this->token &&
	            $this->getController()->getIdentifier()->name == 'person' )
	    {
	        if ( $this->getController()->canRead() ) 
	        {
	            $this->getController()->login();
	            if ( $this->reset_password ) 
	            {
	                $url = $this->getController()->getItem()->getURL().'&get=settings&edit=account&reset_password=1';
	            }
	            else {
	                $url = $this->_login_redirect_url;
	            }
	            $url = $this->getService('application.router')->build($url);
	            $this->getService('application')->redirect($url);
	            return false;	            
	        }     
	    }
	    	    
	    return parent::_actionDispatch($context);
	}
	
	/**
	 * If a person has been registered succesfully and no activation is required then
	 * log them in. If activation is required show a message 
	 * 
	 * (non-PHPdoc)
	 * @see ComBaseDispatcher::_actionForward()
	 */
	protected function _actionForward(KCommandContext $context)
	{
	    //if creating a new person login the person in
	    if ( $this->format == 'html' )
	    {
	        if ( $this->getController()->getIdentifier()->name == 'person' )
	        {
	            //if new person is created an no activation is 
	            //required
	            if ( $context->status == KHttpResponse::CREATED && 
	                    !$this->getController()->activationRequired() ) 
	            {
	                $this->getController()->login();
	                $this->getController()->setRedirect(array('url'=>$this->_login_redirect_url));
	            }
	        }
	    }    
	       	    	    
		elseif ( $this->getController()->getIdentifier()->name == 'session' )
		{
			//if a new session is created then render the session
			//info or redirect
			if ( $context->status == KHttpResponse::CREATED ) 
			{
			    if ( $this->format == 'html' ) {
			        $this->getController()->setRedirect(array('url'=>$this->_login_redirect_url));
			    } 
			    else {
			        $context->result = $this->getController()->display();
			    }
			}
		}
		return parent::_actionForward($context);				
	}
}