<div jsFunc="renderItems($items, $binds)" q-args="$items = null, $binds = null" class="qc-custom-dd-items">
	<script jsFuncMode="prepend">

		//if (!$binds)
		//	$binds = {};

	</script>
	<div>
		<?php 
			$useModels = ($binds && $binds["useModels"]);
			$noneItm = ($binds && $binds["NoneItm"]);
			if ($noneItm)
			{
				?>
				<div class="qc-custom-dd-item" item.value='null'><?= ($binds && $binds["NoneItmCaption"]) ? $binds["NoneItmCaption"] : "None" ?></div>
				<?php
			}

			if ($items && count($items) > 0) 
			{
				$dl = "";
				if ($binds["_tree_"])
				{
					if (!$binds["_depth_"])
						$binds["_depth_"] = 0;

					// if depth is 0 then we are at the base and we need to index items
					if ($binds["_depth_"] === 0)
					{
						// we can have a starting point for the tree
						$data = $this->initTreeData($items, $binds["_tree_sp_"], $useModels);
						$items = $data[0];
						$binds["_byParent_"] = $data[1];
					}

					$byParent = $binds["_byParent_"];

					$dl = ($binds["_depth_"] > 0) ? " style='padding-left: ".($this->Distancer * 0.1 * $binds["_depth_"])."rem;'" : "";

					$binds["_depth_"]++;
				}

				foreach ($items as $val => $item)
				{
					$data = $useModels ? " item.id='{$item->getId()}' item.ty='".q_get_class($item)."'" : " item.value='{$val}'";
					?><div class="qc-custom-dd-item"<?= $dl.$data ?>>{{ $useModels ? $item->getModelCaption() : $item }}</div><?php

					if (!$binds["_tree_"] || !$byParent[$item->getId()])
						continue;
					$this->renderItems($byParent[$item->getId()], $binds);
				}
			}
		?>
	</div>
</div>