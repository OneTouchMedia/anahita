<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Com_People
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id: resource.php 11985 2012-01-12 10:53:20Z asanieyan $
 * @link       http://www.anahitapolis.com
 */

class ComPeopleRouter extends ComBaseRouter
{
    /**
     * Build the route
     *
     * @param   array   An array of URL arguments
     * @return  array   The URL arguments to use to assemble the subsequent URL.
     */
    public function buildRoute(&$query)
    {
        $segments = array();
        
        if ( isset($query['view']) ) {
            $segments[] = $query['view'];
            unset($query['view']);
        } 
    
        if ( isset($query['id']) && !is_array($query['id']) ) {
            $segments[] = $query['id'];
            unset($query['id']);
        }
        
        if ( isset($query['get']) ) {
            $segments[] = $query['get'];
            unset($query['get']);
        }
        
        if ( isset($query['type']) ) {
            $segments[] = $query['type'];
            unset($query['type']);
        }
        
        return $segments;
    }
        
    /**
     * Parse the segments of a URL.
     *
     * @param   array   The segments of the URL to parse.
     * @return  array   The URL attributes to be used by the application.
     */    
    public function parseRoute($segments)
    {
        $vars = array();
        
        $vars['view']   = array_shift($segments);
        
        if ( count($segments) )
            $vars['id'] = array_shift($segments);
            
        if ( count($segments) )
            $vars['get'] = array_shift($segments);
            
        if ( count($segments) )
            $vars['type'] = array_shift($segments);
                    
        return $vars;        
    }
}