<?php

if (!defined('Q_USE_XSS_INPUT_PROTECTION'))
	throw new \Exception('Specify Q_USE_XSS_INPUT_PROTECTION');

# secure cookies, do not allow javscript to access it
ini_set('session.cookie_httponly', 1);
# Uses a secure connection (HTTPS) if possible
# ini_set('session.cookie_secure', 1);

const Q_SECURITY_SAFE_FOLDERS = [
	'code' => ['res' => true],
	'temp' => true,
	'api' => true,
	'uploads' => true,
];

(function (){

	error_reporting( error_reporting() &  ~ (E_WARNING | E_NOTICE) );

	$admin_ip = dev_ip;
	
	$domain_to_instance = [
		'nfon-02.voipfuse.com' => 'nfon-uk',
		'nfon.voipfuse.com' => 'nfon-uk',
		'cloudpbx.voipfuse.com' => 'nfon-uk',
		'nfon-2020.voipfuse.com' => 'nfon-uk',
		'www.buycomms.com' => 'nfon-uk',
		'www.buycomms.co.uk' => 'nfon-uk',
		'buycomms.com' => 'nfon-uk',
		'buycomms.co.uk' => 'nfon-uk',
		'omt-de-2023.nfon.com' => 'nfon-germany',
		'omt-fr-2023.nfon.com' => 'nfon-fr',
		'omt.nfon.com' => 'nfon-germany',
		'omt-fr.nfon.com' => 'nfon-fr',
		'omt-uk.nfon.com' => 'nfon-uk',
	];
	
	$default_instance = $domain_to_instance[$_SERVER['HTTP_HOST']] ?? null;
	
	list ($or, $lc_ext, $ext, $mime, $or_dir) = q_security_extract_request_info();
	
	# multimode
	if (defined('Q_CODE_MULTI_INSTANCE_FOLDER')) # multimode
	{
		# define('Q_CODE_MULTI_INSTANCE_FOLDER', __DIR__ . "/../voip-fuse/voip-fuse/~instances/");
		if (!defined('Q_CODE_DIR'))
			define('Q_CODE_DIR', realpath('../voip-fuse/').'/');
		
		if (!is_dir(Q_CODE_MULTI_INSTANCE_FOLDER))
			die('Missing instances dir.');
		if (empty($or) && (!$default_instance))
		{
			if ($_SERVER['REMOTE_ADDR'] === $admin_ip)
			{
				$opts = scandir(Q_CODE_MULTI_INSTANCE_FOLDER);
				?><!doctype html><html><body><pre><?php
				foreach ($opts as $o)
				{
					if (($o === '.') || ($o === '..') || (!preg_match("/^[\\w\\d\\_\\-]+\$/uis", $o)))
						continue;
					echo "<a href='/",htmlentities($o),"/'>",htmlentities($o),"</a>\n";
				}
				?></pre></body></html><?php
				exit;
			}
			else
			{
				die('No instance selected.');
			}
		}
		else
		{
			if ($default_instance)
			{
				$instance = $default_instance;
				$orig_req = $or;
			}
			else
			{
				list ($instance, $orig_req) = preg_split("/\\//uis", $or, 2, PREG_SPLIT_NO_EMPTY);
				if (empty($instance))
					die('Instance extract error.');
			}
			
			# fix back $or
			{
				$or = $orig_req;
				$or_dir = ($ext !== null) ? dirname($or) : $or;
			}
			
			# we need to fix __or__ for compatibility, @TODO in the future we should define a constant
			{
				$_GET['__or__'] = $orig_req;
				if (($s_qs = ($_SERVER['QUERY_STRING'] ?? null)))
				{
					$qs_tmp = null;
					parse_str($_SERVER['QUERY_STRING'], $qs_tmp);
					if (is_array($qs_tmp))
					{
						$qs_tmp['__or__'] = $orig_req;
						$_SERVER['QUERY_STRING'] = http_build_query($qs_tmp);
					}
				}
			}
			
			if ($default_instance)
				define('BASE_HREF', "/");
			else
				define('BASE_HREF', "/{$instance}/");

			define('Q_EXEC_DIR', realpath(Q_CODE_MULTI_INSTANCE_FOLDER)."/{$instance}/");
		}
	}
	# 1. define basic constants
	else
	{
		if (!defined('Q_RUNNING_PATH'))
			throw new \Exception('Running path not defined.');
		define('Q_EXEC_DIR', Q_RUNNING_PATH);
	}

	# 2. Change working dir
	chdir(Q_EXEC_DIR);

	# 3. Handle static resources
	{
		if (($lc_ext !== null) && ($lc_ext !== 'php'))
		{
			# @TODO - php needs to be on an exclusion list
			# CHECK USER AUTH BEFORE ALLOWING ACCESS TO VARIOUS FOLDERs
			# ex : uploads ... etc
			
			# we serve-it
			$diff_dir = substr(Q_EXEC_DIR, strlen(Q_CODE_DIR));
			if (defined('Q_SECURITY_SAFE_FOLDERS') && (!empty(Q_SECURITY_SAFE_FOLDERS)))
			{
				$or_dir_parts = preg_split("/(\\/)/uis", $or_dir, -1, PREG_SPLIT_NO_EMPTY);
				if (!empty($or_dir_parts))
				{
					$found = null;
					foreach ($or_dir_parts as $chunk)
					{
						if (($found = (Q_SECURITY_SAFE_FOLDERS[$chunk] ?? null)) !== null)
						{
							if ($found === true)
								break;
							# else continue
						}
						else
							break;
					}
					if ($found === true)
					{
						$or_dir = $diff_dir . $or_dir;
						$or = $diff_dir . $or;
					}
				}
			}
			
			$app_rel_request = substr( $or_dir, (strlen(Q_EXEC_DIR) - strlen(Q_CODE_DIR)) );
			
			$security_check = false;
			if (($security_check = q_security_allow_extension($lc_ext, $app_rel_request, $or_dir)) && file_exists(Q_CODE_DIR . $or))
			{
				if ($mime)
				{
					header('Content-Type: '.$mime);
					readfile(Q_CODE_DIR . $or);
					exit;
				}
				else
				{
					# we need to log it
					# file_put_contents(dirname( __DIR__ ). '/log_secured_access.txt', 'Unknown mime for: '.$or."\n", FILE_APPEND);
					# http_response_code(404);
					# die('Not there #1.');
					header('Content-Type: application/octet-stream');
					readfile(Q_CODE_DIR . $or);
					exit;
				}
			}
			else
			{
				if (!$security_check)
				{
					file_put_contents(dirname( __DIR__ ). '/log_secured_unsafe.txt', 'Missing: '.$or."\n", FILE_APPEND);
					http_response_code(403);
					die('403 Forbidden.');
				}
				else
				{
					file_put_contents(dirname( __DIR__ ). '/log_secured_missing.txt', 'Missing: '.$or."\n", FILE_APPEND);
					http_response_code(404);
					die('Not there #2.');
				}
			}
		}
		else
		{
			if (Q_USE_XSS_INPUT_PROTECTION)
			{
				# secure input
				$rc = q_xss_secure_input();
				if ($rc === true)
				{
					# all was good
				}
				else
				{
					http_response_code(500);
					die('Input error. ' . $rc);
				}
			}
			
			if ($lc_ext === 'php')
				file_put_contents(dirname( __DIR__ ). '/log_all_phps.txt', date('Y-m-d H:i:s') . " - {$_SERVER['REMOTE_ADDR']} - {$or} - {$_SERVER['HTTP_USER_AGENT']}\n", FILE_APPEND);
			
			if (rtrim($or_dir, "/") === 'api')
			{
				chdir(Q_EXEC_DIR . "api");
				require Q_EXEC_DIR . $or . "/index.php";
			}
			else if (($or_dir === 'voip-fuse/code/scripts/crons') && ($ext === 'php'))
			{
				# @TODO - from limited IPs only
				require Q_CODE_DIR . $or;
			}
			else if ($or === 'export/pdf/pdfg.php')
			{
				require 'export/pdf/pdfg.php';
			}
			else if ($or === 'partners_report.php')
			{
				require 'partners_report.php';
			}
			else if ($or === 'partners_sync_cat.php')
			{
				require 'partners_sync_cat.php';
			}
			else if ($or === 'salesforce.php')
			{
				require 'salesforce.php';
			}
			else if ($or === 'salesforce_import.php')
			{	
				require 'salesforce_import.php';
			}
			else if ($or === 'salesforce_products.php')
			{	
				require 'salesforce_products.php';
			}
			else if ($or === 'util_resync_cat.php')
			{	
				require 'util_resync_cat.php';
			}
			else if ($or === 'activate_offers_in_price_profiles.php')
			{	
				require 'activate_offers_in_price_profiles.php';
			}
			else if ($or === 'test.php')
			{	
				require 'test.php';
			}
			else
			{
				# require 'index.php';
			}
		}
	}
})();

function q_xss_secure_input()
{
	# echo "<pre>\n\n";
	if ($_GET)
	{
		foreach ($_GET as $k => &$v)
		{
			$saved_k = $k;
			$k = q_xss_secure_data($k);
			if ($saved_k !== $k)
				return "Bad variable name in request (GET).";
			
			$prev_v = $v;
			$error = null;
			q_xss_secure_data($v, 'GET', $error);
			if ($error)
				return $error;
			
			if (($k === '__or__') && ($prev_v !== $v))
			{
				return "Bad request URL.";
			}
		}
		unset($v);
	}
	if ($_POST)
	{
		foreach ($_POST as $k => &$v)
		{
			$saved_k = $k;
			$k = q_xss_secure_data($k);
			if ($saved_k !== $k)
				return "Bad variable name in request (POST).";
			
			$error = null;
			q_xss_secure_data($v, 'POST', $error);
			if ($error)
				return $error;
		}
		unset($v);
	}
	if ($_COOKIE)
	{
		foreach ($_COOKIE as $k => &$v)
		{
			$saved_k = $k;
			$k = q_xss_secure_data($k);
			if ($saved_k !== $k)
				return "Bad variable name in request (COOKIE).";
			
			$error = null;
			q_xss_secure_data($v, 'COOKIE', $error);
			if ($error)
				return $error;
		}
		unset($v);
	}
	# WE DO NOT USE REQUEST
	$_REQUEST = [];
	
	# var_dump($_FILES); # we need a demo ?!
	# php://input !

	return true; # was ok
}

function q_xss_secure_data(&$data, string $input_type = null, string &$error = null)
{
	if (is_string($data))
	{
		# $s_data = $data;
		$data = htmlspecialchars($data, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5);
		# if ($s_data !== $data)
		#	echo "changed from " . json_encode($s_data) . " |TO| " . json_encode($data) . "\n";
	}
	else if (is_array($data))
	{
		foreach ($data as $k => &$v)
		{
			$saved_k = $k;
			$k = q_xss_secure_data($k);
			if ($saved_k !== $k)
			{
				$error = "Bad variable name in request".($input_type ? " ({$input_type}) " : "").".";
				return false;
			}
			q_xss_secure_data($v);
		}
		unset($v);
	}
	return $data;
}

function q_security_allow_extension(string $ext, string $instance_rel_request, string $full_request_dir)
{
	$image_exts = [
		'svg' => true,
		'bmp' => true,
		'png' => true,
		'jpg' => true,
		'tif' => true,
		'tiff' => true,
		'jpeg' => true, 
		'bmp' => true, 
		'gif' => true,

		'pbm' => true,
		'pgm' => true,
		'ppm' => true,
		'rgb' => true,
		'ico' => true,
	];
	
	$allowed_app_extensions = array_merge([
				'js' => true,
				'css' => true,
				
				'woff' => true,
				'ttf' => true,
				'docx' => true, # needed for proposal document, we should have a sep. folder
	], $image_exts);
	
	$allowed_upload_extensions = array_merge([
				'html' => true,
				'htm' => true,
		
				'doc' => true, 
				'docx' => true, 
				'csv' => true, 
				'xls' => true, 
				'xlsx' => true, 
				'xlsm' => true, 
				'docm' => true,
				'pdf' => true, 
				'txt' => true, 
				'eml' => true,
				'ppt' => true,
				'pptx' => true,
				'pps' => true,
				'odp' => true,
		], $image_exts);
	
	$allowed_temp_extensions = array_merge($allowed_app_extensions, $allowed_upload_extensions);
	
	$setup_config = [
						'*' => $allowed_app_extensions, # any other folder , expecting code folder
						'uploads' => $allowed_upload_extensions,
						'temp' => $allowed_temp_extensions,
	];
	
	
	list($instance_rel_request) = explode("/", trim($instance_rel_request, " \t\\/"), 2);
	
	if ((!empty($instance_rel_request)) && isset($setup_config[$instance_rel_request]))
	{
		return $setup_config[$instance_rel_request][$ext] ?? false;
	}
	else
		return $setup_config['*'][$ext] ?? false;
}

function q_security_extract_request_info()
{
	$qs = $_SERVER['QUERY_STRING'];
	$or = null;
	$mime = null;
	$lc_ext = null;
	$ext = null;

	if ($qs)
	{
		$result = null;
		parse_str($qs, $result);
		$or = $result['__or__'] ?? null;

		$ext = (($p = strrpos($or, '.')) !== false) ? substr($or, $p + 1) : null;
		if (is_string($ext))
		{
			$ext = trim($ext);
			if (strlen($ext) === 0)
				$ext = null;
		}
		$lc_ext = is_string($ext) ? strtolower($ext) : null;
		if ($lc_ext !== null)
		list ($mime, ) = q_security_get_mime($lc_ext);
	}

	return [$or, $lc_ext, $ext, $mime, ($ext !== null) ? dirname($or) : $or];
}

function q_security_get_mime(string $ext)
{
	return [

	'csv'	=>	['text/csv', 'Comma separated values'],
	'bmp'	=>	['image/bmp',	'Bitmap'],
	'png'	=>	['image/png',	'PNG'],
	'cod'	=>	['image/cis-cod',	'compiled source code'],
	'gif'	=>	['image/gif',	'graphic interchange format'],
	'ief'	=>	['image/ief',	'image file'],
	'jpe'	=>	['image/jpeg',	'JPEG image'],
	'jpeg'	=>	['image/jpeg',	'JPEG image'],
	'jpg'	=>	['image/jpeg',	'JPEG image'],
	'jfif'	=>	['image/pipeg',	'JPEG file interchange format'],
	'svg'	=>	['image/svg+xml',	'scalable vector graphic'],
	'tif'	=>	['image/tiff',	'TIF image'],
	'tiff'	=>	['image/tiff',	'TIF image'],
	'ras'	=>	['image/x-cmu-raster',	'Sun raster graphic'],
	'cmx'	=>	['image/x-cmx',	'Corel metafile exchange image file'],
	'ico'	=>	['image/x-icon',	'icon'],
	'pnm'	=>	['image/x-portable-anymap',	'portable any map image'],
	'pbm'	=>	['image/x-portable-bitmap',	'portable bitmap image'],
	'pgm'	=>	['image/x-portable-graymap',	'portable graymap image'],
	'ppm'	=>	['image/x-portable-pixmap',	'portable pixmap image'],
	'rgb'	=>	['image/x-rgb',	'RGB bitmap'],
	'xbm'	=>	['image/x-xbitmap',	'X11 bitmap'],
	'xpm'	=>	['image/x-xpixmap',	'X11 pixmap'],
	'xwd'	=>	['image/x-xwindowdump',	'X-Windows dump image'],
	'css'	=>	['text/css',	'Cascading Style Sheet'],
	'323'	=>	['text/h323',	'H.323 internet telephony file'],
	'htm'	=>	['text/html',	'HTML file'],
	'html'	=>	['text/html',	'HTML file'],
	'stm'	=>	['text/html',	'Exchange streaming media file'],
	'uls'	=>	['text/iuls',	'NetMeeting user location service file'],
	'bas'	=>	['text/plain',	'BASIC source code file'],
	'c'	=>	['text/plain',	'C/C++ source code file'],
	'h'	=>	['text/plain',	'C/C++/Objective C header file'],
	'txt'	=>	['text/plain',	'text file'],
	'rtx'	=>	['text/richtext',	'rich text file'],
	'sct'	=>	['text/scriptlet',	'Scitext continuous tone file'],
	'tsv'	=>	['text/tab-separated-values',	'tab separated values file'],
	'htt'	=>	['text/webviewhtml',	'hypertext template file'],
	'htc'	=>	['text/x-component',	'HTML component file'],
	'etx'	=>	['text/x-setext',	'TeX font encoding file'],
	'vcf'	=>	['text/x-vcard',	'vCard file'],
	'evy'	=>	['application/envoy',	'Corel Envoy'],
	'fif'	=>	['application/fractals',	'fractal image file'],
	'spl'	=>	['application/futuresplash',	'Windows print spool file'],
	'hta'	=>	['application/hta',	'HTML application'],
	'acx'	=>	['application/internet-property-stream',	'Atari ST Program'],
	'hqx'	=>	['application/mac-binhex40',	'BinHex encoded file'],
	'doc'	=>	['application/msword',	'Word document'],
	'dot'	=>	['application/msword',	'Word document template'],
	'*'	=>	['application/octet-stream',	'Binary file'],
	'bin'	=>	['application/octet-stream',	'binary disk image'],
	'class'	=>	['application/octet-stream',	'Java class file'],
	'dms'	=>	['application/octet-stream',	'Disk Masher image'],
	'exe'	=>	['application/octet-stream',	'executable file'],
	'lha'	=>	['application/octet-stream',	'LHARC compressed archive'],
	'lzh'	=>	['application/octet-stream',	'LZH compressed file'],
	'oda'	=>	['application/oda',	'CALS raster image'],
	'axs'	=>	['application/olescript',	'ActiveX script'],
	'pdf'	=>	['application/pdf',	'Acrobat file'],
	'prf'	=>	['application/pics-rules',	'Outlook profile file'],
	'p10'	=>	['application/pkcs10',	'certificate request file'],
	'crl'	=>	['application/pkix-crl',	'certificate revocation list file'],
	'ai'	=>	['application/postscript',	'Adobe Illustrator file'],
	'eps'	=>	['application/postscript',	'postscript file'],
	'ps'	=>	['application/postscript',	'postscript file'],
	'rtf'	=>	['application/rtf',	'rich text format file'],
	'setpay'	=>	['application/set-payment-initiation',	'set payment initiation'],
	'setreg'	=>	['application/set-registration-initiation',	'set registration initiation'],
	'xla'	=>	['application/vnd.ms-excel',	'Excel Add-in file'],
	'xlc'	=>	['application/vnd.ms-excel',	'Excel chart'],
	'xlm'	=>	['application/vnd.ms-excel',	'Excel macro'],
	'xls'	=>	['application/vnd.ms-excel',	'Excel spreadsheet'],
	'xlt'	=>	['application/vnd.ms-excel',	'Excel template'],
	'xlw'	=>	['application/vnd.ms-excel',	'Excel workspace'],
	'msg'	=>	['application/vnd.ms-outlook',	'Outlook mail message'],
	'sst'	=>	['application/vnd.ms-pkicertstore',	'serialized certificate store file'],
	'cat'	=>	['application/vnd.ms-pkiseccat',	'Windows catalog file'],
	'stl'	=>	['application/vnd.ms-pkistl',	'stereolithography file'],
	'pot'	=>	['application/vnd.ms-powerpoint',	'PowerPoint template'],
	'pps'	=>	['application/vnd.ms-powerpoint',	'PowerPoint slide show'],
	'ppt'	=>	['application/vnd.ms-powerpoint',	'PowerPoint presentation'],
	'mpp'	=>	['application/vnd.ms-project',	'Microsoft Project file'],
	'wcm'	=>	['application/vnd.ms-works',	'WordPerfect macro'],
	'wdb'	=>	['application/vnd.ms-works',	'Microsoft Works database'],
	'wks'	=>	['application/vnd.ms-works',	'Microsoft Works spreadsheet'],
	'wps'	=>	['application/vnd.ms-works',	'Microsoft Works word processor document'],
	'hlp'	=>	['application/winhlp',	'Windows help file'],
	'bcpio'	=>	['application/x-bcpio',	'binary CPIO archive'],
	'cdf'	=>	['application/x-cdf',	'computable document format file'],
	'z'	=>	['application/x-compress',	'Unix compressed file'],
	'tgz'	=>	['application/x-compressed',	'gzipped tar file'],
	'cpio'	=>	['application/x-cpio',	'Unix CPIO archive'],
	'csh'	=>	['application/x-csh',	'Photoshop custom shapes file'],
	'dcr'	=>	['application/x-director',	'Kodak RAW image file'],
	'dir'	=>	['application/x-director',	'Adobe Director movie'],
	'dxr'	=>	['application/x-director',	'Macromedia Director movie'],
	'dvi'	=>	['application/x-dvi',	'device independent format file'],
	'gtar'	=>	['application/x-gtar',	'Gnu tar archive'],
	'gz'	=>	['application/x-gzip',	'Gnu zipped archive'],
	'hdf'	=>	['application/x-hdf',	'hierarchical data format file'],
	'ins'	=>	['application/x-internet-signup',	'internet settings file'],
	'isp'	=>	['application/x-internet-signup',	'IIS internet service provider settings'],
	'iii'	=>	['application/x-iphone',	'ARC+ architectural file'],
	'js'	=>	['application/x-javascript',	'JavaScript file'],
	'latex'	=>	['application/x-latex',	'LaTex document'],
	'mdb'	=>	['application/x-msaccess',	'Microsoft Access database'],
	'crd'	=>	['application/x-mscardfile',	'Windows CardSpace file'],
	'clp'	=>	['application/x-msclip',	'CrazyTalk clip file'],
	'dll'	=>	['application/x-msdownload',	'dynamic link library'],
	'm13'	=>	['application/x-msmediaview',	'Microsoft media viewer file'],
	'm14'	=>	['application/x-msmediaview',	'Steuer2001 file'],
	'mvb'	=>	['application/x-msmediaview',	'multimedia viewer book source file'],
	'wmf'	=>	['application/x-msmetafile',	'Windows meta file'],
	'mny'	=>	['application/x-msmoney',	'Microsoft Money file'],
	'pub'	=>	['application/x-mspublisher',	'Microsoft Publisher file'],
	'scd'	=>	['application/x-msschedule',	'Turbo Tax tax schedule list'],
	'trm'	=>	['application/x-msterminal',	'FTR media file'],
	'wri'	=>	['application/x-mswrite',	'Microsoft Write file'],
	'cdf'	=>	['application/x-netcdf',	'computable document format file'],
	'nc'	=>	['application/x-netcdf',	'Mastercam numerical control file'],
	'pma'	=>	['application/x-perfmon',	'MSX computers archive format'],
	'pmc'	=>	['application/x-perfmon',	'performance monitor counter file'],
	'pml'	=>	['application/x-perfmon',	'process monitor log file'],
	'pmr'	=>	['application/x-perfmon',	'Avid persistent media record file'],
	'pmw'	=>	['application/x-perfmon',	'Pegasus Mail draft stored message'],
	'p12'	=>	['application/x-pkcs12',	'personal information exchange file'],
	'pfx'	=>	['application/x-pkcs12',	'PKCS #12 certificate file'],
	'p7b'	=>	['application/x-pkcs7-certificates',	'PKCS #7 certificate file'],
	'spc'	=>	['application/x-pkcs7-certificates',	'software publisher certificate file'],
	'p7r'	=>	['application/x-pkcs7-certreqresp',	'certificate request response file'],
	'p7c'	=>	['application/x-pkcs7-mime',	'PKCS #7 certificate file'],
	'p7m'	=>	['application/x-pkcs7-mime',	'digitally encrypted message'],
	'p7s'	=>	['application/x-pkcs7-signature',	'digitally signed email message'],
	'sh'	=>	['application/x-sh',	'Bash shell script'],
	'shar'	=>	['application/x-shar',	'Unix shar archive'],
	'swf'	=>	['application/x-shockwave-flash',	'Flash file'],
	'sit'	=>	['application/x-stuffit',	'Stuffit archive file'],
	'sv4cpio'	=>	['application/x-sv4cpio',	'system 5 release 4 CPIO file'],
	'sv4crc'	=>	['application/x-sv4crc',	'system 5 release 4 CPIO checksum data'],
	'tar'	=>	['application/x-tar',	'consolidated Unix file archive'],
	'tcl'	=>	['application/x-tcl',	'Tcl script'],
	'tex'	=>	['application/x-tex',	'LaTeX source document'],
	'texi'	=>	['application/x-texinfo',	'LaTeX info document'],
	'texinfo'	=>	['application/x-texinfo',	'LaTeX info document'],
	'roff'	=>	['application/x-troff',	'unformatted manual page'],
	't'	=>	['application/x-troff',	'Turing source code file'],
	'tr'	=>	['application/x-troff',	'TomeRaider 2 ebook file'],
	'man'	=>	['application/x-troff-man',	'Unix manual'],
	'me'	=>	['application/x-troff-me',	'readme text file'],
	'ms'	=>	['application/x-troff-ms',	'3ds Max script file'],
	'ustar'	=>	['application/x-ustar',	'uniform standard tape archive format file'],
	'src'	=>	['application/x-wais-source',	'source code'],
	'cer'	=>	['application/x-x509-ca-cert',	'internet security certificate'],
	'crt'	=>	['application/x-x509-ca-cert',	'security certificate'],
	'der'	=>	['application/x-x509-ca-cert',	'DER certificate file'],
	'pko'	=>	['application/ynd.ms-pkipko',	'public key security object'],
	'zip'	=>	['application/zip',	'zipped file'],
	'woff'	=>	['application/x-font-woff', 'woff font'],

		][$ext];
}
