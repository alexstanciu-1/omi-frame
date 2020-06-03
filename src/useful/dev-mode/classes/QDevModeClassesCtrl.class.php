<?php


/**
 * @class.name QDevModeClassesCtrl
 */
abstract class QDevModeClassesCtrl_frame_ extends QWebControl
{
	
	
	public function init($recurse = true)
	{
		parent::init($recurse);
		
		$this->autoloadData = QAutoload::GetAutoloadData();
		// var_dump($this->autoloadData);
	}
	
	public function setupMethodFlags($method, $namespace, $class_name, $is_patch, $is_generated_trait = false, ReflectionClass $refl_obj = null)
	{
		$flags = ["method" => "method"];
		$refl_meth = null;
		
		if ($method instanceof QPHPTokenFunction)
		{
			if (!$is_generated_trait)
			{
				$flags["def-self"] = "def-self";
				if ($is_patch)
					$flags["def-patch"] = "def-patch";
			}
			else
			{
				$flags["def-generated"] = "def-generated";
			}
			if ($method->static)
				$flags["static"] = "static";
			else
				$flags["instance"] = "instance";
			$flags[$method->visibility] = $method->visibility;
			$doc_comm = $method->docComment;
			if (is_array($doc_comm))
				$doc_comm = $doc_comm[1];
			
			$refl_meth = $refl_obj->getMethod($method->name);
		}
		else if ($method instanceof ReflectionMethod)
		{
			$refl_meth = $method;
			
			$flags["def-inherit"] = "def-inherit";
			if ($method->isStatic())
				$flags["static"] = "static";
			else
				$flags["instance"] = "instance";
			
			if ($method->isPrivate())
				$flags["private"] = "private";
			else if ($method->isPublic())
				$flags["public"] = "public";
			else if ($method->isProtected())
				$flags["protected"] = "protected";
			$doc_comm = $method->getDocComment();
		}
		
		// @todo renderMeth, urlMeth
		
		$print_flags = "";
		if ($flags["static"])
			$print_flags .= "<span title='Static method'><i class='fa fa-usd'></i></span> ";
		else
			$print_flags .= "<span title=''><i class='fa fa-usd' style='color: transparent;'></i></span> ";
		if ($flags["public"])
			$print_flags .= "<span title='Public method'><i class='fa fa-eye'></i></span> ";
		if ($flags["protected"])
			$print_flags .= "<span title='Protected method'><i class='fa fa-unlock'></i></span> ";
		if ($flags["private"])
			$print_flags .= "<span title='Private method'><i class='fa fa-lock'></i></span> ";
		
		if ($flags["def-self"])
			$print_flags .= "<span title='Method defined in this class'><i class='fa fa-home'></i></span> ";
		if ($flags["def-inherit"])
			$print_flags .= "<span title='Method is inherited'><a href='".$this->parent->url("classitem", $method->defined_in)."'><i class='fa fa-share-alt'></i></a></span> ";
		if ($flags["def-patch"])
			$print_flags .= "<span title='Method was patched'><i class='fa fa-code-fork'></i></span> ";
		if ($flags["def-generated"])
			$print_flags .= "<span title='Method was generated'><i class='fa fa-cogs'></i></span> ";
		
		$extract_comment = null;
		$doc_comm = QCodeStorage::parseDocComment($doc_comm, true, $namespace, $extract_comment);
		
		if ($extract_comment)
			$method->print_comment = $extract_comment;
		
		if ($doc_comm["return"] && $doc_comm["return"]["type"])
			$method->print_types = $this->printType($doc_comm["return"]["type"]);
		
		$print_params = "";
		$refl_params = $refl_meth->getParameters();
		if ($refl_params)
		{
			$pos = 0;
			foreach ($refl_params as $param)
			{
				if ($pos)
					$print_params .= ", ";
				
				// Parameter #0 [ <required> MyCompany\Ecomm\Model\Order $order ]
				$param_matches = null;
				$forced_type = null;
				if (preg_match('/\[\s\<\w+?>\s([\w]+)/s', $param->__toString(), $param_matches))
				{
					$forced_type = $param_matches[1];
				}
				
				$prepend = "";
				$default_value = null;
				
				if ($param->allowsNull())
					$prepend = " = null";
				else if ($param->isDefaultValueAvailable())
				{
					$default_value = $param->getDefaultValue();
					if ((!$default_value) && $param->isDefaultValueConstant())
						$default_value = $param->getDefaultValueConstantName();
					
					$prepend = $default_value ? " = ".json_encode($default_value) : "";
				}	
				
				$param_data = $doc_comm["params"] && $doc_comm["params"]["\$".$param->name] ? $doc_comm["params"]["\$".$param->name] : null;
				if ($param_data)
				{
					// name, type, comment
					$print_params .= $this->printType($param_data["type"])." ";
				}
				else if ($forced_type)
					$print_params .= $this->printType($forced_type)." ";
				
				$print_params .= "<span>".($param->isPassedByReference() ? "&" : "")."\${$param->name}</span>{$prepend}";
				$pos++;
			}
		}
		
		$method->docsPrintFlags = $flags;
		$method->print_flags = $print_flags;
		$method->print_params = $print_params;
	}
	
	public function setupPropertyFlags($property, $namespace, $class_name, $is_patch, $is_generated_trait = false)
	{
		// property, method, constant, modelProp, def-self, def-inherit, def-patch, def-generated, static, instance, public, protected, private, renderMeth, urlMeth
		$flags = ["property" => "property"];
		// var_dump($class_name);
		$model_type = QModelQuery::GetTypesCache($class_name);
		// var_dump($model_type[$property->name]);
		if ($model_type && $model_type[$property->name])
			$flags["modelProp"] = "modelProp";
		
		$doc_comm = null;
		
		if ($property instanceof QPHPTokenProperty)
		{
			if (!$is_generated_trait)
			{
				$flags["def-self"] = "def-self";
				if ($is_patch)
					$flags["def-patch"] = "def-patch";
			}
			else
			{
				$flags["def-generated"] = "def-generated";
			}
			if ($property->static)
				$flags["static"] = "static";
			else
				$flags["instance"] = "instance";
			$flags[$property->visibility] = $property->visibility;
			$doc_comm = $property->docComment;
			if (is_array($doc_comm))
				$doc_comm = $doc_comm[1];
		}
		else if ($property instanceof ReflectionProperty)
		{
			$flags["def-inherit"] = "def-inherit";
			if ($property->isStatic())
				$flags["static"] = "static";
			else
				$flags["instance"] = "instance";
			
			if ($property->isPrivate())
				$flags["private"] = "private";
			else if ($property->isPublic())
				$flags["public"] = "public";
			else if ($property->isProtected())
				$flags["protected"] = "protected";
			
			$doc_comm = $property->getDocComment();
		}
		
		$print_flags = "";
		if ($flags["static"])
			$print_flags .= "<span title='Static property'><i class='fa fa-usd'></i></span> ";
		else
			$print_flags .= "<span title=''><i class='fa fa-usd' style='color: transparent;'></i></span> ";
		if ($flags["public"])
			$print_flags .= "<span title='Public property'><i class='fa fa-eye'></i></span> ";
		if ($flags["protected"])
			$print_flags .= "<span title='Protected property'><i class='fa fa-unlock'></i></span> ";
		if ($flags["private"])
			$print_flags .= "<span title='Private property'><i class='fa fa-lock'></i></span> ";
		
		if ($flags["def-self"])
			$print_flags .= "<span title='Property defined in this class'><i class='fa fa-home'></i></span> ";
		if ($flags["def-inherit"])
			$print_flags .= "<span title='Property is inherited'><a href='".$this->parent->url("classitem", $property->defined_in)."'><i class='fa fa-share-alt'></i></a></span> ";
		if ($flags["def-patch"])
			$print_flags .= "<span title='Property was patched'><i class='fa fa-code-fork'></i></span> ";
		if ($flags["def-generated"])
			$print_flags .= "<span title='Property was generated'><i class='fa fa-fire'></i></span> ";
		if ($flags["modelProp"])
			$print_flags .= "<span title='Model property'><i class='fa fa-table'></i></span> ";
		
		// var_dump($doc_comm);
		$extract_comment = null;
		$doc_comm = QCodeStorage::parseDocComment($doc_comm, true, $namespace, $extract_comment);
		if ($extract_comment)
			$property->print_comment = $extract_comment;
		
		$property->print_types = $doc_comm["types"] ? $this->printType($doc_comm["types"]) : "";
		
		$property->docsPrintFlags = $flags;
		$property->print_flags = $print_flags;
	}
	
	public function setupConstantFlags($constant, $namespace, $class_name, $is_patch, $is_generated_trait = false)
	{
		// property, method, constant, modelProp, def-self, def-inherit, def-patch, def-generated, static, instance, public, protected, private, renderMeth, urlMeth
		$flags = ["constant" => "constant"];
		$doc_comm = null;
		
		if ($constant instanceof QPHPTokenClassConst)
		{
			if (!$is_generated_trait)
			{
				$flags["def-self"] = "def-self";
				if ($is_patch)
					$flags["def-patch"] = "def-patch";
			}
			else
			{
				$flags["def-generated"] = "def-generated";
			}
			
			$doc_comm = $constant->docComment;
			if (is_array($doc_comm))
				$doc_comm = $doc_comm[1];
		}
		else 
		{
			var_dump($constant);
			/*$flags["def-inherit"] = "def-inherit";
			if ($property->isStatic())
				$flags["static"] = "static";
			else
				$flags["instance"] = "instance";
			
			if ($property->isPrivate())
				$flags["private"] = "private";
			else if ($property->isPublic())
				$flags["public"] = "public";
			else if ($property->isProtected())
				$flags["protected"] = "protected";
			$doc_comm = $property->getDocComment();*/
		}
		
		/*
		$print_flags = "";
		if ($flags["static"])
			$print_flags .= "<span title='Static property'><i class='fa fa-usd'></i></span> ";
		else
			$print_flags .= "<span title=''><i class='fa fa-usd' style='color: transparent;'></i></span> ";
		if ($flags["public"])
			$print_flags .= "<span title='Public property'><i class='fa fa-eye'></i></span> ";
		if ($flags["protected"])
			$print_flags .= "<span title='Protected property'><i class='fa fa-unlock'></i></span> ";
		if ($flags["private"])
			$print_flags .= "<span title='Private property'><i class='fa fa-lock'></i></span> ";
		
		if ($flags["def-self"])
			$print_flags .= "<span title='Property defined in this class'><i class='fa fa-home'></i></span> ";
		if ($flags["def-inherit"])
			$print_flags .= "<span title='Property is inherited'><a href='".$this->parent->url("classitem", $property->defined_in)."'><i class='fa fa-share-alt'></i></a></span> ";
		if ($flags["def-patch"])
			$print_flags .= "<span title='Property was patched'><i class='fa fa-code-fork'></i></span> ";
		if ($flags["def-generated"])
			$print_flags .= "<span title='Property was generated'><i class='fa fa-fire'></i></span> ";
		if ($flags["modelProp"])
			$print_flags .= "<span title='Model property'><i class='fa fa-table'></i></span> ";
		
		// var_dump($doc_comm);
		$extract_comment = null;
		$doc_comm = QCodeStorage::parseDocComment($doc_comm, true, $namespace, $extract_comment);
		if ($extract_comment)
			$property->print_comment = $extract_comment;
		
		$property->print_types = $doc_comm["types"] ? $this->printType($doc_comm["types"]) : "";
		
		$property->docsPrintFlags = $flags;
		$property->print_flags = $print_flags;
		 * 
		 */
	}
	
	protected function printType($type)
	{
		$ret = "";
		if (is_string($type))
		{
			// we do it
			if (strtolower($type{0}) === $type{0})
			{
				// scalar
				$ret .= "<span class='scalar-type {$type}'>{$type}</spane>";
			}
			else // object
			{
				$short_class = (($p = strrpos($type, "\\")) !== false) ? substr($type, $p + 1) : $type;
				$ret .= "<a class='reference-type' href='".$this->parent->url("classitem", $type)."'>{$short_class}</a>";
			}
		}
		else if ($type instanceof QModelAcceptedType)
		{
			$pos = 0;
			$ty_cnt = count($type->options);
			if ($ty_cnt > 1)
				$ret .= "(";
			foreach ($type->options as $t)
			{
				if ($pos)
					$ret .= "| ";
				$ret .= $this->printType($t);
				$pos++;
			}
			if ($ty_cnt > 1)
				$ret .= ")";
			$ret .= "[]";
		}
		else if (qis_array($type))
		{
			$pos = 0;
			foreach ($type as $t)
			{
				if ($pos)
					$ret .= "| ";
				$ret .= $this->printType($t);				
				$pos++;
			}
		}
		return $ret;
	}
	
	/**
	 * @api.enable
	 * 
	 * @param string $class_name
	 * @param string $property
	 * 
	 * @return string
	 */
	public function getSourceForProperty($class_name, $property, $is_generated = null)
	{
		if (QApp::$UrlController->demoMode || \QApp::$DemoMode)
			return "";
		
		$path = QAutoload::GetClassFileName($class_name);
		$code = QPHPToken::ParsePHPFile($path);
		$code_code = $code ? $code->findFirstCodeElement() : null;
		$code_class = $code_code ? $code_code->findFirstPHPTokenClass() : null;
		$code_prop = $code_class ? $code_class->properties[$property] : null;
		
		if ((!$is_generated) && (!$code_prop) && (!(preg_match("/_GenTrait\$/us", $class_name))))
			return $this->getSourceForProperty($class_name."_GenTrait", $property, true);
		return $code_prop ? highlight_string("<?php // {$class_name} :: {$path} \n".$code_prop->toString(), true) : "";
	}
	
	/**
	 * @api.enable
	 * 
	 * @param string $class_name
	 * @param string $method
	 * 
	 * @return string
	 */
	public function getSourceForMethod($class_name, $method, $is_generated = null)
	{
		if (QApp::$UrlController->demoMode || \QApp::$DemoMode)
			return "";
		
		$path = QAutoload::GetClassFileName($class_name);
		$code = QPHPToken::ParsePHPFile($path);
		$code_code = $code ? $code->findFirstCodeElement() : null;
		$code_class = $code_code ? $code_code->findFirstPHPTokenClass() : null;
		$code_method = $code_class ? $code_class->methods[$method] : null;
		
		if ((!$is_generated) && (!$code_method) && (!(preg_match("/_GenTrait\$/us", $class_name))))
			return $this->getSourceForMethod($class_name."_GenTrait", $method, true);
		return $code_method ? highlight_string("<?php // {$class_name} :: {$path} \n".$code_method->toString(), true) : "";
	}
	
	/**
	 * @api.enable
	 * 
	 * @param string $class_name
	 * @param string $mode
	 * 
	 * @return string
	 */
	public function getSourceFor($class_name, $mode, $tag)
	{
		if (QApp::$UrlController->demoMode || \QApp::$DemoMode)
			return "";

		ob_start();
		
		// var_dump($class_name, $mode, $tag);
		
		$path = QAutoload::GetClassFileName($class_name);
		if ($mode === "url")
		{
			$controller_path = substr($path, 0, -3)."url.php";
			if (!file_exists($controller_path))
				$controller_path = null;
			else
				$path = $controller_path;
		}
		else if ($mode === "tpl")
		{
			$tpl_path = substr($path, 0, -3).($tag ? $tag."." : "")."tpl";
			if (!file_exists($tpl_path))
				$tpl_path = null;
			else
				$path = $tpl_path;
		}
		else if ($mode === "gen")
		{
			$gen_path = substr($path, 0, -3)."gen.php";
			if (!file_exists($gen_path))
				$gen_path = null;
			else
				$path = $gen_path;
		}
		else if ($mode === "js")
		{
			$gen_path = substr($path, 0, -3)."js";
			if (!file_exists($gen_path))
				$gen_path = null;
			else
				$path = $gen_path;
		}
		else if ($mode === "css")
		{
			$gen_path = substr($path, 0, -3)."css";
			if (!file_exists($gen_path))
				$gen_path = null;
			else
				$path = $gen_path;
		}
		
		$out = ob_get_clean();
		
		return $out.highlight_file($path, true);
	}
	
	public function getDocsCachePath($class = null)
	{
		$class = $class ?: $this->showClass;
		$docs_path = QAutoload::GetRuntimeFolder()."temp/docs/";
		if (!is_dir($docs_path))
			qmkdir($docs_path);
		
		// watch: self, trait, css, js
		$parents_list = QAutoload::GetClassParentsList();
		$parents = [];
		foreach ($parents_list as $p_data)
		{
			foreach ($p_data as $pclass => $parent)
				$parents[$pclass] = $parent;
		}
		
		$watch_paths = [];
		$has_changes = false;
		
		$d_path = $docs_path.str_replace("\\", "-", $class).".docs.php";
		$s_path = $docs_path.str_replace("\\", "-", $class).".state.php";
		if (!(file_exists($d_path) && file_exists($s_path)))
		{
			$has_changes = true;
			$class_paths = [];
		}
		else
		{
			include($s_path);
			// $class_paths; will be filled here
		}

		while ($class)
		{
			$path = QAutoload::GetClassFileName($class);
			$trait_path = substr($path, 0, -3)."gen.php";
			$js_path = substr($path, 0, -3)."js";
			$css_path = substr($path, 0, -3)."css";
			
			// current state of paths
			$watch_paths[$path] = file_exists($path) ? filemtime($path) : false;
			$watch_paths[$trait_path] = file_exists($trait_path) ? filemtime($trait_path) : false;
			$watch_paths[$js_path] = file_exists($js_path) ? filemtime($js_path) : false;
			$watch_paths[$css_path] = file_exists($css_path) ? filemtime($css_path) : false;

			if ((!$has_changes) && $class_paths)
			{
				if (($watch_paths[$path] !== $class_paths[$path]) ||
						($watch_paths[$trait_path] !== $class_paths[$trait_path]) ||
						($watch_paths[$js_path] !== $class_paths[$js_path]) ||
						($watch_paths[$css_path] !== $class_paths[$css_path]))
				{
					$has_changes = true;
				}
			}

			// now on parents
			$class = $parents[$class];
		}
		
		// var_dump($has_changes);
		
		if ($has_changes)
		{
			// build the cache file and return it
			QCodeSync::filePutContentsIfChanged($s_path, qArrayToCode($watch_paths, "class_paths"));
			ob_start();
			$this->renderDocs();
			$docs_contents = ob_get_clean();
			file_put_contents($d_path, $docs_contents);
		}
		
		return $d_path;
		
		// we need to get all dependencies in a tree for sync to make this more solid !!
		// $path = QAutoload::GetClassFileName($class);
		
		/**
		 * a - get a list with all the files that would influence this
		 * b - from filestate pull a list 
		 * c - when changed resync, no change ... load from cache
		 * d - on full sync clean docs cache
		 */
	
		
	}
}
