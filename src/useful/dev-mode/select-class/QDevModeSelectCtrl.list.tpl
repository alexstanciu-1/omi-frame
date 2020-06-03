<ul class="qHideOnClickAway-container classesList" qArgs="$filter = null, $limit = 200, $classes = null, $url_tag = null">
<?php
	$url_tag = $url_tag ?: $this->url_tag;
	$classes = $classes ?: $this->getClassesList($filter, $limit);
	if ($classes)
	{
		// $webpage = $this->getWebPage();
		foreach ($classes as $class => $path)
		{
			if (is_numeric($class))
			{
				$dr_len = strlen($_SERVER["DOCUMENT_ROOT"]);
				?><li style="color: #243e85; font-weight: bold;"><?= substr($path, $dr_len) ?></li><?php
			}
			else
			{
				if (substr($class, -strlen("_GenTrait")) === "_GenTrait")
					continue;

				$short_class = (($p = strrpos($class, "\\")) !== false) ? substr($class, $p + 1) : $class;
				$namespace = ($p !== false) ? substr($class, 0, $p) : null;
				$url = $this->getLink($url_tag, $class);
				
				if (($url === null) || ($url === false)):
					?><li data-classname="<?= strtolower($class) ?>" data-oclassname="<?= $class ?>"><a><?= $short_class ?></a><?= $namespace ? "<small>{$namespace}</small>" : "" ?></li><?php
				else:
					?><li data-classname="<?= strtolower($class) ?>" data-oclassname="<?= $class ?>"><a href="<?= $url ?>"><?= $short_class ?></a><?= $namespace ? "<small>{$namespace}</small>" : "" ?></li><?php
				endif;
			}
		}
	}
?>
</ul>