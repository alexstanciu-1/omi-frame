@php $mc = (<?= $_data_value ?>) ? (<?= $_data_value ?>)->getModelCaption() : '';
<?php if ($__expand && (!$_use_dropdown)) : ?>
	<a class='js-expand expand-data' href='javascript: //' data-expand='<?= $property ?>'>
		@if ($mc)
			@echo $mc;
		@else
			<i class='fa fa-cog'></i>
		@endif
	</a>
<?php else : ?>
	@echo $mc;
<?php endif; ?>