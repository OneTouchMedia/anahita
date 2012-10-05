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
class ComPostsControllerPost extends ComMediumControllerDefault
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
    }
      
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
            $entity->setAccess(array($this->actor->id, $viewer->id));
        }
        
        if ( $entity->owner->isSubscribable() ) {
            $notification = $this->createNotification(array(
                'name'             => 'post_add',
                'object'           => $entity,
                'subscribers'      => $entity->owner->subscriberIds->toArray()
            ))->setType('post', array('new_post'=>true));
        }
        
        return $entity;
    }
}