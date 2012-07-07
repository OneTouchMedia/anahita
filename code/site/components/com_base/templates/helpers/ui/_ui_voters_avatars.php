<?php defined('KOOWA') or die; ?>
<?php if( $entity->voteUpCount > 0 ) : ?>
<div class="popup-header">
	<h3><?= $entity->voteUpCount == 1 ? @text('LIB-AN-VOTE-ONE-VOTED') : sprintf(@text('LIB-AN-VOTE-OTHER-VOTED'), $entity->voteUpCount)?></h3>
</div>
<div class="popup-body">
	<div class="popup-entities an-actors">
		<?php foreach($entity->voteups->voter as $actor) : ?>
		<div class="an-entity">		
			<div class="entity-thumbnail">
				<?= @avatar($actor) ?>
			</div>
			<div class="entity-container">
				<h4 class="entity-title"><?= @name($actor) ?></h4>
				
				<div class="an-meta">
					<?= $actor->followerCount ?> 
					<span class="stat-name"><?= @text('COM-ACTORS-SOCIALGRAPH-FOLLOWERS') ?></span> 
				</div>
				
				<div class="entity-description">
					<?= @helper('text.truncate', strip_tags($actor->description), array('length'=>200)); ?>
				</div>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
</div>
<?php endif;?>