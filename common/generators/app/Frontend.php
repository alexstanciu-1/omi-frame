<?php

namespace Omi\Gens;

class Frontend implements IGenerator
{
	public static $ForCollection = true;
	public static $DefaultClassParent = "Omi\View\Listing";
	
	public static function GenerateConfig($watch_folder, $batch_id, $className, $prop_name, $namespace, $prefix, $autosync, $autosyncadd, $autosyncremove, $include_properties)
	{
		// var_dump($watch_folder, $batch_id, $className, $prop_name, $namespace, $prefix, $autosync, $autosyncadd, $autosyncremove, $include_properties);
		/*
			frontend
				-> controller (just the main one)
				-> view (main page)
		 */
		$controller = Controller::GenerateConfig($watch_folder, $batch_id, $className, $prop_name, $namespace, $prefix, $autosync, $autosyncadd, $autosyncremove, $include_properties);
		$controller = reset(reset($controller));
		
		$controller["className"] = $namespace."\\Frontend";
		$controller["classPath"] = $watch_folder;
		$controller["name"] = "Frontend";
		
		$controller["urls"]["index"] = ["load" => ""];
		$controller["urls"]["notfound"] = ["load" => "var_dump('not found page');"];
		
		$controller["prefix"] = "(\\QModel::GetLanguage_Dim() === \\QModel::GetDefaultLanguage_Dim()) ? \"\" : \\QModel::GetLanguage_Dim()";
		
		/* <get param.0="lang" noprefix><?= ($lang === reset(CmsApp::$Languages)) ? "" : $lang; ?></get> */
		$controller["urls"]["url-lang"] = [
								"tag" => "lang",
								"get" => "<get param.0=\"lang\" noprefix><?= (\$lang === \\QModel::GetDefaultLanguage_Dim()) ? \"\" : \$lang; ?></get>",
							];
		
		$controller["load"] = "if (in_array(\$url->current(), \\QModel::GetLanguages_Dim()))\n".
									"\t\t{\n".
									"\t\t\t\\QModel::SetLanguage_Dim(\$url->current());\n".
									"\t\t\t\$url->next();\n".
									"\t\t}\n".
								"\t\t\$this->webPage = new View\\Frontend();";
		
		$controller["unload"] = 
				"if ((!\QWebRequest::IsFastAjax()) && \$this->webPage)
				{
					\$this->webPage->init();
					\$this->webPage->execQB();
					\$this->webPage->render();
				}
		";
		
		/* <prefix><?= (CmsApp::$Language_Dim === CmsApp::$DefaultLanguage_Dim) ? "" : CmsApp::$Language_Dim; ?></prefix> */
		
		
		$menu = Menu::GenerateConfig($watch_folder, $batch_id, $className, $prop_name, $namespace, $prefix, $autosync, $autosyncadd, $autosyncremove, $include_properties);
		$menu = reset(reset($menu));
		
		$menu["name"] = "Menu";
		$menu["className"] = $namespace."\\View\\Menu";
		$menu["classPath"] = $watch_folder."view/Menu/";
		$menu["controllerClass"] = $controller["className"];
	
		$page = [];
		$page["className"] = $namespace."\\View\\Frontend";
		$page["classPath"] = $watch_folder."view/Frontend/";
		$page["generator"] = get_called_class();
		$page["menuClass"] = $menu["className"];
		$page["controllerClass"] = $controller["className"];
		
		// the menu has options based on the controller
		
		$controller["generatedFor"] = $prop_name ? $className."::".$prop_name : $className."";
		$page["generatedFor"] = $prop_name ? $className."::".$prop_name : $className."";
		$menu["generatedFor"] = $prop_name ? $className."::".$prop_name : $className."";
		
		
		$return = [];
		$return[$controller["className"]][$controller["generator"]] = $controller;
		$return[$page["className"]][$page["generator"]] = $page;
		$return[$menu["className"]][$menu["generator"]] = $menu;
		
		return $return;
		// "name" => "Frontend", "generator" => get_called_class(), "controller" => $controller, "page" => $page, "menu" => $menu
	}
	
	public static function Generate($config)
	{
		/*
		$controller = $config["controller"];
		if ($controller)
			$controller["generator"]::Generate($controller);
		$menu = $config["menu"];
		if ($menu)
			$menu["generator"]::Generate($menu);
		*/
		
		// this we handle here
		$page = $config;
		$page_path = $page["classPath"];
		$page_class = $page["className"];
		
		$menu_className = $config["menuClass"];
		$controllerClass = $page["controllerClass"];
		
		$extends = "\\QWebPage";
		
		list($page_short_class, $page_namespace) = qClassShortAndNamespace($page_class);
		
		$tpl_str = 
"<!doctype html>
<html".($extends ? " extends=\"{$extends}\"" : "").($page_namespace ? " q-namespace=\"".htmlspecialchars($page_namespace)."\"" : "").">
	<head>
		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/>
		<title></title>
		<base href=\"<?= BASE_HREF; ?>\" />
		<link href=\"<?= Q_FRAME_REL; ?>view/css/grid-12.css\" rel=\"stylesheet\" />
		<link href=\"<?= Q_FRAME_REL; ?>view/css/omi-normalize.css\" rel=\"stylesheet\" />
		<?php if (file_exists(\QAutoload::GetRuntimeFolder().\"temp/js_paths.js\")):
				?><script type=\"text/javascript\" src=\"<?= Q_APP_REL ?>code/temp/js_paths.js\"></script><?php endif; ?>
		<script type=\"text/javascript\" src=\"<?= Q_FRAME_REL; ?>view/js/stacktrace.min.js\"></script>
		<script type=\"text/javascript\" src=\"<?= Q_FRAME_REL; ?>view/js/phpjs.min.js\"></script>
		<script type=\"text/javascript\" src=\"<?= Q_FRAME_REL; ?>view/js/jquery-1.11.2.min.js\"></script>
		<script type=\"text/javascript\" src=\"<?= Q_FRAME_REL; ?>view/js/functions.min.js\"></script>
		<script type=\"text/javascript\" src=\"<?= Q_FRAME_REL; ?>base/QObject.min.js\"></script>
	</head>
	<body>
		<?php
			\$this->renderBody();
			\$this->renderCallbacks();
		?>
	</body>
</html>
";
		
		$tpl_str_body = 
"<div class=\"main-wrap container\">
	<div class=\"header row\">
		<a href=\"{{\\{$controllerClass}::GetUrl()}}\">LOGO</a>
		@if (\QModel::GetLanguages_Dim())
			<div class='lang-select'>
				<span>Language:</span>
				<ul class='lang-selector'>
					@each (\\QModel::GetLanguages_Dim() as \$langauage)
						@if (\\QModel::GetLanguage_Dim() === \$langauage)
							<li>{{\$langauage}}</li>
						@else
							<li><a href='{{\\{$controllerClass}::GetUrl(\"lang\", \$langauage)}}'>{{\$langauage}}</a></li>
						@endif
					@endeach;
				</ul>
			</div>
		@endif;
	</div>
	<div class=\"menu row\">
		<?php if (\$this->menu) \$this->menu->render(); ?>
	</div>
	<div class=\"container\">
		<div class=\"left-panel col-16\">
			<?php if (\$this->leftPanel) \$this->leftPanel->render(); ?>
			left panel
		</div>
		<div class=\"content col-fill\">
			<?php if (\$this->content) \$this->content->render(); ?>
		</div>
	</div>
	<div class=\"footer\">
		<?php if (\$this->footer) \$this->footer->render(); ?>
	</div>
</div>";
		
		$css_str = 
"
.main-wrap {
	width: 96rem;
	margin: 0 auto;
}
.lang-select {
	float: right;
}
.lang-select ul {
	list-style: none;
	display: inline;
	padding: 0;
	margin: 0;
}
.lang-select li {
	display: inline;
	margin: 0;
	padding: 0;
}
.lang-select li a {
	
}
.lang-select span {
	
}

";
		
		$php_str = 
"<?php
".($page_namespace ? "\nnamespace {$page_namespace};\n" : "")."
class {$page_short_class}".($extends ? " extends {$extends}" : "")."
{
	use Frontend_GenTrait;
	
	public function init(\$recursive = true)
	{
		\$this->menu = \$menu = new ".qClassRelativeToNamespace($menu_className, $page_namespace)."();
		\$this->addControl(\$menu, \"menu\");
		
		parent::init(\$recursive);
	}
}
";
		
		if (!is_dir($page_path))
			qmkdir($page_path);
		\QCodeSync::filePutContentsIfChanged($page_path.$page_short_class.".php", $php_str);
		\QCodeSync::filePutContentsIfChanged($page_path.$page_short_class.".tpl", $tpl_str);
		\QCodeSync::filePutContentsIfChanged($page_path.$page_short_class.".body.tpl", $tpl_str_body);
		\QCodeSync::filePutContentsIfChanged($page_path.$page_short_class.".css", $css_str);
	}
}