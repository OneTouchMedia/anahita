<?php defined('KOOWA') or die; ?>

<?php if(count($items)) :?>
<div class="an-entities">
	<?php foreach($items as $item ) : ?>
		<?= @view('search')->layout('list')->item($item)->keywords($keywords)?>
	<?php endforeach; ?>
</div>

<?php if ( !isset($pagination) || $pagination !== false ) : ?>
<div id="an-more-records" class="an-more-records">
<?= empty($pagination) ? @pagination($items, array('url'=>@route('layout=list&q='.$q))) : $pagination?>
</div>
<?php endif;?>

<?php else : ?>
<?= @message(@text('LIB-AN-PROMPT-NO-MORE-RECORDS-AVAILABLE')) ?>
<?php endif; ?>