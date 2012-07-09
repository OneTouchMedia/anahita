<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Com_Actors
 * @subpackage View
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id: resource.php 11985 2012-01-12 10:53:20Z asanieyan $
 * @link       http://www.anahitapolis.com
 */

/**
 * Default Setting View
 *
 * @category   Anahita
 * @package    Com_Actors
 * @subpackage View
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class ComActorsViewSettingsHtml extends ComBaseViewHtml
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
			'template_paths' => array(dirname(__FILE__).'/html')			
		));	

		parent::_initialize($config);
		
		$config->append(array(
            'template_paths' => array(JPATH_THEMES.'/'.JFactory::getApplication()->getTemplate().'/html/com_actor/settings')
		));
	}	
	
	/**
	 * Layout Profile
	 * 
	 * @return void
	 */
	protected function _layoutProfile()
	{
		$this->profile = new KConfig();
		
		dispatch_plugin('profile.onEdit', array('actor'=>$this->entity, 'profile'=>$this->profile));				
	}
	
	/**
	 * Layout Privacy
	 * 
	 * @return void
	 */
	protected function _layoutPermissions()
	{
		$apps_resources = new KConfig();
        $apps     = AnHelperArray::indexBy($this->apps,'id');
        function __sort_apps__($app1, $app2)
        {
            return $app1->getDelegate()->getPriority() > $app2->getDelegate()->getPriority();    
        }
        usort($apps, "__sort_apps__");
		$this->apps = $apps;
		foreach($this->apps as $app) 
		{
            $assignment  = $app->getAssignment($this->entity);
		    
		    //if the app assignment is set to always or has been
		    //enabled then show the permission tab
		    if ( $assignment == ComAppsDomainEntityApp::ACCESS_GLOBAL || $app->enabled($this->entity))
		    {
    			$resources = KConfig::unbox($app->getDelegate()->getResources());
    			
    			if ( !empty($resources) ) 
    			{
    				foreach($resources as $name => $operations ) 
    				{
    					foreach($operations as $operation) 
    					{
    						//COM-<Name>-PERMISSION-<Resource>-<Operation>
    						$label  = strtoupper('COM-'.$app->getName().'-PERMISSION'.'-'.$name.'-'.$operation);
                            $apps_resources->append(array(
                                $app->getName() => array(
                                    translate($label) => $app->component.':'.$name.':'.$operation
                                )
                            ));					
    					}
    				}
    			}
		    }
		}
		
		$this->apps_resources = $apps_resources;
	}
		
	/**
	 * Default Layout
	 * 
	 * @return void
	 */
	protected function _layoutDefault()
	{
		$this->edit = pick($this->edit, 'profile');
		
		$tabs   = new LibBaseTemplateObjectContainer();
		
		$tabs->insert('profile', array(
            'label' => JText::_('COM-ACTORS-PROFILE-EDIT-TAB-PROFILE'),
		));

		$tabs->insert('avatar', array(
		   'label' => JText::_('COM-ACTORS-PROFILE-EDIT-TAB-AVATAR'),
		));			
		
		$tabs->insert('permissions', array(
            'label' => JText::_('COM-ACTORS-PROFILE-EDIT-TAB-PERMISSIONS'),            
		));
        
        if ( $this->entity->isFollowable() && $this->entity->followRequesterIds->count() > 0 )
        {
                    
            $tabs->insert('requests', array(
                'label' => JText::_('COM-ACTORS-PROFILE-EDIT-TAB-REQUESTS').'<span class="pull-right badge badge-important">'.$this->entity->followRequesterIds->count().'</span>',
            ));            
        }
        
	
		if ( $this->entity->isAdministrable() ) {
		    $tabs->insert('admins', array(
		        'label' 	=> JText::_('COM-ACTORS-PROFILE-EDIT-TAB-ADMINS'),
		    ));
		}

		
		//addable/removable apps
		$enablable_apps = array();
		
		foreach($this->apps as $app)
		{
		    $assignment  = $app->getAssignment($this->entity);
		    
		    if ( $assignment == ComAppsDomainEntityApp::ACCESS_OPTIONAL ) {
		        $enablable_apps[] = $app;
		    }
		}
		
		$this->enablable_apps = $enablable_apps;
				
		if ( count($this->enablable_apps) )
    		$tabs->insert('apps', array(
                'label' 	=> JText::_('COM-ACTORS-PROFILE-EDIT-TAB-APPS'),
    		));
		
		
		$this->getService('anahita:event.dispatcher')->dispatchEvent('onSettingDisplay', array('actor'=>$this->entity, 'tabs'=>$tabs));
		
        $tabs->insert('delete', array(
                'label'     => JText::_('COM-ACTORS-PROFILE-EDIT-TAB-DELETE'),
        ));
                			
		$url        = $this->entity->getURL().'&get=settings&edit=';		
		$active_tab = $tabs['profile'];;
		foreach($tabs as $tab) 
		{
			$tab->url = $url.$tab->getName();
			if ( $tab->name == $this->edit ) {
				$active_tab = $tab;
			}
		}
		
		$active_tab->active = true;
		
		$this->tabs = $tabs;
		
		if ( $active_tab->content ) 
		{
		     $this->content = $active_tab->content;
		}
		elseif ( $active_tab->controller )  
		{
			 $this->content = $this->getService($active_tab->controller)->oid($this->entity->id)->display();			  
		}        
		else $this->content	= $this->load(pick($active_tab->layout, $active_tab->name), array('url'=>$url));
	}		
}