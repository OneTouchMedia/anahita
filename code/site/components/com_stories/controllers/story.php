<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Com_Stories
 * @subpackage Controller
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id$
 * @link       http://www.anahitapolis.com
 */

//set a max limit for the story
define('STORY_MAX_LIMIT', 5000);

/**
 * Story Controller
 * 
 * @category   Anahita
 * @package    Com_Stories
 * @subpackage Controller
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class ComStoriesControllerStory extends ComBaseControllerService
{
	/**
	 * Constructor.
	 *
	 * @param 	object 	An optional KConfig object with configuration options
	 */
	public function __construct(KConfig $config)
	{
		parent::__construct($config);
		
		$this->getCommentController()->registerCallback('after.add', array($this, 'createStoryCommentNotification'));		
	}
		
    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param 	object 	An optional KConfig object with configuration options.
     * @return 	void
     */	
	protected function _initialize(KConfig $config)
	{
        $config->append(array(
            'behaviors' => array(
                'ownable' => array('default'=>get_viewer())
            )
        ));
        
		parent::_initialize($config);
	}
		
    /**
     * Browse action
     * 
     * @param KCommandContext $context Context parameter
     * 
     * @return void
     */
	protected function _actionBrowse($context)
	{		
		$query 	  = $this->getRepository()->getQuery()			
					->limit( $this->start == 0 ?  20 : 20, $this->start );

        
        if ( $this->filter == 'leaders') 
        {
            $ids    = get_viewer()->leaderIds->toArray();
            $ids[]  = get_viewer()->id;
            $query->where('owner.id','IN', $ids);            
        }
		else {
			$query->owner($this->actor);
		}
	
		$apps 		  =	 $this->getService('repos:apps.app')->fetchSet();		
        $keys         =  new KConfig();
		
        if ( count($apps ) ) 
        {
    		foreach($apps as $app) 
            {
    			$context = new KCommandContext();
    			$app->getDelegate()->setStoryOptions($context);
    			$keys->append(array(
    				$app->component => pick($context->summarize, array())
    			));
    		}
        }
        
        $query->aggregateKeys($keys);
        
        return $this->setList($query->toEntitySet())
                    ->getList();
	}
	
	/**
	 * Delete a story
	 * 
	 * @return boolean
	 */
	protected function _actionDelete($context)
	{
        $this->getItem()->delete();
        $this->setRedirect($this->getItem()->owner->getURL());
	}
}