<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Com_Application
 * @subpackage Controller 
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id: resource.php 11985 2012-01-12 10:53:20Z asanieyan $
 * @link       http://www.anahitapolis.com
 */

/**
 * Page Controller
 *
 * @category   Anahita
 * @package    Com_Application
 * @subpackage Controller
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class ComApplicationControllerDefault extends LibBaseControllerResource implements KServiceInstantiatable
{
    /**
     * Force creation of a singleton
     *
     * @param KConfigInterface  $config    An optional KConfig object with configuration options
     * @param KServiceInterface $container A KServiceInterface object
     *
     * @return KServiceInstantiatable
     */
    public static function getInstance(KConfigInterface $config, KServiceInterface $container)
    {
        if (!$container->has($config->service_identifier))
        {
            $classname = $config->service_identifier->classname;
            $instance  = new $classname($config);
            $container->set($config->service_identifier, $instance);
        }
    
        return $container->get($config->service_identifier);
    }
    
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
        
        if ( empty($config->template) ) {
            throw new KControllerException('theme option is required for '.__CLASS__);   
        }
        
        //load the langauge        
        JFactory::getLanguage()->load('tpl_'.$config->template);
 
        $params = '';
        
        if ( is_readable($file = JPATH_THEMES.'/'.$config->template.'/params.ini') ) {
            $params = file_get_contents($file);  
        }
        
        $params = new JParameter($params);
        
        $config->append(array(
            'params'    => $params->toArray()            
        ));
                          
        //set the view         
        $identifier = 'tmpl://site/'.$config->template.'.view.'.
                        $this->getIdentifier()->name.'.'.
                        $this->format;
                                
        $this->_view = $this->getService($identifier, $config->toArray());
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
            'template' => 'base'   
        ));   

        parent::_initialize($config);
    }
}