<select q-args="$from = null, $name = null, $data = null, $selected = null, $default = null, $extraclass = null, $captionMethod = null, $selector = null, $bindParams = null, $method = null, $attrs = null" 
	name='<?= $name ?>' data-from="<?= $from ?>" data-selector="<?= $selector ?>" data-params='<?= $bindParams ? json_encode($bindParams) : "" ?>' data-method="<?= $method ?>" data-caption-method="<?= $captionMethod ?>"
	class="js-select-ctrl select-2<?= $extraclass ? $extraclass : "" ?> select2-hidden-accessible" tabindex="-1" aria-hidden="true"<?= $attrs ? $attrs : "" ?>>
	<?php if ($default) : ?>
		<option value="default"><?= $default ?></option>
	<?php endif; 

	$selectedValue = null;
	$selectedCaption = null;
	if ($selected)
	{
		$selectedIsQImodel = ($selected instanceof \QIModel);
		$selectedValue = $selectedIsQImodel ? $selected->getId() : (is_array($selected) ? $selected["id"] : $selected);
		$selectedCaption = $selectedIsQImodel ? (($captionMethod && method_exists($selected, $captionMethod)) ? $selected->$captionMethod() : $selected->getModelCaption()) :
			(is_array($selected) ? $selected["text"] : $selected);
	}

	$selectedFound = false;

	ob_start();
	if ($data && (count($data) > 0))
	{
		foreach ($data as $itm)
		{
			$itmIsQImodel = ($itm instanceof \QIModel);
			$itmValue = $itmIsQImodel ? $itm->getId() : (is_array($itm) ? $itm["id"] : $itm);
			$itmCaption = $itmIsQImodel ? (($captionMethod && method_exists($itm, $captionMethod)) ? $itm->$captionMethod() : $itm->getModelCaption()) :
				(is_array($itm) ? $itm["text"] : $itm);
			
			$isSelected = ($selected && ($selectedValue === $itmValue));

			if ($isSelected)
				$selectedFound = true;
			?>
			<option<?= $isSelected ? " selected='selected'" : "" ?> value="<?= $itm->getId() ?>">
					<?= ($captionMethod && method_exists($itm, $captionMethod)) ? $itm->$captionMethod() : $itm->getModelCaption() ?>
			</option>
			<?php
		}
	}
	$options = ob_get_clean();

	if ($selected && !$selectedFound): ?>
		<option selected="selected" value="<?= $selectedValue ? $selectedValue : "" ?>"><?= $selectedCaption ? $selectedCaption : "" ?></option>
	<?php endif; ?>
	<?= $options ?>
</select>