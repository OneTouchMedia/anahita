<?php defined('KOOWA') or die; ?>

<module position="sidebar-b" style="simple"></module>

<?= @helper('ui.searchbox', @route('layout=list'))?>

<div id="an-entities-wrapper">
<?php if(count($items)) : ?>
<?= @template('list') ?>
<?php endif; ?>
</div>