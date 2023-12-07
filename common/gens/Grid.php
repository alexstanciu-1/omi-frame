<?php

namespace Omi\Gens;

class Grid implements IGenerator
{
	use GridTpls, Grid_Config_;
	
	public static $Default_Template = null;
	
	public static $Template = null;
	public static $Config = null;
	
	public static $OpCodes = [];

	public static $CachedData = [];
	
	public static $Place_Holders = [];
	
	public static $Place_Holders_Paths = [];
	public static $Place_Holders_Content = [];
	
	public static $Extra_Selectors = [];
	
	/**
	 * Starts the generate here. Will create LIST & FORM.
	 * 
	 * @param array $config
	 */
	public static function Generate($config, string $template = null)
	{
		if (static::$Default_Template === null)
		{
			if (defined('Q_Saas_Template') && Q_Saas_Template)
				static::$Default_Template = Q_Saas_Template;
			else
				static::$Default_Template = 'v02-modern';
		}
		# static::$OpCodes = [];
		# static::$CachedData = [];
		static::$Place_Holders = [];
		static::$Place_Holders_Paths = [];
		static::$Place_Holders_Content = [];
		static::$Extra_Selectors = [];
		
		static::$Template = $template ?? (static::$Template ?? static::$Default_Template);
		
		$original_config = $config;
		
		// get model info
		$from			= $config["from"];
		$from_info		= q_reset(\QApi::ParseSourceInfo($from));
		$src_from		= q_reset($from_info);
		$storage_model	= (defined('Q_DATA_CLASS') && Q_DATA_CLASS) ? Q_DATA_CLASS : \QApp::GetDataClass();
		$src_from_types = \QApi::DetermineFromTypes($storage_model, $src_from);
		
		if (!$src_from_types)
			throw new \Exception('Failed to determine $src_from_types');
		
		// get class name
		$class = $config["className"] ?: $src_from;

		// get class namespace
		list($class_short, $namespace) = qClassShortAndNamespace($class);

		// set classname in config
		if (!$config["className"])
			$config["className"] = $class;

		// set extend
		$extends = $config["extends"] ?: "\\Omi\\View\\Grid";

		// get storage model type
		$m_type = \QModel::GetTypeByName($storage_model);

		// get model property
		$m_property = $m_type->properties[$from];

		if (!$m_property)
			throw new \Exception("Property {$from} not found on {$m_type->class}!");

		$views = [$class_short => $class_short];
		$additional_views = ($m_property->storage && $m_property->storage["views"]) ? explode(",", $m_property->storage["views"]) : [];
		if ($additional_views && (count($additional_views) > 0))
		{
			foreach ($additional_views as $av)
			{
				if ($m_type->properties[$av])
					throw new \Exception("Additional view named as existing property => ".$av);
				$views[$av] = $av;
			}
		}
		
		static::$Config = $config;
		
		$ret = [];
		
		try
		{
			filePutContentsIfChanged_start();
		
			static::GenerateViews($config, $namespace, $extends, $from,  $storage_model, $src_from_types, $m_property, $views, $original_config);
			
			$opened_content = [];
			
			foreach (static::$Place_Holders as $ph_view => $s_ph)
			{
				foreach ($s_ph as $ph_tag => $s_Place_Holders)
				{
					foreach ($s_Place_Holders as $gen_mode => $ph_def_)
					{
						$ph_def = q_reset($ph_def_);

						if (!$ph_def['@layout'])
							$ph_def['@layout'] = "{{@".implode("}}\n{{@", $ph_def['@select'])."}}";

						foreach ($ph_def['@select'] as $sel_prop_key)
						{

							$ph_content = static::$Place_Holders_Content[$ph_view][$sel_prop_key][$gen_mode];

							if (!$ph_content)
								continue;

							$ph_def['@layout'] = str_replace("{{@{$sel_prop_key}}}", $ph_content, $ph_def['@layout']);
						}

						$path_to_save = static::$Place_Holders_Paths[$ph_view][$ph_tag];

						if (file_exists($path_to_save))
						{
							$file_new_content = $opened_content[$path_to_save] ?: ($opened_content[$path_to_save] = file_get_contents($path_to_save));
							$opened_content[$path_to_save] = str_replace($ph_tag, $ph_def['@layout'], $file_new_content);
							# filePutContentsIfChanged($path_to_save, $new_content);
						}
					}
				}
			}
			
			foreach ($opened_content as $path_to_save => $new_content)
			{
				# $saved_data = file_exists($path_to_save) ? file_get_contents($path_to_save) : null;
				# $not_changed = 
				filePutContentsIfChanged($path_to_save, $new_content);
				
				/*if ($not_changed !== true)
				{
					echo "<textarea>".htmlspecialchars($original_txt)."</textarea>";
					echo "<textarea>".htmlspecialchars($saved_data)."</textarea>";
					echo "<textarea>".htmlspecialchars($new_content)."</textarea>";
				}*/
			}
			
			/*global $_filePutContentsIfChanged_;
			foreach ($_filePutContentsIfChanged_->files ?: [] as $file_path => $file_m_time)
			{
				qvar_dumpk([$file_path, $file_m_time]);
			}*/
			
			$changes = filePutContentsIfChanged_get();
			
			filePutContentsIfChanged_commit();
			
			$ret[0] = $changes;
			
		}
		catch (\Exception $ex)
		{
			filePutContentsIfChanged_roolback();
			throw $ex;
		}
		finally
		{
			# just in case
		}
		
		return $ret;
	}

	public static function GenerateViews($config, $namespace, $extends, $from,  $storage_model, $src_from_types, $m_property, $views = [], $original_config = null)
	{
		$__fp = $config["gen_path"];
		
		if (!$__fp)
			throw new \Exception("Gen Path not provided!");

		// check if the property is collection
		$is_collection = $m_property->hasCollectionType();
		
		$config_folder = $config["gen_config"] ? rtrim($config["gen_config"], "/")."/" : null;
		
		$defined_type_confs = static::Config_View_Get_Defs();
		
		if ($views)
		{
			$first_view = $m_property->name;
			foreach ($views as $view)
			{
				$dir =  rtrim($__fp, "\\/") . "/" . $view;

				// create directory if doesn't exist
				if (!is_dir($dir))
					qmkdir($dir);

				$basic_path = $dir . '/' . $view;
				
				// reset cfg
				$config["cfg"] = [];
				
				foreach ($src_from_types ?: [] as $d_type)
				{
					$cfg = \QModel::GetTypeByName($d_type);
					if ($cfg->cfg)
					{
						$__CONF = ['::class' => []];
						foreach ($cfg->cfg ?: [] as $k => $v)
						{
							if ($defined_type_confs[$k] !== null)
								$__CONF['::class']["@".$k] = $v;
						}
						
						static::SetupExtraConfig($config, null, null, $__CONF);
					}
				}
				
				if ($config_folder)
				{
					if (file_exists($config_folder."@Global.php"))
					{
						$__CONF = null;
						require($config_folder."@Global.php");
						static::SetupExtraConfig($config, null, null, $__CONF);
					}
					if (file_exists($config_folder.$first_view.".php"))
					{
						$__CONF = null;
						require($config_folder.$first_view.".php");
						static::SetupExtraConfig($config, $first_view, null, $__CONF);
					}
					if (file_exists($config_folder.$first_view."@".$view.".php"))
					{
						$__CONF = null;
						require($config_folder.$first_view."@".$view.".php");
						static::SetupExtraConfig($config, $first_view, $view, $__CONF);
					}
				}
				
				if (($grid_def = $config["cfg"]["#grid"]))
				{
					throw new \Exception('@todo');
					// we are using the new way of generating 
					$get = [];
					$post = [
						// "folders" => \QAutoload::GetWatchFolders(),
						"runtime" => \QAutoload::GetRuntimeFolder(),
						"config_folder" => $config_folder,
						"current_dir" => getcwd(),
						"config" => $original_config,
						'data_class' => \QApp::GetDataClass()
					];
					$status = \QFunc::HttpRequest(QNEW_GRID_GEN_URL, $get, $post);
					// and then we return
					if ($status === false)
						throw new \Exception('Generator failiure');
					
					try
					{
						$ret_data = json_decode($status, true);
						if ($ret_data && $ret_data['output'])
							echo $ret_data['output'];
						else if ($ret_data === true) {} // all is good 
						else 
							echo $status;
					}
					catch (\Exception $ex)
					{
						echo $status;
					}
					
					return;
				}
				
				if (defined('Q_GENERATE_GRID_BOXES_BY_DEFAULT') && Q_GENERATE_GRID_BOXES_BY_DEFAULT && (!$config['cfg']['::']['@groups']) && 
						$form_selector = static::Get_Form_Selector($config, $view, $storage_model))
				{
					$flat_selector = static::Flat_Selector($form_selector, $m_property);
					if (!($layout_placement_list = $config['cfg']['::']['@boxes']))
					{
						$layout_placement = static::Propose_Boxes_Setup($form_selector, $m_property, $flat_selector, $view);
						$layout_placement_list = [$layout_placement];
                        # @TODO - is this a bug or not ?! #  $config['cfg']['::']['@boxes'] = $layout_placement_list;
					}
					
					# @TODO - very stupid bug ... we need to understand it !
					#		we understand it ... we need to send the info that the layout container is custom explicitly (now it is determined) !
					# unset($config['cfg']['::']['@boxes']);
                    # qvar_dumpk($view, $config['cfg']['::']['@boxes']);
					
					foreach ($layout_placement_list as $layout_placement)
					{
						list ($grp_layout, $selected_props) = static::Render_Boxes_To_Layout($layout_placement, $flat_selector, $m_property, $config);
						
						if (!$selected_props)
							throw new \Exception('Missing properties!');
						
						$groups[q_reset($selected_props)] = 'after';
						$groups['@select'] = $selected_props;
						$groups['@layout'] = $grp_layout;
					
						$config['cfg']['::']['@groups']["@".implode("_AND_", $layout_placement["for"])] = $groups;
					}
				}
				
				/**
				 * We do a check that we have the elements in the selector
				 */
				$dataCls = \QApp::NewData();
				foreach ($config['cfg']['::']['@groups'] ?: [] as $grp_for_tag => $grp_data)
				{
					if ($grp_for_tag[0] !== '@')
						continue;
					$grp_types = explode("_AND_", substr($grp_for_tag, 1));
					$grp_sel_elements = $grp_data['@select'];
					if (!$grp_sel_elements)
						throw new \Exception('Missing select definition in `@groups` for: '.$view.' - '.$grp_for_tag);
					$grp_selector_fixed = [];
					foreach ($grp_sel_elements as $gse)
						$grp_selector_fixed = qJoinSelectors($grp_selector_fixed, qParseEntity($gse));

					foreach ($grp_types as $grp_ty)
					{
						$func_select_name = null;
						if (($grp_ty === 'form') || ($grp_ty === 'view'))
						{
							$must_be_in_selector = $dataCls::GetEntityForGenerateForm_Final($view);
							$func_select_name = "App::GetEntityForGenerateForm('{$view}')";
						}
						else if (($grp_ty === 'list') || ($grp_ty === 'bulk'))
						{
							$must_be_in_selector = $dataCls::GetEntityForGenerateList_Final($view);
							$func_select_name = "App::GetEntityForGenerateList('{$view}')";
						}
						else 
							throw new \Exception('Unknow view type: '.$grp_ty);

						$q_missing_selectors = qSelectorsMissing($must_be_in_selector, $grp_selector_fixed);
						if ($q_missing_selectors)
						{
							throw new \Exception("The following elements are missing in `{$func_select_name}`: '".qImplodeEntity($q_missing_selectors)."'");
						}
					}
				}
				
				$config['cfg_type_flags'] = [];
				foreach ($config["cfg"]["::"] ?: [] as $k => $v)
				{
					if (($k[0] === '@') && ($defined_type_confs[substr($k, 1)] !== null))
					{
						$config['cfg_type_flags'][substr($k, 1)] = $v;
						if (substr($k, 1) === 'steps')
						{
							$steps_path = $config_folder."~components/steps/{$v}.php";
							$_CONF = null;
							require($steps_path);
							$config['cfg_type_flags'][substr($k, 1)] = $__CONF;
							# if (file_exists($config_folder . "@Global.php"))
						}
					}
				}
				
				static::GenerateView($config, $namespace, $view, $extends, $from, $is_collection, $storage_model, $src_from_types, $basic_path);
			}
		}
	}

	protected static function SetupExtraConfig(&$config, string $property = null, string $view = null, array $context_config = null)
	{
		if (!$context_config)
			return;

		// qvar_dump("SetupExtraConfig(&$config, string $view, array $context_config");
		$cfg = $context_config;
		if ($config["cfg"] === null)
			$config["cfg"] = [];
		
		// qvar_dump($context_config);
		
		static::SetupExtraConfigMerge($config["cfg"], $context_config);
	
		// qvar_dump($config["cfg"]);
		/*
		if ((!$property) && (!$view))
			$config["cfg"][] = $cfg;
		else if (!$view)
			$config["cfg"]["//{$property}"] = $cfg;
		else
			$config["cfg"]["//{$property}/{$view}"] = $cfg;*/
	}

	protected static function SetupExtraConfigMerge(array &$cfg, array $new, string $rel_path = "")
	{
		$se_ret = null;
		
		foreach ($new as $_k => $v)
		{
			$k = trim($_k);
			
			// in case we enter the view, we no longer expect selectors
			if  (($k[0] === "#"))
			{
				if ($se_ret === null)
				{
					if ($rel_path)
						$se_ret = &$cfg[$rel_path];
					else
						$se_ret = &$cfg;
				}
				$se_ret[$k] = $v;
				
				// @TODO later, implement directives inside #grid : +add, -remove, ~update
				//				view is to be processed on a separate thread
			}
			else if (($k[0] === "@"))
			{
				if ($se_ret === null)
				{
					if ($cfg[$rel_path] === null)
						$cfg[$rel_path] = [];
					$se_ret = &$cfg[$rel_path];
				}
				$add_mode = false;
				if (($add_mode = (substr($k, -3, 3) === "[+]")))
					$k = substr($k, 0, -3);
				// handle config entry
				$k_parts = explode('.', $k);
				
				if (count($k_parts) > 1)
				{
					$r = &$se_ret;
					foreach ($k_parts as $kp)
					{
						$r = &$r[$kp];
						if ($r === null)
							$r = [];
					}
				
					if ($add_mode)
					{
						if (!is_array($r))
							$r = [$r];
						if (is_array($v))
						{
							foreach ($v as $_sv)
								$r[] = $_sv;
						}
						else
							$r[] = $v;
					}
					else if (is_array($v))
						$r = array_replace_recursive($r, $v);
					else
						$r = $v;
					unset($r);
				}
				else if (isset($se_ret[$k]))
				{
					if ($add_mode)
					{
						if (!is_array($se_ret[$k]))
							$se_ret[$k] = [$se_ret[$k]];
						if (is_array($v))
						{
							foreach ($v as $_sv)
								$se_ret[$k][] = $_sv;
						}
						else
							$se_ret[$k][] = $v;
					}
					else
						$se_ret = array_replace_recursive($se_ret, [$k => $v]);
				}
				else
					$se_ret[$k] = $v;
			}
			else
			{
				// explode first
				$selectors = explode(",", $k);
				foreach ($selectors as $sel)
				{
					// handle selector
					$key = $rel_path ? ($rel_path.".".trim($sel)) : trim($sel);
					if (($class_ends = (substr($key, -7, 7) === "::class")))
						$key = substr($key, 0, -7);
					// fix middle ::
					$key = str_replace('::', '.', $key);
					$key = rtrim($key, ".");
					if ($class_ends)
						$key .= "::";
					
					static::SetupExtraConfigMerge($cfg, $v, $key);
				}
			}
		}
		
		return $se_ret ?: null;
	}

	protected static function ExtractExtraConfig(array $cfg, string $path, string $key)
	{
		// ends with ::
		// *.Address.Field			=> %ends%Address.Field
		// Order.Item.*.Address.Field	=> %wrap%Order.Item.*.Address.Field
		$key = explode('.', $key);
		$c_keys = count($key);
		$ret = [];
		
		foreach ($cfg as $selector => $data)
		{
			$matches = false;
			if ($selector[0] === '*')
			{
				// ends with
				if (substr($selector, 2) === $path)
					$matches = true;
			}
			else if (($p = strpos('*', $selector)) !== false)
			{
				// contains
				$sel_len = strlen($selector);
				$path_len = strlen($path);
				if (($sel_len >= ($path_len - 2)) &&
						(substr($selector, 0, $p) === substr($path, 0, $p)) &&
						(substr($selector, $p + 1) === substr($path, -(strlen($selector) - ($p + 1)))))
				{
					// ok
					$matches = true;
				}
			}
			else
			{
				// full match
				if ($selector === $path)
				{
					$matches = true;
				}
			}
			
			if ($matches)
			{
				$extr = null;
				$hasResult = null;
				if ($c_keys === 2)
				{
					if (($hasResult = isset($data[$key[0]][$key[1]])))
						$extr = $data[$key[0]][$key[1]];
				}
				else if ($c_keys === 1)
				{
					if (($hasResult = isset($data[$key[0]])))
						$extr = $data[$key[0]];
				}
				else if ($c_keys === 3)
				{
					if (($hasResult = isset($data[$key[0]][$key[1]][$key[2]])))
						$extr = $data[$key[0]][$key[1]][$key[2]];
				}
				else if ($c_keys === 4)
				{
					if (($hasResult = isset($data[$key[0]][$key[1]][$key[2]][$key[3]])))
						$extr = $data[$key[0]][$key[1]][$key[2]][$key[3]];
				}
				else
				{
					$d = $data;
					foreach ($key as $k)
					{
						if ($d === null)
							break;
						$d = $d[$k];
					}

					if ($d !== null)
					{
						$extr = $d;
						$hasResult = true;
					}
				}

				if ($hasResult)
				{
					$ret = $ret ? array_replace_recursive($ret, $extr) : $extr;
				}
			}
		}

		return ($ret !== []) ? $ret : null;
	}
	
	public static function GenerateView($config, $namespace, $view, $extends, $from, $is_collection, $storage_model, $src_from_types, $basic_path, $path = null)
	{
		$new_mode = defined('Q_RUN_CODE_NEW_AS_TRAITS') && Q_RUN_CODE_NEW_AS_TRAITS;
	
		if (!$config)
			$config = [];

		// set path
		$file_path = $new_mode ? ($basic_path . '.class.php') : $basic_path . '.php';

		//echo "GENERATE VIEW {$view}<br/>";
		$config["Caption"] = static::ExtractExtraConfig($config["cfg"], "::", "@caption");
		$viewCaption = $config["Caption"] ?: \QModel::GetCaption($config["Title"], $src_from_types, null, $config["__view__"]);
		
		if (defined('Q_GENERATE_GRID_CAPTION_REPLACE_UNDERSCORE') && Q_GENERATE_GRID_CAPTION_REPLACE_UNDERSCORE)
		{
			# $config["Caption"] = preg_replace('/[\\_\\s]+/', " ", $config["Caption"]);
			$viewCaption = preg_replace('/[\\_\\s]+/', " ", $viewCaption);
		}
		
		$escaped_from = "\"".qaddslashes($from)."\"";
		$escaped_view = "\"".qaddslashes($view)."\"";

		$code_php = "<?php "
			. "\nnamespace {$namespace}; "
			. "\n"
			. "/**\n"
			. ($new_mode ? " * @class.name {$view}\n" : "")
			. " */"
			. "\nclass {$view}".($new_mode ? '_'.Q_GENERATED_VIEW_FOLDER_TAG.'_' : '')." extends {$extends} "
			. "\n{"
			. ($new_mode ? "" : "\n\tuse {$view}_GenTrait;")
			. "\n\t/**"
			. "\n\t * @var string"
			. "\n\t */"
			. "\n\tpublic \$from = {$escaped_from};"
			. (!$is_collection ? 
					"\n\t/**"
					. "\n\t * @var string"
					. "\n\t */"
					. "\n\tpublic \$_is_reference = true;" : "")

			. "\n\t/**"
			. "\n\t * @var string"
			. "\n\t */"
			. "\n\tpublic static \$FromAlias = {$escaped_view};\n"
			. "\n\t/**"
			. "\n\t * @var string"
			. "\n\t */"
			. "\n\tpublic \$gridHeadingText = '{$viewCaption}';\n";
			

		// setup current view on config
		$config["__view__"] = $view;

		$config["Caption"] = static::ExtractExtraConfig($config["cfg"], "::", "@caption");
		$config["Caption_Add"] = static::ExtractExtraConfig($config["cfg"], "::", "@caption_add");
		$config["Caption_Edit"] = static::ExtractExtraConfig($config["cfg"], "::", "@caption_edit");
		$config["Caption_View"] = static::ExtractExtraConfig($config["cfg"], "::", "@caption_view");
		$config["Caption_List"] = static::ExtractExtraConfig($config["cfg"], "::", "@caption_list");
		$config["Caption_Delete"] = static::ExtractExtraConfig($config["cfg"], "::", "@caption_delete");
		
		# @view.checkboxes
		$config["@view.checkboxes"] = static::ExtractExtraConfig($config["cfg"], "::", "@view.checkboxes");
		
		$config["Title"] = $config["Caption"] ?: $view;
		
		// get settings
		$config["__settings__"] = 
				static::ExtractExtraConfig($config["cfg"], "::", "@settings") ?: 
				static::GetModelStorageData($src_from_types ? q_reset($src_from_types) : null, $view, "settings");
		
		// get tabs
		$config["__tabs__"] = 
				static::ExtractExtraConfig($config["cfg"], "::", "@tabs") ?: 
				static::GetModelStorageData($src_from_types ? q_reset($src_from_types) : null, $view, "display.tabs");
		
		// get sections
		$config["__sections__"] = 
				static::ExtractExtraConfig($config["cfg"], "::", "@sections") ?: 
				static::GetModelStorageData($src_from_types ? q_reset($src_from_types) : null, $view, "display.sections");
		
		// get subsections
		$config["__sub_sections__"] = 
				static::ExtractExtraConfig($config["cfg"], "::", "@subSections") ?: 
				static::GetModelStorageData($src_from_types ? q_reset($src_from_types) : null, $view, "display.sub_sections");

		$config["withSecurity"] = 
				static::ExtractExtraConfig($config["cfg"], "::", "@withSecurity") ?: 
				static::GetModelStorageData($src_from_types ? q_reset($src_from_types) : null, $view, "withSecurity");

		// get selector
		$form_selector = static::Get_Form_Selector($config, $view, $storage_model);
				
		// get namespace
		$config["namespace"] = $namespace;

		//save all types
		$config["src_from_types"] = $src_from_types;

		//save all types
		$config["parent_model"] = $config["storage_model"] = $storage_model;
		$config["property"] = $from;

		$__grid_config = [];
		$__q_grid_config = [];
		
		//$view, $search_info, $obinf, $src_from_types, $config_clone, $__grid_config, $path
		//$from, $storage_model, $basic_path.".list", $src_from_types, $list_selector, true, false, "", $search_str, $path
		//$from, $storage_model, $basic_path.".form", $src_from_types, $form_selector, true,
			//$tabs, $on_tab, [], "", [], [], $search_str, $path
		
		if ($is_collection)
		{
			// get list selector
			$config["selector"] = $config["selector:list"] ?: (method_exists($storage_model, "GetEntityForGenerateList") ? $storage_model::GetEntityForGenerateList_Final($view) : $storage_model::GetPropertyListingEntity($view));

			$analyze_query = null;// $storage_model::GetPropertyListingQuery($from);
			$analyze_start_type = null;
			$search_str = "";
			if (!$analyze_query)
			{
				$analyze_start_type = q_reset($src_from_types);
				$analyze_query = $analyze_start_type::GetListingQuery();
			}

			$analyze_result = null;
			$search_info = null;
			$order_by_info = null;
			
			// check if analyze
			if ($analyze_query)
			{
				// analyze result
				//qvardump($analyze_query);
				$analyze_result = \QQueryAnalyzer::Analyze($analyze_query, $analyze_start_type);
				
				//qvardump($analyze_result);
				$search_info = \QQueryAnalyzer::GetSearchInfo($analyze_result);
				
				//qvardump($analyze_query, $search_info, $analyze_result);

				$order_by_info = \QQueryAnalyzer::GetOrderByInfo($analyze_result);

				//var_dump($order_by_info);
				$obinf = [];
				if ($order_by_info)
				{
					foreach ($order_by_info as $v)
					{
						if ($v["params"])
						{
							foreach ($v["params"] as $vv)
							{
								try
								{
									$o = &$obinf;
									if (!$vv["_idf"])
									{
										# if (\QAutoload::GetDevelopmentMode())
										#	qvar_dumpk($analyze_query, $vv, $order_by_info);

										throw new \Exception("Idf not found !");
									}
									end($vv["_idf"]);
									$last_k = key($vv["_idf"]);
									foreach ($vv["_idf"] as $ik => $p)
									{
										if (!isset($o[$p]))
											$o[$p] = [];
										if ($last_k === $ik)
											$o[$p] = $vv;
										else
											$o = &$o[$p];
									}
									unset($o);
								}
								catch (\Exception $ex)
								{
									qvar_dumpk("ERROR", $ex->getMessage(), $ex->getTraceAsString());
								}
							}
						}
					}
				}
				
				$config["analyze_result"]  = $analyze_result;
				$config["search_info"]  = $search_info;
				$config["order_by_info"]  = $obinf;

				// get search str
				$adv_search_str = static::GenerateListSearch($config, $__grid_config, $__q_grid_config, $path);

			}
			

			// setup grid cfg
			$config["_GRID_CFG_"] = $__grid_config;
			$config["_GRID_Q_CFG_"] = $__q_grid_config;

			// list view mode
			$config["__readonly"] = true;
			$config["__list"] = true;
			$config["__listmode"] = true;
			static::GenerateGridList($config, $path, $basic_path.".list", true, [$adv_search_str]);

			$config["__readonly"] = false;
			$config["__list"] = true;
			$config["__listmode"] = false;
			static::GenerateGridList($config, $path, $basic_path.".bulk", true, [$adv_search_str]);
		}
		else
		{
			// gnerate list search
			$adv_search_str = static::GenerateListSearch($config, $__grid_config, $__q_grid_config, $path);
		}
	
		// setup form selector
		$config["selector"] = $form_selector;
		
		$config["__readonly"] = true;
		$config["__list"] = false;
		$config["__listmode"] = false;
		static::GenerateGridForm($config, $path, $basic_path.".view", true, false, [$adv_search_str]);

		$config_clone = $config;
		$config_clone["__readonly"] = false;
		$config_clone["__list"] = false;
		$config_clone["__listmode"] = false;
		static::GenerateGridForm($config_clone, $path, $basic_path.".form", true, false, [$adv_search_str]);
		
		if ($__grid_config && ($cfg_code = qArrayToCode($__grid_config, null, false)))
		{
			$code_php .= "\n\t/**"
			. "\n\t * @var array"
			. "\n\t */"
			. "\n\tpublic static \$CONFIG = {$cfg_code}";
		}
		
		if (static::$Extra_Selectors[$config["__view__"]])
		{
			$code_php .= "\n\t/**"
			. "\n\t * @var array"
			. "\n\t */"
			. "\n\tpublic static \$Extra_Selectors = ".var_export(static::$Extra_Selectors[$config["__view__"]], true).";";
		}
		
		$code_php .= "\n}";
				
		// write php code into file
		//qvardump($__grid_config, $file_path, $code_php);
		filePutContentsIfChanged($file_path, $code_php);
	}

	/**
	 * 
	 * @param type $config
	 * @param type $__grid_cfg
	 * @param type $path
	 * @return type
	 */
	public static function GenerateListSearch($config, &$__grid_cfg = [], &$__q_grid_config = [], $path = null)
	{
		$viewTag = $config["__view__"];
		$analyze_search_inf = $config["search_info"];
		$order_by_inf = $config["order_by_info"];
		$parent_model = $config["parent_model"];
		$src_from_types = $config["src_from_types"];

		$dataCls = \QApp::GetDataClass();
		$acceptedFilters = $dataCls::GetAcceptedFilters($viewTag);
		
		// go through each param
		$__search_fields = [];
		//qvardump($analyze_search_inf);
		if ($analyze_search_inf && (count($analyze_search_inf) > 0))
		{
			//qvardump($analyze_search_inf);
			$_pp = 0;
			$qpp = 0;
			
			foreach ($analyze_search_inf as $label => $data)
			{
				// skip search fields if the view has it seted
				if (($acceptedFilters === null) || (is_array($acceptedFilters) && !in_array($label, $acceptedFilters)))
					continue;

				// get info and params
				$inf = $data["inf"];
				$params = $data["params"];

				// check if params
				if (!$params)
				{
					// set defautl type boolean
					$type = "boolean";
					$param = $inf["first_idf"];
					$operation = $param ? $param["_op"] : null; // LIKE, =, IN, BETWEEN, ...
					$identifiers_path = $param ? $param["_idf"] : null;
					$parent_type = $param ? $param["_prop_ty"] : null;
					$parent_prop = $param ? $param["_prop"]: null;

					$bind_path = [$label];
					$binds_count = 1;
					// check box to enable disable bind
					//$input = static::GenerateSearchInput($parent_model, $type, $label, $operation, $identifiers_path, $params, $parent_prop, $parent_type, $bind_path, $binds_count);
				}
				else
				{
					if (!$__grid_cfg["_QSEARCH_"])
						$__grid_cfg["_QSEARCH_"] = [];
					
					if (!$__grid_cfg["_Q_SET_SEARCH_"])
						$__grid_cfg["_Q_SET_SEARCH_"] = [];
					
					if (!$__q_grid_config["_QSEARCH_"])
						$__q_grid_config["_QSEARCH_"] = [];
					
					if (!$__q_grid_config["_Q_SET_SEARCH_"])
						$__q_grid_config["_Q_SET_SEARCH_"] = [];

					// count params
					$binds_count = count($params);
					
					foreach ($params as $param)
					{
						// get info from param
						$identifiers_path = $param["_idf"];
						$type			= $param["_ty"];
						$operation		= $param["_op"];
						$parent_type	= $param["_prop_ty"];
						$parent_prop	= $param["_prop"];
						$bind_path		= $param["bind_path"];
						
						//qvardump($param);
						if ($binds_count === 1)
						{
							// if it's the only param in the bind, there is no point to have a last index
							end($bind_path);
							if ((current($bind_path) === 0) || (current($bind_path) === "0"))
								array_pop($bind_path);
						}

						$fbp_ts = null;
						foreach ($bind_path ?: [] as $_bpk => $_bpv)
						{
							$_intial_bpv = $_bpv;
							if (substr($_bpv, 0, ($_qslen = strlen("QINSEARCH_"))) == "QINSEARCH_")
							{
								$_bpv = substr($_bpv, $_qslen);
							}

							if ($fbp_ts === null)
								$fbp_ts = $_bpv;

							if (!$__grid_cfg["_QSEARCH_"][$_bpv])
								$__grid_cfg["_QSEARCH_"][$_bpv] = [];
							$__grid_cfg["_QSEARCH_"][$_bpv][] = [$_intial_bpv, static::$OpCodes[$operation] ?: (static::$OpCodes[$operation] = \QQueryAnalyzer::GetSearchOpCode($operation))];
							
							if (!$__q_grid_config["_QSEARCH_"][$_bpv])
								$__q_grid_config["_QSEARCH_"][$_bpv] = [];
							$__q_grid_config["_QSEARCH_"][$_bpv][] = [$_intial_bpv, static::$OpCodes[$operation] ?: (static::$OpCodes[$operation] = \QQueryAnalyzer::GetSearchOpCode($operation))];

							$bind_path[$_bpk] = $_bpv;
						}

						$prop_cfg = $config;
						$prop_cfg["property"] = $label;
						$prop_cfg["parent_model"] = is_array($src_from_types) ? q_reset($src_from_types) : $src_from_types;
						$prop_path = ($path ? rtrim($path, ".") . "." : "") . $label;
						
						$_LIST_PROP_FLAGS = static::GetPropertyFlags($prop_cfg, $prop_path, true, false, true);
						
						// generate search input
						list($search_field, $bind_val_index, $input_name) = static::GenerateSearchInput($prop_cfg, $prop_path, $type, $operation, $identifiers_path, $params, $parent_prop, 
							$parent_type, $bind_path, $binds_count, $_pp);

						// prepare search data
						$__grid_cfg["_Q_SET_SEARCH_"][$fbp_ts] = [
							"_QS_DATA_" => $__grid_cfg["_QSEARCH_"][$fbp_ts],
							"_QS_FIELD_" => [$label, $search_field, $bind_val_index, $operation, $input_name, $parent_prop, $param["_prop_ty"]]
						];

						$__search_fields[] = [$label, $_LIST_PROP_FLAGS["display.caption"], $search_field, $bind_val_index, $operation, $input_name, $parent_prop, $param["_prop_ty"]];
						
						$prop_cfg["qsearch"] = true;
						$_LIST_PROP_FLAGS = static::GetPropertyFlags($prop_cfg, $prop_path, true, false, true);

						// generate search input
						list($search_field, $bind_val_index, $input_name) = static::GenerateSearchInput($prop_cfg, $prop_path, $type, $operation, $identifiers_path, $params, $parent_prop, 
							$parent_type, $bind_path, $binds_count, $qpp);

						$__q_grid_config["_Q_SET_SEARCH_"][$fbp_ts] = [
							"_QS_DATA_" => $__grid_cfg["_QSEARCH_"][$fbp_ts],
							"_QS_FIELD_" => [$label, $search_field, $bind_val_index, $operation, $input_name, $parent_prop, $param["_prop_ty"]]
						];

						$_pp++;
						$qpp++;
					}
				}
			}
		}
				
		// generate order_by
		$oby_data = [];
		if ($order_by_inf && (count($order_by_inf) > 0))
		{
			foreach ($order_by_inf as $label => $data)
			{
				if ((!$data["bind_path"]) && is_array($data))
				{
					foreach ($data as $k => $v)
					{
						if ((count($data) > 1) && ($k == "Id"))
							continue;
						$bind_path = $v["bind_path"];
					}
				}
				else
					$bind_path = $data["bind_path"];

				$input_name = is_array($bind_path) ? q_reset($bind_path) : $bind_path;
				$bind_val_index = "[\"" . $input_name . "\"]";

				// add order by field
				$oby_data[] = [$input_name, $bind_val_index];
			}
		}

		$__grid_cfg["__oby_data__"] = $oby_data;

		/*
		Variables used in template

		$__search_fields
		$oby_data
		*/

		ob_start();
		require(static::GetTemplate("search/search.tpl", $config));
		return ob_get_clean();
	}
	/**
	 * Generates the search field input.
	 * 
	 * @param type $parent_model
	 * @param type $type
	 * @param type $key
	 * @param type $operation
	 * @param type $identifiers_path
	 * @param type $params
	 * @param type $parent_prop
	 * @param type $parent_type
	 * @param type $bind_path
	 * @param type $binds_count
	 * @return type
	 * @throws \Exception
	 */
	public static function GenerateSearchInput($config, $path, $type, $operation, $identifiers_path, $params, $parent_prop, 
		$parent_type, $bind_path, $binds_count, $spos = 0)
	{
		$viewTag = $config["__view__"];
		$parent_model = $config["parent_model"];
		$key = $config["property"];
		
		// check if type is array and reset
		if (is_array($type))
			$type = q_reset($type);
		
		// check bind path to gen the input name
		if (count($bind_path) > 1)
		{
			$bp = $bind_path;
			array_shift($bp);
			$input_name = q_reset($bind_path)."[".implode("][", $bp)."]";
		}
		else
			$input_name = q_reset($bind_path);

		$bind_val_index = "";
		foreach ($bind_path as $b)
			$bind_val_index .= is_numeric($b) ? "[{$b}]" : "[\"".qaddslashes($b)."\"]";

		// get props flags
		$_PROP_FLAGS = static::GetPropertyFlags($config, $path, false, false, true);
		// get type flags
		$_TYPE_FLAGS = $config['cfg_type_flags'];
		
		$propCaption = $_PROP_FLAGS["display.caption"];
		$qsearch = $config["qsearch"];
		
		//qvardump($qsearch);

		# ($model, $prop, $config["__view__"], $mandatory, $validation, $fix, $in_search)
		$mixed_data = \QModel::MixPropertyData($parent_model, $key, $viewTag, null, null, null, true);
		$mixed_types = $mixed_data["types"];

		$_is_reference = isset($type) && ((strtolower($type[0]) !== $type[0]) || ($identifiers_path && (strtolower(end($identifiers_path)) === "id")));

		if ($_is_reference)
		{

			$dd_property = static::GetAppPropertyFor(array($parent_type => $parent_type), $parent_model, $bind_path[0]);
			$esc_dd_property = $dd_property ? qaddslashes($dd_property->name) : null;
			$esc_dd_caption = "Select";

			$esc_caption_selector = qaddslashes(static::GetCaptionSelectorFor(array($parent_type => $parent_type), $parent_model, $bind_path[0]));
			$_isTree = ($mixed_types && $mixed_types["display"] && isset($mixed_types["display"]["tree"]));
		}

		//qvardump($parent_model, $type, $key);

		/*
		Variables used in template
		$params
		$input_name
		$type
		$identifiers_path
		$operation
		$_isTree
		$esc_dd_property
		$esc_caption_selector
		$esc_dd_caption
		$bind_path
		$bind_val_index
		*/

		if ($_PROP_FLAGS["__is_scalar"])
		{	
			$_is_mandatory = $_PROP_FLAGS["mandatory"];
			$_is_password = $_PROP_FLAGS["type.password"];
			$_is_file = $_PROP_FLAGS["type.file"]; 
			$_is_string = $_PROP_FLAGS["type.string"];
			$_is_textarea = $_PROP_FLAGS["display.textarea"];
			$_is_date = $_PROP_FLAGS["type.date"];
			$_date_format = $_PROP_FLAGS["date.format"];
			$_is_enum = $_PROP_FLAGS["type.enum"];
			$_is_set = $_PROP_FLAGS["type.set"];
			
			$_enum_vals = $_PROP_FLAGS["enum.vals"];
			$_set_vals = $_PROP_FLAGS["set.vals"]; 
			$_enum_captions = $_PROP_FLAGS["enum.captions"];
			$_enum_styles = $_PROP_FLAGS["enum.styles"];
			$_enum_display = $_PROP_FLAGS["enum.display"];
			$_q_valid = $_PROP_FLAGS["validation"]; 
			$_q_fix = $_PROP_FLAGS["fix"]; 
			$_q_info = $_PROP_FLAGS["info"]; 
			$_extra_attrs = $_PROP_FLAGS["display.attrs"];
			
			$_is_bool = $_PROP_FLAGS["type.bool"]; 
			if ($_is_bool && (!$_is_enum))
			{
				$_is_bool = null;
				$_is_enum = true;
				if (!$_enum_vals)
					$_enum_vals = [1, 0, null];
				if (!$_enum_captions)
					$_enum_captions = [1 => "Yes", 0 => "No", null => "Any"];
				$_force_heading = true;
				$operation = "=";
			}
		}

		ob_start();
		require(static::GetTemplate("search/search_field.tpl", $config));
		return [ob_get_clean(), $bind_val_index, $input_name];
	}
	/**
	 * 
	 * @param string $template
	 * @param boolean $isFormElement
	 * @return string
	 * @throws \Exception
	 */
	public static function GetTemplate($template, array $config = null)
	{
		if ($config === null)
			$config = static::$Config;
		
		$config_folder = static::GetConfigFolder($config);
		$alt_template = $config_folder ? ($config_folder."~templates/{$template}") : null;
		
		if ($alt_template && file_exists($alt_template))
		{
			return $alt_template;
		}
		else
		{
			$template = __DIR__ . "/templates/" . static::$Template . "/{$template}";
		
			if (!file_exists($template))
				throw new \Exception("Template \"{$template}\" not found!");
			return $template;
		}
	}

	/**
	 * Generalte a list:
	 *		- basic listing
	 *		- bulk editing
	 *		- collection in property
	 * Calls ListForm
	 * 
	 * @param type $config
	 * @param type $property
	 * @param type $parent_model
	 * @param type $basic_path
	 * @param type $src_from_types
	 * @param type $selector
	 * @param type $is_top
	 * @param type $parent_in_list_mode
	 * @param type $tabs
	 * @param type $on_tab
	 * @param type $vars_path
	 * @param type $vars_post_path
	 * @param type $vars_relative
	 * @param type $vars_map
	 */
	public static function GenerateGridList($config, $path, $basic_path, $is_top = false, $search_data = [])
	{
		list($search_str, $qsearch_str) = $search_data;
		$property = $config["property"];
		$parent_model = $config["parent_model"];
		$src_from_types = $config["src_from_types"];
		$selector = $config["selector"];
		
		if ($is_top && isset($config["cfg"]["::"]['@list-selector']))
		{
			$selector = is_string($config["cfg"]["::"]['@list-selector']) ? qParseEntity($config["cfg"]["::"]['@list-selector']) : $config["cfg"]["::"]['@list-selector'];
		}

		$viewCaption = $config["Caption"] ?: \QModel::GetCaption($config["Title"], $src_from_types, null, $config["__view__"]);
		$listCaption = $config["Caption_List"];
		
		if (defined('Q_GENERATE_GRID_CAPTION_REPLACE_UNDERSCORE') && Q_GENERATE_GRID_CAPTION_REPLACE_UNDERSCORE)
		{
			$viewCaption = preg_replace('/[\\_\\s]+/', " ", $viewCaption);
			$listCaption = preg_replace('/[\\_\\s]+/', " ", $listCaption);
		}

		// unset selector * from query string
		if ($selector && is_array($selector) && ($selector["*"] !== null))
			unset($selector["*"]);
		
		if ($selector === null)
			$selector = [];

		// get readonly from config
		$read_only = $config["__readonly"] ?: false;

		// get props flags
		$_PROP_FLAGS = static::GetPropertyFlags($config, $path, true, $read_only);
		// get type flags
		$_TYPE_FLAGS = $config['cfg_type_flags'];
		
		$xg_tag = static::GetXGTag($property, $parent_model, $read_only);

		// step 1: type of collection : $src_from_types
		if ($src_from_types === null)
		{
			$qm_prop = \QModel::GetTypeByName($parent_model)->properties[$property]->getCollectionType();
			$src_from_types = $qm_prop->getAllInstantiableReferenceTypes();

			if (!$src_from_types)
				$src_from_types = $qm_prop->options;
		}

		// get heading props
		list($headings, $headings_rates, $heading_rates_total, $heading_props) = static::GetHeadingsData($config, $read_only, $src_from_types, $selector);
		
		if (!$headings)
			$headings = [];

		$_is_subpart = $_PROP_FLAGS["struct.subpart"];

		$_is_scalar = $_PROP_FLAGS["__is_scalar"];

		$_coll_uses_chks = $_PROP_FLAGS["coll.checkboxes"];

		if ($_is_scalar && $_coll_uses_chks)
			throw new \Exception("Cannot render collection of scalars with checkboxes!");

		$_use_chks = ($is_top || $_coll_uses_chks);

		$item_basic_path = $basic_path."Item";

		$noResultsPath = $basic_path."NoResults";
		$include_method = substr($item_basic_path, strrpos($item_basic_path, ".") + 1);
		$noResultsMethod = substr($noResultsPath, strrpos($noResultsPath, ".") + 1);

		//&$tabs = null, &$on_tab = null, $vars_path = [], $vars_post_path = "", $vars_relative = [], $vars_map = [], $path = null

		$config["headings_data"] = [$headings, $headings_rates, $heading_rates_total, $heading_props];
		list($subBlocks, $bulk_edit_props) = static::GenerateGridListForm($config, $path, $item_basic_path, $is_top);
		
		// calculates the headings width percent

		$_headings_data = [];
		$_oby_data = $config["_GRID_CFG_"]["__oby_data__"];
		
		//qvardump("\$_oby_data", $_oby_data);
		foreach ($headings as $pos => $heading)
		{			
			//$procent_width = round(($headings_rates[$pos] / $heading_rates_total ) * 100, 4);
			$h_prop = $heading_props[$pos];
			
			$property_cfg = $config;
			$property_cfg["property"] = $h_prop->name;
			$property_cfg["parent_model"] = $src_from_types;
			$property_cfg["selector"] = $selector ? $selector[$h_prop->name] : null;
			$property_cfg["src_from_types"] = null;
			$prop_path = ($path ? rtrim($path, ".") . "." : "") .  $h_prop->name;

			$_LIST_PROP_FLAGS = static::GetPropertyFlags($property_cfg, $prop_path, true, $read_only);
			
			if ($_LIST_PROP_FLAGS['no_render'])
			{
				# do not render
				continue;
			}

			$_finfo = $is_top ? $_LIST_PROP_FLAGS["info"] : null;
			
			$caption = $_LIST_PROP_FLAGS["display.caption"] ?: \QModel::GetCaption($heading, $src_from_types, $h_prop->name, $config["__view__"]);

			// @setup serach headings
			$heading_search_data = ($h_prop && $h_prop->name && $config["_GRID_CFG_"] && $config["_GRID_CFG_"]["_Q_SET_SEARCH_"] && 
				($_sfd = $config["_GRID_CFG_"]["_Q_SET_SEARCH_"][$h_prop->name])) ? $_sfd["_QS_FIELD_"] : null;

			// quick search data
			$heading_qsearch_data = ($h_prop && $h_prop->name && $config["_GRID_Q_CFG_"] && $config["_GRID_Q_CFG_"]["_Q_SET_SEARCH_"] && 
				($_sfd = $config["_GRID_Q_CFG_"]["_Q_SET_SEARCH_"][$h_prop->name])) ? $_sfd["_QS_FIELD_"] : null;

			//qvardump("\$heading_search_data", $heading_search_data);

			$xg_prop_tag = $h_prop ? static::GetXGTag($h_prop->name, $parent_model, $read_only) : null;

			if ($config["order_by_info"] && $is_top && (($heading != "<i class=\"fa fa-th-large m-left-10\"></i>") || ($heading != "#") || ($heading != "Actions")))
			{
				$heading_order = null;
				foreach ($config["order_by_info"] as $prop => $data)
				{
					if ((!$data["bind_path"]) && is_array($data))
					{
						foreach ($data as $k => $v)
						{
							if ((count($data) > 1) && ($k == "Id"))
								continue;
							$bind_path = $v["bind_path"];
						}
					}
					else
						$bind_path = $data["bind_path"];

					if ($prop == $heading)
						$heading_order = is_array($bind_path) ? q_reset($bind_path) : $bind_path;
				}
			}

			$_headings_data[] = [$xg_prop_tag, $caption, "", $heading_order, $h_prop, $_finfo, $heading_search_data, $heading_qsearch_data, $_LIST_PROP_FLAGS];
		}

		$esc_include_method = htmlspecialchars("render".ucfirst($include_method));

		$dd_basic_path = $basic_path."Row";
		$dd_include_method = substr($dd_basic_path, strrpos($dd_basic_path, ".") + 1);

		$add_render = htmlspecialchars("render".ucfirst($dd_include_method));

		$chkCollCustomQ = null;
		$chkCollFromProp = null;
		$chkCollSelector = null;
		$chkCollBinds = null;

		if ($_coll_uses_chks)
		{
			$selector_has_properties = static::SelectorHasProperties($selector);
			if (!$selector_has_properties)
				$_headings_data[] = ["", "#", "", ""];

			$chkCollFromProp = static::GetAppPropertyFor($src_from_types, $parent_model, $property);
			if (!$chkCollFromProp || (!($chkCollFrom = $chkCollFromProp->name)))
			{
				# qvar_dumpk($selector_has_properties, $selector);
				# throw new \Exception("Cannot determine from for collection with checkboxes!");
			}

			$chkCollCustomQ = $_PROP_FLAGS["coll.checkboxCustomQuery"];

			$chkCollBinds = $_PROP_FLAGS["coll.checkboxBinds"];

			$chkCollSelector = $_PROP_FLAGS["coll.checkboxSelector"];

			if (!$chkCollSelector)
				$chkCollSelector = $_is_subpart ? qImplodeEntity($selector) : static::GetCaptionSelectorFor($src_from_types, $parent_model, $property);
			if (!$chkCollSelector)
				$chkCollSelector = "Id";
			$chkCollSelector = qaddslashes($chkCollSelector);
		}

		if ($is_top)
		{
			$inner_path =  $basic_path."Inner.tpl";
			$_inner_tpl_file = pathinfo($inner_path, PATHINFO_FILENAME);
			$_inner_tpl = end(explode(".", $_inner_tpl_file));
		}

		// used in templates
		$_no_default_row = !$_PROP_FLAGS["coll.hasDefaultRow"];

		$template = $is_top ? ($read_only ? "list/list.tpl" : "bulk/bulk.tpl") : 
			($_coll_uses_chks ? "collections/collection_with_checkboxes.tpl" : 
				($_is_subpart ? "collections/collection_subpart.tpl" : "collections/collection_with_reference.tpl"));

		/*
		Variables used in templates:
	
		$xg_tag
		$search_str
		$property
		$_headings_data
		$dd_include_method
		$add_render
		$chkCollFromProp
		$_no_default_row
		$chkCollSelector
		$chkCollBinds
		$read_only
		*/

		ob_start();
		$md5_seed__ = $basic_path;
		require(static::GetTemplate($template, $config));
		unset($md5_seed__);
		$str = ob_get_clean();
		filePutContentsIfChanged($basic_path.".tpl", $str);

		if ($is_top)
		{
			$has_bulk_edit_props = false;
			if (false && (!$read_only && (count($bulk_edit_props) > 0)))
			{
				$propsPath = $basic_path."EditProps.tpl";
				ob_start();
				$md5_seed__ = $propsPath;
				require(static::GetTemplate("bulk/bulk_edit_props.tpl", $config));
				unset($md5_seed__);
				$str = ob_get_clean();
				filePutContentsIfChanged($propsPath, $str);
				$has_bulk_edit_props = true;
				$bulk_method = substr($basic_path, strrpos($basic_path, ".") + 1) . "EditProps";
			}
			
			ob_start();
			$md5_seed__ = $inner_path;
			require(static::GetTemplate($read_only ? "list/list_inner.tpl" : "bulk/bulk_inner.tpl", $config));
			unset($md5_seed__);
			$str = ob_get_clean();
			filePutContentsIfChanged($inner_path, $str);
			
			//qvardump($noResultsPath);
			ob_start();
			$md5_seed__ = $inner_path;
			require(static::GetTemplate("list/list_no_results.tpl", $config));
			unset($md5_seed__);
			$str = ob_get_clean();
			filePutContentsIfChanged($noResultsPath . ".tpl", $str);
		}
		
		

		// we need to setup sub blocks
		$renderedSubBlocks = "";
		if ($subBlocks && (count($subBlocks) > 0))
		{
			foreach ($subBlocks as $subBlock)
				$renderedSubBlocks .= $subBlock;
		}

		/*
		Variables used in template

		$esc_include_method
		$include_method
		$renderedSubBlocks
		*/

		ob_start();
		$md5_seed__ = $dd_basic_path;
		require(static::GetTemplate("rows_group.tpl", $config));
		unset($md5_seed__);
		$str = ob_get_clean();

		filePutContentsIfChanged($dd_basic_path.".tpl", $str);
	}
	/**
	 * @param type $config
	 * @param type $parent_model
	 * @param type $basic_path
	 * @param type $src_from_types
	 * @param type $selector
	 * @param type $list_mode
	 * @param type $is_top
	 * @param type $useSelectedData
	 * @param type $output_additional
	 * @return type
	 */
	public static function GenerateHiddens($config, $parent_model, $basic_path, $src_from_types = null, $selector = null, 
		$list_mode = false, $is_top = false, $useSelectedData = false, $output_additional = true)
	{
		/*
		Variables used in template

		$useSelectedData
		$parent_model
		$list_mode
		*/

		ob_start();
		require(static::GetTemplate("hiddens.tpl", $config));
		return ob_get_clean();
	}

	public static function GenerateCollectionRowi($config)
	{
		// No variables are used in template
		ob_start();
		require(static::GetTemplate("rowi.tpl", $config));
		return ob_get_clean();
	}
	/**
	 * @param type $config
	 * @param type $property
	 * @param type $parent_model
	 * @param type $basic_path
	 * @param type $src_from_types
	 * @param type $selector
	 * @param type $list_mode
	 * @param type $is_top
	 * @return type
	 */
	public static function GenerateGridProperty($config, $path, $basic_path, $list_mode = false, $is_top = false)
	{
		$property = $config["property"];
		$parent_model = $config["parent_model"];
		$src_from_types = $config["src_from_types"];
		$selector = $config["selector"];
		
		$all_props_are_tracked = true;
		// get read only from config
		$read_only = $config["__readonly"];

		// unset selector * from query
		if ($selector && is_array($selector) && ($selector["*"] !== null))
			unset($selector["*"]);
		// if in bulk mode we will not descend deeper, we will just link the proper renders to form elements
		$sub_blocks = [];
		// $parent_model may also be an array !

		$_PROP_FLAGS = static::GetPropertyFlags($config, $path, $list_mode, $read_only);
		// get type flags
		$_TYPE_FLAGS = $config['cfg_type_flags'];
		
		if (isset($config["__callback__"]) && is_callable($config["__callback__"]))
		{
			$config["__callback__"]('property', $config, $path, $basic_path, $list_mode, $is_top, $_PROP_FLAGS, $_TYPE_FLAGS);
		}
		
		// if in bulk edit mode and the property is not set to be editable in bulk mode - return
		if ($config["__bulk_edit_props__"] && (!$_PROP_FLAGS["display.bulk"]))
			return "";

		$__iscollection = $_PROP_FLAGS["__is_collection"];
		$__isreference = $_PROP_FLAGS["__is_reference"];
		$__isscalar = $_PROP_FLAGS["__is_scalar"];
		
		$__is_pure_reference = $_PROP_FLAGS["__is_pure_reference"];
		$__is_pure_scalar = $_PROP_FLAGS["__is_pure_scalar"];

		$blockWhenRecord = $_PROP_FLAGS["block.whenRecord"];
		$blockWhenData = $_PROP_FLAGS["block.whenData"];
		$forceBlock = $_PROP_FLAGS["block"];

		// set to read only
		$readonly = $_PROP_FLAGS["readonly"];

		if ($readonly && (!$config["__readonly"]))
			$config["__readonly"] = true;
		
		$read_only = $config["__readonly"];

		$xg_tag = static::GetXGTag($property, $parent_model, $read_only, $list_mode);

		$selector_has_properties = static::SelectorHasProperties($selector);
	
		$propCaption = ($_PROP_FLAGS["display.caption"] ?:
			\QModel::GetCaption($property, ($parent_model && is_array($parent_model)) ? q_reset($parent_model) : $parent_model, $property, $config["__view__"]));

		$propIsMandatory = $_PROP_FLAGS["mandatory"];

		$validationAlert = $_PROP_FLAGS['validation.alert'];
		$placeholder = $_PROP_FLAGS["display.placeholder"];

		$info = $_PROP_FLAGS["info"];

		$avoidDuplicates = $_PROP_FLAGS["coll.avoidDuplicates"];
		$avoid_duplicates_markup = static::GetAvoidDuplicatesMarkup($avoidDuplicates);
		$qc_avoid_duplicates_cls = (strlen($avoid_duplicates_markup) > 0) ? " ".$avoid_duplicates_markup : "";

		$_is_subpart = $_PROP_FLAGS["struct.subpart"];
		$_displayIndividual = $_PROP_FLAGS["display.individual"];
		$_isIndividualCollection = ($__iscollection && $_displayIndividual);

		$noLabel = $_PROP_FLAGS["display.noLabel"];
		$hideLabel = $_PROP_FLAGS["display.hideLabel"];

		$__addSublock = false;
		$__field_content = "";
		$__field_content_readonly = null;

		$__expand = false;

		// 1. PROPERTY IS REFERENCE
		
		$_rf_hiddens = "";
		
		$_default = $_PROP_FLAGS["default"];
		# fixing the vars path
		{

			if ($config['inside_custom_group'])
			{
				$input_name_path = "";
				$name_parts = explode(".", $path);
				$input_name_path = $name_parts[0];
				if (count($name_parts) > 1)
					$input_name_path .= "[".implode("][", array_slice($name_parts, 1))."]";

				$_data_property = "'".$input_name_path."'";
				$mark_pos = strrpos($input_name_path, "[");
				$_data_property_id_ = "'". (($mark_pos !== false) ? substr($input_name_path, 0, $mark_pos).'[Id]' : 'Id') ."'";

				$input_value_path = str_replace(".", "->", $path);
				$last_mark = strrpos($input_value_path, "->");
				$input_id_value_path = ($last_mark !== false) ? (substr($input_value_path, 0, $last_mark) . "->Id") : "Id";

				$_data_value = ($blockWhenData || $blockWhenRecord) ? 
					"\$this->data->{$input_value_path}" :
					"((\$this->data->{$input_value_path} !== null) ? \$this->data->{$input_value_path} : ".(isset($_default) ? "'{$_default}'" : "''").")";
				$_data_value_raw = "\$this->data->{$input_value_path}";
				$input_value_path_parent = implode("->", array_slice(explode(".", $path), 0, -1));
				$_data_value_parent = $input_value_path_parent ? "\$this->data->{$input_value_path_parent}" : "\$this->data";

				$_data_value_id_ = ($blockWhenData || $blockWhenRecord) ? 
					"\$this->data->{$input_id_value_path}" :
					"((\$this->data->{$input_id_value_path} !== null) ? \$this->data->{$input_id_value_path} : '')";
			}
			else
			{
				$prop_parts = explode(".", $property);
				if (count($prop_parts) > 1)
				{
					$input_name_path = $prop_parts[0]."[".implode("][", array_slice($prop_parts, 1))."]";
					$input_value_path = str_replace(".", "->", $property);
				}
				else
				{
					$input_name_path = $property;
					$input_value_path = $property;
				}
				
				$_data_property = "\$vars_path ? \$vars_path.'[{$input_name_path}]' : '{$input_name_path}'";
				$_data_property_id_ = "\$vars_path ? \$vars_path.'[Id]' : 'Id'";

				$_data_value = ($blockWhenData || $blockWhenRecord) ? 
					"\$data->{$input_value_path}" :
					"((\$data->{$input_value_path} !== null) ? \$data->{$input_value_path} : ".(isset($_default) ? "'{$_default}'" : "''").")";
				$_data_value_raw = "\$data->{$input_value_path}";
				$_data_value_parent = "\$data";

				$_data_value_id_ = ($blockWhenData || $blockWhenRecord) ? 
					"\$data->Id" :
					"((\$data->Id !== null) ? \$data->Id : '')";
			}
		}
				
		if ($__is_pure_reference)
		{
			// use dropdown only if not subpart - otherwise just expand property
			$_use_dropdown = !$_is_subpart;
			
			$_has_controller = $_PROP_FLAGS["dd.controller"];
			
			// we need to perform the expand only if subpart
			//$__expand = ($_is_subpart && $selector_has_properties);
			$__expand = ($_is_subpart || $selector_has_properties);
			
			if ($__expand)
			{
				if (!$config)
					$config = [];

				if ($config['_ref_expanded_'])
					$config['_in_expanded_ref_'] = true;

				$config['_ref_expanded_'] = true;

				$prop_basic_path = $basic_path . ucfirst($property);
				
				$grid_form_cfg = $config;
				
				# throw new \Exception('We need to decide when we UNSET inside_custom_group for refs');
				/*
				unset($grid_form_cfg['inside_custom_group']);
				unset($grid_form_cfg['inside_custom_group_layout']);
				unset($grid_form_cfg['inside_custom_group_layout_width']);
				*/
				
				$grid_form_cfg["property"] = $property;
				$grid_form_cfg["parent_model"] = $parent_model;
				$grid_form_cfg["src_from_types"] = $_PROP_FLAGS["ref.types"];
				$grid_form_cfg["selector"] = $selector;

				list ($all_props_are_tracked_sub) = static::GenerateGridForm($grid_form_cfg, $path, $prop_basic_path, false, $list_mode);
				if (!$all_props_are_tracked_sub)
					$all_props_are_tracked = false;

				$include_method = substr($prop_basic_path, strrpos($prop_basic_path, ".") + 1);
				$esc_include_method = htmlspecialchars("render" . ucfirst($include_method));

				//if ($list_mode)
				//	$__addSublock = true;
			}
			
			$_ddToLoad = $_PROP_FLAGS["dd.loadClass"];
			$esc_dd_property = $_PROP_FLAGS["dd.property"];
			$esc_caption_selector = $_PROP_FLAGS["dd.captionSelector"];
			$binds = $_PROP_FLAGS["dd.binds"];
			//$_has_controller = $_PROP_FLAGS["dd.controller"];
			$attrs = $_PROP_FLAGS["dd.attrs"];
			$_view_to_load = $_PROP_FLAGS["dd.viewToLoad"];
			
			if (!$list_mode && !$__expand && !$esc_dd_property)
			{
				qvar_dumpk("AAA", $parent_model, $property, $__expand);
				throw new \Exception("Property not linked to app #AAA");
			}
			
			// readonly
			{
				/*
				Variables used in template: 

				$property
				$include_method
				*/

				$__expand_reference = ($__expand && (!($list_mode && $is_top)));

				if (!$__expand_reference && $__expand)
					$__addSublock = true;
				
				# Setup $Extra_Selectors if we have extra data to grab for showing FKs (ex: drop down caption)
				if ($_PROP_FLAGS['dd.captionSelector'] && (! (($__expand_reference && (!$_use_dropdown)))))
				{
					# dd.captionSelector[string(23)]: "Person.{Name,Firstname}"
					$tmp_check_path_sel = qParseEntity($path.".{{$_PROP_FLAGS['dd.captionSelector']}}");

					$tmp_check_path_sel = qSelector_Remove_Ids($tmp_check_path_sel);
					
					$tmp_check_app_selector = $list_mode ? (\QApp::NewData())::GetListEntity_Final($config["__view__"]) : (\QApp::NewData())::GetFormEntity_Final($config["__view__"]);
					$tmp_check_missing_props = qSelectorsMissing($tmp_check_app_selector, $tmp_check_path_sel);
					
					if ($tmp_check_missing_props)
					{
						if (static::$Extra_Selectors[$config["__view__"]][$config["__listmode"] ? 'list' : 'form'])
						{
							static::$Extra_Selectors[$config["__view__"]][$config["__listmode"] ? 'list' : 'form'] = 
									qJoinSelectors(static::$Extra_Selectors[$config["__view__"]][$config["__listmode"] ? 'list' : 'form'], $tmp_check_missing_props);
						}
						else
							static::$Extra_Selectors[$config["__view__"]][$config["__listmode"] ? 'list' : 'form'] = $tmp_check_missing_props;
						
						# we throw an explicit exception
						/**
						$tmp_check_func_name = $list_mode ? "App::GetListEntity_Final('{$config["__view__"]}')" : 
																"App::GetFormEntity_Final('{$config["__view__"]}')";
						throw new \Exception("Missing in `{$tmp_check_func_name}` elements [needed for reference `{$path}`]: ".qImplodeEntity($tmp_check_missing_props)." | Caption selector: ".$_PROP_FLAGS['dd.captionSelector']);
						 * 
						 */
					}
				}

				ob_start();
				require(static::GetTemplate(($__expand_reference && (!$_use_dropdown)) ? "reference/reference_subpart.tpl" : 
							($list_mode ? "reference/reference_list_view.tpl" : "reference/reference_form_view.tpl"), $config)) ;
				$__field_content_readonly .= ob_get_clean();
			}
			
			if (!$read_only)
			{
				if ($_use_dropdown)
				{
					$_view_to_load = $_PROP_FLAGS["dd.viewToLoad"];
					$_dd_insert_full_data = $_PROP_FLAGS["dd.insert_full_data"];
					$_default = $_PROP_FLAGS["default"];
					/*
					Variables used in template: 

					$property
					$blockWhenData
					$blockWhenRecord
					$__iscollection
					$_is_subpart
					$_has_controller
					$_ddToLoad
					$esc_dd_property
					$esc_caption_selector
					$binds
					$attrs
					$_view_to_load
					*/
					
					ob_start();
					if ($config['inside_custom_group'])
						require(static::GetTemplate("reference/reference_dropdown_cg.tpl", $config));
					else
						require(static::GetTemplate("reference/reference_dropdown.tpl", $config));
					$__field_content .= ob_get_clean();
					
				}
				else
				{

					/*
					Variables used in template: 
					$property
					$include_method
					*/
					

					ob_start();
					require(static::GetTemplate($__expand ? "reference/reference_subpart.tpl" : "reference/reference_list_view.tpl", $config));
					$__field_content .= ob_get_clean();
				}
			}
			else
			{
				$__field_content = $__field_content_readonly;
			}
			// if in readonly we don't care if we have dropdown or not!
		}

		// 2. PROPERTY IS COLLECTION

		if ($__iscollection)
		{
			// get property basic path
			$prop_basic_path = $basic_path.ucfirst($property);

			// populate new config
			$grid_list_cfg = $config;
			
			# for collections we always unset this !
			unset($grid_list_cfg['inside_custom_group']);
			unset($grid_list_cfg['inside_custom_group_layout']);
			unset($grid_list_cfg['inside_custom_group_layout_width']);
			
			$grid_list_cfg["property"] = $property;
			$grid_list_cfg["parent_model"] = $parent_model;
			$grid_list_cfg["src_from_types"] = $_PROP_FLAGS["coll.types"];
			$grid_list_cfg["selector"] = $selector;

			// generate grid list
			static::GenerateGridList($grid_list_cfg, $path, $prop_basic_path);

			// set include method
			$include_method = substr($prop_basic_path, strrpos($prop_basic_path, ".") + 1);

			if ($list_mode)
			{
				$mc = $_PROP_FLAGS["display.caption"] ?: \QModel::GetCaption($property, $parent_model, $property, $config["__view__"]);
				$_model_caption = true;
				// 
				/*
				Variables used in template: 

				$property
				$mc
				*/

				ob_start();
				require(static::GetTemplate("expand.tpl", $config));
				$__field_content .= ob_get_clean();
				$__addSublock = true;
				unset($_model_caption);
			}
			else
			{
				/*
				Variables used in template: 

				$property
				$include_method
				*/
				ob_start();
				require(static::GetTemplate("collections/collection_expand.tpl", $config));
				$__field_content .= ob_get_clean();
			}
			
			$__field_content_readonly = $__field_content;
		}

		// 3. PROPERTY IS SCALAR
		if ($__is_pure_scalar)
		{
			$_is_mandatory = $_PROP_FLAGS["mandatory"];
			$_is_bool = $_PROP_FLAGS["type.bool"]; 
			$_is_password = $_PROP_FLAGS["type.password"];
			$_is_file = $_PROP_FLAGS["type.file"]; 
			$_is_string = $_PROP_FLAGS["type.string"];
			$_is_textarea = $_PROP_FLAGS["display.textarea"];
			$_is_date = $_PROP_FLAGS["type.date"];
			$_date_format = $_PROP_FLAGS["date.format"];
			$_is_enum = $_PROP_FLAGS["type.enum"];
			$_is_set = $_PROP_FLAGS["type.set"];
			$_enum_vals = $_PROP_FLAGS["enum.vals"]; 
			$_set_vals = $_PROP_FLAGS["set.vals"]; 
			$_enum_captions = $_PROP_FLAGS["enum.captions"];
			$_enum_styles = $_PROP_FLAGS["enum.styles"];
			$_enum_display = $_PROP_FLAGS["enum.display"];
			$_q_valid = $_PROP_FLAGS["validation"]; 
			$_q_fix = $_PROP_FLAGS["fix"]; 
			$_q_info = $_PROP_FLAGS["info"]; 
			$_extra_attrs = $_PROP_FLAGS["display.attrs"];

			$useEditor = $_PROP_FLAGS['display.editor'];
			$useDatepicker = $_PROP_FLAGS['display.datepicker'];
			$_default = $_PROP_FLAGS["default"];
					
			// if ($read_only)
			{

				/*
				Variables used in template: 

				$property
				$_is_date
				$_date_format
				$_is_bool
				*/
				
				ob_start();
				require(static::GetTemplate("scalar/scalar_view.tpl", $config));
				$__field_content_readonly .= ob_get_clean();
			}
			
			if (!$read_only)
			{
				$__field_content .= static::GenerateEditableScalar($config, $path, $xg_tag, $list_mode, $read_only, $is_top, md5($basic_path."\nedit\nscalar\n".$property));
			}
			else
			{
				$__field_content = $__field_content_readonly;
			}
			
		}

		if ($__addSublock)
		{
			$_hide_sub_block_caption = true;

			/*
			Variables used in template: 

			$property
			$_hide_sub_block_caption
			$include_method
			*/

			ob_start();
			require(static::GetTemplate("sub_block.tpl", $config));
			$sub_blocks[$property] = ob_get_clean();
		}
		
		if (is_string($_PROP_FLAGS['readonly_IF']) && (strlen($_PROP_FLAGS['readonly_IF']) > 0))
		{
			$__field_content = 
					"\n@if ({$_PROP_FLAGS['readonly_IF']})\n".
							$__field_content_readonly."\n".
						"@else\n".
							$__field_content."\n".
						"@endif\n";
		}

		$str = "";
		if (!$list_mode)
		{
			$removeLabel = ($noLabel || $_displayIndividual);
			
			/*
			Variables used in template: 
			$property
			$xg_tag
			$noLabel
			$__field_content
			$__field_content_readonly
			*/

			if ($_PROP_FLAGS["display.forceSection"])
			{
				ob_start();
				require(static::GetTemplate("section/section.tpl", $config));
				$str = ob_get_clean();
			}
			else
			{
				ob_start();
				require(static::GetTemplate("property/property_form.tpl", $config));
				$str = ob_get_clean();
			}
		}
		else
		{
			$str = $__field_content;
		}
				
		if (is_string($_PROP_FLAGS['render_IF']) && (strlen($_PROP_FLAGS['render_IF']) > 0))
		{
			$str = "\n"."@if (".$_PROP_FLAGS['render_IF'].")\n".
							$str.
							"\n@endif\n";
		}

		return [$str, $sub_blocks, ($selector_has_properties || $__iscollection), $__expand, $all_props_are_tracked];
	}
	/**
	 * Generates editable scalar: input, textarea, file etc.
	 * 
	 * @param type $property
	 * @param type $types
	 * @param type $parent_model
	 * @param type $storage
	 * @param type $xg_tag
	 * @return string
	 */
	public static function GenerateEditableScalar($config, $path, $xg_tag = null, $list_mode = false, $read_only = false, $is_top = false, $md5_seed__ = null)
	{
		$property = $config["property"];
		$parent_model = $config["parent_model"];
		// get first type: string, boolean, datetime, etc...

		// get property flags
		$_PROP_FLAGS = static::GetPropertyFlags($config, $path, $list_mode, $read_only);
		// get type flags
		$_TYPE_FLAGS = $config['cfg_type_flags'];

		$blockWhenRecord = $_PROP_FLAGS["block.whenRecord"];
		$blockWhenData = $_PROP_FLAGS["block.whenData"];
		$forceBlock = $_PROP_FLAGS["block"];
		
		if ($config['inside_custom_group'])
		{
			$input_name_path = "";
			$name_parts = explode(".", $path);
			$input_name_path = $name_parts[0];
			if (count($name_parts) > 1)
				$input_name_path .= "[".implode("][", array_slice($name_parts, 1))."]";
			
			$_data_property = "'".$input_name_path."'";
			$mark_pos = strrpos($input_name_path, "[");
			$_data_property_id_ = "'". (($mark_pos !== false) ? substr($input_name_path, 0, $mark_pos).'[Id]' : 'Id') ."'";
		}
		else
		{
			$_data_property = "\$vars_path ? \$vars_path.'[{$property}]' : '{$property}'";
			$_data_property_id_ = "\$vars_path ? \$vars_path.'[Id]' : 'Id'";
		}

		$_field_style = $_PROP_FLAGS["display.style"];

		$_is_mandatory = $_PROP_FLAGS["mandatory"];
		$_is_bool = $_PROP_FLAGS["type.bool"]; 
		$_is_password = $_PROP_FLAGS["type.password"];
		$_is_file = $_PROP_FLAGS["type.file"]; 
		$_is_string = $_PROP_FLAGS["type.string"];
		$_is_textarea = $_PROP_FLAGS["display.textarea"];
		$_is_date = $_PROP_FLAGS["type.date"];
		$_date_format = $_PROP_FLAGS["date.format"];
		$_is_enum = $_PROP_FLAGS["type.enum"];
		$_is_set = $_PROP_FLAGS["type.set"];
		$_enum_vals = $_PROP_FLAGS["enum.vals"];
		$_set_vals = $_PROP_FLAGS["set.vals"];
		$_enum_captions = $_PROP_FLAGS["enum.captions"];
		$_enum_styles = $_PROP_FLAGS["enum.styles"];
		$_enum_display = $_PROP_FLAGS["enum.display"];
		$_q_valid = $_PROP_FLAGS["validation"]; 
		$_q_fix = $_PROP_FLAGS["fix"]; 
		$_q_info = $_PROP_FLAGS["info"]; 
		$_extra_attrs = $_PROP_FLAGS["display.attrs"];
		$_default = $_PROP_FLAGS["default"];
		
		$isCustomDD = ((count($_enum_vals ?? []) > 2) || ($_enum_display && ($_enum_display == "dropdown")));

		$validationAlert = $_PROP_FLAGS['validation.alert'];

		$useEditor = $_PROP_FLAGS['display.editor'];
		$useDatepicker = $_PROP_FLAGS['display.datepicker'];

		$placeholder = $_PROP_FLAGS["display.placeholder"];

		if ($config['inside_custom_group'])
		{
			$input_value_path = str_replace(".", "->", $path);
			$last_mark = strrpos($input_value_path, "->");
			$input_id_value_path = ($last_mark !== false) ? (substr($input_value_path, 0, $last_mark) . "->Id") : "Id";
			
			$_data_value = ($blockWhenData || $blockWhenRecord) ? 
				"\$this->data->{$input_value_path}" :
				"((\$this->data->{$input_value_path} !== null) ? \$this->data->{$input_value_path} : ".(isset($_default) ? "'{$_default}'" : "''").")";
				
			$_data_value_raw = "\$this->data->{$input_value_path}";
				
			$input_value_path_parent = implode("->", array_slice(explode(".", $path), 0, -1));
			$_data_value_parent = $input_value_path_parent ? "\$this->data->{$input_value_path_parent}" : "\$this->data";
				
			$_data_value_id_ = ($blockWhenData || $blockWhenRecord) ? 
				"\$this->data->{$input_id_value_path}" :
				"((\$this->data->{$input_id_value_path} !== null) ? \$this->data->{$input_id_value_path} : '')";
		}
		else
		{
			$_data_value = ($blockWhenData || $blockWhenRecord) ? 
				"\$data->{$property}" :
				"((\$data->{$property} !== null) ? \$data->{$property} : ".(isset($_default) ? "'{$_default}'" : "''").")";
				
			$_data_value_raw = "\$data->{$property}";
				
			$_data_value_parent = "\$data";
				
			$_data_value_id_ = ($blockWhenData || $blockWhenRecord) ? 
				"\$data->Id" :
				"((\$data->Id !== null) ? \$data->Id : '')";
		}
			
		$_date_show_val = ($blockWhenData || $blockWhenRecord) ? 
			($_date_format ? "{$_data_value} ? date('{$_date_format}', strtotime({$_data_value})) : ''" : "{$_data_value}") :
			($_date_format ? 
				"({$_data_value} ? date('{$_date_format}', strtotime({$_data_value})) : ".
					(isset($_default) ? "date('{$_date_format}', strtotime('{$_default}'))" : "''").")" :
				"({$_data_value} ? {$_data_value} : ".(isset($_default) ? "'{$_default}'" : "''").")");

		$_data_value_caption = ($blockWhenData || $blockWhenRecord) ? 
			"{$_data_value}" :
			"(({$_data_value} !== null) ? {$_data_value} : ".(isset($_default) ? "'{$_default}'" : ($isCustomDD ? "'Select'" : "''")).")";

		$_cust_dd_value = ($blockWhenData || $blockWhenRecord) ? 
			($isCustomDD ? ("{$_data_value} ?: " . (isset($_default) ? "'{$_default}'" : "''")) : "{$_data_value}") :
			"(({$_data_value} !== null) ? {$_data_value} : " . (isset($_default) ? "'{$_default}'" : 
					($isCustomDD ? "''" : "''")).")";
			
		$_cust_dd_value_caption = ($blockWhenData || $blockWhenRecord) ? 
			($isCustomDD ? ("{$_data_value} ?: " . (isset($_default) ? "'{$_default}'" : "'Select'")) : "{$_data_value}") :
			"(({$_data_value} !== null) ? {$_data_value} : " . (isset($_default) ? "'{$_default}'" : 
					($isCustomDD ? "'Select'" : "''")).")";
			
		/*
		Variables used in template:

		$property
		$blockWhenData
		$_extra_attrs
		$blockWhenRecord
		$_data_value
		$_data_property
		$xg_tag
		$_is_bool
		$_is_date
		$_is_textarea
		$_is_enum
		$_is_password
		$_enum_vals
		$_set_vals
		$_enum_captions
		$_field_style
		*/
		
		ob_start();
		// to show that we are using it here !!!
		$md5_seed__ = $md5_seed__;
		require(static::GetTemplate("scalar/scalar_edit.tpl", $config));
		return ob_get_clean();
	}

	/**
	 * 
	 * @param type $config
	 * @param type $headings_data
	 * @param type $property
	 * @param type $parent_model
	 * @param type $basic_path
	 * @param type $src_from_types
	 * @param array $selector
	 * @param type $is_top
	 * @param type $tabs
	 * @param type $on_tab
	 * @param type $vars_path
	 * @param type $vars_post_path
	 * @param type $vars_relative
	 * @param type $vars_map
	 * @return type
	 */
	public static function GenerateGridListForm($config, $path, $basic_path, $is_top = false)
	{
		$headings_data = $config["headings_data"];
		$property = $config["property"];
		$parent_model = $config["parent_model"];
		$src_from_types = $config["src_from_types"];
		$selector = $config["selector"];
		
		if ($is_top && isset($config["cfg"]["::"]['@list-selector']))
		{
			$selector = is_string($config["cfg"]["::"]['@list-selector']) ? qParseEntity($config["cfg"]["::"]['@list-selector']) : $config["cfg"]["::"]['@list-selector'];
		}

		// unset selecto * from query
		if ($selector && is_array($selector) && ($selector["*"] !== null))
			unset($selector["*"]);

		// get read only from config
		$read_only = $config["__readonly"] ?: false;

		list($headings, $headings_rates, $heading_rates_total, $heading_props) = $headings_data;


		$_PROP_FLAGS = static::GetPropertyFlags($config, $path, true, $read_only);
		// get type flags
		$_TYPE_FLAGS = $config['cfg_type_flags'];
	
		$xg_tag = static::GetXGTag($property, $parent_model, $read_only);

		// is collection - true
		$__iscollection = true;
		$isOneToMany = $_PROP_FLAGS["coll.oneToMany"];
		$blockWhenRecord = $_PROP_FLAGS["block.whenRecord"];
		$blockWhenData = $_PROP_FLAGS["block.whenData"];
		$forceBlock = $_PROP_FLAGS["block"];
		$_is_scalar = $_PROP_FLAGS["__is_scalar"];
		$_is_subpart = $_PROP_FLAGS["struct.subpart"];

		$avoidDuplicates = $_PROP_FLAGS["coll.avoidDuplicates"];
		
		$propIsMandatory = $_PROP_FLAGS["mandatory"];
		$validationAlert = $_PROP_FLAGS['validation.alert'];
		

		// determine if checkbox collection
		$_coll_uses_chks = $_PROP_FLAGS["coll.checkboxes"];
		$_use_chks = (!$read_only && ($is_top || $_coll_uses_chks));

		$hiddens = "";
		
		if (!$_is_scalar && ($_is_subpart || $_use_chks))
			$hiddens .= static::GenerateHiddens($config, $parent_model, $basic_path, $src_from_types, $selector, true, $is_top, $_coll_uses_chks, false);
		
		$hiddens .= static::GenerateCollectionRowi($config);

		if (!$src_from_types)
		{
			$prop_reflection = static::GetTypeByName($parent_model)->properties[$property];
			$src_from_types = $prop_reflection->hasCollectionType() ? $prop_reflection->getCollectionType()->getAllInstantiableReferenceTypes() : $prop_reflection->getAllInstantiableReferenceTypes();
		}

		if ($selector === null)
			$selector = [];

		$sub_blocks = [];
		$sub_parent_model = $src_from_types;

		$caption_entity = ($is_top && $read_only) ? qParseEntity(static::GetCaptionSelectorFor($src_from_types)) : null;				
		
		//qvardump("\$caption_entity", $caption_entity, $src_from_types);

		$properties = [];
		$has_caption_property = false;
		
		$edit_props = [];
		foreach ($selector as $prop => $sub_selector)
		{
			$property_cfg = $config;
			$property_cfg["property"] = $prop;
			$property_cfg["parent_model"] = $src_from_types;
			$property_cfg["selector"] = $sub_selector;
			$property_cfg["src_from_types"] = null;
			$prop_path = ($path ? rtrim($path, ".") . "." : "") . $prop;

			//$_LIST_PROP_FLAGS = static::GetPropertyFlags($property_cfg, $prop_path, false, $read_only);
			$str_property = null;
			$property_sub_blocks = null;

			$_C_PROP_FLAGS = static::GetPropertyFlags($property_cfg, $prop, true, $read_only);
			
			if ($_C_PROP_FLAGS['no_render'])
			{
				# we do not render
			}
			else
			{
				list($str_property, $property_sub_blocks) = static::GenerateGridProperty($property_cfg, $prop_path, $basic_path, true, $is_top);
			}
			
			if (!$str_property)
				continue;
			
			$setup_view_link = false;
			if ($prop && $caption_entity && isset($caption_entity[$prop]))
			{
				$setup_view_link = true;
				$has_caption_property = true;
			}

			$xg_prop_tag = static::GetXGTag($prop, $sub_parent_model, $read_only);

			// if is bulk we may need to render again the property
			//$_LIST_PROP_FLAGS = static::GetPropertyFlags($property_cfg, $prop_path, false, $read_only);
			$apply_translate = false;
			$tmp_PROP_FLAGS = static::GetPropertyFlags($property_cfg, $prop_path, $basic_path, true, $is_top);
			if ($tmp_PROP_FLAGS && $tmp_PROP_FLAGS['type.enum'])
			{
				$apply_translate = true;
				# qvar_dump($str_property);
			}
			
			$properties[] = [$xg_prop_tag, $str_property, $setup_view_link, $prop, $apply_translate, $tmp_PROP_FLAGS];

			if (!$read_only && $is_top)
			{

				$_pro = $property_cfg["__readonly"];
				$property_cfg["__bulk_edit_props__"] = true;

				list($edit_prop, $edit_prop_sublocks)  = static::GenerateGridProperty($property_cfg, $prop_path, $basic_path, true, $is_top);

				unset($property_cfg["__bulk_edit_props__"]);
				$property_cfg["__readonly"] = $_pro;


				$edit_props[] = [$xg_prop_tag, $edit_prop];
			}

			foreach ($property_sub_blocks as $_tp => $sb)
				$sub_blocks[$_tp] = $sb;
		}

		if ($is_top && $read_only && !$has_caption_property && (count($properties) > 0))
		{
			$p_first = array_shift($properties);
			array_pop($p_first);
			$p_first[] = true;
			array_unshift($properties, $p_first);
		}

		if (!$_is_subpart)
		{
			$avoid_duplicates_markup = static::GetAvoidDuplicatesMarkup($avoidDuplicates);
			$qc_avoid_duplicates_cls = (strlen($avoid_duplicates_markup) > 0) ? " ".$avoid_duplicates_markup : "";

			if (!$_is_scalar)
			{
				$_ddToLoad = $_PROP_FLAGS["dd.loadClass"];
				$esc_dd_property = $_PROP_FLAGS["dd.property"];
				$esc_caption_selector = $_PROP_FLAGS["dd.captionSelector"];
				$binds = $_PROP_FLAGS["dd.binds"];
				$_has_controller = $_PROP_FLAGS["dd.controller"];
				$attrs = $_PROP_FLAGS["dd.attrs"];
				$_view_to_load = $_PROP_FLAGS["dd.viewToLoad"];

				$selector_has_properties = static::SelectorHasProperties($selector);
				$__expand = ($_is_subpart || $selector_has_properties);
				
				if ($_PROP_FLAGS['dd.captionSelector'] && (!$__expand))
				{
					# dd.captionSelector[string(23)]: "Person.{Name,Firstname}"
					$tmp_check_path_sel = qParseEntity($path.".{{$_PROP_FLAGS['dd.captionSelector']}}");

					$tmp_check_path_sel = qSelector_Remove_Ids($tmp_check_path_sel);
					
					$list_mode = $config['__list'];

					$tmp_check_app_selector = $list_mode ? \QApp::NewData()::GetListEntity_Final($config["__view__"]) : \QApp::NewData()::GetFormEntity_Final($config["__view__"]);
					$tmp_check_missing_props = qSelectorsMissing($tmp_check_app_selector, $tmp_check_path_sel);
					
					if ($tmp_check_missing_props)
					{
						if (static::$Extra_Selectors[$config["__view__"]][$list_mode ? 'list' : 'form'])
							static::$Extra_Selectors[$config["__view__"]][$list_mode ? 'list' : 'form'] = 
									qJoinSelectors(static::$Extra_Selectors[$config["__view__"]][$list_mode ? 'list' : 'form'], $tmp_check_missing_props);
						else
							static::$Extra_Selectors[$config["__view__"]][$list_mode ? 'list' : 'form'] = $tmp_check_missing_props;

						# we throw an explicit exception
						/**
						$tmp_check_func_name = $list_mode ? "App::GetListEntity_Final('{$config["__view__"]}')" : 
																"App::GetFormEntity_Final('{$config["__view__"]}')";
						throw new \Exception("Missing in `{$tmp_check_func_name}` elements [needed for reference `{$path}`]: ".qImplodeEntity($tmp_check_missing_props)." | Caption selector: ".$_PROP_FLAGS['dd.captionSelector']);
						 * 
						 */
					}
				}
				
				if (!$esc_dd_property) # && (!$__expand))
				{
					qvardump("Property not linked to app #BBB", $parent_model, $property, get_defined_vars());
					# throw new \Exception("Property not linked to app #BBB");
				}

				$xg_prop_tag = $xg_tag;
			}
		}

		if ($_coll_uses_chks)
		{
			$selector_has_properties = static::SelectorHasProperties($selector);
			if (!$selector_has_properties)
				$properties[] = [static::GetXGTag($property, $parent_model, $read_only), "{{\$data->getModelCaption()}}"];
		}

		if ($_is_scalar)
		{
			$xg_prop_tag = $xg_tag;

			$_data_value = "\$data";
			
			# @TODO - this is not ok !!! ... but will have to do atm to `escape` the error
			$_data_value_parent = "\$data";
			
			$_data_property = "\$vars_path ? \$vars_path.\"\" : \"\"";
			if ($config['inside_custom_group'])
			{
				$input_name_path = "";
				$name_parts = explode(".", $path);
				$input_name_path = $name_parts[0];
				if (count($name_parts) > 1)
					$input_name_path .= "[".implode("][", array_slice($name_parts, 1))."]";

				$_data_property = "'".$input_name_path."'";
			}

			$_is_password = $_PROP_FLAGS["type.password"];
			$_is_file = $_PROP_FLAGS["type.file"];
			$_isBool = false;
		}

		/*
		Variables used in templates:

		$xg_tag
		$vars_post_path
		$src_from_types
		$selector
		$hiddens
		$properties
		$read_only
		$xg_prop_tag
		$isOneToMany
		*/		
		
		$template = $is_top ? ($read_only ? "list/list_row.tpl" : "bulk/bulk_row.tpl") : 
			($_use_chks ? "collections/collection_with_checkboxes_row.tpl" : ($_is_scalar ? "collections/collection_scalar_row.tpl" : 
				(!$_is_subpart ? "collections/collection_with_reference_row.tpl" : "collections/collection_subpart_row.tpl")));

		ob_start();
		$md5_seed__ = $file_name;
		require(static::GetTemplate($template, $config));
		unset($md5_seed__);
		$str = ob_get_clean();

		$file_name = $basic_path.".tpl";
		filePutContentsIfChanged($file_name, $str);
		return [$sub_blocks, $edit_props];
	}
	
	/**
	 * Generates a form 
	 * 
	 * @param type $config
	 * @param type $property
	 * @param type $parent_model
	 * @param type $basic_path
	 * @param type $src_from_types
	 * @param type $selector
	 * @param type $is_top
	 * @param type $tabs
	 * @param type $on_tab
	 * @param type $vars_path
	 * @param type $vars_post_path
	 * @param type $vars_relative
	 * @param type $vars_map
	 * @return type
	 */
	public static function GenerateGridForm($config, $path, $basic_path, $is_top = false, $list_mode = false, $search_data = [])
	{
		list($search_str, $qsearch_str) = $search_data;
		$property = $config["property"];
		$parent_model = $config["parent_model"];
		$src_from_types = $config["src_from_types"];
		$selector = $config["selector"];
		
		$viewCaption = $config["Caption"] ?: \QModel::GetCaption($config["Title"], $src_from_types, null, $config["__view__"]);
		$addCaption = $config["Caption_Add"];
		$editCaption = $config["Caption_Edit"];
		$viewModeCaption = $config["Caption_View"];
		$deleteCaption = $config["Caption_Delete"];
		
		if (defined('Q_GENERATE_GRID_CAPTION_REPLACE_UNDERSCORE') && Q_GENERATE_GRID_CAPTION_REPLACE_UNDERSCORE)
		{
			$viewCaption = preg_replace('/[\\_\\s]+/', " ", $viewCaption);
			$addCaption = preg_replace('/[\\_\\s]+/', " ", $addCaption);
			$editCaption = preg_replace('/[\\_\\s]+/', " ", $editCaption);
			$viewModeCaption = preg_replace('/[\\_\\s]+/', " ", $viewModeCaption);
			$deleteCaption = preg_replace('/[\\_\\s]+/', " ", $deleteCaption);
		}
		
		$has_custom_layout = false;
		if ($config["cfg"]["::"]['@boxes'])
		{
			foreach ($config["cfg"]["::"]['@boxes'] as $cfg_box_data)
			{
				$for_current_item = substr(basename($basic_path), strlen($config["__view__"]) + 1);
                
				if ((!$cfg_box_data["for"]) || in_array($for_current_item, $cfg_box_data["for"]))
				{
					$has_custom_layout = true;
				}
			}
		}
		
		$read_only = $config["__readonly"] ?: false;

		// unset selector * from query
		if ($selector && is_array($selector) && ($selector["*"] !== null))
			unset($selector["*"]);

		$xg_tag = static::GetXGTag($property, $parent_model, false, false);
		
		// get props flags
		$_PROP_FLAGS = static::GetPropertyFlags($config, $path, $list_mode, $read_only);
		// get type flags
		$_TYPE_FLAGS = $config['cfg_type_flags'];
		
		// when not on top - get src from types
		if (!$src_from_types)
		{
			//qvardump($_PROP_FLAGS);
			throw new \Exception("blocked!");
			$prop_reflection = \QModel::GetTypeByName($parent_model)->properties[$property];
			$src_from_types = $prop_reflection->hasCollectionType() ? $prop_reflection->getCollectionType()->getAllInstantiableReferenceTypes() : $prop_reflection->getAllInstantiableReferenceTypes();
		}

		if ($selector === null)
		{
			//$selector = $parent_model::GetPropertyModelEntity($property);
			//return;
			$selector = [];
		}

		// init tabs and tabs content
		$tabs_str = "";

		//$prop_type_m = \QModel::GetTypeByName(is_array($src_from_types) ? reset($src_from_types) : $src_from_types);

		$_is_scalar = $_PROP_FLAGS["__is_scalar"];

		$isReference = $_PROP_FLAGS["__is_reference"];
		$isPureReference = $_PROP_FLAGS["__is_pure_reference"];

		$hiddens = (!$selector["Id"] && !$_is_scalar) ? 
			static::GenerateHiddens($config, $src_from_types, $basic_path, null, [], false, $is_top, false, !$is_top) : "";

		$tabs = [];
		$tabsContents = [];
		
		$generalTab = null;
		$generalTabIdf = "tab" . md5("tab\n" . "general");
		
		$config_groups = $config['cfg']['::']['@groups'];
		
		$added_place_holders = [];
		
		$place_holders_after = [];
		$place_holders_before = [];
		
		$tracked_props_list = [];
		$all_props_are_tracked = true;
		$sub_properties_tracked = [];
		
		foreach ($selector as $prop => $sub_selector)
		{	
			$property_config = $config;
			$property_config["property"] = $prop;
			$property_config["parent_model"] = $src_from_types;
			$property_config["selector"] = $sub_selector;
			$property_config["src_from_types"] = null;

			$prop_path = ($path ? rtrim($path, ".") . "." : "") . $prop;
			
			$track_property_content = false;
			$inside_custom_group = false;
			$inside_custom_group_layout = false;
			
			if ($config_groups)
			{
				foreach ($config_groups as $cg_name => $cg_data)
				{
					if ($cg_data[$prop_path] === 'before')
					{
						$ph = '<!-- '.sha1(json_encode(['before', $cg_name, $config["__readonly"], $prop_path])).' -->';
						$place_holders_before[$prop][] = $ph;
						static::$Place_Holders[$config["__view__"]][$ph][$config['__readonly'] ? 'view' : 'form'][$cg_name] = $cg_data;
						$added_place_holders[$ph] = $ph;
					}
					else if ($cg_data[$prop_path] === 'after')
					{
						$ph = '<!-- '.sha1(json_encode(['after', $cg_name, $config["__readonly"], $prop_path])).' -->';
						$place_holders_after[$prop][] = $ph;
						static::$Place_Holders[$config["__view__"]][$ph][$config['__readonly'] ? 'view' : 'form'][$cg_name] = $cg_data;
						$added_place_holders[$ph] = $ph;
					}
					
					if ($cg_data['@select'][$prop_path])
					{
						$track_property_content = true;
						$tracked_props_list[$prop] = $prop;
						$inside_custom_group = true;
						$inside_custom_group_layout = $cg_data["@layout"] ? true : false;
						$inside_custom_group_layout_width = $cg_data["@layout-width"] ?: false;
					}
				}
			}
			
			$str_property = null; $sub_blocks = null; $selector_has_properties = null; $_expanded = null;
			
			$_C_PROP_FLAGS = static::GetPropertyFlags($property_config, $prop, true, $read_only);
			if ($_C_PROP_FLAGS['no_render'])
			{
				# 
			}
			else
			{
				$property_config['inside_custom_group'] = $inside_custom_group;
				$property_config['inside_custom_group_layout'] = $inside_custom_group_layout;
				$property_config['inside_custom_group_layout_width'] = $inside_custom_group_layout_width;
				// generate grid property
				list($str_property, $sub_blocks, $selector_has_properties, $_expanded, $all_props_are_tracked_sub_inf) = 
					static::GenerateGridProperty($property_config, $prop_path, $basic_path, false, $is_top);
				
				if (!$all_props_are_tracked_sub_inf)
					$all_props_are_tracked = false;
				$sub_properties_tracked[$prop] = $all_props_are_tracked_sub_inf;
			}
			
			$place_holders_before_str = "";
			$place_holders_after_str = "";
			
			foreach ($place_holders_before[$prop] ?: [] as $placeholder_before)
				$place_holders_before_str .= $placeholder_before."\n";
			foreach ($place_holders_after[$prop] ?: [] as $placeholder_after)
				$place_holders_after_str .= $placeholder_after."\n";
			
			if ($track_property_content)
			{
				static::$Place_Holders_Content[$config["__view__"]][$prop_path][$config['__readonly'] ? 'view' : 'form'] = $str_property;
				$str_property = "";
			}
			
			if ($place_holders_before_str || $place_holders_after_str)
				$str_property = $place_holders_before_str . $str_property . $place_holders_after_str;
			
			if (!$str_property)
				continue;
			
			// prop 
			$_FORM_PROP_FLAGS = static::GetPropertyFlags($property_config, $prop_path, $list_mode, $read_only);

			// check if is top and first add the general tab if not created
			if ($is_top)
			{
				// on other tab
				$onOtherTab = $has_custom_layout ? false : $_FORM_PROP_FLAGS["display.individual"];

				// on other tab custom
				$onOtherTab_Custom = $has_custom_layout ? false : $_FORM_PROP_FLAGS["display.tab"];

				$isGeneral = false;
				$tabSelected = false;
				$tabProps = null;

				// display individual - creates tab - only on top level for now
				if ($onOtherTab)
				{
					// if on other tab it means that is an expanded reference or an expanded collection
					// in both cases we will have by default a section for this
					$tab_identifier = "tab" . md5("\n" . $prop);
					$tabCaption = $_FORM_PROP_FLAGS["display.caption"] ?: \QModel::GetCaption($prop, $src_from_types, $prop, $config["__view__"]);
					$tabProperty = $prop;
					$tabProps = $_FORM_PROP_FLAGS["tab.props"];
				}
				// on other tab custom - only on top level for now
				else if ($onOtherTab_Custom)
				{
					$tab_identifier = preg_replace("/\W|_/", "", $onOtherTab_Custom);
					$tabProps = $config["__tabs__"][$tab_identifier];
					$tabCaption = $tabProps["caption"] ?: $onOtherTab_Custom;
					$tabProperty = $tab_identifier;
				}

				// general tab - this is the general tab
				else
				{
					$tab_identifier = $generalTabIdf;
					$tabCaption = "Main";
					$tabProperty = "general";
					$tabSelected = true;
					$isGeneral = true;
				}	
			}
			else
			{
				$tab_identifier = "tab" . md5($prop . "\n" . $path);
				$tabCaption = $path;
				$tabProperty = $path;
			}

			if (!$isGeneral)
			{
				// save tab
				$tabs[$tab_identifier] = [$tab_identifier, $tabCaption, $tabSelected, $tabProperty, $tabProps, $_FORM_PROP_FLAGS, $prop, $prop_path];
			}
			else
				$generalTab = [$tab_identifier, $tabCaption, $tabSelected, $tabProperty, $tabProps, $_FORM_PROP_FLAGS, $prop, $prop_path];


			// setup main tab at beginning
			if (!isset($tabsContents[$tab_identifier]))
			{
				if ($isGeneral)
					$tabsContents = array_merge([$tab_identifier => []], $tabsContents);
				else
					$tabsContents[$tab_identifier] = [];
			}

			// setup sections and other data
			$useSection = $_FORM_PROP_FLAGS["display.section"];
			$onSection = ($useSection || $_FORM_PROP_FLAGS["display.forceSection"]);
			$onSubSection = $_FORM_PROP_FLAGS["display.subSection"];

			if ($onSection || $onSubSection)
			{
				$sectionProps = $useSection ? $config["__sections__"][$useSection]  : null;

				if ($onSubSection)
				{
					if (!$onSection && (!$sectionProps || !$sectionProps["parent"]))
					{
						//qvardump($prop_obj_m->storage, $config);
						throw new \Exception("Not linked to section!");
					}
				}

				if (!isset($tabsContents[$tab_identifier]["sections"]))
					$tabsContents[$tab_identifier]["sections"] = [];

				$section_indx = $useSection ? preg_replace("/\W|_/", "", $useSection) : "section-{$prop}";

				if (!isset($tabsContents[$tab_identifier]["sections"][$section_indx]))
				{
					if (!$sectionProps)
						$sectionProps = [];
					
					if ($_FORM_PROP_FLAGS["display.forceSection"])
					{
						if (!$sectionProps["caption"])
							$sectionProps["caption"] = $_FORM_PROP_FLAGS["display.caption"];

						if (!$_FORM_PROP_FLAGS["section.props"])
							$_FORM_PROP_FLAGS["section.props"] = [];
						$sectionProps = array_replace_recursive($sectionProps, $_FORM_PROP_FLAGS["section.props"]);
					}

					// section prop
					$sectionProps["prop"] = $prop;

					$tabsContents[$tab_identifier]["sections"][$section_indx] = ["section" => $sectionProps];
				}

				if ($onSubSection)
				{
					if (!isset($tabsContents[$tab_identifier]["sections"][$section_indx]["sections"]))
						$tabsContents[$tab_identifier]["sections"][$section_indx]["sections"] = [];

					$subSectionProps = $onSubSection ? $config["__sub_sections__"][$onSubSection]  : null;
					$sub_section_indx = preg_replace("/\W|_/", "", $onSubSection);

					if (!isset($tabsContents[$tab_identifier]["sections"][$section_indx]["sections"][$sub_section_indx]))
					{
						$tabsContents[$tab_identifier]["sections"][$section_indx]["sections"][$sub_section_indx] = 
							["section" => ($subSectionProps ?: null)];
					}

					if (!isset($tabsContents[$tab_identifier]["sections"][$section_indx]["sections"][$sub_section_indx]["properties"]))
						$tabsContents[$tab_identifier]["sections"][$section_indx]["sections"][$sub_section_indx]["properties"] = [];
					$tabsContents[$tab_identifier]["sections"][$section_indx]["sections"][$sub_section_indx]["properties"][] = $str_property;
				}
				else
				{
					if (!$_FORM_PROP_FLAGS["display.forceSection"])
					{
						if (!isset($tabsContents[$tab_identifier]["sections"][$section_indx]["properties"]))
							$tabsContents[$tab_identifier]["sections"][$section_indx]["properties"] = [];
						$tabsContents[$tab_identifier]["sections"][$section_indx]["properties"][] = $str_property;
					}
					else
					{
						if (!isset($tabsContents[$tab_identifier]["sections"][$section_indx]["data"]))
							$tabsContents[$tab_identifier]["sections"][$section_indx]["data"] = [];
						$tabsContents[$tab_identifier]["sections"][$section_indx]["data"][] = $str_property;
						$tabsContents[$tab_identifier]["sections"][$section_indx]["prop"] = $prop;
					}

				}
			}
			else
			{
				if (!isset($tabsContents[$tab_identifier]["properties"]))
					$tabsContents[$tab_identifier]["properties"] = [];
				$tabsContents[$tab_identifier]["properties"][] = $str_property;
			}
		}
		
		if ($tracked_props_list && $all_props_are_tracked)
		{
			ksort($tracked_props_list);
			$selector_copy = $selector;
			ksort($selector_copy);
			$all_props_are_tracked = (array_keys($tracked_props_list) === array_keys($selector_copy));
		}
		
		// setup general tab - we need to make sure that general tab is always first
		if ($generalTab)
		{
			$tabs = array_merge([$generalTabIdf => $generalTab], $tabs);
		}

		$hasMultipleTabs = ($tabs && (count($tabs) > 1));
		// go through tabs and display the contnet
		# qvar_dumpk('xxxxx', [$config, $has_custom_layout, array_keys($tabsContents), $tabsContents]);
		
		if ($has_custom_layout)
		{
			$first_placeholder_was_set = false;
			$tabsContents = [key($tabsContents) => q_reset($tabsContents)];
			
			$tabs = [key($tabs) => q_reset($tabs)];
			
			$tabs_count = count($tabsContents);
			
			foreach ($tabsContents as &$tc)
			{
				if (isset($tc['sections']) && (($tabs_count > 1) || isset($tc['properties'])))
					unset($tc['sections']);
				if ($first_placeholder_was_set)
				{
					if (isset($tc['properties']))
						unset($tc['properties']);
				}
				else
				{
					$first_placeholder_was_set = true;
					
					# make sure we only keep the first one !
					$tc['properties'] = isset($tc['properties']) ? array_slice($tc['properties'], 0, 1) : [];
				}
			}
			
			# qvar_dumpk('$is_top && $hasMultipleTabs', $is_top , $hasMultipleTabs);
		}
		
		foreach ($tabsContents ?: [] as $tab_indx => $tab_content)
		{
			if (empty($tabs[$tab_indx]))
				continue;
			
			list($tab_for, $tab_caption, $tab_active, $tab_property, $tab_props, $_TAB_PROP_FLAGS, $for_prop_name, $for_prop_path) = $tabs[$tab_indx];
			
			$tab_str_to_add = "";
			
			if (is_array($tab_content))
			{
				$tab_id = preg_replace("/\W|_/", "", $tab_indx);
				ob_start();
				require(static::GetTemplate(($is_top && $hasMultipleTabs) ? "form/form_tab_content.tpl" : "form/form_tab_content_inner.tpl", $config));
				$str = ob_get_clean();
				$tab_str_to_add = $str;
			}
			else
				$tab_str_to_add = $tab_content;
			
			if (is_string($_TAB_PROP_FLAGS['render_IF']) && (strlen($_TAB_PROP_FLAGS['render_IF']) > 0))
			{
				$tabs_str .= "\n"."@if (".$_TAB_PROP_FLAGS['render_IF'].")\n".
								$tab_str_to_add.
								"\n@endif\n";
			}
			else
			{
				$tabs_str .= $tab_str_to_add;
			}
		}

		$template = $is_top ? ($hasMultipleTabs ? "form/form_top_with_tabs.tpl" : "form/form_top.tpl") : "form/form.tpl";

		/*
		Variables used in templates:

		$xg_tag
		$vars_post_path
		$src_from_types
		$selector
		$hiddens
		$tabs_str
		$search_str
		$tabs
		$property
		$_byStickyProps
		*/
		
		ob_start();
		require(static::GetTemplate($template, $config));
		$str = ob_get_clean();

		$file_name = $basic_path.".tpl";
		//qvardump("QWEQWEQ", $str, $file_name, $template, \QModel::GetCaption($config["Title"], $src_from_types, null, $config["__view__"]));
		
		foreach ($added_place_holders as $ph)
			static::$Place_Holders_Paths[$config["__view__"]][$ph] = $file_name;
		
		filePutContentsIfChanged($file_name, $str);
		
		return [$all_props_are_tracked];
	}

	/**
	 * 
	 * @param type $config
	 * @param type $path
	 * @param type $list_mode
	 * @param type $read_only
	 * @return type
	 * @throws \Exception
	 */
	public static function GetPropertyFlags($config, $path, $list_mode = false, $read_only = false, $in_search = false)
	{
		$prop_chunks = preg_split("/(\\s*\\.\\s*)/uis", trim($config["property"]), -1, PREG_SPLIT_NO_EMPTY);
		
		if (isset($prop_chunks[1]))
		{
			$parent_model = is_array($config["parent_model"]) ? reset($config["parent_model"]) : $config["parent_model"];
			for ($i = 0; $i < (count($prop_chunks) - 1); $i++)
			{
				$c_property = $prop_chunks[$i];
				$parent_model = \QApi::DetermineFromTypes(is_array($parent_model) ? reset($parent_model) : $parent_model, $c_property);
			}
			
			$config['property'] = end($prop_chunks);
			$config['parent_model'] = $parent_model;
		}

		$mode = $list_mode ? ($read_only ? "list" : "bulk") : ($read_only ? "view" : "form");

		$model = is_array($config["parent_model"]) ? q_reset($config["parent_model"]) : $config["parent_model"];
		$prop = $config["property"];

		// property identifier
		$prop_idf = $config["__view__"] . "~" . $model . "~" . $prop. "~" . $path . "~" . 
			($list_mode ? "1" : "0") . "~" . ($in_search ? "1" : "0") . "~" . ($read_only ? "1" : "0");

		if (isset(static::$CachedData[$prop_idf]))
			return static::$CachedData[$prop_idf];

		static::$CachedData[$prop_idf] = [];
		
		$mandatory = $in_search ? false : static::ExtractExtraConfig($config["cfg"], $path ?: "", "@mandatory");
		$validation = $in_search ? false : static::ExtractExtraConfig($config["cfg"], $path ?: "", "@validation");
		$fix = $in_search ? false : static::ExtractExtraConfig($config["cfg"], $path ?: "", "@fix");		
		
		$selectorHasProps = static::SelectorHasProperties($config["selector"]);

		// mix property data		
		$mixed_data = \QModel::MixPropertyData($model, $prop, $config["__view__"], $mandatory, $validation, $fix, $in_search);
		$mixed_data = $mixed_data['types'];

		// determine property type (collection, reference, scalar)
			// we first need to know if it is a collection - if it is a collection it is a collection of scalars or references
		static::$CachedData[$prop_idf]["__is_collection"] = $mixed_data["[]"] ? true : false;
		
		foreach (static::Config_Property_Get_Defs() ?: [] as $config_definition_key => $config_definition)
		{
			$value = static::ExtractExtraConfig($config["cfg"], $path ?: "", "@{$config_definition_key}");
			if ($value === null)
				$value = $mixed_data[$config_definition_key];
			if ($value === null)
				$value = $mixed_data['cfg'][$config_definition_key];
			if ($value !== null)
				static::$CachedData[$prop_idf][$config_definition_key] = $value;
		}

		// is scalar
		static::$CachedData[$prop_idf]["__is_pure_scalar"] = $mixed_data["\$"] ? true : false;
		static::$CachedData[$prop_idf]["__is_scalar"] = static::$CachedData[$prop_idf]["__is_pure_scalar"] ? true : 
			((static::$CachedData[$prop_idf]["__is_collection"] && static::IsScalarProperty($mixed_data)));

		// is reference
		static::$CachedData[$prop_idf]["__is_pure_reference"] = $mixed_data["#"] ? true : false;
		static::$CachedData[$prop_idf]["__is_reference"] = static::$CachedData[$prop_idf]["__is_pure_reference"] ? true : 
			(static::$CachedData[$prop_idf]["__is_collection"] && !static::$CachedData[$prop_idf]["__is_scalar"]);

		// get property views
		static::$CachedData[$prop_idf]["display.views"] = static::ExtractExtraConfig($config["cfg"], $path ?: "", "@views") ?: 
			(($mixed_data["storage.full"]["views"]) ? explode(",", $mixed_data["storage.full"]["views"]) : []);

		// setup caption
		$captions = ($mixed_data["storage.full"] && $mixed_data["storage.full"]["captions"]) ? 
			json_decode($mixed_data["storage.full"]["captions"], true) : null;

		static::$CachedData[$prop_idf]["display.caption"] = 
			static::ExtractExtraConfig($config["cfg"], $path ?: "", "@caption") ??
			(($captions && $captions[$config["__view__"]]) ? $captions[$config["__view__"]] : $prop);
		
		if (defined('Q_GENERATE_GRID_CAPTION_REPLACE_UNDERSCORE') && Q_GENERATE_GRID_CAPTION_REPLACE_UNDERSCORE)
			static::$CachedData[$prop_idf]["display.caption"] = preg_replace('/[\\_\\s]+/', " ", static::$CachedData[$prop_idf]["display.caption"]);

		if (!($fixedCaption = static::ExtractExtraConfig($config["cfg"], $path ?: "", "@fixedCaption")))
		{
			$tmp_display_caption = preg_replace_callback('/(?<!\b)[A-Z][a-z]+|(?<=[a-z])[A-Z]/', function($match) {
				return ' '. $match[0];
			}, static::$CachedData[$prop_idf]["display.caption"]);
			
			$tmp_display_caption = preg_replace('/[\\_\\s]+/', " ", $tmp_display_caption);
			
			static::$CachedData[$prop_idf]["display.caption"] = $tmp_display_caption;
		}
		
		// flags for subpart and individual display type
		static::$CachedData[$prop_idf]["options.pool"] = 
				static::ExtractExtraConfig($config["cfg"], $path ?: "", "@storage.optionsPool") ?? $mixed_data["storage.full"]["optionsPool"];
		
		static::$CachedData[$prop_idf]["struct.subpart"] = 
				((static::ExtractExtraConfig($config["cfg"], $path ?: "", "@storage.dependency") ?? ($mixed_data["dependency"] ?? 
						((defined('Q_MODEL_SUBPART_IS_DEFAULT') && Q_MODEL_SUBPART_IS_DEFAULT && (!static::$CachedData[$prop_idf]["options.pool"])) ? 'subpart' : null))) === 'subpart');
				# (($struct_subpart_ = static::ExtractExtraConfig($config["cfg"], $path ?: "", "@storage.dependency")) === 'subpart') ? $struct_subpart_  :
				# ($mixed_data && $mixed_data["dependency"] && ($mixed_data["dependency"] === "subpart"));
		
		if (static::$CachedData[$prop_idf]["struct.subpart"] === null)
		{
			if ($config['selector'] && (count($config['selector']) > 0))
				static::$CachedData[$prop_idf]["struct.subpart"] = true;
		}
		
		static::$CachedData[$prop_idf]["display.individual"] = 
				
			((static::ExtractExtraConfig($config["cfg"], $path ?: "", "@display.individual") ?? ($mixed_data["display"]["individual"] ?? null)));
			/*(($displayIndividual = static::ExtractExtraConfig($config["cfg"], $path ?: "", "@display.individual")) !== null) ? $displayIndividual  :
			($mixed_data && $mixed_data["display"] && $mixed_data["display"]["individual"]);*/

		// tab
		$tabs = ($mixed_data && $mixed_data["storage.full"] && 
			$mixed_data["storage.full"]["displayOnTab"]) ? json_decode($mixed_data["storage.full"]["displayOnTab"], true) : null;

		static::$CachedData[$prop_idf]["display.tab"] = 
			static::ExtractExtraConfig($config["cfg"], $path ?: "", "@tab") ?:
			($tabs ? ($tabs[$config["__view__"]] ?: $tabs['general']) : null);

		// section
		$sections = ($mixed_data && $mixed_data["storage.full"] && 
			$mixed_data["storage.full"]["displayOnSection"]) ? json_decode($mixed_data["storage.full"]["displayOnSection"], true) : null;

		
		static::$CachedData[$prop_idf]["display.section"] = 
			(static::ExtractExtraConfig($config["cfg"], $path ?: "", "@section") ?:
			($sections ? ($sections[$config["__view__"]] ?: $sections['general']) : null));
		

		//sub section
		$subSections = ($mixed_data && $mixed_data["storage.full"] && 
			$mixed_data["storage.full"]["displayOnSection"]) ? json_decode($mixed_data["storage.full"]["displayOnSubSection"], true) : null;

		static::$CachedData[$prop_idf]["display.subSection"] = 
			static::ExtractExtraConfig($config["cfg"], $path ?: "", "@subSection") ?:
			($subSections ? ($subSections[$config["__view__"]] ?: $subSections['general']) : null);

		static::$CachedData[$prop_idf]["section.props"] = static::ExtractExtraConfig($config["cfg"], $path ?: "", "@sectionProps");
		static::$CachedData[$prop_idf]["tab.props"] = static::ExtractExtraConfig($config["cfg"], $path ?: "", "@tabProps");
		
		static::$CachedData[$prop_idf]["display.bulk"] = (static::ExtractExtraConfig($config["cfg"], $path ?: "", "@bulk.property") ?: $mixed_data["bulk_property"]);

		// readonly
		static::$CachedData[$prop_idf]["readonly"] = static::ExtractExtraConfig($config["cfg"], $path ?: "", "@readonly") ?: isset($mixed_data["admin.readonly"]);		
	
		$data_full_path = $config['from'].($path ? ".".$path : "");
		// readonly IF
		static::$CachedData[$prop_idf]["readonly_IF"] = static::ExtractExtraConfig($config["cfg"], $path ?: "", "@readonly_if") ?? $mixed_data['storage.full']["admin.readonly_IF"];
		if (static::$CachedData[$prop_idf]["readonly_IF"])
			static::$CachedData[$prop_idf]["readonly_IF"] .= " || (!q_security_new('{$data_full_path}', \$grid_mode ?: 'edit', null, static::\$FromAlias))";
		else
			static::$CachedData[$prop_idf]["readonly_IF"] = "(!q_security_new('{$data_full_path}', \$grid_mode ?: 'edit', null, static::\$FromAlias))";
		
		// no_render
		static::$CachedData[$prop_idf]["no_render"] = static::ExtractExtraConfig($config["cfg"], $path ?: "", "@no_render") ?: ($mixed_data['storage.full']["admin.no_render"] ? true : false);

		// readonly IF
		static::$CachedData[$prop_idf]["render_IF"] = static::ExtractExtraConfig($config["cfg"], $path ?: "", "@render_if") ?: $mixed_data['storage.full']["admin.render_IF"];
		if (static::$CachedData[$prop_idf]["render_IF"])
			static::$CachedData[$prop_idf]["render_IF"] .= " && q_security_new('{$data_full_path}', 'view', null, static::\$FromAlias)";
		else
			static::$CachedData[$prop_idf]["render_IF"] = "q_security_new('{$data_full_path}', 'view', null, static::\$FromAlias)";
		
		
		//readonly
		static::$CachedData[$prop_idf]["default"] = static::ExtractExtraConfig($config["cfg"], $path ?: "", "@default") ?? 
					$mixed_data["default"] ?? $mixed_data["storage.full"]["admin.default"];

		// mandatory 
		static::$CachedData[$prop_idf]["mandatory"] = ($mandatory !== null) ? $mandatory : $mixed_data["mandatory"];

		// validation
		static::$CachedData[$prop_idf]["validation"] = $validation ?: $mixed_data["js_validation"];
		static::$CachedData[$prop_idf]["validation.alert"] = static::ExtractExtraConfig($config["cfg"], $path ?: "", "@validation.alert") ?: $mixed_data["validation_alert"];
		static::$CachedData[$prop_idf]["validation.info"] = static::ExtractExtraConfig($config["cfg"], $path ?: "", "@validation.info") ?: $mixed_data["validation_info"];

		// fixers
		static::$CachedData[$prop_idf]["fix"] = $fix ?: $mixed_data["js_fix"];

		// info
		$info = static::ExtractExtraConfig($config["cfg"], $path ?: "", "@info") ?: 
			(($mixed_data && $mixed_data["storage.full"] && $mixed_data["storage.full"]["info"]) ? $mixed_data["storage.full"]["info"] : null);

		$validation_info = static::$CachedData[$prop_idf]["validation_info"] ?: null;

		// info on property
		static::$CachedData[$prop_idf]["info"] = ($info || $validation_info) ? ($info ?: "") . ($validation_info ? $validation_info : "") : null;

		// manage property block
		static::$CachedData[$prop_idf]["block"] = 
			(
				(($blockIn = static::ExtractExtraConfig($config["cfg"], $path ?: "", "@block.in")) && isset($blockIn[$mode])) || 
				((($mixed_data["display"] && $mixed_data["display"]["block"]) ? explode(", ", $mixed_data["display"]["block"]) : null))
			);

		// manage fields blocking
		$_display_props = ($mixed_data["display"] && $mixed_data["display"]["properties"]) ? $mixed_data["display"]["properties"] : null;
		if ($_display_props && is_string($_display_props))
			$_display_props = [$_display_props];

		// block when record
		static::$CachedData[$prop_idf]["block.whenRecord"] = 
			(static::ExtractExtraConfig($config["cfg"], $path ?: "", "@block.whenRecord") ||
			($_display_props && in_array("blocked_when_record", $_display_props)));

		static::$CachedData[$prop_idf]["block.whenData"] = (!static::$CachedData[$prop_idf]["block.when_record"] && 
			(
				static::ExtractExtraConfig($config["cfg"], $path ?: "", "@block.whenData") ||
				($_display_props && in_array("blocked_when_data", $_display_props))
			));

		$style = static::ExtractExtraConfig($config["cfg"], $path ?: "", "@style") ?:
			((($mixed_data && $mixed_data['storage.full'] && $mixed_data['storage.full']['field.style']) && 
				($ed = json_decode($mixed_data['storage.full']['field.style'], true))) ? ($ed[$config["__view__"]] ?: $ed["general"]) : null);

		if ($style)
		{
			if (!is_scalar($style))
			{
				$_field_style = "";
				$_fsp = 0;
				foreach ($style ?: [] as $_k => $_v)
				{
					$_field_style .= (($_fsp > 0) ? " " : "").$_k . ": " . $_v . ";";
					$_fsp++;
				}
				static::$CachedData[$prop_idf]["display.style"] = (strlen($_field_style) > 0) ? $_field_style : null;
			}
			else
				static::$CachedData[$prop_idf]["display.style"] = $style;
		}

		// force label hide
		static::$CachedData[$prop_idf]["display.hideLabel"] = 
			(static::ExtractExtraConfig($config["cfg"], $path ?: "", "@display.hideLabel") || 
			($mixed_data && $mixed_data["display"] && isset($mixed_data["display"]["hideLabel"])));

		// force label hide
		static::$CachedData[$prop_idf]["display.hideLabel"] = 
			(static::ExtractExtraConfig($config["cfg"], $path ?: "", "@display.hideLabel") || 
			($mixed_data && $mixed_data["display"] && isset($mixed_data["display"]["hideLabel"])));
		
		static::$CachedData[$prop_idf]["display.placeholder"] = 
			(static::ExtractExtraConfig($config["cfg"], $path ?: "", "@display.placeholder") ?:
			($mixed_data && $mixed_data["display"] && isset($mixed_data["display"]["placeholder"])));
		
		//qvardump("placeholder", $prop, static::$CachedData[$prop_idf]["display.placeholder"]);

		if (static::$CachedData[$prop_idf]["display.placeholder"] && is_bool(static::$CachedData[$prop_idf]["display.placeholder"]))
			static::$CachedData[$prop_idf]["display.placeholder"] = static::$CachedData[$prop_idf]["display.caption"];

		$attrs = static::ExtractExtraConfig($config["cfg"], $path ?: "", "@attrs") ?:
			(($mixed_data["storage.full"] && $mixed_data["storage.full"]["attrs"] && ($ed = json_decode($mixed_data["storage.full"]["attrs"], true))) ? 
			($ed[$config["__view__"]] ?: $ed["general"]) : null);
		
		if ($attrs)
		{
			if (!is_scalar($attrs))
			{
				$attrs_data = "";
				foreach ($attrs ?: [] as $_attr => $_attr_val)
					$attrs_data .= " ".($_attr."=\"{$_attr_val}\"");
				static::$CachedData[$prop_idf]["display.attrs"] = $attrs_data;
			}
			else
				static::$CachedData[$prop_idf]["display.attrs"] = $attrs;
		}

		/*
		$cssClasses = static::ExtractExtraConfig($config["cfg"], $path ?: "", "@cssClasses") ?:
			(($mixed_data["storage.full"] && $mixed_data["storage.full"]["attrs"] && ($ed = json_decode($mixed_data["storage.full"]["classes"], true))) ? 
			($ed[$config["__view__"]] ?: $ed["general"]) : null);
		
		if ($cssClasses)
		{
			if (!is_scalar($cssClasses))
			{
				$classes = "";
				foreach ($cssClasses ?: [] as $cls)
					$classes .= " ". $cls;
				static::$CachedData[$prop_idf]["display.cssClasses"] = $classes;
			}
			else
				static::$CachedData[$prop_idf]["display.cssClasses"] = $cssClasses;
		}
		*/
		
		foreach (['list', 'form', 'view'] as $css_class_mode)
		{
			static::$CachedData[$prop_idf]["display.{$css_class_mode}-css-classes"] = 
				static::ExtractExtraConfig($config["cfg"], $path ?: "", "@{$css_class_mode}-css-classes");
		}
		
		foreach (['list', 'form', 'view'] as $css_class_mode)
		{
			static::$CachedData[$prop_idf]["display.{$css_class_mode}-css-row-before"] = 
				static::ExtractExtraConfig($config["cfg"], $path ?: "", "@{$css_class_mode}-css-row-before");
				
			static::$CachedData[$prop_idf]["display.{$css_class_mode}-css-row-after"] = 
				static::ExtractExtraConfig($config["cfg"], $path ?: "", "@{$css_class_mode}-css-row-after");
		}
		
		// property is scalar
		if (static::$CachedData[$prop_idf]["__is_scalar"])
		{
			static::$CachedData[$prop_idf]["scalar.types"] = $mixed_data["\$"];

			$firstScalarType = static::$CachedData[$prop_idf]["__is_collection"] ?
				$mixed_data["[]"] :
				(is_array($mixed_data["\$"]) ? $mixed_data["\$"] : q_reset($mixed_data["\$"]));

			// only the type what we have on storage.type
			$type = $mixed_data["storage"];

			static::$CachedData[$prop_idf]["type.bool"] = in_array('boolean', $firstScalarType);
			static::$CachedData[$prop_idf]["type.password"] = 
				(
					static::ExtractExtraConfig($config["cfg"], $path ?: "", "@type.password") ?: 
					($mixed_data["display"]["type"] === "password")
				);

			static::$CachedData[$prop_idf]["type.file"] = in_array('file', $firstScalarType);

			static::$CachedData[$prop_idf]["type.string"] = in_array('string', $firstScalarType);

			static::$CachedData[$prop_idf]["type.date"] = (in_array('datetime', $firstScalarType) || in_array('date', $firstScalarType));
			
			static::$CachedData[$prop_idf]["display.textarea"] = 
				(static::ExtractExtraConfig($config["cfg"], $path ?: "", "@display.textarea") ||
				(strpos(strtolower($type), 'text') !== false));

			static::$CachedData[$prop_idf]["display.editor"] = 
				(static::$CachedData[$prop_idf]["display.textarea"] && 
				(static::ExtractExtraConfig($config["cfg"], $path ?: "", "@display.editor") ||
				$mixed_data["storage.full"]["use_wysiwyg"]));
            
			static::$CachedData[$prop_idf]["display.datepicker"] = 
				((static::ExtractExtraConfig($config["cfg"], $path ?: "", "@display.datepicker") ||
				$mixed_data["storage.full"]["use_flatpickr"]));

			static::$CachedData[$prop_idf]["date.format"] = 
				static::ExtractExtraConfig($config["cfg"], $path ?: "", "@date.format") ?: 
				((static::$CachedData[$prop_idf]["type.date"] && $mixed_data["storage.full"]) ? 
					$mixed_data["storage.full"]["date_format"] : null);

			static::$CachedData[$prop_idf]["enum.force"] = ($mixed_data["display"] && $mixed_data["display"]["enum"]);
			static::$CachedData[$prop_idf]["type.enum"] = (static::$CachedData[$prop_idf]["enum.force"] || (strpos($type, 'enum') !== false));
			
			// static::$CachedData[$prop_idf]["enum.force"] = ($mixed_data["display"] && $mixed_data["display"]["enum"]);
			static::$CachedData[$prop_idf]["type.set"] = preg_match("/^set\\b/uis", $type) ? true : false;
			
			if (static::$CachedData[$prop_idf]["type.enum"])
			{
				if (static::$CachedData[$prop_idf]["enum.force"])
				{
					$p_enum_vals = 
							static::ExtractExtraConfig($config["cfg"], $path ?: "", "@enum.values") ?:
							(($mixed_data && $mixed_data['storage.full'] && $mixed_data['storage.full']['enum_values']) ? 
							json_decode($mixed_data['storage.full']['enum_values'], true) : null);

					$p_view_vals = 
							static::ExtractExtraConfig($config["cfg"], $path ?: "", "@enum.values_props") ?:
							(($mixed_data && $mixed_data['storage.full'] && $mixed_data['storage.full']['enum_values_props']) ? 
							json_decode($mixed_data['storage.full']['enum_values_props'], true) : null);

					static::$CachedData[$prop_idf]['enum.vals'] = ($p_view_vals && isset($p_view_vals[$config["__view__"]])) ? $p_view_vals[$config["__view__"]] : $p_enum_vals;
				}
				else
				{
					static::$CachedData[$prop_idf]['enum.vals'] = 
						static::ExtractExtraConfig($config["cfg"], $path ?: "", "@enum.values") ?:
							static::ParseEnumInfo($type);
							// explode(',', str_replace(array('enum(', ')', "'", '"'), '', $type));
				}

				if (empty(static::$CachedData[$prop_idf]['enum.vals']))
				{
					qvar_dumpk(static::$CachedData[$prop_idf]);
					throw new \Exception("enum values not found!");
				}

				static::$CachedData[$prop_idf]['enum.captions'] = 
					static::ExtractExtraConfig($config["cfg"], $path ?: "", "@enum.captions") ?:
					(($mixed_data && $mixed_data['storage.full'] && $mixed_data['storage.full']['enum_captions']) ? 
					json_decode($mixed_data['storage.full']['enum_captions'], true) : null);

				static::$CachedData[$prop_idf]['enum.styles'] = 
					static::ExtractExtraConfig($config["cfg"], $path ?: "", "@enum.styles") ?:
					(($mixed_data && $mixed_data['storage.full'] && $mixed_data['storage.full']['enum_styles']) ? 
					json_decode($mixed_data['storage.full']['enum_styles'], true) : null);

				static::$CachedData[$prop_idf]['enum.display'] = 
					static::ExtractExtraConfig($config["cfg"], $path ?: "", "@enum.display") ?: 
					(($mixed_data && $mixed_data['storage.full']) ? $mixed_data['storage.full']['enum_display'] : null);
			}
			
			if (static::$CachedData[$prop_idf]["type.set"])
			{
				
				static::$CachedData[$prop_idf]['set.vals'] = 
					static::ExtractExtraConfig($config["cfg"], $path ?: "", "@set.values") ?:
						static::ParseEnumInfo($type);
						// explode(',', str_replace(array('set(', ')', "'", '"'), '', $type));

				if (empty(static::$CachedData[$prop_idf]['set.vals']))
				{
					qvar_dumpk(static::$CachedData[$prop_idf]);
					throw new \Exception("set values not found!");
				}

				static::$CachedData[$prop_idf]['set.captions'] = 
					static::ExtractExtraConfig($config["cfg"], $path ?: "", "@set.captions") ?:
					(($mixed_data && $mixed_data['storage.full'] && $mixed_data['storage.full']['set_captions']) ? 
					json_decode($mixed_data['storage.full']['set_captions'], true) : null);

				static::$CachedData[$prop_idf]['set.styles'] = 
					static::ExtractExtraConfig($config["cfg"], $path ?: "", "@set.styles") ?:
					(($mixed_data && $mixed_data['storage.full'] && $mixed_data['storage.full']['set_styles']) ? 
					json_decode($mixed_data['storage.full']['set_styles'], true) : null);

				static::$CachedData[$prop_idf]['set.display'] = 
					static::ExtractExtraConfig($config["cfg"], $path ?: "", "@set.display") ?: 
					(($mixed_data && $mixed_data['storage.full']) ? $mixed_data['storage.full']['set_display'] : null);
			}
		}

		if (static::$CachedData[$prop_idf]["__is_reference"])
		{
			static::$CachedData[$prop_idf]["dd.binds"] = 
				static::ExtractExtraConfig($config["cfg"], $path ?: "", "@dropdown.binds") ?: 
				(($mixed_data["storage.full"] && $mixed_data["storage.full"]["filter"]) ? $mixed_data["storage.full"]["filter"] : "[]");
		}
		else
		{
			static::$CachedData[$prop_idf]["dd.binds"] = 
				static::ExtractExtraConfig($config["cfg"], $path ?: "", "@dropdown.binds") ?: 
				(($mixed_data["storage.full"] && $mixed_data["storage.full"]["filter"]) ? $mixed_data["storage.full"]["filter"] : null);
		}

		// property is reference or is collection but a collection of references
		if (static::$CachedData[$prop_idf]["__is_reference"])
		{
			$appForModel = static::$CachedData[$prop_idf]["__is_collection"] ? ($config["src_from_types"] ?: $mixed_data["[]"]) : $mixed_data["#"];
			// dd property

			static::$CachedData[$prop_idf]["ref.types"] = $mixed_data["#"];

			if (!static::$CachedData[$prop_idf]["display.individual"] && static::$CachedData[$prop_idf]["struct.subpart"] && $selectorHasProps)
				static::$CachedData[$prop_idf]["display.forceSection"] = true;

			static::$CachedData[$prop_idf]["dd.property"] = 
				static::ExtractExtraConfig($config["cfg"], $path ?: "", "@dropdown.property") ?: 
				(($mixed_data && isset($mixed_data["storage.full"]["dropdownProperty"])) ? $mixed_data["storage.full"]["dropdownProperty"] : 
				(($dd_property = static::GetAppPropertyFor($appForModel, $model, $prop)) ? qaddslashes($dd_property->name) : null));

			static::$CachedData[$prop_idf]["dd.selector"] = 
				static::ExtractExtraConfig($config["cfg"], $path ?: "", "@dropdown.selector") ?: 
				(($mixed_data && $mixed_data["storage.full"] && $mixed_data["storage.full"]["dropdownSelector"]) ? 
				$mixed_data["storage.full"]["dropdownSelector"] : null);

			static::$CachedData[$prop_idf]["dd.captionSelector"] = 
				static::ExtractExtraConfig($config["cfg"], $path ?: "", "@dropdown.captionSelector") ?: 
				(qaddslashes(static::$CachedData[$prop_idf]["dd.selector"] ?: static::GetCaptionSelectorFor($appForModel, $model, $prop)));

			static::$CachedData[$prop_idf]["dd.type.tree"] = 
				static::ExtractExtraConfig($config["cfg"], $path ?: "", "@dropdown.tree") ?: 
				($mixed_data && $mixed_data["display"] && isset($mixed_data["display"]["tree"]));
			
			static::$CachedData[$prop_idf]["dd.loadClass"] = 
				static::ExtractExtraConfig($config["cfg"], $path ?: "", "@dropdown.loadClass") ?: 
				"\Omi\View\DropDown".(static::$CachedData[$prop_idf]["dd.type.tree"] ? "Tree" : "");


			static::$CachedData[$prop_idf]["dd.controller"] = 
				static::ExtractExtraConfig($config["cfg"], $path ?: "", "@dropdown.controller") ?: 
				(/*static::$CachedData[$prop_idf]["use_dropdown"] && */
					($mixed_data["display"] && $mixed_data["display"]["controls"] && ($mixed_data["display"]["controls"] === "on")));
			
			static::$CachedData[$prop_idf]["dd.insert_full_data"] = 
				static::ExtractExtraConfig($config["cfg"], $path ?: "", "@dropdown.insert_full_data");

			// validation
			static::$CachedData[$prop_idf]["dd.attrs"] = (static::$CachedData[$prop_idf]["validation"]) ? 
				" 'q-valid=\"" . htmlspecialchars(static::$CachedData[$prop_idf]["validation"]) . "\"'" : "null";

			// force hiding none option for mandatory drodpowns
			if (static::$CachedData[$prop_idf]["mandatory"] && (strpos(static::$CachedData[$prop_idf]["dd.binds"], "hideNoneOption") === false))
			{
				static::$CachedData[$prop_idf]["dd.binds"] = substr(static::$CachedData[$prop_idf]["dd.binds"], 0, -1) . 
					((static::$CachedData[$prop_idf]["dd.binds"] != "[]") ? ", " : "") . "\"hideNoneOption\" => true]";
			}

			static::$CachedData[$prop_idf]["dd.viewToLoad"] = 
				static::ExtractExtraConfig($config["cfg"], $path ?: "", "@dropdown.useControls") ?:
				(
					static::$CachedData[$prop_idf]["dd.controller"] ? 
					$config["namespace"] . "\\" . (
						($mixed_data && $mixed_data["storage.full"] && $mixed_data["storage.full"]["view_to_load"]) ? 
							qaddslashes($mixed_data["storage.full"]["view_to_load"]) : 
							static::$CachedData[$prop_idf]["dd.property"]
					) : null
				);

		}

		// property is collection
		if (static::$CachedData[$prop_idf]["__is_collection"])
		{
			if (!static::$CachedData[$prop_idf]["display.individual"])
				static::$CachedData[$prop_idf]["display.forceSection"] = true;

			static::$CachedData[$prop_idf]["coll.types"] = $mixed_data["[]"];
			// is one to many collection
			static::$CachedData[$prop_idf]["coll.oneToMany"] = ($mixed_data && $mixed_data["storage.full"] && $mixed_data["storage.full"]["oneToMany"]);

			// flag if it is checkbox collection
			static::$CachedData[$prop_idf]["coll.checkboxes"] = 
				static::ExtractExtraConfig($config["cfg"], $path ?: "", "@coll.checkboxes") ?:
				($mixed_data && $mixed_data["storage.full"] && $mixed_data["storage.full"]["collection_type"] && 
					($mixed_data["storage.full"]["collection_type"] === "checkbox"));

			static::$CachedData[$prop_idf]["coll.checkboxCustomQuery"] = 
				static::ExtractExtraConfig($config["cfg"], $path ?: "", "@coll.checkboxCustomQuery") ?:
				(($mixed_data && $mixed_data["storage.full"] && $mixed_data["storage.full"]["checkbox_coll_custq"]) ? 
					$mixed_data["storage.full"]["checkbox_coll_custq"] : null);

			static::$CachedData[$prop_idf]["coll.checkboxBinds"] = 
				static::ExtractExtraConfig($config["cfg"], $path ?: "", "@coll.checkboxBinds") ?:
				(($mixed_data && $mixed_data["storage.full"] && $mixed_data["storage.full"]["checkbox_coll_binds"]) ? 
					$mixed_data["storage.full"]["checkbox_coll_binds"] : "[]");

			static::$CachedData[$prop_idf]["coll.checkboxSelector"] = 
				static::ExtractExtraConfig($config["cfg"], $path ?: "", "@coll.checkboxSelector") ?:
				(($mixed_data && $mixed_data["storage.full"] && $mixed_data["storage.full"]["checkbox_coll_selector"]) ? 
					$mixed_data["storage.full"]["checkbox_coll_selector"] : null);

			// avoid duplicates on collection items
			static::$CachedData[$prop_idf]["coll.avoidDuplicates"] = 
				static::ExtractExtraConfig($config["cfg"], $path ?: "", "@coll.avoidDuplicates") ?:
				(($mixed_data && $mixed_data["storage.full"]) ? $mixed_data["storage.full"]["avoid_duplicates"] : false);

			// force default row on collection
			static::$CachedData[$prop_idf]["coll.hasDefaultRow"] = 
				static::ExtractExtraConfig($config["cfg"], $path ?: "", "@coll.hasDefaultRow") ?:
				(($mixed_data && $mixed_data["storage.full"]) ? $mixed_data["storage.full"]["has_default_row"] : false);
		}

		if (static::$CachedData[$prop_idf]["mandatory"])
		{
			# (!empty($value))
			if (static::$CachedData[$prop_idf]["validation"] === null)
				static::$CachedData[$prop_idf]["validation"] = '((!empty($value)) || ($value === "0"))';
			else
				static::$CachedData[$prop_idf]["validation"] .= ' && ((!empty($value)) || ($value === "0"))';
		}
		
		return static::$CachedData[$prop_idf];
	}

	public static function IsScalarProperty($mixed_types)
	{
		$_ct = ($mixed_types["[]"] && (count($mixed_types["[]"]) === 1)) ? q_reset($mixed_types["[]"]) : null;

		try
		{
			$ty = \QModelType::GetScalarTypeId($_ct);

		}
		catch (\Exception $ex)
		{
			$ty = null;
		}

		return !is_null($ty);
	}

	public static function GetAvoidDuplicatesMarkup($avoidDuplicates)
	{
		$cls = "";
		if (!$avoidDuplicates)
			return "";
		else if ($avoidDuplicates === "filter")
			return "qc-avd-filter";
		else if ($avoidDuplicates === "on_select")
			return "qc-avd-on-select";
		return "";
	}

	public static function GetXGTag($property, $parent_model, $read_only, $list_mode = true)
	{
		return $property."(".(is_array($parent_model) ? implode(",", $parent_model) : $parent_model).
			")|ro=".($read_only ? "y" : "n").",list=".($list_mode ? "y" : "n");
	}

	//=========================================Getting properties details==========================================
	/**
	 * Checks if a selector has properties.
	 * 
	 * @param type $selector
	 * @return boolean
	 */
	public static function SelectorHasProperties($selector)
	{
		$keys = array_keys($selector ?? []);
		// must be an array, must have at least one key, that is not Id
		return is_array($selector) && (($first = q_reset($keys)) && (next($keys) || (strtolower($first) !== "id")));
	}
	
	/**
	 * 
	 * @param string[] $types
	 * @param string $parent_model
	 * @param string $property
	 * @return string
	 */
	public static function GetCaptionSelectorFor($types, $parent_model = null, $property = null)
	{
		// check if types
		if (!$types)
			return null;
		
		if (!is_array($types))
			$types = [$types];
		
		$ret = null;

		foreach ($types as $ty)
		{
			$type_inf = \QModelQuery::GetTypesCache($ty);
			//var_dump($ty, $type_inf["#%misc"]["model"]["captionProperties"]);
			if ($type_inf["#%misc"]["model"]["captionProperties"])
			{
				$caption_props = qParseEntity(implode(",", $type_inf["#%misc"]["model"]["captionProperties"]));
				$ret = qJoinSelectors($ret, $caption_props);
			}
		}
		
		return $ret ? qImplodeEntity($ret) : "Id";
	}
	
	
	/**
	 * @param type $read_only
	 * @param type $model_name
	 * @param type $selector
	 * @param type $headings
	 * @param type $heading_rates
	 * @param type $heading_rates_total
	 * @return type
	 * @throws \Exception
	 */
	public static function GetHeadingsData($config, $read_only, $model_name, $selector, &$headings = null, &$heading_rates = null, &$heading_rates_total = 0)
	{
		// unset selector * from query
		if ($selector && is_array($selector) && ($selector["*"] !== null))
			unset($selector["*"]);
		
		if ($headings === null)
			$headings = [];
		if ($heading_rates === null)
			$heading_rates = [];

		if (is_array($model_name))
			$model_name = q_reset($model_name);

		$headings_props = [];

		$m_ty = \QModel::GetTypeByName($model_name);

		// go through each selector
		if ($selector && count($selector) > 0)
		{
			foreach ($selector as $k => $s)
			{
				$prop = $m_ty->properties[$k];
				if (!$prop)
				{
					$headings[] = $k;
					$headings_props[] = (object)['name' => $k];
					continue;
				}

				if ($prop->hasReferenceType())
				{

				}

				$headings[] = $k;
				$headings_props[] = $prop;

			}
		}

		return [$headings, $heading_rates, $heading_rates_total, $headings_props];
	}
	
	public static function LoadHeadings(&$headings, &$headings_props, $selector, $model_name, $prop)
	{
		//if ($selector && count($selector) > 0)
	}
	/**
	 * 
	 * @param type $model
	 * @return type
	 */
	public static function GetModelType($model)
	{
		if (!$model)
			return null;

		if (isset(static::$CachedData[$model]))
			return static::$CachedData[$model];
		return (static::$CachedData[$model] = \QModel::GetTypeByName($model));
	}

	public static function GetModelStorageData($model, $viewTag, $prop)
	{
		$modelType = static::GetModelType($model);
		$storageData = ($modelType && $modelType->storage && $modelType->storage[$prop] && (($json = json_decode($modelType->storage[$prop], true)))) ? $json : null;
		return ($storageData && ($ret = ($storageData[$viewTag] ?: $storageData['general']))) ? $ret : null;
		
	}
	
	/**
	 * 
	 * @param string[] $types
	 * @param string $parent_model
	 * @param string $property
	 * @return \QModelProperty
	 */
	public static function GetAppPropertyFor($types, $parent_model = null, $property = null)
	{
		$storage_model = \QApp::GetDataClass();
		$ty = \QModel::GetTypeByName($storage_model);
		
		if ($parent_model)
		{
			if (!is_array($parent_model))
				$parent_model = [$parent_model];
			foreach ($parent_model as $pm)
			{
				$optionsPool = $pm::GetOptionsPool($property, $pm);
				if ($optionsPool)
				{
					$app_prop = q_reset($optionsPool);
					return $ty->properties[$app_prop];
				}
			}
		}
		
		foreach ($ty->properties as $prop)
		{
			// @todo : we should use another flag to control this
			if ($prop->storage["none"])
				continue;
			
			$ct = $prop->getCollectionType();
			if ($ct)
			{
				$res = array_intersect_key($ct->options, $types);
				if ($res === $types)
					return $prop;
			}
		}

		return null;
	}
	
	public static function GetConfigFolder($config = null)
	{
		if ($config === null)
			$config = static::$Config;
		
		return $config["gen_config"] ? rtrim($config["gen_config"], "/")."/" : null;
	}
	
	public static function ParseEnumInfo($type)
	{
		$ret = preg_split("/".
				
				"('(?:(?:[^\\\\\']+|(?:\\\\.)+)*)\')|". # strings with '
				"(\"(?:(?:[^\\\\\"]+|(?:\\\\.)+)*)\")|". # strings with "
				"(\\s*\\,\\s*)".
				
					"/ius", $type, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
		
		if (!$ret)
			throw new \Exception('Enum parse error: '.$type);
		
		$enum_vals = [];
		foreach ($ret as $r)
		{
			if (($r[0] === "'") || ($r[0] === "\""))
				$enum_vals[] = substr($r, 1, -1);
		}
		
		if (!$enum_vals)
			throw new \Exception('Enum parse error (empty): '.$type);
		
		// qvar_dump($type, $enum_vals, $ret);
		
		return $enum_vals;
	}
	
	protected static function Get_Form_Selector_Extract_Select_Data(array $data)
	{
		$ret = [];
		foreach ($data ?: [] as $k => $v)
		{
			if ($k === '@select')
			{
				foreach ($v as $kv => $vv)
				{
					if (empty($kv) || empty($vv))
						continue;
					
					$prts = explode(".", trim($kv));
					$d_pos = &$ret;
					foreach ($prts as $ppp)
					{
						if (!isset($d_pos[$ppp]))
							$d_pos[$ppp] = [];
						$d_pos = &$d_pos[$ppp];
					}
				}
			}
			else if (is_array($v))
			{
				$sub_arr = static::Get_Form_Selector_Extract_Select_Data($v);
				if ($sub_arr)
					$ret = qJoinSelectors($ret, $sub_arr);
			}
		}
		return $ret;
	}
	
	public static function Get_Form_Selector($config, $view, $storage_model)
	{
		$app_form_selector = $config["selector:form"] ?: $config["selector:new"] ?: $config["selector:edit"] ?: (method_exists($storage_model, "GetEntityForGenerateForm") ?
				$storage_model::GetEntityForGenerateForm_Final($view) : $storage_model::GetPropertyModelEntity($view));
		
		$form_selector = null;
		if ($config['cfg']['::']['@boxes'])
		{
			$form_selector = static::Get_Form_Selector_Extract_Select_Data($config['cfg']['::']['@boxes']);
			foreach ($form_selector ?: [] as $fs_k => $fs_v)
			{
				if (($app_sub_sell = $app_form_selector[$fs_k]) !== null)
				{
					$form_selector[$fs_k] = qJoinSelectors($fs_v, $app_sub_sell);
					# ... only for collections
				}
			}
			
			return $form_selector;
		}
		else if ($config['cfg']['::']['@groups'])
		{
			$form_selector = [];
			foreach ($config['cfg']['::']['@groups'] ?: [] as $tmp_grp)
			{
				foreach ($tmp_grp["@select"] ?: [] as $frm_sel => $frm_sel_val)
				{
					$frm_sel_parts = explode(".", $frm_sel);
					$fs = &$form_selector;
					foreach ($frm_sel_parts ?: [] as $fsp)
					{
						if (!is_array($fs[$fsp]))
							$fs[$fsp] = [];
						$fs = &$fs[$fsp];
					}
				}
			}
			foreach ($form_selector ?: [] as $fs_k => $fs_v)
			{
				# this has to be changed for multi-level
				if (($app_sub_sell = $app_form_selector[$fs_k]) !== null)
					$form_selector[$fs_k] = qJoinSelectors($fs_v, $app_sub_sell);
			}
			return $form_selector;
		}
		else
		{
			return $app_form_selector;
		}
	}
	
	public static function Flat_Selector($selector, \QModelProperty $storage_model_property = null, bool $skip_collections = true, array $prev_list = null)
	{
		$ret = [];
		
		if ($skip_collections && $storage_model_property)
		{
			$first_type = $storage_model_property->getFirstInstantiableType();
			$class_def = \QModelType::GetModelTypeByClass($first_type);
		}
		
		foreach ($selector as $k => $v)
		{
			$do_recurse = true;
			if ($skip_collections && $class_def && ($k_prop_reflection = $class_def->properties[$k]) && $k_prop_reflection->getCollectionType())
			{
				$do_recurse = false;
			}
			
			$next_list = $prev_list;
			$next_list[] = $k;
			if (empty($v) || (!$do_recurse))
			{
				$r_key = implode(".", $next_list);
				$ret[$r_key] = $do_recurse ? true : '[]';
			}
			else
			{
				# we only use $skip_collections at the first level
				$rets = static::Flat_Selector($v, null, false, $next_list);
				foreach ($rets ?: [] as $r_k => $r)
					$ret[$r_k] = $r;
			}
		}
			
		return $ret;
	}
	
	protected static function Propose_Boxes_Setup($form_selector, \QModelProperty $storage_model_property, $flat_selector, string $view)
	{
		# reset($flat_selector);
		# $flat_selector_first = key($flat_selector);

		$grp_selector = [];
		foreach ($flat_selector ?: [] as $fs_k => $fs_v)
		{
			if ($fs_v === '[]')
				$grp_selector[$fs_k] = $fs_v;
			else
			{
				$parts = explode(".", $fs_k, 2);
				if (isset($parts[1]))
					$grp_selector[$parts[0]] = $fs_v;
				else
					$grp_selector['@main'][$fs_k] = $fs_k;
			}
		}

		$layout_placement = [];
		$layout_placement['for'] = ['form', 'view'];

		#$groups = [];
		# $groups["@all"][$flat_selector_first] = 'after';
		$first_element = null;

		$g_pos = 0;
		$lp_row_index = 0;
		$lp_sub_row_index = 0;
		$lp_col_index = 0;
	
		foreach ($grp_selector ?: [] as $fs_k => $fs_v)
		{
			if (!$first_element)
				$first_element = $fs_k;

			$g_sbg_index = $g_pos % 4;
			$lp_sub_row_index = 0;
			$lp_col_index = 0;

			if ($g_sbg_index === 1)
				$lp_col_index = 1;
			if ($g_sbg_index === 2)
			{
				$lp_col_index = 1;
				$lp_sub_row_index = 1;
			}

			if ($fs_v === '[]') # collection, have a seprate box
			{
				# $groups["@all"]['@select'][$fs_k] = $fs_k;

				$layout_placement["rows"][$lp_row_index]["cols"][$lp_col_index]["sub-rows"][$lp_sub_row_index] = [
					'@select' => [$fs_k => $fs_k],
					'@tag' => $fs_k,
					'@cols' => 1,
				];
			}
			else if ($fs_k === '@main')
			{
				# foreach ($fs_v ?: [] as $fs_v__k => $fs_v__v)
					# $groups["@all"]['@select'][$fs_v__k] = $fs_v__v;

				$layout_placement["rows"][$lp_row_index]["cols"][$lp_col_index]["sub-rows"][$lp_sub_row_index] = [
						'@select' => $fs_v,
						'@tag' => '@main',
						'@caption' => 'Details',
						'@cols' => ($g_sbg_index === 3) ? 2 : 1,
					];
			}
			else
			{
				# this is a FK
				# $groups["@all"]['@select'][$fs_k] = $fs_k;

				$layout_placement["rows"][$lp_row_index]["cols"][$lp_col_index]["sub-rows"][$lp_sub_row_index] = [
						'@select' => [$fs_k => $fs_k],
						'@tag' => $fs_k,
						'@cols' => 1,
					];
			}

			# setup the next position
			{
				if ($g_sbg_index === 0)
					$lp_col_index++;
				else if ($g_sbg_index === 1)
					$lp_sub_row_index++;
				else if (($g_sbg_index === 2) || ($g_sbg_index === 3))
				{
					$lp_col_index = 0;
					$lp_sub_row_index = 0;
					$lp_row_index++;
				}
				$g_pos++;
			}
		}
		
		return $layout_placement;
	}
	
    /**
     * Renders boxes :: grid layout :: cols count
     * 
     * @param type $layout_placement
     * @param type $form_selector
     * @param type $storage_model_property
     * 
     * @return type
     * 
     * @throws \Exception
     */
	protected static function Render_Boxes_To_Layout($layout_placement, $form_selector, $storage_model_property, $config)
	{
		$grp_layout = "";
		$selected_props = [];
		
		require(static::GetTemplate('_render_boxes_to_layout.tpl', $config));
		
		# debug output
		{
			$class_def = null;
			$str_3 = null;
			$props_list = [];
			if ($storage_model_property)
			{
				$first_type = $storage_model_property->getFirstInstantiableType();
				$class_def = \QModelType::GetModelTypeByClass($first_type);
				$props_list = isset($class_def->properties) ? array_combine($class_def->properties->getKeys(), $class_def->properties->getKeys()) : [];
				ksort($props_list);
			}

			$str_1 = json_encode($layout_placement, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
			$str_1 = preg_replace(["/\\{/uis", "/\\}/uis", "/\\:/uis"], ["[", "]", " =>"], $str_1);
			$str_2 = json_encode(array_combine(array_keys($form_selector), array_keys($form_selector)), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
			$str_2 = preg_replace(["/\\{/uis", "/\\}/uis", "/\\:/uis"], ["[", "]", " =>"], $str_2);
			$str_3 = json_encode($props_list, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
			$str_3 = preg_replace(["/\\{/uis", "/\\}/uis", "/\\:/uis"], ["[", "]", " =>"], $str_3);

			if (defined('Q_SHOW_LAYOUT_BOXES') && Q_SHOW_LAYOUT_BOXES)
			{
				$grp_layout .= "\n@if (\QAutoload::GetDevelopmentMode() && defined('Q_SHOW_LAYOUT_BOXES') && Q_SHOW_LAYOUT_BOXES) \n <pre style='line-height: 13px; font-size: 12px;'>\n";
				$grp_layout .= "\n@LAYOUT-BOXES:\n". str_replace("@", "<span>@</span>", htmlentities($str_1));
				$grp_layout .= "\n@LAYOUT-OPTIONS:\n". str_replace("@", "<span>@</span>", htmlentities($str_2));
				$grp_layout .= "\n@PROPS:\n". str_replace("@", "<span>@</span>", htmlentities($str_3));
				$grp_layout .= "</pre>\n@endif\n\n";
			}
		}

		return [$grp_layout, $selected_props];
	}
	
	public static function Reset_All_Generated()
	{
		if (!defined('QGEN_SaveDirBase'))
			throw new \Exception('`QGEN_SaveDirBase` is not defined');
		if (!is_dir(QGEN_SaveDirBase))
			throw new \Exception('`QGEN_SaveDirBase` ('.QGEN_SaveDirBase.') is not a dir');
		
		$scan_path = realpath(QGEN_SaveDirBase)."/";
		$views = scandir($scan_path);
		
		foreach ($views ?: [] as $view)
		{
			if (($view === '.') || ($view === '..') || ($view[0] !== strtoupper($view[0])))
				continue;
			$full_path = $scan_path.$view."/";
			if (!is_dir($full_path))
				continue;
			
			if (file_exists($full_path.$view.'.gen.php'))
			{
				file_put_contents($full_path.$view.'.gen.php', '<?php
/**
  * This file was generated and it will be overwritten when it\'s dependencies are changed or removed.
  */
namespace '.Q_Gen_Namespace.';

trait '.$view.'_GenTrait
{
}
');
			}
			
			$tpls = scandir($full_path);
			foreach ($tpls ?: [] as $tpl)
			{
				if (substr($tpl, -4, 4) !== '.tpl')
					continue;
				$ff_path = $full_path.$tpl;
				if (is_file($ff_path))
					file_put_contents($ff_path, '<div xg-security="$grid_mode, $settings[\'model:property\'], $vars_path, $data" xg-item=\'Branding(Omi\App)|ro=n,list=n\' class=\'qc-xg-item\' q-args="$settings = null, $data = null, $bind_params = null, $grid_mode = null, $id = null, $vars_path = \'\', $_qengine_args = null">
						</div>');
			}
		}
		
	}
	
	public static function GetTypeFlags($config, $src_from_types, $property, $view)
	{
		# loop the type , extract cfg
		# qvar_dumpk($config, $src_from_types, $property, $view);
		
		foreach ($src_from_types ?: [] as $d_type)
		{
			$cfg = \QModel::GetTypeByName($d_type);
			qvar_dumpk($cfg->cfg, $config['cfg']['::']);
		}
		
	}
	
	public static function Is_Date_Input(string $input_type = null)
	{
		if (!$input_type)
			return false;
		switch ($input_type)
		{
			case 'date':
			case 'datetime':
			case 'datetime-local':
				return true;
			default:
				return false;
		}
	}
}

