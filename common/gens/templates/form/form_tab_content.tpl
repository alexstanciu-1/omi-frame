<div id="<?= $tab_id ?>" class="tab-content qc-tab-content-<?= $tab_property ?> <?= $tab_active ? ' active' : '' ?>">
	<div class="row">
        <?php include(static::GetTemplate("form/form_tab_content_inner.tpl", $config)) ?>
	</div>
</div>