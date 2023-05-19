<?php

$itmsContent = "";
$show = isset($cfg["show"]) ? filter_var($cfg["show"], FILTER_VALIDATE_BOOLEAN) : null;
$caption = isset($cfg["caption"]) ? $cfg["caption"] : $mk;

$active = false;
$url = null;
if ($cfg["items"])
{
	ob_start();
	foreach ($cfg["items"] as $itmK => $itmCfg)
	{
		list($itmShow, $itmActive) = $this->renderNavItem($itmK, $itmCfg, $props, $req);
		if ($itmShow)
			$show = true;
		if ($itmActive)
			$active = true;
	}
	$itmsContent = ob_get_clean();				
}
else
{
	if (is_null($show))
		$show = isset($props[$mk]);
	$url = isset($cfg["url"]) ? $cfg["url"] : qUrl('p-adminitem', $mk);
	$url = rtrim($url, "\\/");
	$active = ($url == $req);
}

if (!$show)
	return [false, $active];

?>
<li<?= $cfg["items"] ? " class='submenu'" : "" ?> q-args="$mk = null, $cfg = null, $props = null, $req = null">
	<?php if ($cfg["items"]) : ?>
		<a class='pointer'><?= _L($caption) ?></a>
		<ul>
			<?= $itmsContent ?>
		</ul>
	<?php  else : ?>
		<?php if ($url == '') : ?>
			<a<?= $active ? ' class="active"' : '' ?> href="<?= $url ?>"><i class="fa fa-home"></i></a>
		<?php  else : ?>
			<a<?= $active ? ' class="active"' : '' ?> href="<?= $url ?>"><?= _L($caption) ?></a>
		<?php  endif; ?>
	<?php  endif; ?>
</li>
<?php 
return [true, $active];
?>
