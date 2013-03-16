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
     * Return true
     * 
     * @param KCommandContext $context
     * 
     * @return boolean
     */
    public function canExecute(KCommandContext $context)
    {
    	return true;
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
            'request'   => array('format'=>'json'),
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
        if ( JFactory::getUser()->id == 0 ) 
        {
        	if ( $this->format == 'html' ) {
        		//display the login page
        		return $this->getView()->display();
        	}
        	$context->status = KHttpResponse::UNAUTHORIZED;
        } else 
        {
            $person = $this->getService('repos://site/people.person')->fetch(array('userId'=>JFactory::getUser()->id));
            $this->_state->setItem(array('personId'=> $person->id ));
            if ( $this->format == 'html' ) {
            	$url = $this->setRedirect($person->getURL())->getRedirect()->url;
            	$this->getService('application')->redirect($url);
            }
            else {
            	return $this->getView()->display();  
            }
        }
    }
    
    /**
     * Authenticate a person. If a username password is passed then the user is first logged in. 
     * 
     * @param KCommandContext $context Command chain context 
     * 
     * @return void
     * 
     * @throws KControllerException with KHttpResponse::UNAUTHORIZED code If authentication failed
     * @throws KControllerException with KHttpResponse::FORBIDDEN code If person is authenticated but forbidden
     * @throws KControllerException with KHttpResponse::INTERNAL_SERVER_ERROR code for unkown error
     */
    protected function _actionAuthenticate(KCommandContext $context)
    {
        $data     = $context->data;
        
        if ( JFactory::getUser()->id > 0 ) {            
            return $this->display();    
        }
        jimport( 'joomla.user.authentication');
       
        $authenticate = & JAuthentication::getInstance();
        $credentials  = KConfig::unbox($data);
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
                if ( $data->remember === true || $this->format == 'json'  )
                {
                    //legacy for now
                    $key      = JUtility::getHash(KRequest::get('server.HTTP_USER_AGENT','raw'));
                    $crypt    = new JSimpleCrypt($key);
                    $cookie  = $crypt->encrypt(serialize($credentials));
                    $lifetime = time() + AnHelperDate::yearToSeconds();
                    setcookie(JUtility::getHash('JLOGIN_REMEMBER'), $cookie, $lifetime, '/');
                }
                if ( $this->isDispatched() ) {
                	return $this->display();
                }
                return true;
                
            } else {
                
                $user = $this->getService('repos://site/users.user')->fetch(array('username'=>$response['username']));
                
                if ( $user && $user->block ) {
                    $context->setError(new KControllerException('User is blocked', KHttpResponse::FORBIDDEN));                
                }
                else {                    
                    $context->setError(new KControllerException('Unkown Error'));
                }
                return false;
            }
        }
         // Trigger onLoginFailure Event
        JFactory::getApplication()->triggerEvent('onLoginFailure', array((array)$response));
        $context->setError(new KControllerException('Authentication Failed. Check username/password', KHttpResponse::UNAUTHORIZED));
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