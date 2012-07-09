<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Lib_Base
 * @subpackage Domain_Authorizer
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id: resource.php 11985 2012-01-12 10:53:20Z asanieyan $
 * @link       http://www.anahitapolis.com
 */

/**
 * Default Authorizer
 *
 * @category   Anahita
 * @package    Lib_Base
 * @subpackage Domain_Authorizer
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class LibBaseDomainAuthorizerDefault extends LibBaseDomainAuthorizerAbstract 
{
	/**
	 * Check if a node authroize being subscribed too
	 * 
	 * @param KCommandContext $context Context parameter
	 * 
	 * @return boolean
	 */
	protected function _authorizeSubscribe($context)
	{
		$entity = $this->_entity;
		
		if ( $this->_viewer->guest() )
			return false;
	
		if ( !$entity->isSubscribable() )
			return false;
		
		//if already subscribed to the node then return false
		if ( $entity->subscribed($this->_viewer) )
			return false;	
		
		return true;
	}	
	
	/**
	 * Check if a node authroize being voted
	 * 
	 * @param KCommandContext $context Context parameter
	 * 
	 * @return boolean
	 */
	protected function _authorizeVote($context)
	{
		if ( $this->_viewer->guest() )
			return false;
					
		return $this->_entity->isVotable();
	}


	/**
	 * Checks if a comment can be added to a  node
	 * 
	 * @param KCommandContext $context Context parameter
	 * 
	 * @return boolean
	 */
	protected function _authorizeAddComment($context)
	{		
		if ( $this->_viewer->guest() )
		    return false;
		
		if ( $this->_entity->isCommentable() )
		{
			if ( !$this->_entity->openToComment )
                return false;
			
			if ( $this->_entity->isOwnable() ) 
			{
                //if ownable and can't access the owner then
                //can't comment
				if ( $this->_entity->owner->authorize('access') === false )
                    return false;
                                    
                $action  = 'com_'.$this->_entity->getIdentifier()->package.':'.$this->_entity->getIdentifier()->name.':addcomment';
                				            
                return $this->_entity->owner->authorize('action',array('action'=>$action));
			}
		}
		
		return  false;		
	}
}