<?php defined('KOOWA') or die('Restricted access') ?>
<module position="sidebar-b" style="basic"></module>

<div>

<div>
	
	<div class="user">
	
		<form data-behavior="FormValidator" action="<?= @route() ?>" method="post">
		<fieldset>
			<legend><?= @text('COM-PEOPLE-RESET-PASSWORD'); ?></legend>
			
			<div class="control-group">				
				<div class="controls">
					<input data-validators="required" placeholder=<?= @text('COM-PEOPLE-PASSWORD'); ?> id="password1" name="password" type="password" />
				</div>
			</div>
			
			<div class="control-group">
				<div class="controls">
					<input placeholder=<?= @text('COM-PEOPLE-PASSWORD-VERIFY');?> data-validators="required validate-match matchInput:'password' matchName:'<?= @text( 'Password' )?>'" type="password" id="password2" name="password2" value="" />
				</div>
			</div>
			
			<div class="form-actions">
				<button type="submit" class="btn btn-primary"><?= @text('LIB-AN-ACTION-SAVE') ?></button>
			</div>
			
		</fieldset>		
		</form>		
	</div>
</div>