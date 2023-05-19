<?php

	

	// run from root

	error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
	ini_set("display_errors", "off");

	chdir("../");
	require_once("config.php");
	require_once("img.responsive/image.php");

	$configFile = "img.responsive/img.responsive.config.json";
	if (!file_exists($configFile))
		notFound();

	$config = json_decode(file_get_contents($configFile), true);

	$file = $_GET["__or__"];
	$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION)) ?: null;
	if (!$ext || !in_array($ext, array("jpg", "jpeg", "png", "gif", "bmp")))
		notFound($config);

	if (($fExists = file_exists($file)))
		returnImage($file, $ext);

	$baseFn = pathinfo($file, PATHINFO_FILENAME);
	$path = pathinfo($file, PATHINFO_DIRNAME);

	$baseNarr = explode(".", $baseFn);
	$width = end($baseNarr);

	$rsImgsDir = end(explode("/", $path));

	if ($rsImgsDir !== $config['images_folder'])
	{
		if (!$fExists)
			notFound($config, $width);
		else
			returnImage($file, $ext);
	}

	if (!file_exists($path))
	{
		try 
		{	
			mkdir($path, 0777);
			chmod($path, 0777);
		}
		catch (Exception $ex) 
		{
			notFound($config, $width);
		}
	}

	//var_dump($width, $config['responsive_sizes']);
	//die();

	if (!in_array($width, $config['responsive_sizes']))
		notFound($config, $width);

	array_pop($baseNarr);
	$fn = implode(".", $baseNarr);

	$fpath = dirname($path)."/".$fn.".".$ext;
	
	$thumb = mkThumb($fpath, $width, $file);

	if (!$thumb)
		notFound($config, $width);

	returnImage($thumb, $ext);

function notFound($config, $width)
{
	if ($config["use_na"] && $config["na_img"])
	{
		if (!in_array($width, $config['responsive_sizes']))		
			returnImage($config["na_img"], "gif");

		$fn = pathinfo($config["na_img"], PATHINFO_FILENAME);
		$path = pathinfo($config["na_img"], PATHINFO_DIRNAME);
		$path = rtrim($path, "\\/")."/".$config['images_folder'];
		$fpath = $path."/{$fn}.{$width}.gif";
		$thumb = mkThumb($config["na_img"], $width, $fpath);
		returnImage($thumb, "gif");
	}

	header("HTTP/1.1 404 Not Found");
	header("Status: 404 Not Found");
	die();
}

function returnImage($image, $ext)
{
	header('Content-Type: image/'.$ext);
	readfile($image);
	die();
}