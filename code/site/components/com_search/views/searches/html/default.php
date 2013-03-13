<?php defined('KOOWA') or die; ?>

<module position="sidebar-a" style="none">
<?= @template('scopes') ?>
</module>
<module position="sidebar-b" style="simple">
	
</module>
<div class="an-entities-wrapper">
	<?php if ( !empty($keywords)) : ?>
	<?= @template('list') ?>
	<?php endif;?>
</div>