<urls>
	<load><?php
		static::$User = \Omi\User::GetCurrentUser();
		if (!static::$FromAlias)
		{
			$ref = new \ReflectionClass(get_called_class());
			static::$FromAlias = $ref->getShortName();
		}

		$dataClass = \QApp::GetDataClass();

		static::$_USE_SECURITY_FILTERS = $dataClass::$_USE_SECURITY_FILTERS;
		$this->grid_params = [];
		$url_chunks = preg_split("/\\&/uis", $_SERVER['QUERY_STRING'], -1, PREG_SPLIT_NO_EMPTY);
		foreach ($url_chunks as $url_chk)
		{
			list ($uc_key, $uc_val) = preg_split("/\\=/uis", $url_chk, 2, PREG_SPLIT_NO_EMPTY);
			$uc_key_parts = preg_split("/(\\]?\\[)/uis", rtrim(urldecode($uc_key), "]"), -1, PREG_SPLIT_NO_EMPTY);
			$c_uc_key_parts = count($uc_key_parts);
			
			if ($c_uc_key_parts === 1)
				$this->grid_params[$uc_key] = urldecode($uc_val);
			else
			{
				$uc_arr_ref = &$this->grid_params[$uc_key_parts[0]];
				for ($uc_i = 1; $uc_i < $c_uc_key_parts; $uc_i++)
					$uc_arr_ref = &$uc_arr_ref[$uc_key_parts[$uc_i]];
				$uc_arr_ref = urldecode($uc_val);
				unset($uc_arr_ref);
			}
		}
		
		# OLD WAY - broken in case of `.` or space in name ... etc
		# $this->grid_params = filter_var_array($_GET); # we use $_GET in case the controller adds more stuff
		
		if ($this->grid_params && is_array($this->grid_params))
		{
			$ns = [];
			foreach ($this->grid_params as $k => $v)
			{
				if ($v === "")
					continue;
				$ns[$k] = $v;
			}
			$this->grid_params = $ns;
		}

		if ($_POST && $_POST["__submitted"])
		{
			unset($_POST["__submitted"]);
			unset($_POST["__tab"]);
			if (empty($_POST))
			{
				throw new \Exception("No data sumbited");
			}
			$this->prepareSubmit($_POST, $_FILES);
		}
		else if ($_GET && $_GET["__submitted"])
		{			
			unset($_GET["__submitted"]);
			if (empty($_GET))
			{
				throw new \Exception("No data sumbited");
			}
			$this->prepareSubmit($_GET);
		}

	?></load>
	<url tag="export">
		<get translate="export" />
		<test><?= (($url->current() == qTranslate("export")) && (!static::$_USE_SECURITY_FILTERS || (static::$User && static::$User->can("export", static::$FromAlias)))) ?></test>
		<load>
			<?php 
			$this->inExport = true;

			$this->grid_mode = $testResult;

			$remaining_url_chunks = \QUrl::$Requested->getFromCurrent();
			if (isset($remaining_url_chunks[2]) && $remaining_url_chunks[2])
			{
				$url_id = $remaining_url_chunks[2];
				$this->grid_mode = 'view';
				$this->grid_params["LIMIT"] = [0, $this->get_export_limit($url->current( +1 ))];
				$this->setupGrid("view", ($this->grid_id = $url_id), $this->grid_params);
			}
			else
			{
				$this->grid_params["LIMIT"] = [0, $this->get_export_limit($url->current( +1 ))];
				$this->setupGrid("list", $this->grid_id, $this->grid_params);
			}
			
			$url->next();
			
			if (!$url->current())
				die("restricted!");

			?>
		</load>
		<url tag='pdf'>
			<get translate="pdf" />
			<test><?= (($url->current() == qTranslate("pdf")) && (!static::$_USE_SECURITY_FILTERS || (static::$User && static::$User->can("export_pdf", static::$FromAlias)))) ?></test>
			<load>
				<?php 
				
				$this->exportPdf();
				die();

				?>
			</load>
		</url>
		<url tag='pdf-test'>
			<get translate="pdf-test" />
			<load>
				<?php
					$this->test_pdf();
					die();
				?>
			</load>
		</url>
		<url tag='excel'>
			<get translate="excel" />
			<test><?= (($url->current() == qTranslate("excel")) && (!static::$_USE_SECURITY_FILTERS || (static::$User && static::$User->can("export_excel", static::$FromAlias)))) ?></test>
			<load>
				<?php 

				$this->exportExcel();
				die();

				?>
			</load>
		</url>
		<url tag='csv'>
			<get translate="csv" />
			<test><?= (($url->current() == qTranslate("csv")) && (!static::$_USE_SECURITY_FILTERS || (static::$User && static::$User->can("export_csv", static::$FromAlias)))) ?></test>
			<load>
				<?php 
				if (!$url->next())
				{
					$this->exportCsv();
					die();
				}
				?>
			</load>
			<url tag="csv-item">
				<get param.0="id"><?= $id ?: null ?></get>
				<test><?= $url->current() ?: false ?></test>
				<load><?php
					$this->grid_id = $testResult;
					$this->exportItemToCsv();
					die();
				?></load>
			</url>
		</url>
	</url>
	<url tag="mode">
		<get param.0="mode"><?= ($mode === "list") ? "" : (in_array($mode, $this->getAllGridActions()) ? $mode : "") ?></get>
		<test><?= (in_array(($mode = ($url->current() ?: "list")), $this->getAllGridActions())  && (!static::$_USE_SECURITY_FILTERS || (static::$User && static::$User->can($mode, static::$FromAlias)))) ? $mode : false ?></test>
		<load><?php
			$this->grid_mode = $testResult;
			// on list, bulk and add we don't care about the rest of the url - we have match
			if (in_array($this->grid_mode, ["list", "bulk", "add"]))
			{
				return true;
			}
			$url->next();
		?></load>
		<url tag="id">
			<get param.0="id"><?= $id ?: null ?></get>
			<test><?= $url->current() ?: false ?></test>
			<load><?php
				$this->grid_id = $testResult;
				$url->next();
				return true;
			?></load>
		</url>
	</url>
	<url tag="add">
		<get><?= "add" ?></get>
	</url>
	<unload><?php

	if (!$_rv)
		return false;

	if (property_exists($this, "_is_reference") && $this->_is_reference)
	{
		$this->grid_mode = "edit";
		$this->app_reference = true;
	}

	/*
	if ($this->showPropertyDetails && $this->showPropertyDetails["#"])
	{
		$this->grid_mode = "edit";
		$this->app_reference = true;
	}
	*/

	if ($this->submitData)
	{
		$this->doSubmitData($this->submitData, $this->grid_mode, $this->grid_id);
		return $this->setupGrid($this->grid_mode, $this->grid_id, $this->grid_params);
	}
	else
	{
		return $this->setupGrid($this->grid_mode, $this->grid_id, $this->grid_params);
	}

	?></unload>
</urls>

