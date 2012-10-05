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
class ComPostsControllerPost extends ComBaseControllerService
{
    /**
     * Post a story
     * 
     * @param  KCommandContext $context
     * @return string;
     */
    protected function _actionPost($context)
    {
        $data    = $context->data;
        $actor   = $this->actor;
        $viewer  = get_viewer();
        
        if ( $data->private_message && is_person($actor) && !$actor->eql($viewer) ) {
            $name = 'private_message';
        } else
            $name = 'story_add';
            
        $component = $actor->component ;
        
        $story = $this->setItem($this->createStory(array(
            'component' => $component,
            'name'      => $name,
            'subject'   => get_viewer(),
            'target'    => $actor,
            'owner'     => $actor,
            'body'      => $data['body']
        )))->getItem();
          
        if ( !$story->sanitizeData('body', array('length'=>STORY_MAX_LIMIT))
                    ->validateData('body', 'required') ) 
        
           return false;
                
        if ( $name == 'private_message' ) {
            $this->getItem()->setAccess(array($actor->id, $viewer->id));
        }                   

        if ( $this->commit($context) === false ) {
            return false;
        }
                
        $this->actor = $actor;
        $output      = $this->setView('story')->layout('list')->display();
            
        $helper = clone $this->getView()->getTemplate()->getHelper('parser');
        
        $data   = array(
            'story'     => $story,
            'actor'     => $actor,
            'viewer'    => $viewer,
            'channels'  => $data->channels,
            'data'      => $helper->parse($story) 
        );
        
        if ( $name != 'private_message' )
            dispatch_plugin('connect.onPostStory', $data);
        

        $subscribers = array();
        
        if ( $actor->isSubscribable() ) {
            $subscribers   = $actor->subscriberIds->toArray();
            $subscribers[] = $actor;
        }
        else 
            $subscribers = array($actor);
            
        $notifcation = $this->createNotification(array(
            'component' => $component,   
            'name'      => $name,
            'target'    => $actor,
            'object'         => $story,
            'subscribers'    => $subscribers
        ))
        ->setType('post', array('new_post'=>true))
        ;

        return $output;
    }    
}