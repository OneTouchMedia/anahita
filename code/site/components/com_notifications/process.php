<?php
if ( !defined('JPATH_BASE') ) 
{
    $base = dirname($_SERVER['DOCUMENT_ROOT'].$_SERVER['SCRIPT_NAME']);
    $base = str_replace('/components/com_notifications','',$base);
    define(JPATH_BASE, $base);
    require_once JPATH_BASE.'/includes/framework.php';
    KService::get('com://site/application.dispatcher')->load(); 
}

$ids = (array)KRequest::get('get.id', 'int', array());
$notifications = KService::get('repos://site/notifications.notification')->getQuery()
    ->disableChain()
    ->status(ComNotificationsDomainEntityNotification::STATUS_NOT_SENT)
    ->id($ids)
    ->fetchSet()
;
// print json_encode(array('proccessing'=>count($notifications)));
KService::get('com://site/notifications.mailer')
    ->sendNotifications($notifications);
?>