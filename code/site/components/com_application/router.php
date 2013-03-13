<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Com_Application
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id: resource.php 11985 2012-01-12 10:53:20Z asanieyan $
 * @link       http://www.anahitapolis.com
 */

/**
 * JRouter application. Temporary until merged with the KDispatcherRouter
 *
 * @category   Anahita
 * @package    Com_Application
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class JRouterSite extends KObject
{
    /**
     * cloneable url
     * 
     * @var KHttpUrl
     */
    private $__url;
    
    /** 
     * Constructor.
     *
     * @param array $config An optional KConfig object with configuration options.
     * 
     * @return void
     */ 
	public function __construct($config = array()) 
    {                
		parent::__construct();
        
        //always force the router to SEF        
        $this->_mode = JROUTER_MODE_SEF;
        
        $this->__url = KService::get('koowa:http.url');
	}

    /**
     * Return the router mode
     * 
     * @return int
     */
    public function getMode()
    {
        return $this->_mode;   
    }
    
    /**
     * Parses the URI
     * 
     * @param JURI $uri
     * 
     * @return void
     */
	public function parse($url)
	{
        // Get the path
        $uri  = clone $this->__url;
        //clean up the url
        //remove the index.php
        $url  = preg_replace('/index\/?.php/','',$url);
        $uri->setUrl($url);        
        $path = $uri->path;
        //remove the base
        $path = substr_replace($path, '', 0, strlen(JURI::base(true)));
        //remove trailing /
        $path = trim($path , '/');
                        
        $vars = new KConfig($uri->getQuery(true));
        
        //set the format
        if ( $uri->format ) {            
            $vars->append(array('format'=>$uri->format));
        }
        
        if ( empty($path) && count($vars) == 0 ) 
        {
            $menu = JSite::getMenu(true)->getDefault();
            if ( $menu ) {
                $vars->append(array('Itemid'=>$menu->id));
            }
        }
        
        $segments  = explode('/', $path);        
        $component = array_shift($segments);
        
        if ( !$component ) {
        	$component = 'com_frontpage';
        }
        
        $vars->append(array(
			'option' => 'com_'.str_replace('com_','',$component)
		));
                
        $vars->append($this->getComponentRouter($vars->option)->parse($segments));
        
        $vars->append(array('Itemid'=> null));
                
        return KConfig::unbox($vars);     
	}

    /**
     * Builds a SEF URL
     * 
     * @param string $url URL to build
     * 
     * @return void
     */
	function build($url)
	{                
        $uri = clone $this->__url;
        $uri->setUrl(str_replace('index.php','',$url));        
        $query = $uri->getQuery(true);
        if ( !isset($query['option']) ) {
            return $url;
        }
                        
        if ( isset($query['format']) ) {
            $uri->format = $query['format'];
            unset($query['format']);  
        }
        
        $router   = $this->getComponentRouter(str_replace('com_','', $query['option']));
        $parts    = $router->build($query);
                
        if ( empty($parts) ) {
        	$parts = array();
        }
        
        if ( isset($query['option']) ) {
        	array_unshift($parts, str_replace('com_','', $query['option']));
        	unset($query['option']);
        }
        
        //only add index.php is it's rewrite SEF
        array_unshift($parts,'index.php');
        
        $path  = implode('/', $parts);

        //lets set the query stuff
        $uri->setQuery($query);        
        $uri->setPath(JURI::base(true).'/'.$path);

		return (string)$uri;
	}

    /**
     * Returna component router
     * 
     * @param string $component Component name 
     * 
     * @return ComBaseRouter
     */
    public function getComponentRouter($component)
    {
        $identifier = 'com://site/'.str_replace('com_','',$component).'.router';        
        register_default(array('identifier'=>$identifier, 'default'=>'ComBaseRouterDefault'));
        $router = KService::get($identifier);
        return $router;
    }
}
