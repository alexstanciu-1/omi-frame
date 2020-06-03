<?php

class QApi
{
	public static $DebugApi = false;
	public static $DebugUidf = [];
	
	protected static $_InImportProcess;
	
	protected static $_LastCalledPartner;

	protected static $_Froms = [];
	/**
	 * @var \QModel 
	 */
	public static $DataToProcess = null;
	
	public static $CurrentSupplier = null;
	
	public static $_Caller_Company_In_Callee_Box = null;

	public static $_Partner_Call = false;

	/**
	 * @param string $call_info
	 * 
	 * @return mixed
	 */
	public static function _Call($call_info)
	{
		if (static::$DebugApi)
			$debug_uidf = static::DebugStartApi();
		
		list($class_name, $method) = explode("::", $call_info, 2);
		if ($class_name{0} === "\\")
			$class_name = substr($class_name, 1);
		if (!class_exists($class_name))
			throw new Exception("Class `{$class_name}` was not found");
		if (!method_exists($class_name, $method))
			throw new Exception("Method `{$class_name}::{$method}` was not found");

		$m_type = QModel::GetTypeByName($class_name);
		
		if ($m_type)
		{
			if (!$m_type->methodHasApiAccess($method))
				throw new Exception("No access to method: {$class_name}::{$method}");
		}
		else
		{
			$refl_method = new ReflectionMethod($class_name, $method);
			$doc_comm = $refl_method->getDocComment();
			if (!$doc_comm)
				throw new Exception("No access to method: {$class_name}::{$method}");

			// @todo : to improve this !!!
			$method_info = QCodeStorage::parseDocComment($doc_comm, true);
			if (!($method_info["api"] && $method_info["api"]["enable"]))
				throw new Exception("No access to method: {$class_name}::{$method}");
		}
		
		$args = func_get_args();
		array_shift($args);
		
		if (method_exists($class_name, $method."_in_"))
			call_user_func_array([$class_name, $method."_in_"], $args);
		
		$return = call_user_func_array([$class_name, $method], $args);
		
		if (method_exists($class_name, $method."_out_"))
			call_user_func_array([$class_name, $method."_out_"], array_unshift($args, $return));
		
		if (static::$DebugApi && static::DebugEndApi($debug_uidf))
			static::DebugCall($return, $class_name, $method, $args);
		
		return $return;
	}
	
	/**
	 * @api.enable
	 * @param string $call_info
	 * @return mixed
	 */
	public static function Call($call_info)
	{
		$args = func_get_args();
		if (strpos($call_info, "::") === false)
		{
			$call_info = \QApp::GetDataClass() . "::" . $call_info;
			$args[0] = $call_info;
			return QApi::_Call(...$args); # call_user_func_array(['QApi', '_Call'], $args);
		}
		else
			return QApi::_Call(...func_get_args());
	}
	
	/**
	 * 
	 * @param string $from
	 * @param string $selector
	 * @param array $parameters
	 * 
	 * @return QIModel
	 */
	public static function __Query($from, $selector = null, $parameters = null, $only_first = false, $id = null)
	{
		if (static::$DebugApi)
			$debug_uidf = static::DebugStartApi();
		
		$view_tag = null;
		if (is_array($from) && (count($from) === 2))
			list($from, $view_tag) = $from;

		$skip_security = false;
		
		$parsed_sources = $from ? static::ParseSourceInfo($from) : [null, null];
		if (!$parsed_sources)
			throw new Exception("Source information error");
		
		$orig_selector = $selector;
		if (is_string($selector))
			$selector = qParseEntity($selector);
		
		$result = [];
		
		$storage_model = QApp::GetDataClass();
		foreach ($parsed_sources as $src_key => $src_info)
		{
			// @todo : handle multiple requests on the same source
			$src_from = reset($src_info);
			$storage = QApp::GetStorage($src_key);
			
			$property_reflection = null;
			$is_collection = null;
			$src_from_types = static::DetermineFromTypes($storage_model, $src_from, $is_collection, $property_reflection);
			
			$sql_filter = true;
			// $storage_model, $src_from
			if (($security_filter = \QModel::GetFinalSecurityForAppProperty($src_from, 'filter', $property_reflection)))
			{
				if (count($security_filter) > 1)
					throw new \Exception('Multiple filters are not supported');
				$sql_filter = \QModel::ExtractSQLFilter(reset($security_filter), \Omi\User::GetGroupsList(), \QModel::GetFinalSecurityForAppProperty($src_from, 'relation', $property_reflection));
			}
			
			foreach ($src_from_types ?: [] as $src_from_types_ty)
			{
				if (method_exists($src_from_types_ty, 'GetFinalSecurityForAppProperty__ON_TOP'))
				{
					$sql_filter = $src_from_types_ty::GetFinalSecurityForAppProperty__ON_TOP($src_from, $sql_filter, $property_reflection);
				}
			}
			
			if ($sql_filter === false)
			{
				$result[$src_key] = null;
			}
			else
			{
				if ($property_reflection && ($property_reflection->storage['engine'] === 'model'))
				{
					if ((!$src_from_types) || (count($src_from_types) !== 1))
						throw new \Exception('Only one storage engine is supported by the `model` storage');
					$model_type = reset($src_from_types);
					$result[$src_key] = $model_type::ApiQuery($storage_model, $view_tag ? [$src_from, $view_tag] : $src_from, $src_from_types, $selector, $parameters, $only_first, $id, $skip_security, $sql_filter && is_string($sql_filter) ? [$src_from, $sql_filter] : null);
				}
				else if (($extrenalEngine = $property_reflection->storage['extrenalEngine']))
				{
					$storage = \QApp::GetStorage($extrenalEngine);
					if (!$storage)
						throw new \Exception('Missing extrenalEngine: '.$extrenalEngine);
					$result[$src_key] = $storage::ApiQuery($storage_model, $view_tag ? [$src_from, $view_tag] : $src_from, $src_from_types, $selector, $parameters, $only_first, $id, $skip_security, $sql_filter && is_string($sql_filter) ? [$src_from, $sql_filter] : null);
				}
				else
				{
					$result[$src_key] = $storage::ApiQuery($storage_model, $view_tag ? [$src_from, $view_tag] : $src_from, $src_from_types, $selector, $parameters, $only_first, $id, $skip_security, $sql_filter && is_string($sql_filter) ? [$src_from, $sql_filter] : null);
				}
			}
		}
		
		$return_val = (!$result) ? null : ((count($result) === 1) ? reset($result) : $result);

		if (static::$DebugApi && static::DebugEndApi($debug_uidf))
			static::DebugQuery($return_val, $from, $orig_selector, $parameters, $only_first, $id);
		
		return $return_val;
	}

	protected static function ApplyStates(QIModel $model, $selector, $state)
	{
		$model->_ts = $state;
		if ($model instanceof QIModelArray)
		{
			foreach ($model as $k => $obj)
			{
				if ($model->_tsp[$k] === null)
					$model->_tsp[$k] = $state;
				if ($obj instanceof QIModel)
					static::ApplyStates($obj, $selector, $state);
			}
		}
		else if ($model->_ts === null)
		{
			foreach ($selector as $k => $sub_sel)
			{
				$obj = $model->$k;
				if ($obj instanceof QIModel)
					static::ApplyStates($obj, $sub_sel, $state);
			}
		}
	}
	
	public static function SecureStates($parent_data, $property, $state = null, $selector = null)
	{
		$allowed_states = QModel::TransformCreate | QModel::TransformDelete | QModel::TransformUpdate;
		
		$data = $parent_data->$property;
		// ensure a proper state
		if ($state === null)
			$state = $allowed_states;
		else
			$state = ((int)$state) & $allowed_states;
		
		if ($state !== null)
		{
			if ($data instanceof QIModel)
			{
				if ($selector && (($prop_sel = $selector[$property]) !== null))
					static::ApplyStates($data, $prop_sel, $state);
				else if ($data instanceof QIModelArray)
				{
					foreach ($data as $k => $obj)
					{
						if ($data->_tsp[$k] === null)
							$data->_tsp[$k] = $state;
					}
				}
				else if ($data->_ts === null)
					$data->_ts = $state;
			}
			else if ($data->_ts === null)
			{
				// we can only have an unset
				if ($state === QModel::TransformDelete)
					$parent_data->_ts = QModel::TransformUpdate;
				else
					$parent_data->_ts = $state;
			}
		}
		
		if ($data instanceof QModelArray)
		{
			$has_tsp = $data->_tsp;
			foreach ($data as $key => $obj)
			{
				if ($has_tsp)
					$data->_tsp[$key] = ((int)$data->_tsp[$key]) & $allowed_states;
				if (($obj instanceof QIModel) && ($obj->_ts !== null))
					$obj->_ts = ((int)$obj->_ts) & $allowed_states;
			}
		}
		else if ($data instanceof QIModel)
		{
			if ($data->_ts !== null)
				$data->_ts = ((int)$data->_ts) & $allowed_states;
		}
		
		return null;
	}
	
	/**
	 * @api.enable
	 * 
	 * @param string $destination
	 * @param QIModel $data
	 * @return mixed
	 * @throws Exception
	 */
	public static function Insert($destination, $data, $selector = null)
	{
		return static::Save($destination, $data, QIModel::TransformCreate, $selector, null, false);
	}
	
	/**
	 * @api.enable
	 * 
	 * @param string $destination
	 * @param QIModel $data
	 * @return mixed
	 * @throws Exception
	 */
	public static function Merge($destination, $data, $selector = null)
	{
		return static::Save($destination, $data, QIModel::TransformMerge, $selector, null, false);
	}
	
	/**
	 * @api.enable
	 * 
	 * @param string $destination
	 * @param QIModel $data
	 * @return mixed
	 * @throws Exception
	 */
	public static function Update($destination, $data, $selector = null)
	{
		return static::Save($destination, $data, QIModel::TransformUpdate, $selector, null, false);
	}
	
	/**
	 * @api.enable
	 * 
	 * @param string $from
	 * @param QIModel|string $data_or_id
	 * @return mixed
	 * @throws Exception
	 */
	public static function Delete($from, $data_or_id, $selector = null)
	{
		$is_scalar = is_scalar($data_or_id);
		$data = $is_scalar ? null : $data_or_id;
		$id = $is_scalar ? $data_or_id : null;
		return static::Save($from, $data, QIModel::TransformDelete, $selector, $id, false);
	}
	
	/**
	 * @api.enable
	 * 
	 * @param string $from
	 * @param integer|string $id
	 * @param string $selector
	 * 
	 * @return mixed
	 */
	public static function DeleteById($from, $id, $selector = null)
	{
		return static::Delete($from, $id, $selector);
	}
	
	/**
	 * @api.enable
	 * 
	 * @param string $from
	 * @param string $selector
	 * @param array $parameters
	 * 
	 * @return mixed
	 */
	public static function QueryFirst($from, $selector = null, $parameters = null)
	{
		return static::Query($from, $selector, $parameters, true);
	}
	
	/**
	 * Extract source identity from $from or $destination
	 * 
	 * Examples: 
	 *				src://source1/Orders;src://source2/Customers;
	 *				//source1/Orders;src://source2/Customers;
	 *				/Orders
	 *				Orders
	 * 
	 *				#ClassName
	 *				#ClassName;#ClassName;
	 * 
	 * @param string $sources
	 * @return string[][]
	 */
	public static function ParseSourceInfo($sources)
	{
		if (!$sources)
			return [null,null];
		
		$parsed_sources = [];
		
		$sources_list = preg_split("/\\;/us", $sources, -1, PREG_SPLIT_NO_EMPTY);
		foreach ($sources_list as $src)
		{
			$matches = null;
			// src://source1/Orders
			$ok = preg_match_all("/^\\s*(?:(?:(?:src\\:)?\\/\\/(#?[\\w+\\\$\\\\]+))?\\s*\\/)?(.*)/us", $src, $matches, PREG_SET_ORDER);
			if (!$ok)
				throw new Exception("Parsing source information error");
			
			list(,$parsed_src, $parsed_from) = $matches[0];
			if (!($parsed_from || $parsed_src))
				continue;
			
			if ($parsed_from{0} === "#")
			{
				$type_name = substr($parsed_from, 1);
				$parsed_from = \QApp::GetDefaultAppPropertyForTypeValues($type_name);
				if (!$parsed_from)
					throw new Exception("Unable to find default property in root data class for: ".$parsed_from);
			}
			
			if ($parsed_src)
				$parsed_sources[$parsed_src][] = $parsed_from;
			else
				$parsed_sources[0][] = $parsed_from;
		}
		
		if (empty($parsed_sources))
			return [null, null];
		
		return $parsed_sources;
	}
	
	public static function DetermineFromTypes($model_class, $from, &$is_collection = null, &$property_reflection = null)
	{
		$from = trim($from);
		$m_type = QModel::GetTypeByName($model_class);
		if (!$m_type)
			throw new Exception("Missing model class for: ".$model_class);
		$property_reflection = 
			$m_proprety = $m_type->properties[$from];
		if (!$m_proprety)
			throw new Exception("Missing model property: ".$model_class."::".$from);
		
		$is_collection = $m_proprety->hasCollectionType();
		
		return $is_collection ? $m_proprety->getCollectionType()->getAllInstantiableReferenceTypes() : $m_proprety->getAllInstantiableReferenceTypes();
	}
	
	/**
	 * Calls for a class/method on a remote app
	 * 
	 * @param string $url
	 * @param string $class_method
	 * @param mixed[] $arguments
	 * @param mixed[] $args_selector
	 * 
	 * @return mixed
	 * @throws Exception
	 */
	public static function Remote($url, $class_method, $arguments = [], $args_selector = [])
	{
		list($class, $method) = explode("::", $class_method);
		$args_data = self::ToArray($arguments);
		$args_data["_q_"] = $class.".".$method;
		// $args_str = json_encode($arguments);
		
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(["__qFastAjax__" => 1, "_qb0" => $args_data]));
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_MAXREDIRS, 3);
		curl_setopt($curl, CURLOPT_POSTREDIR, 1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($curl);
		if ($response === false)
			throw new Exception("Invalid response from: ".$url."\n\n".curl_error($curl));

		$json_decode = json_decode($response, true);
		if (!$json_decode)
			return null;
		$resp_data = $json_decode[0];
		if (!$resp_data)
			return null;
		
		if (is_bool($json_decode[0]))
			return $json_decode[0];

		return QModel::FromArray($json_decode[0]);
	}
	
	/**
	 * Transforms $data into a PHP array. 
	 * The function avoids recursion
	 * Also 2 objects of the same class and same id (getId() is used), will not be included twice.
	 * 
	 * @return array
	 */
	public static function ToArray($data, $selector = null, $include_nonmodel_properties = false, $with_type = true, $with_hidden_ids = true, $ignore_nulls = true, &$refs = null, &$refs_no_class = null)
	{
		if ($refs === null)
			$refs = [];
		
		if ($data instanceof QIModel)
			return $data->toArray($selector, $include_nonmodel_properties, $with_type, $with_hidden_ids, $ignore_nulls, $refs, $refs_no_class);
		
		if ($selector !== null)
		{
			if (is_string($selector))
				$selector = qParseEntity($selector);
			else if (!(($selector !== null) && is_array($selector)))
				return;
		}
		
		$ty = gettype($data);
		/* "boolean" "integer" "double" "string" "array" "object" "resource" "NULL" "unknown type"*/
		switch ($ty)
		{
			case "NULL":
			case "string":
			case "integer":
			case "double":
			case "boolean":
			{
				return $data;
			}
			case "array":
			{
				$arr = [];
				foreach ($data as $k => $v)
					$arr[$k] = static::ToArray($v, $selector, $include_nonmodel_properties, $with_type, $with_hidden_ids, $ignore_nulls, $refs, $refs_no_class);
				return $arr;
			}
			case "object":
			{
				$obj_class = get_class($data);
				$was_included = false;
				if ($refs_no_class === null)
					$refs_no_class = [];
				if (($refs_class = $refs_no_class[$obj_class]) && in_array($data, $refs_class, true))
					$was_included = true;
				else
					$refs_no_class[$obj_class][] = $data;

				$arr = [];
				if ($with_type)
					$arr["_ty"] = $obj_class;
				if (!$was_included)
				{
					foreach ($data as $k => $v)
						$arr[$k] = static::ToArray($v, $selector, $include_nonmodel_properties, $with_type, $with_hidden_ids, $ignore_nulls, $refs, $refs_no_class);
				}
				return $arr;
			}
			default:
				return null;
		}
	}
	
	public static function FromArray()
	{
		//
	}
	
	public static function DebugCleanupParams($args)
	{
		if (!is_array($args))
			return $args;
		$ret = [];
		foreach ($args as $k => $v)
		{
			if ($k{0} === '_')
				continue;
			// $ret[$k] = ($v instanceof \QIModel) ? $v->toArray(null, true, false) : $v;
			$ret[$k] = $v;
		}
		return $ret;
	}
	
	public static function DebugCall($result, $class_name, $method, $args)
	{
		$url = "/RESTFul/".str_replace("\\", ".", $class_name)."/{$method}/";
		$args = $args;
		return static::DebugCommon($result, $url, $args, "CALL");
	}
	
	public static function DebugCommon($result, $url, $args, $url_tag = "CALL")
	{
		$args = static::DebugCleanupParams($args);
		$key = sha1(serialize([$url, $args]));
		if (\QAutoload::$DebugStacks[$key])
			return;
		if ($result)
			$args[">RESULT"] = $result;
		ob_start();
		if ($args)
		{
			$bag = [];
			qDSDumpVar([$url_tag => $url, "ARGS" => $args], 12, $bag, 0, null, null, true);
		}
		else
			qDSDumpVar([$url_tag => $url." (no args)"], 12, $bag, 0, null, null, true);
		
		return (\QAutoload::$DebugStacks[$key] = ob_get_clean());
	}
	
	public static function DebugSave($result, $destination, $data, $state, $selector, $id, $data_is_collection)
	{
		$url = "/RESTFul/".str_replace("\\", ".", $destination)."/";
		$args = [];
		if ($data)
			$args["DATA"] = $data;
		if ($state)
		{
			switch ($state)
			{
				case \QModel::TransformCreate:
				{
					$args["State"] = "CREATE";
					break;
				}
				case \QModel::TransformUpdate:
				{
					$args["State"] = "UPDATE";
					break;
				}
				case \QModel::TransformDelete:
				{
					$args["State"] = "DELETE";
					break;
				}
				case \QModel::TransformMerge:
				{
					$args["State"] = "MERGE";
					break;
				}
				default:
				{
					$args["State"] = $state;
					break;
				}
			}
			
		}
		if ($selector)
			$args["selector"] = preg_replace("/(\\s+)/is", "", is_array($selector) ? qImplodeEntity ($selector) : $selector);
		if ($id)
			$args["id"] = $id;
		if ($data_is_collection)
			$args["isCollection"] = $data_is_collection;
		return static::DebugCommon($result, $url, $args, "SAVE");
	}
	
	public static function DebugQuery($result, $from, $selector, $parameters, $only_first, $id)
	{
		$url = "/RESTFul/".str_replace("\\", ".", $from)."/";
		$args = [];
		if ($selector)
			$args["selector"] = preg_replace("/(\\s+)/is", "", is_array($selector) ? qImplodeEntity ($selector) : $selector);
		if ($parameters)
			$args["parameters"] = static::DebugCleanupParams($parameters);
		if ($id)
			$args["id"] = $id;
		if ($only_first)
			$args["only_first"] = $only_first;
		// do not debug if nested !
		return static::DebugCommon($result, $url, $args, "QUERY");
		/*
		$parameters = static::DebugCleanupParams($parameters);
		$key = sha1(serialize([$from, $selector, $parameters, $only_first, $id]));
		if (\QAutoload::$DebugStacks[$key])
			return;
		ob_start();
		if ($parameters)
			qDSDumpVar(["QUERY FROM" => "/RESTFul/".str_replace("\\", ".", $from)."/?selector=".preg_replace("/(\\s+)/is", "", is_array($selector) ? qImplodeEntity ($selector) : $selector ), "ARGS" => $parameters]);
		else
			qDSDumpVar(["QUERY" => "/RESTFul/".str_replace("\\", ".", $from)."/{$selector}"]);
		return (\QAutoload::$DebugStacks[$key] = ob_get_clean());*/
	}
	
	public static function DebugStartApi()
	{
		return static::$DebugUidf ? false : (static::$DebugUidf = uniqid());
	}
	
	public static function DebugEndApi($debug_uidf)
	{
		if ($debug_uidf && ($debug_uidf === static::$DebugUidf))
		{
			static::$DebugUidf = null;
			return true;
		}
		else
			return false;
	}
	
	/**
	 * @api.enable
	 * 
	 * @param string $from
	 * @param string $selector
	 * @param array $parameters
	 * 
	 * @return QIModel
	 */
	public static function QSync($from, $selector = null, $parameters = null)
	{
		return static::__QSync($from, $selector, $parameters);
	}
	
	/**
	 * @api.enable
	 * 
	 * @param string $from
	 * @param string $selector
	 * @param array $parameters
	 * 
	 * @return mixed
	 */
	public static function QSyncFirst($from, $selector = null, $parameters = null)
	{
		return static::__QSync($from, $selector, $parameters, true);
	}

	/**
	 * @api.enable
	 * 
	 * @param string $from
	 * @param integer|string $id
	 * @param string $selector
	 * 
	 * @return QIModel
	 */
	public static function QSyncById($from, $id, $selector = null)
	{
		return static::__QSync($from, $selector, null, true, $id);
	}
	
	/**
	 * 
	 * @param string $from
	 * @param string|array $selector
	 * @param array $parameters
	 * @param boolean $only_first
	 * @param int $id
	 * @return QIModel
	 * @throws Exception
	 */
	private static function __QSync($from, $selector = null, $parameters = null, $only_first = false, $id = null)
	{
		$dataCls = \QApp::GetDataClass();

		// translate from view name to the actual property
		$initialFrom = $from;
		$from = static::GetFrom($from);

		if (!$parameters)
			$parameters = [];

		if ($dataCls::$_USE_SEPARATE_INSTANCES)
			static::SetupOwnerFilter($from, $parameters, $id);

		if (property_exists($dataCls, '$_USE_SECURITY_FILTERS') && $dataCls::$_USE_SECURITY_FILTERS)
		{
			$usr = \Omi\User::GetCurrentUser();
			if (!$usr)
				return null;
		}

		if ($selector === null)
		{
			$selector = $initialFrom ? 
				($id || $only_first) ? $dataCls::GetFormEntity_Final($initialFrom) : $dataCls::GetListEntity_Final($initialFrom) : null;
		}

		$fromParams = ($id || $only_first) ? $dataCls::GetFormBinds($initialFrom) : $dataCls::GetListBinds($initialFrom);

		if ($fromParams)
		{
			if (!$parameters)
				$parameters = [];
			$parameters = array_merge($parameters, $fromParams);
		}

		if (static::$_Caller_Company_In_Callee_Box && static::$_Partner_Call)
			$parameters["PartnerPriceProfile"] = static::$_Caller_Company_In_Callee_Box->BuyPriceProfile->getId();

		$parsed_sources = $from ? static::ParseSourceInfo($from) : [null, null];
		if (!$parsed_sources)
			throw new Exception("Source information error");

		if (is_string($selector))
			$selector = qParseEntity($selector);

		$result = [];
		foreach ($parsed_sources as $src_key => $src_info)
		{
			// @todo : handle multiple requests on the same source
			$src_from = reset($src_info);
			$storage = QApp::GetStorage($src_key);
			$storage_model = QApp::GetDataClass();
			$src_from_types = static::DetermineFromTypes($storage_model, $src_from);
			$result[$src_key] = $storage::ApiQuerySync($storage_model, $src_from, $src_from_types, $selector, $parameters, $only_first, $id);
		}
		
		$ret = !$result ? null : ((count($result) === 1) ? reset($result) : $result);
		return $ret;
	}

	/**
	 * @api.enable
	 * 
	 * @param string $destination
	 * @param QIModel $data
	 * @param string $selector
	 * 
	 * @return mixed
	 * @throws Exception
	 */
	public static function Import($destination, $data, $selector = true)
	{
		static::$_InImportProcess = true;
		$parsed_sources = $destination ? static::ParseSourceInfo($destination) : [null, null];
		if (!$parsed_sources)
			throw new Exception("Source information error");
		
		if (is_string($selector))
			$selector = qParseEntity($selector);

		$result = [];
		foreach ($parsed_sources as $src_key => $src_info)
		{
			// @todo : handle multiple requests on the same source
			$src_from = reset($src_info);
			$storage = QApp::GetStorage($src_key);
			$storage_model = QApp::GetDataClass();
			$is_collection = false;
			$src_from_types = static::DetermineFromTypes($storage_model, $src_from, $is_collection);

			// we will need to convert data here
			if ($src_from_types)
			{
				if ($data)
				{
					if (is_array($data))
					{
						$decode_type = reset($src_from_types) ? reset($src_from_types).($is_collection ? "[]" : "") : "auto";
						if ($is_collection)
							$data = [$data];
						$data = QModel::FromArray($data, $decode_type);
					}
				}

				if ($is_collection && $data && (!qis_array($data)))
				{
					$_item = $data;
					$data = new QModelArray();
					$data[] = $_item;
				}

				if ($is_collection && $data && (!qis_array($data)))
				{
					$_item = $data;
					$data = new QModelArray();
					$data[] = $_item;
				}
			}
			$result[$src_key] = $storage::ApiImport($storage_model, $src_from, $src_from_types, $data, QIModel::TransformMerge, $selector);
		}

		static::$_InImportProcess = false;
		return !$result ? null : ((count($result) === 1) ? reset($result) : $result);
	}
	/**
	 * Returns true if in import process, false otherwise
	 * 
	 * @return boolean
	 */
	public static function InImportProcess()
	{
		return static::$_InImportProcess;
	}
	
	/**
	 * Returns the remote partner
	 * Available after remote requests
	 * 
	 * @return \Omi\Comm\Reseller
	 */
	public static function GetLastCalledPartner()
	{
		return static::$_LastCalledPartner;
	}
	/**
	 * Returns the remote partner
	 * Available after remote requests
	 * 
	 * @return \Omi\Comm\Reseller
	 */
	public static function GetCallerPartner()
	{
		return \QWebRequest::IsRemoteRequest() ? \QApi::$_Caller_Company_In_Callee_Box : \Omi\App::GetUserOwner();
	}
	
	/**
	 * @api.enable
	 * 
	 * @param string $from
	 * @param integer|string $id
	 * @param string $selector
	 * 
	 * @return QIModel
	 */
	public static function QueryById($from, $id, $selector = null)
	{
		return static::Query($from, $selector, null, true, ["Id" => $id]);
	}
		
	/**
	 * @api.enable
	 * 
	 * @param string $from
	 * @param string $selector
	 * @param array $parameters
	 * 
	 * @return QIModel
	 */
	public static function Query($from, $selector = null, $parameters = null, $only_first = false, $id = null)
	{
		$dataCls = \QApp::GetDataClass();

		$initialFrom = $from;
		$from = static::GetFrom($from);
		
		if (!$parameters)
			$parameters = [];

		if (property_exists($dataCls, '$_USE_SECURITY_FILTERS') && $dataCls::$_USE_SECURITY_FILTERS)
		{
			$usr = \Omi\User::GetCurrentUser();
			if (!$usr)
				return null;
		}
		
		if ($selector === null)
		{
			$selector = $initialFrom ? 
				($id || $only_first) ? $dataCls::GetFormEntity_Final($initialFrom) : $dataCls::GetListEntity_Final($initialFrom) : null;
			$selector_gen_form = $initialFrom ? 
				($id || $only_first) ? $dataCls::GetEntityForGenerateForm_Final($initialFrom) : $dataCls::GetEntityForGenerateList_Final($initialFrom) : null;
			
			if ($initialFrom)
			{
				$view_class_name = \Omi\TFS\View\Controller::$GenNamespace."\\".$initialFrom;
				$append_extra_selector = null;
				if (class_exists($view_class_name) && property_exists($view_class_name, 'Extra_Selectors') && $view_class_name::$Extra_Selectors
						&& ($append_extra_selector = $view_class_name::$Extra_Selectors[($id || $only_first) ? 'form' : 'list']))
				{
					# add extra selector to resolve FK/References caption issues
					$selector = qJoinSelectors($selector, $append_extra_selector);
				}
			}
			
			$selector = qJoinSelectors($selector, $selector_gen_form);
			
			if ($selector === null)
			{
				if (\QAutoload::GetDevelopmentMode())
					throw new \Exception("No selector defined for: `{$initialFrom}`/`{$from}`");
			}
		}
		
		$fromParams = ($id || $only_first) ? $dataCls::GetFormBinds($initialFrom) : $dataCls::GetListBinds($initialFrom);

		if ($fromParams)
		{
			if (!$parameters)
				$parameters = [];
			$parameters = array_merge($parameters, $fromParams);
		}

		$q = static::__Query(($initialFrom !== $from) ? [$from, $initialFrom] : $from, $selector, $parameters, $only_first, $id);

		return $q;
	}
	
	/**
	 * Setup owner in binds
	 * 
	 * @param array $parameters
	 * @return null
	*/
	private static function SetupOwnerFilter($from, &$parameters, &$id)
	{
		// if the user is not logged in don't use the owner filter
		$user = \Omi\User::GetCurrentUser();

		//ob_start();
		//qvardump("SetupOwnerFilter", $user, \Omi\App::GetCurrentOwner());
		//file_put_contents("GetToSyncData_dump.html", ob_get_clean(), FILE_APPEND);

		if (!$user)
			return;

		if (!$parameters)
			$parameters = [];

		$prop = \QApp::NewData()->getModelType()->properties[$from];
		if ($prop && $prop->storage && $prop->storage["synchronizable"])
		{
			$parameters["Owner"] = ($owner = \Omi\App::GetCurrentOwner()) ? $owner->getId() : 0;
			if ($id && is_array($id))
				$id["Owner"] = $parameters["Owner"];
		}
	}

	/**
	 * Transforms from view name to property name
	 * 
	 * @param string $destination
	 * @return string
	 */
	public static function GetFrom(string $destination)
	{
		if (static::$_Froms[$destination])
			return static::$_Froms[$destination];
		
		if (substr($destination, 0, 2) === "//")
			return (static::$_Froms[$destination] = $destination);

		$modelType = \QModel::GetTypeByName(\QApp::GetDataClass());
		if (!$modelType->properties)
			return null;

		foreach ($modelType->properties as $prop)
		{
			if ($prop->name === $destination)
				return (static::$_Froms[$destination] = $prop->name);

			if (!$prop->storage || (!($viewsStr = $prop->storage["views"])) || (!($views = explode(",", $viewsStr))))
				continue;

			foreach ($views as $view)
			{
				if ($view === $destination)
					return (static::$_Froms[$destination] = $prop->name);
			}			
		}
		return null;
	}

	/**
	 * @api.enable
	 * 
	 * @param string $destination
	 * @param QIModel $data
	 * @param integer $state
	 * @return mixed
	 * @throws Exception
	 */
	public static function Save($destination, $data, $state = null, $selector = null, $id = null, $data_is_collection = true)
	{
		if (static::$DebugApi)
			$debug_uidf = static::DebugStartApi();
		
		if ($destination === 'Salesforce_Customers')
		{
			// also save in SF
			\Omi\Vf\SalesForce\SalesForceQuery::SaveAccounts($destination, $data, $state, $selector, $id, $data_is_collection);
		}
		else if ($destination === 'Salesforce_Orders')
		{
			// also save in SF
			\Omi\Vf\SalesForce\SalesForceQuery::SaveOrders($destination, $data, $state, $selector, $id, $data_is_collection);
		}
		
		$orig_params = [$destination, $data, $state, $selector, $id, $data_is_collection];
		
		$dataCls = \QApp::GetDataClass();
		
		$initialDestination = $destination;
		$destination = static::GetFrom($destination);
		
		$parsed_sources = $destination ? static::ParseSourceInfo($destination) : [null, null];
		if (!$parsed_sources)
			throw new Exception("Source information error");

		if (is_string($selector))
			$selector = qParseEntity($selector);

		$result = [];
		foreach ($parsed_sources as $src_key => $src_info)
		{
			// @todo : handle multiple requests on the same source
			$src_from = reset($src_info);
			$storage = QApp::GetStorage($src_key);
			$storage_model = QApp::GetDataClass();
			$is_collection = false;
			$property_reflection = null;
			$src_from_types = static::DetermineFromTypes($storage_model, $src_from, $is_collection, $property_reflection);
			
			// we will need to convert data here
			// $array, $type = "auto", $selector = null, $include_nonmodel_properties
			if ($src_from_types)
			{
				if ($data)
				{
					if (is_array($data))
					{
						// determine $data_is_collection - don't use the parameter
						/*==========================determine if data is provided as collection or as single item=========================*/
						$_ft = reset($src_from_types);
						$decode_type = $_ft ? $_ft.($is_collection ? "[]" : "") : "auto";
						
						$data_is_collection = true;
						$_ks = array_keys($data);
						
						$_dmt = \QModel::GetTypeByName($_ft);
						
						foreach ($_ks as $__k)
						{
							if ($_dmt->properties[$__k])
							{
								$data_is_collection = false;
								break;
							}
						}
						/*================================================================================================================*/

						if ((!$data_is_collection) && $is_collection)
							$data = [$data];
						$data = QModel::FromArray($data, $decode_type);
					}
				}
				else if ($id && (!($data instanceof QIModel)))
				{
					$data_ty = reset($src_from_types);
					$data = new $data_ty();
					$data->setId($id);
					if ($is_collection)
					{
						$_item = $data;
						$data = new QModelArray();
						$data[] = $_item;
					}
				}
				
				if ($is_collection && $data && (!qis_array($data)))
				{
					$_item = $data;
					$data = new QModelArray();
					$data[] = $_item;
				}
				
				if ($is_collection && $data && (!qis_array($data)))
				{
					$_item = $data;
					$data = new QModelArray();
					$data[] = $_item;
				}
			}

			// do here security check - mihai - to be removed when security module is implemented
			if ($data && property_exists($dataCls, '$_USE_SECURITY_FILTERS') && $dataCls::$_USE_SECURITY_FILTERS)
			#														if ($dataCls::$_USE_SECURITY_FILTERS && $data)
			{
				$user = \Omi\User::GetCurrentUser();
				// we need to rethink this - now we check only logged in users
				if ($user)
				{
					//if (!$user)
					//	throw new \Exception("No access!");

					$action = (!$state || ($state == \QModel::TransformMerge) || ($state == \QModel::TransformUpdate)) ? "edit" : 
						(($state == \QModel::TransformDelete) ? "delete" : (($state == \QModel::TransformCreate) ? "add" : null));

					if (!$action)
						throw new \Exception("No access");

					$to_check_data = qis_array($data) ? $data : [$data];
					foreach ($to_check_data ?: [] as $itm)
					{
						if (!$itm)
							continue;

						if (!$itm->getId() && ($action === "edit"))
							$action = "add";

						if (!$user->can($action, $initialDestination, $itm))
							throw new \Exception("No access!");
					}
				}
			}

			if ($src_from === 'SupportTickets')
			{
				# qvar_dumpk($storage_model, $src_from, $src_from_types, $data, $state, $selector);
				# throw new \Exception('remake!');
				if ($data && ($first_data = reset($data)))
					$first_data::Api_Save($src_from, $data, $state, $selector, $src_from_types);
			}
			else if ($property_reflection && ($property_reflection->storage['engine'] === 'model'))
			{
				if ((!$src_from_types) || (count($src_from_types) !== 1))
					throw new \Exception('Only one storage engine is supported by the `model` storage');
				$model_type = reset($src_from_types);
				$result[$src_key] = $model_type::ApiSave($storage_model, $src_from, $src_from_types, $data, $state, $selector, $initialDestination);
			}
			else
			{
				$result[$src_key] = $storage::ApiSave($storage_model, $src_from, $src_from_types, $data, $state, $selector, $initialDestination);
			}
		}
		$ret = !$result ? null : ((count($result) === 1) ? reset($result) : $result);

		
		if (static::$DebugApi && static::DebugEndApi($debug_uidf))
		{
			list($orig_destination, $orig_data, $orig_state, $orig_selector, $orig_id, $orig_data_is_collection) = $orig_params;
			static::DebugSave($ret, $orig_destination, $orig_data, $orig_state, $orig_selector, $orig_id, $orig_data_is_collection);
		}
		
		return $ret;
	}
	
	
	/**
	 * 
	 * 
	 * @param \Omi\Comm\Reseller $Partner
	 * @param type $class_method
	 * @param type $arguments
	 * @param type $args_selector
	 * @param type $include_nonmodel_properties
	 */
	public static function CallOnResellerApp_old(\Omi\Comm\Reseller $Partner, $class_method, $arguments = [], $args_selector = [], $include_nonmodel_properties = true)
	{
		// reset the remote patner
		static::$_LastCalledPartner = null;
		if (!$Partner)
			throw new \Exception("Reseller not provided!");

		$Owner = \Omi\App::GetCurrentOwner();
		if (!$Owner)
			throw new \Exception("Cannot determine owner!");

		$user = \Omi\App::GetUserForRemoteCall($Partner);

		if (!$user)
			throw new \Exception("Remote call user not found for partner/supplier ({$Owner->getId()}){$Owner->Name} => ({$Partner->getId()}){$Partner->Name}!");
			
		if (!($pDomain = $Partner->getWLDomain()))
			throw new \Exception("Domain not found for partner {$Partner->Name}");

		$request = null;
		if (defined("MONITOR_REQUESTS") && MONITOR_REQUESTS)
		{
			$req_data = [
				"User" => ($_lusr = \Omi\User::GetCurrentUser()) ? get_class($_lusr)."::".$_lusr->getId() : null,
				"RemoteCallUser" => get_class($user)."::".$user->getId(),
				"Owner" => get_class($Owner)."::".$Owner->getId(),
				"Partner" => get_class($Partner)."::".$Partner->getId(),
				"InRequest" => ($inpreq = (\QWebRequest::IsRemoteRequest() || \QWebRequest::IsAsyncRequest())) ? 1 : 0,
			];

			if ($inpreq)
				$req_data["InRequestTypes"] = (\QWebRequest::IsAsyncRequest() ? "async" : "").(\QWebRequest::IsRemoteRequest() ? (\QWebRequest::IsAsyncRequest() ? "&" : "")."remote" : "");
			$_app = \QApi::Merge("RequestsMonitor", $req_data);
			$request = $_app->RequestsMonitor ? reset($_app->RequestsMonitor) : null;
		}

		static::$_LastCalledPartner = $Partner;
		$ret = static::Remote($pDomain, $class_method, $arguments, $args_selector, $user, $include_nonmodel_properties, $request);
		return $ret;
	}
	
	/**
	 * 
	 * 
	 * @param \Omi\Comm\Reseller $partner
	 * @param string $class_method
	 * @param array $arguments
	 */
	public static function CallOnResellerApp(\Omi\Comm\Reseller $partner, $class_method, $arguments = [])
	{
		static::$_Partner_Call = false;
		$user = \Omi\User::GetCurrentUser();
		//$current_identity = \Omi\User::CheckLogin();
		$enter_impersonate = QQuery('Users.{Impersonate.Id WHERE Id=?}', $user->getId())->Users;
		$enter_impersonate = $enter_impersonate ? reset($enter_impersonate) : null;
		
		if (!$user)
			throw new \Exception("User not logged in!");
		
		if (!($owner = \Omi\App::GetCurrentOwner()))
			throw new \Exception("Ownernot found!");

		$saved_context = [
			'user' => \Omi\User::ResetStaticContext(),
			'app' => \Omi\App::ResetStaticContext(),
			'session' => $_SESSION,
			'session_id' => session_id(),
			// 'remote_req' => \QWebRequest::IsRemoteRequest(),
			'request' => \QWebRequest::ResetStaticContext(), 
		];
		
		static::$_LastCalledPartner = null;
		
		$result = null;
		$saved_caller_owner = static::$_Caller_Company_In_Callee_Box;

		try
		{
			if (headers_sent())
				throw new \Exception('headers_sent() we will not be able to login');
			
			ob_start();
			// unset data
			\QApp::UnsetData();

			// public static function Logout($user_or_email = null, $session_id = null, bool $reset_context = true)
			\Omi\User::Logout(null, null, false);
			session_write_close();
			
			/* $is_my_partner = \QQuery("Partners.{Id WHERE Id=? AND Owner.Id=?}", [$partner->getId(), $owner->getId()])->Partners;
			$is_my_partner = $is_my_partner ? $is_my_partner[0] : null;
			if (!$is_my_partner)
			*/
			
			$arguments = static::CloneDataForFakeRemote($arguments);
			
			if (!$partner->wasSet('Gid'))
				$partner->populate('Gid');

			if ($partner->Gid)
			{
				// from down to up, we are calling an upper partner
				if (!$owner->Gid)
					throw new \Exception('Missing Gid in owner');

				$_ccicb = QQuery("Partners.{Id,Gid,Owner WHERE Id=?}", [$owner->Gid])->Partners;
				$_ccicb = $_ccicb ? $_ccicb[0] : null;
				static::$_Partner_Call = true;
				
				if (!$_ccicb)
					throw new \Exception('Unable to determine Caller Company in Callee Box | '.$owner->Gid);
			}
			else
			{
				$partner_id_in_caller = QQuery("Companies.{Id,Gid,Owner WHERE Gid=? AND Id=Owner.Id}", [$partner->getId()])->Companies;
				$partner_id_in_caller = $partner_id_in_caller ? $partner_id_in_caller[0]->getId() : null;
				if (!$partner_id_in_caller)
					throw new \Exception('Unable to determine Callee Box');
				$_ccicb = QQuery("Companies.{Id,Gid,Owner WHERE Owner.Id=? AND Gid=?}", [$partner_id_in_caller, $owner->getId()])->Companies;
				$_ccicb = $_ccicb ? $_ccicb[0] : null;

				if (!$_ccicb)
					throw new \Exception('Unable to determine Caller Company in Callee Box | '.$partner_id_in_caller." | ".$owner->getId());
			}

			$_ccicb->populate('*');
			
			static::$_Caller_Company_In_Callee_Box = $_ccicb;

			//  valid characters are a-z, A-Z, 0-9 and '-,'
			$new_session_id = preg_replace("/[^a-zA-Z0-9\\-]/us", '-', uniqid("", true));
			session_id($new_session_id);
			session_start();

			\QWebRequest::RestoreStaticContext([
								"FastAjax" => true,
								"RemoteRequest" => true, 
							]);
			
			$possible_users = QQuery("Users.{Username, Password, PrevPwd, Active, BackendAccess, IsRemoteCallUser, UsedToCall "
						. " WHERE Owner.Id=? AND IsRemoteCallUser=? AND Access.Id=? AND UsedToCall=?}", 
						[$owner->getId(), 1, $partner->getId(), 1])->Users;

			$remote_user = $possible_users ? $possible_users[0] : null;
			
			if (!$remote_user)
				throw new \Exception('Missing remote user. Owner['.$owner->getId().']: '.$owner->getModelCaption().'; Partner['.$partner->getId().']: '.$partner->getModelCaption());
						
			$user_or_email = $remote_user->Username;
			if (!$user_or_email)
				throw new \Exception('Missing remote user username. Owner['.$owner->getId().']: '.$owner->getModelCaption().'; Partner['.$partner->getId().']: '.$partner->getModelCaption());
			$password = $remote_user->Password;
			if (!$password)
				throw new \Exception('Missing remote user password. Owner['.$owner->getId().']: '.$owner->getModelCaption().'; Partner['.$partner->getId().']: '.$partner->getModelCaption());

			$login_res = \Omi\User::LoginInternal($user_or_email, $password, session_id(), false);

			//qvardump("\Active logins", \Omi\User::$ActiveLogins);
			//$identity = \Omi\User::CheckLogin();
			//qvardump("\$identity", $identity);


			//qvardump("IDENTITY BEFORE :: ", $identity, $current_identity);

			if ($login_res !== true)
			{
				if (\QAutoload::GetDevelopmentMode())
					qvar_dump($login_res);
				throw new \Exception('Login failed for: '.$user_or_email);
			}
			
			$login_identity = \Omi\User::CheckLogin();
			if (!$login_identity)
				throw new \Exception('Login identity failed for: '.$user_or_email);

			// $login_identity->populate('*,User.{*, Context.*, Owner.*},Session.*');
			// serialize and deserialize to break references
			
			list($class_name, $method) = explode("::", $class_method, 2);
			
			//qvardump("\$remote_user", \Omi\User::GetCurrentUser(), $remote_user, $class_name, $method, $arguments);

			$result = call_user_func_array([$class_name, $method], $arguments);
			
			static::$_LastCalledPartner = $partner;
		}
		/*
		catch (\Exception $ex)
		{
			echo \QErrorHandler::GetExceptionToHtml($ex);
			throw $ex;
		}
		*/
		finally
		{
			static::$_Caller_Company_In_Callee_Box = $saved_caller_owner;
			
			\Omi\User::Logout();
			session_write_close();
			
			session_id($saved_context['session_id']);
			session_start();
			// session_id($saved_context['session_id']);
			foreach ($saved_context['session'] as $k => $v)
				$_SESSION[$k] = $v;
			
			$exit_impersonate = QQuery('Users.{Impersonate.Id WHERE Id=?}', $enter_impersonate->getId())->Users;
			$exit_impersonate = $exit_impersonate ? reset($exit_impersonate) : null;
			
			$enter_impersonate_id = $enter_impersonate->Impersonate ? $enter_impersonate->Impersonate->getId() : null;
			$exit_impersonate_id = $exit_impersonate->Impersonate ? $exit_impersonate->Impersonate->getId() : null;
			if ($enter_impersonate_id != $exit_impersonate_id)
			{
				if ($enter_impersonate)
				{
					$enter_impersonate->setImpersonate($enter_impersonate->Impersonate);
					$enter_impersonate->save("Impersonate");

					\Omi\App::GetSecurityUser(true);
				}
			}
			
			$login_res = \Omi\User::LoginInternal($user->Username, $user->Password, $saved_context['session_id'], false);
			if (!$login_res)
				throw new \Exception('Login failed for: '.$user->Username);
			$login_identity = \Omi\User::CheckLogin();
			if (!$login_identity)
				throw new \Exception('Login identity failed for: '.$user->Username);

			// restore context
			\Omi\User::RestoreStaticContext($saved_context['user']);
			\Omi\App::RestoreStaticContext($saved_context['app']);

			\QWebRequest::RestoreStaticContext($saved_context['request']);

			echo ob_get_clean();
		}
		
		static::$_Partner_Call = false;
		return $result;
	}
	
	/**
	 * Calls for a class/method on a remote app
	 * 
	 * @param string $url
	 * @param string $class_method
	 * @param mixed[] $arguments
	 * @param mixed[] $args_selector
	 * 
	 * @return mixed
	 * @throws Exception
	 */
	public static function Remote_old($url, $class_method, $arguments = [], $args_selector = [], $user = null, $include_nonmodel_properties = false, $request = null)
	{
		if (file_exists("debug/srv_rsp.html"))
			unlink("debug/srv_rsp.html");

		if (file_exists("debug/srv_rsp_data.html"))
			unlink("debug/srv_rsp_data.html");

		if (!$url)
			throw new \Exception("Url not found!");
		if (!$class_method)
			throw new \Exception("Class method not provided!");

		list($class, $method) = explode("::", $class_method);
		$args_data = self::ToArray($arguments, null, $include_nonmodel_properties);

		if (!$args_data)
			$args_data = [];

		// go through args and set them up to empty string
		foreach ($args_data as $key => $value)
			$args_data[$key] = empty($value) ? 0 : $value;

		$args_data["_q_"] = $class.".".$method;

		$postData = http_build_query([
				"__qFastAjax__" => 1, 
				"__remoteRequest__" => 1, 
				"_qb0" => $args_data
			]
		);

		//file_put_contents("post_data.txt", $postData);

		if (($monitor_req = (defined("MONITOR_REQUESTS") && MONITOR_REQUESTS)))
		{
			$req_data = $request ? $request->toArray("Id") : [];
			if (!$request || !$request->User)
				$req_data["User"] = ($_lusr = \Omi\User::GetCurrentUser()) ? get_class($_lusr)."::".$_lusr->getId() : null;
			if (!$request || !$request->Owner)
				$req_data["Owner"] = ($_cown = \Omi\App::GetCurrentOwner()) ? get_class($_cown)."::".$_cown->getId() : null;
			$req_data["Url"] = $url;

			/*
			ob_start();
			debug_print_backtrace();
			$req_data["Stack"] = ob_get_clean();
			*/

			$req_data["Type"] = "remote";
			$req_data["Date"] = date("Y-m-d H:i:s");
			$req_data["RequestData"] = $postData;
			$_app = \QApi::Merge("RequestsMonitor", $req_data);
			$request = $_app->RequestsMonitor ? reset($_app->RequestsMonitor) : null;
		}
		
		if (\QAutoload::GetDevelopmentMode())
			$curl = curl_init($url.((strpos($url, "?") === false) ? "?" : "&")."dev_mode=1");
		else
			$curl = curl_init($url);
		
		//curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, 1);

		// if we have user send the auth credentials
		if ($user)
			curl_setopt($curl, CURLOPT_USERPWD, $user->Username.":".$user->Password);

		curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);

		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_MAXREDIRS, 3);
		curl_setopt($curl, CURLOPT_POSTREDIR, 1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLINFO_HEADER_OUT, true);

		// curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 1); 
		//curl_setopt($curl, CURLOPT_TIMEOUT, 20); //timeout in seconds

		$response = curl_exec($curl);

		if ($monitor_req)
		{
			$req_data = $request ? $request->toArray("Id") : [];
			$req_data["ResponseData"] = $response;
			$_app = \QApi::Merge("RequestsMonitor", $req_data);
		}

		try
		{
			if (!is_dir("debug"))
				mkdir("debug", 0775);
			chmod("debug", 0775);
		}
		catch (\Exception $ex)
		{
			throw $ex;
		}

		// write to file - for debug purpose - to be removed
		//ob_start();
		//qvardump($url, $class_method, $arguments, $response);
		//file_put_contents("debug/srv_rsp.html", ob_get_clean());

		if ($response === false)
			throw new Exception("Invalid response from: ".$url."\n\n".curl_error($curl));
		$json_decode = json_decode($response, true);

		if (isset($json_decode["EXCEPTION"]))
		{
			qvardump($json_decode);
			throw new \Exception($json_decode["EXCEPTION"]["Message"]);
		}

		if (!$json_decode)
			return null;

		$resp_data = $json_decode[0];
		if (!$resp_data)
			return null;

		if (is_bool($json_decode[0]))
			return $json_decode[0];

		$data = \QModel::FromArray($json_decode[0]);

		// write to file - for debug purpose - to be removed
		//ob_start();
		//qvardump($url, $class_method, $arguments, $data);
		//file_put_contents("debug/srv_rsp_data.html", ob_get_clean());

		return $data;
	}

	/**
	 * Execute script that is given in url async
	 * Params is an associative array that will be transformed in post (in the script)
	 * 
	 * @param string $class_method
	 * @param array $params
	 */
	public static function ExecAsync($class_method, $params = [])
	{
		if (!$class_method)
			throw new \Exception("Class method not provided!");

		$url = \QWebRequest::GetBaseUrl();

		list($class, $method) = (strpos($class_method, "::") === false) ? [\QApp::GetDataClass(), $class_method] : explode("::", $class_method);

		if (!$class || !$method)
			throw new \Exception("Invalid call param in exec async {$class_method}");

		if (!class_exists($class))
			throw new \Exception("Class {$class} not found!");

		if (!method_exists($class, $method))
			throw new \Exception("Method {$method} not found on class {$class}!");

		$loggedInUser = \Omi\User::GetCurrentUser();
		if (!$loggedInUser)
			throw new \Exception("Logged in user not found!");

		$args_data = self::ToArray($params, null, true);

		if (!$args_data)
			$args_data = [];

		// go through args and set them up to empty string
		foreach ($args_data as $key => $value)
			$args_data[$key] = empty($value) ? 0 : $value;
		$args_data["_q_"] = $class.".".$method;

		$toSendData = http_build_query([
			"_qb0" => $args_data, 
			"__qFastAjax__" => 1, 
			"__asyncRequest__" => 1
		]);

		$parts = parse_url($url);
		$isHttps = ($parts && $parts["scheme"] && ($parts["scheme"] === "https"));

		$host = ($isHttps ? "ssl://" : "").$parts['host'];
		$port = $isHttps ? 443 : ($parts["port"] ? $parts["port"] : 80);

		$data = "POST ".$parts['path']." HTTP/1.1\r\n";
		$data .= "Authorization: Basic ".  base64_encode($loggedInUser->Username . ":" . $loggedInUser->Password) . "\r\n";
		$data .= "SessId: ".  session_id() . "\r\n";
		$data .= "IP: ".  Q_REMOTE_ADDR . "\r\n";
		if (\QWebRequest::IsRemoteRequest())
		{
			$data .= "RemoteRequest: 1\r\n";
		}
		$data .= "Host: ".$parts['host']."\r\n";
		$data .= "Accept: */*\r\n";
		$data .= "Content-Length: ".strlen($toSendData)."\r\n";
		$data .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$data .= "Connection: Close\r\n\r\n";

		if (isset($toSendData))
			$data .= $toSendData;

		//self::DebugRemote($class_method, $params, $data);

		//qvardump($data);
		if (defined("MONITOR_REQUESTS") && MONITOR_REQUESTS)
		{			
			
			$req_data = [
				"Url" => $url,
				"User" => ($_lusr = \Omi\User::GetCurrentUser()) ? get_class($_lusr)."::".$_lusr->getId() : null,
				"Owner" => ($_cown = \Omi\App::GetCurrentOwner()) ? get_class($_cown)."::".$_cown->getId() : null,
				"Date" => date("Y-m-d H:i:s"),
				"InRequest" => ($inpreq = (\QWebRequest::IsRemoteRequest() || \QWebRequest::IsAsyncRequest())) ? 1 : 0,
				"Type" => "async",
				"RequestData" => $data
			];

			/*
			ob_start();
			debug_print_backtrace();
			$req_data["Stack"] = ob_get_clean();
			*/
			if ($inpreq)
				$req_data["InRequestTypes"] = (\QWebRequest::IsAsyncRequest() ? "async" : "").(\QWebRequest::IsRemoteRequest() ? (\QWebRequest::IsAsyncRequest() ? "&" : "")."remote" : "");
			
			\QApi::Merge("RequestsMonitor", $req_data);
		}

		$fp = fsockopen($host, $port, $errno, $errstr, 30);
		fwrite($fp, $data);
		fclose($fp);
	}

	public static function DumpToFile()
	{
		$args = func_get_args();
		self::__Debug("debug/dump.html", $args);
	}

	public static function DebugRemote()
	{
		$args = func_get_args();
		if (!\QWebRequest::IsRemoteRequest())
			return;
		self::__Debug("debug/remote_calls_debug.html", $args);
	}

	public static function DebugAsync()
	{
		$args = func_get_args();
		if (!\QWebRequest::IsAsyncRequest())
			return;
		self::__Debug("debug/async_calls_debug.html", $args);
	}

	private static function __Debug($file, $args)
	{
		/*
		return;
		$fdir = dirname($file);
		if (!is_dir($fdir))
			qmkdir($fdir);

		$f = fopen($file, "a+");
		ob_start();
		
		array_unshift($args, \QWebRequest::$_pid);
		qvardump($args);
		fwrite($f, ob_get_clean());
		fclose($f);
		*/
	}
	
	public static function CloneData_Rec($data, \SplObjectStorage $_bag)
	{
		$ty = gettype($data);
		switch ($ty)
		{
			case "NULL":
			case "string":
			case "integer":
			case "double":
			case "boolean":
			{
				return $data;
			}
			case "array":
			{
				$arr = [];
				foreach ($data as $k => $v)
					$arr[$k] = static::CloneData_Rec($v, $_bag);
				return $arr;
			}
			case "object":
			{
				if ($_bag->contains($data))
					return $_bag[$data];
				/*
				ToArray($data, $selector = null, $include_nonmodel_properties = false, $with_type = true, $with_hidden_ids = true, $ignore_nulls = true, &$refs = null, &$refs_no_class = null)
	{
		if ($refs === null)
			$refs = [];
		
		if ($data instanceof QIModel)
			return $data->toArray($selector, $include_nonmodel_properties = false, $with_type = true, $with_hidden_ids = true, $ignore_nulls = true, $refs, $refs_no_class);
				 */
				/*if ($data instanceof \QIModel)
					return $data->getClone(null, $_bag);
				else
				{*/
				
				$cc = get_class($data);
				$clone = new $cc;
				$_bag[$data] = $clone;

				if ($data instanceof \QIModelArray)
				{
					foreach ($data as $k => $v)
						$clone[$k] = ($v === null) ? null : static::CloneData_Rec($v, $_bag);
				}
				else if ($data instanceof \QIModel)
				{
					$type_inf = \QModelQuery::GetTypesCache($cc);
					foreach ($type_inf as $k => $v)
					{
						if ($k{0} === '#')
							continue;
						$val = $data->$k;
						if ($val === null)
							continue;
						$clone->$k = static::CloneData_Rec($data->$k, $_bag);
					}
					return $clone;
				}
				else
				{
					foreach ($data as $k => $v)
						$clone->$k = ($v === null) ? null : static::CloneData_Rec($v, $_bag);
					return $clone;
				}
				// }
			}
			default:
				return $data;
		}
	}
	
	public static function CloneDataForFakeRemote(array $args = null)
	{
		$_bag = new \SplObjectStorage();
		if (!$args)
			return $args;
		$ret = [];
		foreach ($args as $arg)
			$ret[] = ($arg === null) ? null : static::CloneData_Rec($arg, $_bag);
		return $ret;
		/*
		$args_data = self::ToArray($arguments, null, $include_nonmodel_properties);
		if (!$args_data)
			$args_data = [];
		// go through args and set them up to empty string
		foreach ($args_data as $key => $value)
			$args_data[$key] = empty($value) ? 0 : $value;
		 */
	}
	
	public static function ImportCsvData(string $file, array $csv_config, array $import_config)
	{
		$data = static::ImportCsvData_Parse($file, $csv_config, $import_config);
		return static::ImportData($data, $import_config);
	}
	
	public static function ImportCsvData_Parse(string $file, array $config, array $import_config)
	{
		if (!is_file($file))
			throw new \Exception("`{$file}` is not a file!");

		try
		{
			$data = \QApp::NewData();
			echo "About to import from: ".$file." [".(is_file($file) ? 'ok' : 'not-found')."] <br/>\n";
			
			if (is_array($config["headings"]))
			{
				$config["headings"] = array_values($config["headings"]);
				foreach ($config["headings"] as $k => $v)
				{
					if (is_string($v))
						$config["headings"][$k] = trim($v);
				}
			}
			
			$refsfds = false;
			if (($config["cols"] === null) && is_array($config["headings"]))
			{
				# we default the cols thefinition to the headings definition !
				$config["cols"] = array_combine($config["headings"], $config["headings"]);
			
				$refsfds = true;
			}
			
			if (!is_array($config["cols"]))
				throw new \Exception('No definition for cols!');
			
			foreach ($config["cols"] as $cc_k => $cc_v)
			{
				if (is_string($cc_v))
					$config["cols"][$cc_k] = explode(".", $cc_v);
			}

			# IF ($config["headings"] === false) then we do not expect headings
			$headings_found = ($config["headings"] === false) ? true : false;
			$row_property = $import_config["Destination"][""];
			if (!$row_property)
				throw new \Exception("Missing root destination");
			
			$prop_definition = \QModel::GetTypeByName(get_class($data))->properties[$row_property];
			if (!$prop_definition)
				throw new \Exception('Unable to find property on APP: '.$row_property);
			if (!$prop_definition->hasCollectionType())
				throw new \Exception('The property is not a collection: '.$row_property);
			
			$row_data_type = $prop_definition->getCollectionType()->getAllInstantiableReferenceTypes();
			if (!$row_data_type)
				throw new \Exception('No instantiable data type for property: '.$row_property);
			if (count($row_data_type) > 1)
				throw new \Exception('Too many instantiable data types for property: '.$row_property);
			
			$data->{$row_property} = new \QModelArray();
			$row_data_type = reset($row_data_type);
			$reflection_cache = [];
			
			$f = fopen($file, "rt");
			while (($row = fgetcsv($f)))
			{
				$empty = true;
				foreach ($row as &$v)
				{
					$v = trim($v);
					if (strlen($v) > 0)
						$empty = false;
				}
				if ($empty)
					continue;
				
				if ($headings_found)
				{
					# import data
					$c_pos = 0;
					$row_data = new $row_data_type();
					foreach ($config["cols"] as $col_k => $col_v)
					{
						if (empty($col_k))
							continue;
						
						static::ImportCsvData_Parse_set($row_data, $row[$c_pos], $import_config, $col_k, $col_v, $reflection_cache, $c_pos);
						# $row_data->$col_v = $row[$c_pos];
						$c_pos++;
					}
					
					$data->{$row_property}[] = $row_data;
				}
				else
				{
					# try to find the headings
					if ($config["headings"])
					{
						if ($config["headings"] === $row)
						{
							$headings_found = true;
						}
						else
						{
							qvar_dumpk($row, $config["headings"]);
							die;
						}
					}
					else
					{
						throw new \Exception('Invalid/Unknown method of idenfying the headings');
					}
				}
			}

		}
		finally
		{
			if ($f)
				fclose($f);
		}
		
		echo "<hr/>";

		return $data;
	}
	
	public static function ImportData(\QModel $data, array $import_config)
	{
		$with_owner = false;
		$owner = null;
		if (isset($import_config["owner"]) && $import_config["owner"])
		{
			if ($import_config["owner"] === true)
				$import_config["owner"] = \Omi\App::GetHolder()->Id;
			# we need to set the owner to all the data
			$owner = new \Omi\Comm\Reseller();
			$owner->setId($import_config["owner"]);
			$bag = new \SplObjectStorage();
			static::ImportData_Set_Owner($data, $owner, $bag);
			unset($bag);
			
			$with_owner = true;
		}
		
		if ($import_config["mergeby"])
		{
			if (!is_array($import_config["mergeby"]))
				throw new \Exception('Only merge by as an array is implemented !');
			$walk_selector = [];
			foreach ($import_config["mergeby"] ?: [] as $k => $v)
			{
				$walk_selector = qJoinSelectors($walk_selector, qParseEntity ($k));
				if (is_string($v) || is_numeric($k))
					$import_config["mergeby"][$k] = [$k => $v];
			}
			
			$merge_by_list = [];
			static::ImportData_Merge_By($data, $import_config["mergeby"], $walk_selector, $merge_by_list);
			
			foreach ($merge_by_list as $app_property => $merge_by_data)
			{
				$query = null;
				$binds = [];
				
				if (count($merge_by_data["cols"]) === 1)
				{
					$query = "Id,".reset($merge_by_data["cols"])." WHERE ".reset($merge_by_data["cols"])." IN (?)";
					$binds[] = array_keys($merge_by_data["list"]);
					if ($with_owner)
					{
						$query .= " AND Owner.Id=?";
						$binds[] = $owner->Id;
					}
				}
				else
				{
					$query = "Id,".implode(", ", $merge_by_data["cols"])." WHERE (".implode(", ", $merge_by_data["cols"]).") IN (?)";
					$binds[] = array_values($merge_by_data["list"]);
					if ($with_owner)
					{
						$query .= " AND Owner.Id=?";
						$binds[] = $owner->Id;
					}
					
					throw new \Exception('This case needs to be tested !!!');
				}
				
				$list_of = \QQuery($app_property.".{{$query}}", $binds)->$app_property;
				
				$cols_def_mb = $merge_by_data["cols"];
				$objs_list_mb = $merge_by_data["objs"];
				
				foreach ($list_of ?: [] as $db_item)
				{
					$data_hash = "";
					foreach ($cols_def_mb as $mby_col)
					{
						$data_hash .= ($data_hash ? "\n" : "").$db_item->$mby_col;
					}
					foreach ($objs_list_mb[$data_hash] ?: [] as $matched_obj)
						$matched_obj->setId($db_item->Id);
				}
				
				unset($list_of);
			}
			
			unset($merge_by_list);
			
			qvar_dumpk($data);
			
			throw new \Exception("We should also try to link `subparts` rather than create new ones, should be ruled defined !!!! ");
		}
		
		qvar_dumpk($import_config, $data);
		die;
	}
	
	public static function ImportData_Merge_By(\QIModel $data, array $mergeby_def, array $walk_selector, array &$merge_by_list, string $path = "")
	{
		if ($data instanceof \QModelArray)
		{
			foreach ($data as $value)
				static::ImportData_Merge_By($value, $mergeby_def, $walk_selector, $merge_by_list, $path);
		}
		else if ($data instanceof \QIModel)
		{
			$mby_rule = $mergeby_def[$path];
			if ($mby_rule)
			{
				foreach ($mby_rule as $app_prop => $merge_by_prop_def)
				{
					if ((!is_string($merge_by_prop_def)) || (strpos($merge_by_prop_def, ".") !== false))
						throw new \Exception('We need to implement more merge by scenarios!');
					$mb_data_cols = [$merge_by_prop_def];
					$mb_data_key = [$data->$merge_by_prop_def];
					$mb_data_hash = $data->$merge_by_prop_def;
					
					$merge_by_list[$app_prop]["list"][$mb_data_hash] = $mb_data_key;
					$merge_by_list[$app_prop]["cols"] = $mb_data_cols;
					$merge_by_list[$app_prop]["objs"][$mb_data_hash][] = $data;
				}
			}
			
			foreach ($walk_selector as $property => $sub_walk_selector)
			{
				$v = $data->$property;
				if ($v instanceof \QIModel)
					static::ImportData_Merge_By($v, $mergeby_def, $sub_walk_selector, $merge_by_list, $path ? $path.".".$property : $property);
			}
		}
	}
	
	public static function ImportCsvData_Parse_set(\QIModel $row_data, string $csv_value, array $import_config, string $config_col_key, $config_col_selector, array &$reflection_cache, int $c_pos)
	{
		if (is_array($config_col_selector))
		{
			$data = $row_data;
			$len = count($config_col_selector);
			for ($i = 0; $i < $len; $i++)
			{
				$current_property = $config_col_selector[$i];
				if ($i === ($len - 1))
					$data->$current_property = $csv_value;
				else
				{
					if (!isset($data->$current_property))
					{
						$prop_data_type = $reflection_cache[get_class($data).'->'.$current_property];
						
						if (!$prop_data_type)
						{
							$prop_refelection = $data->getModelType()->properties[$current_property];
							if (!$prop_refelection)
								throw new \Exception('Property '.get_class($data).' -> '.$current_property.' | is not defined');
							if (!$prop_refelection->hasReferenceType())
								throw new \Exception('Only references are supported for the CSV import atm: '.get_class($data).' -> '.$current_property);
							# we need to move forward
							$prop_data_type = $prop_refelection->getAllInstantiableReferenceTypes();
							if (!$prop_data_type)
								throw new \Exception('No instantiable data type for property: '.get_class($data).' -> '.$current_property);
							if (count($prop_data_type) > 1)
								throw new \Exception('Too many instantiable data types for property: '.get_class($data).' -> '.$current_property);
							$prop_data_type = reset($prop_data_type);
							$reflection_cache[get_class($data).'->'.$current_property] = $prop_data_type;
						}
						
						$data->$current_property = new $prop_data_type;
					}
						
					$data = $data->$current_property;
				}
			}
		}
		else
		{
			throw new \Exception('Not implemented atm');
		}
	}
	
	public static function ImportData_Set_Owner(\QIModel $data, \Omi\Comm\Reseller $owner, \SplObjectStorage $bag)
	{
		if (isset($bag[$data]))
			return;
		$bag[$data] = true;
		
		if ($data instanceof \QModelArray)
		{
			foreach ($data as $value)
			{
				if ($value instanceof \QIModel)
					static::ImportData_Set_Owner($value, $owner, $bag);
			}
		}
		else if ($data instanceof \QIModel)
		{
			if ($data->_synchronizable === true)
				$data->setOwner($owner);
			foreach ($data->getModelType()->properties ?: [] as $m_property => $prop_reflection)
			{
				$value = $data->$m_property;
				if ($value instanceof \QIModel)
					static::ImportData_Set_Owner($value, $owner, $bag);
			}
		}
	}

}
