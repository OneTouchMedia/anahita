<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Anahita_Service
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id$
 * @link       http://www.anahitapolis.com
 */

/**
 * Wrapps JCache with ArrayAccess Interface
 * 
 * @category   Anahita
 * @package    Anahita_Service
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class AnCache extends KObject implements ArrayAccess 
{    
    /**
     * JCache object
     * 
     * @var JCache
     */
    protected $_cache;
    
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
        
        $conf =& JFactory::getConfig();
        
        $this->_cache = array();
        
        if ( $conf->getValue('config.caching') ) {
            $this->_cache = JFactory::getCache($config->prefix.'-'.$config->name, 'output');    
        }
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
        $config->append(array(
            'prefix' => 'system',
            'name'   => 'internal'
        ));

        parent::_initialize($config);
    }
            
    /**
     * Check if the offset exists
     *
     * Required by interface ArrayAccess
     *
     * @param   int   $offset
     * @return  bool
     */
    public function offsetExists($offset)
    {
        if ( is_array($this->_cache) ) {
            return isset($this->_cache[$offset]);
        }
        else {
            $result = $this->_cache->get($offset);
            return !empty($result);
        }
    }

    /**
     * Get an item from the cache by offset
     *
     * Required by interface ArrayAccess
     *
     * @param   int     $offset
     * @return  mixed The item from the array
     */
    public function offsetGet($offset)
    {
        if ( is_array($this->_cache) )
        {
            return $this->_cache[$offset];   
        }
        else
            return $this->_cache->get($offset);
    }

    /**
     * Set an item in the cache
     *
     * Required by interface ArrayAccess
     *
     * @param   int     $offset
     * @param   mixed   $value
     * @return  KObjectArray
     */
    public function offsetSet($offset, $value)
    {        
        if ( is_array($this->_cache) ) {
            $this->_cache[$offset] = $value;
        }
        else {                        
            $this->_cache->store($value, $offset);
        }
        return $this;
    }

    /**
     * Unset an item in the cache
     *
     * All numerical array keys will be modified to start counting from zero while
     * literal keys won't be touched.
     *
     * Required by interface ArrayAccess
     *
     * @param   int     $offset
     * @return  KObjectArray
     */
    public function offsetUnset($offset)
    {
        if ( is_array($this->_cache) ) 
        {
            unset($this->_cache[$offset]);    
        }
        else
            $this->_cache->store->remove($offset);
        return $this;
    }
}