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
     * Disable
     * 
     * @var boolean
     */
    protected $_enable;
    
    /**
     * The repository cache
     * 
     * @var AnDomainRepositoryCache
     */
    protected $_cache;
    
    /**
     * Caches the data in an array
     *
     * @var array
     */    
    protected $_data;
    
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
        $this->_enable = $config->enable;
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
        	'enable'	 => true,
            'priority'   => KCommand::PRIORITY_LOWEST,
            'cache'      => new ArrayObject()
        ));
    
        parent::_initialize($config);
    }

    /**
     * @{inheritdoc}
     */
    public function getMixableMethods(KObject $mixer = null)
    {                        
        return parent::getMixableMethods($mixer);
    }
        
    /**
     * Command handler
     *
     * @param   string      The command name
     * @param   object      The command context
     * @return  boolean     Can return both true or false.
     */
    public function execute( $name, KCommandContext $context)
    {
        $operation = $context->operation;
        $parts     = explode('.', $name);
        $cache	   = $this->_cache;
        
        if ( !$this->_enable || !$operation )
        	return;

        if ( $operation & AnDomain::OPERATION_COMMIT )
        {
        	$this->_cache = new ArrayObject();	
        }
        
        elseif ( $operation & AnDomain::OPERATION_FETCH ) 
        {
        	$key = $this->_getCacheKey($context->query);
        	
        	if ( $parts[0] == 'before' )
        	{
        		if ( $cache->offsetExists($key) )
        		{
        			$context->data = $cache->offsetGet($key);
        			return false;
        		}
        	}
        	else
        	{
        		$cache->offsetSet($key, $context->data);
        	}
        }                
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
