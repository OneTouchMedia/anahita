<?php if (!defined('KOOWA')) die; ?>

<div class="page-header">
    <h1><?= $error->getCode() ?> - <?php print $error->getMessage() ?></h1>
</div>

<div class="alert alert-block alert-error">
<p><?= $error->getMessage() ?></p>
</div>