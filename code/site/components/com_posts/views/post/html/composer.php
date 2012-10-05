<?php defined('KOOWA') or die ?>

<?php if ( $actor->authorize('action', 'com_posts:post:add') ) : ?>
<div id="post-composer">
	<form id="post-composer-form" action="<?= @route('option=com_posts&view=post&oid='.$actor->id) ?>" method="POST" data-formvalidator-options="'evaluateFieldsOnBlur':true">
		<textarea class="input-xxlarge" data-validators="minLength:1 maxLength:<?=STORY_MAX_LIMIT?>" id="composer-textarea"  name="body" overtext="<?= @text('COM-POSTS-SHARE-PROMPT') ?>"></textarea>
	    <input type="hidden" name="composed" value="1" />
		<div class="post-actions">
		    <?php 
		        $app = @service('repos://site/apps.app')->fetch(array('component'=>'com_connect'));		    
		    ?>
			<?php if ( $app && $app->authorize('echo', array('actor'=>$actor)) ) : ?>
                <?php               
                $services  = ComConnectHelperApi::getServices();
                @service('repos://site/connect.session'); 
                $sessions  = $actor->sessions->toArray();
                foreach($sessions as $key => $session) 
                {
                    if ( $session->getApi()->isReadOnly() ) {
                        unset($sessions[$key]);
                    }
                }                
                ?>    	        
    			<?php if ( count($sessions) > 0 ) : ?>
    			<div class="post-action">
    			    <div class="connect-service-share">
    			    <?php foreach($sessions as $session) : ?>
    			        <a data-behavior="BS.Twipsy" title="<?= sprintf(@text('COM-CONNECT-SHARE-POST'), ucfirst($session->api->getName()))?>">
    			            <?= @helper('com://site/connect.template.helper.service.icon', $session->api->getName())?>
    			            <input type="checkbox" name="channels[]" value="<?= $session->getName() ?>" class="hide"/>			         
    			        </a>                        
    			    <?php endforeach;?>
    			    </div>
    			</div>
    			<?php elseif (count($services) > 0 ) : ?>
    			<a href="<?= @route($actor->getURL().'&get=settings&edit=connect') ?>" class="btn">
    			    <?= @text('COM-CONNECT-ENABLE-SHARE')?>
    			</a>
    			<?php endif; ?>
			<?php endif; ?>
			
			<div class="post-action right">
				<button data-trigger="Share" data-request-options="{inject:'an-stories'}" class="btn btn-primary" >
					<?= @text('LIB-AN-ACTION-SHARE') ?>
				</button>
			</div>
			
			<?php if ( is_person($actor) && !is_viewer($actor) ) : ?>			
			<div class="post-action right">
				<label class="checkbox" for="private-message">
					<input id="private-flag" type="checkbox" name="private_message"> 
					<?=@text('COM-POSTS-COMPOSER-PRIVATE-FLAG')?>
				</label>
			</div>
			<?php endif; ?>
			
			<div class="post-action right">
				<span class="counter"></span>
			</div>
		</div>
	</form>
</div>
<?php endif; ?>