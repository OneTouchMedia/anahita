<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Com_Mailer
 * @subpackage Controller_Behavior
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2011 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id: resource.php 11985 2012-01-12 10:53:20Z asanieyan $
 * @link       http://www.anahitapolis.com
 */

/**
 * Mailer Behavior can be used to send emails using a template
 *
 * @category   Anahita
 * @package    Com_Mailer
 * @subpackage Controller_Behavior
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class ComMailerControllerBehaviorMailer extends KControllerBehaviorAbstract
{
	/**
	 * Email View
	 * 
	 * @var ComMailerEmailView
	 */
	protected $_template_view;
	
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
		
		$this->_template_view = $config->template_view;
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
	        'template_view' => 'com://site/mailer.view.template'
		));
	
		parent::_initialize($config);
	}
	
	/**
	 * Return the email view
	 * 
	 * @return ComMailerViewTemplate
	 */
	public function getEmailTemplateView()
	{
	    if ( !$this->_template_view instanceof ComMailerViewTemplate ) 
	    {
	        $identifier = clone $this->_mixer->getIdentifier();
	        $identifier->path = array('emails');

	        $paths[] = dirname($identifier->filepath);
	        $paths[] = implode(DS, array(JPATH_THEMES, JFactory::getApplication()->getTemplate(), 'emails', $identifier->type.'_'.$identifier->package));
	        $paths[] = implode(DS, array(JPATH_THEMES, JFactory::getApplication()->getTemplate(), 'emails')); 	        
	        $config = array(
                'base_url'          => KRequest::url(),
	            'template_paths'    => $paths
	        );	
     
	        $this->_template_view = $this->getService($this->_template_view, $config);    
	    }
	    
	    return $this->_template_view;		
	}
	
	/**
	 * Send an email
	 * 
	 * @param array $config An array of config
	 * 				'to' => array of recipients
	 * 				'template' => name of the email template to use
	 * 				'data'	   => array of data
	 * 				'subject'  => the mail subject 
	 * 
	 * @return void
	 */
	public function mail($config = array())
	{
		$config = new KConfig($config);
		
		$data   = $this->getState()->toArray();
		
		if ( $this->getState()->getItem() ) {
			$data[$this->_mixer->getIdentifier()->name] = $this->getState()->getItem(); 
		}

		if ( $this->getState()->getList() ) {
			$data[KInflector::pluralize($this->_mixer->getIdentifier()->name)] = $this->getState()->getList();
		}
		
		$config->append(array(
			'data' => $data	
		));
					
		$emails	= (array)$config['to'];
		$output = $this->getEmailTemplateView()
					->layout($config->template)
					->config($config)
					->display($config['data'])
			;
		//@TODO what the hell is this. use template filter 
		//also what if the mailer is not HTML ??
		$output = nl2br($output);	
		$mailer = JFactory::getMailer();
		$mailer->setSubject($config->subject);
		$mailer->setBody($output);
		$mailer->isHTML(true);
		$mailer->addRecipient($emails);
		$mailer->Send();
	}
}