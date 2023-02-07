<?php

namespace Omi\Gens;

class Generator 
{
	public static $Generators = ["Omi\\Gens\\Listing", "Omi\\Gens\\Form", "Omi\\Gens\\View"];
	public static $BatchGenerators = ["Omi\\Gens\\Frontend", "Omi\\Gens\\Backend", "Omi\\Gens\\Menu", "Omi\\Gens\\Controller"];
	
	public static function GenerateAllBatches($watch_folder)
	{
		$path = ConfigGenerator::GetConfigsPath($watch_folder);
		$dirs = scandir($path);
		foreach ($dirs as $dir)
		{
			if (($dir === ".") || ($dir === ".."))
				continue;
			$fp = $path.$dir;
			if (is_dir($fp))
			{
				// yep
				static::GenerateBatch($watch_folder, $dir);
			}
			// $ext = pathinfo($fp, PATHINFO_EXTENSION);
			//if ((!is_file($fp)) || (($ext !== "php")))
			//	continue;
			
			// $batch_id = pathinfo($fp, PATHINFO_FILENAME);
			//static::GenerateBatch($watch_folder, $batch_id);
		}
	}
	
	public static function GenerateBatch($watch_folder, $batch_id, $mode = "merge")
	{
		//var_dump($watch_folder, $batch_id);
		
		$batch_dir = ConfigGenerator::GetConfigsPath($watch_folder, $batch_id);
		
		//var_dump($batch_dir);
		
		$batch_items = scandir($batch_dir);
		
		//var_dump($batch_items);
		
		foreach ($batch_items as $batch_file)
		{
			if (($batch_file === ".") || ($batch_file === ".."))
				continue;
			
			$fp = $batch_dir.$batch_file;
			$ext = pathinfo($fp, PATHINFO_EXTENSION);
			if ((!is_file($fp)) || (($ext !== "php")))
				continue;
			// var_dump("GenerateBatch::".$path, file_get_contents($path));
			include($fp);
			$QGEN_Config = $QGEN_Config ?: [];
			$batch_config = $QGEN_Config;
			if (!($batch_config && is_array($batch_config)))
				continue;
			
			//var_dump($batch_config);
			
			$generator = $batch_config["generator"];
			if (!$generator)
				continue;

			$generator::Generate($batch_config);
		}
	}
	
	public static function MergeArrays($data_1, $data_2, $save_mode = "merge", $var_name_1 = null, $var_name_2 = null)
	{
		// echo "<hr/>";
		// var_dump(qArrayToCode($data_1), qArrayToCode($data_2));
		
		if (is_string($data_1) && file_exists($data_1) && $var_name_1)
		{
			include($data_1);
			$data_1 = $$var_name_1;
		}
		if (is_string($data_2) && file_exists($data_2) && $var_name_2)
		{
			include($data_2);
			$data_2 = $$var_name_2;
		}
		if (!$data_1)
			return $data_2;
		else if (!$data_2)
			return $data_1;
		
		if ((!is_array($data_1)) && (!is_array($data_2)))
			return false;
		// completed data check
		
		if (($save_mode === "merge") || ($save_mode === "overwrite-values"))
		{
			$return = static::MergeArraysWorker($data_1, $data_2, ($save_mode = "overwrite-values"));
			// var_dump(qArrayToCode($return));
			//echo "</hr>";
			return $return;
		}
		else
			return false;
	}
	
	public static function MergeArraysWorker($data_1, $data_2, $overwrite_values = false)
	{
		foreach ($data_2 as $k => $v)
		{
			if (is_array($v))
			{
				if (array_key_exists($k, $data_1))
					$data_1[$k] = static::MergeArraysWorker($data_1[$k], $v, $mode);
				else
					$data_1[is_numeric($k) ? null : $k] = $v;
			}
			else
			{
				if (is_numeric($k))
				{
					if (!in_array($v, $data_1))
						$data_1[] = $v;
				}
				else if ($overwrite_values || (!$data_1[$k]))
					$data_1[$k] = $v;
			}
		}
		return $data_1;
	}
}
