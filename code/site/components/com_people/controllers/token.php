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
 * Token Controller. Performs password RESTful operation for reseting a token
 *
 * @category   Anahita
 * @package    Com_People
 * @subpackage Controller
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class ComPeopleControllerToken extends ComBaseControllerResource
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
    
        $this->registerCallback('after.reset', array($this, 'mailConfirmation'));
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
                'behaviors' => array('mailer')
        ));
    
        parent::_initialize($config);
    }
    
    /**
     * Dispatches a correct action based on the state
     *
     * @param KCommandContext $context
     *
     * @return void
     */
    protected function _actionPost(KCommandContext $context)
    {
        $token = pick($this->id);
    
        if ( $token )
        {
            $this->user = $this->getService('repos://site/users.user')
            ->getQuery()
            ->activation($token)
            ->block(false)
            ->fetch();
            //remove the activation
        }
    
        $result = null;
    
        if ( $this->user ) {
            $result = $this->execute('edit',  $context);
        } else {
            $result = $this->execute('reset', $context);
        }
        return $result;
    }
    
    /**
     * Edits a password
     *
     * @param KCommandContext $context
     *
     * @return void
     */
    protected function _actionEdit(KCommandContext $context)
    {
        $person = $this->getService('repos://site/people.person')
        ->getQuery()
        ->disableChain()->userId($this->user->id)->fetch();
    
        if ( $person )
        {
            $password = $context->data->password;
            $person->setPassword($password);
            	
            //passwrod validation has passed then store the passord in the user
            if ( $person->validateEntity() )
            {
                $this->user->setData(array(
                        'password' 		=> $person->getPassword(true),
                        'activation'	=> null
                ));
    
                $this->user->save();
                $this->setRedirect(array('url'=>'view=session','message'=>'login'));
            } else {
                $context->setError(new AnErrorException($person->getErrors(), KHttpResponse::BAD_REQUEST));
                return false;
            }
        } else {
            $context->setError(new KControllerException('Invalid Token', KHttpResponse::NOT_FOUND));
            return false;
        }
    
    }
    
    /**
     * Resets a password
     *
     * @param KCommandContext $context
     *
     * @return void
     */
    protected function _actionReset(KCommandContext $context)
    {
        $data  = $context->data;
        $email = $data->email;
        //an email
        $user  = $this->getService('repos://site/users.user')
                    ->getQuery()
                    ->email($email)
                    ->where('IF(@col(block),@col(activation) <> \'\',1)')
                    ->fetch();
        
        if ( $user ) {
            $user->requiresActivation()->save();
            $this->user = $user;
        }
        else {
            $context->setError(new KControllerException('Invalid Email Address', KHttpResponse::NOT_FOUND));
            return false;
        }
    }
    
    /**
     * Send an email confirmation after reset
     *
     * @param KCommandContext $context
     *
     * @return void
     */
    public function mailConfirmation(KCommandContext $context)
    {
        if ( $this->user )
        {
            $this->mail(array(
                    'to' 	   => 'ash@peerglobe.com',
                    'subject'  => sprintf(JText::_('COM-PEOPLE-PASSWORD-RESET-SUBJECT'), JFactory::getConfig()->getValue('sitename')),
                    'template' => $this->user->block ? 'account_activate' : 'password_reset'
            ));
            	
            $this->setRedirect('layout=email_sent');
        }
    }
    
    /**
     * (non-PHPdoc)
     * @see LibBaseControllerResource::_actionGet()
     */
    protected function _actionGet(KCommandContext $context)
    {
        $this->_state->insert('id');
        
        if ( $this->id )
        {
            $this->user = $this->getService('repos://site/users.user')
                ->getQuery()
                ->activation($this->id)
                ->block(false)
                ->fetch();

            if ( $this->user ) {
                $this->getView()->layout('_reset');
            }            
        }
        return $this->getView()->display();
    }    
}