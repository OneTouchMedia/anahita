<?php defined('KOOWA') or die; ?>

<?php if($item->isDescribable()): ?>
<div class="an-entity">

	<?php if($item->isPortraitable() && !$item->inherits('ComActorsDomainEntityActor')): ?>
	<div class="entity-portrait-medium">
		<a title="<?= @escape($item->title) ?>" href="<?= @route($item->getURL()) ?>">			
			<img alt="<?= @escape($item->title) ?>" src="<?= $item->getPortraitURL('medium') ?>" />
		</a>
	</div>
	<?php endif; ?>

	<div class="entity-portrait-square">
		<?php if($item->inherits('ComActorsDomainEntityActor')): ?>
		<?= @avatar($item) ?>
		<?php elseif($item->isModifiable()): ?>
		<?= @avatar($item->author) ?>
		<?php endif; ?>
	</div>
	
	<div class="entity-container">
		<?php if(!empty($item->title)): ?>
		<h3 class="entity-title">
			<a href="<?= @route($item->getURL()) ?>">
				<?= @escape($item->title) ?>
			</a>
		</h3>
		<?php endif; ?>
		
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
<?php elseif ( $item->inherits('ComBaseDomainEntityComment') ) : ?>
<div class="an-entity">
	<div class="entity-portrait-square">
		<?= @avatar($item->author)  ?>
	</div>

	<div class="entity-container">
		<?php if( $item->parent->isDescribable() && $item->parent->title ): ?>
		<h3 class="entity-title">
			<a href="<?= @route($item->parent->getURL()).'#permalink='.$item->id ?>">
				<?= @escape($item->parent->title) ?>
			</a>
		</h3>
		<?php endif; ?>	
		<div class="entity-description">
		<?php $preview = @helper('text.truncate', strip_tags($item->body), array('length'=>400, 'read_more'=>true))?>
			<?= @helper('text.highlight', $preview, $keywords) ?>
		</div>
		
		<div class="entity-meta">
			<div class="an-meta"><?= sprintf(@text('LIB-AN-MEDIUM-AUTHOR'), @date($item->creationTime), @name($item->author)) ?></div>
		</div>			
	</div>
</div>
<?php endif; ?>