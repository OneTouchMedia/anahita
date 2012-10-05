<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Com_Posts
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id$
 * @link       http://www.anahitapolis.com
 */

/**
 * Post app delegate
 *   
 * @category   Anahita
 * @package    Com_Posts
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class ComPostsDelegate extends ComAppsDomainDelegateDefault
{	
    
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
            'priority'            => -PHP_INT_MAX,
            'assignment_option'   => self::ASSIGNMENT_OPTION_ALWAYS       
        ));
        
        return parent::_initialize($config);
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
        
	/**
	 * Return a set of resources and type of operation on each resource
	 *
	 * @return array
	 */
	public function getResources()
	{
	    return array(
	        'post' => array('add','addcomment')	            
	    );
	}	
}