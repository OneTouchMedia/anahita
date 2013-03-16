<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Com_Base
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
 * @package    Com_Base
 * @subpackage Controller_Behavior
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class ComBaseControllerBehaviorMailer extends KControllerBehaviorAbstract
{
	/**
	 * Email View
	 * 
	 * @var ComBaseEmailView
	 */
	protected $_view;
	
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
	
		));
	
		parent::_initialize($config);
	}
	
	/**
	 * Return the email view
	 * 
	 * @return ComBaseEmailView
	 */
	public function getEmailView()
	{
		if ( !isset($this->_view) ) 
		{
			$identifier = clone $this->getIdentifier();
			$identifier->path = array('email');
			$identifier->name = 'view';
			register_default(array('identifier'=>$identifier, 'default'=>array('ComBaseEmailView')));
			$this->_view = $this->getService($identifier,array('base_url'=>KRequest::url()));
		}
		return $this->_view;
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
		$output = $this->getEmailView()
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