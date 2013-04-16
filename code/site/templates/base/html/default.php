<?php defined('KOOWA') or die;?>
<!DOCTYPE html>
<html lang="en">
  <head>
  	<meta name="viewport" content="width=device-width, initial-scale=1.0">    
  	<?= @render('style') ?>
  </head>

  <body>	  	
	<?= @template('tmpl/js') ?>
    
    <div id="container-system-message" class="container">       
    	<?= @render('messages') ?>
    </div>
    		
    <?= @template('tmpl/navbar') ?>
    
    <?= @render('modules', '1', array('style'=>'none')) ?>
	<?= @render('modules', '2', array('style'=>'simple')) ?>
	<?= @render('modules', '3', array('style'=>'simple')) ?>
    <?= @render('modules', '4', array('style'=>'simple')) ?>
    <?= @render('modules', '5', array('style'=>'simple')) ?>
    
    <?= @render('component') ?>
    <?= @render('analytics') ?> 
  </body>
</html>