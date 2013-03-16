<?php defined('KOOWA') or die('Restricted access') ?>
<module position="sidebar-b" style="basic"></module>

<div>

<div>
	
	<div class="user">
	
		<form data-behavior="FormValidator" action="<?= @route() ?>" method="post">
		<fieldset>
			<legend><?php print JText::_('Reset your Password'); ?></legend>
			
			<div class="control-group">				
				<div class="controls">
					<input data-validators="required" placeholder=<?php print JText::_('Password'); ?> id="password1" name="password" type="password" />
				</div>
			</div>
			
			<div class="control-group">
				<div class="controls">
					<input placeholder=<?php print JText::_('Verify Password');?> data-validators="required validate-match matchInput:'password' matchName:'<?php print JText::_( 'Password' )?>'" type="password" id="password2" name="password2" value="" />
				</div>
			</div>
			
			<div class="form-actions">
				<button type="submit" class="btn btn-primary"><?php print JText::_('Submit'); ?></button>
			</div>
			
		</fieldset>		
		</form>		
	</div>
</div>