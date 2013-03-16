<?php 
$config->subject = sprintf(JText::_('COM-PEOPLE-PASSWORD-RESET-SUBJECT'), JFactory::getConfig()->getValue('sitename'))
?>
<?= @text('Hi')?> <?= $user->name ?>


<?= @text('COM-PEOPLE-PASSWORD-RESET-BODY')?>


<?= @route('view=password&token='.$user->activation) ?>




