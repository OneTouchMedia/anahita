<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Plugins
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id$
 * @link       http://www.anahitapolis.com
 */

jimport('joomla.plugin.plugin');

/**
 * Anahita System Plugin
 * 
 * @category   Anahita
 * @package    Plugins
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class PlgSystemAnahita extends JPlugin 
{
    /**
     * Remebers handling
     * 
     * @return void
     */
    public function onAfterInitialise()
    {
        global $mainframe;

        // No remember me for admin
        if ($mainframe->isAdmin()) {
            return;
        }

        //if alredy logged in then forget it
        if ( JFactory::getUser()->id)  {
            return;    
        }
        
        jimport('joomla.utilities.utility');
        
        $cookie = KRequest::get('cookie.'.JUtility::getHash('JLOGIN_REMEMBER'),'raw');
        
        if ( $cookie ) 
        {
            //first lets clear the cookie
            setcookie( JUtility::getHash('JLOGIN_REMEMBER'), false, time() - AnHelperDate::dayToSeconds(), '/' );
            
            //lets decrypt
            $key      = JUtility::getHash(KRequest::get('server.HTTP_USER_AGENT','raw'));
            $crypt    = new JSimpleCrypt($key);
            $cookie   = $crypt->decrypt($cookie);
            $data     = (array)@unserialize($cookie);
            
            //@TODO what happens when a user is blocked
            try {
                KService::get('com://site/people.controller.session')
                    ->authenticate($data);
            }
            
            catch(KControllerException $e) {
                //only throws exception if we are using JSON format
                //otherwise let the current app handle it 
                if ( KRequest::format() == 'json' && 
                    $e->getCode() == KHttpResponse::UNAUTHORIZED ) {
                    throw $e;
                }
            }
        }
    }
        
	/**
	 * Constructor
	 * 
	 * @param mixed $subject Dispatcher
	 * @param array $config  Array of configuration
     * 
     * @return void
	 */
	public function __construct($subject, $config = array())
	{	    
        // Command line fixes for Joomla
        if (PHP_SAPI === 'cli') 
        {
            if (!isset($_SERVER['HTTP_HOST'])) {
                $_SERVER['HTTP_HOST'] = '';
            }
            
            if (!isset($_SERVER['REQUEST_METHOD'])) {
                $_SERVER['REQUEST_METHOD'] = '';
            }
        }                
        
        // Check for suhosin
        if(in_array('suhosin', get_loaded_extensions()))
        {
            //Attempt setting the whitelist value
            @ini_set('suhosin.executor.include.whitelist', 'tmpl://, file://');

            //Checking if the whitelist is ok
            if(!@ini_get('suhosin.executor.include.whitelist') || strpos(@ini_get('suhosin.executor.include.whitelist'), 'tmpl://') === false)
            {
                JFactory::getApplication()->redirect(JURI::base().'templates/system/error_suhosin.html');
                return;
            }
        }
        
        //Safety Extender compatibility
        if(extension_loaded('safeex') && strpos('tmpl', ini_get('safeex.url_include_proto_whitelist')) === false)
        {
            $whitelist = ini_get('safeex.url_include_proto_whitelist');
            $whitelist = (strlen($whitelist) ? $whitelist . ',' : '') . 'tmpl';
            ini_set('safeex.url_include_proto_whitelist', $whitelist);
        }

        if ( !JFactory::getApplication()->getCfg('caching') ) 
        {
            //clear apc cache for module and components
            //@NOTE If apc is shared across multiple services
            //this causes the caceh to be cleared for all of them
            //since all of them starts with the same prefix. Needs to be fix
            clean_apc_with_prefix('cache_mod');
            clean_apc_with_prefix('cache_com');
            clean_apc_with_prefix('cache_plg');
            clean_apc_with_prefix('cache_system');
            clean_apc_with_prefix('cache__system');
        }
        
		KService::get('plg:storage.default');
        
        JFactory::getLanguage()->load('overwrite',   JPATH_ROOT);
		JFactory::getLanguage()->load('lib_anahita', JPATH_ROOT);
        
        parent::__construct($subject, $config);
	}
    
	/**
	 * store user method
	 *
	 * Method is called after user data is stored in the database
	 *
	 * @param 	array		holds the new user data
	 * @param 	boolean		true if a new user is stored
	 * @param	boolean		true if user was succesfully stored in the database
	 * @param	string		message
	 */
	public function onAfterStoreUser($user, $isnew, $succes, $msg)
	{
		global $mainframe;

		if( !$succes )
			return false;
        
        $person =   KService::get('repos://site/people.person')
                    ->getQuery()
                    ->disableChain()
                    ->userId($user['id'])
                    ->fetch();
                    ;
							
		if ( $person ) 
		{		    
			KService::get('com://site/people.helper.person')->synchronizeWithUser($person, JFactory::getUser($user['id']) );
			
		} else 
		{
			$person = KService::get('com://site/people.helper.person')->createFromUser( JFactory::getUser($user['id']) );
		}
		
		$person->save();
		
		return true;
	}	
    
	/**
	 * store user method
	 *
	 * Method is called before user data is deleted from the database
	 *
	 * @param 	array		holds the user data
	 */
	public function onBeforeDeleteUser($user)
	{							
		$person = 	KService::get('repos://site/people.person')
                    ->getQuery()
                    ->disableChain()
                    ->userId($user['id'])
					->fetch();
					;
		
		if(!$person)
			return;

		$apps = KService::get('repos://site/apps.app')->getQuery()->disableChain()->fetchSet();
		
		foreach($apps as $app) 
		{
		    KService::get('anahita:event.dispatcher')->addEventSubscriber($app->getDelegate());
		}
		
		$person->destroy();
	}
}