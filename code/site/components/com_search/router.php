<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Com_Search
 * @subpackage Dispatcher
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id: view.php 13650 2012-04-11 08:56:41Z asanieyan $
 * @link       http://www.anahitapolis.com
 */

/**
 * Base Router
 *
 * @category   Anahita
 * @package    Com_Search
 * @subpackage Dispatcher
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class ComSearchRouter extends ComBaseRouterDefault
{    
    /**
     * Build the route
     *
     * @param   array   An array of URL arguments
     * @return  array   The URL arguments to use to assemble the subsequent URL.
     */
    public function build(&$query)
    {
        $segments = array();
        //we don't need the view
                
        if ( !empty($query['type']) ) {
            $segments = array_merge($segments, explode('.',$query['type']));           
        }
        
        if ( isset($query['q']) ) {
            $segments[] = $query['q'];        
        }
        
        unset($query['view']);
        unset($query['q']);        
        unset($query['type']);        
        
        return $segments;
    }

    /**
     * Parse the segments of a URL.
     *
     * @param   array   The segments of the URL to parse.
     * @return  array   The URL attributes to be used by the application.
     */
    public function parse($segments)
    {
        $vars = array();   
        //check if we are searching for tags     
        $vars['q'] = array_pop($segments);
        //if the next is tags
        if ( key($segments) == 'tags' ) {
            $vars['q'] = '#'.$vars['q'];
            array_pop($segments);
        }
        $vars['type'] = implode('.', $segments);
        return $vars;
    }    
    
}