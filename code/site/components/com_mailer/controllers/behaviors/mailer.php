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
	 * Mailer test options
	 * 
	 * @var KConfig
	 */
	protected $_test_options;
	
	/**
	 * Base URL to use within the mails
	 * 
	 * @var KHttpUrl
	 */
	protected $_base_url;
	
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
		
		$this->_template_view  = $config->template_view;		
		
		$this->_base_url       = $config->base_url;
		
		$this->_test_options   = $config->test_options;
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
	    $identifier = clone $config->mixer->getIdentifier();
	    $identifier->path = array('emails');
	    $identifier->name = 'template';
	    
		$config->append(array(
		    'base_url'      => KRequest::base(),
		    'test_options'  => array(
		        'enabled'   => JDEBUG,
		        'email'     => get_config_value('mailer.redirect_email'),
		        'log'       => JFactory::getConfig()->getValue('tmp_path').'/emails.html'       
            ),		    
	        'template_view'  => $identifier
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
                'base_url'          => $this->_base_url,
	            'template_paths'    => $paths	            
	        );
	        register_default(array('identifier'=>$this->_template_view, 'default'=>'LibBaseViewTemplate'));
            $this->_template_view = $this->getService($this->_template_view, $config);
	    }
	    
	    return $this->_template_view;		
	}
	
	/**
	 * Retun the mail into a string
	 * 
	 * @return string
	 */
	public function renderMail($config = array())
	{
	    $config = new KConfig($config);
	    
	    $config->append(array(
            'layout'  => 'default_layout'	            
        ));
	    
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
	      
	    $template = $this->getEmailTemplateView()->getTemplate();
	    $data     = array_merge($config['data'], array('config'=>$config));	    
	    $output   = $template->loadTemplate($config->template, $data)->render();
        
        if ( $config->layout ) {
            $output = $template->loadTemplate($config->layout, array('output'=>$output))->render();            
        }
        
        //Supposed to fix the random exclamation points
        $output = wordwrap($output,900,"\n");

	    return $output;
	}
	
	/**
	 * Replaces to with the admin emails
	 * 
	 * @param array $config
	 * 
	 * @see ComMailerControllerBehaviorMaile::mail
	 */
	public function mailAdmins($config = array())
	{
	    $admins = $this->getService('repos://site/users.user')
	        ->fetchSet(array('usertype'=>'Super Administrator'));
	    
	    $config['to'] = $admins->email;
	    
	    return $this->mail($config);
	}
	
	/**
	 * Send an email
	 * 
	 * @param array $config An array of config
	 * 				'to' => array of recipients
	 * 				'template' => name of the email template to use
	 *              'layout'   => the email layout. It's set to default 
	 * 				'data'	   => array of data
	 * 				'subject'  => the mail subject 
	 * 
	 * @return void
	 */
	public function mail($config = array())
	{
	    $config = new KConfig($config);
	    $emails	= (array)$config['to'];
	    
	    if ( $this->_test_options->enabled ) {
	        $emails = $this->_test_options->email;
	    }
	    
		$output = $config->body ? $config->body : $this->renderMail($config);

		$config->append(array(		    
		    'is_html' => true        
        ));
		
		//@TODO what the hell is this. use template filter 
		//also what if the mailer is not HTML ??
		if ( !empty($emails) )
		{
		    $output = nl2br($output);
		    $mailer = JFactory::getMailer();
		    $mailer->setSubject($config->subject);
		    $mailer->setBody($output);
		    $mailer->isHTML($config->is_html);
		    $mailer->addRecipient($emails);
		    $mailer->Send();		    
		}
		
		if ( $this->_test_options->enabled && 
		        $this->_test_options->log ) {
		    file_put_contents($this->_test_options->log, $output);
		}
	}
}