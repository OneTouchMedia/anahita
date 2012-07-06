<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Com_Base
 * @subpackage Controller_Behavior
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2011 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id: resource.php 11985 2012-01-12 10:53:20Z asanieyan $
 * @link       http://www.anahitapolis.com
 */

/**
 * Publisher Behavior. Publishes stories and notification
 *
 * @category   Anahita
 * @package    Com_Base
 * @subpackage Controller_Behavior
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class ComBaseControllerBehaviorPublisher extends KControllerBehaviorAbstract
{
    /**
     * Creates a story
     *
     * @param array|KCommandContext $config Config. Can be a story data or KCommandContext if the method
     * is used as a callback
     *
     * @return ComStoriesDomainEntityStory
     */
    public function createStory($config = array())
    {
        if ( is($config, 'KCommandContext') )
        {
            if ( $config->result !== false )
            {
                $data	 = $config->data;
                $name    = $this->_mixer->getIdentifier()->name.'_'.$config->action;
                $config->append(array(
                        'story' => array(
                                'component' => 'com_'.$this->getIdentifier()->package,
                                'name' 		=> $name,
                                'owner'		=> $data->actor,
                                'object'	=> pick($data->entity, $data->{$this->_mixer->getIdentifier()->name}),
                                'target'	=> $data->actor	,
                                'comment'	=> $this->isCommentable() ? $data->comment : null
                                )
                                ));
                $story = $this->createStory( KConfig::unbox($config->story) );
                $data->story = $story;
                return $story;
            }
            else return false;
        } else
            $config =  new KConfig($config);
    
        $config->append(array(
                'subject'	=> get_viewer(),
                'component' => 'com_'.$this->getIdentifier()->package,
                'owner'		=> get_viewer()
        ));
    
        $story = $this->getService('repos:stories.story')->create($config);
        $story->save();
        return $story;
    }
    
    /**
     * Creates a notification
     *
     * @param array $data Notification data
     *
     * @return ComNotificationDomainEntityNotification
     */
    public function createNotification($data = array())
    {        
        $data = new KConfig($data);
    
        $data->append(array(
                'component' => 'com_'.$this->getIdentifier()->package,
                'subject'	=> get_viewer()
        ));
            
        $notification = $this->getService('repos:notifications.notification')->getEntity(array('data'=>$data));
    
        $notification->removeSubscribers( get_viewer() );
    
        return $notification;
    }    
}