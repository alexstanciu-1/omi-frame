<?php

namespace Omi\View;


/**
 * Description of Select
 *
 * @author Mihaita
 * @class.name Jwysiwyg
 */
abstract class Jwysiwyg_omi_view_ extends \QWebControl
{
	public static $AcceptedImagesExtensions = "/jpg|jpeg|png|gif|bmp/";
	
	public static $PublicDir = "uploads/images/";

	public static $PublicDirPerms = 0744;
	
	public static $ImagesPerms = 0744;

	/**
	 * @api.enable
	 * 
	 * @param string $image
	 * @param string $filename
	 * @return array
	 */
	public static function UploadImage($image, $filename)
	{
		if (!$image || !$filename)
			return ['success' => false, 'message' => 'A problem occured while trying to upload image!'];

		$publicDir = (self::$PublicDir ? rtrim(self::$PublicDir, "\\/")."/" : "");

		if ($publicDir && !is_dir($publicDir))
		{
			try {
				mkdir($publicDir, self::$PublicDirPerms);
			} catch (\Exception$ex) {
				return ['success' => false, 'message' => 'Public directory not found!'];
			}

			if ($publicDir && is_dir($publicDir))
			{
				try {
					chmod($publicDir, self::$PublicDirPerms);
				} catch (\Exception$ex) {
					
				}
			}
		}

		$fName = pathinfo($filename, PATHINFO_FILENAME);
		$ext = pathinfo($filename, PATHINFO_EXTENSION);

		if (!preg_match(self::$AcceptedImagesExtensions, $ext))
			return ['success' => false, 'message' => 'Extension not accepted!'];

		$imgFullPath = $publicDir.$fName.".".$ext;

		$i = 0;
		while (file_exists($imgFullPath))
			$imgFullPath = $publicDir.$fName."_".(++$i).".".$ext;

		$ifp = fopen($imgFullPath, "wb");
		$data = explode(',', $image);
		fwrite($ifp, base64_decode($data[1])); 
		fclose($ifp);
		
		if (self::$ImagesPerms)
		{
			try {
				chmod($imgFullPath, self::$ImagesPerms);
			} catch (\Exception$ex) {

			}
		}
			
		return ['success' => true, 'message' => $imgFullPath];
	}
}