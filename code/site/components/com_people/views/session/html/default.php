<?php defined('KOOWA') or die('Restricted access') ?>
<module position="sidebar-b" style="basic"></module>

<div>	
	<div class="user">
		<form action="<?=@route()?>" method="post">
		<fieldset>
			<legend><?php print JText::_('LOGIN') ?></legend>
			
			<?php KService::get('koowa:loader')->loadIdentifier('com://site/connect.template.helper.service')?>
			<?php if ( class_exists('ComConnectTemplateHelperService', true) ): ?>
			<div class="connect-service-actions">
			<?php echo KService::get('com://site/connect.template.helper.service')->renderLogins() ?>
			</div>
			<?php endif; ?>
			
			<div class="control-group">				
				<div class="controls">
					<input name="username" placeholder="<?= @text('COM-PEOPLE-LOGIN-PLACEHOLDER-USERNAME-EMAIL')?>" id="username" type="text" alt="username" size="18" />
				</div>
			</div>
			
			<div class="control-group">				
				<div class="controls">
					<input type="password" placeholder="<?= @text('COM-PEOPLE-LOGIN-PLACEHOLDER-PASSWORD')?>" id="passwd" name="password" size="18" alt="password" />
				</div>
			</div>
			
			<?php if(JPluginHelper::isEnabled('system', 'remember')) : ?>
			<div id="form-login-remember" class="control-group">
				<label class="checkbox">
					<input type="checkbox" name="remember" value="yes" alt="<?php print JText::_('Remember me'); ?>" />
					<?php print JText::_('Remember me'); ?>
				</label>
			</div>
			<?php endif; ?>
			<div class="form-actions">
				<input type="submit" name="Submit" class="btn btn-primary" value="<?php print JText::_('LOGIN') ?>" />
			</div>
			
		</fieldset>
	
		<ul>
			<li>
				<a href="<?= @route('view=token') ?>">
					<?php print JText::_('COM-PEOPLE-LOGIN-FORGOT-PASSWORD'); ?>
				</a>
			</li>
		</ul>
		<?php if ( !empty($this->return) ) : ?>
			<input type="hidden" name="return" value="<?= $this->return; ?>" />
		<?php endif;?>
		</form>

	</div>
</div>