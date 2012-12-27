<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Com_Medium
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id: resource.php 11985 2012-01-12 10:53:20Z asanieyan $
 * @link       http://www.anahitapolis.com
 */

class ComMediumRouter extends ComBaseRouter
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
        
        if ( isset($query['oid']) ) {            
            $segments[] = $query['oid'];
            unset($query['oid']);
        }
        
        $segments = array_merge($segments, parent::build($query));
        
        if ( isset($query['alias']) ) {
              $segments[] = $query['alias'];
              unset($query['alias']);
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
        $vars = array();        
       
        //if the first segment is numeric then it's oid
        if ( is_numeric(current($segments)) ) {
            $vars['oid'] = array_shift($segments);
        }
        
        $vars = array_merge($vars, parent::parse($segments));
        
        if ( isset($vars['id']) && current($segments) ) {
            $vars['alias'] = array_shift($segments);
        }
        
        return $vars;
    }
}