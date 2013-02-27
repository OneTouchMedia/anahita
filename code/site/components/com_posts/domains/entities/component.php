<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Com_Posts
 * @subpackage Domain_Entity
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id: view.php 13650 2012-04-11 08:56:41Z asanieyan $
 * @link       http://www.anahitapolis.com
 */

/**
 * Component object
 *
 * @category   Anahita
 * @package    Com_Posts
 * @subpackage Domain_Entity
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class ComPostsDomainEntityComponent extends ComMediumDomainEntityComponent
{
	/**
	 * Return max
	 * 
	 * @return int
	 */
	public function getPriority()
	{
		return -PHP_INT_MAX;
	}
	
	/**
	 * @{inheritdoc}
	 */
	protected function _setComposers($actor, $composers, $mode)
	{
		if ( $actor->authorize('action','com_posts:post:add') )
		{
			$composers->insert('posts',array(
					'title'        => JText::_('COM-POSTS-COMPOSER-POST'),
					'placeholder'  => JText::_('COM-POSTS-COMPOSER-PLACEHOLDER'),
					'url'      => 'option=com_posts&layout=composer&view=post&oid='.$actor->id
			));
		}
	}	
}