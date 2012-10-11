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
        
        if (!function_exists('mysqli_connect')) 
        {
            JFactory::getApplication()->redirect(JURI::base().'templates/system/error_mysqli.html');
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
	 * onAfterInitialise handler
	 *
	 * Adds the mtupgrade folder to the list of directories to search for JHTML helpers.
	 * 
	 * @return null
	 */
	public function onAfterRoute()
	{
        return;
		$type 	= strtolower(KRequest::get('request.format', 'cmd', 'html'));
				
		$format = $type;
		
		if ( KRequest::type() == 'AJAX' ) {
		    $format = strtolower(KRequest::get('server.HTTP_X_REQUEST', 'cmd', 'raw'));
		}
		
		$document =& JFactory::getDocument();
		
		//if a document type is raw then convert it to HTML
		//and set the format to html
		if ( $format == 'raw' )
		{
		    $document = JDocument::getInstance('html');
		
		    //set the format to html
		    $format = 'html';
		    //set the tmpl to raw
		    JRequest::setVar('tmpl', 		'raw');
		}
		
		KRequest::set('get.format',		$format);
				
		//wrap a HTML document around a decorator
		if (JFactory::getDocument()->getType() == 'html')
		{
		    if ( $format == 'html' )
		    {
		        //set the document
		        $document = JFactory::getApplication()->isAdmin() ? 
		            JDocument::getInstance('html') : 
		            new AnDocumentDecorator($document); 
		    }		        
		    else {
		        $document = JDocument::getInstance('raw');
		    }
		}

        //set the error document to a decorated
        //document object
        if ( !JFactory::getApplication()->isAdmin() )
        {
            //set the error document
            $error =& JDocument::getInstance('error');
            $error = new AnDocumentDecorator($error);
        }
        
		$tag   = JFactory::getLanguage()->getTag();
		
		if ( JFactory::getApplication()->isAdmin() )
		{
		    JHTML::script('lib_koowa/js/koowa.js', 	   'media/');
		    JHTML::script('lib_anahita/js/anahita.js?lang='.$tag.'&token='.JUtility::getToken(), 'media/');
		    JHTML::script('lib_anahita/js/admin.js',   'media/');
		}
		else
		{
		    //JHTML::script('lib_anahita/js/min/bootstrap.js', 'media/');
		    //JHTML::script('lib_anahita/js/anahita.js?lang='.$tag.'&token='.JUtility::getToken().'&'.uniqid(), 'media/');
		    //JHTML::script('lib_anahita/js/site.js?'.uniqid(), 'media/');
		}
		
		if ( !JFactory::getApplication()->isAdmin() ) 
		{
    		KService::get('com://site/default.filter.string');
		}	    	
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