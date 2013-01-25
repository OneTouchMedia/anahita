<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Com_Posts
 * @subpackage Controller
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id$
 * @link       http://www.anahitapolis.com
 */
//set a max limit for the story
define('POST_MAX_LIMIT', 5000);

/**
 * Post Controller
 * 
 * @category   Anahita
 * @package    Com_Posts
 * @subpackage Controller
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class ComPostsControllerPost extends ComMediumControllerDefault
{
    /**
     * Adds a new post
     * 
     * @param KCommandContext $context Context parameter         
     * 
     * @return void
     */      
    protected function _actionAdd($context)
    {   
        $data   = $context->data;
        
        $entity = parent::_actionAdd($context);
        
        //if a person posting a message on his profile
        //or if a target is not actor then it can't be a private message
        if ( get_viewer()->eql($this->actor) || !is_person($this->actor) ) {
            unset($data->private_message);       
        }
        
        //if a private message then
        //set the privacy to subject/target
        if ( $data->private_message ) {
            $entity->setAccess(array($this->actor->id, get_viewer()->id));
        }

        //create a notification for the subscribers and 
        //the post owner as well
        if ( $entity->owner->isSubscribable() ) 
        {
            //create a notification and pass the owner
            $notification = $this->createNotification(array(
                'name'             => 'post_add',
                'object'           => $entity,
                'subscribers'      => array($entity->owner->subscriberIds->toArray(),$entity->owner)
            ))->setType('post', array('new_post'=>true));
        }
        
        return $entity;
    }
}