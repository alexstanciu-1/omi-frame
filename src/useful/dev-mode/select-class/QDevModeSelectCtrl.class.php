<?php


/**
 * @class.name QDevModeSelectCtrl
 */
abstract class QDevModeSelectCtrl_frame_ extends QWebControl
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
		
		$all_classes = QAutoload::GetAutoloadData();
		$classes = [];
		// order classes
		$parents_list = QAutoload::GetClassParentsList();
		$data = end($parents_list);
		$count = 0;
		
		// up to 5 parts to search for
		$filter_parts = $filter ? preg_split("/\\s+/us", strtolower($filter), 5, PREG_SPLIT_NO_EMPTY) : null;
		$f_count = $filter_parts ? count($filter_parts) : 0;
		
		$extra_classes = [];
		
		$last_wf = null;
		
		while ($data)
		{
			$watch_folder = key($parents_list);
			$wf_len = strlen($watch_folder);
			
			foreach ($data as $class => $parent)
			{
				$path = $all_classes[$class];
				if (substr($path, 0, $wf_len) !== $watch_folder)
					continue;
				
				if ($filter_parts)
				{
					$class_lc = strtolower($class);
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
				
				if ($this->class_filter && (!call_user_func($this->class_filter, $class)))
					continue;
				
				if ($last_wf !== $watch_folder)
				{
					$classes[] = $watch_folder;
					$last_wf = $watch_folder;
				}
				$classes[$class] = $path;
				
				$count++;
				if ($count >= $limit)
					break;
			}
			$data = prev($parents_list);
		}
		
		if (($count < $limit) && $extra_classes)
		{
			foreach ($extra_classes as $watch_folder => $data)
			{
				foreach ($data as $class => $path)
				{
					if ($last_wf !== $watch_folder)
					{
						$classes[] = $watch_folder;
						$last_wf = $watch_folder;
					}
					$classes[$class] = $path;
					$count++;
					if ($count >= $limit)
						break;
				}
			}
		}
		
		return $classes;
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