<?php defined('KOOWA') or die; ?>
<form id="an-search-form" class="well form-search" name="an-search-form"  method="GET">				
	<input  type="text" name="q" class="input-large search-query" id="an-search-query" value="<?=empty($value) ? '' : $value?>" size="21" maxlength="21" />
	<button data-trigger="Request" data-request-options="{url:'<?=$search_action?>',update:'an-entities-main'}" type="submit" class="btn">
		<?=@text('LIB-AN-ACTION-SEARCH')?>
	</button>
</form>