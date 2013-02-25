<ul class="nav nav-pills nav-stacked">
<?php foreach($scopes as $scope ) : ?>
	<?php 		
		$name		= $scope->identifier->name;
		$count		= (int)$items->getScopeCount($scope);
			
		if ( $count == 0 && $current_scope !== $scope )
			continue;
	?>
	<li class="<?= $scope !== $current_scope ?: 'active'?>" >
		<a data-trigger="ChangeScope" href="<?= @route('layout=results&scope='.$scope->getKey()) ?>"><?=  $name ?>
			<small class="pull-right"><?= $count ?></small>
		</a>
	</li>
<?php endforeach;?>
</ul>
