<?php defined('KOOWA') or die('Restricted access');?>

<data name="title">
<?php if ( $type != 'notification' && ($target->id == $subject->id || $target->eql($actor))) : ?>
	<?=@name($subject)?>	
<?php else :?>
    <?php if ( $type == 'notification' ) : ?>	
	<?=sprintf(@text('COM-POSTS-ADD-POST-NOTIFICATION'),@name($subject), @route($object->getURL()), @possessive($target))?>
    <?php else : ?>
    <?=sprintf(@text('COM-POSTS-ADD-POST'),@name($subject), @possessive($target))?>
    <?php endif; ?>
<?php endif; ?>
</data>

<?php if ( $type == 'story') : ?>
<data name="body">
	<?= @content($object->body) ?>
</data>
<?php else : ?>
<data name="email_body">
<div><?= $object->body ?></div>
<?php $commands->insert('viewstory', array('label'=>@text('COM-POSTS-VIEW-POST')))->href($object->getURL())?>
</data>
<?php endif;?>

