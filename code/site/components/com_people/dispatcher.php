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
	 * (non-PHPdoc)
	 * @see ComBaseDispatcher::_handleDispatchException()
	 */
    protected function _handleDispatchException(KCommandContext $context, KException $exception)
    {		
		//if a session throws exception then check the code
		//and generate a correct message
		if ( $this->getController()->getIdentifier()->name == 'session' ) 
		{
			if ( KRequest::type() == 'HTTP' && $this->format == 'html' )
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
		}
		elseif ( $this->getController()->getIdentifier()->name == 'person' ) 
		{
			//if the registration is closed
			if ( $this->format == 'html' && 
					KRequest::type() == 'HTTP' && $exception->getCode() == KHttpResponse::METHOD_NOT_ALLOWED )
			{
				if ( KRequest::method() == 'GET' && $this->getRequest()->layout == 'add' )
				{
					$this->getService('application')
							->redirect($this->_login_url, JText::_('COM-PEOPLE-REGISTRATION-CLOSED'), 'error');
					return false;					
				}
			}
		}
		
		return parent::_handleDispatchException($context, $exception);		
	}
	
	/**
	 * If a person has been registered succesfully and no activation is required then
	 * log them in. If activation is required show a message 
	 * 
	 * (non-PHPdoc)
	 * @see ComBaseDispatcher::_actionForward()
	 */
	protected function _a_ctionForward(KCommandContext $context)
	{
		//if a http request 
		//then creating a person
		if ( KRequest::type() == 'HTTP' )
		{
			if ( $this->getController()->getIdentifier()->name == 'person' )
			{
				if ( $context->status == KHttpResponse::CREATED )
				{
					_die();	
				}
			}			
		}		
		
		return parent::_actionForward($context);	
	}
}