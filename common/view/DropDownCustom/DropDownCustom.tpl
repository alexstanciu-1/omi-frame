<?php 
$useModels = ($binds && $binds["useModels"]);
$useSearch = ($binds && $binds["useSearch"]);
$cssCls = ($binds && $binds["cssCls"]) ? $binds["cssCls"] : null;

$isTree = ($binds && $binds["_tree_"]);

$selectedCaption = $selected ? ($useModels ? $selected->getModelCaption() : $selected["caption"]) : "";
$selectedValue = $selected ? ($useModels ? $selected->getId() : $selected["value"]) : "";

if (!$binds)
	$binds = [];

?>
<div q-args="$bind = null, $items = null, $selected = null, $binds = null" jsFunc="render($bind, $items, $binds)" class="qc-custom-dd q-hide-on-click-away<?= ($isTree ? ' qc-custom-dd-tree' : ''). $cssCls. ($useModels ? ' qc-custom-dd-use-model' : '') ?>">
	<?php

	if ($useModels)
	{
		?>
		<input type="hidden" value="<?= $selected ? $selected->getId() : '' ?>" name<?= $selected ? "" : "-x" ?>="<?= $bind ?>[Id]" class="qc-dd-input-id<?= $selected ? '' : ' qc-new-data' ?>" />
		<input type="hidden" value="<?= $selected ? get_class($selected) : '' ?>" name<?= $selected ? "" : "-x" ?>="<?= $bind ?>[_ty]" class="qc-dd-input-ty<?= $selected ? '' : ' qc-new-data' ?>" />
		<?php
	}
	else
	{
		?>
		<input type="hidden" value="{{$selectedValue}}" name<?= $selected ? "" : "-x" ?>="<?= $bind ?>" class="qc-dd-input-val <?= $selected ? '' : ' qc-new-data' ?>" />
		<?php 
	}
	?>
	<div class="qc-custom-dd-pick"><?= $selectedCaption ? $selectedCaption : $this->noItemCaption ?></div>
	<div class="qc-custom-dd-box q-hide-on-click-away-container" style="display: none;">
		<?php 
		if ($useSearch) 
		{
			?>
			<div class="qc-custom-dd-search">
				<input type="text" placeholder="search" value="" />
			</div>
			<?php 
		}
		?>
		@include($this::items, $items, $binds);
	</div>
</div>