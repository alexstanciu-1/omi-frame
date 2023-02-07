<?php

namespace Omi\Gens;

class ConfigGenerator 
{
	public static function Generate($watch_folder, $batch_id, $batch_data, $old_config, $save_mode = "propose")
	{
		$className = $batch_data["className"];
		$autosync = $batch_data["autosync"] ? true : false;
		$autosyncadd = $batch_data["autosyncadd"] ? true : false;
		$autosyncremove = $batch_data["autosyncremove"] ? true : false;
		$namespace = $batch_data["namespace"];
		$prefix = $batch_data["prefix"];
		$rel_path = $batch_data["rel_path"];
		
		$batch_config = [];
		$batch_config["_batch_id_"] = $batch_id;
		
		$batch_top = [
					"id" => $batch_id,
					"forClass" => $className,
					"autosync" => $autosync,
					"autosyncadd" => $autosyncadd,
					"autosyncremove" => $autosyncremove,
					"namespace" => $namespace,
					"prefix" => $prefix,
					"rel_path" => $rel_path,
				];
		
		// var_dump($batch_data);
		
		// 1. call for main generators
		if ($batch_data["batchGens"])
		{
			foreach ($batch_data["batchGens"] as $batch_gen)
			{
				if (!$batch_gen)
					continue;

				// extract the includes for me
				$include_properties = [];
				if ($batch_data["properties"])
				{
					foreach ($batch_data["properties"] as $prop_name => $prop_data)
					{
						if ($prop_data["gens_incl"] && in_array($batch_gen, $prop_data["gens_incl"]))
							$include_properties[$prop_name] = $prop_name;
					}
				}
				$configs = $batch_gen::GenerateConfig($watch_folder, $batch_id, $className, null, $namespace, $prefix, $autosync, $autosyncadd, $autosyncremove, $include_properties);
				if ($configs)
				{
					foreach ($configs as $cn => $config_list)
					{
						foreach ($config_list as $gn => $config)
							$batch_config[$cn][$gn] = ($existing = $batch_config[$cn][$gn]) ? Generator::MergeArrays($config, $existing) : $config;
					}
				}
			}
		}
		
		// 2. then loop properties generators
		if ($batch_data["properties"])
		{
			foreach ($batch_data["properties"] as $prop_name => $prop_data)
			{
				// echo "Generate {$prop_name}:<br/>";
				if (!$prop_data["gens"])
					continue;
				
				$has_listing_gen = in_array("Omi\\Gens\\Listing", $prop_data["gens"]);
				
				foreach ($prop_data["gens"] as $prop_gen_class)
				{
					if ($prop_gen_class)
					{
						// ob_start();
						$g_configs = $prop_gen_class::GenerateConfig($watch_folder, $batch_id, $className, $prop_name, $namespace, $prefix, $autosync, $autosyncadd, $autosyncremove);
						foreach ($g_configs as $cn => $config_list)
						{
							foreach ($config_list as $gn => $config)
							{
								$batch_config[$cn][$gn] = ($existing = $batch_config[$cn][$gn]) ? Generator::MergeArrays($config, $existing) : $config;
								if ($has_listing_gen)
								{
									if ($prop_gen_class === "Omi\\Gens\\Form")
										$batch_config[$cn][$gn]["templateTag"] = "form";
									else if ($prop_gen_class === "Omi\\Gens\\View")
										$batch_config[$cn][$gn]["templateTag"] = "view";
								}
							}
						}

					}
				}
			}
		}
		
		$save_dir = static::GetConfigsPath($watch_folder, $batch_id);
		// $propose_path = static::GetConfigProposePath($watch_folder, $batch_id);
		// $save_dir = dirname($save_path);
		if (!is_dir($save_dir))
			qmkdir($save_dir);
		
		$batch_top_path = rtrim($save_dir, "/\\").".php";
		$old_batch_top = null;
		$old_namespace = null;
		if (file_exists($batch_top_path))
		{
			// fix if the namespace was changed
			include($batch_top_path);
			$old_batch_top = $QGEN_Config ?: null;
			$old_namespace = $old_batch_top["namespace"];
			unset($QGEN_Config);
		}
		
		/** @todo rename files on namespace change, this is a bit to complicated to do (concept wise)
		if (($old_namespace !== null) && ($old_namespace !== $namespace))
		{
			// the namespace was changed we will need to rename all the files
			// in some cases it may not be needed, but we do it to be sure
			$existing_files = scandir($save_dir);
			$rename_list = [];
			foreach ($existing_files as $e_file)
			{
				if (($e_file === ".") || ($e_file === ".."))
					continue;
				$fp = $save_dir.$e_file;
				// get namespace from className
				include($fp);
				$file_inf = $QGEN_Config ?: null;
				unset($QGEN_Config);
				if (($fi_class_name = $file_inf["className"]) && ($fi_generator = $file_inf["generator"]))
				{
					// we will need to recompose things
					list ($fi_cn, $fi_ns) = qClassShortAndNamespace($fi_class_name);
					$new_fi_class_name = @todo... recompose file name ... 
					$new_fp = static::GetConfigPath($watch_folder, $batch_id, $new_fi_class_name, $fi_generator, $namespace);
					if ($new_fp !== $fp)
						$rename_list[$fp] = $new_fp;
				}
			}
			
			// to avoid conflicts we rename things in 2 steps
			foreach ($rename_list as $old_path => $new_path)
				rename($old_path, $new_path.".-to-rename");
			foreach ($rename_list as $new_path)
				rename($new_path.".-to-rename", $new_path);
		}
		*/
		
		qArrayToCodeFile($batch_top, "QGEN_Config", $batch_top_path);
		
		foreach ($batch_config as $cn => $config_list)
		{
			if (!is_array($config_list))
				continue;
			
			foreach ($config_list as $gn => $config)
			{
				$save_path = static::GetConfigPath($watch_folder, $batch_id, $cn, $gn, $namespace);
				$propose_path = substr($save_path, -3)."gen";
				$file_exists = file_exists($save_path);

				if ($save_mode === "propose")
				{
					qArrayToCodeFile($config, "QGEN_Config", $propose_path);
				}
				else if (($save_mode === "overwrite") || (!$file_exists))
				{
					qArrayToCodeFile($config, "QGEN_Config", $save_path);
				}
				else if (($save_mode === "merge") || ($save_mode === "overwrite-values"))
				{
					$write_array = Generator::MergeArrays($save_path, $batch_config, $save_mode, "QGEN_Config");
					if ($write_array)
						qArrayToCodeFile($write_array, "QGEN_Config", $save_path);
					else
						return false;
				}
				else if ($save_mode === "delete")
				{
					// @todo
				}
				else
				{
					// no valid option selected
				}
			}
		}
		
	}
	
	public static function GetConfigPath($watch_folder, $batch_id, $class_name, $generator_class, $base_namespace = null)
	{
		if ($base_namespace)
		{
			$base_namespace = rtrim($base_namespace, "\\")."\\";
			$class_name = (substr($class_name, 0, strlen($base_namespace)) === $base_namespace) ? substr($class_name, strlen($base_namespace)) : "-".$class_name;
		}
		$base_path = static::GetConfigsPath($watch_folder, $batch_id);
		$gc = (substr($generator_class, 0, strlen("Omi\\Gens\\")) === "Omi\\Gens\\") ? substr($generator_class, strlen("Omi\\Gens\\")) : "-".$generator_class;
		return $base_path.preg_replace("/[^\\w0-9\\_]/us", "-", strtolower($class_name)).".".preg_replace("/[^\\w0-9\\_]/us", "-", strtolower($gc)).".php";
	}
	
	public static function GetConfigsPath($watch_folder, $batch_id = null)
	{
		$save_dir = $watch_folder."~configs/";
		return $batch_id ? $save_dir.preg_replace("/[^\\w0-9\\_]/us", "-", strtolower($batch_id))."/" : $save_dir;
	}
}
