<?php

class Q_Json
{
	/**
	 * 
	 * @param scalar|array|stdClass|QIModel $input_data
	 * @param array $processed
	 * @param int $max_depth
	 * 
	 * @return string
	 * 
	 * @throws \Exception
	 */
	public static function encode($input_data, array &$processed = null, int $max_depth = 24)
	{
		$how_deep_we_go = 0;
		$objs_count = 0;
		
		$t0 = microtime(true);
		
		$start_depth = $max_depth;
		
		$skipped_elements = [];
		
		if (($input_data === null) || is_scalar($input_data))
			return [json_encode($input_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE), true];
		else if (is_array($input_data) || is_object($input_data))
		{
			$t0 = microtime(true);
			$m0 = memory_get_usage();
			
			$json = [""];
			$data_list = [[$input_data, &$json]];
			
			while ($data_list && ($max_depth > 0))
			{
				$how_deep_we_go++;
				
				$new_data_list = [];
				foreach ($data_list as &$inf)
				{
					list ($data, &$parent) = $inf;
					$is_obj = is_object($data);
					$is_q_model = $is_obj && ($data instanceof \QIModel);
					
					$last_str = &$parent[0];
					
					if ($is_obj)
					{
						$class = get_class($data);
						$obj_id = spl_object_id($data);
						
						$last_str .= '{"!#id":' . $obj_id . ',"!#ty":' . json_encode($class, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
						
						if (isset($processed[$obj_id]))
						{
							$last_str .= "}";
							# we do not do it again
							continue;
						}
						else if ( ($class !== 'stdClass') && ( ! $is_q_model ))
						{
							$last_str .= "}";
							$skipped_elements[$class][] = $data;
							# we will not process it
							continue;
						}
						$processed[$obj_id] = true;
					}
					else
					{
						$last_str .= '{"!#ty":"array"';
					}

					foreach ($data as $k => $v)
					{
						/*
						if (is_string($k) && (($k === 'Offers') || ($k === 'Content') || ($k === 'TourOperator')) || ($k === 'City') || ($k === 'MasterCity') || ($k === 'County') || ($k === 'MasterCounty')
												 || ($k === 'Address') || ($k === 'Meals'))
							continue;
						*/
						# 'Content' => true, 'MainImage' => true,
						if ((($v === null) && $is_obj && is_string($k) && ($is_q_model || ($k[0] === '_'))) || 
								isset(['_ty' => true, '_sc' => true, '_wst' => true, '_qini' => true, '_found_on_merge' => true, '_filter_by_grp' => true, '_typeIdsPath' => true, '_facilitiesLinksIndexed' => true][$k]))
							continue;
						/*
						if ($is_obj && is_string($k) && ($k !== '_id') && ($k !== '_tmpid') && ($k !== '_INDX') && (strlen($k) < 7) && ($k[0] === '_'))
						{
							qvar_dump("(\$k[0] === '_')", $k, $data, $v);
							die;
						}
						*/
						else if (is_scalar($v) || ($v === null))
						{
							$last_str .= "," . (is_int($k) ? "\"{$k}\"" : json_encode($k, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE)) . ':' . json_encode($v, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
						}
						else if (is_object($v) || is_array($v))
						{
							$n_obj = [""];
							$last_str .= "," . (is_int($k) ? "\"{$k}\"" : json_encode($k, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE)) . ':';
							
							$parent[] = &$n_obj;
							unset($last_str);
							$last_str = "";
							$parent[] = &$last_str;
							$new_data_list[] = [$v, &$n_obj];
							unset($n_obj);
						}
						else
						{
							# throw new \Exception('Not supported data type #1' . gettype($v));
							return [false, new \Exception('Not supported data type #1' . gettype($v))];
						}
					}
					
					$last_str .= "}";
					
					unset($last_str);
				}
				
				$data_list = $new_data_list;
				$max_depth--;
			}
			
			# $t1 = microtime(true);
			# $m1 = memory_get_usage();
			
			$json_str = static::collected_array_to_string($json);
			
			return [$json_str, true, $skipped_elements, $data_list, $start_depth - $max_depth, $json];
		}
		else
		{
			# throw new \Exception('Not supported data type #2' . gettype($data));
			return [false, new \Exception('Not supported data type #1' . gettype($v))];
		}
	}
	
	public static function encode_direct_return($input_data, int $max_depth = 24)
	{
		$processed = null;
		$ret = static::encode($input_data, $processed, $max_depth);
		return is_array($ret) ? $ret[0] : $ret;
	}
	
	protected static function collected_array_to_string(array $collected)
	{
		$ret = "";
		foreach ($collected ?? [] as $itm)
		{
			$ret .= is_array($itm) ? static::collected_array_to_string($itm) : $itm;
		}
		return $ret;
	}
	
	public static function decode_recurse($data, array &$processed)
	{
		if (($data === null) || is_scalar($data))
			return [$data, true];
		$ty = $data['!#ty'] ?? null;
		$id = $data['!#id'] ?? null;
		if (!isset($ty))
			return [null, false];
		
		$obj = null;
		$is_obj = false;
		if (isset($id))
		{
			$exists = $processed[$id] ?? null;
			if ($exists)
			{
				# return [$exists, true];
				$obj = $exists; # we are not order-aware ... so we need to process it
				$is_obj = ! ( $obj instanceof \QIModelArray);
			}
			else if ($ty !== 'array')
			{
				$processed[$id] = $obj = new $ty;
				$is_obj = ! ( $obj instanceof \QIModelArray);
			}
			else
				$obj = [];
		}
		else if ($ty === 'array')
		{
			$obj = [];
		}
		else
			return [null, false];
		
		foreach ($data as $k => $v)
		{
			if (($k[0] === '!') && ($k[1] === '#'))
				continue;
			else if (($v === null) || is_scalar($v))
				$is_obj ? ($obj->$k = $v) : ($obj[$k] = $v);
			else
			{
				list ($ret_v, $sub_ok) = static::decode_recurse($v, $processed);
				if ($sub_ok)
					$is_obj ? ($obj->$k = $ret_v) : ($obj[$k] = $ret_v);
				else
					return [$ret_v, $sub_ok];
			}
		}
		
		return [$obj, true];
	}
	
	public static function decode(string $str, int $flags = null, int $depth = 512)
	{
		if ($flags === null)
			$flags = JSON_INVALID_UTF8_SUBSTITUTE;
		
		$input_data = json_decode($str, true, $depth, $flags);
		
		if (json_last_error())
			return [null, false, new \Exception(json_last_error_msg ())];
		
		$processed = [];
		return static::decode_recurse($input_data, $processed);
	}
}

