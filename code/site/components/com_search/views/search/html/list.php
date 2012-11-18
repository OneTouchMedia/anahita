<?php defined('KOOWA') or die; ?>

<?php if($item->isDescribable()): ?>
<div class="an-entity">
	<div class="entity-thumbnail">
		<?php if($item->inherits('ComActorsDomainEntityActor')): ?>
		<?= @avatar($item) ?>
		<?php elseif($item->isModifiable()): ?>
		<?= @avatar($item->author) ?>
		<?php endif; ?>
	</div>
	
	<div class="entity-container">
		<h3 class="medium-title">
			<a href="<?= @route($item->getURL()) ?>">
				<?= @escape($item->title) ?>
			</a>
		</h3>
		
		<div class="entity-description">
			<?php $preview = @helper('text.truncate', strip_tags($item->body), array('length'=>400, 'read_more'=>true))?>
			<?= @helper('text.highlight', $preview, $keywords) ?>
		</div>
		
		<?php if($item->isModifiable()): ?>
		<div class="entity-meta">
			
			
			<?php if($item->inherits('ComActorsDomainEntityActor')): ?>
			<div class="an-meta">
				<?= $item->followerCount ?>
				<span class="stat-name"><?= @text('COM-ACTORS-SOCIALGRAPH-FOLLOWERS') ?></span> 
				
				<?php if($item->isLeadable()): ?>
				/ <?= $item->leaderCount ?>
				<span class="stat-name"><?= @text('COM-ACTORS-SOCIALGRAPH-LEADERS') ?></span>
				<?php endif; ?>
			</div>
			<div class="an-meta"><?= sprintf(@text('LIB-AN-MEDIUM-EDITOR'), @date($item->updateTime), @name($item->editor)) ?></div>
			<?php else: ?>
			<div class="an-meta"><?= sprintf(@text('LIB-AN-MEDIUM-AUTHOR'), @date($item->creationTime), @name($item->author)) ?></div>
			<?php endif; ?>
		</div>
		<?php endif; ?>
	</div>
</div>
<?php endif; ?>