<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Com_Menu
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id: view.php 13650 2012-04-11 08:56:41Z asanieyan $
 * @link       http://www.anahitapolis.com
 */

/**
 * Router
 *
 * @category   Anahita
 * @package    Com_Menu
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class ComMenuRouter extends ComBaseRouterDefault
{
    /**
     * (non-PHPdoc)
     * @see ComBaseRouterAbstract::build()
     */
    public function build(&$query)
    {
        $segments = array();
        if ( isset($query['id']) ) 
        {
            $item = JSite::getMenu()->getItem($query['id']);                        
            if ( $item ) {
                $route = $item->route;
                unset($query['id']);
                $segments[] = $route;                
            }
        }
        return $segments;
    }
    
    /**
     * (non-PHPdoc)
     * @see ComBaseRouterAbstract::build()
     */
    public function parse($segments)
    {
        $vars = array();
        $menu = &JSite::getMenu();
        if ( !empty($segments) ) {
            $route  = implode('/', $segments);
            $item	= $menu->getItems('route', $route, true);
        } else {
            $item   = $menu->getItems('home', true, true);
        }

		if ( !empty($item) ) {
			$vars = $item->query;
			$vars['Itemid'] = $item->id;
		}
		$vars = array_merge(array('option'=>null), $vars);
		return $vars;
    }    
}