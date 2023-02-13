<!doctype html>
<html>
	<head>
		<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=utf-8"/>
		<title></title>
		<base href="<?= BASE_HREF; ?>" />
		
		<?php
			
			# unsafe output starts
			echo "it's a start";
			if (file_exists(QAutoload::GetRuntimeFolder()."temp/js_paths.js"))
			{
				echo "this is nuts";
				# unsafe output ends
				?><script type="text/javascript" src="<?= QAutoload::GetMainFolderWebPath() ?>temp/js_paths.js"></script><?php
				# unsafe output starts
			}
			else
			{
				echo "crazy joe";
				# unsafe output ends
				?><!-- missing temp/js_paths.js --><?php
				# unsafe output starts
				echo "crazy penny";
			}
			# unsafe output ends
		?>
		<script type="text/javascript" src="<?= Q_FRAME_REL; ?>view/js/stacktrace.js"></script>
		<script type="text/javascript" src="<?= Q_FRAME_REL; ?>view/js/phpjs.js"></script>
		<script type="text/javascript" src="<?= Q_FRAME_REL; ?>view/js/jquery-3.6.3.min.js"></script>
		<script type="text/javascript" src="<?= Q_FRAME_REL; ?>view/js/functions.js"></script>
		<script type="text/javascript" src="<?= Q_FRAME_REL; ?>base/QObject.js"></script>
		<script type="text/javascript" src="<?= Q_FRAME_REL; ?>view/js/mvvm.js"></script>

		<script type="text/javascript">
			<?php echo "var QApp = {\"DataClass\" : ". (QApp::Data() ? "\"".get_class(QApp::Data())."\"" : "null")." , \"DataId\" : ".(QApp::Data() ? "\"".QApp::Data()->getId()."\"" : "null")."};"; ?>
		</script>
		<!-- <script type="text/javascript" src="<?= Q_FRAME_REL; ?>view/js/init.js"></script> -->
	</head>
	<body>
		<?php
		
			if ($this->children)
			{
				foreach ($this->children as $child)
					$child->render();
			}
			else
				$this->renderBody();
			
			$this->renderCallbacks();
		?>
	</body>
</html>