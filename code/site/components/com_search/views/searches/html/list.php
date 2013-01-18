<?php defined('KOOWA') or die; ?>

<div class="an-entities" id="an-entities-main">
<?php if(isset($keywords)): ?>
	<?php if(count($items)) :?>
		<?php foreach($items as $item ) : ?>
			<?= @view('search')->layout('list')->item($item)->keywords($keywords)?>
		<?php endforeach; ?>
	<?php else : ?>
		<?= @message(@text('LIB-AN-PROMPT-NO-MORE-RECORDS-AVAILABLE')) ?>
	<?php endif; ?>
<?php endif; ?>
</div>

<?php if(isset($keywords)): ?>
<?= @pagination($items, array('url'=>@route('layout=list&q='.$q))) ?>
<?php endif; ?>