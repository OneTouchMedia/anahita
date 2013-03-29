<?php defined('KOOWA') or die; ?>

<?php if ( $current_scope && ($current_scope->commentable || $current_scope->ownable) ) : ?>
<fieldset>
	<legend><?= @text('COM-SEARCH-OPTIONS') ?></legend>
	<?php if ( $current_scope && $current_scope->commentable ) : ?>
	<label class="checkbox">
		<input data-trigger="SearchOption" <?= $search_comments ? 'checked' : ''?> type="checkbox" name="search_comments" value="1" >
		<?= @text('COM-SEARCH-OPTION-COMMENTS') ?>
    </label>
    <?php endif;?>
</fieldset>
<?php endif;?>

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