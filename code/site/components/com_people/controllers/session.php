<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Com_People
 * @subpackage Controller
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id: resource.php 11985 2012-01-12 10:53:20Z asanieyan $
 * @link       http://www.anahitapolis.com
 */

/**
 * Session Controller. Manages a session of a person
 *
 * @category   Anahita
 * @package    Com_People
 * @subpackage Controller
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class ComPeopleControllerSession extends ComBaseControllerResource
{
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
        
        $this->_action_map['post'] = 'add';

        if  ( $this->isDispatched() ) {
        	$this->registerCallback('after.add', array($this, 'display'));
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
            //by default the format is json
            'request'   => array('format'=>'json')            
        ));

        parent::_initialize($config);
    }
            
    /**
     * Return the session
     * 
     * @param KCommandContext $context Command chain context 
     * 
     * @return void
     */
    protected function _actionGet(KCommandContext $context)
    {
    	$person = $this->getService('repos://site/people.person')->fetch(array('userId'=>JFactory::getUser()->id));
    	$this->_state->setItem($person);    	
    	return $this->getView()->display();
    }

    /**
     * Creates a new session
     * 
     * @param array   $user     The user as an array
     * @param boolean $remember Flag to whether remember the user or not
     * 
     * @return void
     */
    public function login(array $user, $remember = false)
    {		
		$session  = &JFactory::getSession();
    		
		// we fork the session to prevent session fixation issues
		$session->fork();   
		JFactory::getApplication()->_createSession($session->getId());
    		
    	// Import the user plugin group
		JPluginHelper::importPlugin('user');
    	$options = array();	    	
    	$results = JFactory::getApplication()->triggerEvent('onLoginUser', array($user, $options));
    	$failed  = false;
    		
		foreach($results as $result)
		{
			$failed = $result instanceof JException || $result instanceof Exception || $result === false;
			if ( $failed )
				break;
		}
    		
		if ( !$failed )
		{
			// Set the remember me cookie if enabled
			jimport('joomla.utilities.simplecrypt');
			jimport('joomla.utilities.utility');
    		
    			//if remeber is true or json api is being called
    			//return a cookie that contains the credential
			if ( $remember === true )
			{
    				//legacy for now
    				$key      = JUtility::getHash(KRequest::get('server.HTTP_USER_AGENT','raw'));
    				$crypt    = new JSimpleCrypt($key);
    				$cookie  = $crypt->encrypt(serialize($credentials));
    				$lifetime = time() + AnHelperDate::yearToSeconds();
    				setcookie(JUtility::getHash('JLOGIN_REMEMBER'), $cookie, $lifetime, '/');
			}
    			    			
			return true;
    		
		} else {
    		
			$user = $this->getService('repos://site/users.user')->fetch(array('username'=>$user['username']));
    		
			if ( $user && $user->block ) {
    				throw new KControllerException('User is blocked', KHttpResponse::FORBIDDEN);
			}
			else {
				throw new KControllerException('Unkown Error');
			}
			return false;
		}  
    	// Trigger onLoginFailure Event
    	JFactory::getApplication()->triggerEvent('onLoginFailure', array((array)$user));
    	throw new KControllerException('Authentication Failed. Check username/password', KHttpResponse::UNAUTHORIZED);
    	return false;
    }
    
    /**
     * Authenticate a person and create a new session If a username password is passed then the user is first logged in. 
     * 
     * @param KCommandContext $context Command chain context 
     * 
     * @return void
     * 
     * @throws KControllerException with KHttpResponse::UNAUTHORIZED code If authentication failed
     * @throws KControllerException with KHttpResponse::FORBIDDEN code If person is authenticated but forbidden
     * @throws KControllerException with KHttpResponse::INTERNAL_SERVER_ERROR code for unkown error
     */
    protected function _actionAdd(KCommandContext $context)
    {
        $data = $context->data;
                
        jimport('joomla.user.authentication');
       
        $authenticate = & JAuthentication::getInstance();
        $credentials  = KConfig::unbox($data);
        $options      = array();
        $authentication = $authenticate->authenticate($credentials, $options);
        if ( $authentication->status === JAUTHENTICATE_STATUS_SUCCESS )
        {
        	try
        	{
        		$this->login((array)$authentication);
        		$context->status = KHttpResponse::CREATED;
        	}
        	catch(Exception $e) {
        		$context->setError($e);
        		return false;
        	}
        } else {
        	JFactory::getApplication()->triggerEvent('onLoginFailure', array((array)$authentication));
        	$context->setError(new KControllerException('Authentication Failed. Check username/password', KHttpResponse::UNAUTHORIZED));
        	return false;        	
        }
    }
    
    /**
     * Deletes a session and logs out the user
     * 
     * @param KCommandContext $context Command chain context 
     * 
     * @return void
     */
    protected function _actionDelete(KCommandContext $context)
    {        
    	$context->status = KHttpResponse::RESET_CONTENT;
        //we don't care if a useris logged in or not just delete
        JFactory::getApplication()->logout();
    }    
}