<?php

/**
 * Generates and patches platform standards
 */
class QCodeSync2
{
	use QCodeSync2_Upgrade, QCodeSync2_Utility, QCodeSync2_Gen_Model, QCodeSync2_Gen_Urls, QCodeSync2_Reorganize, QCodeSync2_Security;
	
	const Status_Added = 'added';
	const Status_Changed = 'changed';
	const Status_Changed_Dependencies = 'changed-deps';
	const Status_Moved = 'moved';
	const Status_Removed = 'removed';
	
	public static $PHP_LINT_CHECK = true;
	public static $PHP_LINT_CHECK_TPL = false;
	
	public $upgrage_mode = null;
	public $full_sync = false;
	
	protected $temp_code_dir;
	
	/**
	 * @var array
	 */
	protected $grouped_data;
	/**
	 * @var string[]
	 */
	protected $watch_folders_tags;
	/**
	 * @var string[]
	 */
	protected $tags_to_watch_folders;
	/**
	 * @var array
	 */
	protected $changes_by_class;
	/**
	 * @var array
	 */
	protected $info_by_class;
	/**
	 * @var array
	 */
	protected $prev_by_class;
	/**
	 * @var array
	 */
	protected $extends_map;
	/**
	 * @var string[]
	 */
	protected $autoload;
	/**
	 * @var string[]
	 */
	protected $autoload_for_sync;
	/**
	 * @var string[]
	 */
	protected $model_types;
	/**
	 * @var string[]
	 */
	protected $cache_types;
	/**
	 * @var boolean
	 */
	protected $empty_gens;
	/**
	 * @var integer
	 */
	protected $sync_started_at;
	/**
	 * @var array
	 */
	protected $dependencies;
	/**
	 * @var array
	 */
	protected $cache_get_info_by_layer_file;
	/**
	 * @var boolean
	 */
	protected $do_not_allow_empty_extended_by = false;
	/**
	 * @var QModelType
	 */
	protected $saved_data_class_info = null;
	/**
	 * @var boolean
	 */
	protected $inside_sync = false;
	/**
	 * @var boolean
	 */
	protected $model_only_run = false;
	/**
	 * @var boolean
	 */
	protected $has_model_changes = false;
	/**
	 * @var float
	 */
	protected $start_time = null;
	/**
	 * Know what JS generated files were reset in case of a full sync
	 * 
	 * @var array
	 */
	protected $js_gens_reset_map = [];
	/**
	 * Files being modified
	 * 
	 * @var array
	 */
	protected $modified_gens = [];
	/**
	 * @var array
	 */
	protected $class_to_info = [];
	
	public function init()
	{
		$this->modified_gens = [];
		
		$this->temp_code_dir = "temp/code/";
		$this->tags_to_watch_folders = \QAutoload::GetWatchFoldersByTags();
		$this->watch_folders_tags = array_flip($this->tags_to_watch_folders);
	}

	/**
	 * Resyncs the code
	 * 
	 * @param array $files List with all the files
	 * @param array $changed_or_added List with the changed or added files
	 * @param array $removed_files List with the removed files
	 */
	public function resync($files, $changed_or_added, $removed_files, $new_files, bool $full_resync = false, array $generator_changes = null)
	{
		$ex_ct = null;
				
		$this->start_time = microtime(true);
		
		ob_start();
		
		try
		{
			$this->init();

			$this->sync_started_at = microtime(true);
			if ($changed_or_added || $removed_files || $new_files)
				echo ('RESYNC STARTS @AT: '. (($this->sync_started_at - $_SERVER['REQUEST_TIME_FLOAT']) * 1000) . ' ms'), "<br/>\n";

			if (($this->upgrage_mode === null) && (defined('Q_RUN_CODE_UPGRADE_TO_TRAIT') && Q_RUN_CODE_UPGRADE_TO_TRAIT))
			{
				$this->upgrage_mode = true;
			}
			
			# just for testing now @TODO - remove it from here
			# else if (file_exists(".upgrade_possible_parent_issues.json"))
			# {
			#	static::after_upgrade();
			#	exit;
			# }
			if ($this->upgrage_mode)
			{
				$this->full_sync = true;
				$this->run_upgrade($files, $changed_or_added, $removed_files, $new_files);
				// exit after upgrade

				# throw new \Exception('finished upgrade');
				# return;
				die('FINISH UPGRADE');
			}
			
			if (defined('Q_RUN_CODE_NEW_AS_TRAITS') && Q_RUN_CODE_NEW_AS_TRAITS)
			{
				$this->full_sync = $full_resync;
				$this->do_not_allow_empty_extended_by = false;

				# if ($this->full_sync) # this should only be triggerd explicitly
				#	$this->empty_gens = true; # for testing

				$this->run_backend_fix = false;
				$this->model_only_run = true;

				if (defined('Q_GENERATED_VIEW_FOLDER_TAG') && Q_GENERATED_VIEW_FOLDER_TAG) # if ($this->full_sync)
				{
					\QApp::SetDataClass_Internal(Q_DATA_CLASS);

					# reset some basic info
					{
						$tags_to_watch_folders = $this->tags_to_watch_folders;
						$this->tags_to_watch_folders = [];
					}

					$second_stage_tags = [];

					$model_files = [];
					$model_changed_or_added = [];
					$model_removed_files = [];
					$model_new_files = [];

					foreach ($tags_to_watch_folders as $k => $v)
					{
						if ($second_stage_tags || ($k === Q_GENERATED_VIEW_FOLDER_TAG))
							$second_stage_tags[$k] = $v;
						else
						{
							$this->tags_to_watch_folders[$k] = $v;
							if ($files[$v])
								$model_files[$v] = $files[$v];
							if ($changed_or_added[$v])
								$model_changed_or_added[$v] = $changed_or_added[$v];
							if ($removed_files[$v])
								$model_removed_files[$v] = $removed_files[$v];
							if ($new_files[$v])
								$model_new_files[$v] = $new_files[$v];
						}
					}

					// foreach ()
					$this->watch_folders_tags = array_flip($this->tags_to_watch_folders);

					# first run a sync on the model only !!!
					# array $files, array $changed_or_added, array $removed_files, array $new_files, bool $sync_sql_meta = false
					
					$this->sync_code($model_files ?? [], $model_changed_or_added ?? [], $model_removed_files ?? [], $model_new_files ?? [], true);

					// next generate all the views :-)
					if (!defined('Q_DATA_CLASS'))
						throw new \Exception('Data class constant `Q_DATA_CLASS` must be defined !');

					# @TODO - remove all generated files

					# @TODO - set the correct value for this variable !
					$has_backend_config_changes = $generator_changes ? true : false;

					$generated_views = [];

					if ($this->full_sync || ($model_changed_or_added || $model_removed_files) || $has_backend_config_changes)
					{
						// if (!$this->full_sync)
						$ru = $this->full_sync ? null : $_SERVER["REQUEST_URI"];
						$rel_url = ((!$this->full_sync) && $ru && (substr($ru, 0, strlen(BASE_HREF)) === BASE_HREF)) ? substr($ru, strlen(BASE_HREF)) : null;

						if (defined('Q_BACKEND_URL_PREFIX') && Q_BACKEND_URL_PREFIX)
						{
							if (substr($rel_url, 0, strlen(Q_BACKEND_URL_PREFIX)) === Q_BACKEND_URL_PREFIX)
								$rel_url = substr($rel_url, strlen(Q_BACKEND_URL_PREFIX));
							else
								$rel_url = false;
						}

						if (is_string($rel_url))
						{
							$match_ru = null;
							$rc_ru = preg_match("/^([^\\/]+)/uis", $rel_url, $match_ru);
							$rel_url = $rc_ru ? $match_ru[1] : null;
						}

						if (($this->full_sync || $rel_url) && ((!defined('Q_DISABLE_BACKEND_GENERATE')) || (!Q_DISABLE_BACKEND_GENERATE)))
						{
							# @TODO - Is there a better solution here then to unlock autoload ? issue is that interface_exists is called
							\QAutoload::UnlockAutoload();
							try
							{

								$app_type = \QCodeStorage::Get_Cached_Class(Q_DATA_CLASS);
								
								$generator_classes_included = false;

								foreach ($app_type->properties as $property => $prop_info)
								{
									if ($prop_info->isScalar() || (!(
											$prop_info->hasCollectionType() ? $prop_info->getCollectionType()->hasInstantiableReferenceType() : $prop_info->hasInstantiableReferenceType()
											)))
									{
										# there is no data type that can be used
										echo "Grid::SKIPPING::{$property}\n";
										continue;
									}

									$prop_views = $prop_info->storage['views'];
									$prop_views_arr = $prop_views ? preg_split("/(\\s*\\,\\s*)/uis", $prop_info->storage['views'], -1, PREG_SPLIT_NO_EMPTY) : null;

									if ((!$this->full_sync) && ($rel_url !== $property) && ((!$prop_views_arr) || (!in_array($rel_url, $prop_views_arr))))
									{
										continue;
									}

									$config = [];
									// sync that one
									$config["from"] = $property;
									$config["className"] = Q_Gen_Namespace."\\".ucfirst($property);
									//$save_dir = rtrim(self::$SaveDirBase, "\\/") . "/" . ucfirst($prop);
									$config["gen_path"] = QGEN_SaveDirBase;
									$config["gen_config"] = QGEN_ConfigDirBase;

									if (!$generator_classes_included)
									{
										if (file_exists(Q_FRAME_BPATH . 'common/gens/IGenerator.php'))
										{
											# new way (may change)
											require_once(Q_FRAME_BPATH . 'common/gens/IGenerator.php');
											require_once(Q_FRAME_BPATH . 'common/gens/Grid_Config_.php');
											require_once(Q_FRAME_BPATH . 'common/gens/GridTpls.php');
											require_once(Q_FRAME_BPATH . 'common/gens/Grid.php');
										}
										else if (file_exists(Omi_Mods_Path.'generators/control/Grid/Grid.php'))
										{
											require_once Omi_Mods_Path . 'generators/control/Grid/IGenerator.php';
											require_once Omi_Mods_Path . 'generators/control/Grid/Grid_Config_.php';
											require_once Omi_Mods_Path . 'generators/control/Grid/GridTpls.php';
											require_once Omi_Mods_Path . 'generators/control/Grid/Grid.php';
										}
										else
										{
											throw new \Exception('Unable to locate grid generator');
										}

										$generator_classes_included = true;
									}

									try
									{
										echo "Grid::Generate({$property}) | START<br/>\n";
										
										$gen_ret = \Omi\Gens\Grid::Generate($config);
										
										if (isset($gen_ret[0]))
											$this->process_gen_changes($gen_ret[0]);
										echo "Grid::Generate({$property}) | DONE<br/>\n";
	
										$generated_views[$property] = $property;
										foreach ($prop_views_arr ?: [] as $prop_v)
											$generated_views[$prop_v] = $prop_v;
									}
									catch (\Exception $eeex_grid_gen)
									{
										echo "Grid::Generate({$property}) | ERROR | {$eeex_grid_gen->getMessage()} | {$eeex_grid_gen->getTraceAsString()}<br/>\n";
									}
								}
							}
							finally
							{
								\QAutoload::LockAutoload();
							}
						}
					}
					
					define('Q_SYNC_GENERATED_VIEWS', $generated_views);

					{
						$gens_folder = $second_stage_tags[Q_GENERATED_VIEW_FOLDER_TAG];

						$info = [];
						$files_state = $full_resync ? [] : [$gens_folder => $files[$gens_folder]];
						$changed = [];
						$new = [];

						$info[$gens_folder] = [];
						if (!$files_state[$gens_folder])
							$files_state[$gens_folder] = [];
						$changed[$gens_folder] = [];
						$new[$gens_folder] = [];

						// scan for changes inside Q_GENERATED_VIEW_FOLDER_TAG

						/* $full_resync = false, $debug_mode = false, $path = null, $avoid_folders = null, 
											$skip_on_ajax = true,
											&$info = null, &$files_state = null, &$changed = null, &$new = null, $root_folder = null,
											&$top_info = null, &$top_files_state = null, &$top_changed = null, &$top_new = null
						*/
						\QAutoload::ScanForChanges(false, false, $gens_folder, null, true,
												$info[$gens_folder], $files_state[$gens_folder], $changed[$gens_folder], $new[$gens_folder], 
												null,
												$info, $files_state, $changed, $new);

						/*					
						self::ScanForChanges($full_resync, $debug_mode, $folder, (($pos === 0) ? $avoid_frame_folders : null), $skip_on_ajax,
								$info[$folder], $files_state[$folder], $changed[$folder], $new[$folder], null, $info, $files_state, $changed, $new);
						*/
						$sync = new $this;
						$sync->full_sync = $this->full_sync;
						$sync->inside_sync = true;
						$this->model_only_run = false;

						$sync->init();

						$files_2 = $files;
						$changed_or_added_2 = $changed_or_added;
						$removed_files_2 = $removed_files;
						$new_files_2 = $new_files;

						if (!isset($files_2[$gens_folder]))
							$files_2[$gens_folder] = $info[$gens_folder];
						else
						{
							foreach ($info[$gens_folder] ?: [] as $k => $v)
								$files_2[$gens_folder][$k] = $v;
						}

						if (!isset($changed_or_added_2[$gens_folder]))
							$changed_or_added_2[$gens_folder] = $changed[$gens_folder];
						else
						{
							foreach ($changed[$gens_folder] ?: [] as $k => $v)
								$changed_or_added_2[$gens_folder][$k] = $v;
						}

						if (!isset($removed_files_2[$gens_folder]))
							$removed_files_2[$gens_folder] = $files_state[$gens_folder];
						else
						{
							foreach ($files_state[$gens_folder] ?: [] as $k => $v)
								$removed_files_2[$gens_folder][$k] = $v;
						}
						if (!isset($new_files_2[$gens_folder]))
							$new_files_2[$gens_folder] = $new[$gens_folder];
						else
						{
							foreach ($new[$gens_folder] ?: [] as $k => $v)
								$new_files_2[$gens_folder][$k] = $v;
						}

						// $sync->resync($files_2, $changed_or_added_2, $removed_files_2, $new_files_2, $full_resync);
						$sync->sync_code($files_2 ?? [], $changed_or_added_2 ?? [], $removed_files_2 ?? [], $new_files_2 ?? []);
					}

					return $second_stage_tags;
				}
				else
				{
					$this->sync_code($files ?? [], $changed_or_added ?? [], $removed_files ?? [], $new_files ?? []);
				}

				return true;
			}

			# if ($this->full_sync && file_exists(".upgrade_possible_parent_issues.json"))
			{
				# static::after_upgrade();
			}
		}
		
		catch (\Exception $ex)
		{
			$ex_ct = $ex;
		}
		finally
		{
			$modif_gens_path = \QAutoload::GetRuntimeFolder()."temp/modified_gen.json";
			$prev_modif = file_exists($modif_gens_path) ? json_decode(file_get_contents($modif_gens_path) ?: []) : [];
			if ($prev_modif)
			{
				foreach ($prev_modif ?: [] as $k => $v)
					if (!isset($this->modified_gens[$k]))
						$this->modified_gens[$k] = $v;
			}
			file_put_contents($modif_gens_path, json_encode($this->modified_gens, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_UNICODE));
			
			echo "<hr/>\n";
			echo "EXEC TIME FOR SYNC: ".round(microtime(true) - $this->start_time, 3)." sec";
			echo "<hr/>\n";
			echo "MEM PEAK: ".round(memory_get_peak_usage()/1024/1024, 2)." MB";
			
			$out_string = ob_get_clean();
			
			if ($_GET['force_resync'] || $ex_ct)
			{
				echo $out_string;
			}
			else
			{
				# \QWebRequest::AddHiddenOutput($out_string);
			}
			
			if ($ex_ct)
				throw $ex_ct;
		}
	}
	
	/**
	 * Resyncs the code
	 * 
	 * @param array $files List with all the files
	 * @param array $changed_or_added List with the changed or added files
	 * @param array $removed_files List with the removed files
	 */
	public function sync_code(array $files, array $changed_or_added, array $removed_files, array $new_files, bool $sync_sql_meta = false)
	{
		# disable it for a sec !
		
		# $this->temp_code_dir = "temp/code/";
		if (!is_dir($this->temp_code_dir))
			qmkdir($this->temp_code_dir);
		$this->dependencies = [];
		
		# ensure proper classes in the backend
		if ($this->run_backend_fix)
			$this->upgrade_backend_fix($files, $changed_or_added);
		
		# echo ('BEFORE COLLECT: '. ((microtime(true) - $this->sync_started_at) * 1000) . ' ms'), "<br/>\n";
		if ($removed_files === null)
			$removed_files = [];
		
		$all_files_grouped = $this->group_files_by_folder($files);
		
		$this->boot_info_by_class($all_files_grouped, $removed_files);
		
		# STAGE 1 - Collect information from files, determine namespaces and populate $this->info_by_class
		$this->sync_code__collect_info($all_files_grouped, $files, $changed_or_added, $removed_files, $new_files);
		
		if ((!$this->full_sync) && ((($first_stack_of_data = (isset($this->changes_by_class) ? reset($this->changes_by_class) : null)) === null) || 
										(!isset($first_stack_of_data['files']))))
		{
			// no change
			return null;
		}
		
		if ($this->full_sync && $this->empty_gens)
		{
			$emptied_gens = [];
			foreach ($this->info_by_class as $full_class_name => $info)
			{
				foreach ($info['files'] as $layer_tag => $layer_files)
				{
					$layer_path = $this->tags_to_watch_folders[$layer_tag];
					foreach ($layer_files as $file_tag => $header_inf)
					{
						$gens_dir = dirname($layer_path.$header_inf['file'])."/~gens/";
						if ((!$emptied_gens[$gens_dir]) && is_dir($gens_dir))
						{
							echo "REMOVING GENS DIR ... " . $gens_dir, "<br/>\n";
							$this->empty_gens_dir($gens_dir);
						}
						$emptied_gens[$gens_dir] = true;
					}
				}
			}
		}
		
		# echo 'AFTER COLLECT: '. ((microtime(true) - $this->sync_started_at) * 1000) . ' ms', "<br/>\n";
				
		# STAGE 2 - Populate dependencies
		if (!$this->full_sync)
			$this->sync_code__populate_dependencies();
		
		# STAGE 2.1. All previous info on $this->info_by_class is dropped except 'files' and info on $this->info_by_class['class_name'] is populated from 'files'
		$this->sync_code__setup_default_metas();

		# STAGE 3 - PRE Compile - we make sure that we can boot up PHP classes so that we can use native reflection
		$this->sync_code__pre_compile();
		
		# after pre-compile it's safer to do a reset
		# opcache_reset(); => use invalidate better !
		
		# STAGE 4 - create the required traits for : model (getters/setters & misc), views (templates/resources), url controllers
		$this->sync_code__compile_02();
		
		# STAGE 5 - cache data
		$this->cache_data($sync_sql_meta);
	}
	
	/**
	 * Collect information from files, determine namespaces and populate $this->info_by_class
	 * 
	 * @param array $files
	 * @param array $changed_or_added
	 * @param array $removed_files
	 * @param array $new_files
	 * @throws \Exception
	 */
	function sync_code__collect_info(array $all_files_grouped, array $files, array $changed_or_added, array $removed_files, array $new_files)
	{
		$lint_info = null;
		
		if ($this->full_sync)
		{
			# empty $this->temp_code_dir."hashes/" in full sync mode
			if ((!empty($this->temp_code_dir)) && is_dir($this->temp_code_dir) && is_dir($this->temp_code_dir."hashes/"))
				exec("rm " . escapeshellarg($this->temp_code_dir."hashes/") . " -R -f");
		}
		
		$loop_list_orig = $this->full_sync ? $files : $changed_or_added;
		# @TODO - this takes ~30 ms or more - modify QAutoload::ScanForChanges to give the info in this format !
		$loop_list = $this->full_sync ? $all_files_grouped : $this->group_files_by_folder($changed_or_added);
		
		if (!$this->full_sync)
			# we could cache this later, atm it's only taking ~2ms
			$this->get_info_by_layer_file_do_caching();
		
		# we loop either $files (if full_sync) or $changed_or_added
		foreach ($loop_list as $layer => $layer_files)
		{
			$layer_tag = $this->watch_folders_tags[$layer];
			if (!$layer_tag)
				throw new \Exception('Missing tag for code folder: '.$layer);
			
			if (static::$PHP_LINT_CHECK)
				# check PHP files for syntax errors with `php -l`
				$this->check_syntax($layer, $loop_list_orig[$layer], $lint_info, $this->full_sync);
			
			foreach ($layer_files as $file_dir => $dir_files)
			{
				foreach ($dir_files as $short_class_name__ => $class_files)
				{
					if (!$class_files)
						continue;
					
					$file_namespace = null;
					$locations = [];
					$short_class_name = null;
					$class = null;
					
					# The files may share: namespace, extends, implements
					#  .... => so will have to use $this->info_by_class also to make sure we have the right data
					
					# foreach ($class_files as $file_with_key => $mtime)
					$loop_class_files = $this->full_sync ? $class_files : $all_files_grouped[$layer][$file_dir][$short_class_name__];
					
					$has_only_res_in_folder = true;
					
					foreach ($loop_class_files as $file_with_key => $mtime)
					{
						$do_set_file = $this->full_sync || $class_files[$file_with_key];
						
						$set_gd_value = null;
						
						$file = $file_dir.substr($file_with_key, 3); // skip 03-
						
						if (!(file_exists($layer.$file)))
							throw new \Exception('Missing file: '.$layer.$file);
						
						$is_php_ext = (substr($file, -4, 4) === '.php');
						$is_tpl_ext = (substr($file, -4, 4) === '.tpl');
						list ($short_file_name, $full_ext) = explode(".", basename($file), 2);
						$full_ext = ".".$full_ext;

						if (!(strtolower($short_file_name[0]) !== $short_file_name[0]))
						{
							// this is to fix a bug for files like: 01-mvvm.js
							continue;
						}
						
						$last_8 = substr($file, -8, 8);
						$last_7 = substr($file, -7, 7);

						if (($is_php_ext && (($last_8 === '.gen.php') || ($last_8 === '.dyn.php'))) || 
							(($last_7 === '.min.js') || ($last_8 === '.min.css') || ($last_7 === '.gen.js') || ($last_8 === '.gen.css')))
							// just skip!
							continue;
						
						if (!($is_php_ext || $is_tpl_ext))
						{
							// css, js ... 
							$class = $short_class_name = $short_file_name;
							if (! (($full_ext === '.js') || ($full_ext === '.css')))
								throw new \Exception('Unexpected resource type: `'.$full_ext.'` in: '.$layer.$file);
							
							$set_gd_value = [
									"class" => $short_class_name, 
									'type' => 'resource', 
									'res_type' => trim($full_ext, '.'), 
									'file' => $file,
									'file_time' => $mtime,
									'layer' => $this->watch_folders_tags[$layer],
									'tag' => 'res@'.trim($full_ext, '.'),
									"final_class" => $short_class_name,
									'namespace' => $file_namespace];

							if ($do_set_file)
								$locations[] = $set_gd_value;
						}
						else
						{
							// echo "Evaluating: ".$layer.$file."<br/>\n";
							// plain PHP ... set it in the autoload
							$header_inf = \QPHPToken::ParseHeaderOnly($layer.$file, false);
							
							if (($header_inf['class'] !== $short_file_name) && ($header_inf['class'] !== $short_file_name."_".$layer_tag."_"))
							{
								$expected_class_name = $short_file_name."_".$layer_tag."_";
								qvar_dumpk(['$layer_tag' => $layer_tag, $layer.$file, $header_inf]);
								throw new \Exception('The basename of the file, up to the first dot, must be the class\'s short name (without namespace and without the layer\'s tag).'
										. ' Ex: Class_Name.php, Class_Name.tpl, Class_Name.form.tpl, Class_Name.url.php . OR Expected class name: ' . $expected_class_name);
							}

							$short_class_name = (($p = strrpos($header_inf["class"], "\\")) !== false) ? substr($header_inf["class"], $p + 1) : $header_inf["class"];
							if (!isset($header_inf["class"]))
								throw new \Exception('Unable to identify short class name in: '.$layer.$file);
							$header_inf['is_tpl'] = $is_tpl_ext;
							$header_inf['is_url'] = $is_php_ext && (substr($file, -8, 8) === '.url.php');
							$header_inf['is_php'] = $is_php_ext && (!$header_inf['is_url']);
							$header_inf['is_patch'] = $is_php_ext && ((!$header_inf['is_url']) && ($full_ext !== '.php'));
							$header_inf['file'] = $file;
							$header_inf['file_time'] = $mtime;
							$header_inf['layer'] = $this->watch_folders_tags[$layer];
							
							if ($header_inf['is_tpl'])
							{
								$header_inf['type'] = 'tpl';
								$header_inf['tag'] = 'tpl@'.substr(basename($header_inf['file']), strlen($short_class_name) + 1, -4);
							}
							else if ($header_inf['is_url'])
							{
								$header_inf['type'] = 'url';
								$header_inf['tag'] = 'url';
							}
							else if ($header_inf['is_php'])
							{
								$header_inf['type'] = 'php';
								$header_inf['tag'] = 'php';
							}

							$final_class_name = $short_class_name;
							
							if ($header_inf['is_php'])	
							{
								if (isset($header_inf['doc_comment']) && strpos($header_inf['doc_comment'], "@class.name") && 
										($parsed_dc = static::parse_doc_comment($header_inf['doc_comment'])) && $parsed_dc['class.name'])
								{
									$final_class_name = trim(trim(trim($parsed_dc['class.name'][1]), "* \t\n"));
								}
								else if ($short_class_name !== $short_file_name)
								{
									throw new \Exception("Pached classes names must be explicit: @class.name Class_Name");
								}
								/*
								else if (substr($short_class_name, -strlen("_".$layer_tag."_")) === "_".$layer_tag."_")
								{
									$final_class_name = substr($short_class_name, 0, -strlen("_".$layer_tag."_"));
									qvar_dumpk('@TODO - this code is not tested and we don\'t know if we want to support this ! $final_class_name #02', 
											$final_class_name, $short_class_name, $layer_tag, $layer, $header_inf);
									die;
								}
								*/
								
								if ($final_class_name !== $short_class_name)
									$header_inf['is_patch'] = true;

								if ($parsed_dc['class.abstract'])
									$header_inf['@class.abstract'] = strtolower(trim($parsed_dc['class.abstract'][1])) !== 'false';
								if ($parsed_dc['class.final'])
									$header_inf['@class.final'] = strtolower(trim($parsed_dc['class.final'][1])) !== 'false';
							}
							
							$header_inf['final_class'] = $final_class_name;					
							$class = $final_class_name;

							if ($header_inf['is_patch'] && ($header_inf['class'] === $final_class_name))
							{
								qvar_dumpk('$header_inf', $header_inf);
								throw new \Exception('Can not compile: `'.$layer.$file.'` because the name of class will conflict with the compiled class\'s name');
							}

							if ((!$file_namespace) && $header_inf['namespace'])
								$file_namespace = $header_inf['namespace'];
							else if ($file_namespace && (!$header_inf['namespace']))
								$header_inf['namespace'] = $file_namespace;

							if ($do_set_file)
								$locations[$header_inf['tag']] = $header_inf;
							
							$has_only_res_in_folder = false;
						}
					}
					
					// test if it extends
					$full_class_name = \QPHPToken::ApplyNamespaceToName($class, $file_namespace);
					$info_by_class_files = $this->info_by_class[$full_class_name]['files'][$layer_tag];
					
					foreach ($info_by_class_files ?: [] as $header_inf_tag => $header_inf)
					{
						// skip existing / updated entries
						if ($locations[$header_inf_tag])
							continue;
						
						// ensure namespace if missing
						if ((!$file_namespace) && $header_inf['namespace'])
							$file_namespace = $header_inf['namespace'];
						
						if ($header_inf['type'] !== 'resource')
							$has_only_res_in_folder = false;
					}
					
					if ($has_only_res_in_folder)
						# if only resources (ex: css/js) we will not setup (at the moment) an entry
						continue;
					
					foreach ($locations as $header_inf)
					{
						# test for changes
						if ((!$this->full_sync) && ($prev_version = $info_by_class_files[$header_inf['tag']]))
						{
							$same_time = ($prev_version['file_time'] === $header_inf['file_time']);
							$same_content = false;
							if ($same_time && ($save_state_path = $this->temp_code_dir."hashes/".$layer_tag."/".$file)
										&& file_exists($save_state_path))
							{
								$prev_content = gzuncompress(\QEncrypt::Decrypt_With_Hash(file_get_contents($save_state_path)));
								$same_content = ($prev_content === file_get_contents($layer.$header_inf['file']));
							}

							if ($same_content && $same_time)
								$header_inf['status'] = static::Status_Moved;
							else
								$header_inf['status'] = static::Status_Changed;	
						}
						else
						{
							$header_inf['status'] = static::Status_Added;
						}
						
						if ($file_namespace && (!$header_inf['namespace']))
							$header_inf['namespace'] = $file_namespace;
						$header_inf['class_full'] = $full_class_name;
						
						# if ($this->full_sync)
						$this->info_by_class[$full_class_name]['files'][$header_inf['layer']][$header_inf['tag']] = $header_inf;
						$this->class_to_info[($header_inf['namespace'] ? $header_inf['namespace']."\\" : '').$header_inf['class']] = $header_inf;
						
						# else 
						if (!$this->full_sync)
						{
							if (isset($this->changes_by_class[$full_class_name]['files'][$header_inf['layer']][$header_inf['tag']]))
							{
								qvar_dumpk($full_class_name, $header_inf['layer'], $header_inf['tag'], $header_inf);
								throw new \Exception('This should not duplicate: '.$full_class_name." | ". json_encode($header_inf));
							}
							else
							{
								# qvar_dumpk("setting it up once!");
								$this->changes_by_class[$full_class_name]['files'][$header_inf['layer']][$header_inf['tag']] = $header_inf;
							}
						}
						
						$save_state_path = $this->temp_code_dir."hashes/".$layer_tag."/".$file;
						$save_state_dir = dirname($save_state_path);
						if (!is_dir($save_state_dir))
							qmkdir($save_state_dir);
						file_put_contents($save_state_path, \QEncrypt::Encrypt_With_Hash(gzcompress(file_get_contents($layer.$file))));
					}
				}
			}
		}
		
		# REMOVED !
		if (!$this->full_sync)
		{
			foreach ($removed_files ?: [] as $layer_path => $layer_files)
			{
				# $header_inf['removed'] = true;
				// we need to test and see if it's a moved file !
				foreach ($layer_files as $file => $file_mtime)
				{
					# (($removed_count === 1) && ($in_layer_count === 1)) => in case we only have one removed file we will return now, there is no point to fill the cache
					$layer_tag = $this->watch_folders_tags[$layer_path];
					if (!$layer_tag)
						throw new \Exception('Missing layer tag. '.$layer_path);
					list ($full_class_name, $header_inf) = $this->get_info_by_layer_file($layer_tag, $file);
					if (!($full_class_name && $header_inf))
					{
						throw new \Exception("Information about the removed file could not be found. Please do a full resync. layer={$layer_tag} | file=" . $file);
					}

					if (isset($this->changes_by_class[$full_class_name]['files'][$layer_tag][$header_inf['tag']]))
					{
						# the file was moved, it was managed previously
					}
					else
					{
						# the file was removed
						$header_inf['status'] = static::Status_Removed;
						$this->changes_by_class[$full_class_name]['files'][$layer_tag][$header_inf['tag']] = $header_inf;
						
						if (file_exists($save_state_path = $this->temp_code_dir."hashes/".$layer_tag."/".$file))
							unlink($save_state_path);
					}
				}
			}			
		}
		
		# return $this->grouped_data;
	}
	
	/**
	 * The aim here is to prepare the code for PHP's reflection so we can use it instead of our internal engine.
	 * 
	 * @throws \Exception
	 */
	function sync_code__pre_compile()
	{
		$this->autoload = [];
		$this->autoload_for_sync = [];
		
		# @TODO - we also need to cache autoload_for_sync
		
		if (!$this->full_sync)
		{
			$temp_folder = QAutoload::GetRuntimeFolder()."temp/";
			if (file_exists($temp_folder."autoload.php"))
			{
				$_Q_FRAME_LOAD_ARRAY = null;
				# do not wrap it in a function !!! it will slow down a lot, it copies the data!
				require($temp_folder."autoload.php");
				$this->autoload = $_Q_FRAME_LOAD_ARRAY;
				if (!is_array($this->autoload))
					throw new \Exception('Autoload is wrong. You should force a full resync.');
			}
			else
				throw new \Exception('Autoload is missing. You should force a full resync.');
		}
		
		foreach ($this->info_by_class as $full_class_name => &$info)
		{
			$is_patch = $info['is_patch'];
			$has_plain_class = $info['has_php'] && (!$is_patch);
			
			# is_model[bool]: true
			if ($info['has_php'])
			{
				foreach ($info['files'] as $layer => $layer_files)
				{
					$layer_folder = $this->tags_to_watch_folders[$layer] ?? null;
					if (empty($layer_folder))
					{
						if ($this->model_only_run)
							continue;
						else
							throw new \Exception('Missing layer.');
					}

					$lf_php = $layer_files['php'];
					if ($has_plain_class)
					{
						$this->autoload_for_sync[$full_class_name] = $layer_folder . $lf_php['file'];
					}
					else
					{
						if (!$lf_php)
						{
							$lf_php = ($layer_files['url'] ?? reset($layer_files));
						}

						# @TODO - test URL & misc 
						# abstract
						$abstract_class_name = (isset($lf_php["namespace"]) ? $lf_php["namespace"] . "\\" : ""). $lf_php['class'];
						
						if (isset($layer_files['php']))
							$this->autoload_for_sync[$abstract_class_name] = $layer_folder . $lf_php['file'];

						$gens_layer = $info['gens_layer'] ?? null;
						# real
						if ((!$gens_layer) || ($gens_layer === $layer))
						{
							if (!isset($info['gens_dir']))
								throw new \Exception('Gens dir not specified.');

							$short_class_name = end(explode("\\", $full_class_name));

							$extra_gen_content = null;
							if ($info['has_url'])
							{
								$extra_gen_content = "	public function getUrlForTag(\$tag = '', \$_arg0 = null, \$_arg1 = null, \$_arg2 = null, \$_arg3 = null, \$_arg4 = null, \$_arg5 = null, \$_arg6 = null, \$_arg7 = null, \$_arg8 = null, \$_arg9 = null, \$_arg10 = null, \$_arg11 = null, \$_arg12 = null, \$_arg13 = null, \$_arg14 = null, \$_arg15 = null) {}\n".
												"	public function loadFromUrl(\QUrl \$url, \$parent = null) {}\n".
												"	public function initController(\QUrl \$url = null, \$parent = null) {}\n";
							}

							# /home/alex/voip-fuse/omi-frame/src/io/~gens/QFile.ignore.gen.php
							$expected_path = $info['gens_dir'] . "{$short_class_name}.ignore.gen.php";

							if (file_exists($expected_path) && (!$this->full_sync))
								$this->autoload_for_sync[$full_class_name] = $expected_path;
							else
								$this->autoload_for_sync[$full_class_name] = $this->ensure_class($full_class_name, $short_class_name, $info['gens_dir'], $abstract_class_name, $lf_php, [], true, $extra_gen_content);
						}
					}
				}
			}

			if ((!$this->full_sync) && (!$this->changes_by_class[$full_class_name]))
			{			
				continue;
			}
			
			echo "PRE COMPILE :: {$full_class_name}<br/>\n";
			
			if (!$this->full_sync)
			{
				if ($info['count_not_res'] === 0)
				{
					qvar_dumpk('@TODO - this class was removed for good. Cleanup !!!');
				}
				else
				{
					# @TODO - this is a big todo
					# if removed_file_count > 0 ....
					if ($removed_layers) # call $this->get_removed_layers
					{
						# todo ... cleanup layers
					}
					if ($removed_files) # call $this->get_removed_files (exclude layers)
					{
						/*
						foreach ($removed_files as $rm_layer => $removed_files)
						{
							if ($removed_layers[$rm_layer])
								# ???
								continue;
						}
						*/
					}
				}
			}
			
			if ($info['has_tpl'] || $is_patch || ($info['res'] && (!$has_plain_class)) || $info['has_url'])
			{
				# ($resources && (!$has_plain_class)) => we will allow resources for plain classes!
				if ($has_plain_class)
				{
					qvar_dumpk('$full_class_name, $info', $full_class_name, $info);
					throw new \Exception("Can not compile class `{$full_class_name}` because the definition in file "
											. "	`{$has_plain_class}` already uses the desired compile name.");
				}
				
				$gens_dir = $info['gens_dir'];
				if (!is_dir($gens_dir))
					qmkdir($gens_dir);
				
				$short_class_name = end(explode("\\", $full_class_name));
				$namespace = ($short_class_name === $full_class_name) ? null : 
								substr($full_class_name, 0, - strlen($short_class_name) - 1);
				
				$patch_extends = $info['generated_extends'];
				$patch_extends_info = $info['generated_extends_info'];

				$info['is_model'] = 
					$is_model = $this->check_if_qmodel($full_class_name);
				
				$include_traits = [];
				
				$gen_file_wo_ext = $gens_dir.$short_class_name;
				// @TODO - maybe we should cleanup the main class if exists, so when we start using reflection it will work
				
				$needs_class_setup = $this->full_sync ? true : false;
				$prev_version = $this->full_sync ? null : $this->prev_by_class[$full_class_name];
				
				if (!$this->full_sync)
				{
					if ((!$prev_version) || 
							($prev_version['is_patch'] != $is_patch) || 
							($prev_version['is_model'] !== $is_model) || 
							($prev_version['has_tpl'] != $info['has_tpl']) || 
							($prev_version['has_url'] != $info['has_url'])
							)
					{
						$needs_class_setup = true;
						$include_traits["{$short_class_name}_GenModel_"] = "{$short_class_name}_GenModel_";
					}
				}
				
				if ($is_model)
				{
					$needs_class_setup = true;
					if (!$this->full_sync)
						$include_traits["{$short_class_name}_GenModel_"] = "{$short_class_name}_GenModel_";
				}
				
				if (file_exists($gen_file_wo_ext.'.model.gen.php'))
				{
					if ((!$this->full_sync) && $is_model)
					{
						$al_pointer = &$this->autoload[$full_class_name.'_GenModel_'];
						if ($al_pointer !== $gen_file_wo_ext.'.model.gen.php')
						{
							$al_pointer = $gen_file_wo_ext.'.model.gen.php';
							echo "AUTOLOAD: `{$full_class_name}_GenModel_` => ".$gen_file_wo_ext.".model.gen.php<br/>\n";
						}
						unset($al_pointer);
					}
					else
					{
						$needs_class_setup = true;
						# $this->unlink($gen_file_wo_ext.'.model.gen.php'); # we have some issues here ... @TODO - we need to make it less fragile
						unset($this->autoload[$full_class_name.'_GenModel_']);
					}
				}

				if (file_exists($gen_file_wo_ext.'.view.gen.php'))
				{
					if ($info['has_tpl'])
					{
						$al_pointer = &$this->autoload[$full_class_name.'_GenView_'];
						if ($al_pointer !== $gen_file_wo_ext.'.view.gen.php')
						{
							$al_pointer = $gen_file_wo_ext.'.view.gen.php';
							echo "AUTOLOAD: `{$full_class_name}_GenView_` => ".$gen_file_wo_ext.".view.gen.php<br/>\n";
						}
						unset($al_pointer);
					}
					else
					{
						$needs_class_setup = true;
						$this->unlink($gen_file_wo_ext.'.view.gen.php');
						unset($this->autoload[$full_class_name.'_GenView_']);
					}
				}
				
				if (file_exists($gen_file_wo_ext.'.url.gen.php'))
				{
					if ($info['has_url'])
					{
						$al_pointer = &$this->autoload[$full_class_name.'_GenUrl_'];
						if ($al_pointer !== $gen_file_wo_ext.'.url.gen.php')
						{
							$al_pointer = $gen_file_wo_ext.'.url.gen.php';
							echo "AUTOLOAD: `{$full_class_name}_GenUrl_` => ".$gen_file_wo_ext.".url.gen.php<br/>\n";
						}
						unset($al_pointer);
					}
					else
					{
						$needs_class_setup = true;
						$this->unlink($gen_file_wo_ext.'.url.gen.php');
						unset($this->autoload[$full_class_name.'_GenUrl_']);
					}
				}
				else if ($info['has_url'])
				{
					# we need to make sure we have the interface methods or we will get an error !
					$url_trait_str = "<?php\n\n".($namespace ? "namespace ".$namespace.";\n\n" : "").
							"trait {$short_class_name}_GenUrl_\n".
							"{\n\n".
							"	public function getUrlForTag(\$tag) {}\n".
							"	public function loadFromUrl(\QUrl \$url, \$parent = null) {}\n".
							"	public function initController(\QUrl \$url = null, \$parent = null) {}\n".
							"\n}\n\n";
					$this->file_put_contents($gen_file_wo_ext.'.url.gen.php', $url_trait_str);
					opcache_invalidate($gen_file_wo_ext.'.url.gen.php');
					
					if ($this->autoload[$full_class_name.'_GenUrl_'] !== $gen_file_wo_ext.'.url.gen.php')
					{
						$this->autoload[$full_class_name.'_GenUrl_'] = $gen_file_wo_ext.'.url.gen.php';
						echo "AUTOLOAD: `{$full_class_name}_GenUrl_` => ".$gen_file_wo_ext.".url.gen.php<br/>\n";
					}
					
					$include_traits["{$short_class_name}_GenUrl_"] = "{$short_class_name}_GenUrl_";
					echo "CREATE URL EMPTY: `{$full_class_name}_GenUrl_` => ".$gen_file_wo_ext.".url.gen.php<br/>\n";
					
					$needs_class_setup = true;
				}
				
				if ($needs_class_setup)
				{
					echo "ensure_class :: {$full_class_name} | {$gens_dir} | {$patch_extends} | ". implode(", ", $include_traits)." <br/>\n";
					$class_path_full = $this->ensure_class($full_class_name, $short_class_name, $gens_dir, $patch_extends, $patch_extends_info, $include_traits);
					$this->autoload[$full_class_name] = $class_path_full;
					echo "AUTOLOAD FULL CLASS NAME: `{$full_class_name}` => {$class_path_full}<br/>\n";
				}
				else if (!($class_path_full = $this->autoload[$full_class_name]))
					throw new \Exception('Missing autoload info for class: '.$full_class_name.'. Please consider a full resync.');
				
				# this should be cached in non-full-sync mode
				foreach ($info['patch_autoload'] ?: [] as $patch_class_full => $patch_path_full)
				{
					$al_pointer = &$this->autoload[$patch_class_full];
					if ($al_pointer !== $patch_path_full)
					{
						$al_pointer = $patch_path_full;
						echo "AUTOLOAD patch: `{$patch_class_full}` => {$patch_path_full}<br/>\n";
					}
					unset($al_pointer);
				}
			}
			else if ($has_plain_class)
			{
				$classes_files = $info['classes_files'];
				if (count($classes_files) !== 1)
					throw new \Exception('Too many definitions in files for the same class: '.$full_class_name." | ".implode("; ", $classes_files));
				$class_path_full = realpath(reset($classes_files));
				$this->autoload[$full_class_name] = $class_path_full;
				echo "AUTOLOAD PLAIN PHP: `{$full_class_name}` => {$class_path_full}<br/>\n";
			}
		}
		
		# if (!$this->model_only_run)
		{
			# qvar_dump($this->autoload_for_sync);
			# die;
		}
		
		\QAutoload::SetAutoloadArray($this->autoload_for_sync);
	}
	
	function sync_code__compile_02()
	{
		foreach ($this->info_by_class as $full_class_name => $info)
		{
			# echo "sync_code__compile_02 :: {$full_class_name}\n";
			// if ($full_class_name !== 'Omi\VF\View\Partners')
			//	continue;
			// $info = $this->full_sync ? $ch_info : $this->info_by_class[$full_class_name];
			if ((!$this->full_sync) && (!$this->changes_by_class[$full_class_name]))
				continue;
			
			echo "COMPILE DO :: {$full_class_name}<br/>\n";
			
			# # @TODO - ugly fix | fix deps issues for model classes !
			if ((!$this->full_sync) && (!empty($info['files'])))
			{
				$last_status = null;
				foreach ($info['files'] as $layer_tag => &$files_list)
				{
					foreach ($files_list as $file_tag => &$header_inf)
					{
						if (($header_inf['type'] === 'php') && $header_inf['is_patch'])
						{
							if ($header_inf['status'] !== null)
								$last_status = $header_inf['status'];
							else if ($last_status !== null)
							{
								# @TODO - ugly fix 
								$header_inf['status'] = static::Status_Changed;
							}
						}
					}
				}			
			}
			
			$traits_on_gen = [];
			$php_class_done = false;
			$url_trait_done = false;
			$has_tpl = $info['has_tpl'];
			$has_url = $info['has_url'];
			
			$render_methods = [];
			$tpls_tags_done = [];
			
			$removed_files = [];
			$resync_template_trait = false;
			
			$model_deps_stack = [];
						
			foreach (array_reverse($info['files']) ?: [] as $layer_tag => $files_list)
			{
				$layer_path = $this->tags_to_watch_folders[$layer_tag];
				
				foreach ($files_list as $file_tag => $header_inf)
				{
					if ($this->full_sync)
						$added_or_changed = true;
					else
					{
						if ($header_inf['status'] === static::Status_Removed)
						{
							$removed_files[$layer_tag][$file_tag] = $header_inf;
							if ($header_inf['type'] === 'tpl')
							{
								$resync_template_trait = true;
								
								# cleanup the generated tpl file if it exists
								if (file_exists(($possible_gen_path = $info['gens_dir'].$this->get_generated_xml_template_name($header_inf))))
								{
									$this->unlink($possible_gen_path);
								}
							}
							continue;
						}
						$added_or_changed = (($header_inf['status'] === static::Status_Changed) || 
											($header_inf['status-deps'] === static::Status_Changed_Dependencies) ||
											($header_inf['status'] === static::Status_Added));
					}
					
					if ($header_inf['type'] === 'tpl')
					{
						// echo "EVAL :: {$full_class_name} :: {$header_inf['tag']} <br/>\n";
						if (!$tpls_tags_done[$header_inf['tag']])
						{
							if ($added_or_changed)
								echo "compile_template_method({$header_inf['tag']}) :: {$full_class_name}<br/>\n";
							# we should cache this to get faster results
							$render_meth = $this->compile_template_method($full_class_name, $header_inf, $info, $added_or_changed);
							$render_methods[$render_meth['name']] = $render_meth;
							$tpls_tags_done[$header_inf['tag']] = true;
							$resync_template_trait = true;
						}
					}
					else if ($header_inf['type'] === 'php')
					{
						if ((!$php_class_done) && $header_inf['is_patch'])
						{
							if (!($has_tpl || $has_url))
							{
								# if ($this->model_only_run) # @TODO - this was a fix for empty view classes !!! not sure if this is a good idea ?!!
								{
									echo "compile_model :: {$full_class_name}<br/>\n";
									list($trait_name, $trait_path) = $this->compile_model($full_class_name, $header_inf, $info, $added_or_changed, $model_deps_stack);
									
									$model_deps_stack[] = $header_inf;
									
									if ($trait_name)
										$traits_on_gen[$trait_name] = $trait_path;
									$php_class_done = true;
									$this->model_types[$full_class_name] = $full_class_name;
								}
							}
							$this->cache_types[$full_class_name] = $full_class_name;
						}
						else if (($full_class_name === 'QIModel') || class_implements($full_class_name)['QIModel'])
						{
							$this->model_types[$full_class_name] = $full_class_name;
							$this->cache_types[$full_class_name] = $full_class_name;
						}
						# else # will be ignored anyway
						#	$this->cache_types[$full_class_name] = $full_class_name;
					}
					else if ($header_inf['type'] === 'url')
					{
						if (!$url_trait_done)
						{
							list($trait_name, $trait_path) = $this->compile_url_controller($full_class_name, $header_inf, $info, $added_or_changed);
							if ($trait_name)
								$traits_on_gen[$trait_name] = $trait_path;
							$url_trait_done = true;
						}
					}
					else if ($header_inf['type'] === 'resource')
					{
						# @TODO
					}
					else
					{
						# qvar_dumpk($header_inf, get_defined_vars());
						throw new \Exception('Unknown type: '.$header_inf['type']." | ".json_encode($header_inf));
					}
				}	
			}
			
			if ($resync_template_trait || $render_methods)
			{
				# handle them here
				# @TODO - if a template was removed and we have no more render methods
				list($trait_name, $trait_path) = $this->compile_template_trait($full_class_name, $header_inf, $info, $render_methods);
				if ($trait_name)
					$traits_on_gen[$trait_name] = $trait_path;
			}
			
			if ($traits_on_gen)
			{
				$last_gen_info = $this->info_by_class[$full_class_name]['generated_extends_info'];
				
				$class_ns = $last_gen_info['namespace'];
				$extends_full_name = $this->info_by_class[$full_class_name]['generated_extends']; // $class_ns ? \QPHPToken::ApplyNamespaceToName($last_gen_info['class'], $class_ns) : $last_gen_info['class'];
				
				$this->compile_class($full_class_name, end(explode("\\", $full_class_name)), $info['gens_dir'], $extends_full_name, $last_gen_info, array_keys($traits_on_gen));
				
				foreach ($traits_on_gen as $trait_name => $trait_path)
				{
					$trait_full_name = $class_ns ? \QPHPToken::ApplyNamespaceToName($trait_name, $class_ns) : $trait_name;
					if (!$trait_full_name)
					{
						qvar_dumpk($full_class_name, $traits_on_gen);
						throw new \Exception('@check');
					}
					$this->autoload[$trait_full_name] = $trait_path;
				}
			}
		}
	}
	
	function ensure_class(string $full_class_name, string $short_class_name, string $gen_dir, string $extend_class, 
									array $extends_info, array $include_traits, bool $for_generating_only = false, string $extra_gen_content = null)
	{
		$gen_path = $gen_dir.$short_class_name. ($for_generating_only ? '.ignore' : '') . ".gen.php";
		
		$expected_content = $this->compile_setup_class($full_class_name, $short_class_name, $extend_class, $extends_info['namespace'], $extends_info['doc_comment'], $extends_info);
		
		
		# in case the file does not exist, or the begining is not what we expect, reset it
		{
			# @TODO - if the file already exists, make sure the triats inside it are there & ok for autoload !
			$content_str = "";
			# if ($include_traits)
			{
				$content_str .= $expected_content[0];
				
				if ($include_traits)
					$content_str .= "	use ".implode(", ", $include_traits).";\n\n";
				if ($extra_gen_content !== null)
					$content_str .= $extra_gen_content;
					
				$content_str .= $expected_content[1];
			}
			#else
			#	$content_str = implode("", $expected_content);
			
			if ((!file_exists($gen_path)) || (file_get_contents($gen_path) !== $content_str))
			{
				if (!is_dir(dirname($gen_path)))
					qmkdir(dirname($gen_path));
				
				$rc = $this->file_put_contents($gen_path, $content_str);
				if ($rc === false)
					throw new \Exception('Unable to write to: '.$gen_path);
				opcache_invalidate($gen_path);
			}
		}
		if (!file_exists($gen_path))
			throw new \Exception('Unable to setup class file: '.$gen_path);
		
		return realpath($gen_path);
	}
	
	function compile_class(string $full_class_name, string $short_class_name, string $gen_dir, string $extend_class, array $extends_info, array $add_traits)
	{
		$gen_path = $gen_dir.$short_class_name.".gen.php";
		// echo "compile_class: ",$gen_path,"<br/>\n"; 
		// the class itself, extends, pull from the last class the doc comment so it's not lost ! & namespace 
		// getters/setters
		// (deprecated) api methods | I think this is deprecated !
		$class_parts = $this->compile_setup_class($full_class_name, $short_class_name, $extend_class, $extends_info['namespace'], $extends_info['doc_comment'], $extends_info);
		$class_str = $class_parts[0];
		if ($add_traits)
			$class_str .= "\tuse ".implode(", ", $add_traits).";\n\n";
		$class_str .= $class_parts[1];
		
		$this->file_put_contents($gen_path, $class_str);
		opcache_invalidate($gen_path);
		
		return $class_str;
	}
	
	function compile_model(string $full_class_name, array $header_inf, array $full_class_info, bool $added_or_changed, array $merge_dependencies_stack = [])
	{
		echo "compile_model :: {$full_class_name}\n";
		
		foreach ($merge_dependencies_stack as $dep_header_info)
		{
			if (empty($header_inf) || empty($header_inf['class_full']) || empty($header_inf['layer']) || empty($header_inf['tag']))
			{
				qvar_dumpk("zzzzzz", get_defined_vars());
				throw new \Exception('not ok!');
			}
			
			$this->dependencies[$header_inf['class_full']][$header_inf['layer']][$header_inf['tag']]
					[$dep_header_info['class_full']][$dep_header_info['layer']][$dep_header_info['tag']] = $dep_header_info['tag'];
		}
		
		$short_class_name = $header_inf['final_class'];
		$tait_name = $short_class_name."_GenModel_";
		$gen_path = $full_class_info['gens_dir'];
		
		if ((!$added_or_changed) && file_exists($gen_path.$short_class_name.".model.gen.php"))
			return [$tait_name, $gen_path.$short_class_name.".model.gen.php"];
		
		$namespace = $header_inf['namespace'];
		
		$setter_methods = $this->generate_model_methods(new ReflectionClass($full_class_name));
		
		# do it always , as it is a model (less complexity)
		# if ($setter_methods) # later add || $security_methods ... and so on
		{
			list ($trait_start_str, $trait_end_str) = $this->compile_setup_trait($tait_name, $namespace);

			foreach ($setter_methods as $method_str)
				$trait_start_str .= $method_str;

			$this->file_put_contents($gen_path.$short_class_name.".model.gen.php", $trait_start_str.$trait_end_str);
			opcache_invalidate($gen_path.$short_class_name.".model.gen.php");

			return [$tait_name, $gen_path.$short_class_name.".model.gen.php"];
		}
		# else
		#	return [null, null];
	}
	
	function compile_url_controller(string $full_class_name, array $header_inf, array $full_class_info, bool $added_or_changed)
	{
		// $added_or_changed
		$trait_name = $header_inf["final_class"]."_GenUrl_";
		$trait_path = $full_class_info['gens_dir'].$header_inf["final_class"].".url.gen.php";
		if (!$added_or_changed)
		{
			return [$trait_name, $trait_path];
		}
		
		list ($url_ctrl_tokens_obj, $gen_info) = $this->compile_xml_file($header_inf);
		
		$url_controller = $url_ctrl_tokens_obj->generateUrlController($gen_info);
		
		// save it, then return it
		$trait_obj = $url_controller->findFirstPHPTokenClass();
		$trait_obj->setClassName($trait_name);
		$url_controller_str = "<?php\n\n";
		if ($header_inf["namespace"])
			$url_controller_str .= "namespace ".$header_inf["namespace"].";\n\n";
		$url_controller_str .= $trait_obj;

		$this->file_put_contents($trait_path, $url_controller_str);
		opcache_invalidate($trait_path);
		
		return [$trait_name, $trait_path];
	}
	
	function compile_template_trait(string $full_class_name, array $header_inf, array $full_class_info, array $render_methods)
	{
		$trait_name = $header_inf["final_class"]."_GenView_";
		// save it, then return it
		$tpl_trait_str = "<?php\n\n";
		if ($header_inf["namespace"])
			$tpl_trait_str .= "namespace ".$header_inf["namespace"].";\n\n";
		$tpl_trait_str .= "trait {$trait_name}\n{\n\n";
		
		$all_resources = [];
		# setup resources here
		{
			$tmp_class_name = $full_class_name;
			
			do
			{
				$tmp_class_info = $this->info_by_class[$tmp_class_name];
				foreach ($tmp_class_info['res'] ?: [] as $r_type => $r_items_list)
				{
					# we need to order them to have something like : QObject => QModel => ... => DropDown => DropDown.gen.js
					foreach (array_reverse($r_items_list) ?: [] as $r_items)
					{
						$all_resources[$r_type][] = ['final_class' => $r_items['final_class'], 'res_type' => $r_items['res_type'], 
												'file' => $r_items['file'], 'layer' => $r_items['layer'],
												'layer_path' => $this->tags_to_watch_folders[$r_items['layer']],];
					}
				}
				$tmp_class_name = $tmp_class_info['extends'];
			}
			while ($tmp_class_name);
			
			# we need to order them to have something like : QObject => QModel => ... => DropDown => DropDown.gen.js
			foreach ($all_resources as $k => $v)
				$all_resources[$k] = array_reverse($v);
		}
		
		$tpl_trait_str .= "\t"."protected static function _get_Tpl_Compiled_Res()\n\t{\n\t\t"."return ".var_export($all_resources, true).";\n\t}\n\n";
		
		foreach ($render_methods as $r_meth)
			$tpl_trait_str .= $r_meth['body'];
		
		$tpl_trait_str .= "\n}\n\n";
		
		$trait_path = $full_class_info['gens_dir'].$header_inf["final_class"].".view.gen.php";
				
		$this->file_put_contents($trait_path, $tpl_trait_str);
		opcache_invalidate($trait_path);
		
		return [$trait_name, $trait_path];
	}
	
	function get_generated_xml_template_name(array $header_inf, string $method_tag = null)
	{
		if (!$method_tag)
			list(, $method_tag) = $this->compile_template_get_method_name_and_tag($header_inf['tag']);
		return $header_inf["final_class"].".ztpl".($method_tag ? '.' : '').$method_tag.".gen.php";
	}
	
	function compile_template_method(string $full_class_name, array $header_inf, array $full_class_info, bool $added_or_changed)
	{
		list($method_name, $method_tag) = $this->compile_template_get_method_name_and_tag($header_inf['tag']);
		$include_name = $this->get_generated_xml_template_name($header_inf, $method_tag);
		
		// extract defaults from ($header_inf['q-args'])
		$q_args = $header_inf['q-args'] ?? null;
		if (($q_args === null) && isset($full_class_info['files']))
		{
			# @TODO - try to inherit and not to default
			foreach ($full_class_info['files'] as $layer_taag => $items)
			{
				if ($layer_taag === $header_inf['layer'])
					break; # no more than the current one
				$n_q_args = $items[$header_inf['tag']]['q-args'] ?? null;
				if ($n_q_args !== null)
					$q_args = $n_q_args; # do not break ... let the last one overwrite
			}
		}
		
		# a bit dirty ...
		if ($q_args === null)
			$q_args = "\$settings = null, \$data = null, \$bind_params = null, ".
											"\$grid_mode = null, \$id = null, \$vars_path = '', \$_qengine_args = null";
		
		list(, $params_names_only_str) = $q_args ? $this->compile_template_prepare_args($q_args, $method_name) : [null, ""];
		
		$method_body = 
"	/**
	 * @api.enable
	 */
	function {$method_name}({$q_args})
	{
		{$params_names_only_str}\$this->includeJsClass();
		include(__DIR__.'/{$include_name}');
	}
";
						
		# just for testing now:
		if ($added_or_changed)
			$this->compile_template_xml($header_inf);
		
		// we will only setup the function for the render , the actual rendering will be done on the fly later !
		return ['name' => $method_name, 'body' => $method_body];
	}
	
	function compile_template_get_method_name_and_tag(string $header_inf_tag)
	{
		$p = strpos($header_inf_tag, '@');
		$method_tag = substr($header_inf_tag, ($p === false) ? 0 : ($p + 1));
		$method_tag_parts = explode('.', $method_tag);
		$method_tag_str = "";
		foreach ($method_tag_parts as $sp)
			$method_tag_str .= ucfirst ($sp);
		
		$method_name = 'render'.$method_tag_str;
		return [$method_name, $method_tag];
	}
	
	function compile_template_prepare_args(string $q_args, string $method_name, bool $with_args_list = false)
	{
		if (!$q_args)
			return null;
		
		$params_names_only = $q_args ? q_token_get_all("<?php function __noname_ignore({$q_args}) {}\n") : [];
		$params_names_only_items = [];
		foreach ($params_names_only as $token)
		{
			if (is_array($token) && ($token[0] === T_VARIABLE))
				$params_names_only_items[] = $token[1];
		}
		
		return [$params_names_only_items, 
		"if ((!func_num_args()) && \$this && \$this->_rf_args && (\$_rf_args = \$this->_rf_args[\"{$method_name}\"]))
			list (".implode(", ", $params_names_only_items).") = \$_rf_args;
".($with_args_list ? "		else if (func_num_args())\n\t\t\tlist(".implode(", ", $params_names_only_items).") = func_get_args();\n" : "")];
	}
	
	function compile_template_xml(array $header_inf)
	{
		list ($xml_tokens, $gen_info) = $this->compile_xml_file($header_inf);
		
		$p = strpos($header_inf['tag'], '@');
		$method_tag = substr($header_inf['tag'], ($p === false) ? 0 : ($p + 1));
		$include_name = $header_inf["final_class"].".ztpl".($method_tag ? '.' : '').$method_tag.".gen.php";
		
		# echo 'FILE :: ', $full_class_name, " | " , $header_inf['tag'], ' | ', ' | ', $include_name, "\n<br/>";
		
		$full_class_name = \QPHPToken::ApplyNamespaceToName($header_inf["final_class"], $header_inf["namespace"]);
		if (!$full_class_name)
		{
			qvar_dumpk("AAZ", $header_inf);
			throw new \Exception('Unable to determine full, final class name');
		}
		$gens_dir = $this->info_by_class[$full_class_name]['gens_dir'];
		if (!$gens_dir)
		{
			qvar_dumpk("AA_QQ", $header_inf);
			throw new \Exception('Unable to find gens dir for');
		}
		
		if ($xml_tokens->jsFunc)
			$this->compile_template_xml_js_func($xml_tokens, $gens_dir, $full_class_name, $header_inf);
				
		/**$prepend_str = "";
		if ((!$header_inf['q-args']) && ($first_xml = $xml_tokens->findFirstXMLElement()) && ($first_xml->attrs['q-args']))
		{
			$q_args = $first_xml->getAttribute('q-args', true);
			# not the best way to do it, we may have to consider later
			list($method_name, $method_tag) = $this->compile_template_get_method_name_and_tag($header_inf['tag']);
			list(, $params_names_only_str) = $this->compile_template_prepare_args($q_args, $method_name, true);
			// $include_name = $header_inf["final_class"].".ztpl".($method_tag ? '.' : '').$method_tag.".gen.php";
			$prepend_str = "<?php\n".$params_names_only_str."\n?>\n";
			
			// does not work inside a template !!!!
		}*/
		
		# we need to secure after JS creation
		
		# we need to do this on-demand only !
		# list ($xml_tokens) = $this->secure_template($xml_tokens, $header_inf);
		
		$xml_tokens_str = $xml_tokens->toString(false, true);
		# here we apply the namespace
		if (isset($header_inf['namespace']))
			$xml_tokens_str = "<?php\nnamespace {$header_inf['namespace']};\n?>" . $xml_tokens_str;
			
		# echo "<pre>";
		# echo htmlentities($xml_tokens_str);
		# die;
		
		$this->file_put_contents($gens_dir.$include_name, $xml_tokens_str);
		opcache_invalidate($gens_dir.$include_name);
	}
	
	function compile_template_xml_js_func(\QPHPToken $render_code, string $gens_dir, string $full_class_name, array $header_inf)
	{
		# generate one method per file and concat them on any change ... or something similar !!!!
		# NO MORE PARSING THE FILE PLEASE !!!!
		$js_gens_path = $gens_dir.$header_inf['final_class'].".gen.js";
		
		$namespace = $header_inf['namespace'];
		
		$parent_class_inf = $this->get_js_parent_class($full_class_name);
		$parent_class_for_js = $parent_class_inf ? \QPHPToken::ApplyNamespaceToName($parent_class_inf["final_class"], $parent_class_inf['namespace']) : 
									'QWebControl';
		
		$insert_code = 'a25cd303d592c9c7d9039a0a80943ed6cf9be9ac';
		$insert_key = '// insert_before_'.$insert_code;
		
		$out_parent_class_for_js = ($parent_class_for_js === 'QWebControl') ? 'omi' : $parent_class_for_js;
		
		$force_reset_js = false;
		if ($this->full_sync)
		{
			# make sure we only reset once
			if (!isset($this->js_gens_reset_map[$js_gens_path]))
			{
				$this->js_gens_reset_map[$js_gens_path] = true;
				$force_reset_js = true;
			}
		}
		
		$contents = ($force_reset_js || (!file_exists($js_gens_path))) ?
						"QExtendClass(\"".qaddslashes($full_class_name)."\", \"".qaddslashes($out_parent_class_for_js).
							"\", {\n".
							"__dummy_for_comma_syntax: null\n".
							$insert_key."\n".
							"\n});"
						: file_get_contents($js_gens_path);
		
		foreach ($render_code->jsFunc as $func_tag => $func_code)
		{
			list($func_class, $func_name) = explode("#", $func_tag, 2);
			
			$new_first_elem = "{$func_name}: function";
			$func_code[0] = $new_first_elem;

			$func_str = QPHPToken::To_String($func_code);
			$func_str = rtrim($func_str, "\n\t\r ;");
			
			$start_tag = "// JS_FUNC: {$func_name}@{$insert_code}";
			$end_tag = "// END_JS_FUNC: {$func_name}@{$insert_code}";
			$start_tag_pos = strpos($contents, $start_tag);
			$end_tag_pos = ($start_tag_pos !== false) ? strpos($contents, $end_tag, $start_tag_pos) : false;
			if (($start_tag_pos !== false) && (($end_tag_pos === false) || ($end_tag_pos <= $start_tag_pos)))
				throw new \Exception('JS Parse error on: '.$js_gens_path.". Fix or reset the file.");
			
			if ($start_tag_pos)
			{
				// replace
				$contents = rtrim(substr($contents, 0, $start_tag_pos))."\n\n{$start_tag}\n, ".
								trim($func_str)."\n{$end_tag}\n\n".ltrim(substr($contents, $end_tag_pos + strlen($end_tag)));
			}
			else
			{
				// prepend
				$ins_pos = strpos($contents, $insert_key);
				if ($ins_pos === false)
					throw new \Exception('JS Parse error on: '.$js_gens_path.". Fix or reset the file.");
				$contents = rtrim(substr($contents, 0, $ins_pos))."\n\n{$start_tag}\n, ".
								trim($func_str)."\n{$end_tag}\n\n".ltrim(substr($contents, $ins_pos));
			}
		}
		
		$gens_layer = $this->info_by_class[$full_class_name]['gens_layer'];
		$relative_file_path = substr($js_gens_path, strlen($this->tags_to_watch_folders[$gens_layer]));
	
		$this->file_put_contents($js_gens_path, $contents);
		
		$this->info_by_class[$full_class_name]['res']['js']['gen'] = [
				'class' => $header_inf['final_class'],
				'final_class' => $header_inf['final_class'],
				'type' => "resource",
				'res_type' => "js",
				'file' => $relative_file_path, /// !!!!!!!!!!!!!!!!!!!!!!!!!
				'file_time' => filemtime($js_gens_path),
				'layer' => $gens_layer,
				'tag' => "res@js.gen",
				'namespace' => $header_inf['namespace'],
				'class_full' => $full_class_name,
				'extends_full' => $header_inf['extends_full'],
		];
	}
	
	function compile_xml_file(array $header_inf, array $merge_dependencies_stack = [])
	{
		$full_file_path = $this->tags_to_watch_folders[$header_inf['layer']].$header_inf['file'];
		
		# if (substr($full_file_path, 0, strlen('/home/alex/public_html/vf-merge/vf-base-new/~backend/')) !== '/home/alex/public_html/vf-merge/vf-base-new/~backend/')
		echo "compile_xml_file :: {$full_file_path}<br/>\n";
	
		foreach ($merge_dependencies_stack as $dep_header_info)
		{
			if (empty($header_inf) || empty($header_inf['class_full']) || empty($header_inf['layer']) || empty($header_inf['tag']))
			{
				qvar_dumpk("zzzzzz", get_defined_vars());
				throw new \Exception('not ok!');
			}
			
			$this->dependencies[$header_inf['class_full']][$header_inf['layer']][$header_inf['tag']]
					[$dep_header_info['class_full']][$dep_header_info['layer']][$dep_header_info['tag']] = $dep_header_info['tag'];
		}
		
		$gen_info = new QGeneratePatchInfo($full_file_path);
		if ($header_inf['namespace'])
			$gen_info->namespace = $header_inf['namespace'];
		
		$obj_parsed = \QPHPToken::ParsePHPFile($full_file_path, true);
		# if ($obj_parsed === null)
		{
			# qvar_dumpk($full_file_path, file_exists($full_file_path), $header_inf);
		}
		
		# recurse here if needed
		{
			$do_merge = ($header_inf['type'] === 'url');
			if ((!$do_merge) && ($first_DOM_Element = $obj_parsed->findFirst(".QPHPTokenXmlElement")))
			{
				$q_merge_val = $first_DOM_Element->getAttribute("qMerge");
				if ($q_merge_val)
					$q_merge_val = strtolower(trim($q_merge_val));
				if ($q_merge_val === 'false')
					$q_merge_val = false;
				$do_merge = $q_merge_val ? true : false;
			}
			if ($do_merge)
			{
				// find what we need to merge
				$merge_from = $this->find_file_to_patch($header_inf);
				if ($merge_from)
				{
					$new_deps_stack = $merge_dependencies_stack;
					$new_deps_stack[] = $header_inf;
					list ($merge_from_xml, $merge_from_gen_info) = $this->compile_xml_file($merge_from, $new_deps_stack);
					
					// function compile_url_controller(string $full_class_name, array $header_inf, array $full_class_info)

					if ($merge_from_xml && $merge_from_gen_info)
					{
						$obj_parsed->inheritFrom($merge_from_xml, $merge_from_xml, $obj_parsed, $merge_from_gen_info);
						$obj_parsed->fixBrokenParents();
					}
				}
			}
		}
		
		// echo "<hr/>";
		// echo "STARTING :".$header_inf['layer']." # ".$header_inf['file']." @@@ ".$header_inf['tag']."<br/>\n";
		// skip if merge/patching is not required
		$dependencies_stack = [];
		
		{
			$gen_info->__tpl_parent_cb = function (\QGeneratePatchInfo $gen_info, \QPHPTokenXmlElement $xml_node) use ($header_inf, &$dependencies_stack)
				{
					$prev_header_info = $gen_info->__tpl_header_info ?: $header_inf;
					$patch_header_info = $this->find_file_to_patch($prev_header_info);
					
					$missing_dependency = false;
					
					$dependencies_stack[] = $prev_header_info;
					foreach ($dependencies_stack as $dep_header_info)
					{
						if (empty($patch_header_info) || empty($patch_header_info['class_full']) || empty($patch_header_info['layer']) || empty($patch_header_info['tag']))
						{
							if (defined('Q_RUN_CODE_NEW_IGNORE_MISSING_PATCHES') && Q_RUN_CODE_NEW_IGNORE_MISSING_PATCHES)
							{
								qvar_dumpk("WARNING :: MISSING CLASS TO PATCH `\$header_inf`=".var_export($header_inf, true).";");
								$missing_dependency = true;
								break;
							}
							else
							{
								qvar_dumpk("WARNING :: MISSING CLASS TO PATCH `\$header_inf`=".var_export($header_inf, true).";");
								qvar_dumpk("mmmmmm", $header_inf, $patch_header_info, $dependencies_stack);
								throw new \Exception('not ok!');
							}
						}
						
						$this->dependencies[$patch_header_info['class_full']][$patch_header_info['layer']][$patch_header_info['tag']]
								[$dep_header_info['class_full']][$dep_header_info['layer']][$dep_header_info['tag']] = $dep_header_info['tag'];
					}
										
					if (!$patch_header_info)
					{
						if (defined('Q_RUN_CODE_NEW_IGNORE_MISSING_PATCHES') && Q_RUN_CODE_NEW_IGNORE_MISSING_PATCHES)
						{
							
						}
						else
						{
							// it's asking for a patch but we can not find one
							qvar_dumpk(['Unable to find a parent for patching', 
									'$patch_header_info' => $patch_header_info, 
									'xml-requesting' => $xml_node->getRoot()]);
						}
						
						return null;
						// @TODO - we should throw an error, or at least do not render it !!!
						// throw new \Exception('Unable to find a parent for patching');
					}
					
					echo "------ USING TPL TO PATCH :".$patch_header_info['layer']." # ".$patch_header_info['file']." @@@ ".$patch_header_info['tag']."<br/>\n";
		
					$patch_obj = new \stdClass();
					$patch_obj->tpl_path = $this->tags_to_watch_folders[$patch_header_info['layer']].$patch_header_info['file'];
					$patch_obj->xml_tokens = \QPHPToken::ParsePHPFile($patch_obj->tpl_path, true);
					$patch_obj->__tpl_header_info = $patch_header_info;
					// we need to serve via another compile_xml_file !
					return $patch_obj;
				};
			// $gen_info->__tpl_mode = $header_inf['type']; # php, tpl, url,
			// $gen_info->__tpl_tag = $header_inf['tag'] && (($p = strpos($header_inf['tag'], '@')) !== false) ? substr($header_inf['tag'], $p + 1) : "";
		}

		$obj_parsed->generate($gen_info);
		
		return [$obj_parsed, $gen_info];
	}
	
	function find_file_to_patch(array $header_inf, bool $debug = false)
	{
		// ok, prio -> from this layer ... go back up to the first and find a file with the same full class & tag
		$for_class = $header_inf['class_full'];
		$found = null;
		$tag_to_find = $header_inf['tag'];
		$before_layer = $header_inf['layer'];
		$include_layer = false;
		$accepted_layers = null;
		
		do
		{
			$data = $this->info_by_class[$for_class];
			if (!isset($data['files'])) {
				qvar_dump('$header_inf', $header_inf, '$this->class_to_info[$for_class]', $this->class_to_info[$for_class], $this->class_to_info);
				throw new \Exception('Missing any data for class: '.$for_class);
			}
			$c_df = count($data['files']);
			if ($c_df < 1)
				throw new \Exception('Missing layers for class: '.$for_class);
			else if ($c_df > ($include_layer ? 0 : 1))
			{
				$after_this_layer = false;
				foreach (array_reverse($data['files']) as $layer_tag => $layer_files_list)
				{
					if ((($accepted_layers !== null) ? $accepted_layers[$layer_tag] : $after_this_layer) &&
							($found = $layer_files_list[$tag_to_find]))
					{
						return $found;
					}
					else if ((!$after_this_layer) && ($layer_tag === $before_layer))
						$after_this_layer = true;
				}
			}
			
			# continue with the extended class
			$for_class = $data['extends'];
			if ($for_class && ($tmp_inf = $this->class_to_info[$for_class] ?? null) && isset($tmp_inf['class_full'])) {
				$for_class = $tmp_inf['class_full'];
			}
			$include_layer = true;
			if ($accepted_layers === null)
				# after the first entry we need to know the layers
				$accepted_layers = $this->get_layers_before($before_layer, true);
		}
		while ((!$found) && $for_class);
				
		return null;
	}
	
	function compile_setup_trait(string $short_name, string $namespace = null)
	{
		$class_str = "<?php\n\n";
		if ($namespace)
			$class_str .= "namespace {$namespace};\n\n";
		$class_str .= "trait {$short_name}";
		$class_str .= "\n{\n\n";
		
		return [$class_str, "\n}\n\n"];
	}
	
	function compile_setup_class(string $full_class_name, string $short_name, string $extend_class, 
						string $namespace = null, string $doc_comment = null, 
						array $extends_header_inf = null)
	{
		$class_str = "<?php\n\n";
		if ($namespace)
			$class_str .= "namespace {$namespace};\n\n";
		if ($doc_comment)
			$class_str .= trim($doc_comment)."\n";
		
		$short_extends = null;
		if ($extend_class)
		{
			$short_extends = ($namespace && substr($extend_class, 0, strlen($namespace) + 1) === $namespace."\\") ? substr($extend_class, strlen($namespace) + 1) : 
				($namespace ? "\\".$extend_class : $extend_class);
		}
		if ((!$extend_class) || ($short_name === $short_extends))
		{
			$extend_class = $this->info_by_class[$full_class_name]['extends'];
			$short_extends = ($namespace && substr($extend_class, 0, strlen($namespace) + 1) === $namespace."\\") ? substr($extend_class, strlen($namespace) + 1) : 
				($namespace ? "\\".$extend_class : $extend_class);
		}
		$is_abstract = $extends_header_inf['@class.abstract'] ? true : false;
		$is_final = $extends_header_inf['@class.final'] ? true : false;
		
		$has_url = $this->info_by_class[$full_class_name]['has_url'];
		
		if (($short_name === 'Controller') && ($extend_class === 'QWebControl'))
		{
			# qvar_dumpk($full_class_name, $extend_class, $short_extends, $this->info_by_class[$full_class_name], debug_backtrace());
			# throw new \Exception('fail');
		}
		
		$class_str .= ($is_abstract ? 'abstract ' : '').($is_final ? 'final ' : '').
					"class {$short_name}".(($short_extends && ($short_name !== $short_extends)) ? " extends {$short_extends}" : '').
												($has_url ? " implements \\QIUrlController" : "");
		$class_str .= "\n{\n\n";
				
		return [$class_str, "\n}\n\n"];
	}
	
	function get_class_deps_extends(string $extends, string $type_tag, array $layers_gens_map)
	{
		$ret = null;
		while ($extends && (! ($ret = $layers_gens_map[$extends][$type_tag])))
		{
			$extends = $this->extends_map[$extends];
		}
		
		return $ret;
	}
	
	function cache_data(bool $sync_sql_meta = false)
	{
		\QAutoload::SetAutoloadArray($this->autoload_for_sync);
		\QAutoload::UnlockAutoload();
		
		$temp_folder = QAutoload::GetRuntimeFolder()."temp/";
		$cache_folder = QAutoload::GetRuntimeFolder()."temp/types/";
		
		# $this->dependencies
		$this->file_put_contents($temp_folder."dependencies.php", "<?php\n\n\$_DATA = ".var_export($this->dependencies, true).";\n");
		opcache_invalidate($temp_folder."dependencies.php", true); # we do not force as it may not have changed
		# $this->info_by_class
		$this->file_put_contents($this->temp_code_dir."sync_info_by_class.php", "<?php\n\n\$_DATA = ".var_export($this->info_by_class, true).";\n");
		opcache_invalidate($this->temp_code_dir."sync_info_by_class.php", true);
		
		$this->file_put_contents($temp_folder."autoload.php", "<?php\n\n\$_Q_FRAME_LOAD_ARRAY = ".var_export($this->autoload, true).";\n");
		opcache_invalidate($temp_folder."autoload.php", true);
		// $this->file_put_contents($temp_folder."autoload.php", "<?php\n\n\$_Q_FRAME_LOAD_ARRAY = ".var_export($this->autoload, true).";\n");
		
		// setup extended by list
		$tree = new \stdClass();
		$tree->classes = [];
		$extended_by = [];
		$obj_ext_by = [];
		
		$model_extends_map = [];
		
		foreach ($this->info_by_class as $m_type => $info)
		{
			# if ((!$info['has_tpl']) && (!$info['has_url']) && $info['is_patch'] && $info['has_php'])
			{
				# echo "extends check on: {$m_type}<br/>\n";
				$this->cache_extended_by($tree, $m_type, $obj_ext_by);
				$extnds = $this->info_by_class[$m_type]['extends'];
				if ($extnds && (!interface_exists($m_type)))
					$model_extends_map[$m_type] = $extnds;
			}
		}
		
		foreach ($obj_ext_by as $m_type => $m_obj)
		{
			$ext_by = $this->cache_extended_by_extract($m_obj);
			if ($ext_by)
				$extended_by[$m_type] = $ext_by;
		}
		
		if ($this->do_not_allow_empty_extended_by && (!$this->full_sync) && empty($extended_by))
		{
			$this_items = [];
			foreach ($this as $k => $v)
				$this_items[$k] = $v;
			ksort($this_items);
			qvar_dumpk($this_items, get_defined_vars());
			throw new \Exception('do_not_allow_empty_extended_by');
		}
		
		// must also include interfaces ... bum
		$this->file_put_contents($temp_folder."extended_by.php", "<?php\n\n\$_Q_FRAME_EXTENDED_BY = ".var_export($extended_by, true).";\n");
		opcache_invalidate($temp_folder."extended_by.php", true); # we do not force, maybe not changed
		
		$has_cache_changes = false;
		
		foreach ($this->cache_types as $class_name => $path)
		{
			$cache_path = $cache_folder.qClassToPath($class_name).".type.php";
			
			list($cache_type, $cache_has_changes) = QCodeStorage::CacheData($class_name, $cache_path, true);
			
			if ($cache_has_changes)
			{
				if (!$cache_type)
					throw new \Exception('Unable to setup type info for: '.$class_name.'. Make sure is a QIModel.');
				$has_cache_changes = true;
			}
			if ($class_name === Q_DATA_CLASS)
				$this->saved_data_class_info = $cache_type;
			unset($cache_type, $cache_has_changes);
		}
		
		// model_type.js : rethink it
		$this->file_put_contents($temp_folder."model_type.js", "window.\$_Q_FRAME_JS_CLASS_PARENTS = ".json_encode($model_extends_map, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).";\n");
		
		$autoload_js = [];
		$autoload_css = [];
		
		$js_paths = [];
		$css_paths = [];

		if (!$this->model_only_run)
		{
			foreach ($this->info_by_class as $full_class_name => $info)
			{
				// $this->info_by_class[$full_class_name]['res'][$header_inf['res_type']][] = $header_inf;
				foreach ($info['res'] ?: [] as $res_type => $info_res)
				{
					foreach ($info_res ?: [] as $info_res_item)
					{
						$server_path = $this->tags_to_watch_folders[$info_res_item['layer']].$info_res_item['file'];
						try
						{
							$web_path = \QApp::GetWebPath($server_path);
						}
						catch (\Exception $ex)
						{
							# qvar_dumpk('$this->info_by_class', $this->info_by_class,);
							throw $ex;
						}
						if ($res_type === 'js')
						{
							$js_paths[$full_class_name][$web_path] = $web_path;
							$autoload_js[$full_class_name][$server_path] = $server_path;
						}
						else if ($res_type === 'css')
						{
							$css_paths[$full_class_name][$web_path] = $web_path;
							$autoload_css[$full_class_name][$server_path] = $server_path;
						}
					}
				}
			}

			$this->file_put_contents($temp_folder."js_paths.js", 
					"window.\$_Q_FRAME_JS_PATHS = ".json_encode($js_paths, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).";\n".
					"window.\$_Q_FRAME_CSS_PATHS = ".json_encode($css_paths, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).";\n");

			$this->file_put_contents($temp_folder."autoload_js.php", "<?php\n\n\$_Q_FRAME_JS_LOAD_ARRAY = ".var_export($autoload_js, true).";\n");
			opcache_invalidate($temp_folder."autoload_js.php", true); # do not force, maybe no change
			$this->file_put_contents($temp_folder."autoload_css.php", "<?php\n\n\$_Q_FRAME_CSS_LOAD_ARRAY = ".var_export($autoload_css, true).";\n");
			opcache_invalidate($temp_folder."autoload_css.php", true); # do not force, maybe no change
		}
		
		if ($this->model_only_run && $has_cache_changes)
		{
			$this->has_model_changes = $has_cache_changes;
			# @TODO - maybe just flag that there are changes ... and let the developer push the structure changes !!!
			
			/*
			# @TODO - this needs to be done the right way !
			$conn = new \QMySqlStorage("sql", "127.0.0.1", MyProject_MysqlUser, MyProject_MysqlPass, MyProject_MysqlDb, 3306);
			$conn->connect();
			
			// enable this to resync your DB structure
			$sql_statements = \QSqlModelInfoType::ResyncDataStructure($conn);
			*/
		}
		
		if ($sync_sql_meta && ($this->full_sync || $this->has_model_changes))
		{
			$mysql = new \QMySqlStorage("sql", defined('MyProject_MysqlHost') ? MyProject_MysqlHost : "127.0.0.1", 
							MyProject_MysqlUser, MyProject_MysqlPass, MyProject_MysqlDb, 
							ini_get("mysqli.default_port"),
							defined('MyProject_Mysql_Socket') ? MyProject_Mysql_Socket : ini_get("mysqli.default_socket"));

			$mysql->offline_mode = true;
			# $mysql->connect();
			# $mysql->connection->query('SET NAMES utf8');
			
			// enable this to resync your DB structure
			\QSqlModelInfoType::ResyncDataStructure($mysql, false, true);
		}
		
		# $cache_folder = QAutoload::GetRuntimeFolder()."temp/types/";
		# $cache_path = $cache_folder.qClassToPath($elem->className).".type.php";
		# list($processed_ty, $ty_changes) = QCodeStorage::CacheData($elem->className, $cache_path);
	}
	
	function cache_extended_by($tree, string $class, array &$objs, bool $is_interface = false)
	{
		if (($e = $objs[$class]))
			return $e;
		
		$objs[$class] = $obj = new \stdClass();
		
		$extends = $is_interface ? null : $this->info_by_class[$class]['extends'];
		if ($extends)
		{
			if ($this->autoload[$extends])
			{
				$parent_obj = $this->cache_extended_by($tree, $extends, $objs);
				$parent_obj->classes[$class] = $obj;
			}
		}
		else
			$tree->classes[$class] = $obj;
		
		$implements = class_implements($class);
		if ($implements)
		{
			foreach ($implements as $interface)
			{
				if ($this->autoload[$interface])
				{
					$parent_iface_obj = $this->cache_extended_by($tree, $interface, $objs, true);
					$parent_iface_obj->classes[$class] = $obj;
				}
			}
		}
		return $obj;
	}
	
	function cache_extended_by_extract($object, array &$ret = null)
	{
		if ($ret === null)
			$ret = [];
		
		foreach ($object->classes ?: [] as $class_name => $class_obj)
		{
			$ret[$class_name] = $class_name;
			if ($class_obj->classes)
				$this->cache_extended_by_extract($class_obj, $ret);
		}
		
		return $ret;
	}
	
	function get_layers_before(string $layer, bool $including = false)
	{
		$res = [];
		
		foreach ($this->tags_to_watch_folders as $tag => $path)
		{
			if ($tag === $layer)
				break;
			$res[$tag] = $tag;
		}
		if ($including)
			$res[$layer] = $layer;
		return $res;
	}
	
	function get_js_parent_class(string $full_class_name)
	{
		// last option is QObject
		$class = $full_class_name;
		$extends = $this->info_by_class[$class]['extends'];
		while ($extends)
		{
			$inf = $this->info_by_class[$extends];
			// does it have JS
			foreach ($inf['res']['js'] ?: [] as $f_header_inf)
			{
				if (($f_header_inf['final_class']) && 
						($extends === (\QPHPToken::ApplyNamespaceToName($f_header_inf['final_class'], $f_header_inf['namespace']))))
					return $f_header_inf;
			}
			$extends = $inf['extends'];
		}
		
		return null;
	}
	
	function group_files_by_folder(array $files)
	{
		$ret = [];
		foreach ($files as $layer => $layer_files)
		{
			foreach ($layer_files as $file => $mtime)
			{
				// $key = $header_inf['is_php'] ? '01-' : ($header_inf['is_url'] ? '02-' : '03-');
				$f_name = basename($file);
				$ext = substr($f_name, strrpos($f_name, '.'));
				$ext_2 = ($p = strrpos($f_name, '.', -strlen($ext)-1)) ? substr($f_name, $p, -strlen($ext)) : null;
				$short_name = substr($f_name, 0, strpos($f_name, '.'));
				
				$key = ($ext === '.tpl') ? '03-' : (($ext_2 === '.url') ? '02-' : (($ext === '.php') ? '01-' : '99-'));

				$dir_name = dirname($file);
				$ret[$layer][($dir_name && ($dir_name !== '.')) ? $dir_name."/" : ''][$short_name][$key.$f_name] = $mtime;
			}
			foreach ($ret[$layer] as &$dir_files)
				foreach ($dir_files as &$dir_classes)
					ksort($dir_classes);
		}
		
		return $ret;
	}
	
	function empty_gens_dir(string $dir_path, bool $debug = false)
	{
		$files_in_gens = scandir($dir_path);
		$gens_dir_is_empty = true;
		$has_removed_files = false;
		
		foreach ($files_in_gens as $fg)
		{
			if (($fg === '.') || ($fg === '..'))
				continue;
			else if ((substr($fg, -8, 8) === ".gen.php") && is_file($dir_path.$fg))
			{
				$this->unlink($dir_path.$fg);
				$has_removed_files = true;
			}
			else
				$gens_dir_is_empty = false;
		}

		if ($gens_dir_is_empty)
			rmdir($dir_path);
		
		return $has_removed_files;
	}
	
	function get_patching_classes(string $full_class_name)
	{
		$patching_parents = [];
		foreach ($this->info_by_class[$full_class_name]['files'] ?: [] as $fci_layer)
		{
			if (($p_header_inf = $fci_layer['php']))
				$patching_parents[\QPHPToken::ApplyNamespaceToName($p_header_inf['class'], $p_header_inf['namespace'])] = true;
		}
		return $patching_parents;
	}
	
	function sync_code__setup_default_metas()
	{
		// so ... what's the plan little man ?!?!
		// the aim here is to setup ok:
					# $this->changes_by_class ... AND
					# $this->info_by_class
		
		foreach ($this->info_by_class as $full_class_name => &$info)
		{
			$backup_res_js_gen = null;
			
			if (!$this->full_sync)
			{
				# copy from the previous state ($this->info_by_class => $this->changes_by_class)
				$changes_by_class_files = $this->changes_by_class[$full_class_name]['files'];
				if (!$changes_by_class_files)
					# there are no changes
					continue;
				
				# @TODO - this is a work-around ... we should have a proper way of traking this !!!
				$backup_res_js_gen = $info['res']['js']['gen'] ?? null;
				
				$prev_save_data = [];
				$prev_save_data_files = [];
				foreach ($info as $k => $v)
				{
					if ($k !== 'files')
					{
						$prev_save_data[$k] = $v;
						$info[$k] = null;
					}
				}
				
				foreach ($changes_by_class_files as $layer_tag => $layer_info)
				{
					$sort_files = false;
					foreach ($layer_info as $file_tag => $header_inf)
					{
						# save the previous state
						$prev_save_data_files[$layer_tag][$file_tag] = $info['files'][$layer_tag][$file_tag];
						$info['files'][$layer_tag][$file_tag] = $header_inf;
						$sort_files = true;
					}
					if ($sort_files)
					{
						uksort($info['files'][$layer_tag], [$this, 'sort_file_by_tags']);
						uksort($prev_save_data_files[$layer_tag], [$this, 'sort_file_by_tags']);
					}
				}
				if ($prev_save_data_files)
					$prev_save_data['files'] = $prev_save_data_files;
				$this->prev_by_class[$full_class_name] = $prev_save_data;
			}
			
			$has_php = false;
			$has_tpl = false;
			$has_url = false;
			$is_patch = false;

			$extends = null;
			$file_namespace = null;
			$first_file_in_last_layer = null;
			$last_layer = null;
			$files_count_not_resource = 0;
			
			$patch_extends = null;
			$patch_extends_info = null;
			$classes_files = [];
			
			foreach ($info['files'] as $layer_tag => $layer_info)
			{
				if ($layer_info)
					$first_file_in_last_layer = null;
				
				foreach ($layer_info as $file_tag => $header_inf)
				{
					$first_file_in_last_layer = $header_inf['file'];
					
					# skip removed !
					if ($header_inf['status'] === static::Status_Removed)
						continue;
					
					if ($header_inf['type'] === 'resource')
					{
						$info['res'][$header_inf['res_type']][$layer_tag."#".$file_tag] = $header_inf;
					}
					else
					{
						$files_count_not_resource++;
					
						if ($header_inf['extends'] && ((!$extends) || ($extends === $header_inf['extends'])))
							$extends = $header_inf['extends'];
						if ($header_inf['is_tpl'])
						{
							$has_tpl = true;
							if (!$patch_extends_info)
								$patch_extends_info = $header_inf;
						}
						else if ($header_inf['is_php'])
						{
							$has_php = true;
							if (!$header_inf['is_patch'])
								$classes_files[] = $this->tags_to_watch_folders[$layer_tag].$header_inf["file"];
							else
							{
								$patch_class_name = \QPHPToken::ApplyNamespaceToName($header_inf["class"], $header_inf["namespace"]);
								$info['patch_autoload'][$patch_class_name] = $this->tags_to_watch_folders[$layer_tag].$header_inf["file"];
								$patch_extends_info = $header_inf;
							}
						}
						else if ($header_inf['is_url'])
						{
							$has_url = true;
							if (!$patch_extends_info)
								$patch_extends_info = $header_inf;
						}
						if ($header_inf['is_patch'])
						{
							$is_patch = true;
						}
						if ((!$file_namespace) && $header_inf['namespace'])
							$file_namespace = $header_inf['namespace'];
					}
				}
				
				$last_layer_tag = $layer_tag;
				$last_layer = $this->tags_to_watch_folders[$layer_tag];
			}
			
			$info['count_not_res'] = $files_count_not_resource;
			$info['classes_files'] = $classes_files;
			
			$ff_ll_dir = dirname($first_file_in_last_layer);
			$gens_path = ($ff_ll_dir && ($ff_ll_dir !== '.') && ($ff_ll_dir !== './')) ? $ff_ll_dir.'/~gens/' : '~gens/';
			$info['gens_dir'] = $last_layer.$gens_path;
			$info['gens_layer'] = $last_layer_tag;
			
			$info['generated_extends_info'] = $patch_extends_info;
			$info['generated_extends'] = \QPHPToken::ApplyNamespaceToName($patch_extends_info['class'], $patch_extends_info['namespace']);
			
			if ($has_tpl)
				$info['has_tpl'] = true;
			if ($has_url)
				$info['has_url'] = true;
			if ($has_php)
				$info['has_php'] = true;

			if ($is_patch)
				$info['is_patch'] = true;

			if ((!$extends) && $has_tpl && ((!$is_patch) || $has_php) && (!$info['extends']))
			{
				# if no extends is present up to this layer for a TPL, we make sure it will at least extend `QWebControl`
				$extends_full = $extends = 'QWebControl';
			}
			else if ($extends)
				$extends_full = \QPHPToken::ApplyNamespaceToName($extends, $file_namespace);

			if ($extends)
			{
				if (!$info['extends'])
					$info['extends'] = $extends_full;
			}
			
			if ($backup_res_js_gen !== null)
				$info['res']['js']['gen'] = $backup_res_js_gen;
		}
	}
	
	function sync_code__populate_dependencies()
	{
		$temp_folder = QAutoload::GetRuntimeFolder()."temp/";
		// $this->file_put_contents($temp_folder."dependencies.php", "<?php\n\n\$_DATA = ".var_export($this->dependencies, true).";\n");
		if (file_exists($temp_folder."dependencies.php"))
		{
			$_DATA = null;
			# do not wrap it in a function !!! it will slow down a lot, it copies the data!
			require($temp_folder."dependencies.php");
			$this->dependencies = $_DATA;
			if (!is_array($this->dependencies))
				throw new \Exception('Missing dependencies. You should force a full resync.');
		}
		else
			throw new \Exception('Missing dependencies. You should force a full resync.');
		
		$triggered_changes = [];
		
		foreach ($this->changes_by_class as $full_class_name => $changes_info)
		{
			$deps_info = $this->dependencies[$full_class_name];

			if (!$deps_info)
				continue;
			
			foreach ($changes_info['files'] as $layer_tag => $changes_list)
			{
				$layers_list = $deps_info[$layer_tag];
				if (!$layers_list)
					continue;
				
				foreach ($changes_list as $tag_name => $header_inf)
				{
					$was_moved = ($header_inf['status'] === static::Status_Moved);
					if ($was_moved && (($header_inf['type'] === 'php') || ($header_inf['type'] === 'tpl') || ($header_inf['type'] === 'url')))
						# at the moment a moved file of the specified type should have no effect
						continue;

					$target_deps_list = $layers_list[$tag_name];
					if (!$target_deps_list)
						continue;
					
					# qvar_dumpk('fcn::$target_deps_list', $full_class_name, $target_deps_list, $deps_info, $changes_info);
					# die;

					# now ... for all the dependencies
					foreach ($target_deps_list as $target_class_name => $target_info)
					{
						$change_info = &$triggered_changes[$target_class_name];
						foreach ($target_info as $target_layer_tag => $target_tags)
						{
							$change_info_layer = &$change_info['files'][$target_layer_tag];
							foreach ($target_tags as $target_tag)
							{
								# if (!isset($this->changes_by_class[$target_class_name]['files'][$target_layer_tag][$target_tag]))
								{
									if ($was_moved)
										throw new \Exception('@TODO - implement and test a moved dependency - possible a resource css/js');
									$change_info_layer[$target_tag] = $this->changes_by_class[$target_class_name]['files'][$target_layer_tag][$target_tag] ?: $this->info_by_class[$target_class_name]['files'][$target_layer_tag][$target_tag];
									if (empty($change_info_layer[$target_tag]))
										throw new \Exception('Missing info for: '.$target_class_name."/".$target_layer_tag."/".$target_tag);
									$change_info_layer[$target_tag]['status-deps'] = static::Status_Changed_Dependencies;
								}
							}

							if (empty($change_info_layer))
							{
								unset($change_info['files'][$target_layer_tag]);
								if (empty($change_info['files']))
									unset($change_info['files']);
							}
							unset($change_info_layer);
						}

						if (empty($change_info))
							unset($triggered_changes[$target_class_name]);
						unset($change_info);
					}
				}
			}
		}
		
		foreach ($triggered_changes as $class => $info)
		{
			$cbc_info = &$this->changes_by_class[$class]['files'];
			foreach ($info['files'] ?: [] as $layer_tag => $tags_list)
			{
				foreach ($tags_list as $tag => $header_inf)
				{
					$cbc_info_before = $cbc_info;
					
					if (empty($cbc_info[$layer_tag][$tag]))
						$cbc_info[$layer_tag][$tag] = $this->info_by_class[$class]["files"][$layer_tag][$tag];
					$cbc_info[$layer_tag][$tag]['status-deps'] = $header_inf['status-deps'];
				}
			}
			unset($cbc_info);
		}
	}
	
	/**
	 * get_info_by_layer_file
	 * 
	 * @param string $layer_tag
	 * @param string $file
	 * 
	 * @return type
	 */
	function get_info_by_layer_file(string $layer_tag, string $file)
	{
		$ret = $this->cache_get_info_by_layer_file[$layer_tag][$file];
		if ($ret)
			return $ret;
		
		if ($this->cache_get_info_by_layer_file === null)
			return $this->get_info_by_layer_file_do_caching($layer_tag, $file);
		else
			return null;
	}
	
	function get_info_by_layer_file_do_caching(string $layer_tag = null, string $file = null)
	{
		$this->cache_get_info_by_layer_file = [];
		$ret = null;
		
		foreach ($this->info_by_class as $full_class_name => $info)
		{
			foreach ($info['files'] ?: [] as $i_layer_tag => $list_by_tag)
			{
				foreach ($list_by_tag as $tag => $header_inf)
				{
					$this->cache_get_info_by_layer_file[$i_layer_tag][$header_inf['file']] = [$full_class_name, $header_inf];
					if (($layer_tag !== null) && ($file !== null) && ($layer_tag === $i_layer_tag) && ($header_inf['file'] === $file))
						$ret = [$full_class_name, $header_inf];
				}
			}
		}
		
		return $ret;
	}
	
	function boot_info_by_class(array $all_files_grouped, array &$removed_files)
	{
		if ((!$this->full_sync) && file_exists($this->temp_code_dir."sync_info_by_class.php"))
		{
			$temp_dir = $this->temp_code_dir;
			$_DATA = null;
			# do not wrap it in a function !!! it will slow down a lot, it copies the data!
			require($temp_dir."sync_info_by_class.php");
			$this->info_by_class = $_DATA;
			if (!is_array($this->info_by_class))
			{
				$this->info_by_class = [];
				$this->full_sync = true;
			}
			else
			{
				$wfs_by_tags_full = $this->tags_to_watch_folders;
				
				# cleanup any status on it
				foreach ($this->info_by_class as &$ibc)
				{
					foreach ($ibc['files'] as &$layers)
					{
						foreach ($layers as &$files)
						{
							$fl = $files['layer'];
							if ($fl === null)
								throw new \Exception('Missing layer on file (do a full resync to fix): '.json_encode($files));
							$layer_path = $wfs_by_tags_full[$files['layer']];
							if ($layer_path === null)
							{
								# we only run for the layers that are active on this run
								continue;
							}
							if ((!file_exists($layer_path.$files['file'])))
							{
								$files['status'] = static::Status_Removed;
								$removed_files[$layer_path][$files['file']] = $files['file_time'];
							}
							else
								$files['status'] = null;
						}
					}
				}
			}
			
			foreach ($this->info_by_class as $inf_by_c) {
				foreach ($inf_by_c['files'] ?? [] as $tags_inf) {
					if (($header_inf = $tags_inf['php']) ?? false) {
						$this->class_to_info[($header_inf['namespace'] ? $header_inf['namespace']."\\" : '').$header_inf['class']] = $header_inf;
					}
				}
			}
		}
		else
		{
			$this->info_by_class = [];
			$this->full_sync = true;
		}
		if (empty($this->info_by_class))
			$this->full_sync = true;
	}
	
	function sort_file_by_tags($a, $b)
	{
		// $key = ($ext === '.tpl') ? '03-' : (($ext_2 === '.url') ? '02-' : (($ext === '.php') ? '01-' : '99-'));
		$def = ['tpl' => 3, 'php' => 1, 'url' => 2, 'res' => 99];
		return ($def[(($p = strpos($a, '@')) !== false) ? substr($a, 0, $p) : $a] ?: 100) <=> 
				($def[(($p = strpos($b, '@')) !== false) ? substr($b, 0, $p) : $b] ?: 100);
	}
	
	function check_if_qmodel(string $full_class_name, int $depth = 0)
	{
		$class = $full_class_name;
		$list_of_classes = [];
		while ($class)
		{
			$in_cache = $this->cache_is_qmodel[$class];
			if ($in_cache !== null)
			{
				foreach ($list_of_classes as $l_class)
					$this->cache_is_qmodel[$l_class] = $in_cache;
				return $in_cache;
			}
			else if (($class === 'QModel') || ($class === 'QIModel'))
			{
				foreach ($list_of_classes as $l_class)
					$this->cache_is_qmodel[$l_class] = true;
				return ($this->cache_is_qmodel[$class] = true);
			}
			$list_of_classes[] = $class;

			# increment
			$class = $this->info_by_class[$class]['extends'];
			$depth++;
			if ($depth > 128)
				throw new \Exception('Going too deep!');
		}
		
		# @TODO - check by implements
		
		return ($this->cache_is_qmodel[$full_class_name] = false);
	}
	
	function check_if_qview_base(string $full_class_name, int $depth = 0)
	{
		$class = $full_class_name;
		$list_of_classes = [];
		while ($class)
		{
			$in_cache = $this->cache_is_qview_base[$class];
			if ($in_cache !== null)
			{
				foreach ($list_of_classes as $l_class)
					$this->cache_is_qview_base[$l_class] = $in_cache;
				return $in_cache;
			}
			else if (($class === 'QViewBase'))
			{
				foreach ($list_of_classes as $l_class)
					$this->cache_is_qview_base[$l_class] = true;
				return ($this->cache_is_qview_base[$class] = true);
			}
			$list_of_classes[] = $class;

			# increment
			$class = $this->info_by_class[$class]['extends'];
			$depth++;
			if ($depth > 128)
				throw new \Exception('Going too deep!');
		}
		
		return ($this->cache_is_qview_base[$full_class_name] = false);
	}
	
	protected function file_put_contents(string $filename, mixed $data, int $flags = 0, $context = null): int|false
	{
		$mtime = filemtime($filename);
		
		$rc = file_put_contents($filename, $data, $flags, $context);
		$status = ($rc === false) ? 'error' : (($mtime === false) ? 'add' : 'change');
		
		$this->modified_gens[($rc === false) ? $filename : realpath($filename)] = [$status, filemtime($filename), $rc, 'put', $mtime];
		
		return $rc;
	}
	
	protected function unlink(string $filename, $context = null): bool
	{
		$mtime = filemtime($filename);
		
		$fp = ($mtime === false) ? $filename : realpath($filename);
		
		$rc = unlink($filename, $context);
		$status = ($rc === false) ? 'error' : 'delete';
		
		$this->modified_gens[$fp] = [$status, null, $rc, 'unlink', $mtime];
		
		return $rc;
	}
	
	protected function process_gen_changes($file_changes)
	{
		# $_filePutContentsIfChanged_->files[realpath($filename)] = filemtime($filename);
		foreach ($file_changes->files ?? [] as $full_path => $m_time)
		{
			$check_time = filemtime($full_path);
			$this->modified_gens[$full_path] = [(($check_time !== false) ? 'change' : 'error'), $check_time, $check_time, 'put', $m_time];
		}
	}
}
