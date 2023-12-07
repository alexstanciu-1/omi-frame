<?php

namespace Omi\View;

trait Grid_Methods
{
	public function renderSideMenu()
	{
		?>
		<div class="<?= $this->sideMenuItems ? '' : 'navigation-blocked ' ?>navigate-to m-bottom-20" id="-nav-dd">
			<span><?= _L($this->sideMenuSelected ?: $this->from) ?></span>
			<?php if ($this->sideMenuItems) : ?>
				<ul class="dropdown">
				<?php foreach ($this->sideMenuItems as $itmProp => $itmData) : 

						$has_children = ($itmData["items"] && (count($itmData["items"]) > 0));
						$url = null;
						if (!$has_children)
						{
							$url = isset($itmData["url"]) ? $itmData["url"] : qUrl('p-adminitem', $itmProp);
							$url = rtrim($url, "\\/");
						}
					?>
					<li>
						<a<?= $url ? ' class="qc-rdr" href="'.$url.'"' : '' ?>><?= $itmData["caption"] ? $itmData["caption"] : $itmProp ?></a>
					</li>
				<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
		<?php
	}
	/**
	 * @return string
	 */
	public function getUrlFilters($params = null)
	{
		$url_get = qbArrayToUrl($params ? array_merge($_GET, $params) : $_GET);
		return ($ug = trim($url_get, "&")) ? "?".$ug : "";
	}

	/**
	 * Lists all the possible render modes for this grid
	 * 
	 * @return string[]
	 */
	public function getAllGridActions()
	{
		//return ["list", "bulk", "add", "edit", "merge", "view", "delete"];
		// deactivate bulk for now
		return ["list", "add", "edit", "merge", "view", "delete", "bulk"];
	}
	/**
	 * Gets a data selector based on a grid mode 
	 * 
	 * @param string $grid_mode
	 * @return string|(string|array)[]
	 */
	public function getSelectorForMode($grid_mode)
	{
		return ($this->selectors && ($selector = $this->selectors[$grid_mode])) ? $selector : null;
	}
	/**
	 * Gets the data for the grid
	 * 
	 * @api.enable
	 * 
	 * @param string $grid_mode
	 * @param scalar $id
	 * @param (scalar|array)[] $bind_param
	 * @return QIModel|QIModel[]
	 * @throws Exception
	 */
	public function getData($grid_mode, $id = null, $bind_param = null)
	{
		try
		{
			\QTrace::Begin_Trace([], ['$grid_mode' => $grid_mode, '$id' => $id, 
				'$bind_param' => $bind_param, ], ["grid", "get", "data"]);
			
			$selector = $this->getSelectorForMode($grid_mode);
			$data = static::GetListData($this->app_reference ? "list" : $grid_mode, $this->from, $id, $bind_param, $selector, $this->settings, $this);
			return $data;
		}
		finally
		{
			\QTrace::End_Trace([], ['$return' => $data]);
		}
	}
	/**
	 * Decode bind params
	 * 
	 * @param type $bind_param
	 * @return string
	 */
	protected static function GetInSerachBinds($bind_param)
	{
		if (empty($bind_param) || (!isset(static::$CONFIG)) || (!($_scfg = static::$CONFIG['_QSEARCH_'])))
			return $bind_param;		

		$search_params = [];
		foreach ($bind_param as $k => $v)
		{
			if ($k[0] == "_")
				continue;

			if (($__sprop = (($_scfg && $_scfg[$k]) ? $_scfg[$k] : null)))
			{
				$__sprop = q_reset($__sprop);
				if ((list($_prop, $qinsop) = $__sprop) && $_prop)
				{
					$k = $_prop;
					$_op = \QQueryAnalyzer::GetSearchOpByCode($qinsop);
					switch ($_op)
					{
						case "LIKE" :
						{
							$v = "%".preg_replace("/ /", "%", $v)."%";
							break;
						}
						default :
						{
							break;
						}
					}
				}
			}
			$search_params[$k] = $v;
		}

		return $search_params;
	}
	/**
	 * export grid to pdf - only list for now
	 */
	public function exportPdf()
	{		
		ob_start();
		$this->renderPdfExportPage();
		$str = ob_get_clean();		
		\QApi::Call('\Omi\Util\Pdf::Download', $str, $this->pdfExportFileName ?: (static::$FromAlias ?: $this->from).".pdf", \Omi\Util\Pdf::Landscape);
	}
	/**
	 * Export grid data to excel - only list for now
	 */
	public function exportExcel()
	{
		\Omi\Util\Excel::Download($this->getExcelExportTemplate(), $this->getExportExcelBlocks(), 
			null, $this->excelExportFileName ?: (static::$FromAlias ?: $this->from).".xlsm");
	}

	public function exportItemToCsv()
	{
		
	}

	/**
	 * Export grid data to excel - only list for now
	 */
	public function exportCsv()
	{
		$selector = $this->getSelectorForMode("csv");
		# foreach ($this->data ?: [] as $itm)
		if ($selector === null)
			$selector = $this->getSelectorForMode("list");
		
		$file = "temp/".sha1(uniqid("", true)).".csv";
		\QModel::ToCsvFile($file, $this->data, $selector);

		header('Content-Description: File Transfer');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-type: application octet-stream');
		header('Content-Disposition: attachment; filename="' . $this->from . ' export.csv"');
		readfile($file);
		@unlink($file);
		die();
	}
	/**
	 * @return string
	 * @throws \Exception
	 */
	public function getExcelExportTemplate()
	{
		# $modsDir = dirname(dirname(dirname(dirname(__FILE__))));
		$modsDir = Omi_Mods_Path;
		
		$gridExcelTemplate = rtrim($modsDir, "\\/")."/common/res/export/templates/excel/grid/Grid.xlsm";
		if (!file_exists($gridExcelTemplate))
			throw new \Exception("Grid template not found! {$gridExcelTemplate}!");
			
		return $gridExcelTemplate;
	}
	/**
	 * Returns blocks to be set on excel export templates
	 * 
	 * @return array
	 */
	public function getExportExcelBlocks()
	{
		$blocks = [
			"Captions" => $this->getExportExcelCaptions(),
			"Rows" => $this->getExportExcelRows()
		];
		
		//qvardump($blocks);
		//die();

		return $blocks;
	}
	/**
	 * Returns export excel captions
	 * 
	 * @return array
	 */
	public function getExportExcelCaptions()
	{
		list($headings, $headings_rates, $heading_rates_total, $heading_props) = $this->getHeadingsData();
		
		$captions = [];
		$pos = 0;
		if ($headings && (count($headings) > 0))
		{
			
			foreach ($headings as $heading)
				$captions["p" . ($pos++)] = $heading;
		}
		
		return [$captions];
	}
	
	protected function getExcelSelector()
	{
		$appModel = \QApp::GetDataClass();
		$from = static::$FromAlias ?: $this->from;
		return method_exists($appModel, "GetEntityForGenerateList") ? 
			$appModel::GetEntityForGenerateList($from) : $appModel::GetPropertyListingEntity($from);
	}

	/**
	 * Returns headings data
	 * Pull data from generator
	 * 
	 * @return array
	 */
	public function getHeadingsData($selector = null, $extra_selector = null)
	{
		if ($this->_headingsData)
			return $this->_headingsData;
		$appModel = \QApp::GetDataClass();
		if ($selector === null)
		{
			$selector = method_exists($appModel, "GetEntityForGenerateList") ? 
				$appModel::GetEntityForGenerateList_Final(static::$FromAlias) : $appModel::GetPropertyListingEntity(static::$FromAlias);
		}
		
		if ($extra_selector !== null)
			$selector = qJoinSelectors($selector, $extra_selector);
		
		// unset selector * from query
		if ($selector && is_array($selector) && ($selector["*"] !== null))
			unset($selector["*"]);

		$qm_prop = \QModel::GetTypeByName($appModel)->properties[$this->from]->getCollectionType();
		$model_name = $qm_prop->getAllInstantiableReferenceTypes();
		if (!$model_name)
			$model_name = $qm_prop->options;
		// get heading props

		$headings = [];
		$heading_rates = [];

		if (is_array($model_name))
			$model_name = q_reset($model_name);

		$headings_props = [];

		$m_ty = \QModel::GetTypeByName($model_name);
		$view = end(explode("\\", get_class($this)));

		// go through each selector
		if ($selector && count($selector) > 0)
		{
			foreach ($selector as $k => $s)
			{
				$prop = $m_ty->properties[$k];
				if (!$prop)
					continue;

				$h = \QModel::GetCaption($k, $model_name, $k, $view);
				$headings[] = $h;
				$headings_props[] = $prop;
			}
		}

		return $this->_headings = [$headings, $heading_rates, null, $headings_props];
	}
	/**
	 * Returns rows for the excel export
	 * 
	 * @return array
	 */
	public function getExportExcelRows()
	{
		$rows = [];
		list($headings, $headings_rates, $heading_rates_total, $heading_props) = $this->getHeadingsData();
		
		foreach ($this->data ?: [] as $itm)
		{
			$pos = 0;
			$row = [];
			foreach ($headings as $indx => $heading)
			{
				$prop = $heading_props[$indx];
				$pName = $prop->name;
				$value = $this->getExcelExportValue($itm, $pName, $indx, $heading);

				// to check the property if reference/collection, etc
				if (!is_scalar($value))
				{
					if ($value instanceof \QModel)
					{
						$value = $value->getModelCaption();
					}
					else if ($value instanceof \QIModelArray)
					{
						$value = \QModel::Get_Data_Model_Caption($value);
					}
					else
						$value = "";
				}

				$enum_c = $itm->getEnumCaption($pName);
				if ($enum_c)
					$value = $enum_c;

				$row["p" . ($pos++)] = $value;
			}
			
			$rows[] = $row;

		}
		return $rows;
	}

	protected function getExcelExportValue($itm, $pName, $indx, $heading)
	{
		return $itm->{$pName};
	}

	protected function prepareCsvImport($data)
	{
		if (!$this->from)
			throw new \Exception("From not found!");

		$type = $data["Type"];
		unset($data["Type"]);

		$fname = ($data && $data["_uploads"] && $data["_uploads"]["Path"]) ? $data["_uploads"]["Path"]["name"] : null;
		if (!$fname)
			throw new \Exception("File is mandatory!");

		$ext = pathinfo($fname, PATHINFO_EXTENSION);
		if (!in_array($ext, ["csv"]))
			throw new \Exception("Only csv format is accepted!");

		$file = new \Omi\File();
		$file->extractFromArray($data);
		return [$file->getFullPath("Path"), $this->selectors ? $this->selectors["csv"] : null, $type];
	}

	protected function importDataFromCsvFile($data)
	{
		list($path, $selector, $type) = $this->prepareCsvImport($data);
		\QModel::ImportFromCsv($path, $type, $this->from, $selector);
	}
	
	/**
	 * Gets the data for the grid
	 * 
	 * @api.enable
	 * 
	 * @param string $grid_mode
	 * @param scalar $id
	 * @param (scalar|array)[] $bind_param
	 * @return QIModel|QIModel[]
	 * @throws Exception
	 */
	public static function GetListData($grid_mode, $from, $id = null, $bind_param = null, $selector = null, $settings = null, $grid_reference = null)
	{
		try
		{
			$bind_param = static::GetInSerachBinds($bind_param);

			$cc = get_called_class();
			$dataCls = \QApp::GetDataClass();
			$dataCls::$FromAlias = end(explode("\\", $cc));
			
			\QTrace::Begin_Trace([], ['$grid_mode' => $grid_mode, '$from' => $from, '$id' => $id, 
				'$bind_param' => $bind_param, '$selector' => $selector, '$settings' => $settings, 
				'$dataCls::$FromAlias' => $dataCls::$FromAlias, 'called_class' => $cc], ["grid", "list", "data"]);
		
			$return = null;
			
			switch ($grid_mode)
			{
				case "edit":
				case "merge":
				case "view":
				case "delete":
				{
					if (!$id)
						$return = null;
					else
						$return = \QApi::QueryById($dataCls::$FromAlias, $id, $selector);
					break;
				}
				case "list":
				case "bulk":
				{
					$return = \QApi::Query($dataCls::$FromAlias, $selector, $bind_param);
					break;
				}
				default:
				{
					$return = null;
					break;
				}
			}
			
			return $return;
		}
		finally
		{
			\QTrace::End_Trace([], ['$return' => $return]);
		}
	}
	/**
	 * @api.enable
	 * 
	 * @param \QModel $data
	 * @param array $grid_data
	 */
	public static function DeleteItem(\QModel $data, $grid_data)
	{
		$grid = self::GetLoadedGrid($grid_data);
		$grid->doSubmitData($grid->submitData, "delete", $data->getId());
	}
	/**
	 * @param array $grid_data
	 * @api.enable
	 */
	public static function GetImportForm($grid_data)
	{
		if (!class_exists('Omi\VF\View\ImportFile'))
			throw new \Exception('Class name must be dynamic based on gens namespace');
		
		$import = new \Omi\VF\View\ImportFile();
		$import->settings = ["grid_data" => $grid_data];
		$import->setArguments([$import->settings, null, [], "add"], "renderForm");
		$import->setRenderMethod("renderForm");
		$import->render();
	}
	/**
	 * @api.enable
	 * 
	 * @param array $data
	 * @param array $grid_data
	 * @throws \Exception
	 */
	public static function ImportCSVFile($data, $grid_data)
	{
		try
		{
			if (!$data || (count($data) === 0))
				throw new \Exception("No data submitted!");
			static::BeforeProcessData($data);
			static::BeforeProcessFiles($_FILES);

			$grid = self::GetLoadedGrid($grid_data);
			$grid->prepareSubmit($data, $_FILES);

			$grid->importDataFromCsvFile($grid->submitData);
			
		}
		catch (\Exception $ex)
		{
			throw $ex;
		}
	}
	/**
	 * @api.enable
	 * 
	 * @param type $grid_data
	 * @param type $binds
	 * @return type
	 */
	public static function GetQSSearchData($grid_data, $binds = [])
	{
		static::BeforeProcessData($binds);
		$grid = self::GetLoadedGrid($grid_data);

		if (!$binds)
			$binds = [];

		if (!$binds["LIMIT"])
			$binds["LIMIT"] = [0, $grid->rowsOnPage ?: 20];

		$grid->grid_mode = "list";
		$grid->grid_id = null;
		$grid->grid_params = array_filter($binds, function($v, $k) {
			return (($v !== null) && ($v !== ""));
		}, ARRAY_FILTER_USE_BOTH);

		$data = $grid->getData($grid->grid_mode, $grid->id, $grid->grid_params);

		ob_start();
		
		$item_k_max = -1;
		$pos = 0;
		if ($data && count($data) > 0) 
		{
			foreach ($data as $item_k => $item) 
			{
				$pos++;
				static::RenderS($grid, "listRow", $grid->settings, $item, $grid->grid_params, $grid->grid_mode, $grid->grid_id, "", 
					["_rowi" => ["[_rowi][{$item_k}]", $data->_rowi[$item_k]], "_k" => $item_k, "_pos" => $pos, "_tsp" => ["[_tsp][{$item_k}]"]]);
				$item_k_max = max($item_k_max, $item_k);
			}
			$item_k_max++;
		}

		$item_k_max++;
		if(!$data || (count($data) === 0)) 
			static::RenderS($grid, "listNoResults");

		$inner = ob_get_clean();
		
		$url = trim(qbArrayToUrl($grid->grid_params));
		if ($url[0] === "&")
			$url = substr($url, 1);
		return [$inner, $grid->url() . ($url ? "?" : "") . $url, ($data && ($data->_show_more || (($qc = $data->getQueryCount()) && ($qc >= $item_k_max))))];
	}

	/**
	 * @api.enable
	 * 
	 * @param array $grid_data
	 * @param array $binds
	 */
	public static function GetSearchData($grid_data, $binds = [])
	{
		static::BeforeProcessData($binds);
		$grid = self::GetLoadedGrid($grid_data);

		if (!$binds)
			$binds = [];

		if (!$binds["LIMIT"])
			$binds["LIMIT"] = [0, $grid->rowsOnPage ?: 20];


		$grid->grid_mode = "list";
		$grid->grid_id = null;
		$grid->grid_params = array_filter($binds);

		$data = $grid->getData($grid->grid_mode, $grid->id, $grid->grid_params);

		ob_start();
		$grid->renderListInner($grid->settings, $data, $grid->grid_params);
		$inner = ob_get_clean();
		$url = trim(qbArrayToUrl($grid->grid_params));
		if ($url[0] === "&")
			$url = substr($url, 1);
		return [$inner, $grid->url() . ($url ? "?" : "") . $url];
		//return [$inner, $url];
	}
	
	/**
	 * @api.enable
	 * 
	 * @param array $grid_data
	 * @param string $type
	 * @param int $record_id
	 */
	public static function ProvisioningSync($grid_data, $type, $record_id = null)
	{
		$grid = self::GetLoadedGrid($grid_data);
		if (($type !== "pull") && ($type !== "push"))
			throw new \Exception("Type [{$type}] not accepted!");
	}
	/**
	 * @api.enable
	 * 
	 * @param mixed $data
	 * @param array $grid_data
	 */
	public static function DropdownPopupFormSubmit($data, $grid_data)
	{
		$grid = static::GetLoadedGrid($grid_data);
		$submitData = $grid::FormSubmit($data, $grid_data);

		$savedItm = $submitData ? q_reset($submitData) : null;

		if ($savedItm)
		{
			$type_inf = \QModelQuery::GetTypesCache(get_class($savedItm));
			$captionProps = ($type_inf["#%misc"]["model"]["captionProperties"]) ? 
					qParseEntity(implode(",", $type_inf["#%misc"]["model"]["captionProperties"])) : null;

			if ($captionProps)
				$savedItm = \QApi::QueryById($grid->from, $savedItm->getId(), $captionProps);
		}

		//caption, id, type, full_data
		return !$savedItm ? [null, null, null, null, null] : 
			[$savedItm->getModelCaption(), $savedItm->getId(), get_class($savedItm), $savedItm->toJSON(), $savedItm];
	}
	/**
	 * It expands an array using keys array
	 * 
	 * @param array $arr
	 * @param array $keys
	 * @param mixed $value
	 * @return null
	 */
	protected static function ExpandArray(&$arr, $keys, $value)
	{
		if (!$keys || (($cm = count($keys)) === 0))
			return;

		$pos = 0;
		$tmpArr = &$arr;
		foreach ($keys as $key)
		{
			if (++$pos != $cm)
			{
				if (!$tmpArr[$key])
					$tmpArr[$key] = [];
				$tmpArr = &$tmpArr[$key];
			}
			else
				$tmpArr[$key] = $value;
		}
	}
	/**
	 * When files are send using ajax we receive data in previous functionality format
	 * We need to setup data in correct format so files can be processed using our new system
	 * 
	 * @param array $files
	 * @return null
	 */
	protected static function BeforeProcessFiles(&$files)
	{
		if (!$files || (count($files) === 0))
			return;

		if ($files["_qb0"])
			$files = $files["_qb0"];

		$processedFiles = [];
		foreach ($files as $type => $values)
		{
			$values = q_reset($values);
			if (!$values || (count($values) === 0))
				continue;

			foreach ($values as $prop => $value)
			{
				$prop = urldecode($prop);
				$value = isset($value["_dom"]) ? $value["_dom"] : null;
				if ($value === null)
					continue;

				$propIsArr = (strrpos($prop, "]") !== false);
				$mainProp = $propIsArr ? preg_replace("/(\[|(?<=\[).*)/", "", $prop) : $prop;

				if (!isset($processedFiles[$mainProp]))
					$processedFiles[$mainProp] = [];

				if ($propIsArr)
				{
					$allMatches = [];
					preg_match_all("/(?<=\[).*?(?=\])/", $prop, $allMatches);
					$matches = ($allMatches && $allMatches[0]) ? $allMatches[0] : null;
					if (!$processedFiles[$mainProp][$type])
						$processedFiles[$mainProp][$type] = [];
					static::ExpandArray($processedFiles[$mainProp][$type], $matches, $value);
				}
				else
					$processedFiles[$mainProp][$type] = $value;
			}
		}
		$files = $processedFiles;
	}

	/**
	 * When data is received using ajax then data comes in previous implementation format
	 * We need to setup data in correct format so data can be processed using our new system
	 * 
	 * @param array $data
	 * @return null
	 */
	protected static function BeforeProcessData(&$data)
	{
		if (!$data || (count($data) === 0))
			return;

		// decode params - we may have keys like foo[bar][foo] - we need to expand them
		static::ParamsDecode($data);
		if (is_array($data))
		{
			static::FormSubmit_extract_misc_json($data);
		}
	}

	protected static function ParamsDecode(&$data, $checkFile = true)
	{
		$toProcessData = [];
		foreach ($data ?: [] as $key => $value)
		{
			$key = urldecode($key);
			$isArr = (strrpos($key, "]") !== false);

			$isfile = ($checkFile && 
				(is_array($value) && (count($value) === 5) && empty(array_diff(array_keys($value), ["name", "type", "tmp_name", "error", "size"]))));

			if ($isArr)
			{
				$allMatches = [];
				preg_match_all("/(?<=\[).*?(?=\])/", $key, $allMatches);
				$fkey = preg_replace("/(\[|(?<=\[).*)/", "", $key);
				$matches = ($allMatches && $allMatches[0]) ? $allMatches[0] : null;
				if (!$matches)
					$matches = [];
				array_unshift($matches, $fkey);
				static::ExpandArray($toProcessData, $matches, $isfile ? $value["name"] : $value);
			}
			else
				$toProcessData[$key] = $isfile ? $value["name"] : $value;
		}
		$data = $toProcessData;
	}
	
	
	/**
	 * @api.enable
	 * @param array $grid_data
	 * @param string $render_method
	 * @param string $vars_path
	 * @param int $rows
	 */
	public function renderAddRow($grid_data, $render_method, $vars_path, $rows = 1, $rows_data = [])
	{
		$grid = self::GetLoadedGrid($grid_data);
		
		if (is_array($rows_data))
		{
			# @TODO - we need to know what to populate
			# NO IDs on read-only mode :(
			$rows_data = new \QModelArray($rows_data);
			
			# qvar_dumpk($vars_path);
			# throw new \Exception('ex');
		}
		
		if ($rows_data instanceof \QModelArray)
		{
			$rows_data->populate('$CaptionProperties');
			
			$populate_props = new \QModelArray();
			$ty_props = [];
			
			foreach ($rows_data as $rd)
			{
				$ty = get_class($rd);
				
				if (!($type_props = $ty_props[$ty]))
				{
					$m_type = \QModel::GetTypeByName($ty);
					if (isset($m_type->properties))
						$type_props[$ty] = $type_props =  $m_type->properties;
				}
				if ($type_props)
				{
					foreach ($type_props as $prop_name => $prop_meta)
					{
						$elem = isset($rd->$prop_name) ? $rd->$prop_name : null;
						if (($elem instanceof \QIModel) && isset($elem->Id) && (!($elem instanceof \QIModelArray)))
							$populate_props[] = $elem;
					}
				}
			}
			
			$populate_props->populate('$CaptionProperties');
		}
		# qvar_dumpk($rows_data);
		# throw new \Exception('ex');
				
		$m = null;
		if ($rows > 1)
		{
			# we need to fix a increment issue with $vars_path
			$pm = preg_match("/^(.+)\\[(\\d+)\\]\$/uis", $vars_path, $m);
			if ($pm && $m)
			{
				$vars_path = $m[1]."[".$m[2]."]";
			}
		}
		
		for ($i = 0; $i < $rows; $i++)
		{
			$vars_path_i = $m ? ($m[1]."[".($m[2] + $i)."]") : $vars_path;
			$grid->$render_method($grid->settings, isset($rows_data[$i]) ? $rows_data[$i] : null, null, 
					$grid->grid_mode, $grid->id, $vars_path_i);
		}
	}

	/**
	 * @api.enable
	 * @param string $grid_mode
	 * @param string $render_method
	 * @param string $vars_path
	 * @param string $from
	 * @param string $id
	 * @param string $bind_param
	 * @param string $selector
	 * @param mixed $grid_data
	 * @param mixed $next_crt_no
	 */
	public static function RenderListData($grid_mode, $render_method, $vars_path, $from, $id = null, $bind_param = null, $selector = null, $grid_data = null, $next_crt_no = 1)
	{
		if (empty($selector))
			$selector = null;
		
		if ($bind_param === null)
			$bind_param = [];
		
		//decode binds
		static::ParamsDecode($bind_param, false);
		
		if (isset($grid_data['grid_params']) && $grid_data['grid_params'])
		{
			foreach ($grid_data['grid_params'] as $k => $v)
			{
				if (!isset($bind_param[$k]))
					$bind_param[$k] = $v;
			}
		}
		
		$grid = static::GetLoadedGrid($grid_data);
		$grid->init(false);
		$data = static::GetListData($grid_mode, $from, $id, $bind_param, $selector, $grid_data ? $grid_data["settings"] : null, $grid);
		
		ob_start();
		if ($data)
		{
			foreach ($data as $item)
			{
				$grid->{$render_method}($grid->settings, $item, $bind_param, $grid_mode, null, $vars_path, ["_pos" => $next_crt_no]);
				$next_crt_no++;
			}
		}
		$str = ob_get_clean();
		
		if (isset($data) && (count($data) > 0))
			$data->_show_more = true;
		
		return [$str, $next_crt_no, ($data && ($data->_show_more || (($qc = $data->getQueryCount()) && ($qc >= $next_crt_no))))];
	}
	/**
	 * @api.enable
	 * 
	 * @param \Omi\View\Grid $view
	 * @param int $id
	 * @param array $params
	 * @param string $mode
	 * @throws \Exception
	 */
	public static function RenderViewPopup($view, $id = null, $params = [], $mode = null)
	{
		if (!$view || !class_exists($view))
			throw new \Exception ("View '{$view}' not found!");

		$grid = new $view();

		if (!($grid instanceof \Omi\View\Grid))
			throw new \Exception ("View '{$view}' must extend grid!");

		if ($mode && !in_array($mode, $grid->getAllGridActions()))
			throw new \Exception("not allowed!");

		$grid->grid_mode = $mode ?: ($id ? "edit" : "add");
		$grid->grid_id = $id;
		$grid->grid_params = $params;

		/*
		$method = "renderList";
		if (($mode == "edit") || ($mode == "add"))
			$method = "renderForm";
		else if (($mode == "view") || ($mode == "delete"))
			$method = "renderView";
		else if ($mode == "bulk")
			$method = "renderBulk";
		*/

		$grid->setupGrid($grid->grid_mode, $grid->grid_id, $grid->grid_params);
		//$grid->setArguments([$grid->settings, $grid->data, $grid->grid_params, $grid->grid_mode, $id], $method);
		//$grid->setRenderMethod($method);
		
		$grid->render();
	}
	
	/**
	 * 
	 * @param array $grid_data
	 * @return \Omi\View\Grid
	 */
	public static function GetLoadedGrid($grid_data)
	{
		$cc = get_called_class();
		$grid = new $cc();

		// setup settings
		if ($grid_data && is_array($grid_data))
		{
			foreach ($grid_data as $key => $value)
				$grid->{$key} = $value;
		}
		return $grid;
	}
	/**
	 * Change params here if necessary
	 * 
	 * @param string $grid_mode
	 * @param int $id
	 * @param array $bind_params
	 */
	public function beforeSetupGrid(&$grid_mode, &$id, &$bind_params)
	{
		
	}
	
	public function setupDefaultBinds($grid_mode, &$bind_params)
	{
		$defaultBinds = (($grid_mode === "list") || ($grid_mode === "bulk")) ? 
			\QApi::Call('GetListDefaultBinds', static::$FromAlias) : \QApi::Call('GetFormDefaultBinds', static::$FromAlias);

		if (!$bind_params)
			$bind_params = [];

		foreach ($defaultBinds ?: [] as $dbk => $dbv)
		{
			if (!isset($bind_params[$dbk]))
				$bind_params[$dbk] = $dbv;
		}
	}
	/**
	 * Setups the grid based on the input parameters
	 * 
	 * @param string $grid_mode
	 * @param scalar $id
	 * @param (scalar|array)[] $bind_params
	 */
	public function setupGrid($grid_mode, $id = null, $bind_params = null)
	{
		// do before setup grid
		$this->beforeSetupGrid($grid_mode, $id, $bind_params);
		
		$_inlist = (($grid_mode === "list") || ($grid_mode === "bulk"));
		if (!$_inlist)
		{
			$data = $this->getData($grid_mode, $id, $bind_params);

			// for list, bulk and add the check is done in url
			// here we only need to check for edit/view/delete
			if (static::$_USE_SECURITY_FILTERS && (!static::$User || !static::$User->can($grid_mode, static::$FromAlias, $data)))
				return false;

			if (!$data && ($grid_mode !== "add"))
			{
				if (!$this->_is_reference)
				{
					$grid_mode = "list";
					$_inlist = true;
				}
				else
					$grid_mode = "add";
			}
		}

		// setup default binds
		$this->setupDefaultBinds($grid_mode, $bind_params);

		// setup paginator if in list mode
		if ($_inlist)
		{
			if (!$bind_params)
				$bind_params = [];

			if (!trim($bind_params["WHR_Search"]))
				unset($bind_params["WHR_Search"]);
			else
				$bind_params["WHR_Search"] = "%".trim(preg_replace("/(\\s+)/uis", "%", $bind_params["WHR_Search"]))."%";
			
			// setup here initial limit!
			if (!$bind_params["LIMIT"] && $this->rowsOnPage && !$this->inExport)
				$bind_params["LIMIT"] = [0, $this->rowsOnPage];

			// make sure that limit params are integers
			if ($bind_params["LIMIT"])
			{
				foreach ($bind_params["LIMIT"] as $key => $value)
					$bind_params["LIMIT"][$key] = (int)$value;
			}

			# if ($this->inExport)
			#	unset($bind_params["LIMIT"]);
			
			$data = $this->getData($grid_mode, $id, $bind_params);
		}

		$settings = $this->settings;
		$settings["heading:title"] = $settings["heading:title"] ?: $this->from;
		$settings["heading:form:title"] = $settings["heading:form:title"] ?: ($id ? "Edit: {$id}" : "Create");
		$settings["model:property"] = $this->from;

		$renderMethod = null;
		switch ($grid_mode)
		{
			case "add":
			case "edit":
			case "merge":
			{
				$renderMethod = "renderForm";
				break;
			}
			case "view":
			case "delete":
			{
				$renderMethod = "renderView";
				break;
			}
			case "bulk":
			{
				$id = null;
				$renderMethod = "renderBulk";
				$this->setArguments([$settings, $data, $bind_params, $grid_mode], "renderBulk");
				$this->setRenderMethod("renderBulk");
				break;
			}
			case "list":
			default:
			{
				$id = null;
				$this->setupListSettings($settings, $data, $bind_params);
				$renderMethod = "renderList";
				break;
			}
		}

		$this->setArguments([$settings, $data, $bind_params, $grid_mode, $id], $renderMethod);
		$this->setRenderMethod($renderMethod);

		$this->data = $data;
		$this->_settings = $settings;
		$this->_bind_params = $bind_params;
		$this->_grid_mode = $grid_mode;
		$this->_id = $id;
		$this->_rm = $renderMethod;
		return true;
	}
	/**
	 * 
	 * @param type $settings
	 */
	protected function setupListSettings(&$settings, $data, $bind_params)
	{
		$settings["pagination:rowsOnPage"] = $this->rowsOnPage;
		$settings["pagination:results"] = ($data && ($data instanceof \QModelArray)) ? $data->getQueryCount() : 0;
		$settings["pagination:limit:start"] = ($bind_params && $bind_params["LIMIT"] && $bind_params["LIMIT"][0]) ? $bind_params["LIMIT"][0] : null;
		$settings["pagination:limit:end"] = (($rwsOn = ($settings["pagination:limit:start"] + $settings["pagination:rowsOnPage"])) > $settings["pagination:results"]) ?
				$settings["pagination:results"] : $rwsOn;
		$settings["pagination:selectedpage"] = $settings["pagination:limit:start"] ? $settings["pagination:limit:start"]/$this->rowsOnPage + 1 : 1;
		$settings["pagination:show"] = ($settings["pagination:results"] > $settings["pagination:rowsOnPage"]);

	}

	public function getJsProperties()
	{
		# qvar_dumpk($this->grid_params, $this->toJSON($this->jsPropsSelector, true));
		# die;
		
		$decoded = json_decode($this->toJSON($this->jsPropsSelector, true), true);
		
		if (isset($this->grid_params) && empty($decoded['grid_params']))
		{
			$decoded['grid_params'] = $this->grid_params;
			unset($decoded['grid_params']['full_data']);
		}
		if (isset($this->settings) && empty($decoded['settings']))
			$decoded['settings'] = $this->settings;
		if (isset($this->selectors) && empty($decoded['selectors']))
			$decoded['selectors'] = $this->selectors;
		
		# json_encode($val, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		$ret = json_encode($decoded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		
		if ($ret === false)
			throw new \Exception(_L('Unable to setup grid properties.'));
		
		return $ret;
	}
	/**
	 * @param array $data
	 */
	public function prepareSubmit($data, $files = null)
	{
		if (!empty($files))
		{
			$f_array = [];
			// 'name', 'type', 'tmp_name', 'error', 'size'
			foreach ($files as $k => $f_data)
			{
				$f_array[0][$k] = $f_data["name"];
				$f_array[1][$k] = $f_data["type"];
				$f_array[2][$k] = $f_data["tmp_name"];
				$f_array[3][$k] = $f_data["error"];
				$f_array[4][$k] = $f_data["size"];
			}
			// push files info into data
			$this->prepareSubmitFiles($f_array, $data);
		}
		$this->submitData = $data;
	}
	/**
	 * @param array $data
	 */
	protected function prepareSubmitFiles($files, &$data = null, &$parent = null, $key = null)
	{
		if (($parent !== null) && (!is_array($files[0])))
		{
			// handle it
			$parent["_uploads"][$key] = ['name' => $files[0], 'type' => $files[1], 'tmp_name' => $files[2], 'error' => $files[3], 'size' => $files[4]];
		}
		else if ($parent !== null)
		{
			if ($parent[$key] === null)
				$parent[$key] = [];
			$ref = null;
			foreach ($files[0] as $k => $v)
				$this->prepareSubmitFiles([$files[0][$k], $files[1][$k], $files[2][$k], $files[3][$k], $files[4][$k]], $ref, $parent[$key], $k);
		}
		else
		{
			$ref = null;
			foreach ($files[0] as $k => $v)
				$this->prepareSubmitFiles([$files[0][$k], $files[1][$k], $files[2][$k], $files[3][$k], $files[4][$k]], $ref, $data, $k);
		}
	}
	
	public static function do_submit($data, $grid_mode, $grid_id = null, Grid $grid = null)
	{
		switch ($grid_mode)
		{
			case "add":
			{
				//$result = \QApi::Insert($this->from, $data);
				$result = \QApi::Merge(static::$FromAlias, $data);
				break;
			}
			case "edit":
			{
				//$result = \QApi::Update($this->from, $data);
				$result = \QApi::Merge(static::$FromAlias, $data);
				break;
			}
			case "merge":
			{
				$result = \QApi::Merge(static::$FromAlias, $data);
				break;
			}
			case "bulk":
			{
				//$result = \QApi::Save($this->from, $data);
				$result = \QApi::Merge(static::$FromAlias, isset($data[$grid->from]) ? $data[$grid->from] : $data);
				break;
			}
			case "delete":
			{
				if ($grid_id)
					$result = \QApi::DeleteById(static::$FromAlias, $grid_id);
				else if (qis_array($data))
				{
					if (is_array($data))
						$data = new \QModelArray($data);

					$result = \QApi::Delete(static::$FromAlias, $data);
				}
				break;
			}
			default:
			{
				break;
			}
		}
		return $result;
	}
	
	/**
	 * @param array $data 
	 * @param string $grid_mode
	 * @param string|integer $grid_id
	 */
	public function doSubmitData($data, $grid_mode, $grid_id = null)
	{
		//setup from alias
		$dataCls = \QApp::GetDataClass();
		$dataCls::$FromAlias = static::$FromAlias;

		try
		{
			\QTrace::Begin_Trace([], ['$data' => $data, '$grid_mode' => $grid_mode, 
				'$grid_id' => $grid_id, '$dataCls' => $dataCls, '$dataCls::$FromAlias' => $dataCls::$FromAlias], ["grid", "data", "doSubmitData", "submit"]);

			$result = null;
			
			# $initial_data = $data;
			if (is_array($data))
				list($data) = \QApi::Array_To_Model($data, static::$FromAlias);
			
			# qvar_dump('$data', $data);
			# throw new \Exception('aaaaa: ' . get_class($data) . ' - ' . $data[0]->Private_IP . ' - ' . json_encode($initial_data));
			
			return static::do_submit($data, $grid_mode, $grid_id, $this);
		}
		finally
		{
			\QTrace::End_Trace([], ['$result' => $result]);
		}
	}
	
	/**
	 * @api.enable
	 */
	public static function Multi_Delete($elements, $grid_properties)
	{
		$grid = self::GetLoadedGrid($grid_properties);
		
		$elements = isset($elements[$grid->from]) ? $elements[$grid->from] : $elements;
		
		if ($elements)
		{
			return $grid->doSubmitData($elements, 'delete', null);
		}
		
		return false;
	}
	
	/**
	 * @api.enable
	 */
	public static function Bulk_Set_Values($selected_elements, $popup_data)
	{
		
		# unset : Id,__submitted,_ty
		if ($popup_data)
			unset($popup_data['Id'], $popup_data['__submitted'], $popup_data['_ty']);
		
		$grid = new static;
		
		#	$selected_elements = $selected_elements[$grid->from];
		if (isset($selected_elements[$grid->from]))
			$selected = \Omi\App::FromArray($selected_elements);
		else 
			throw new \Exception('Not implemented.');
		
		if (isset($selected->{$grid->from}))
		{
			$had_saved_props = false;
			
			$props_to_save = [];
			foreach ($selected->{$grid->from} ?: [] as $elem)
			{
				if ($elem->Id)
				{
					foreach ($popup_data as $prop_name => $prop_value)
					{
						if (property_exists($elem, $prop_name))
						{
							$elem->{"set{$prop_name}"}($prop_value);
							$props_to_save[$prop_name] = $prop_name;
							$had_saved_props = true;
						}
					}
				}
			}

			\QApi::Merge($grid->from, $selected->{$grid->from}, implode(",", $props_to_save));
			
			return $had_saved_props;
		}
		else
			return false;
	}
	
	public function get_export_limit(string $type = null)
	{
		return $this->max_export_limit[$type] ?? ($this->max_export_limit['all'] ?: 10000);
	}

	public static function maximum_upload_size()
	{
		# from PHP DOC
		function return_bytes($val) {
			$val = trim($val);
			$last = strtolower($val[strlen($val)-1]);
			switch($last) {
				// The 'G' modifier is available
				case 'g':
					$val *= 1024;
				case 'm':
					$val *= 1024;
				case 'k':
					$val *= 1024;
			}

			return $val;
		}

		$maxUpload      = return_bytes(ini_get('upload_max_filesize'));
		$maxPost        = return_bytes(ini_get('post_max_size'));
		
		return min($maxUpload, $maxUpload);
	}
}

