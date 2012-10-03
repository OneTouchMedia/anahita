<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Com_Stories
 * @subpackage Domain_Behavior
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id$
 * @link       http://www.anahitapolis.com
 */

/**
 * The story subscribable will not subscribe a person to a non-medium like stories
 *
 * @category   Anahita
 * @package    Com_Stories
 * @subpackage Domain_Behavior
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class ComStoriesDomainBehaviorSubscribable extends ComBaseDomainBehaviorSubscribable
{	
    /**
     * Only subscribe the author to the story of posts
     *
     * @param KCommandContext $context Context parameter
     * 
     * @return void
     */
    protected function _afterEntityInsert(KCommandContext $context)
    {
        //only for story_add subscribe the author
        if ( $this->name == 'story_add' ) {
            return parent::_afterEntityInsert($context);    
        }
    }
}