<?php defined('KOOWA') or die; ?>

<module position="sidebar-b" style="simple"></module>

<?= @helper('ui.searchbox', @route('layout=list'), array('value'=>$q))?>

<div id="an-entities-wrapper">
<?php if ( !empty($keywords) ) : ?>
<?= @template('list') ?>
<?php endif; ?>
</div>