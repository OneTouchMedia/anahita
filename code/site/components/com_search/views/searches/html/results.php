<form id="an-search-form" class="well form-search" name="an-search-form" action="<?=@route('view=search')?>" method="GET">				
			<input  type="text" name="q" class="input-large search-query" id="an-search-query" value="<?=empty($q) ? '' : $q?>" size="21" maxlength="21" /> 	
			<button data-trigger="SearchRequest" type="submit" class="btn">
				<?=@text('LIB-AN-ACTION-SEARCH')?>
			</button>
			<?php if ( !empty($actor) ) : ?>
				<input type="hidden" name="oid" value="<?= $actor->id?>" />
			<?php endif; ?>			
			<?php if ( $current_scope ) : ?>
				<input type="hidden" name="scope" value="<?= $current_scope->getKey() ?>" />
				<?php if ( $current_scope->commentable ) : ?>
				<label class="checkbox">
			    	<input type="checkbox" data-trigger="SearchRequest" <?= $search_comments ? 'checked' : ''?> value="1" name="search_comments" />
			    	Search Comments
		  		</label>	
		  		<?php endif;?>			 
			<?php endif; ?>	
</form>	
<div class="an-entities-wrapper">
	<?php if ( !empty($keywords)) : ?>
	<?= @template('list') ?>
	<?php endif;?>
</div>