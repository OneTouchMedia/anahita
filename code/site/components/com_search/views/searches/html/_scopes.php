<?php if ( count($scopes) ) : ?>
	<li class="nav-header"><?= $header ?></li>	
	<?php foreach($scopes as $scope ) : ?>
			<?php 		
				$name		= @text(strtoupper($scope->identifier->type.'-'.$scope->identifier->package.'-SEARCH-SCOPE-'.$scope->identifier->name));
				$count		= $scope->result_count;
					
				if ( $count == 0 && $current_scope !== $scope )
					continue;
				$active = $scope == $current_scope;
			?>
			<li class="<?= $active ? 'active' : ''?>" >
				<a data-trigger="ChangeScope" href="<?= @route('layout=results&scope='.$scope->getKey()) ?>"><?=  $name ?>
					<small class="pull-right"><?= $count ?></small>
				</a>
			</li>
	<?php endforeach;?>
<?php endif ?>