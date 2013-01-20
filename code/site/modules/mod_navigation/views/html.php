<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Mod_Navigation
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id: resource.php 11985 2012-01-12 10:53:20Z asanieyan $
 * @link       http://www.anahitapolis.com
 */

/**
 * Navigation Module
 *
 * @category   Anahita
 * @package    Mod_Navigation
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class ModNavigationViewHtml extends ModBaseView
{	
	/**
	 * Set an array of menu item
	 * 
	 * @param array $items
	 * 
	 * @return void
	 */
	public function setItems($items)
	{
		$array    = array();
		$children = array();
		foreach($items as $item)
		{
			//check if it has any children. then claim them
			if ( isset($children[$item->id]) ) {
				$item->subitems = $children[$item->id];
			}
		
			if ( isset($item->parent) && $item->parent > 0 )
			{
				if ( isset($array[$item->parent]) )
				{
					$parent = $array[$item->parent];
					if ( !isset($parent->subitems) ) {
						$parent->subitems = array();
					}
					$parent->subitems[] = $item;
				}
				else {
					$children[$item->parent][] = $item;
				}
			}
			else {
				$array[$item->id] = $item;
			}
		}
		
		$this->items = $array;
	}
	
	/**
	 * Cleanup the menu route
	 * 
	 * (non-PHPdoc)
	 * @see LibBaseViewAbstract::getRoute()
	 */
	public function getRoute($item, $option = '')
	{
		if ( is_object($item) ) {
			$option = 'Itemid='.$item->id;
			if ( strpos($item->link,'com_content') ) {
				$option .= '&alias='.$item->alias;
			}
			return $this->getRoute($item->link, $option);
		}
		else {
			$route = $item;
			$route = str_replace('index.php?', '', $route);
			if ( strpos($route, 'option=') === 0) {
				if ( $option ) {
					$route .= '&'.$option;
				}
				$route  = parent::getRoute($route);				
			}			
			return $route;					
		}		
	}
}