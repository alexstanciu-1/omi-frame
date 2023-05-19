<?php

namespace Omi\View;

/**
 * Description of Controller
 *
 * @class.name Controller
 */
abstract class Controller_mods_controller_ extends \QWebControl implements \QIUrlController 
{
	/**
	 * @var int
	 */
	protected static $Selected_Property_Id = null;
	/**
	 * @var \Omi\TFH\Property
	 */
	protected static $Selected_Property = null;
	
	/**
	 * Cache prop details
	 *
	 * @var array
	 */
	public $_propDetails = [];
	/**
	 * @var \QWebPage
	 */
	public $webPage;
	/**
	 * Where to store generated files
	 *
	 * @var string
	 */
	public static $SaveDirBase = QGEN_SaveDirBase;
	/**
	 * Where to store generated files
	 *
	 * @var string
	 */
	public static $ConfigDirBase = QGEN_ConfigDirBase;
	/**
	 * The base url
	 *
	 * @var string
	 */
	public static $BaseUrl;
	/**
	 * Setup the language from url
	 * 
	 * @param \QUrl $url
	 */
	public function setupLanguage(\QUrl $url)
	{
		if (($langs = \QModel::GetLanguages_Dim()) && in_array($url->current(), $langs))
		{
			\QModel::SetLanguage_Dim($url->current());
			$url->next();
		}
	}
	
	/**
	 * instantiate the webpage property
	 */
	public function setupWebPage()
	{
		$this->webPage = new \Omi\View\WebPage();
	}

	/**
	 * Load translations and set them up on \QLanguage::$Data array
	 */
	public function setupTranslations()
	{
		$translations = \QApi::Query("Translations", "Tag, Value");
		if ($translations && (count($translations) > 0))
		{
			$c_lang = \QModel::GetLanguage_Dim();
			foreach ($translations as $translation)
				\QLanguage::$Data[$translation->Tag][$c_lang] = $translation->Value;
		}
	}

	/**
	 * 
	 * @param \QUrl $url
	 * @param string $property_name
	 * @param array $settings
	 * @param string $prop_grid_class
	 * @return boolean
	 */
	public function processAdminUrl($url, $property_name, $grid_props = [], $prop_grid_class = null, $menuTag = null)
	{
		$this->showProperty = $property_name;
		if (!$prop_grid_class)
			$prop_grid_class = Q_Gen_Namespace."\\{$property_name}";
			
		$this->resyncView();

		$mt = $menuTag ?: end(explode("\\", $prop_grid_class));
		
		$accepted_views = [];
		
		if (class_exists($prop_grid_class) && ($this->webPage->canAccessMenu($mt) || in_array($mt, $accepted_views)))
		{
			$this->webPage->content = new $prop_grid_class();
			$this->webPage->content->processAction = "ajax";

			// setup property on grid ctrl
			$this->webPage->content->showProperty = $property_name;

			if (($selectedMenuItm = $this->webPage->getMenuSelected($menuTag ?: $property_name)))
				$this->webPage->content->sideMenuSelected = ($selectedMenuItm && $selectedMenuItm["caption"]) ? $selectedMenuItm["caption"] : $property_name;
			$this->webPage->content->sideMenuItems = $this->webPage->getMenuSiblings($menuTag ?: $property_name);

			//make sure that we have properties loaded to get the prop details
			// setup on grid show property details
			$this->webPage->content->showPropertyDetails = $this->_propDetails[$property_name] ? $this->_propDetails[$property_name] : null;

			if ($grid_props)
			{
				foreach ($grid_props as $key => $value)
					$this->webPage->content->{$key} = $value;
			}

			// add the control
			$this->addControl($this->webPage->content);
			$url->next();

			return $this->webPage->content->loadFromUrl($url, $this);
		}
		
		return false;
	}

	public function resyncView($force = false)
	{
		// @TODO - remove this @REMOVE
		$force = $force || \QAutoload::GetDevelopmentMode();
		
		if ((!$force) && (defined("IS_LIVE") && IS_LIVE))
			return;
		if ((!$force) && (!\QAutoload::$HasChanges))
			return;

		# qvar_dumpk('resyncView!!!: '.$this->showProperty);
		
		$props = $this->getProperties();
		
		if (isset($props[$this->showProperty]))
		{
			$property = $this->showProperty;
			if ($this->_views[$property])
			{
				if ($this->_propDetails[$property])
					throw new \Exception('View cannot have the same tag as a property!');
				$property = $this->_viewsProps[$property];
			}
			
			self::SyncPropertyGrid($property);
		}
		else
		{
			//echo "<div style='color: red;'>Property not found [{$this->showProperty}]</div>";
		}
	}

	/**
	 * Setup the control for the Url reference
	 * We will load the relevant control based on url reference type
	 * 
	 * @param \Omi\Cms\IUrlReference $testResult
	 */
	public function setupCtrlForUrl(IUrlReference $testResult)
	{
		if ($testResult instanceof \Omi\Cms\News)
		{
			/*
			$this->webPage->content = new \Omi\Cms\View\News();
			$this->Seo = $testResult->Seo;
			*/
		}
		else if ($testResult instanceof \Omi\Cms\Content)
		{
			/*
			$this->webPage->content = new \Omi\Cms\View\Pages();
			$this->Seo = $testResult->Seo;
			*/
		}
		return $testResult;
	}
	
	public function getProperties($class = null, $class_inf = null, $app_model = null)
	{
		$class = $class ?: \QApp::GetDataClass();
		$class_inf = $class_inf ?: \QModelQuery::GetTypesCache($class);

		$app_model = $app_model ?: \QModel::GetTypeByName($class);

		$props = [];
		$this->_propDetails = [];
		$this->_views = [];
		$this->_viewsProps = [];
		foreach ($class_inf as $p_name => $pinf)
		{
			if (substr($p_name, 0, 2) === "#%")
				continue;

			$props[$p_name] = $p_name;

			$prop = $app_model->properties[$p_name];
			if (!$prop)
				continue;

			$views = $prop->storage && $prop->storage["views"] ? explode(",", $prop->storage["views"]) : [];
			if ($views && (count($views) > 0))
			{
				foreach ($views as $view)
				{
					$this->_views[$view] = $view;
					$this->_viewsProps[$view] = $p_name;
					$props[$view] = $view;
				}
			}

			$this->_propDetails[$p_name] = $pinf;
		}

		ksort($props);
		return $props;
	}

	public static function PrefixUrl($url)
	{
		$prefix = static::GetUrl();
		return ($prefix ? $prefix."/" : "").$url;
	}

	/**
	 * @api.enable
	 */
	public static function SyncPropertyGrid($property = null)
	{
		$properties = $property ? [$property] : static::getProperties();
		
		if (!$properties)
			return false;
		
		if (!self::$SaveDirBase)
			self::$SaveDirBase = \QAutoload::GetRuntimeFolder()."temp/~admin";
		
		if (!is_dir(self::$SaveDirBase))
			qmkdir(self::$SaveDirBase);
		
		foreach ($properties as $prop)
		{
			if (defined('Q_SYNC_GENERATED_VIEWS') && Q_SYNC_GENERATED_VIEWS[$prop])
				// already generated, we will skip !!
				continue;
			
			$config = [];
			// sync that one
			$config["from"] = $prop;
			$config["className"] = static::GetClassNameForProperty($prop); // (includes namespace)
			//$save_dir = rtrim(self::$SaveDirBase, "\\/") . "/" . ucfirst($prop);
			$config["gen_path"] = self::$SaveDirBase;
			$config["gen_config"] = self::$ConfigDirBase;
			\Omi\Gens\Grid::Generate($config);
		}
	}

	public static function GetClassNameForProperty($property = null)
	{
		return Q_Gen_Namespace."\\".ucfirst($property);
	}
	
	public static function TFH_Get_PropertyFilter()
	{
		if (static::$Selected_Property !== null)
			return static::$Selected_Property;
		
		$url_info = static::Get_Property_URL(null, true);
		list($fixed_location, $original_url_owner, $original_url_property) = $url_info;
		
		if (isset($original_url_property) && ((int)$original_url_property > 0))
		{
			$ret = \QApi::QueryById('Properties', (int)$original_url_property);
			return static::$Selected_Property = $ret ?: false;
		}
		else
			return (static::$Selected_Property = false);
		# qvar_dumpk($url_info);
		# die;
		/*
		if (static::$Selected_Property !== null)
			return static::$Selected_Property;
		else if ((isset($_COOKIE['tfh_page_property_select'])) && ((int)$_COOKIE['tfh_page_property_select'] > 0))
		{
			$ret = \QApi::QueryById('Properties', (int)$_COOKIE['tfh_page_property_select']);
			return static::$Selected_Property = $ret ?: false;
		}
		else
			return (static::$Selected_Property = false);
		*/
	}
	
	public static function Setup_Sub_Sub_Domain()
	{
		$url_info = static::Get_Property_URL(null, true);
		list($fixed_location, $original_url_owner, $original_url_property) = $url_info;
		$c_user = \Omi\User::GetCurrentUser();
		if ($c_user && isset($c_user->Owner->Id))
		{
			if (!$original_url_owner || ((int)$original_url_owner !== (int)$c_user->Owner->Id))
			{
				header("Location: ".$fixed_location);
				die;
			}
		}
	}
	
	/**
	 * @api.enable
	 * 
	 * @param int $property_id
	 */
	public static function Get_Property_URL(int $property_id = null, bool $get_details = false, bool $called_by_property_select_drop_down = false)
	{
		$c_user = \Omi\User::GetCurrentUser();
		if (!isset($c_user->Owner->Id))
			return false;
		$request_host = $_SERVER['HTTP_HOST'];
		if (!$request_host)
			return false;

		$parts = array_reverse(preg_split("/(\\.)/uis", $request_host, -1, PREG_SPLIT_NO_EMPTY));
		
		$owner_property_part = isset($parts[3]) ? $parts[3] : null;
		list ($url_owner, $url_property) = $owner_property_part ? preg_split("/(\\-)/uis", $owner_property_part, 2, PREG_SPLIT_NO_EMPTY) : [];
		
		$original_url_owner = $url_owner;
		$original_url_property = $url_property;

		if ((!$property_id) && ($property_id !== null)) # unset was requested
			$url_property = null;
		else if (isset($property_id) && $property_id)
			$url_property = (string)$property_id;
		
		$request_uri = $_SERVER['REQUEST_URI'];
		$request_uri_parts = \QUrl::Get_Current_Parts() ?: [];
		list($url_grid, $url_action, $url_c_id) = $request_uri_parts;
		
		if (isset($property_id) && $property_id)
		{
			
			if (($url_action == 'edit') || ($url_action == 'view') || ($url_action == 'delete'))
			{
				$app_type = \QModel::GetTypeByName("Omi\\App");
				$app_prop_data_views = isset($app_type->properties['Properties']->storage['views']) ? $app_type->properties['Properties']->storage['views'] : null;
				$property_parts = preg_split("/(\\s*\\,\\s*)/uis", trim($app_prop_data_views));
				$property_parts[] = 'Properties';
				
				if (in_array($url_grid, $property_parts))
				{
					$index = count($request_uri_parts) - 1;
					$request_uri_parts[$index] = $property_id;

					$request_uri = \QWebRequest::GetBaseHref() . implode('/', $request_uri_parts);
				}
				else
				{
					# return to list mode
					$request_uri = \QWebRequest::GetBaseHref() . implode('/', [$url_grid]);
				}
			}
		}
		else
		{
			# list mode
			if ($called_by_property_select_drop_down)
			{
				# return to list mode
				$request_uri = \QWebRequest::GetBaseHref() . implode('/', [$url_grid]);
			} 
		}
		
		$fixed_location = ($_SERVER['HTTPS'] ? "https://" : "http://").
			$c_user->Owner->Id . ($url_property ? '-'.$url_property : '') . "." . 
				implode(".", array_reverse(array_slice($parts, 0, 3))) . 
				$request_uri;

		if ($get_details)
			return [$fixed_location, $original_url_owner, $original_url_property];
		else
			return $fixed_location;
	}
}
