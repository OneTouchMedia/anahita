<?php
/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id: view.php 13650 2012-04-11 08:56:41Z asanieyan $
 * @link       http://www.anahitapolis.com
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.helper');

/**
* Joomla! Application class
*
* Provide many supporting API functions
*
* @package		Joomla
* @final
*/
class JSite extends JApplication
{
    /**
     * Template
     * 
     * @var string
     */
    protected $_template;
    
    /** 
     * Constructor.
     *
     * @param array $config An optional KConfig object with configuration options.
     * 
     * @return void
     */
	public function __construct($config = array())
	{
		$config['clientId'] = 0;
		parent::__construct($config);
	}

	/**
	* Initialise the application.
	*
	* @param array $options Initialization options
    * 
    * @return void
	*/
	public function initialise( $options = array())
	{
		// if a language was specified it has priority
		// otherwise use user or default language settings
		if (empty($options['language']))
		{
			$user = & JFactory::getUser();
			$lang	= $user->getParam( 'language' );

			// Make sure that the user's language exists
			if ( $lang && JLanguage::exists($lang) ) {
				$options['language'] = $lang;
			} else {
				$params =  JComponentHelper::getParams('com_languages');
				$client	=& JApplicationHelper::getClientInfo($this->getClientId());
				$options['language'] = $params->get($client->name, 'en-GB');
			}

		}

		// One last check to make sure we have something
		if ( ! JLanguage::exists($options['language']) ) {
			$options['language'] = 'en-GB';
		}

		parent::initialise($options);
	}

    /**
    * Route the application
    *
    * @param array $options Initialization options
    * 
    * @return void
    */
	public function route() 
    {
		parent::route();
	}

    /**
    * Dispatches the application
    *
    * @param string $component Component name
    * 
    * @return void
    */
	function dispatch($component)
	{
        $component = JComponentHelper::renderComponent($component);
        JFactory::getDocument()->setBuffer($component, 'component');
        return;
        die;
        print JURI::current();
        die;
        print $component;
        die;
        
		$document	=& JFactory::getDocument();
		$user		=& JFactory::getUser();
		$router     =& $this->getRouter();
		$params     =& $this->getParams();
        
		switch($document->getType())
		{
			case 'html':
			{
				//set metadata
				$document->setMetaData( 'keywords', $this->getCfg('MetaKeys') );

				if ( $user->get('id') ) {
					$document->addScript( JURI::root(true).'/includes/js/joomla.javascript.js');
				}

				if($router->getMode() == JROUTER_MODE_SEF) {
					$document->setBase(JURI::current());
				}
			} break;

			case 'feed':
			{
				$document->setBase(JURI::current());
			} break;

			default: break;
		}


		$document->setTitle( $params->get('page_title') );
		$document->setDescription( $params->get('page_description') );
		
		$document->setBuffer( $contents, 'component');
	}

    /**
    * Render the content into the response
    * 
    * @return void
    */
	function render()
	{        
        $component = JFactory::getDocument()->getBuffer('component');
        
        $data = $this->getTemplate(true)
            ->layout( KRequest::get('get.tmpl','cmd','default') )
            ->setOutput($component)
            ->display();
            
        JResponse::setBody($data);
        return;            
        die;
        
        $template   = KRequest::get('get.tmpl','cmd','default');
        $theme      = KService::get('tmpl://site/'.$this->getTemplate().'.dispatcher'); 
        
        die;
        $identifier = 'tmpl://site/'.$params['template'].'.dispatcher';
        $body = 's';
        JResponse::setBody($body);        
        return;
		$document =& JFactory::getDocument();
		$user     =& JFactory::getUser();

		// get the format to render
		$format = $document->getType();

		switch($format)
		{
			case 'feed' :
			{
				$params = array();
			} break;

			case 'html' :
			default     :
			{
				$template	= $this->getTemplate();
				$file 		= JRequest::getCmd('tmpl', 'index');

				if ($this->getCfg('offline') && $user->get('gid') < '23' ) {
					$file = 'offline';
				}
				if (!is_dir( JPATH_THEMES.DS.$template ) && !$this->getCfg('offline')) {
					$file = 'component';
				}
				$params = array(
					'template' 	=> $template,
					'file'		=> $file.'.php',
					'directory'	=> JPATH_THEMES
				);
			} break;
 		}

		$data = $document->render( $this->getCfg('caching'), $params);
		JResponse::setBody($data);
	}

   /**
	* Login authentication function
	*
	* @param array $credentials Array( 'username' => string, 'password' => string )
	* @param array $options     Array( 'remember' => boolean )
	* 
	* @see JApplication::login
	*/
	public function login($credentials, $options = array())
	{
		 //Set the application login entry point
		 if(!array_key_exists('entry_url', $options)) {
			 $options['entry_url'] = JURI::base().'index.php?option=com_user&task=login';
		 }

		return parent::login($credentials, $options);
	}

	/**
	* Check if the user can access the application
	*
	* @param int $itemid The menu itemid
    * 
    * @return boolean
	*/
	public function authorize($itemid)
	{
		$menus	=& JSite::getMenu();
		$user	=& JFactory::getUser();
		$aid	= $user->get('aid');

		if(!$menus->authorize($itemid, $aid))
		{
			if ( ! $aid )
			{
				// Redirect to login
				$uri		= JFactory::getURI();
				$return		= $uri->toString();

				$url  = 'index.php?option=com_user&view=login';
				$url .= '&return='.base64_encode($return);;

				//$url	= JRoute::_($url, false);
				$this->redirect($url, JText::_('You must login first') );
			}
			else
			{
				JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
			}
		}
	}

	/**
	 * Get the appliaction parameters
	 *
	 * @param	string	The component option
	 * @return	object	The parameters object
	 * @since	1.5
	 */
	public function &getParams($option = null)
	{
		static $params = array();
		$hash = '__default';
		if(!empty($option)) $hash = $option;
		if (!isset($params[$hash]))
		{
			// Get component parameters
			if (!$option) {
				$option = JRequest::getCmd('option');
			}
			$params[$hash] =& JComponentHelper::getParams($option);

			// Get menu parameters
			$menus	=& JSite::getMenu();
			$menu	= $menus->getActive();

			$title       = htmlspecialchars_decode($this->getCfg('sitename' ));
			$description = $this->getCfg('MetaDesc');

			// Lets cascade the parameters if we have menu item parameters
			if (is_object($menu))
			{
				$params[$hash]->merge(new JParameter($menu->params));
				$title = $menu->name;

			}

			$params[$hash]->def( 'page_title'      , $title );
			$params[$hash]->def( 'page_description', $description );
		}

		return $params[$hash];
	}

	/**
	 * Get the template
	 * 
     * @param boolean $object Flag to whether to return a template object 
     * 
	 * @return string The template name
	 */
	public function getTemplate($object = false)
	{
        if ( !isset($this->_template) ) 
        {
            //get the template
            $template = KService::get('repos:templates.menu', array(
                    'resources'         => 'templates_menu',
                    'identity_property' => 'menuid'
                ))->getQuery()->clientId(0)->fetchValue('template');
            
            $this->setTemplate(pick($template, 'base')); 
        }
        
        return $object ? $this->_template : $this->_template->getIdentifier()->package;
	}

	/**
	 * Overrides the default template that would be used
	 *
	 * @param string $template The template name
     * 
     * @return void
	 */
	public function setTemplate( $template )
	{
        if ( !$template instanceof LibThemeDispatcherAbstract ) 
        {
            if ( is_string($template) && strpos($template,'.') === false ) {            
                $template = 'tmpl://site/'.$template.'.theme';
            }
            
            $template = KService::get($template);
        }
        
		$this->_template = $template;
	}

	/**
	 * Return a reference to the JPathway object.
	 *
	 * @return JMenu
	 */
	public function &getMenu()
	{
		$options = array();
		$menu =& parent::getMenu('site', $options);
		return $menu;
	}

	/**
	 * Return a reference to the JPathway object
     * 
	 * @return JPathway
	 */
	public function &getPathWay()
	{
		$options = array();
		$pathway =& parent::getPathway('site', $options);
		return $pathway;
	}

	/**
	 * Return a reference to the JRouter object.
	 *
	 * @return	JRouter
	 */
	public function &getRouter()
	{
		$config =& JFactory::getConfig();
		$options['mode'] = $config->getValue('config.sef');
		$router =& parent::getRouter('site', $options);
		return $router;
	}
}
