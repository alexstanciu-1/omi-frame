<div jsFunc="renderItems($items = null, $from = null, $selector = null, $binds = null)" q-args="$items = null, $from = null, $selector = null, $binds = null, $level = 0">
	@php $none_opt_was_set = false;
	@if ($binds && $binds['showNoneOption'] && ($level < 1))
		<div class="qc-dd-item qc-dd-reset-item">None</div>
		@php $none_opt_was_set = true;
	@endif
	<script jsFuncMode="prepend">

		if (!$binds)
			$binds = {};

	</script>
	<?php


	if ($items && (count($items) > 0))
	{
		if (!$binds["_depth_"])
			$binds["_depth_"] = 0;

		// if depth is 0 then we are at the base and we need to index items
		if ($binds["_depth_"] === 0)
		{
			// we can have a starting point for the tree
			$data = $this->initTreeData($items, $binds["_tree_sp_"], $binds["_pp_prop_"] ?: "Parent", $binds);
			$items = $data[0];
			$binds["_byParent_"] = $data[1];
		}

		$byParent = $binds["_byParent_"];

		$dl = ($binds["_depth_"] > 0) ? " style='padding-left: ".($this->Distancer * 0.1 * $binds["_depth_"])."rem !important;'" : "";

		$binds["_depth_"]++;

		if ((!$binds || !$binds['hideNoneOption']) && ($level < 1) && (!$none_opt_was_set))
		{
			?><div class="qc-dd-item qc-dd-reset-item">{{$binds['noneOptionCaption'] ?: _L('None')}}</div><?php
			$none_opt_was_set = true;
		}
		foreach ($items ?: [] as $item)
		{
			?>
			<div<?= $dl ?> data-full='<?= htmlspecialchars(json_encode($item->toJSON()), ENT_QUOTES) ?>' class="qc-dd-item" item.id="{{$item->Id}}" item.ty="{{get_class($item)}}">
				{{ $item->getModelCaption($binds ? $binds["__caption_tag"] : null) }}
			</div>

			<?php
			if (!$byParent[$item->getId()])
				continue;

			$this->renderItems($byParent[$item->getId()], $from, $selector, $binds, ++$level);
		}
	}
	?>
</div>