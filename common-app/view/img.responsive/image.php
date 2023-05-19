<?php

function ImageCreateFromType($type,$filename) {
	$im = null;
	switch ($type) {
		case 1:
			$im = ImageCreateFromGif($filename);
			break;
		case 2:
			$im = ImageCreateFromJpeg($filename);
			break;
		case 3:
			$im = ImageCreateFromPNG($filename);
	}
	return $im;
}

function save_image($type, $im, $filename, $quality, $to_file = true) {
	$res = null;
	if(!function_exists('imagegif')) $type = 3;
	switch ($type) {
		case 1:
			$res = ImageGIF($im,$filename);
			break;
		case 2:
			$res = ImageJPEG($im,$filename,$quality);
			break;
		case 3:
			if (PHP_VERSION >= '5.1.2')
			{
				$quality = 9 - min( round($quality / 10), 9 );
				$res = ImagePNG($im, $filename, $quality);
			}
			else
				$res = ImagePNG($im, $filename);
    }
    @imagedestroy($im);
}

function make_thumbnail($file, $width = 150, $height = 150, $images_folder = "images", $trim_canvas = false, 
	$fill_color = true, $default_image = "na.gif", $default_folder = "images", $color_fill = array (255, 255, 255),
	$trim_position = "center center")
{
	// echo "in $file $images_folder";
	// var_dump($trim_canvas);
	// print_r($images_folder . "/" . ltrim($file, "/"));
// echo "nooot found : ".rtrim($images_folder, "/") . "/" . ltrim($file, "/");
	if ((strlen($file) < 1) || (!is_file(rtrim($images_folder, "/") . "/" . ltrim($file, "/"))))
	{
		$file = $default_image;
		$images_folder = $default_folder;
	}
	
	if ((strlen($file) < 1) || (!file_exists(rtrim($images_folder, "/") . "/" . ltrim($file, "/"))))
	{
		// var_dump(rtrim($images_folder, "/") . "/" . ltrim($file, "/"));
		$images_folder = "../" . $images_folder;
		if ((strlen($file) < 1) || (!file_exists(rtrim($images_folder, "/") . "/" . ltrim($file, "/"))))
			trigger_error("make_thumbnail :: File not found (<b>$file</b> in folder <b>$images_folder</b>).", E_USER_ERROR);
	}

	if ($fill_color)
		$trim_canvas = false;

	if (file_exists($images_folder . "/thumbs" . $width . "x" . $height . "/" . $file))
		return $images_folder . "/thumbs" . $width . "x" . $height . "/" . $file;
		
	if (!file_exists($images_folder . "/thumbs" . $width . "x" . $height . "/" . $file))
	{
		//var_dump(debug_backtrace());
		//var_dump($images_folder . "/thumbs" . $width . "x" . $height . "/" . $file);
		list($orig_width, $orig_height, $type, $attr) = GetImageSize($images_folder . "/" . $file);
		$orig_img_path = $images_folder . "/" . $file;
		
		if ($orig_width < $width)
			$width = $orig_width;
		if ($orig_height < $height)
			$height = $orig_height;
		
		if (($orig_width == $width) && ($orig_height == $height))
		{
			return $orig_img_path;
		}

		if (($type < 1) || ($type > 3))
		{
			return $images_folder . "/thumbs" . $width . "x" . $height . "/" . $default_image;
		}
		else
		{
			
			$img = ImageCreateFromType($type, $images_folder . "/" . $file);

			$new_width = $orig_width;
			$new_height = $orig_height;

			$per_x = $new_width / $width;
			$per_y = $new_height / $height;

			$pos_x = 0;
			$pos_y = 0;
			if ($trim_canvas)
			{
				
				$use_per = $per_y;
				if ($per_y < $per_x)
				{
					$new_width = $new_width / $per_y;
					$new_height = $new_height / $per_y;
					$use_per = $per_y;
				}
				else
				{
					$new_width = $new_width / $per_x;
					$new_height = $new_height / $per_x;
					$use_per = $per_x;
				}
				
				// var_dump($new_width, $new_height);

				list($trim_pos_x, $trim_pos_y) = explode(" ", $trim_position, 2);
				
				$pos_x = ($orig_width - $width*$use_per) / 2;
				$pos_y = ($orig_height - $height*$use_per) / 2;
				
				// var_dump($height, $new_height);

				$canvas_new_width = $width;
				$canvas_new_height = $height;
			}
			else
			{
				if (($new_width >= $width) || ($new_height >= $height))
				{
					$use_per = $per_y;
					if ($per_y > $per_x)
					{
						$new_width = $orig_width / $per_y;
						$new_height = $orig_height / $per_y;
						$use_per = $per_y;
					}
					else
					{
						$new_width = $orig_width / $per_x;
						$new_height = $orig_height / $per_x;
						$use_per = $per_x;
					}

					$pos_x = 0;
					$pos_y = 0;
				}
			}

			// if the original dimensions are smaller than the target ones,
			// the picture is centered and filled with $fillcolor
			if (($new_width > $orig_width) || ($new_height > $orig_height))
			{
				$fill_color = true;
				$trim_canvas = false;
				if ($new_width > $orig_width)
				{
					$pos_x = ($new_width - $orig_width) / 2;
					$new_width = $orig_width;
				}
				if ($new_height > $orig_height)
				{
					$pos_y = ($new_height - $orig_height) / 2;
					$new_height = $orig_height;
				}
			}
			else if ($fill_color)
			{
				$pos_x = abs(($width - $new_width) / 2);
				$pos_y = abs(($height - $new_height) / 2);
			}
			$pos_x = 0;

			if ($image_type == 1)
			{
				if ($trim_canvas || $fill_color)
					$ni = imagecreate($width, $height);
				else
					$ni = imagecreate($new_width, $new_height);
			}
			else
			{
				if ($trim_canvas || $fill_color)
					$ni = ImageCreateTrueColor($width, $height);
				else
					$ni = ImageCreateTrueColor($new_width, $new_height);
			}

			$white = imagecolorallocate($ni, $color_fill[0], $color_fill[1], $color_fill[2]);

			if ($trim_canvas || $fill_color)
				imagefilledrectangle($ni, 0, 0, $width, $height, $white);
			else
				imagefilledrectangle($ni, 0, 0, $new_width, $new_height, $white);
			imagepalettecopy($ni,$img);

			//echo "[$width][$height][".($pos_x)."][".($pos_y)."] | ";
			if ($trim_canvas)
			{
				// echo "trim canvas"
				imagecopyresampled(
					$ni, $img,
					0, 0, $pos_x, $pos_y,
					$width, $height,
					$orig_width - (2 * $pos_x), $orig_height - (2 * $pos_y));
			}
			else
			{
				imagecopyresampled(
					$ni, $img,
					$pos_x, $pos_y, 0, 0,
					$new_width, $new_height,
					$orig_width, $orig_height);
			}
			
			@imagedestroy($img);
			@chmod ($images_folder, 0777);
			if (!file_exists($images_folder . "/thumbs" . $width . "x$height")) {
			@mkdir ($images_folder . "/thumbs" . $width . "x$height");
			}
			@chmod ($images_folder . "/thumbs" . $width . "x$height", 0777);
			if (strpos($file, "/") != -1)
			{
				$new_dir = substr($file, 0, strrpos($file, "/") + 1);
				change_mod($new_dir, $images_folder . "/thumbs" . $width . "x$height");
			}
			
			ob_start();
			save_image($type, $ni, $images_folder . "/thumbs" . $width . "x$height/$file", 100);
			try
			{
				@imagedestroy($ni);
				@imagedestroy($img);
			}
			catch (Exception $ex)
			{
				// echo " ";
			}
			ob_end_clean();
			
			return $images_folder . "/thumbs" . $width . "x$height/$file";
		}
		return $images_folder . "/thumbs" . $width . "x" . $height . "/" . $default_image;
	}
	else
		return $images_folder . "/thumbs" . $width . "x" . $height . "/" . $file;
	/*if (!file_exists($images_folder . "/thumbs" . $width . "x" . $height . "/na.gif"))
		return make_thumbnail("na.gif", $width, $height, $images_folder);
	else
		return */
}

function make_thumbnail2($file, $width = 150, $height = 150, $thumbs_folder = null, $trim_canvas = false, $fill_color = true, $default_image = "na.gif", $default_folder = "images", $color_fill = array (255, 255, 255))
{
	if (!$thumbs_folder)
	{
		$thumbs_folder = dirname($file);
		$file = basename($file);
	}
	return make_thumbnail($file, $width, $height, $thumbs_folder, $trim_canvas, $fill_color, $default_image, $default_folder, $color_fill);
}

function change_mod($dir, $start_dir)
{
	while (strlen($dir) > 0)
	{
		$temp_dir = substr($dir, 0, strpos($dir, "/"));
		$start_dir .= "/$temp_dir";
		if (!file_exists($start_dir)) {
			@mkdir($start_dir);
		}
		@chmod ($start_dir, 0777);
		$dir = substr ($dir, strpos($dir, "/") + 1);
	}
}

if (!function_exists('ImageCreateFromBMP'))
{
	function ImageCreateFromBMP($filename)
	{
		//Ouverture du fichier en mode binaire
		if (! $f1 = fopen($filename,"rb")) return FALSE;

		//1 : Chargement des ent�tes FICHIER
		$FILE = unpack("vfile_type/Vfile_size/Vreserved/Vbitmap_offset", fread($f1,14));
		if ($FILE['file_type'] != 19778) return FALSE;

		//2 : Chargement des ent�tes BMP
		$BMP = unpack('Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel'.
					 '/Vcompression/Vsize_bitmap/Vhoriz_resolution'.
					 '/Vvert_resolution/Vcolors_used/Vcolors_important', fread($f1,40));
		$BMP['colors'] = pow(2,$BMP['bits_per_pixel']);
		if ($BMP['size_bitmap'] == 0) $BMP['size_bitmap'] = $FILE['file_size'] - $FILE['bitmap_offset'];
		$BMP['bytes_per_pixel'] = $BMP['bits_per_pixel']/8;
		$BMP['bytes_per_pixel2'] = ceil($BMP['bytes_per_pixel']);
		$BMP['decal'] = ($BMP['width']*$BMP['bytes_per_pixel']/4);
		$BMP['decal'] -= floor($BMP['width']*$BMP['bytes_per_pixel']/4);
		$BMP['decal'] = 4-(4*$BMP['decal']);
		if ($BMP['decal'] == 4) $BMP['decal'] = 0;

		//3 : Chargement des couleurs de la palette
		$PALETTE = array();
		if ($BMP['colors'] < 16777216)
		{
			$PALETTE = unpack('V'.$BMP['colors'], fread($f1,$BMP['colors']*4));
		}

		//4 : Cr�ation de l'image
		$IMG = fread($f1,$BMP['size_bitmap']);
		$VIDE = chr(0);

		$res = imagecreatetruecolor($BMP['width'],$BMP['height']);
		$P = 0;
		$Y = $BMP['height']-1;
		while ($Y >= 0)
		{
			$X=0;
			while ($X < $BMP['width'])
			{
				if ($BMP['bits_per_pixel'] == 24)
					$COLOR = unpack("V",substr($IMG,$P,3).$VIDE);
				elseif ($BMP['bits_per_pixel'] == 16)
				{ 
					$COLOR = unpack("n",substr($IMG,$P,2));
					$COLOR[1] = $PALETTE[$COLOR[1]+1];
				}
				elseif ($BMP['bits_per_pixel'] == 8)
				{ 
					$COLOR = unpack("n",$VIDE.substr($IMG,$P,1));
					$COLOR[1] = $PALETTE[$COLOR[1]+1];
				}
				elseif ($BMP['bits_per_pixel'] == 4)
				{
					$COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1));
					if (($P*2)%2 == 0) $COLOR[1] = ($COLOR[1] >> 4) ; else $COLOR[1] = ($COLOR[1] & 0x0F);
					$COLOR[1] = $PALETTE[$COLOR[1]+1];
				}
				elseif ($BMP['bits_per_pixel'] == 1)
				{
					$COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1));
					if     (($P*8)%8 == 0) $COLOR[1] =  $COLOR[1]        >>7;
					elseif (($P*8)%8 == 1) $COLOR[1] = ($COLOR[1] & 0x40)>>6;
					elseif (($P*8)%8 == 2) $COLOR[1] = ($COLOR[1] & 0x20)>>5;
					elseif (($P*8)%8 == 3) $COLOR[1] = ($COLOR[1] & 0x10)>>4;
					elseif (($P*8)%8 == 4) $COLOR[1] = ($COLOR[1] & 0x8)>>3;
					elseif (($P*8)%8 == 5) $COLOR[1] = ($COLOR[1] & 0x4)>>2;
					elseif (($P*8)%8 == 6) $COLOR[1] = ($COLOR[1] & 0x2)>>1;
					elseif (($P*8)%8 == 7) $COLOR[1] = ($COLOR[1] & 0x1);
					$COLOR[1] = $PALETTE[$COLOR[1]+1];
				}
				else
					return FALSE;
				imagesetpixel($res,$X,$Y,$COLOR[1]);
				$X++;
				$P += $BMP['bytes_per_pixel'];
			}
			$Y--;
			$P+=$BMP['decal'];
	   }

		//Fermeture du fichier
	   fclose($f1);
		return $res;
	}
}

if (!function_exists('imageBMP'))
{
	function imageBMP(&$img, $filename = false)
	{
		$wid = imagesx($img);
		$hei = imagesy($img);
		$wid_pad = str_pad('', $wid % 4, "\0");

		$size = 54 + ($wid + $wid_pad) * $hei * 3; //fixed

		//prepare & save header
		$header['identifier']		= 'BM';
		$header['file_size']		= dword($size);
		$header['reserved']			= dword(0);
		$header['bitmap_data']		= dword(54);
		$header['header_size']		= dword(40);
		$header['width']			= dword($wid);
		$header['height']			= dword($hei);
		$header['planes']			= word(1);
		$header['bits_per_pixel']	= word(24);
		$header['compression']		= dword(0);
		$header['data_size']		= dword(0);
		$header['h_resolution']		= dword(0);
		$header['v_resolution']		= dword(0);
		$header['colors']			= dword(0);
		$header['important_colors']	= dword(0);

		if ($filename)
		{
			$f = fopen($filename, "wb");
			foreach ($header AS $h)
			{
				fwrite($f, $h);
			}

			//save pixels
			for ($y=$hei-1; $y>=0; $y--)
			{
				for ($x=0; $x<$wid; $x++)
				{
					$rgb = imagecolorat($img, $x, $y);
					fwrite($f, byte3($rgb));
				}
				fwrite($f, $wid_pad);
			}
			fclose($f);
		}
		else
		{
			foreach ($header AS $h)
			{
				echo $h;
			}

			//save pixels
			for ($y=$hei-1; $y>=0; $y--)
			{
				for ($x=0; $x<$wid; $x++)
				{
					$rgb = imagecolorat($img, $x, $y);
					echo byte3($rgb);
				}
				echo $wid_pad;
			}
		}	
	}
}

function undword($n)
{
	$r = unpack("V", $n);
	return $r[1];
}

function dword($n)
{
	return pack("V", $n);
}

function word($n)
{
	return pack("v", $n);
}

function byte3($n)
{
	return chr($n & 255) . chr(($n >> 8) & 255) . chr(($n >> 16) & 255);	
}

function mkSimpleThumb($file, $width, $quality = 80, $force = true)
{
	$fdir = dirname($file);
	$thumbsDir = rtrim($fdir, "\\/")."/thumbs_{$width}";
	if (!is_dir($thumbsDir))
	{
		try
		{
			mkdir($thumbsDir, 0775);
		} 
		catch (\Exception $ex) {

		}
		
		try
		{
			chmod($thumbsDir, 0775);
		} 
		catch (\Exception $ex) {

		}
	}
	return mkThumb($file, $width, $thumbsDir."/".basename($file), $quality, $force);
}

function makeSimpleMixThumb($file, $width, $height, $firstByWidth = true, $quality = 80, $force = true)
{
	$fdir = dirname($file);
	$thumbsDir = rtrim($fdir, "\\/")."/thumbs_{$width}X{$height}";
	if (!is_dir($thumbsDir))
	{
		try
		{
			mkdir($thumbsDir, 0775);
		} 
		catch (\Exception $ex) {

		}
		try
		{
			chmod($thumbsDir, 0775);
		}
		catch (\Exception $ex) {

		}
	}
	return makeMixThumb($file, $width, $height, $thumbsDir."/".basename($file), $firstByWidth, $quality, $force);
}

function getImageCoordinates($file)
{
	list($imgInp, $isJpeg, $isPng, $isGif, $isBmp) = getImageResFromExtension($file);
	if (!$imgInp)
		return null;
	$_x = imagesx($imgInp);
	$_y = imagesy($imgInp);
	ImageDestroy($imgInp);
	return [$_x, $_y];
}

function makeSmartThumb($file, $width, $height, $quality = 80, $force = true)
{
	$mixThumb = makeSimpleMixThumb($file, $width, $height, true, $quality, $force);
	list($nex_x, $new_y) = getImageCoordinates($mixThumb);
	return ($new_y > $height) ? makeSimpleMixThumb($file, $width, $height, false, $quality, $force) : $mixThumb;
}

function makeMixThumb($file, $width, $height, $destination, $firstByWidth = true, $quality = 80, $force = true)
{
	if (!$file || !file_exists($file))
		return null;
	
	if (file_exists($destination) && !$force)
		return $destination;
	
	list($imgInp, $isJpeg, $isPng, $isGif, $isBmp) = getImageResFromExtension($file);

	if (!$imgInp)
		return $file;

	// Get dimensions
	$_x = imagesx($imgInp);
	$_y = imagesy($imgInp);
	
	$wider = ($_x >= $width);
	$hStreched = ($_y >= $height);

	if (!$wider && !$hStreched)
	{
		copy($file, $destination);
		return $destination;
	}

	// do the resize here
	if ($firstByWidth)
	{
		if ($wider)
		{
			$_newx = $width;
			$_newy = ($_newx / $_x) * $_y;
		}
		else if ($hStreched)
		{
			$_newy = $height;
			$_newx = ($height / $_y) * $_x;	
		}
	}
	else
	{
		if ($hStreched)
		{
			$_newy = $height;
			$_newx = ($height / $_y) * $_x;	
		}
		else  if ($wider)
		{
			$_newx = $width;
			$_newy = ($_newx / $_x) * $_y;
		}
	}

	return generateFile($destination, $imgInp, $_newx, $_newy, $_x, $_y, $isJpeg, $isPng, $isGif, $isBmp, $quality);
}

function getImageResFromExtension($file)
{
	$ext = strtoupper(pathinfo($file, PATHINFO_EXTENSION));
	$imgInp = null;
	
	
	try 
	{
		$size = getimagesize($file);
	} catch (Exception $ex) {

	}
	$imgType = ($size && $size["mime"]) ? end(explode("/", $size["mime"])) : null;
	$isJpeg = (($imgType && ((($tl = strtolower($imgType)) == "jpg") || ($tl == "jpeg"))) || (($ext == "JPG") || ($ext == "JPEG")));
	$isPng = (($imgType && (strtolower($imgType) == "png")) || ($ext == "PNG"));
	$isGif = (($imgType && (strtolower($imgType) == "gif")) || ($ext == "GIF"));
	$isBmp = (($imgType && (strtolower($imgType) == "bmp")) || ($ext == "BMP"));

	// JPEG image
	if ($isJpeg)
		$imgInp = ImageCreateFromJPEG($file);
	// PNG image
	else if ($isPng)
		$imgInp = ImageCreateFromPNG($file);
	// GIF image
	else if ($isGif)
		$imgInp = ImageCreateFromGIF($file);
	else if ($isBmp)
		$imgInp = ImageCreateFromBMP($file);
	return [$imgInp, $isJpeg, $isPng, $isGif, $isBmp];

}

function generateFile($destination, $imgInp, $_newx, $_newy, $_x, $_y, $isJpeg, $isPng, $isGif, $isBmp, $quality)
{
	$output = ImageCreateTrueColor($_newx, $_newy);
	
	imagealphablending($output, false);
	imagesavealpha($output, true);

	ImageCopyResampled($output, $imgInp, 0, 0, 0, 0, $_newx, $_newy, $_x, $_y);

	if ($isJpeg)
		imageJPEG($output, $destination, $quality);
	else if ($isPng)
		imagePNG($output, $destination);
	else if ($isGif)
        imageGIF($output, $destination);
	else if ($isBmp)
		imageBMP($output, $destination);

	ImageDestroy($imgInp);
    ImageDestroy($output);

	return file_exists($destination) ? $destination : null;
}

function mkThumb($file, $width, $destination, $quality = 80, $force = true)
{
	if (!$file || !file_exists($file))
		return null;
	
	if (file_exists($destination) && !$force)
	{
		return $destination;
	}
	
	list($imgInp, $isJpeg, $isPng, $isGif, $isBmp) = getImageResFromExtension($file);
	
	if (!$imgInp)
		return $file;

	// Get dimensions
	$_x = imagesx($imgInp);
	$_y = imagesy($imgInp);

	if ($_x <= $width)
	{
		copy($file, $destination);
		return $destination;
	}

	// do the resize here
	$_newx = $width;
	$_newy = ($_newx / $_x) * $_y;

	return generateFile($destination, $imgInp, $_newx, $_newy, $_x, $_y, $isJpeg, $isPng, $isGif, $isBmp, $quality);
}