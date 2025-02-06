<?php

	$url = isset($_GET["url"]) ? $_GET["url"] : null;
	if (!$url)
		die('No action @pdfg.php');

	$cover = isset($_GET["cover"]) ? $_GET["cover"] : null;
	$header = isset($_GET["header"]) ? $_GET["header"] : null;
	$footer = isset($_GET["footer"]) ? $_GET["footer"] : null;
	$hfc = isset($_GET["hfc"]) ? $_GET["hfc"] : null;
	$ffc = isset($_GET["ffc"]) ? $_GET["ffc"] : null;
	$orientation = (isset($_GET["orientation"]) && $_GET["orientation"]) ? $_GET["orientation"] : "Landscape";
	$noMargins = isset($_GET["nomargins"]);

	generatePdfFromCmd($url, $orientation, $cover, $header, $footer, $hfc, $ffc, !$noMargins);

	function generatePdfFromCmd($url, $orientation = null, $cover = null, $header = null, $footer = null, $hfc = null, $ffc = null, $setMargins = true)
	{
		if (!$orientation)
			$orientation = "Landscape";

		$f_name = uniqid().".pdf";
		$dir = dirname(__FILE__)."/temp/";
		if (!is_dir($dir))
		{
			try {
				mkdir($dir, 0777);
			}
			catch(Exception $ex)
			{
				throw $ex;
			}
			
			try {
				chmod($dir, 0777);
			}
			catch(Exception $ex)
			{
				throw $ex;
			}
		}
		$f_path = $dir.$f_name;
		$f_path = escapeshellcmd($f_path);
		$f_path = preg_replace("/\\\~/", "~", $f_path);

		$list = array($url, $cover, $header, $hfc, $ffc);
		for ($i = 0; $i < count($list); $i++)
		{
			$list[$i] = escapeshellcmd($list[$i]);
			$list[$i] = preg_replace("/\\\~/", "~", $list[$i]);
		}
		list($url, $cover, $header, $hfc, $ffc) = $list;


		$margins = $setMargins ? " --margin-top 12 --margin-bottom 12 --margin-left 12 --margin-right 12" : " --margin-top 0 --margin-bottom 0 --margin-left 0 --margin-right 0";

		//$command = "wkhtmltopdf{$margins} --page-size A4 --orientation {$orientation}".($cover ? "cover {$cover} " : "").
		# $command = "wkhtmltopdf{$margins} --print-media-type --page-size A4 --disable-smart-shrinking --orientation {$orientation}".($cover ? " cover {$cover}" : "").
		$command = "wkhtmltopdf {$margins} --print-media-type --page-size A4 --orientation {$orientation}".($cover ? " cover {$cover}" : "").
			($hfc ? " --header-html {$hfc}" : "") . 
			($ffc ? " --footer-html {$ffc}" : "") .
			" {$url}" .
			($header ? " --header-html {$header} --header-spacing 2" : "") .
			($footer ? " --footer-html {$footer} --footer-spacing 2" : "")." ".$f_path;

		$out = null;
		$return_var = null;
		//echo exec('whoami')."<br/>";
		$ex = exec($command, $out, $return_var);
		
		if ($return_var > 0)
		{
			//die("There was a problem and the pdf file was not generated!");
		}

		# var_dump($command, $return_var);
		# die('stopped!');

		header('Content-Description: File Transfer');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-type: application/pdf');
		header('Content-Disposition: attachment; filename="'.$f_name.'"');
		header('Content-Length: '.filesize($f_path));

		readfile($f_path);
		unlink($f_path);
	}