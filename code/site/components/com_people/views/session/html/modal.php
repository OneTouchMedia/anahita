<?php defined('KOOWA') or die('Restricted access') ?>
<popup>    
    <popup:header>
    	<a href="#" class="close">x</a>
    	<h3><?= @text('COM-PEOPLE-SESSION-TITLE') ?></h3>
    </popup:header>
    
    <popup:body>
    	<form action="<?=@route()?>" method="post">
			<?php KService::get('koowa:loader')->loadIdentifier('com://site/connect.template.helper.service')?>
			<?php if ( class_exists('ComConnectTemplateHelperService', true) ): ?>
			<div class="connect-service-actions">
			<?php echo KService::get('com://site/connect.template.helper.service')->renderLogins() ?>
			</div>
			<?php endif; ?>
			
			<div class="control-group">			
				<div class="controls">
					<input class="input-block-level" name="username" placeholder="<?= @text('COM-PEOPLE-SESSION-PLACEHOLDER-USERNAME-EMAIL')?>" id="username" type="text" alt="username" size="18" />
				</div>
			</div>
			
			<div class="control-group">				
				<div class="controls">
					<input class="input-block-level" type="password" placeholder="<?= @text('COM-PEOPLE-SESSION-PLACEHOLDER-PASSWORD')?>" id="passwd" name="password" size="18" alt="password" />
				</div>
			</div>
			
			 
			<div id="form-login-remember" class="control-group">
				<label class="checkbox">
					<input type="checkbox" name="remember" value="yes" alt="<?= @text('COM-PEOPLE-SESSION-REMEMBER-ME'); ?>" />
					<?= @text('COM-PEOPLE-SESSION-REMEMBER-ME'); ?>
				</label>
			</div>
			

			<a href="<?= @route('view=token') ?>">
			<?= @text('COM-PEOPLE-SESSION-FORGOT-PASSWORD'); ?>
			</a>
			
    		<?php if ( !empty($return) ) : ?>
    			<input type="hidden" name="return" value="<?= $return; ?>" />
    		<?php endif;?>
		</form>
    </popup:body>
    
    <popup:footer>
        <button data-trigger="Submit" data-submit-form="#modal-login-form" name="Submit" class="btn btn-large btn-primary">
        	<?= @text('COM-PEOPLE-ACTION-LOGIN') ?>
        </button>    
    </popup:footer>
</popup>