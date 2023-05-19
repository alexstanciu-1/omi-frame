<head q-args="$branding = null">
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

    <?php 
        $parts = [];
        parse_str($_SERVER["QUERY_STRING"], $parts);
        $q = $parts["__or__"];
    ?>

    <title>H2B - Hotel2Business<?= $q ? " - ".$q : "" ?></title>
    <base href="<?= BASE_HREF; ?>" />

    @if (($favIcon = ($branding ? $branding->getFullPath('FavIcon') : null)) && file_exists($favIcon) && is_file($favIcon))
        @php $size = getimagesize($favIcon);
        @php $type = $size ? $size['mime'] : null;
        @if ($type)
            <link rel="icon" type="{{$type}}" href="{{$favIcon}}" />
        @endif
    @endif

    <?php if (file_exists(\QAutoload::GetRuntimeFolder()."temp/js_paths.js")) : ?>
            <script type="text/javascript" src="<?= \QAutoload::GetMainFolderWebPath() ?>temp/js_paths.js"></script>
    <?php endif; ?>

    <script type="text/javascript" src="<?= Q_FRAME_REL; ?>view/js/stacktrace.js"></script>
    <script type="text/javascript" src="<?= Q_FRAME_REL; ?>view/js/phpjs.js"></script>
    <script type="text/javascript" src="<?= Q_FRAME_REL; ?>view/js/jquery-3.6.3.min.js"></script>

    <script type="text/javascript" src="<?= Q_FRAME_REL; ?>view/js/functions.js"></script>
    <script type="text/javascript" src="<?= \QAutoload::GetTempWebPath("model_type.js"); ?>"></script>

    <script type="text/javascript" src="<?= Q_FRAME_REL; ?>base/QObject.js"></script>
    <script type="text/javascript" src="<?= Q_FRAME_REL; ?>view/js/mvvm.js"></script>

    <script type="text/javascript" src="<?= Q_VIEW_RES; ?>flexissframe/js/mvvm2.js"></script>
    <script type="text/javascript" src="<?= Q_VIEW_RES; ?>flexissframe/js/basic-controls.js"></script>
	
	<script async tag="init-vars" type="text/javascript">
		<?php if (file_exists(($imgCfgFile = Q_VIEW_IMG_RESPONSIVE . "img.responsive.config.json"))) : ?>
			window.IMGSRESIZECFG = <?= file_get_contents($imgCfgFile) ?>;
		<?php endif; ?>
	</script>
	<script type="text/javascript" src="<?= Q_VIEW_IMG_RESPONSIVE . 'img.responsive.js' ?>"></script>
    
    <!-- <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,500;0,700;0,900;1,400&display=swap" rel="stylesheet" /> -->
    <!-- <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;500;700;900&display=swap" rel="stylesheet" /> -->
	<link rel="preconnect" href="https://fonts.googleapis.com" />
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
	<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />

    <!-- CSS
    ============================================================================ -->
    <link href="https://unpkg.com/tailwindcss@2.1.1/dist/tailwind.min.css" rel="stylesheet" />
    <link href="<?= Q_VIEW_RES ?>main/js/trumbowyg/css/trumbowyg.min.css" rel="stylesheet" />		
    <link href="<?= Q_VIEW_RES ?>main/js/tyippy/css/tippy.css" rel="stylesheet" />
    <link href="<?= Q_VIEW_RES ?>main/js/splide/css/splide.core.min.css" rel="stylesheet" />		
    <link href="<?= Q_VIEW_RES ?>main/js/splide/css/splide-default.min.css" rel="stylesheet" />		
    <link href="<?= Q_VIEW_RES ?>main/js/nouislider/nouislider.all.min.css" rel="stylesheet" />		
    <link href="<?= Q_VIEW_RES ?>main/js/fancybox/fancybox.min.css" rel="stylesheet" />		
	<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.css" integrity="sha512-/zs32ZEJh+/EO2N1b0PEdoA10JkdC3zJ8L5FTiQu82LR9S/rOQNfQN7U59U9BC12swNeRAz3HSzIL2vpp4fv3w==" crossorigin="anonymous" /> -->
    <link href="<?= Q_VIEW_RES ?>main/css/custom.css?i=<?= uniqid() ?>" rel="stylesheet" />
	<link href="<?= Q_VIEW_RES ?>main/js/flatpickr/flatpickr.min.css" rel="stylesheet" />
</head>