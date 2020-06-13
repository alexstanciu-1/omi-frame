<div qArgs="$filter = null, $limit = 200, $classes = null" data-url_tag="<?= $this->url_tag ?>" class="QDevModeSelectCtrl<?= $this->drop_down ? " qHideOnClickAway" : "" ?>">
	<div>
		<input type="text" class="classesSearch" placeholder="search class, you can separate with space" autocomplete="off" 
			<?= $this->input_qb ? "qb=\"".htmlentities($this->input_qb)."\"" : "" ?>
			<?= $this->input_default ? "value=\"".htmlentities($this->input_default)."\"" : "" ?>style="min-width: 260px;" />
	</div>
	<?php
		$this->renderList();
	?>
</div>