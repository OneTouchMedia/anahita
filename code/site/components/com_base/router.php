<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Com_Base
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
 * @package    Com_Base
 * @subpackage Dispatcher
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class ComBaseRouter extends KObject implements KServiceInstantiatable
{
    /**
     * Force creation of a singleton
     *
     * @param   object  An optional KConfig object with configuration options
     * @param   object  A KServiceInterface object
     * @return KDispatcherDefault
     */
    public static function getInstance(KConfigInterface $config, KServiceInterface $container)
    { 
       // Check if an instance with this identifier already exists or not
        if (!$container->has($config->service_identifier))
        {
            //Create the singleton
            $classname = $config->service_identifier->classname;
            $instance  = new $classname($config);
            $container->set($config->service_identifier, $instance);
        }
        
        return $container->get($config->service_identifier);
    }
    
    /**
     * Route patterns
     * 
     * @var array
     */
    protected $_patterns;
    
    /** 
     * Constructor.
     *
     * @param KConfig $config An optional KConfig object with configuration options.
     * 
     * @return void
     */ 
    public function __construct(KConfig $config)
    {
        parent::__construct($config);
        
        $this->_patterns = $config['patterns'];
    }
        
    /**
    * Initializes the default configuration for the object
    *
    * Called from {@link __construct()} as a first step of object instantiation.
    *
    * @param KConfig $config An optional KConfig object with configuration options.
    *
    * @return void
    */
    protected function _initialize(KConfig $config)
    {
        $package = $this->getIdentifier()->package;
        
        $config->append(array(
            'patterns' => array(
                ''          => array('view'=>$package)                             
            )
        ));   

        parent::_initialize($config);
    }
        
    
    /**
     * Build the route
     *
     * @param   array   An array of URL arguments
     * @return  array   The URL arguments to use to assemble the subsequent URL.
     */
    public function build(&$query)
    {
        $segments = array();
        
        if ( isset($query['view']) ) {
            //prevent duplicate name
//            if ( $query['view'] != $this->getIdentifier()->package )
            $segments[] = $query['view'];
            unset($query['view']);
        }
        
        if ( isset($query['id']) && !is_array($query['id']) ) {
            $segments[] = $query['id'];
            unset($query['id']);
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
        
        if ( empty($segments) ) {
            $vars['view'] = $this->getIdentifier()->package;
        }
        elseif ( !is_numeric(current($segments)) ) {
            $vars['view'] = array_shift($segments);
            if ( current($segments) ) {
                $vars['id'] = array_shift($segments);
            }
        }
        
        return $vars;
    }   
    
}