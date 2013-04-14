<?php

/** 
 * LICENSE: ##LICENSE##
 * 
 * @category   Anahita
 * @package    Com_Notifications
 * @subpackage Controller
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @copyright  2008 - 2010 rmdStudio Inc./Peerglobe Technology Inc
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version    SVN: $Id: resource.php 11985 2012-01-12 10:53:20Z asanieyan $
 * @link       http://www.anahitapolis.com
 */

/**
 * Process a notification and mail it out
 *
 * @category   Anahita
 * @package    Com_Notifications
 * @subpackage Controller
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @link       http://www.anahitapolis.com
 */
class ComNotificationsControllerProcessor extends ComBaseControllerResource
{
    /**
     * Parser Template Helper
     *
     * @var ComStoriesTemplateHelperParser
     */
    protected $_parser;
    
    /**
     * Constructor.
     *
     * @param 	object 	An optional KConfig object with configuration options
     */
    public function __construct(KConfig $config)
    {
        parent::__construct($config);
    
        $this->_parser  = $this->getService($config->parser);
    
        JFactory::getLanguage()->load('com_actors');
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
            'parser'    => 'com://site/notifications.template.helper.parser',
            'behaviors' => array(
                'com://site/mailer.controller.behavior.mailer'
                    => $config->toArray()       
             )
        ));
        	
        parent::_initialize($config);
    }
        
    /**
     * Process an array of notifications
     * 
     * @param KCommandContext $context
     * 
     * @return void
     */
    protected function _actionProcess(KCommandContext $context)
    {   
        $ids = (array)KConfig::unbox($this->id);
        $notifications = KService::get('repos://site/notifications.notification')
            ->getQuery()
            ->disableChain()
            ->status(ComNotificationsDomainEntityNotification::STATUS_NOT_SENT)
            ->id($ids)
            ->fetchSet();
        
        $this->sendNotifications($notifications);
    }
    
    /**
     * Send a set of notifications
     *
     * @param array $notifications
     *
     * @return void
     */
    public function sendNotifications($notifications)
    {
        $space = $this->getService('anahita:domain.space');
        try
        {
            foreach($notifications as $notification)
                $notification->status = ComNotificationsDomainEntityNotification::STATUS_SENT;
            //change the notification status
            $space->commitEntities();
            //send the notification
            foreach($notifications as $notification) {
                $this->sendNotification($notification);
            }
        }
        catch(Exception $e)
        {
            //re-throw the exception if debug is on
            if ( JDEBUG )
                throw $e;
        }
         
        $space->commitEntities();
    }
    
    /**
     * Renders emails for a list of people
     *
     * @param array $config Config parameter
     *
     * @return array
     */
    protected function _renderMails($config)
    {
        $mails    = array();
        $config   = new KConfig($config);
        $settings     = $config->settings;
        $people       = $config->people;
        $notification = $config->notification;
        foreach($people as  $person)
        {
            $setting = $settings->{$person->id};
    
            if ( !$ret = $notification->shouldNotify($person, $setting) ) {
                $notification->removeSubscribers($person);
                continue;
            }
             
            $person->addNotification($notification);
             
            if ( $ret !== ComNotificationsDomainDelegateSettingInterface::NOTIFY_WITH_EMAIL ) {
                continue;
            }
             
            //since each owner revieces the mail, they are in fact the viewer
            //so we need to set the as viewer while processing the notification
            KService::set('com:people.viewer', $person);
             
            $notification->owner = $person;
            $data = new KConfig($this->_parser->parse($notification));
    
            $data->append(array(
                    'email_subject' => $data->title,
                    'email_title'	=> pick($data->email_subject, $data->title),
                    'email_body' 	=> $data->body,
                    'notification'	=> $notification
            ));            
    
            $body = $this->renderMail(array(
                    'layout'   => false,
                    'template' => 'notification',
                    'data' => array(
                        'person'    => $person,
                        'commands'  => $data->commands,
                        'subject' => $notification->subject,
                        'title'	  => $data->email_title,
                        'body'    => $data->email_body
                    )
            ));
                
            $subject  = KService::get('koowa:filter.string')->sanitize($data->email_subject);
             
            $mailer = JFactory::getMailer();
            $mailer->setSubject($subject);
            $mailer->setBody($body);
            $mailer->isHTML(true);
            $mailer->addRecipient(array($person->email));
             
            $mails[] = $mailer;
        }
         
        return $mails;
    }
    
    /**
     * Send a set of notifications
     *
     * @param ComNotificationsDomainEntityNotification $notification Notification
     *
     * @return void
     */
    public function sendNotification($notification)
    {
        $people    = $this->getService('repos://site/actors.actor')->getQuery()->disableChain()->id($notification->subscriberIds->toArray())->fetchSet();
        $settings  = $this->getService('repos:notifications.setting')
        ->getQuery()->disableChain()
        ->where('actor.id','IN', $notification->target->id)->fetchSet();
        $settings  = AnHelperArray::indexBy($settings, 'person.id');
         
        $mails     = $this->_renderMails(array('notification'=>$notification,'people'=>$people, 'settings'=>$settings));
         
        //forward all the mails to the specified mail
        if ( JDEBUG )
        {
            $email  = explode(',',get_config_value('notifications.redirect_email'));
            $mailer = JFactory::getMailer();
            $mailer->isHTML(true);
            $mailer->addRecipient($email);
            $mailer->setSubject('Sending out '.count($mails).' notification mail(s)');
            $recipients = array();
            foreach($mails as $i => $mail)
            {
                $recipients[] = $mail->to[0][0];
                if ( $i < 3 )
                {
                    $body   = array();
                    $body[] = 'Subject   : '.$mail->Subject;
                    $body[] = $mail->Body;
                    $body   = implode('<br />', $body);
                    $bodies[] = $body;
                }
            }
            $bodies[] = 'Sending out '.count($mails).' notification mail(s)';
            $bodies   = implode('<hr />', $bodies);
            $mailer->setBody($bodies);
             
            $tmp = JFactory::getConfig()->getValue('tmp_path').'/notifications.html';
            $bodies .= '<br /><br />'.implode('<br />',$recipients);
            file_put_contents($tmp, $bodies);
            if ( $email ) $mailer->Send();
        } else {
            foreach($mails as $person => $mail) {
                $mail->Send();
            }
        }
    }    
}