<ul class="qHideOnClickAway-container classesList" qArgs="$filter = null, $limit = 200, $classes = null, $url_tag = null">
<?php
	$url_tag = $url_tag ?: $this->url_tag;
	$classes = $classes ?: $this->getClassesList($filter, $limit);
	if ($classes)
	{
		// $webpage = $this->getWebPage();
		foreach ($classes as $class => $path)
		{
			$url = $this->getLink($url_tag, $class);
			$namespace = null;
				
			if (($url === null) || ($url === false)):
				?><li data-classname="<?= strtolower($class) ?>" data-oclassname="<?= $class ?>"><a><?= $class ?></a></li><?php
			else:
				?><li data-classname="<?= strtolower($class) ?>" data-oclassname="<?= $class ?>"><a href="<?= $url ?>"><?= $class ?></a></li><?php
			endif;
		}
	}
?>
</ul>