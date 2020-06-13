<?php


/**
 * @class.name QDevModeSelectModelCtrl
 */
abstract class QDevModeSelectModelCtrl_frame_ extends QWebControl
{
	
	
	public $url_tag = "classitem";
	public $class_filter = null;
	public $drop_down = false;
	public $url_getter = null;
	public $input_qb;
	public $input_default;
	
	/**
	 * Gets the list of classes
	 * 
	 * @param string $filter
	 * @param int $limit
	 * 
	 * @return string[]
	 */
	public function getClassesList($filter = null, $limit = 200)
	{
		if (($limit > 200) || ($limit < 1))
			$limit = 200;
		
		$model_class = \QApp::GetDataClass();
		
		$properties = QModelQuery::GetTypesCache($model_class);
		
		$ret_props = [];
		
		// up to 5 parts to search for
		$filter_parts = $filter ? preg_split("/\\s+/us", strtolower($filter), 5, PREG_SPLIT_NO_EMPTY) : null;
		$f_count = $filter_parts ? count($filter_parts) : 0;
		
		$count = 0;
		foreach ($properties as $property => $data)
		{
			if ($limit && ($count >= $limit))
				break;
			// $classes[$class] = $path;
			if (($property{0} === '%') || ($property{0} === '#'))
				continue;
			
			if ($filter_parts)
			{
				$class_lc = strtolower($property);
				// do the filtering
				if (
						(($f_count > 0) && (($p_0 = strpos($class_lc, $filter_parts[0], 0)) === false)) ||
						(($f_count > 1) && (($p_1 = strpos($class_lc, $filter_parts[1], $p_0)) === false)) ||
						(($f_count > 2) && (($p_2 = strpos($class_lc, $filter_parts[2], $p_1)) === false)) ||
						(($f_count > 3) && (($p_3 = strpos($class_lc, $filter_parts[3], $p_2)) === false)) ||
						(($f_count > 4) && ((       strpos($class_lc, $filter_parts[4], $p_3)) === false)) 
					)
				{
					if (($p_0 !== false) && (!(
							(($f_count > 1) && (strpos($class_lc, $filter_parts[1]) === false)) ||
							(($f_count > 2) && (strpos($class_lc, $filter_parts[2]) === false)) ||
							(($f_count > 3) && (strpos($class_lc, $filter_parts[3]) === false)) ||
							(($f_count > 4) && (strpos($class_lc, $filter_parts[4]) === false)) 
							)))
					{
						$extra_classes[$watch_folder][$class] = $all_classes[$class];
					}
					// in this case we skip this class
					continue;
				}
			}
			
			$ret_props[$property] = $property;
			$count++;
		}
		
		unset($ret_props['Del__']);
		
		ksort($ret_props);
		
		return $ret_props;
	}
	
	public function getLink($url_tag, $class)
	{
		if ($this->url_getter)
		{
			$func = $this->url_getter;
			return $func($url_tag, $class);
		}
		else
			return \QDevModePage::GetUrl($url_tag, $class);
	}
}