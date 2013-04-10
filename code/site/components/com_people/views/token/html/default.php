<?php defined('KOOWA') or die('Restricted access') ?>
<?php 
@service('application.dispatcher')->tmpl = 'component';
?>

<div class="row">
	<div class="offset3 span6">
		<form data-behavior="FormValidator" action="<?= @route('view=token') ?>" method="post" class="well">
		<fieldset>
			<legend><?= @text('COM-PEOPLE-TOKEN-FORGOT-PASSWORD') ?></legend>
            <?php if ( !empty($flash['confirm']) ) : ?>
                 <?= @message(@text('COM-PEOPLE-TOKEN-SENT'), array('type'=>'info')) ?>            
            <?php elseif ( !empty($flash['error']) ) : ?>
            <?= @message(@text('COM-PEOPLE-TOKEN-INVALID-EMAIL'), array('type'=>'error')) ?> 
            <?php endif?>			
            
			<div class="control-group">				
				<div class="controls">
					<p><?= @text('COM-PEOPLE-TOKEN-FORGOT-PASSWORD-INSTRUCTIONS') ?></p>
					<input class="input-block-level" id="email" name="email" type="text" placeholder="<?= @text('COM-PEOPLE-EMAIL-PLACEHOLDER') ?>"  data-validators="required validate-email" />
				</div>
			</div>
		
			<div class="form-actions">
				<button type="submit" class="btn btn-large">
					<?= @text('COM-PEOPLE-ACTION-RESET-PASSWORD'); ?>
				</button>
			</div>
		</fieldset>
		</form>
	</div>
</div>