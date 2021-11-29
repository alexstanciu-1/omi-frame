<!DOCTYPE html>
<html lang="en" extends="QWebPage">
	  <head>
		<!-- Meta, title, CSS, favicons, etc. -->
		<meta charset="utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<base href="<?= BASE_HREF ?>" />
		<title><?= $this->htmlTitle ?: "OMIFrame Development Specs - by OMIBIT.com" ?></title>
		<meta name="description" content="<?= $this->htmlMetaDescription ?: "OMIFrame PHP is about productive, fast, simple, yet surprisingly powerful and innovative" ?>" />
		<meta name="keywords" content="<?= $this->htmlMetaKeywords ?: "php framework object-oriented productive fast powerful innovative" ?>" />
		<meta name="author" content="Alex Stanciu" />
		<!-- Latest compiled and minified CSS -->
		<!-- <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css" /> -->
		
		<!-- Optional theme -->
		<!-- <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap-theme.min.css" /> -->
		
		<link href="//netdna.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.css" rel="stylesheet" />
				
		<script src="//code.jquery.com/jquery-2.1.4.js"></script>
		
		<!-- <link rel="stylesheet" href="style/style.css" /> -->
		
		<?php if (file_exists(QAutoload::GetRuntimeFolder()."temp/js_paths.js")):
				?><script type="text/javascript" src="<?= Q_APP_REL ?>code/temp/js_paths.js"></script><?php endif; ?>
		<script type="text/javascript" src="<?= Q_FRAME_REL; ?>view/js/stacktrace.js"></script>
		<script type="text/javascript" src="<?= Q_FRAME_REL; ?>view/js/phpjs.js"></script>
		<script type="text/javascript" src="<?= Q_FRAME_REL; ?>view/js/functions.js"></script>
		<script type="text/javascript" src="<?= \QAutoload::GetTempWebPath("model_type.js"); ?>"></script>
		<script type="text/javascript" src="<?= Q_FRAME_REL; ?>base/QObject.js"></script>
	<script type="text/javascript" src="<?= Q_FRAME_REL; ?>view/js/mvvm.js"></script>
				
	</head>
	<body><?php
	
		$this->renderBody();
		$this->renderCallbacks();
		
	?>
	</body>
</html>