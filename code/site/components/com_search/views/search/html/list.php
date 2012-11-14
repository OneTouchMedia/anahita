<?php defined('KOOWA') or die; ?>

<div class="an-entity">
	<h3 class="medium-title">
		<a href="<?= @route($item->getURL()) ?>">
			<?= @escape($item->title) ?>
		</a>
	</h3>
	
	<div class="medium-description">
		<?= @helper('text.truncate', strip_tags($item->body), array('length'=>200)); ?>
	</div>
</div>