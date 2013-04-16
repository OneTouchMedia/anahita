<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Com_Stories
 * @subpackage Controller_Behavior
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2011 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id: resource.php 11985 2012-01-12 10:53:20Z asanieyan $
 * @link       http://www.anahitapolis.com
 */

/**
 * Publisher Behavior. Publishes stories after an action
 *
 * @category   Anahita
 * @package    Com_Stories
 * @subpackage Controller_Behavior
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class ComStoriesControllerBehaviorPublisher extends KControllerBehaviorAbstract
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
                                'component' => 'com_'.$this->_mixer->getIdentifier()->package,
                                'name' 		=> $name,
                                'owner'		=> $this->actor,
                                'object'	=> $this->getItem(),
                                'target'	=> $this->actor	,
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
                'component' => 'com_'.$this->_mixer->getIdentifier()->package,
                'owner'		=> get_viewer()
        ));
    
        $story = $this->getService('repos://site/stories.story')->create($config);
        $story->save();
        return $story;
    }
}