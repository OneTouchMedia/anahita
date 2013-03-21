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
    private $_clonable_url;
    
    /**
     * If enabled then index.php is removed from the routes
     * 
     * @var boolean
     */
    protected $_enable_rewrite;
    
    /** 
     * Constructor.
     *
     * @param array $config An optional KConfig object with configuration options.
     * 
     * @return void
     */ 
	public function __construct($config = array()) 
    {
    	$config = new KConfig($config);
    	
    	$config->append(array(
    		'enable_rewrite' => false,
    		'url'	=> clone KService::get('koowa:http.url')	
    	));
    	
		parent::__construct($config);
		
		$this->_enable_rewrite = $config->enable_rewrite;
		
        $this->_clonable_url = $config->url;
	}

    /**
     * Return the router mode
     * 
     * @return int
     */
    public function getMode()
    {
        return JROUTER_MODE_SEF;
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
	    $path  = $url->path;
	    //bug in request
	    if ( $url->format == 'php') {
	        $path .= '.php';
	        $url->format = null;
	    }	    	    
	    $path  = substr_replace($path, '', 0, strlen(KRequest::base()));	    	    
	    $path  = str_replace('index.php', '', $path);
	    $path  = trim($path, '/');
	    $url->path   = $path;
	    $url->format = $url->format ? $url->format : pick(KRequest::format(), 'html');	    
	    if(!empty($url->format) ) {
	        $url->query['format'] = $url->format;
	    }
	    $segments   = explode('/', trim($url->path,'/'));
	    $segments   = array_filter($segments);	    	    	    
	    $component  = count($segments) ? array_shift($segments) : 'menu';
	    $query      = $this->getComponentRouter($component)->parse($segments);	   
	    $url->query = array_merge($url->query, array('option'=>'com_'.$component), $query);
	    //legacy reasons
	    if ( empty($url->query['Itemid']) ) {
	        $url->query['Itemid'] = null;
	    }
	    return true;	       
	}

    /**
     * Builds a SEF URL
     * 
     * @param string $url URL to build
     * 
     * @return void
     */
	function build($query = '')
	{
	    $query = str_replace('index.php?', '', $query);	    
        $uri = clone $this->_clonable_url;
        $uri->setQuery($query);
        $query = $uri->query;
        
        if ( isset($query['format']) ) {            
            $uri->format = $query['format'];                        
            unset($query['format']);
        }
        
        $parts = array();
        
        if ( isset($query['option']) ) {
            $router   = $this->getComponentRouter(str_replace('com_','', $query['option']));
            $parts    = pick($router->build($query), array());
        }
                
        if ( isset($query['option']) ) {
            array_unshift($parts, str_replace('com_','', $query['option']));
            unset($query['option']);
        }

        $uri->query = $query;
        
        //only add index.php is it's rewrite SEF
        if ( !$this->_enable_rewrite ) {
            array_unshift($parts,'index.php');
        }
        array_unshift($parts, KRequest::base()->path);        
        $path  = implode('/', $parts);
        $uri->path = $path;
        return $uri;        
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
