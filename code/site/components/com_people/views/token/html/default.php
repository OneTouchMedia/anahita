<?php defined('KOOWA') or die('Restricted access') ?>
<module position="sidebar-b" style="basic"></module>

<div>
	<div class="user">
		<form data-behavior="FormValidator" action="<?= @route('view=token') ?>" method="post">
		<fieldset>
			<legend><?php print JText::_( 'RESET YOUR PASSWORD' ) ?></legend>
			<div class="control-group">				
				<div class="controls">
					<input id="email" name="email" type="text" placeholder="<?= @text('COM-PEOPLE-EMAIL-PLACEHOLDER') ?>"  data-validators="required validate-email" />
				</div>
			</div>
		
			<div class="form-actions">
				<button type="submit" class="btn btn-primary"><?php print JText::_('Submit'); ?></button>
			</div>
		</fieldset>
		<?php echo JHTML::_( 'form.token' ); ?>
		</form>
	</div>
</div>