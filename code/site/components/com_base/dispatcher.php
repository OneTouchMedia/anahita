<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Com_Base
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id: resource.php 11985 2012-01-12 10:53:20Z asanieyan $
 * @link       http://www.anahitapolis.com
 */

/**
 * Default Base Dispatcher
 *
 * @category   Anahita
 * @package    Com_Base
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class ComBaseDispatcher extends LibBaseDispatcherDefault
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
		
		if ( $config->auto_asset_import  )
			$this->registerCallback('after.render', array($this, 'importAsset'));
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
	        'auto_asset_import' => true
	    ));
	    
	    $config->auto_asset_import = $config->auto_asset_import && (KRequest::method() == 'GET' && KRequest::type() == 'HTTP');
	
	    parent::_initialize($config);
        
        if ( $config->request->view ) {
            $config->controller = $config->request->view;    
        }
	}	
		
    /**
     * {@inheritdoc}
     * 
     * Guess the controller name based on the entity type
     */
    public function setController($controller)
    {
        parent::setController($controller);
            
        if ( !$this->_controller instanceof KControllerAbstract ) 
        {
            $resource = clone $this->_controller;
            $resource->path = array('domain','entity');
            try 
            {
                $repository = AnDomain::getRepository($resource);
                $entity     = $repository->getClone();
                $default    = array('prefix'=>$entity, 'fallback'=>'ComBaseControllerService');                         
            } 
            catch(Exception $e)
            {
                $default = 'Com'.ucfirst($this->getIdentifier()->package).'ControllerDefault';
                $default = array('default'=>array($default, 'ComBaseControllerResource'));               
            }
            
            $default['identifier'] = $this->_controller;
            register_default($default);
        }
    }

  	/**
  	 * Dispatch Action
  	 * 
  	 * @param KCommandContext $context Context parameter
     * 
     * @return mixed
     */
    protected function _actionDispatch(KCommandContext $context)
    {
    	//if the viewer is not logged in then 
    	//redirect to the loggin page
    	try 
    	{
    		$result = parent::_actionDispatch($context);
    	} 
        catch(KException $exception) 
    	{
	    	$context = $this->getCommandContext();
            $context['exception'] = $exception;
            $result = $this->execute('dispatcherexception', $context);
    	}
  
    	return $result;
    }    
    
    /**
     * Called after an exception has been thrown in the action dispatch 
     * 
     * @param KCommandContext $context ['exception'=>KException]
     * 
     * @return boolean
     */
    protected function _actionDispatcherexception(KCommandContext $context)
    {       
        $exception = $context['exception'];
        
        $viewer = get_viewer();
        
        //if format html then redirect to login
        if ( $this->format == 'html' && $viewer->guest() && $exception->getCode() <= KHttpResponse::METHOD_NOT_ALLOWED  ) 
        {
            if ( KRequest::type() == 'HTTP' ) 
            {
                $login_url   = 'index.php?option=com_user&view=login';
                $return_url  = KRequest::method() == 'GET' ? KRequest::url() : KRequest::referrer();                        
                $login_url  .= '&return='.base64_encode($return_url);
                $message = JText::_('LIB-AN-PLEASE-LOGIN-TO-SEE');
                JFactory::getApplication()->redirect($login_url, $message);
                return false;
            }
        }
        
        throw $exception;        
    }
    
  	/**
  	 * Forward Action
  	 * 
  	 * Forwards a HTTP request
  	 * 
  	 * @param KCommandContext $context Context parameter
     * 
     * @return mixed
     */
	public function _actionForward(KCommandContext $context)
	{
        //If content is reset then return the content of the controller
        if ( ($this->format == 'json' || KRequest::type() == 'AJAX' )
            && ( $context->status == KHttpResponse::RESET_CONTENT ||
                 $context->status == KHttpResponse::CREATED)
        ) 
        {
            if (  $this->getController()->isIdentifiable() &&
                  $context->result instanceof KObject)
            {
                $context->result = $this->getController()->setItem($context->result)
                    ->execute('get', $context);
                ;
            }
        }
        
		return parent::_actionForward($context);
	}
	
  	/**
  	 * Import component assets automatically 
  	 * 
  	 * This method automatically imports the js/css assets of the app
  	 * 
     * @return mixed
     */
	public function importAsset()
	{
	    $asset = $this->getService('com://site/base.template.asset');
	    
		$url = $asset->getURL("com_{$this->getIdentifier()->package}/js/{$this->getIdentifier()->package}.js");
		
		if ( $url )
			JFactory::getDocument()->addScript($url);
			
		$url = $asset->getURL("com_{$this->getIdentifier()->package}/css/{$this->getIdentifier()->package}.css");
								
		if ( $url )
			JFactory::getDocument()->addStyleSheet($url);
	}
    
    /**
     * Renders a controller view
     * 
     * @return string
     */
    protected function _actionRender(KCommandContext $context)
    {
        $view  = $this->getController()->getView();
        
        header('Content-Type: '.$view->mimetype);
                       
        if ( $this->format == 'html' && KRequest::type() == 'HTTP' ) {
            $this->_setPageTitle();
        }
               
        return parent::_actionRender($context);   
    }
    
    /**
     * Sets the page title/description
     * 
     * @return void
     */
    protected function _setPageTitle()
    {
        $view     = $this->getController()->getView();
        $document = JFactory::getDocument();
        
        $item     = $this->getController()->getState()->getItem();
        $actorbar = $this->getController()->actorbar;
        
        $title = array();
        $description = null;                               
        
        if ( $actorbar && $actorbar->getActor() ) 
        {
            if ( $actorbar->getTitle() )
                $title[] = $actorbar->getTitle();
                
            $description = $actorbar->getDescription();      
        }
        else {
            $title[] = ucfirst($view->getName());   
        }
        
        if ( $item && $item->isDescribable() ) {
            array_unshift($title, $item->name);
            $description = $item->body;
        }
     
        $title = implode(' - ', array_unique($title));      
        $document->setTitle($title);
        $description = preg_replace( '/\s+/', ' ', $description );
        $description = htmlspecialchars($view->getTemplate()->renderHelper('text.truncate', $description, array('length'=>160)));          
        $document->setDescription($description);         
    }
}