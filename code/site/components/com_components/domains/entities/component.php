<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Lib_Components
 * @subpackage Domain_Entity
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id$
 * @link       http://www.anahitapolis.com
 */

/**
 * Component object
 *
 * @category   Anahita
 * @package    Lib_Components
 * @subpackage Domain_Entity
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class ComComponentsDomainEntityComponent extends LibComponentsDomainEntityComponent implements KEventSubscriberInterface
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
		JFactory::getLanguage()->load('com_'.$this->getIdentifier()->package);
		
		$config->append(array(
				
		));		
		
		parent::_initialize($config);
	}
	
	/**
	 * Registers event dispatcher
	 *
	 * @param KEventDispatcher $dispatcher Event dispatche
	 *
	 * @return void
	 */
	public function registerEventDispatcher(KEventDispatcher $dispatcher)
	{
		$dispatcher->addEventSubscriber($this);
	}	
	
	/**
	 * Get the priority of the handler
	 *
	 * @return	integer The event priority
	 */
	public function getPriority()
	{
		return $this->_priority;
	}
	
	/**
	 * Get a list of subscribed events
	 *
	 * Event handlers always start with 'on' and need to be public methods
	 *
	 * @return array An array of public methods
	 */
	public function getSubscriptions()
	{
		if(!$this->__subscriptions)
		{
			$subscriptions  = array();
	
			//Get all the public methods
			$reflection = new ReflectionClass($this);
			foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method)
			{
				if(substr($method->name, 0, 2) == 'on') {
					$subscriptions[] = $method->name;
				}
			}
	
			$this->__subscriptions = $subscriptions;
		}
	
		return $this->__subscriptions;
	}	
}