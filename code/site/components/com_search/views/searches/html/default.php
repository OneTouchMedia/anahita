<?php defined('KOOWA') or die; ?>

<module position="sidebar-b" style="none">
<div class="search-scopes">
	<?php if ( !empty($keywords)) : ?>
	<?= @template('scopes') ?>
	<?php endif;?>
</div>
</module>
<div class="search-results">
	<?= @template('results')?>
</div>