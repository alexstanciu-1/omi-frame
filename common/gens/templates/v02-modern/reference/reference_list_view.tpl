@php $mc = (<?= $_data_value ?>) ? (<?= $_data_value ?>)->getModelCaption() : '';
<?php if ($__expand && (!$_use_dropdown)) : ?>
	<a class="flex js-expand text-blue-700" href="javascript: void(0);" data-expand="<?= $property ?>">
		<span class="underline">
			@echo $mc;
		</span>

		<svg class="w-3 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
		  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
		</svg>
	</a>
<?php else : ?>
	@echo $mc;
<?php endif; ?>