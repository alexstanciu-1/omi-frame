<a class='js-expand expand-data' href='javascript: //' data-expand='<?= $property ?>'>
	<?php if ($_model_caption) : ?>
		{{\QModel::Get_Data_Model_Caption(<?= $_data_value ?>)}}
	<?php elseif ($mc) : ?>
		@echo "<?= $mc ?>";
	<?php else: ?>
		<i class='fa fa-cog'></i>
	<?php endif; ?>
</a>