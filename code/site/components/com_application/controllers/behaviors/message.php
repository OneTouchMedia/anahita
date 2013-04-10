<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Com_Application
 * @subpackage Controller_Behavior
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id: resource.php 11985 2012-01-12 10:53:20Z asanieyan $
 * @link       http://www.anahitapolis.com
 */

/**
 * 
 *
 * @category   Anahita
 * @package    Com_Application
 * @subpackage Controller_Behavior
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class ComApplicationControllerBehaviorMessage extends KControllerBehaviorAbstract
{
    /**
     * Flash
     * 
     * @var array
     */
    protected $_flash;
    
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
        
        if ( KRequest::has('session.flash') )
        {
            $this->_flash = $_SESSION['flash'];
        } else {
            $this->_flash = new KConfig();
        }
                
        unset($_SESSION['flash']);
        $config->mixer->getState()->flash = $this->_flash;
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
    
        ));
    
        parent::_initialize($config);
    }
    
    /**
     * (non-PHPdoc)
     * @see KControllerBehaviorAbstract::execute()
     */
    public function execute( $name, KCommandContext $context) 
	{
	    //$context->headers['X-FLASH'] = (string)$this->_flash;
	    return parent::execute($name, $context);
	}
    
    /**
     * Insert into flash
     * 
     * @param string  $key    The key
     * @param mixed   $value  The value
     * @param boolean $gobal  Glboal flag
     * 
     * @return void
     */
    public function setFlash($key, $value, $global = false)
    {
        $this->_flash[$key] = $value;
        KRequest::set('session.flash', $this->_flash);
        return $this->_mixer;
    }

    /**
     * Return the object handle
     *
     * @return string
     */
    public function getHandle()
    {
        return KMixinAbstract::getHandle();
    }
}