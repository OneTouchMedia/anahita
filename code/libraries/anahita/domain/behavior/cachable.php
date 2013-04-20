<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Anahita_Domain
 * @subpackage Behavior
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id$
 * @link       http://www.anahitapolis.com
 */

/**
 * Cachable Behavior 
 *
 * @category   Anahita
 * @package    Anahita_Domain
 * @subpackage Behavior
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class AnDomainBehaviorCachable extends AnDomainBehaviorAbstract
{           
    /**
     * The repository cache
     * 
     * @var ArrayObject
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
        
        $this->_cache  = $config->cache;     
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
            'priority'   => KCommand::PRIORITY_LOWEST,
            'cache'      => new ArrayObject()
        ));
    
        parent::_initialize($config);
    }
    
    /**
     * Check the object cache see if the data has already been retrieved
     * 
     * This cache is only persisted throughout a request 
     *
     * @param KCommandContext $context
     *
     * @return void
     */
    protected function _beforeRepositoryFetch(KCommandContext $context)
    {
        $key = $this->_getCacheKey($context->query);
        
        if ( $this->_cache->offsetExists($key) ) 
        {
            $context->data = $cache->offsetGet($key);
            return false;
        }
    }
    
    /**
     * Stores the objects in the cache. This cache is persisted during the
     * request life cycle
     *
     * @param KCommandContext $context
     *
     * @return void
     */
    protected function _afterRepositoryFetch(KCommandContext $context)
    {
        $key = $this->_getCacheKey($context->query);    
        $this->_cache->offsetSet($key, $context->data);
    }    
    
    /**
     * Clean and disable the cahce before insert
     *
     * @param KCommandContext $context
     *
     * @return void
     */
    protected function _beforeRepositoryInsert(KCommandContext $context)
    {
        $this->_cache->exchangeArray(array());
    }
    
    /**
     * Clean and disable the cahce before delete
     *
     * @param KCommandContext $context
     *
     * @return void
     */
    protected function _beforeRepositoryDelete(KCommandContext $context)
    {
        $this->_cache->exchangeArray(array());
    }    

    /**
     * Clean and disable the cahce before update
     *
     * @param KCommandContext $context
     *
     * @return void
     */
    protected function _beforeRepositoryUpdate(KCommandContext $context)
    {
        $this->_cache->exchangeArray(array());
    }      
    
    /**
     * Empty the cache
     * 
     * @param AnDomainQuery $query
     * 
     * @return void
     */
    public function emptyCache($query)
    {
    	$this->_cache->offsetSet($this->_getCacheKey($query), null);    	
    }
    
    /**
     * Returns a key to use for cache 
     * 
     * @param AnDomainQuery $query
     * 
     * @return string
     */
    protected function _getCacheKey($query)
    {
    	return (string)$query;
    }
    
    /**
     * Return the handle
     *
     * @return string
     */
    public function getHandle()
    {
         return KMixinAbstract::getHandle();   
    }
}
