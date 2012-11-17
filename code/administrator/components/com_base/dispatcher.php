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
 * Default App Dispatcher
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
                $default    = array('default'=>array('ComBaseController'.ucfirst($this->_controller->name),'ComBaseControllerResource'));			    
			}	
            
            $default['identifier'] = $this->_controller;
            register_default($default);
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
        parent::_initialize($config);
        
        //if there's a view then set the conroller to view        
        if ( $config->request->view ) {
            $config->controller = $config->request->view;    
        }
    }    

	/**
	 * Draw the toolbar
	 * 
	 * @param KCommandContext $context The command context
	 * 
	 * @return string
	 */
	protected function _actionRender(KCommandContext $context)
	{
		if ( $context->result !== false ) {
			
 			$view = $this->getController()->getView();
 			        
	        //Set the document mimetype
    	    JFactory::getDocument()->setMimeEncoding($view->mimetype);
        
	        //Disabled the application menubar
	        if(!KInflector::isPlural($view->getName()) && !KRequest::has('get.hidemainmenu')) {
	            KRequest::set('get.hidemainmenu', 1);
	        } 
		}
		
		return parent::_actionRender($context);
	}
}