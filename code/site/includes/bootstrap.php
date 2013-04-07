<?php
/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id: view.php 13650 2012-04-11 08:56:41Z asanieyan $
 * @link       http://www.anahitapolis.com
 */
// no direct access
defined( 'JPATH_BASE' ) or die( 'Restricted access' );

require_once ( JPATH_BASE.'/includes/framework.php' );

if ( PHP_SAPI == 'cli' ) 
{        
    if ( !empty($_SERVER['argv']) && count($_SERVER['argv']) > 1 ) 
    {
         $args = array_slice($_SERVER['argv'], 1);
         if ( is_readable(realpath($args[0])) ) {
             $file = array_shift($args);
         }
         $args = explode('&-data&', implode($args,'&'));
         $args = array_filter($args, 'trim');
         foreach($args as $i => $arg) 
         {
             $arg = trim($arg);
             if ( $i == 0 ) 
             {
                if ( strpos($arg,'/') !== false ) 
                {
                    $arg  =  substr_replace($arg, '?', strpos($arg,'&'), 1);
                    $url  = KService::get('koowa:http.url', array('url'=>$arg));
                    KRequest::url()->path = KRequest::base().$url->path;
                    $_GET = $url->query;
                } else {
                    KRequest::url()->path = KRequest::base();
                    parse_str($arg, $_GET);
                }
                $_GET['format'] = 'json';
                KRequest::url()->format = 'json';                
                KRequest::url()->setQuery($_GET);
             }
             
             else {
                 parse_str($arg, $_POST);
             }
         }
    }
    
    //if there's a file then just load the file and exit
    if ( !empty($file) ) 
    {
        KService::get('com://site/application.dispatcher')->load();
        KService::get('koowa:loader')->loadFile($file);
        exit(0);
    }
}

KService::get('com://site/application.dispatcher')->run();

