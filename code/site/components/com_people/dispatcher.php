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
	 * @see ComBaseDispatcher::_actionDispatcherexception()
	 */
	protected function _actionDispatcherexception(KCommandContext $context)
	{
		$exception = $context->exception;
		
		//if a session throws exception then check the code
		//and generate a correct message
		if ( $this->getController()->getIdentifier()->name == 'session' ) 
		{
			if ( KRequest::type() == 'HTTP' )
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
				$login_url   = JRoute::_('index.php?option=people&view=session');
				JFactory::getApplication()->redirect($login_url, JText::_($message));
				return false;
			}
		}
		
		return parent::_actionDispatcherexception($context);		
	}	
}