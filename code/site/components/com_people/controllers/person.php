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
 * Person Controller
 *
 * @category   Anahita
 * @package    Com_People
 * @subpackage Controller
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class ComPeopleControllerPerson extends ComActorsControllerDefault
{
	/**
	 * Flag to whether an activation is required or not
	 * 
	 * @var boolean
	 */
	protected $_activation_required;
	
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
		
		$this->_activation_required = $config->activation_required;
		
		if ( $this->_activation_required ) {
			$this->registerCallback('after.add', array($this, 'mailActivationLink'));
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
		$config->append(array(
			'activation_required' => get_config_value('users.useractivation'),
			'behaviors'			  => array('validatable','mailer'),
		    'login_callback'      => array($this, 'login')
		));
		
		parent::_initialize($config);
		
		AnHelperArray::unsetValues($config->behaviors, 'ownable');
        
        //if it's a person view , set the default id to person
        if ( $config->request->view == 'person' )
        {
            $config->append(array(
                    'request'    => array(
						'id' => get_viewer()->id
                    )
            ));
        }
	}
	
    /**
     * Hides the menubar title
     * {@inheritdoc}
     */
	protected function _actionGet(KCommandContext $context)
	{
        $this->getToolbar('menubar')->setTitle(null);
		return parent::_actionGet($context);
	}

    /**
     * Deletes a person and all of their assets. It also logsout the person.
     * 
     * @param KCommandContext $context Context parameter
     * 
     * @return AnDomainEntityAbstract
     */
    protected function _actionDelete(KCommandContext $context)
    {
        parent::_actionDelete($context);
        
        $this->commit();
        
        JFactory::getUser($this->getItem()->userId)->delete();     
        
        $this->getService('com:people.helper.person')->logout($this->getItem(), array('message'=>JText::_('COM-PEOPLE-PERSON-DELETED-MESSAGE')));               
    }
    
    /**
     * Person add action creates a new person object.
     * 
     * @param KCommandContext $context Commaind chain context
     * 
     * @return AnDomainEntityAbstract
     */
    protected function _actionAdd(KCommandContext $context)
    {    	
        //we are not saving this person but just validating it
        $person = parent::_actionAdd($context);
        $data   = $context->data;        
        
        $person->userId = PHP_INT_MAX; //is assiged automatically
        
        
        //manually set the password to make sure there's a password
        
        $person->setPassword($data->password);
        
        //add the validations here
        $this->getRepository()->getValidator()
                ->addValidation('username','uniqueness')
                ->addValidation('email',   'uniqueness')
                ;
                
        if ( $person->validate() === false ) {      	
            $context->setError(new AnErrorException($person->getErrors(), KHttpResponse::BAD_REQUEST));
            return false;
        }

        $person->reset();
        
        $user       = clone JFactory::getUser();
        $authorize  =& JFactory::getACL();
        
        $user->set('id', 0);
        $user->set('name', $person->name);
        $user->set('username', $person->username);
        $user->set('email', $person->email);
        $user->set('password', $person->getPassword(true));
        $user->set('usertype', 'Registered');
        $user->set('gid', $authorize->get_group_id( '', 'Registered', 'ARO' ));
        $date =& JFactory::getDate();
        $user->set('registerDate', $date->toMySQL());
        
        if ( $this->_activation_required ) 
        {
            jimport('joomla.user.helper');
            $user->set('activation', JUtility::getHash( JUserHelper::genRandomPassword()) );
            $user->set('block', '1');
            $context->append(array(
				'headers' => array(
					'X-User-Activation-Required' => true
			)));
        }
        
        $user->save();
        $person = $this->getRepository()->fetch(array('userId'=>$user->id));
        
        //if person is null then user has not been saved
        if ( !$person ) {
            $context->setError(new KControllerException('Unexpected error when saving user'));
            return false;   
        }
        
        //set the portrait image
        if ( $file = KRequest::get('files.portrait', 'raw') ) {
            $person->setPortraitImage(array('url'=>$file['tmp_name'], 'mimetype'=>$file['type']));
        }
                            
        //set the status
        $context->status  = KHttpResponse::CREATED; 
        
        $this->setItem($person);
                        
        return $person;
        
    }
    
    /**
     * Return whether the action is required or not
     * 
     * @return boolean
     */
    public function activationRequired()
    {
    	return $this->_activation_required;	
    }
    
    /**
     * Edit a person's data and synchronize with the person with the user entity
     * 
     * @param KCommandContext $context Context parameter
     * 
     * @return AnDomainEntityAbstract
     */
    protected function _actionEdit(KCommandContext $context)
    {        
        //add the validations here
        $this->getRepository()->getValidator()
                ->addValidation('username','uniqueness')
                ->addValidation('email',   'uniqueness')
                ;
                        
        $data   = $context->data;
        
        $person = parent::_actionEdit($context);
              
        //manually set the password to make sure there's a password
        if ( $data->password ) {
            $person->setPassword($data->password);
        }
                
        if ( $person->validate() === false ) {
            $context->setError(new AnErrorException($person->getErrors(), KHttpResponse::BAD_REQUEST));
            return false;
        }
        
        $user = JFactory::getUser($person->userId);
        
        if ( $person->getModifiedData()->name ) {
            $user->set('name', $person->name);
        }
        
        if ( $person->getModifiedData()->username ) {
            $user->set('username', $person->username);   
        }
        
        if ( $person->getModifiedData()->email ) {
            $user->set('email', $person->email);               
        }
        
        if ( $data->password ) {
            $user->set('password', $person->getPassword(true));
        }
        
        if ( !$user->save() ) {
            $context->setError(new KControllerException('Unexpected error when saving user'));
            return false;               
        }
        
        if ( !$person->save() ) {
            $context->setError(new KControllerException('Unexpected error when saving user'));
            return false;            
        }
        
        $context->status = KHttpResponse::RESET_CONTENT;
         
        return $person;      
    }

    /**
     * (non-PHPdoc)
     * @see ComActorsControllerAbstract::_actionPost()
     */
    protected function _actionPost(KCommandContext $context)
    {
    	$result = parent::_actionPost($context);
    	return $result;
    }   
     
    /**
     * Mail an activation link
     *
     * @param KCommandContext $context The context parameter
     * 
     * @return void
     */    
    public function mailActivationLink(KCommandContext $context)
    {    	    	
		$person = $context->result;
		$this->user = $person->getUserObject();
		$this->mail(array(
    				'to' 		=> 'ash@peerglobe.com',
    				'subject'	=> 'Activate your account',
    				'template'	=> 'account_activate'
		));
		//redirect only happens if it's html
		$this->setRedirect(array('url'=>'view=session','message'=>'activation link has been sent'));
    }
    
    /**
     * Login the user after creating it
     *
     * @param KCommandContext $context The context parameter
     * 
     * @return void
     */
    public function login()
    {    
    	$user = (array)JFactory::getUser($this->getItem()->userId);
    	$this->getService()->set('com:people.viewer', $this->getItem());
    	$controller = $this->getService('com://site/people.controller.session');
    	return $controller->login($user);
    }
    
    /**
     * Called before the setting page is displayed
     * 
     * @param KEvent $event
     * 
     * @return void
     */
    public function onSettingDisplay(KEvent $event)
    {   
        $tabs = $event->tabs;   
        if ( JFactory::getUser()->id == $event->actor->userId ) {     
            $tabs->insert('account',array('label'=>JText::_('COM-PEOPLE-SETTING-TAB-ACCOUNT')));                    
        } 
    }    
}