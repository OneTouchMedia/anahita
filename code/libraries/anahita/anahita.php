<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id$
 * @link       http://www.anahitapolis.com
 */

define('ANAHITA', 1);

/**
 * Include Koowa
 */
require_once JPATH_LIBRARIES.'/koowa/koowa.php';
require_once JPATH_LIBRARIES.'/anahita/functions.php';

/**
 * Service Class
 * 
 * @category   Anahita
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class Anahita
{
    /**
     * Path to Anahita libraries
     * 
     * @var string
     */
    protected $_path;
        
	/**
     * Clone
     *
     * Prevent creating clones of this class
     */
    final private function __clone() { }

	/**
     * Singleton instance
     *
     * @param  array  An optional array with configuration options.
     * @return Koowa
     */
    final public static function getInstance($config = array())
    {
        static $instance;

        if ($instance === NULL) {
            $instance = new self($config);
        }

        return $instance;
    }
    
    /**
     * Constructor
     *
     * Prevent creating instances of this class by making the contructor private
     *
     * @param  array  An optional array with configuration options.
     */
    final private function __construct($config = array())
    {
        //store the path
        $this->_path = dirname(__FILE__);
                
        //instantiate koowa
        Koowa::getInstance(array(
            'cache_prefix'  => $config['cache_prefix'],
            'cache_enabled' => $config['cache_enabled']
        ));
                       
        //if caching is not enabled then reset the apc cache to
        //to prevent corrupt identifier        
        if ( !$config['cache_enabled'] ) {
            clean_apc_with_prefix($config['cache_prefix']);
        }
                
        require_once dirname(__FILE__).'/loader/adapter/anahita.php';
                        
        KLoader::addAdapter(new AnLoaderAdapterAnahita(array('basepath'=>dirname(__FILE__))));
        KLoader::addAdapter(new AnLoaderAdapterDefault(array('basepath'=>JPATH_LIBRARIES.'/default')));
        
        AnServiceClass::getInstance();
        
        KServiceIdentifier::addLocator(new AnServiceLocatorAnahita());
        KServiceIdentifier::addLocator(new AnServiceLocatorRepository());

        //register an empty path for the application
        //a workaround to remove the applicaiton path from an identifier
        KServiceIdentifier::setApplication('', '');
                
        //create a central event dispatcher           
        KService::set('anahita:event.dispatcher', KService::get('koowa:event.dispatcher'));
        
        //create an event command with central event dispatcher
        KService::set('anahita:command.event', 
                    KService::get('koowa:command.event', array('event_dispatcher'=>KService::get('anahita:event.dispatcher'))));
    }
    
    /**
     * Get the version of the Anahita library
     * 
     * @return string
     */
    public function getVersion()
    {
   	    $manifest = self::getManifest();
   	    
   	    if ( $manifest )
   	        return $manifest->getElementByPath('version')->data();
    }
    
    /**
     * Return whether the release is birth or not
     * 
     * @return boolean
     */
    public function isBirth()
    {
        $manifest = self::getManifest();
        
        if ( $manifest ) {
            return trim(strtolower($manifest->getElementByPath('release')->data())) == 'birth';
        }   
        
        return false;     
    }
    
    /**
     * Get path to Anahita libraries
     */
    public function getPath()
    {
        return $this->_path;
    }
    
    /**
     * Returns the Anahita Manifest
     *
     * @return SimpleXML
     */
    public static function getManifest()
    {
        static $_manifest;
    
        if ( !$_manifest )
        {
            $_manifest = new JSimpleXML();
            $_manifest->loadFile(JPATH_ROOT.DS.'manifest.xml');
            $_manifest = $_manifest->document;
        }
    
        return $_manifest;
    }    
}