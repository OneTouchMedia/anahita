<?php defined('KOOWA') or die('Restricted access'); ?>

<ul class="nav" data-behavior="BS.Dropdown">
<?php foreach($items as $item) : ?>	
	<?php if ( !empty($item->subitems) ) : ?>
		<li class="dropdown">
			<a href="#" class="dropdown-toggle" data-toggle="dropdown">
				<?= $item->name?>
				<b class="caret"></b>				
			</a>
			<ul class="dropdown-menu">
			<?php foreach($item->subitems as $subitem) : ?>			
				<li><a href="<?= @route($subitem)?>"><?= $subitem->name?></a></li>
			<?php endforeach;?>
			</ul>			
		</li>
	<?php else : ?>
		<li>
			<a href="<?= @route($item)?>"><?= $item->name?></a>			
		</li>
	<?php endif;?>
<?php endforeach?>
</ul>
