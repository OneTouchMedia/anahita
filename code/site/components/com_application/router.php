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
class JRouterSite extends JRouter
{
    /**
     * SEF component prefix
     */
    static public $SEF_COM_PREFIX = 'component/';
    
    /** 
     * Constructor.
     *
     * @param array $config An optional KConfig object with configuration options.
     * 
     * @return void
     */ 
	public function __construct($config = array()) 
    {        
		parent::__construct($config);
        
        //always force the router to SEF        
        $this->_mode = JROUTER_MODE_SEF;
	}

    /**
     * Parses the URI
     * 
     * @param JURI $uri
     * 
     * @return void
     */
	public function parse(&$uri)
	{
		$vars = array();
    
		// Get the application
		$app =& JFactory::getApplication();

        //force ssl. must be moved to dispatcher
		if($app->getCfg('force_ssl') == 2 && strtolower($uri->getScheme()) != 'https') 
        {
			//forward to https
			$uri->setScheme('https');
			$app->redirect($uri->toString());
		}


		// Get the path
		$path = $uri->getPath();
        
		//Remove the suffix
		if($this->_mode == JROUTER_MODE_SEF)
		{
            //extract the format from the path
            if ( !(substr($path, -9) == 'index.php' || substr($path, -1) == '/') ) {
               $suffix = pathinfo($path, PATHINFO_EXTENSION);
               if ( $suffix ) {
                    $path = str_replace('.'.$suffix, '', $path);
                    $vars['format'] = $suffix;
               }
            }
		}

		//Remove basepath
		$path = substr_replace($path, '', 0, strlen(JURI::base(true)));
        
		//Remove prefix
		$path = str_replace('index.php', '', $path);
        
		//Set the route
		$uri->setPath(trim($path , '/'));

		$vars += parent::parse($uri);

		return $vars;
	}

    /**
     * Builds a SEF URL
     * 
     * @param string $url URL to build
     * 
     * @return void
     */
	function &build($url)
	{
		$uri =& parent::build($url);

		// Get the path data
		$route = $uri->getPath();

		//Add the suffix to the uri
		if($this->_mode == JROUTER_MODE_SEF && $route)
		{
			$app =& JFactory::getApplication();

			if($app->getCfg('sef_rewrite'))
			{
				//Transform the route
				$route = str_replace('index.php/', '', $route);
			}
		}

		//Add basepath to the uri
		$uri->setPath(JURI::base(true).'/'.$route);

		return $uri;
	}

    /**
     * Called by router to parse a raw uri
     * 
     * @param JURI $uri
     * 
     * @return void
     */
	public function _parseRawRoute(&$uri)
	{
		$vars   = array();

		$menu =& JSite::getMenu(true);

		//Handle an empty URL (special case)
		if(!$uri->getVar('Itemid') && !$uri->getVar('option'))
		{
			$item = $menu->getDefault();
			if(!is_object($item)) return $vars; // No default item set

			//Set the information in the request
			$vars = $item->query;

			//Get the itemid
			$vars['Itemid'] = $item->id;

			// Set the active menu item
			$menu->setActive($vars['Itemid']);

			return $vars;
		}

		//Get the variables from the uri
		$this->setVars($uri->getQuery(true));

		//Get the itemid, if it hasn't been set force it to null
		$this->setVar('Itemid', JRequest::getInt('Itemid', null));

		//Only an Itemid ? Get the full information from the itemid
		if(count($this->getVars()) == 1)
		{
			$item = $menu->getItem($this->getVar('Itemid'));
			if($item !== NULL && is_array($item->query)) {
				$vars = $vars + $item->query;
			}
		}

		// Set the active menu item
		$menu->setActive($this->getVar('Itemid'));

		return $vars;
	}

    /**
     * Called by router to parse a sef uri
     * 
     * @param JURI $uri
     * 
     * @return void
     */
	public function _parseSefRoute(&$uri)
	{
		$vars   = array();
        
		$menu  =& JSite::getMenu(true);
		$route = $uri->getPath();

		//Get the variables from the uri
		$vars = $uri->getQuery(true);
        
		//Handle an empty URL (special case)
		if(empty($route))
		{
			//If route is empty AND option is set in the query, assume it's non-sef url, and parse apropriately
			if(isset($vars['option']) || isset($vars['Itemid'])) {
				return $this->_parseRawRoute($uri);
			}

			$item = $menu->getDefault();

			//Set the information in the request
			$vars = $item->query;

			//Get the itemid
			$vars['Itemid'] = $item->id;

			// Set the active menu item
			$menu->setActive($vars['Itemid']);

			return $vars;
		}
        
        $prefix = substr($route, 0, strlen(self::$SEF_COM_PREFIX)) == self::$SEF_COM_PREFIX;
        $found  = false;
        
        //if not being routed through componnet
        //check if it matches any menu item
        if ( !$prefix ) 
        {
            //Need to reverse the array (highest sublevels first)
            $items = array_reverse($menu->getMenu());
            $found = false;
            foreach ($items as $item)
            {
                $lenght = strlen($item->route); //get the lenght of the route

                if($lenght > 0 && strpos($route.'/', $item->route.'/') === 0 && $item->type != 'menulink')
                {
                    $route   = substr($route, $lenght);
                    
                    $vars['Itemid'] = $item->id;
                    $vars['option'] = $item->component;
                    $found = true;
                    break;
                }
            }
            
            //if matches no menu item then route it through a 
            //component by setting the prefix to empty
            if ( !$found ) {
                $route = self::$SEF_COM_PREFIX.$route;
            }
        }
        
		/*
		 * Parse the application route
		 */        
		if(!$found)
		{
			$segments	= explode('/', $route);
			$route      = str_replace($segments[0].'/'.$segments[1], '', $route);
			$vars['option'] = 'com_'.$segments[1];
			$vars['Itemid'] = null;
		}
		
		// Set the active menu item
		if ( isset($vars['Itemid']) ) {
			$menu->setActive(  $vars['Itemid'] );
		}
        
		//Set the variables
		$this->setVars($vars);
        
		/*
		 * Parse the component route
		 */
		if( isset($this->_vars['option']) )
		{
			$segments = empty($route) ? array() : explode('/', ltrim($route,'/'));
            
            $router   = $this->_getComponentRouter($this->_vars['option']);
            
            if ( $router === false ) {
                $function =  substr($this->_vars['option'],4).'ParseRoute';
                $vars = $function($segments);
            } else {
                $vars = $router->parse($segments);
            }
            $this->setVars($vars);
		}
		else
		{
			//Set active menu item
			if($item =& $menu->getActive()) {
				$vars = $item->query;
			}
		}

		return $vars;
	}

    /**
     * Builds a SEF router from a URI
     * 
     * @param JURI $uri 
     * 
     * @return void
     */
	public function _buildSefRoute(&$uri)
	{
		// Get the route
		$route = $uri->getPath();
        
		// Get the query data
		$query = $uri->getQuery(true);

		if(!isset($query['option'])) {
			return;
		}

		$menu =& JSite::getMenu();

        $router = $this->_getComponentRouter($query['option']);
        
        if ( $router ) {
            $parts    = $router->build($query);
        } else {
            $function = substr($query['option'], 4).'BuildRoute';
            $parts    = $function($query);
        }
        
        $path   = implode('/', $parts);
        
        $path   = self::$SEF_COM_PREFIX.substr($query['option'], 4).'/'.$path;
		$route .= '/'.$path;
        
		// Unset unneeded query information
		unset($query['Itemid']);
		unset($query['option']);
        
        //if format is set then there already not
        //extenion (just in case) then add the format to the path
        if ( isset($query['format']) && !pathinfo($route, PATHINFO_EXTENSION) ) 
        {
            if ( $query['format'] != 'html' ) {
               $route .= '.'.$query['format'];
            }
            
            unset($query['format']);
        }
        
		//Set query again in the URI
		$uri->setQuery($query);
		$uri->setPath($route);
	}

    /**
     * Returna component router
     * 
     * @param string $component Component name 
     * 
     * @return ComBaseRouter
     */
    protected function _getComponentRouter($component)
    {
        $identifier = KService::getIdentifier('com://site/'.substr($component,4).'.router');
        $router     = false;            
        if ( file_exists($identifier->filepath) && !class_exists($identifier->classname) ) {
            require_once $identifier->filepath;            
        } else {
            register_default(array('identifier'=>$identifier, 'default'=>'ComBaseRouter'));
            $router = KService::get($identifier);   
        }
        
        return $router;
    }
    
    /**
     * Creates a JURI object from a string url
     * 
     * @param string $url String url
     * 
     * @return JURI return the created uri
     */
	public function &_createURI($url)
	{
		//Create the URI
		$uri =& parent::_createURI($url);

		// Set URI defaults
		$menu =& JSite::getMenu();

		// Get the itemid form the URI
		$itemid = $uri->getVar('Itemid');

		if(is_null($itemid))
		{
			if($option = $uri->getVar('option'))
			{
				$item  = $menu->getItem($this->getVar('Itemid'));
				if(isset($item) && $item->component == $option) {
					$uri->setVar('Itemid', $item->id);
				}
			}
			else
			{
				if($option = $this->getVar('option')) {
					$uri->setVar('option', $option);
				}

				if($itemid = $this->getVar('Itemid')) {
					$uri->setVar('Itemid', $itemid);
				}
			}
		}
		else
		{
			if(!$uri->getVar('option'))
			{
				$item  = $menu->getItem($itemid);
				$uri->setVar('option', $item->component);
			}
		}

		return $uri;
	}
}
