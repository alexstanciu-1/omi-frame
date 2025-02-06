<?php

namespace Omi\Util;


/**
 * Description of PdfExport
 *
 * @class.name Pdf
 */
abstract class Pdf extends \QModel
{
	const Portrait = 1;
	
	const Landscape = 2;
			
	public static function GetFooter()
	{
		ob_start();
		?>
		<!doctype html>
		<html>
			<head>
				<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
				<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
				<style>
					* {
						font-family: "Open Sans";
					}
				</style>
				<script>
					function subst() 
					{
						var vars={};
						var x=document.location.search.substring(1).split('&');
						for (var i in x) 
						{
							var z=x[i].split('=',2);vars[z[0]] = unescape(z[1]);
						}
						var x=['frompage','topage','page','webpage','section','subsection','subsubsection'];
						for (var i in x) 
						{
							var y = document.getElementsByClassName(x[i]);
							for (var j=0; j<y.length; ++j) y[j].textContent = vars[x[i]];
						}
					}
				</script>
			</head>
			<body onload='subst()'>
				<div>Pagina <span class='page'></span> din <span class='topage'></span></div>
			</body>
		</html>
		<?php
		return ob_get_clean();
	}
	/**
	 * @api.enable
	 * 
	 * @param string $content
	 * @param string $name
	 * @param string $orientation
	 * @param boolean $withPageNumbers
	 * @param boolean $withMargins
	 */
	public static function Download($content, $name = null, $orientation = null, $withPageNumbers = false, $withMargins = true)
	{
		$tmpPath = \QAutoload::GetTempWebPath();
		$tmpPath = $tmpPath ? rtrim($tmpPath, "\\/")."/" : "";
		list($relPath, $fpath) = self::Export($content, $name, $orientation, $tmpPath.uniqid().".pdf", $withPageNumbers, $withMargins);
		header('Content-Description: File Transfer');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-type: application octet-stream');
		header('Content-Disposition: attachment; filename="'.addslashes($name).'"');
		readfile($fpath, false, stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]));
		unlink($relPath);
		die();
	}
	/**
	 * @api.enable
	 * 
	 * @param string $content
	 * @param string $name
	 * @param string $orientation
	 * @param boolean $withPageNumbers
	 * @param boolean $withMargins
	 * @return array
	 */
	public static function GetFileData($content, $name = null, $orientation = null, $withPageNumbers = false, $withMargins = true)
	{
		$tmpPath = \QAutoload::GetTempWebPath();
		$tmpPath = $tmpPath ? rtrim($tmpPath, "\\/")."/" : "";
		if (!$name)
			$name = "Untitled document";
		return self::Export($content, $name, $orientation, $tmpPath.$name.".pdf", $withPageNumbers, $withMargins);
	}

	/**
	 * @api.enable
	 * 
	 * @param string $content
	 * @param string $name
	 * @param string $orientation
	 * @param boolean $withPageNumbers
	 * @param boolean $withMargins
	 * @return string
	 */
	public static function GetDownloadFile($content, $name = null, $orientation = null, $withPageNumbers = false, $withMargins = true)
	{
		list($relPath, $fpath) = self::Export($content, $name, $orientation, null, $withPageNumbers, $withMargins);
		return $fpath;
	}

	/**
	 * @api.enable
	 * 
	 * @param string $content
	 * @param string $name
	 * @param string $orientation
	 * @param string $savePath
	 * @param boolean $withPageNumbers
	 * @param boolean $withMargins
	 
	 * @return type
	 */
	public static function Export($content, $name = null, $orientation = null, $savePath = null, $withPageNumbers = false, $withMargins = true)
	{
		if (!defined("Pdf_GenScript"))
			throw new \Exception("Pdf_GenScript not defined");

		if (!$name)
			$name = "Untitled document";

		if ($orientation && !in_array($orientation, array(static::Portrait, static::Landscape)))
			$orientation = null;
		
		if ($orientation)
			$orientation = ($orientation === static::Portrait) ? "Portrait" : "Landscape";
		
		if (is_array($content))
			list($content, $header, $footer, $cover) = $content;
		
		if ($cover && is_array($cover))
			list($cover, $headerForCover, $footerForCover) = $cover;
		
		$covurl = $hurl = $furl = $hfc = $ffc = null;

		$tmpPath = \QAutoload::GetTempWebPath();
		$tmpPath = $tmpPath ? rtrim($tmpPath, "\\/")."/" : "";
		$file = $tmpPath.uniqid().".html";
		$pFile = $tmpPath."print_".uniqid().".php";

		file_put_contents($file, $content);

		$base = \QWebRequest::GetBaseUrl();
		$url = $base.$file;
	
		$fpage = null;
		if ($withPageNumbers && !$footer)
		{
			$fpage = $tmpPath.uniqid().".html";
			file_put_contents($fpage, self::GetFooter());
			$furl = $base.$fpage;
		}
		
		$coverPage = null;
		if ($cover)
		{
			$coverPage = $tmpPath.uniqid().".html";
			# $coverPage = $tmpPath."cover.html";
			file_put_contents($coverPage, $cover);
			$covurl = $base.$coverPage;
		}
		
		$footerPage = null;
		if ($footer)
		{
			$footerPage = $tmpPath.uniqid().".html";
			file_put_contents($footerPage, $footer);
			$furl = $base.$footerPage;
		}
		
		$headerPage = null;
		if ($header)
		{
			$headerPage = $tmpPath.uniqid().".html";
			file_put_contents($headerPage, $header);
			$hurl = $base.$headerPage;
		}

		$headerForCoverPage = null;
		if ($headerForCover)
		{
			$headerForCoverPage = $tmpPath.uniqid().".html";
			file_put_contents($headerForCoverPage, $headerForCover);
			$hfc = $base.$headerForCoverPage;
		}

		$footerForCover = null;
		if ($footerForCover)
		{
			$footerForCover = $footerForCover.uniqid().".html";
			file_put_contents($footerForCover, $footerForCover);
			$ffc = $base.$footerForCover;
		}

		$genFile = Pdf_GenScript."?url=".urlencode($url).
			($furl ? "&footer=".urlencode($furl) : "").
			($hurl ? "&header=".urlencode($hurl) : "").
			($covurl ? "&cover=".urlencode($covurl) : "").
			($hfc ? "&hfc=".urlencode($hfc) : "").
			($ffc ? "&ffc=".urlencode($ffc) : "").
			($orientation ? "&orientation=".urlencode($orientation) : "").
			(!$withMargins ? "&nomargins=true" : "");
		
		if (!$savePath)
		{
			$data = "<?php\n".
					"	header('Content-Description: File Transfer');\n".
					"	header('Content-Transfer-Encoding: binary');\n".
					"	header('Expires: 0');\n".
					"	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');\n".
					"	header('Pragma: public');\n".
					"	header('Content-type: application octet-stream');\n".
					"	header(\"Content-Disposition: attachment; filename=\\\"".addslashes($name)."\\\"\");\n".
					"	//header('Content-Length: ".filesize($file)."');\n".
					"\n	readfile(\"".$genFile."\", false, stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]));\n".
					"\n unlink(\"".addslashes(basename($file))."\");\n".
					($fpage ? "\n unlink(\"".addslashes(basename($fpage))."\");\n" : "").
					($coverPage ? "\n unlink(\"".addslashes(basename($coverPage))."\");\n" : "").
					($footerPage ? "\n unlink(\"".addslashes(basename($footerPage))."\");\n" : "").
					($headerPage ? "\n unlink(\"".addslashes(basename($headerPage))."\");\n" : "").
					($headerForCoverPage ? "\n unlink(\"".addslashes(basename($headerForCoverPage))."\");\n" : "").
					($footerForCover ? "\n unlink(\"".addslashes(basename($footerForCover))."\");\n" : "").
					"	unlink(__FILE__);\n".
					"?>";

			file_put_contents($pFile, $data);
			$savePath = [$pFile, $base.$pFile];
		}
		else
		{
			ob_start();
			readfile($genFile, false, stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]));
			$str = ob_get_clean();
			file_put_contents($savePath, $str);
			unlink($file);
			if ($fpage)
				unlink($fpage);
			if ($coverPage)
				unlink($coverPage);
			if ($footerPage)
				unlink($footerPage);
			if ($headerPage)
				unlink($headerPage);
			if ($headerForCoverPage)
				unlink($headerForCoverPage);
			if ($footerForCover)
				unlink($footerForCover);
			$savePath = [$savePath, $base.$savePath];
		}
		return $savePath;
	}
}