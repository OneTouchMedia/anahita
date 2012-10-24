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
        
        $this->_action_map['post'] = 'authenticate';
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
            'readonly'  => false
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
        //if there's not a valid user then return unathorized
        if ( JFactory::getUser()->id == 0 ) {
             $context->status = KHttpResponse::UNAUTHORIZED;
        }
    }
    
    /**
     * Authenticate a person. If a username password is passed then the user is first logged in. 
     * If a person is not authenticated a KHttpResponse::UNAUTHORIZED is returned
     * 
     * @param KCommandContext $context Command chain context 
     * 
     * @return void
     */
    protected function _actionAuthenticate(KCommandContext $context)
    {
        $data     = $context->data;
        $filter   = $this->getService('koowa:filter.string');
        $username = $filter->sanitize($data->username);
        $password = $filter->sanitize($data->password);
        
        jimport( 'joomla.user.authentication');
       
        $authenticate = & JAuthentication::getInstance();
        $credentials  = array('username'=>$username,'password'=>$password);
        $options      = array();
        $response     = $authenticate->authenticate($credentials, $options);
        if ($response->status === JAUTHENTICATE_STATUS_SUCCESS)
        {
            $response = (array)$response;
            $session  = &JFactory::getSession();

            // we fork the session to prevent session fixation issues
            $session->fork();
            
            //legacy
            JFactory::getApplication()->_createSession($session->getId());
            
            // Import the user plugin group
            JPluginHelper::importPlugin('user');
            
            // OK, the credentials are authenticated.  Lets fire the onLogin event
            $results = JFactory::getApplication()->triggerEvent('onLoginUser', array($response, $options));
            if (!in_array(false, $results, true))
            {
                // Set the remember me cookie if enabled
                jimport('joomla.utilities.simplecrypt');
                jimport('joomla.utilities.utility');
                
                //if remeber is true or json api is being called
                //return a cookie that contains the credential        
                if ( $data->remember === true || $this->format == 'json'  )
                {
                    //legacy for now
                    $key = JUtility::getHash(@$_SERVER['HTTP_USER_AGENT']);  
                    $crypt   = new JSimpleCrypt($key);
                    $cookie  = $crypt->encrypt(serialize($credentials));
                    $lifetime = time() + AnHelperDate::yearToSeconds();
                    setcookie(JUtility::getHash('JLOGIN_REMEMBER'), $cookie, $lifetime, '/');
                }
                
                return true;
            }
        } 
         // Trigger onLoginFailure Event
        JFactory::getApplication()->triggerEvent('onLoginFailure', array((array)$response));
        $context->setError(new KControllerException('Authentication Failed. Check username or password', KHttpResponse::UNAUTHORIZED));
        return false;              
        
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
        //we don't care if a useris logged in or not just delete
        JFactory::getApplication()->logout();
    }    
}