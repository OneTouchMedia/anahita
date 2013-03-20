<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Com_People
 * @subpackage Controller_Permission
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id: resource.php 11985 2012-01-12 10:53:20Z asanieyan $
 * @link       http://www.anahitapolis.com
 */

/**
 * People Permissions
 *
 * @category   Anahita
 * @package    Com_People
 * @subpackage Controller_Permission
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class ComPeopleControllerPermissionPerson extends ComActorsControllerPermissionDefault
{ 
    /**
     * Flag to see whather registration is open or not
     * 
     * @var boolean
     */
    protected $_can_register;
    
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
        
        $this->_can_register = $config->can_register;
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
            'can_register' => get_config_value('users.allowUserRegistration', true)
        ));
    
        parent::_initialize($config);
    }

    /**
     * if a token is passed in the reqeust, then it allows reading  
     * 
     * (non-PHPdoc)
     * @see ComActorsControllerPermissionAbstract::canRead()
     */
    public function canRead()
    {
        //if there's a 
        if ( $this->token ) 
        {
           $user = $this->getService('repos://site/users.user')->fetch(array('activation'=>$this->token));
           if ( $user ) 
           {
               $this->setItem($this->getRepository()->fetch(array('userId'=>$user->id)));               
               $user->activation = null;
               $user->save();
           }
        }
        
        return parent::canRead();
    }
    
    /**
     * Return whether the registration is open or not
     * 
     * @return boolean
     */
    public function canRegister()
    {
        return $this->_can_register;
    }
    
	/**
	 * Returns whether registration is possible or not. The user can not
	 * registered if it's already logged in or the configuration value
	 * 'users.allowUserRegistration' is set to no
	 *
	 * @return boolean Returns whether registration is possible or not.
	 */
	public function canAdd()
	{	    
		//if user is alrready logged in
		if ( JFactory::getUser()->id > 0 ) {
			return false;
		}
	
		return $this->canRegister();
	}	
}