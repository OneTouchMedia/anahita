<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Com_People
 * @subpackage Controller
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id: resource.php 11985 2012-01-12 10:53:20Z asanieyan $
 * @link       http://www.anahitapolis.com
 */

/**
 * Person Controller
 *
 * @category   Anahita
 * @package    Com_People
 * @subpackage Controller
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class ComPeopleControllerPerson extends ComActorsControllerDefault
{
    /**
     * Initializes the default configuration for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param KConfig $config An optional KConfig object with configuration options.
     * 
     * @return void
     */
	protected function _initialize(KConfig $config)
	{
		if ( $config->request->view != 'people' && KRequest::method() == 'GET' )
		{
		    $config->append(array(
		            'request'	 => array(
		                    'id' => get_viewer()->id
		            )
		    ));		    
		}
	
		$config->append(array(
		      'behaviors' => array('validatable')     
		));
		
		parent::_initialize($config);
		
		AnHelperArray::unsetValues($config->behaviors, 'ownable');
	}
	
    /**
     * Hides the menubar title
     * {@inheritdoc}
     */
	protected function _actionGet(KCommandContext $context)
	{
        $this->getToolbar('menubar')->setTitle(null);
					
		return parent::_actionGet($context);
	}

    /**
     * Deletes a person and all of their assets. It also logsout the person.
     * 
     * @param KCommandContext $context Context parameter
     * 
     * @return AnDomainEntityAbstract
     */
    protected function _actionDelete(KCommandContext $context)
    {
        parent::_actionDelete($context);
        
        $this->commit($context);
        
        JFactory::getUser($context->data->entity->userId)->delete();     
        
        $this->getService('com:people.helper.person')->logout($context->data->entity, array('message'=>JText::_('COM-PEOPLE-PERSON-DELETED-MESSAGE')));               
    }
    
    /**
     * Edit a person's data and synchronize with the person with the user entity
     * 
     * @param KCommandContext $context Context parameter
     * 
     * @return AnDomainEntityAbstract
     */
    protected function _actionEdit(KCommandContext $context)
    {
        parent::_actionEdit($context);
        $person = $context->data->entity;
        if ( $person->modifications()->name )
        {
            $user = JFactory::getUser($person->userId);
            $user->name = $context->data->entity->name;
            $user->save();           
        }       
    }
     
    /**
     * Called before the setting page is displayed
     * 
     * @param KEvent $event
     * 
     * @return void
     */
    public function onSettingDisplay(KEvent $event)
    {   
        $tabs = $event->tabs;   
        if ( JFactory::getUser()->id == $event->actor->userId )
        {     
            $tabs->insert('account',array('label'=>JText::_('COM-PEOPLE-SETTING-TAB-ACCOUNT')));
            $tabs->rearrange(array('profile','avatar','account'));            
        } 
    }    
}