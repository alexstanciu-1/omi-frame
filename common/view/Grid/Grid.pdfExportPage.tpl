<!DOCTYPE html>
<html>
	<head>
		<base href="<?= \QWebRequest::GetBaseUrl() ?>" />
		<link href="<?= Omi_Mods_Path ?>res/css/omi-css/normalize.min.css" rel="stylesheet" />
		<link href="<?= Omi_Mods_Path ?>res/css/omi-css/grid-12.min.css" rel="stylesheet" />
		<link href="<?= Omi_Mods_Path ?>res/css/omi-css/omi-normalize.min.css" rel="stylesheet" />
		<link href="<?= Omi_Mods_Path ?>res/css/omi-css/basic.css" rel="stylesheet" />
		<link href='<?= \QWebPage::Cache_Font('https://fonts.googleapis.com/css?family=Roboto:300,400,500') ?>' rel='stylesheet' type='text/css' />
		<link href="<?= Q_APP_REL ?>code/res/css/color_theme.css?i=<?= uniqid() ?>" rel="stylesheet" />	
		<script type="text/javascript" src="<?= Q_FRAME_REL; ?>view/js/stacktrace.js"></script>
		<script type="text/javascript" src="<?= Q_FRAME_REL; ?>view/js/phpjs.js"></script>
		<script type="text/javascript" src="<?= Q_FRAME_REL; ?>view/js/jquery-3.6.3.min.js"></script>
		<script type="text/javascript" src="<?= Q_FRAME_REL; ?>view/js/functions.js"></script>
		<script type="text/javascript" src="<?= \QAutoload::GetTempWebPath("model_type.js"); ?>"></script>
		<script type="text/javascript" src="<?= Q_FRAME_REL; ?>base/QObject.js"></script>
		<script type="text/javascript" src="<?= Q_FRAME_REL; ?>view/js/mvvm.js"></script>
		<link href="<?= Omi_Mods_Path ?>common/res/export/css/pdf_export.css" rel="stylesheet" />
	</head>
	<body>
		@include($this::render)
		<?php $this->renderCallbacks() ?>
	</body>
</html>