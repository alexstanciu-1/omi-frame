<div>
		<?php
	if ($this->showClass)
	{
		$class_name = $this->showClass;
		$short_class = (($p = strrpos($this->showClass, "\\")) !== false) ? substr($this->showClass, $p + 1) : $this->showClass;
		$short_class = trim($short_class);
		$namespace = ($p !== false) ? substr($this->showClass, 0, $p) : null;
		
		$path = QAutoload::GetClassFileName($this->showClass);
		
		$is_patch = (substr($path, -strlen(".patch.php")) === ".patch.php");
		
		$ext_by_inf = QAutoload::GetExtendedByList();

		$is_qimodel = $ext_by_inf["QIModel"][$class_name] ? true : false;
		$is_controller = $ext_by_inf["QIUrlController"][$class_name] ? true : false;
		$is_web_control = ($ext_by_inf["QViewBase"][$class_name] || ($class_name === "QViewBase")) ? true : false;
		$trait_path = substr($path, 0, -3)."gen.php";
		$has_trait = ($trait_path !== $path) && file_exists($trait_path);
		if (!$has_trait)
			$trait_path = null;
		$js_path = substr($path, 0, -3)."js";
		$has_js = file_exists($js_path);
		if (!$has_js)
			$has_js = null;
		$css_path = substr($path, 0, -3)."css";
		$has_css = file_exists($css_path);
		if (!$css_path)
			$css_path = null;
		
		include(QAutoload::GetRuntimeFolder()."temp/namespaces.php");
		$namespace_cache = $_Q_FRAME_NAMESPACES_ARRAY;
		
		$possible_tpls = [];
		if ($is_web_control)
		{
			$possible_files = scandir(dirname($path));
			foreach ($possible_files as $pf)
			{
				$parts = explode(".", $pf);
				if ($parts[0] === $short_class)
				{
					if ((end($parts) === "tpl") && (count($parts) === 3))
						$possible_tpls[$parts[1]] = dirname($path)."/".$pf;
				}
			}
		}
		
		$controller_path = substr($path, 0, -3)."url.php";
		if (!file_exists($controller_path))
			$controller_path = null;
		$tpl_path = substr($path, 0, -3)."tpl";
		if (!file_exists($tpl_path))
			$tpl_path = null;
		
		$refl = new ReflectionClass($this->showClass);
		$class_obj = QPHPToken::ParsePHPFile($path)->findFirst(".QPHPTokenClass");
		echo "<h1 data-src='{$class_name}'>{$short_class} <span class='showFile' data-src='php' style=\"font-size: 15px; color: #05B2D2;\">[{$class_obj->type} <i class='fa fa-eye'></i>]</span>";
		if ($is_patch)
			echo " <span class='showFile' data-src='patch' style=\"font-size: 15px; color: #CC4C25;\">[patch <i class='fa fa-eye'></i>]</span>";
		if ($is_web_control)
			echo " <span class='showFile' data-src='tpl' style=\"font-size: 15px; color: #00A300;\">[web control <i class='fa fa-eye'></i>]</span>";
		else if ($is_qimodel)
			echo " <span class='showFile' data-src='php' style=\"font-size: 15px; color: #4F007C;\">[model <i class='fa fa-eye'></i>]</span>";
		if ($is_controller)
			echo " <span class='showFile' data-src='url' style=\"font-size: 15px; color: #AD2226;\">[url controller <i class='fa fa-eye'></i>]</span>";
		if ($has_trait)
			echo " <span class='showFile' data-src='gen' style=\"font-size: 15px; color: gray;\">[generated <i class='fa fa-eye'></i>]</span>";
		if ($has_js)
			echo " <span class='showFile' data-src='js' style=\"font-size: 15px; color: blue;\">[javascript <i class='fa fa-eye'></i>]</span>";
		if ($has_css)
			echo " <span class='showFile' data-src='css' style=\"font-size: 15px; color: orange;\">[css <i class='fa fa-eye'></i>]</span>";
		echo "</h1>";
		
		
		?><div class='smallHeadingCol'><h5>Path:</h5><div class='smallHeadingDataCol'><span style="color: black;"><?= dirname($path) ?></span></div></div><?php
		if ($namespace):
			?><div class='smallHeadingCol'><h5>Namespace:</h5><div class='smallHeadingDataCol'><span style="color: black;"><?= $namespace ?></span></div></div><?php	endif;
		
		if ($class_obj->extends)
		{
			$ext_full = QPHPToken::ApplyNamespaceToName($class_obj->extends, $namespace);
			
			?><div class='smallHeadingCol'><h5>Extends:</h5><div class='smallHeadingDataCol'><span style="color: black;"><a href="<?= $this->parent->url("classitem", $ext_full) ?>"><?= $ext_full ?></a></span></div></div><?php
		}
		
		$interfaces = $refl->getInterfaceNames();
		
		if ($interfaces)
		{
			echo "<div class='smallHeadingCol'><h5>Implements:</h5><div class='smallHeadingDataCol'>";
			$pos = 0;
			foreach ($interfaces as $implements)
			{
				if ($pos)
					echo ", ";
				?><span style="color: black;"><a href="<?= $this->parent->url("classitem", $implements) ?>"><?= $implements ?></a></span><?php
				$pos++;
			}
			echo "</div></div>";
		}
		
		$traits = $refl->getTraitNames();
		if ($traits)
		{
			echo "<div class='smallHeadingCol'><h5>Traits:</h5><div class='smallHeadingDataCol'>";
			$pos = 0;
			foreach ($traits as $trait)
			{
				if ($pos)
					echo ", ";
				?><span style="color: black;"><a href="<?= $this->parent->url("classitem", $trait) ?>"><?= $trait ?></a></span><?php
				$pos++;
			}
			echo "</div></div>";
		}
		
		if ($possible_tpls || $tpl_path)
		{
			echo "<div class='smallHeadingCol'><h5>Templates:</h5><div class='smallHeadingDataCol'>";
			echo " <span class='showFile' data-src='tpl' data-tag='' data-class='{$class_name}' style=\"font-size: 14px;\"><i>tpl</i></span>";
			foreach ($possible_tpls as $tpl_name => $tpl_path)
			{
				echo ", ";
				echo " <span class='showFile' data-src='tpl' data-tag='{$tpl_name}' data-class='{$class_name}' style=\"font-size: 14px;\">{$tpl_name}</span>";
			}
			echo "</div></div>";
		}
		
		echo "<div style='clear: both;'><!-- --></div>";

		$model_type = QModel::GetTypeByName($class_name);
		$model_type_info = QModelQuery::GetTypesCache($class_name);
		
		if (is_array($model_type))
		{
			var_dump($model_type);
			throw new Exception("Not good");
		}
		
		$id_column_name = null;
		
		if ($model_type_info)
		{
			$id_column_name = $model_type_info["#%id"][0];
			
			echo "<div class='smallHeadingCol'><h5 style='color: green;'>Storage</h5><div class='smallHeadingDataCol'>";
			
			$info = ["Table" => $model_type_info["#%table"],
					"Type Id" => QApp::GetStorage()->getTypeIdInStorage($class_name),
						"Id Column" => $id_column_name
					];
			if (is_string($model_type_info["#%id"][1]))
			{
				$info["Multiple types in table via column"] = $model_type_info["#%id"][1];
				$info["Possible types in table"] = implode(", ", $model_type_info["#%tables"][$model_type_info["#%table"]]["#"]);
			}
			else
				$info["Only one type in table"] = "(".$model_type_info["#%id"][1][0].") ".$model_type_info["#%id"][1][1];
			
			foreach ($info as $k => $v)
			{
				echo "<b>{$k}:</b> ".(is_array($v) ? implode(",", $v) : $v)."<br/>";
			}
			echo "</div></div>";
		}
		if ($model_type && $model_type->model)
		{
			echo "<div class='smallHeadingCol'><h5 style='color: green;'>Model</h5><div class='smallHeadingDataCol'>";
			foreach ($model_type->model as $k => $v)
			{
				echo "<b>{$k}:</b> ".(is_array($v) ? implode(", ", $v) : $v)."<br/>";
			}
			echo "</div></div>";
		}
		if ($model_type && $model_type->patch)
		{
			echo "<div class='smallHeadingCol'><h5 style='color: green;'>Patch</h5><div class='smallHeadingDataCol'>";
			foreach ($model_type->patch as $k => $v)
			{
				if ($k === "rename")
				{
					foreach ($v as $_vk => $_vv)
						echo "<b>{$k}:</b> {$_vk} => {$_vv}<br/>";
				}
				else
					echo "<b>{$k}:</b> ".(is_array($v) ? implode(", ", $v) : $v)."<br/>";
			}
			echo "</div></div>";
		}
		
		if (($class_docComment = $class_obj->docComment) || ($class_docComment = $refl->getDocComment()))
		{
			$print_doc_comment = null;
			$dc = QCodeStorage::parseDocComment($class_obj->docComment, true, $namespace, $print_doc_comment);
			if ($print_doc_comment)
			{
				echo "<div class='smallHeadingCol'><h5 style='color: green;'></h5><div class='smallHeadingDataCol'><hr style='margin-bottom: 5px; margin-top: 5px;'/>";
				echo nl2br($print_doc_comment);
				echo "</div></div>";
			}
		}

		echo "<div style='clear: both;'><!-- --></div><br/><hr style='margin-bottom: 5px; margin-top: 5px;'/>";
		
		// ROLE: All | Model, Web Control, Controller, 
		// anything special if it's a patch ?
		/*
		echo "<ul class='classElementsTabs'>";
		echo "<li class='selected'>All</li>";
		if ($class_obj->properties)
			echo "<li>Properties</li>";
		if ($class_obj->methods)
			echo "<li>Methods</li>";
		if ($class_obj->constants)
			echo "<li>Constants</li>";
		if ($is_web_control)
			echo "<li>Renders</li>";
		else if ($is_qimodel)
			echo "<li>Model</li>";
		if ($is_controller)
			echo "<li>URLs</li>";
		if ($has_trait)
			echo "<li>Generated</li>";
		if ($has_js)
			echo "<li>Javascript</li>";
		if ($has_css)
			echo "<li>CSS</li>";
		echo "</ul>";
		*/
		
		echo "<div style='clear: both;'><!-- --></div>";
		
		// inherited or not, in generated trait or not
		?><!-- <div class="searchClassElementsFilter"><br/>
			<table><tr><td style="padding-right: 20px;">Show: <a class="check-all">[all]</a> <a class="check-none">[none]</a></td>
		<td><input type="checkbox" checked="true" id="searchClassElementsSelf" autocomplete="off" /><label for="searchClassElementsSelf">self</label></td>
		<td><input type="checkbox" checked="true" id="searchClassElementsInherited" autocomplete="off" /><label for="searchClassElementsInherited">inherited</label></td>
		<td><input type="checkbox" checked="true" id="searchClassElementsNonStatic" autocomplete="off" /><label for="searchClassElementsNonStatic">non static</label></td>
		<td><input type="checkbox" checked="true" id="searchClassElementsStatic" autocomplete="off" /><label for="searchClassElementsStatic">static</label></td>
				</tr><tr><td colspan="1"></td>
		<td><input type="checkbox" checked="true" id="searchClassElementsPublic" autocomplete="off" /><label for="searchClassElementsPublic">public</label></td>
		<td><input type="checkbox" checked="true" id="searchClassElementsProtected" autocomplete="off" /><label for="searchClassElementsProtected">protected</label></td>
		<td><input type="checkbox" checked="true" id="searchClassElementsPrivate" autocomplete="off" /><label for="searchClassElementsPrivate">private</label></td>
		</tr>
		<tr><td colspan="1"></td>
		<td><input type="checkbox" checked="true" id="searchClassElementsPatch" autocomplete="off" /><label for="searchClassElementsPatch">patch</label></td>
		<td><input type="checkbox" checked="true" id="searchClassElementsGen" autocomplete="off" /><label for="searchClassElementsGen">generated</label></td>
		<td><input type="checkbox" checked="true" id="searchClassElementsModel" autocomplete="off" /><label for="searchClassElementsModel">model</label></td>
		<td><input type="checkbox" checked="true" id="searchClassElementsRender" autocomplete="off" /><label for="searchClassElementsRender">render</label></td>
		<td><input type="checkbox" checked="true" id="searchClassElementsUrl" autocomplete="off" /><label for="searchClassElementsUrl">url</label></td>
		</tr></table><br/></div>-->
			
		<table class='classElements'>
			<thead>
				<tr>
					<th style="min-width: 100px;"></th>
					<th nowrap><h5>Name</h5><input type="text" class="searchClassElements" placeholder="type to search in class" autocomplete="off" /></th>
					<th nowrap><h5>Type</h5></th>
					<!-- <th nowrap>Defined in</th>-->
					<th nowrap><h5>Description</h5></th>
				</tr>
			</thead>
			<tbody>
		<?php
		
		$trait_obj = null;
		if ($has_trait)
			$trait_obj = QPHPToken::ParsePHPFile($trait_path)->findFirst(".QPHPTokenClass");
		
		$refl_properties = $refl->getProperties();
		$all_props = [];
		if ($class_obj->properties)
		{
			foreach ($class_obj->properties as $name => $prop)
			{
				$this->setupPropertyFlags($prop, $namespace, $class_name, $is_patch);
				$all_props[$name] = $prop;
			}
		}
		if ($trait_obj && $trait_obj->properties)
		{
			foreach ($trait_obj->properties as $name => $prop)
			{
				if (!$all_props[$name])
				{
					$prop->defined_in = QPHPToken::ApplyNamespaceToName($trait_obj->className, $namespace);
					$this->setupPropertyFlags($prop, $namespace, $class_name."_GenTrait", $is_patch, true);
					$prop->from_generated = true;
					$all_props[$name] = $prop;
				}
			}
		}
		if ($refl_properties)
		{
			foreach ($refl_properties as $prop)
			{
				if (!$all_props[$prop->name])
				{
					$prop->defined_in = $prop->class;
					$prop_ns = $namespace_cache[$prop->class];
					$this->setupPropertyFlags($prop, $prop_ns, $class_name, $is_patch);
					$all_props[$prop->name] = $prop;
				}
			}
		}
		
		if ($all_props)
		{
			?><thead><tr><td colspan="10"><h4>Properties</h4></td></tr></thead><?php
			foreach ($all_props as $property)
			{
				if ($property->defined_in)
					$short_defined_in = (($p = strrpos($property->defined_in, "\\")) !== false) ? substr($property->defined_in, $p + 1) : $property->defined_in;
				$flags = "";
				
				$model_prop = $model_type ? $model_type->properties[$property->name] : null;
				
				echo "<tr class='data-idf' data-idf='".strtolower($property->name)."'><td nowrap>{$property->print_flags}</td>
						<td nowrap><span class='propertyName'><a onclick='\$ctrl(this).popupProperty(\"".qaddslashes($property->defined_in ?: $class_name)."\",\"{$property->name}\")'>{$property->name}</a></span></td>".
						"<td >{$property->print_types}</td>".
						/*"<td><a href='".$this->parent->url("classitem", $property->defined_in)."'>{$short_defined_in}</a></td>".*/
						"<td>".nl2br($property->print_comment)."</td></tr>";
						
				$prop_type_inf = $model_type_info[$property->name];
						
				if ($model_prop->storage || $model_prop->validation || $model_prop->fixValue || $prop_type_inf)
				{
					echo "<tr class='data-idf' data-idf='".strtolower($property->name)."'><td></td><td></td>";
					
					$add_tr = false;
					if ($model_prop->validation || $model_prop->fixValue)
					{
						$both = $model_prop->validation && $model_prop->fixValue;
						echo "<td colspan='3'>{$model_prop->fixValue}".($both ? "; " : "")."{$model_prop->validation}</td>";
						$add_tr = true;
					}
					
					if ($model_prop->storage || $prop_type_inf)
					{
						$storage_type = $model_prop->storage ? $model_prop->storage["type"] : null;
						
						if ($add_tr)
							echo "</tr><tr class='data-idf' data-idf='".strtolower($property->name)."'><td></td><td></td>";
						echo "<td colspan='3'>";
						
						if ($prop_type_inf)
						{
							$is_pk = ($prop_type_inf["vc"] === $id_column_name);
							if ($is_pk)
								echo "<b>Primary key: {$prop_type_inf["vc"]}</b><br/>";
							else if ($prop_type_inf["vc"])
								echo "<b>Scalar column: </b> ".$prop_type_inf["vc"]."<br/>";
							if ($prop_type_inf["rc"])
								echo "<b>Reference column: </b> ".$prop_type_inf["rc"]."<br/>";
							if ($storage_type)
								echo "<b>Field type: </b> {$storage_type}<br/>";
							$more_types = false;
							if ($prop_type_inf["rc_t"] || ($is_pk && $prop_type_inf["\$"]))
							{
								$eval_data = $is_pk ? $prop_type_inf["\$"] : $prop_type_inf["rc_t"];
								if (is_string($eval_data))
								{
									echo "<b>Multiple types via column: </b> ".$eval_data."<br/>";
									$more_types = true;
								}
								else
									echo "<b>Possible type: </b> ".implode(" / ", $eval_data)."<br/>";
							}
							if ($more_types && $prop_type_inf["refs"])
								echo "<b>Possible types: </b> ".implode(", ", $prop_type_inf["refs"])."<br/>";
							if ($prop_type_inf["j"])
							{
								foreach ($prop_type_inf["j"] as $j_table => $for_types)
								{
									echo "<b>Joins table `{$j_table}` for types: </b> ".implode(", ", $for_types)."<br/>";
								}
							}
							
							if ($prop_type_inf["[]"])
							{
								$one_to_many = $prop_type_inf["o2m"] ? true : false;
								
								if ($one_to_many)
									echo "<b>One to many via table: </b>";
								else
									echo "<b>Many to many via table: </b>";
								echo " ".$prop_type_inf["ct"]."<br/>";
								
								echo "<b>Column linking back to `{$short_class}`.`{$id_column_name}`:</b> `{$prop_type_inf["ct"]}`.`{$prop_type_inf["cb"]}`<br/>";
								
								list($fwd_tab_id, $fwd_tab_ty) = $prop_type_inf["cf"];
								if (!$one_to_many)
									echo "<b>Column linking forward:</b> `{$prop_type_inf["ct"]}`.`{$fwd_tab_id}`<br/>";
								if (is_array($fwd_tab_ty))
									echo "<b>One possible type in collection:</b> {$fwd_tab_ty[0]} / {$fwd_tab_ty[1]}<br/>";
								else
								{
									echo "<b>Type column for collection item:</b> `{$prop_type_inf["ct"]}`.`{$fwd_tab_id}`<br/>";
									if ($prop_type_inf["[]"]["refs"] && is_array($prop_type_inf["[]"]["refs"]))
										echo "<b>Possible types in collection:</b> ".implode(", ", $prop_type_inf["[]"]["refs"])."<br/>";
								}
							}
							
							if ($model_prop->storage)
							{
								if (($storagefilePath = $model_prop->storage["filePath"]))
									echo "<b>File path:</b> {$storagefilePath}<br/>";
								if (($storagefileMode = $model_prop->storage["fileMode"]))
									echo "<b>File mode:</b> {$storagefileMode}<br/>";
								if (($storagefileWithPath = $model_prop->storage["fileWithPath"]))
									echo "<b>Include file path in DB field</b><br/>";
							}
						}
						echo "</td>";
					}
					
					echo "</tr>\n";
				}
			}
		}
		
		// GRAB METHODS
		$refl_methods = $refl->getMethods();
		$all_meths = [];
		if ($class_obj->methods)
		{
			foreach ($class_obj->methods as $name => $meth)
			{
				$this->setupMethodFlags($meth, $namespace, $class_name, $is_patch, false, $refl);
				$all_meths[$name] = $meth;
			}
		}
		if ($trait_obj && $trait_obj->methods)
		{
			foreach ($trait_obj->methods as $name => $meth)
			{
				if (!$all_meths[$name])
				{
					$meth->defined_in = QPHPToken::ApplyNamespaceToName($trait_obj->className, $namespace);
					$this->setupMethodFlags($meth, $namespace, $class_name."_GenTrait", $is_patch, true, $refl);
					$meth->from_generated = true;
					$all_meths[$name] = $meth;
				}
			}
		}
		if ($refl_methods)
		{
			foreach ($refl_methods as $meth)
			{
				if (!$all_meths[$meth->name])
				{
					$meth->defined_in = $meth->class;
					$meth_ns = $namespace_cache[$meth->class];
					$this->setupMethodFlags($meth, $meth_ns, $class_name, $is_patch, false, $refl);
					$all_meths[$meth->name] = $meth;
				}
			}
		}
		
		if ($all_meths)
		{
			if (($meth_constructor = $all_meths["__construct"]))
			{
				unset($all_meths["__construct"]);
				array_unshift($all_meths, $meth_constructor);
			}
			
			?><thead><tr><td colspan="10"><br/><h4>Methods</h4></td></tr></thead><?php
			foreach ($all_meths as $method)
			{
				$flags = "";
				$short_defined_in = "";
				if ($method->defined_in)
					$short_defined_in = (($p = strrpos($method->defined_in, "\\")) !== false) ? substr($method->defined_in, $p + 1) : $method->defined_in;
				
				if (!$method->print_types)
					$method->print_types = "undef";
				
				$api_enabled = $model_type ? $model_type->methodHasApiAccess($method->name) : null;
				
				echo "<tr class='data-idf' data-idf='".strtolower($method->name)."'><td nowrap>{$method->print_flags}</td>
						<td colspan='4'><span class='methodName'><a onclick='\$ctrl(this).popupMethod(\"".qaddslashes($method->defined_in ?: $class_name)."\",\"{$method->name}\")'>{$method->name}</a></span>({$method->print_params}) : {$method->print_types}".($api_enabled ? " <span style='color: red;'>[API]</span>" : "")."</td>".
						"</tr><tr class='data-idf' data-idf='".strtolower($method->name)."'><td ></td><td ></td><td ></td>".
						/*"<td><a href='".$this->parent->url("classitem", $method->defined_in)."'>{$short_defined_in}</a></td>".*/
						"<td colspan='2'>".nl2br($method->print_comment)."</td></tr>\n";
			}
		}
		
		// GRAB CONSTANTS
		$refl_constants = $refl->getConstants();
		$all_constants = [];
		if ($class_obj->constants)
		{
			foreach ($class_obj->constants as $name => $constant)
			{
				$this->setupConstantFlags($constant, $namespace, $class_name, $is_patch, false, $refl);
				$all_constants[$name] = $constant;
			}
		}
		if ($trait_obj && $trait_obj->constants)
		{
			foreach ($trait_obj->constants as $name => $constant)
			{
				if (!$all_constants[$name])
				{
					$constant->defined_in = QPHPToken::ApplyNamespaceToName($trait_obj->className, $namespace);
					$constant->from_generated = true;
					$this->setupConstantFlags($constant, $namespace, $class_name."_GenTrait", $is_patch, true, $refl);
					$all_constants[$name] = $constant;
				}
			}
		}
		if ($refl_constants)
		{
			
			foreach ($refl_constants as $c_name => $constant)
			{
				if (!$all_constants[$c_name])
				{
					// to be found, somehow (in the future, for now we just list it empty 
					// defined('className::CONSTANT_NAME')
					$const = new stdClass();
					$const->name = $c_name;
					$const->value = $constant;
					$all_constants[$c_name] = $const;
				}
			}
		}
		
		if ($all_constants)
		{
			?><thead><tr><td colspan="10"><h4>Constants</h4></td></tr></thead><?php
			foreach ($all_constants as $const)
			{
				echo "<tr class='data-idf' data-idf='".strtolower($const->name)."'>";
				$flags = "";
				if ($const instanceof QPHPTokenClassConst)
				{
					$doc_comment = $const->docComment ? trim($const->toString($const->docComment)) : null;
					if ($doc_comment)
						QCodeStorage::parseDocComment($doc_comment, true, null, $doc_comment);
					$toks = $const->children;
					while (($tok = reset($toks)) && (!(is_array($tok) && ($tok[0] === T_CONST))))
						array_shift($toks);
					
					echo "<td>{$flags}</td><td colspan='3'>".$const->toString($toks)." // defined in this class</td>";
					if ($doc_comment)
						echo "</tr><tr><td colspan='2'></td><td colspan='3'>".nl2br($doc_comment)."</td>";
				}
				else
					echo "<td>{$flags}</td><td colspan='3'><span class='keyword'>const</span> {$const->name} = ".json_encode($const->value)."; // not defined in this class</td>";
				echo "</tr>\n";
			}
		}
		
		?></tbody></table><?php
		
	}
?></div>