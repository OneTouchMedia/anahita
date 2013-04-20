<?php defined('KOOWA') or die ?>

<?php @commands('toolbar') ?>

<module position="sidebar-b" style="simple"></module>

<?= @template('note') ?>

<?= @helper('ui.comments', $note) ?>