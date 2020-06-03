<div class="QDevModeApiGenCtrl" data-showclass="<?= $this->showClass ?>">
	<h3>APIs Manager</h3>
	
	<h5><?= $this->showClass ?: "Please select a class" ?></h5>
	<qCtrl qCtrl="QDevModeSelectCtrl" tag="classesDropDown" name="classesDropDown">
		<?php
			// If this PHP code block is not present then it is created and an instance of the control is created
			$this->addControl($classesDropDown);
			$classesDropDown->init();
			$classesDropDown->render();
		?>
		<init qArgs="$recursive = true"><?php
			$this->url_tag = "apigenitem";
			$this->drop_down = true;
			$this->class_filter = function ($class_name)
			{
				// check that is model and not a control
				return QModelQuery::GetTypesCache($class_name) ? true : false;
			};
		?></init>
	</qCtrl>
	<hr style="margin: 10px 0" />
	<?php
	
	if ($this->showClass)
	{
		$path = QAutoload::GetClassFileName($this->showClass);
		if ((!$path) || (!file_exists($path)))
			throw new Exception("Missing path for class: ".$this->showClass);
		$tok_file = QPHPToken::ParsePHPFile($path);
		if (!$tok_file)
			throw new Exception("Parse error");
		$tok_class = $tok_file->findFirstPHPTokenClass();
		if (!$tok_class)
			throw new Exception("Missing class in file: ".$path);
		$namespace = $tok_file->getNamespace();
		
		$doc_data_list = [];
		
		?><select class="methodSelection">
			<option value="___stdMethods___">CRUD Methods</option>
			<option disabled="true">--------------</option><?php
			if ($tok_class->methods)
			{
				foreach ($tok_class->methods as $name => $method)
				{
					$doc_comm = $method->docComment;
					if (!$doc_comm)
						continue;
					$doc_comm = is_array($doc_comm) ? $doc_comm[1] : $doc_comm;

					$extract_comment = null;
					$doc_data = QCodeStorage::parseDocComment($doc_comm, true, $namespace, $extract_comment);
					$has_api = $doc_data && $doc_data["api"];

					$doc_data_list[$name] = $doc_data;

					?><option value="<?= $name ?>"><?= $name ?>()<?= $has_api ? " :: API" : "" ?></option><?php
				}
			}?>
		</select>
		<button onclick="$ctrl(this).proposeMethods()">Propose Methods</button><?php
		
		/*
		if ($tok_class->methods)
		{
			foreach ($tok_class->methods as $name => $method)
			{
				$doc_data = $doc_data_list[$name];
				if ((!$doc_data) || (!$doc_data["api"]))
					continue;
				
				$str_meth_head = "";
				foreach ($method->children as $k)
				{
					if ($k instanceof QPHPTokenCode)
						break;
					$str_meth_head .= $method->toString($k);
				}
				echo "<div class='hightlight'>";
				highlight_string("<?php\n\t".trim($str_meth_head));
				echo "</div>";
			}
		}
		*/
		echo "<div class='hightlight'>";
		ob_start();
		$this->proposeMethods($this->showClass, "___stdMethods___");
		highlight_string("<?php\n".rtrim(ob_get_clean()));
		echo "</div>";
	
		// we need the methods defined here, or, in case of a patch ... hmm ... not really
	
		// ok, let's do the dew
		// next we need to pick a method ... how about the standards ? 
	
		
	}
	?>
</div>