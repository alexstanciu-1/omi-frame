<?php


/**
 * @class.name QDevModeGeneratedCtrl
 */
abstract class QDevModeGeneratedCtrl_frame_ extends QWebControl
{
	
	
	public $watchFolder;
	
	/**
	 * @api.enable
	 */
	public function getGenerated()
	{
		$wfs = QAutoload::GetWatchFolders();
		
		$ret = [];
		
		foreach ($wfs as $wf)
		{
			$gen_path = $wf."generate.php";
			if (!file_exists($gen_path))
				continue;
			
			$QGEN_Config = null;
			include($gen_path);
			if ($QGEN_Config && ($caption = $QGEN_Config["Caption"]) !== null)
				$ret[$wf] = $caption;
			else
				$ret[$wf] = qrelative_path($wf, Q_RUNNING_PATH);
		}
		
		// var_dump($ret);
		
		return $ret ?: null;
	}
	
	/**
	 * 
	 * @param string $class_name
	 * @return QModelProperty[]
	 */
	public function getPropertiesForClass($class_name)
	{
		$mi = QModelQuery::GetTypesCache($class_name);
		if (!$mi)
			return null;
		
		next($mi);
		next($mi);
		next($mi);
		
		$m_ty = QModel::GetTypeByName($class_name);
		
		$ret = [];
		while (($prop_data = next($mi)))
		{
			$prop_name = key($mi);
			$ret[$prop_name] = $m_ty->properties[$prop_name];
		}
		
		return $ret;
	}
	
	/**
	 * @api.enable
	 * 
	 * @param array $data
	 */
	public function createBatch($data, $folder, $do_delete = false)
	{
		$new_batch = false;
		$batch_id = $data["batchid"] ?: ($new_batch = $data["className"]);
		
		$gen_path = $this->getPreConfigPath($folder);
		$QGEN_Config = $this->getPreConfig($folder);

		if ($new_batch)
		{
			if ($QGEN_Config["batches"][$batch_id])
				$batch_id = $batch_id . " #" . uniqid();
			$data["batchid"] = $batch_id;
		}
		
		if ((!$new_batch) && $do_delete)
			unset($QGEN_Config["batches"][$batch_id]);
		else
			$QGEN_Config["batches"][$batch_id] = $data;
		
		qArrayToCodeFile($QGEN_Config, "QGEN_Config", $gen_path);
		
		return $batch_id;
	}
	
	public function getPreConfig($watch_folder = null)
	{
		$folder = $watch_folder ?: $this->watchFolder;
		
		if (!QAutoload::GetWatchFolders()[$folder])
			throw new Exception("There is no watch folder with that path. ".$folder);
		
		$gen_path = $folder."generate.php";
		if (!file_exists($gen_path))
			throw new Exception("There watch folder has no generated file");
		
		include($gen_path);
		$QGEN_Config = ($QGEN_Config !== null) ? $QGEN_Config : [];
		
		return $QGEN_Config;
	}
	
	public function getPreConfigPath($watch_folder = null)
	{
		$folder = $watch_folder ?: $this->watchFolder;
		
		if (!QAutoload::GetWatchFolders()[$folder])
			throw new Exception("There is no watch folder with that path. ".$folder);
		
		$gen_path = $folder."generate.php";
		if (!file_exists($gen_path))
			throw new Exception("There watch folder has no generated file");
		
		return $gen_path;
	}
	
	/**
	 * @api.enable
	 * 
	 * @param string $watch_folder
	 */
	public function syncPreConfigs($watch_folder = null, $old_config = null, $batch_id = null, $save_mode = "merge")
	{
		$watch_folder = ($watch_folder !== null) ? $watch_folder : $this->watchFolder;
		
		$pre_config = $this->getPreConfig($watch_folder);
		if (!$pre_config)
			return false;
		$batches = $pre_config["batches"];
		if (!$pre_config)
			return false;
		
		if ($batches)
		{
			if ($batch_id !== null)
			{
				\Omi\Gens\ConfigGenerator::Generate($watch_folder, $batch_id, $batches[$batch_id], ($old_config && $old_config["batches"]) ? $old_config["batches"][$batch_id] : null, $save_mode);
			}
			else
			{
				foreach ($batches as $batch_id => $batch_data)
				{
					// $this->syncPreConfigBatch($watch_folder, $batch_id, $batch_data, ($old_config && $old_config["batches"]) ? $old_config["batches"][$batch_id] : null);
					\Omi\Gens\ConfigGenerator::Generate($watch_folder, $batch_id, $batch_data, ($old_config && $old_config["batches"]) ? $old_config["batches"][$batch_id] : null, $save_mode);
				}
				
				// @todo ... an easy way to trigger this 
				// \Omi\Gens\Generator::GenerateAllBatches($watch_folder);
			}
		}
		
		return true;
	}
	
	/**
	 * @api.enable
	 * 
	 * @param array $data
	 */
	public function generateCode($data, $folder, $mode = "merge")
	{
		// first save the batch
		$batch_id = $this->createBatch($data, $folder);
		return \Omi\Gens\Generator::GenerateBatch($folder, $batch_id, $mode);
	}
	
	/**
	 * @api.enable
	 * 
	 * @param array $data
	 */
	public function syncBatch($data, $folder, $mode = "merge")
	{
		// first save the batch
		$batch_id = $this->createBatch($data, $folder);
		return $this->syncPreConfigs($folder, null, $batch_id, $mode);
	}
	
}
