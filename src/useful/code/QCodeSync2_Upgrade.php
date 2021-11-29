<?php

/**
 * Generates and patches platform standards
 */
trait QCodeSync2_Upgrade
{
	protected $upgrage_dir;
	protected $upgrade_inside_dir;
	
	protected $upgrade_possible_parent_issues;
	

	public function run_upgrade(array $files, array $changed_or_added, array $removed_files, array $new_files)
	{
		# one dir above to catch more situations
		$this->upgrade_inside_dir = dirname( getcwd() )."/";
		$this->upgrage_dir = dirname(getcwd())."/upgrade/";
		$this->upgrade_possible_parent_issues = [];
		
		$this->temp_code_dir = $this->upgrage_dir."temp/code/";
		
		if (!is_dir($this->temp_code_dir))
			qmkdir($this->temp_code_dir);
		if (!is_dir($this->upgrage_dir))
			throw new \Exception('Please create the upgrade dir: '.$this->upgrage_dir);
		
		// new steps ... 
		# 1. copy trivia, not included files
		{
			$runtime_folder = \QAutoload::GetRuntimeFolder();
			
			# copy run stuff
			# copy ~backend_config
			# copy .gitignore ... etc etc etc
			$loop_over = $files;
			$loop_over[dirname($runtime_folder)."/~backend_config/"] = 'full';
			$loop_over[dirname($runtime_folder)."/"] = 'non-recursive';
			if (defined('Q_RUN_CODE_UPGRADE_TO_TRAIT_EXTAR_DIRS') && Q_RUN_CODE_UPGRADE_TO_TRAIT_EXTAR_DIRS)
			{
				foreach (Q_RUN_CODE_UPGRADE_TO_TRAIT_EXTAR_DIRS as $cfg_path => $run_folder_mode)
				{
					if (is_dir($cfg_path))
						$loop_over[realpath($cfg_path)."/"] = $run_folder_mode;
				}
			}
			
			foreach ($loop_over as $layer => $run_folder_mode)
			{
				if (substr($layer, 0, strlen($this->upgrade_inside_dir)) !== $this->upgrade_inside_dir)
					throw new \Exception('Code folder `'.$layer.'` is outside the upgrade directory `'.$this->upgrade_inside_dir.'`');

				if ($run_folder_mode === 'non-recursive')
				{
					foreach (scandir($layer) ?: [] as $sdir_item)
					{
						if (($sdir_item === '.') || ($sdir_item === '..'))
							continue;
						$fp = $layer.$sdir_item;
						if (is_file($fp))
						{
							$upgrade_path = $this->upgrage_dir . (substr($fp, strlen($this->upgrade_inside_dir)));
							copy($fp, $upgrade_path);
							
							static::remove_gen_trait_in_use($upgrade_path);
							
							if (($layer === dirname($runtime_folder)."/") && ($sdir_item === 'config.php'))
							{
								$config_content = file_get_contents($upgrade_path);

								$new_config_content = static::upgrade_replace_constant_values($config_content, 
											[
												'Q_RUN_CODE_UPGRADE_TO_TRAIT' => false,
												'Q_RUN_CODE_NEW_AS_TRAITS' => true,
												'Q_SESSION_PATH' => '../../../sessions/',
											]);
								
								if ($new_config_content)
									filePutContentsIfChanged($upgrade_path, $new_config_content);
							}
						}
					}
				}
				else
				{
					if ($run_folder_mode !== 'full')
					{
						$regex_iterator = new RegexIterator(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($layer)), 
											'/(^|\/)\p{Ll}.*\.(php|js|css|tpl)$/us');
					}
					else
					{
						$regex_iterator = new RegexIterator(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($layer)), 
											'/(^|\/).*$/us');
					}
					# qvar_dumpk('/(^|\/)\p{Ll}.*\.(php|js|css|tpl)$/us');
					$regex_iterator->setFlags(RegexIterator::USE_KEY);
					foreach ($regex_iterator as $file)
					{
						# skip upper case, skip gen. skip dyn.
						$bn = $file->getBasename();
						if (($run_folder_mode !== 'full') && (strtolower($bn[0]) !== $bn[0]))
							continue;

						$upgrade_path = $this->upgrage_dir . (substr($file->getPathname(), strlen($this->upgrade_inside_dir)));
						if (!is_dir(dirname($upgrade_path)."/"))
							qmkdir(dirname($upgrade_path)."/");
						
						# echo $file->getPathname() . " | " . $upgrade_path . PHP_EOL . "<br/>";
						copy($file->getPathname(), $upgrade_path);
						
						static::remove_gen_trait_in_use($upgrade_path);
					}
				}
			}
		}
		
		$lint_info = null;
		$grouped_data = [];
		
		foreach ($files as $layer => $layer_files)
		{
			if (substr($layer, 0, strlen($this->upgrade_inside_dir)) !== $this->upgrade_inside_dir)
				throw new \Exception('Code folder `'.$layer.'` is outside the upgrade directory `'.$this->upgrade_inside_dir.'`');
			
			# if (static::$PHP_LINT_CHECK)
			#	$this->check_syntax($layer, $layer_files, $lint_info);
			
			foreach ($layer_files as $file => $mtime)
			{
				$is_php_ext = (substr($file, -4, 4) === '.php');
				$is_tpl_ext = (substr($file, -4, 4) === '.tpl');
				if ($is_php_ext && ((substr($file, -8, 8) === '.gen.php') || substr($file, -8, 8) === '.dyn.php'))
					// just skip!
					continue;
				else if (!($is_php_ext || $is_tpl_ext))
				{
					$this->upgrade_copy_file($layer, $file);
				}
				else
				{
					// echo "Evaluating: ".$layer.$file."<br/>\n";
					// plain PHP ... set it in the autoload
					$header_inf = \QPHPToken::ParseHeaderOnly($layer.$file, false);
					if (!isset($header_inf["class"]))
						throw new \Exception('Unable to identify short class name in: '.$layer.$file);
					$short_class_name = (($p = strrpos($header_inf["class"], "\\")) !== false) ? substr($header_inf["class"], $p + 1) : $header_inf["class"];

					$header_inf['is_tpl'] = $is_tpl_ext;
					$header_inf['is_url'] = $is_php_ext && (substr($file, -8, 8) === '.url.php');
					$header_inf['is_php'] = $is_php_ext && (!$header_inf['is_url']);
					$header_inf['is_patch'] = $is_php_ext && (substr($file, -10, 10) === '.patch.php');
					$header_inf['file'] = $file;
					
					$key = $header_inf['is_php'] ? '01-' : ($header_inf['is_url'] ? '02-' : '03-');

					$grouped_data[$layer][dirname($file)."/"][$short_class_name][$key.basename($file)] = $header_inf;
					
					if ($header_inf['is_url'] || $header_inf['is_tpl'])
						$this->upgrade_copy_file($layer, $file);
				}
			}
		}
		
		// from here on we only do PHP classes
		$info_by_class = [];
		
		foreach ($grouped_data as $gd_layer => $gd_dirs)
		{
			foreach ($gd_dirs as $gd_dir_name => $gd_classes_list)
			{
				foreach ($gd_classes_list as $gd_class_short => $gd_class_files)
				{
					// sort by (1.php, 2.url, 3.tpl) to get the best info
					ksort($gd_class_files);
					
					$namespace = null;
					$class = null;
					$extends = null;
					$implements = null;
					
					$has_tpl = null;
					$has_url = null;
					$is_patch = null;
					
					$locations = [];
					
					foreach ($gd_class_files as $gd_file_name => $header_inf)
					{
						if ($header_inf['namespace'] && ((!$namespace) || ($namespace === $header_inf['namespace'])))
							$namespace = $header_inf['namespace'];
						//else if ($header_inf['namespace'] && ($header_inf['namespace'] !== $namespace))
						//	throw new \Exception('Namespace mistmatching '.$gd_layer.$gd_dir_name.$gd_file_name);
						
						if ($header_inf['class'] && ((!$class) || ($class === $header_inf['class'])))
							$class = $header_inf['class'];
						//else if ($header_inf['class'] && ($header_inf['class'] !== $class))
						//	throw new \Exception('Class mistmatching '.$gd_layer.$gd_dir_name.$gd_file_name);
						
						if ($header_inf['extends'] && ((!$extends) || ($extends === $header_inf['extends'])))
							$extends = $header_inf['extends'];
						//else if ($header_inf['extends'] && ($header_inf['extends'] !== $extends))
						//	throw new \Exception('Extends mistmatching '.$gd_layer.$gd_dir_name.$gd_file_name);
						
						if ($header_inf['implements'] && ((!$implements) || ($implements === $header_inf['implements'])))
							$implements = $header_inf['implements'];
						//else if ($header_inf['implements'] && ($header_inf['implements'] !== $implements))
						//	throw new \Exception('Implements mistmatching '.$gd_layer.$gd_dir_name.$gd_file_name);
						
						if ($header_inf['is_tpl'])
							$has_tpl = true;
						if ($header_inf['is_url'])
							$has_tpl = true;
						if ($header_inf['is_patch'])
							$is_patch = true;
						
						if ($header_inf['is_patch'] || $header_inf['is_php'])
							// only php / patch are to be converted if needed
							$locations[] = $header_inf['file'];
					}
					
					// test if it extends 
					$full_class_name = \QPHPToken::ApplyNamespaceToName($class, $namespace);
					
					if ($extends)
						$info_by_class[$full_class_name]['extends'] = \QPHPToken::ApplyNamespaceToName($extends, $namespace);
					
					foreach ($implements ?: [] as $impl)
						$info_by_class[$full_class_name]['implements'][$impl] = \QPHPToken::ApplyNamespaceToName($impl, $namespace);
					
					if ($has_tpl)
						$info_by_class[$full_class_name]['has_tpl'] = true;
					if ($has_url)
						$info_by_class[$full_class_name]['has_url'] = true;
					if ($is_patch)
						$info_by_class[$full_class_name]['is_patch'] = true;
					foreach ($locations as $loc)
						$info_by_class[$full_class_name]['files'][$gd_layer][$loc] = true;
				}
			}
		}
		
		$watch_folders_tags = array_flip(\QAutoload::GetWatchFoldersByTags());
		$toks_cache_methods = [];
		
		foreach ($info_by_class as $class => $info)
		{
			# $class_ns = (($p = strrpos($class, "\\")) !== false) ? substr($class, 0, $p) : null;
			
			if (($info['has_tpl']) || ($info['has_url']) || ($info['is_patch']) || 
						($info['extends'] && $this->check_if_class_extends($class, 'QModel', $info['extends'], $info_by_class)) 
						/* || ($info['implements'] && $this->check_if_class_implements($class, 'QIModel', $info['implements'], $info_by_class)) */ )
			{
				// do the upgrade
				$prev_layer_class_name = null;
				foreach ($info['files'] ?: [] as $layer => $files_list)
				{
					foreach ($files_list as $file => $tmp)
					{
						// do the `upgrade` then push it
						// we need the layer name !!!
						$prev_layer_class_name = $this->upgrade_class_file($class, $layer, $file, $watch_folders_tags[$layer], $toks_cache_methods, $prev_layer_class_name);
					}
				}
			}
			else
			{
				// transfer them
				foreach ($info['files'] ?: [] as $layer => $files_list)
				{
					foreach ($files_list as $file => $tmp)
					{
						$this->upgrade_copy_file($layer, $file);
					}
				}
			}
		}
		
		/**
		 * STEPS: 
		 *			1. if a class is patched `later` | or has tpl/url (now or later) | or QModel/QIModel (for getters/setters), rename it to CLASS_$layer_
		 *				ELSE JUST COPY IT !
		 *			2. inside it set ** @class.name CLASS *
		 *			3. patching classes must ` extend ` prev_patched class name
		 */
		
		if ($this->upgrade_possible_parent_issues)
		{
			$new_runtime_path = $this->upgrage_dir . (substr(dirname($runtime_folder)."/", strlen($this->upgrade_inside_dir)));
			filePutContentsIfChanged($new_runtime_path.".upgrade_possible_parent_issues.json", json_encode($this->upgrade_possible_parent_issues));
		}
		
		
	}
	
	public function upgrade_class_file(string $full_class_name, string $layer, string $file, string $layer_tag, array &$toks_cache_methods, string $prev_layer_class_name = null)
	{
		$file_tok = \QPHPToken::ParsePHPFile($layer.$file, false, false);
		if (!$file_tok)
			throw new \Exception('Unable to parse: '.$layer.$file);
		
		$class_tok = $file_tok->findFirstPHPTokenClass();
		if (!$class_tok)
			throw new \Exception('Unable to parse and find class in: '.$layer.$file);
		
		$prev_toks_methods = $toks_cache_methods[$full_class_name];
		
		$new_class_name = $class_tok->className.'_'.$layer_tag.'_';
		$force_extends = $prev_layer_class_name ?: false;
		
		$after_class_stmt = false;
		$inside_extends = false;
		$class_name_was_set = false;
		$extends_was_set = false;
		$class_name_pos = false;
		$doc_comment_pos_before = false;
		# $inside_use_stmt = false;
		
		$patch_renames = [];
		
		if ($class_tok->final)
			throw new \Exception("Can not upgrade class `{$full_class_name}` because it's `final` and it needs patching");
			
		# $unset_gen_trait = null;
		
		foreach ($class_tok->children ?: [] as $t_pos => $child)
		{
			if (($child instanceof \QPHPTokenDocComment) && ($child === $class_tok->docComment))
			{
				$parsed_dc = $this->parse_doc_comment((string)$child);
				if (isset($parsed_dc["class.name"]))
					$parsed_dc["class.name"][1] = " ".$class_tok->className;
				else
					$parsed_dc["class.name"] = ["\n * @class.name", " ".$class_tok->className."\n "];
				
				if ($class_tok->abstract)
				{
					if (isset($parsed_dc["class.abstract"]))
						$parsed_dc["class.abstract"][1] = " true";
					else
						$parsed_dc["class.abstract"] = ["\n * @class.abstract", " true\n "];
				}
				if ($class_tok->final)
				{
					if (isset($parsed_dc["class.final"]))
						$parsed_dc["class.final"][1] = " true";
					else
						$parsed_dc["class.final"] = ["\n * @class.final", " true\n "];
				}
				
				if ($parsed_dc['patch.rename'])
				{
					$pr_lines = explode("\n", $parsed_dc['patch.rename'][1]);
					foreach ($pr_lines as $pr_line_)
					{
						$pr_line = trim(trim(trim($pr_line_), '*'));
						if (!empty($pr_line))
						{
							list($pr_key, $pr_val) = preg_split("/(\s+)/uis", $pr_line, 2);
							if ($pr_key && $pr_val)
								$patch_renames[$pr_key] = $pr_val;
						}
					}
				}
				
				$dc_str = "";
				foreach ($parsed_dc as $pdc_item)
					$dc_str .= $pdc_item[0].rtrim($pdc_item[1]);
				$child->children[0][1] = "/**{$dc_str}\n */";
			}
			else if ($child instanceof \QPHPToken)
				continue;
			
			// $done = false;
			$type = is_array($child) ? $child[0] : $child;
			switch ($type)
			{
				case T_ABSTRACT:
				case T_FINAL:
				{
					if ($doc_comment_pos_before === false)
						$doc_comment_pos_before = $t_pos;
					break;
				}
				case T_CLASS:
				{
					if ($doc_comment_pos_before === false)
						$doc_comment_pos_before = $t_pos;
					$after_class_stmt = true;
					break;
				}
				case T_STRING:
				case T_NS_SEPARATOR:
				{
					if ($inside_extends)
					{
						if ($force_extends)
						{
							$class_tok->children[$t_pos][1] = $force_extends;
							$extends_was_set = true;
						}
						$inside_extends = false;
					}
					else if ($after_class_stmt && (!$class_name_was_set))
					{
						$class_tok->children[$t_pos][1] = $new_class_name;
						$class_name_was_set = true;
						$class_name_pos = $t_pos;
					}
					/*else if ($inside_use_stmt && ($child[1] === $class_tok->className.'_GenTrait'))
					{
						# $class_tok->children[$t_pos][1] = $new_class_name.'_GenTrait';
						# qvar_dumpk('$inside_use_stmt: '. $child[1]);
						$unset_gen_trait = $class_tok->className.'_GenTrait';
					}*/
					break;
				}
				case T_EXTENDS:
				{
					$inside_extends = true;
					break;
				}
				case T_IMPLEMENTS:
				{
					$inside_extends = false;
					break;
				}
				/*
				case "{":
				{
					//if ($after_class_stmt)
					//	$done = true;
					$inside_use_stmt = false;
					break;
				}
				case ";":
				{
					$inside_use_stmt = false;
					break;
				}
				case T_USE:
				{
					$inside_use_stmt = true;
					break;
				}
				*/
				default:
					break;
			}
			//if ($done)
			//	break;
		}
		
		if ($force_extends && (!$extends_was_set))
		{
			// the order for splice is important !!!
			if ($class_name_pos === false)
				throw new \Exception('Class name was not found in tokens: '.$layer.$file);
			
			array_splice($class_tok->children, $class_name_pos + 1, 0, 
						[
							[T_WHITESPACE, ' '], 
							[T_EXTENDS, 'extends'],
							[T_WHITESPACE, ' '],
							[T_STRING, $force_extends],
							]);
			 
		}
		
		if (!$class_tok->abstract)
		{
			$insert_abstract_def = [
							[T_ABSTRACT, 'abstract'],
							[T_WHITESPACE, " "],];
			
			array_splice($class_tok->children, $doc_comment_pos_before, 0, $insert_abstract_def);
		}
		
		if (!$class_tok->docComment)
		{
			// the order for splice is important !!!
			if ($doc_comment_pos_before === false)
				throw new \Exception('Class definition start was not found in tokens: '.$layer.$file);
			$doc_comm = "/**\n * @class.name {$class_tok->className}\n ";
			
			if ($class_tok->abstract)
				$doc_comm .= "* @class.abstract true\n ";
			if ($class_tok->final)
				$doc_comm .= "* @class.final true\n ";
			
			$doc_comm .= '*/';
			$insert_doc_comm_array = [
							[T_WHITESPACE, "\n"], 
							[T_DOC_COMMENT, $doc_comm],
							[T_WHITESPACE, "\n"],
							];
			array_splice($class_tok->children, $doc_comment_pos_before, 0, $insert_doc_comm_array);
		}
		
		$dump_str = "";
		$break_out = false;
		
		$file_tok->walk(function ($element, $pos, $parent) use (&$dump_str, &$break_out) {
			if (($element === '{') && ($parent instanceof \QPHPTokenClass))
			{
				$break_out = true;
				$dump_str .= (is_array($element) ? $element[1] : $element);
				return false;
			}
			else if ((!($element instanceof \QPHPToken)) && (!$break_out))
				$dump_str .= (is_array($element) ? $element[1] : $element);
		});
		
		if ($patch_renames)
		{
			foreach ($patch_renames as $pr_from => $pr_to)
			{
				$method_name_was_set = false;
				$method_str = "\n\t/**\n\t * @##upgraded_patch_rename {$pr_from} => {$pr_to}\n\t */\n\t";
				$prev_class_meth = $prev_toks_methods[$pr_from];
				if (!$prev_class_meth)
					throw new \Exception("Can not find method `{$pr_from}()` to patch in ".$layer.$file);
				foreach ($prev_class_meth->children as $child)
				{
					if ($child instanceof \QPHPTokenCode)
						break;
					else if (($child instanceof \QPHPTokenDocComment) || (is_array($child) && ($child[0] === T_DOC_COMMENT)))
					{
						// skip it
					}
					else if ((!$method_name_was_set) && is_array($child) && ($child[0] === T_STRING))
					{
						// function name
						$method_str .= $pr_to;
						$method_name_was_set = true;
					}
					else
						$method_str .= is_array($child) ? $child[1] : $child;
				}
				$method_str .= "{\n\t\treturn parent::{$pr_from}(...func_get_args());\n\t}\n";
				
				$class_tok->setMethodFromString($pr_to, $method_str, false);
			}
			
			// echo "<textarea style='height: 300px; width: 1200px; -moz-tab-size : 4;tab-size : 4;' wrap='off'>".htmlspecialchars($method_str)."</textarea>";
			// echo "<textarea style='height: 300px; width: 1200px; -moz-tab-size : 4;tab-size : 4;' wrap='off'>".htmlspecialchars((string)$class_tok)."</textarea>";
			
		}
		
		// all ok, save it !
		$write_to_file_name = null;
		$full_ext = substr(basename($file), strpos(basename($file), "."));
		if ($full_ext === '.php')
			$write_to_file_name = substr($file, 0, -4).".class.php";
		else if ($full_ext === '.patch.php')
			$write_to_file_name = substr($file, 0, -strlen($full_ext)).".class.php";
		else
		{
			qvar_dumpk($file, $full_ext, $write_to_file_name);
			throw new \Exception('Unexpected extension: '.$full_ext.' in: '.$layer.$file);
		}
		
		
		$write_to_file_content = (string)$file_tok;
		$upgrade_path = $this->upgrade_copy_file($layer, $write_to_file_name, $write_to_file_content);
		/**
		 *	1. rename it to CLASS_$layer_
		 *	2. inside it set ** @class.name CLASS *
		 *	3. patching classes must ` extend ` prev_patched class name if not first
		 */
		
		// update the cache for the next go
		foreach ($class_tok->methods ?: [] as $m_name => $c_meth)
			$toks_cache_methods[$full_class_name][$m_name] = $c_meth;
		
		$this->analyze_parent_calls($upgrade_path, $write_to_file_name, $write_to_file_content, $full_class_name, $layer, $file, $layer_tag);

		return $new_class_name;
	}
	
	public function upgrade_copy_file(string $layer, string $file, string $content = null)
	{
		$upgrade_path = $this->upgrage_dir . (substr($layer, strlen($this->upgrade_inside_dir))).$file;
		
		if (!is_dir(dirname($upgrade_path)))
			qmkdir(dirname($upgrade_path));
		
		if ($content !== null)
		{
			if (empty($content))
				throw new \Exception('ex');
			filePutContentsIfChanged($upgrade_path, $content);
		}
		else
		{
			if (!file_exists($layer.$file))
				throw new \Exception('ex: ' . $layer.$file);
			copy($layer.$file, $upgrade_path);
		}
		
		static::remove_gen_trait_in_use($upgrade_path);
		
		touch($upgrade_path, filemtime($layer.$file));
		
		return $upgrade_path;
	}
	
	function upgrade_backend_fix(array &$files, array &$changed_or_added)
	{
		$backend_full_path = realpath(QGEN_SaveDirBase);
		if (is_dir($backend_full_path))
		{
			$backend_full_path = $backend_full_path."/";
			
			$backend_existing_files = scandir($backend_full_path);
			foreach ($backend_existing_files as $lv_view)
			{
				if (($lv_view === '.') || ($lv_view === '..'))
					continue;
				
				if (!is_dir($backend_full_path.$lv_view.'/'))
					continue;

				$possible_class_path = $backend_full_path.$lv_view.'/'.$lv_view.'.class.php';
				$should_not_be_there_path = $backend_full_path.$lv_view.'/'.$lv_view.'.php';
				if (file_exists($should_not_be_there_path))
					throw new \Exception('Please remove legacy generated Grid/backend PHP files.');

				if (!file_exists($possible_class_path))
				{
					filePutContentsIfChanged($possible_class_path, "<?php

namespace Omi\\VF\\View;

/**
 * @class.name {$lv_view}
 */
class {$lv_view}_backend_ extends \\Omi\\View\\Grid 
{
	
}

");
					$file_m_time = filemtime($possible_class_path);
					$files[$backend_full_path][$lv_view.'/'.$lv_view.'.class.php'] = $file_m_time;
					$changed_or_added[$backend_full_path][$lv_view.'/'.$lv_view.'.class.php'] = $file_m_time;
				}
			}
		}
	}
	
	public static function upgrade_replace_constant_values(string $config_content, array $key_value)
	{
		$ret = $config_content;
		# jusy a reg-exp at the moment
		foreach ($key_value as $const_name => $const_value)
		{
			$ret = preg_replace_callback("/\\bconst\\s+".str_replace("\\", "\\\\", $const_name)."\\s*\\=\\s*[^\\;]+\\;/us", 
					function (array $match) use ($const_name, $const_value) {

						return "const {$const_name} = " . var_export($const_value, true) . ";";
					}, $ret);
		}
		# T_CONST / T_STRING::define - as a function
		# $toks = \QPHPToken::ParsePHPString($config_content);
		
		return $ret;
	}
	
	public static function remove_gen_trait_in_use(string $full_path)
	{
		$p_ext = pathinfo($full_path, PATHINFO_EXTENSION);
		$p_base = basename($full_path);
		if (($p_ext !== 'php') || (strtolower($p_base[0]) === $p_base[0]))
			return null; # no action
		
		$f_content = file_get_contents($full_path);
		$toks = token_get_all($f_content);
		$look_for_use = false;
		$use_tok = null;
		$use_tok_index = null;
		$inside_use = false;
		$inside_use_bra = false;
		
		$new_toks = [];
		$use_bits = [];
		$use_bits_pos = 0;
		
		$has_use_changes = false;
		
		foreach ($toks as $index => $tok)
		{
			$tok_type = is_array($tok) ? $tok[0] : $tok;
			
			if ($inside_use)
			{
				if ( ((!$inside_use_bra) && ($tok_type === ";")) || ($inside_use_bra && ($tok_type === "}")) )
				{
					$inside_use = false;
					$look_for_use = false;
					
					$new_use_bits = [];
					foreach ($use_bits as $use_trait)
					{
						if (substr($use_trait, -9) !== '_GenTrait')
							$new_use_bits[] = $use_trait;
					}
					
					if ($new_use_bits)
						$new_toks[] = "use ".implode(", ", $new_use_bits).";";
					
					if ($new_use_bits !== $use_bits)
						$has_use_changes = true;
				}
				else if ($tok_type === "{")
				{
					qvar_dumpk("WARN - Not supporting USE with {} / insteadof. Fix manually. {$full_path}");
					$inside_use_bra = true;
				}
				else if (!$inside_use_bra)
				{
					if ($tok === ',')
						$use_bits_pos++;
					else if ( ! (($tok_type === T_WHITESPACE) || ($tok_type === T_COMMENT) || ($tok_type === T_DOC_COMMENT)))
						$use_bits[$use_bits_pos] .= is_array($tok) ? $tok[1] : $tok;
				}
			}
			else if ($tok_type === T_CLASS)
			{
				$look_for_use = true;
				$new_toks[] = is_array($tok) ? $tok[1] : $tok;
			}
			else if ($look_for_use && (($tok_type === T_FUNCTION) || ($tok_type === T_CONST) || ($tok_type === T_PUBLIC) || ($tok_type === T_PROTECTED) || ($tok_type === T_PRIVATE)))
			{
				$inside_use = false;
				$look_for_use = false;
				if (!$use_tok) # no reason to loop any more
					break;

				$new_toks[] = is_array($tok) ? $tok[1] : $tok;
			}
			else if ($look_for_use && ($tok_type === T_USE))
			{
				$use_tok = $tok;
				$use_tok_index = $index;
				$inside_use = true;
			}
			else
				$new_toks[] = is_array($tok) ? $tok[1] : $tok;
		}
		if ((!$use_tok) || (!$has_use_changes))
			return null;
		else
		{
			$str_to_write = implode('', $new_toks);
			if (!strlen(trim($str_to_write)))
				throw new \Exception('ex');
			filePutContentsIfChanged($full_path, $str_to_write);
			return true;
		}
	}
	
	protected function analyze_parent_calls(string $converted_file_name, string $rel_file_name, string $converted_file_content, string $full_class_name, string $layer, string $file, string $layer_tag)
	{
		$m = null;
		if (preg_match("/\\bparent\\:\\:\\\$/uis", $converted_file_content))
		{
			throw new \Exception('`panret::$` - is not supported.');
		}
		else if (preg_match("/\\bparent\\:\\:\\s*([\\w\\_\\d]+)\\b/uis", $converted_file_content, $m))
		{
			# so the problem is if one of the parents is a .class.php with that method
			$this->upgrade_possible_parent_issues[] = $converted_file_name;
			# @TODO 
			# foreach ($m as $mi)
			# {
			#	echo "<span style='background-color: orange;'><b>parent::</b> ", $layer, $rel_file_name, " [pos:", $mi[0][1], "] - ", $mi[0][0], "</span><br/>\n";
			# }
			# qvar_dumpk($converted_file_name, $m, $converted_file_content);
			# die;
		}
		# else ... not an issue
	}
	
	public static function after_upgrade()
	{
		# \QAutoload::UnlockAutoload();
		
		$data = json_decode(file_get_contents(".upgrade_possible_parent_issues.json"));
		
		$layers_tags = \QAutoload::Get_WatchFoldersTag_To_Layer();
		
		$possible_issues = [];
		
		foreach ($data as $file)
		{
			$content = file_get_contents($file);
			
			$bn = basename($file);
			
			$toks = \QPHPToken::ParsePHPString($content, false, false);
			$class_toks = $toks->findFirstPHPTokenClass();
			
			$class_name = $class_toks->className;
			
			$short_class_name = substr($bn, 0, strpos($bn, "."));
			$layer_tag = (($class_name[strlen($class_name)-1] === '_') && ($class_name[strlen($short_class_name)] === '_')) ? 
							substr($class_name, strlen($short_class_name) + 1, -1) : null;
			
			$extends = $class_toks->extends;

			if ($layer_tag && $extends && 
					(substr($extends, 0, strlen($short_class_name)).'_' === $short_class_name.'_') && 
					($extends[strlen($extends)-1] === '_') &&
					($layers_tags[$layer_tag] || in_array($layer_tag, $layers_tags)))
			{
				# now the extended class must also be in the same scenario
				
				$m_meths = null;
				$rc = preg_match_all("/\\bparent\\:\\:\\s*([\\w\\d\\_]+)\\s*\\(\\s*(\\s*\\.\\.\\.func\\_get\\_args\\()?/uis", $content, $m_meths);
				
				# must avoid all :: return parent::CheckLogin(...func_get_args());
				
				# now ... this is only an issue if the method is defined on the parent 
				# here the reflection kicks in
				
				# qvar_dumpk("fcn", $toks->getNamespace(), $class_toks->getNamespace(), \QPHPToken::ApplyNamespaceToName($class_name, $toks->getNamespace()));
				$valid_meths = [];
				if ($rc && isset($m_meths[0]) && $m_meths[0])
				{
					foreach ($m_meths[0] as $k => $v)
					{
						if ($m_meths[2][$k] === '...func_get_args(') # not valid
							;
						else
							$valid_meths[$m_meths[1][$k]] = $m_meths[1][$k];
					}
				}
				
				if ($rc && $m_meths && $valid_meths)
				{
					
					$refl = new ReflectionClass(\QPHPToken::ApplyNamespaceToName($class_name, $toks->getNamespace()));
					$refl_parent = $refl;
					
					# qvar_dumpk('starting with ', $refl_parent->name, $short_class_name, $refl_parent->getParentClass()->getShortName());
					
					$parents_stack = [];
					while (($refl_parent = $refl_parent->getParentClass()) && 
								(substr($refl_parent->getShortName(), 0, strlen($short_class_name))."_" === $short_class_name."_") && 
								(substr($refl_parent->getShortName(), -1, 1) === '_'))
					{
						// qvar_dumpk('ok_parent', $refl_parent->name);
						$parents_stack[] = $refl_parent;
					}
					
					if ($parents_stack)
					{
						foreach ($valid_meths as $method_str)
						{
							# $meth_refl = $refl->getMethod($method_str);
							
							foreach ($parents_stack as $parent_refl)
							{
								$za_meth = $parent_refl->hasMethod($method_str) ? $parent_refl->getMethod($method_str) : null;
								if ($za_meth && ($za_meth->getDeclaringClass()->name === $parent_refl->name))
								{
									$possible_issues[$file] = $method_str;
								}
							}
						}
					}
				}
			}
		}
		
		qvar_dumpk('$possible_issues', $possible_issues);
	}
}

