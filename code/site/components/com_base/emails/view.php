<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Com_Base
 * @subpackage Email
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id: view.php 13650 2012-04-11 08:56:41Z asanieyan $
 * @link       http://www.anahitapolis.com
 */

/**
 * HTML View Class
 *
 * @category   Anahita
 * @package    Com_Base
 * @subpackage Email
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class ComBaseEmailView extends LibBaseViewTemplate
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
		$paths[] = dirname($this->getIdentifier()->filepath);
		$paths[] = implode(DS, array(JPATH_THEMES, JFactory::getApplication()->getTemplate(), 'emails', $this->getIdentifier()->type.'_'.$this->getIdentifier()->package));
		$paths[] = implode(DS, array(JPATH_THEMES, JFactory::getApplication()->getTemplate(), 'emails'));
		
		$config->append(array(				
			'template_paths'   => $paths
		));
	
		parent::_initialize($config);
	}
	
	/**
	 * Display the email
	 * 
	 * @return string
	 */
	public function display($data = array())
	{
		$output = $this->load($this->getLayout(), $data);
		
		if ( $this->findTemplate('default') ) {
			$output = $this->load('default', array('body'=>$output));
		}
		
		return $output; 
	}
}