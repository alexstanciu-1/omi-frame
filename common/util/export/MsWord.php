<?php

namespace Omi\Util;

/**
 * Description of MsWord
 *
 * @author Omi-Mihai
 * @class.name MsWord
 */
abstract class MsWord extends \QModel
{
	/**
	 * @api.enable
	 * 
	 * Exports the file
	 * 
	 * @param string $path
	 * @param string $name
	 */
	public static function Download($template, $blocks = [], $path = null, $name = "Untitled.docx")
	{
		if (!$path)
		{
			$path = \QAutoload::GetTempWebPath();
			$path = ($path ? rtrim($path, "\\/")."/" : "").uniqid().".docx";
		}

		list($file, $fullPath) = static::Export($template, $blocks, $path);

		header('Content-Description: File Transfer');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-type: application octet-stream');
		header("Content-Disposition: attachment; filename=\"".addslashes($name)."\"");
		readfile($fullPath, false, stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]));
		unlink(addslashes($path));
		die();
	}
	/**
	 * @api.enable
	 * 
	 * @param string $template
	 * @param array $blocks
	 */
	public static function Export($template, $blocks = [], $savePath = null, $name = null)
	{
		$base = \QWebRequest::GetBaseUrl();

		if ($savePath)
		{
			file_put_contents($savePath, static::GetTbsString($template, $blocks));
			return [$savePath, $base.$savePath];
		}
		
		if (!$name)
			$name = "Untitled.xlsm";

		$tmpPath = \QAutoload::GetTempWebPath();
		$file = ($tmpPath ? rtrim($tmpPath, "\\/")."/" : "").uniqid().".xlsm";

		if (file_exists($file))
			@unlink($file);

		file_put_contents($file, static::GetTbsString($template, $blocks));

		$pFile = $tmpPath."print_".uniqid().".php";

		$data = "<?php\n".
				"	header('Content-Description: File Transfer');\n".
				"	header('Content-Transfer-Encoding: binary');\n".
				"	header('Expires: 0');\n".
				"	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');\n".
				"	header('Pragma: public');\n".
				"	header('Content-type: application octet-stream');\n".
				"	header(\"Content-Disposition: attachment; filename=\\\"".addslashes($name)."\\\"\");\n".
				"	//header('Content-Length: ".filesize($file)."');\n".
				"\n	readfile(\"".$base.$file."\", false, stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]));\n".
				"\n unlink(\"".addslashes(basename($file))."\");\n".
				($file ? "\n unlink(\"".addslashes(basename($file))."\");\n" : "").
				"	unlink(__FILE__);\n".
				"?>";

		file_put_contents($pFile, $data);
		return [$pFile, $base.$pFile];
	}
	/**
	 * Returns the string for the excel file
	 * 
	 * @param string $template
	 * @param array $blocks
	 * @return type
	 * @throws \Exception
	 */
	protected static function GetTbsString($template, $blocks)
	{
		if (!$template || !file_exists($template))
			throw new \Exception("Template {$template} not found!");

		// TinyButStrong template engine
		require_once('include/opentbs/demo/tbs_class_php5.php');
		// load the OpenTBS plugin
		require_once('include/opentbs/tbs_plugin_opentbs.php');

		$TBS = new \clsTinyButStrong; // new instance of TBS
		$TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN, OPENTBS_ALREADY_UTF8); // load OpenTBS plugin
		$TBS->LoadTemplate($template, OPENTBS_ALREADY_UTF8);

		/*
		$configuration = [
			// Name of the parent entity. The process will be limited to this element and its childs.
			'parent' => 'table',
			// Names of tags that must be simply ignored. Such start and ending tags are ignored but childs are not ignored.
			// For elements that must be completely ignored including childs, you just have to not mentioned it in the configuration.
			'ignore' => ['!--', 'caption', 'thead', 'thbody', 'thfoot'],
			// Names of entities recognized as column's definitions, and their span attributes ('' for no span attribute).
			// Such entities must always be placed before the row entities.
			'cols'   => [],
			// Names of entities recognized as rows.
			'rows'   => ['tr'],
			// Names of entities recognized as cells, and their span attibute ('' for no span attribute).
			'cells'  => [
				'td' => 'colspan',
				'th' => 'colspan',
			],
		];

		// Save the new configuratin with its ID.
		$TBS->SetOption('parallel_conf', 'tbs:table',  $configuration);
		*/

		foreach ($blocks as $blockTag => $block)
			$TBS->MergeBlock($blockTag, $block);
		
		$TBS->Show(OPENTBS_STRING);
		
		return $TBS->Source;
	}
}
