<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Com_Actors
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id: resource.php 11985 2012-01-12 10:53:20Z asanieyan $
 * @link       http://www.anahitapolis.com
 */

/**
 * Abstract Actor Router
 * 
 * @category   Anahita
 * @package    Com_Actors
 */
abstract class ComActorsRouterAbstract extends ComBaseRouterDefault
{
    /**
     * Build the route
     *
     * @param   array   An array of URL arguments
     * @return  array   The URL arguments to use to assemble the subsequent URL.
     */
    public function build(&$query)
    {
        $has_id = isset($query['id']);
        $segments = parent::build($query);
        
        if ( $has_id ) {
        	if ( isset($query['get']) ) {
        		$segments[] = $query['get'];
        		if ( $query['get'] == 'graph' ) {
        			if ( !isset($query['type']) ) {
        				$query['type'] = 'followers';
        			}
        			$segments[] = $query['type'];
        			unset($query['type']);
        		}
        		unset($query['get']);
        	}
        } else if ( isset($query['oid']) ) {
        	$segments[] = '@'.$query['oid'];
        	unset($query['oid']);
        }
        
        return $segments;
    }

    /**
     * Parse the segments of a URL.
     *
     * @param   array   The segments of the URL to parse.
     * @return  array   The URL attributes to be used by the application.
     */
    public function parse(&$segments)
    {    	
    	$last = AnHelperArray::getValueAtIndex($segments, AnHelperArray::LAST_INDEX);
    	
    	$vars = array();
    	
    	if ( preg_match('/@\w+/', $last) ) {
    		$vars['oid'] = str_replace('@','',array_pop($segments));
    	}
    	
        $vars = array_merge($vars, parent::parse($segments));
        
        if ( isset($vars['id']) && isset($vars['view']) ) {
        	if ( count($segments) ) {
        		$vars['get'] = array_shift($segments);
        		if ( $vars['get'] == 'graph' ) {
        			$vars['type'] = count($segments) ? array_shift($segments) : 'followers';
        		}
        	}
        }
        
        return $vars;
    }
}