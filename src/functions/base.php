<?php
/**
 * Here we store some functions that are widely used
 * @todo Review functions that should be moved into methods
 */

/**
 * IMPORTANT CONSTANTS
 */

const S_View = 1;
const S_Add = 2;
const S_Edit = 4;
const S_Delete = 8;

const S_Anon = 1;
const S_Auth = 2;
const S_Any = 3;

$_q_base_num_format = null;

/**
 * @todo To review the location of these constants
 */
define("QPermsFlagNoRights",	0); // 
define("QPermsFlagInherit",		16); // for method & property - use from parent

define("QPermsFlagCreate",		1); // add an entry on a reference or within a collection
define("QPermsFlagDelete",		2); // remove an entry from a reference or collection

define("QPermsFlagAppend",		4); // you are allowed to append data to strings
define("QPermsFlagUpdate",		(8 | QPermsFlagAppend)); // you are allowed to change a field
define("QPermsFlagFix",			(32 | QPermsFlagUpdate)); // you may change a field and mark it as fixed

define("QPermsFlagMerge",		(QPermsFlagCreate | QPermsFlagUpdate));

define("QPermsFlagRead",		64);
define("QPermsFlagExecute",		128);

define("QPermsFlagAll",			(QPermsFlagCreate | QPermsFlagDelete | QPermsFlagAppend | QPermsFlagUpdate | QPermsFlagFix | QPermsFlagMerge | QPermsFlagRead | QPermsFlagExecute | QPermsFlagInherit));

// aliases
define("QPermsFlagSet",			QPermsFlagCreate);
define("QPermsFlagUnset",		QPermsFlagDelete);

define("QMemory_LIMIT_GB",		1);

define("Q_Accepted_Uploads", [
	"image/bmp" => "bmp",
	"image/gif" => "gif",
	"image/ief" => "ief",
	"image/jpeg" => "jpeg",
	"image/jpg" => "jpg",
	"image/jpe" => "jpe",
	"image/png" => "png",
	"image/sgi" => "sgi",
	"image/svg+xml" => "svg",
	"image/svg" => "svg",
	"image/tiff" => "tiff",
	"image/vnd.adobe.photoshop" => "psd",
	"image/vnd.dwg" => "dwg",
	"image/vnd.wap.wbmp" => "wbmp",
	"image/vnd.xiff" => "xif",
	"image/webp" => "webp",
	"image/wmf" => "webp",
	"application/pdf" => "pdf",
	"text/csv" => "csv",
	"text/plain" => [
		"csv" => "csv",
		"cer" => "cer", 
		"key" => "key"
	]
]);

$__dump_data = "";

/**
		- - no rights
		i - inherit
		c - create
		d - delete
		a - append
		u - update
		f - fix
		m - merge (create + update)
		r - read
		x - execute

		* - all rights
 */


/**
* Executes a query
* 
* @param string $query
* @param mixed[] $binds
* @param QIModel[] $dataBlock 
* 
* @return QIModel
* @throws Exception
*/
function QQuery($query, $binds = null, QIModel $from = null, &$dataBlock = null, $skip_security = true, $filter_selector = null, \QIStorage $storage = null)
{
	return QModelQuery::BindQuery($query, $binds, $from, $dataBlock, $skip_security, $filter_selector, false, $storage);
}

function QQuery_By_Id(string $collection, int $id, string $selector = "Id", QIModel $from = null, &$dataBlock = null, $skip_security = true, $filter_selector = null, \QIStorage $storage = null)
{
	$query = $collection.".{{$selector} WHERE Id=? LIMIT 1}";
	$ret = \QQuery($query, [$id] ?: null, $from, $dataBlock, $skip_security, $filter_selector, $storage);
	return isset($ret->{$collection}[0]) ? $ret->{$collection}[0] : null;
}

function QQuery_First_By_Filter(string $collection, array $conditions = null, string $selector = "Id", QIModel $from = null, &$dataBlock = null, $skip_security = true, $filter_selector = null, \QIStorage $storage = null)
{
	$conditions_query = [];
	$conditions_binds = [];
	
	foreach ($conditions ?: [] as $prop_name => $value)
	{
		$conditions_query[] = $prop_name."=?";
		$conditions_binds[] = $value;
	}
	
	$query = $collection.".{{$selector} ".($conditions_query ? " WHERE ".implode(" AND ", $conditions_query) : "")." LIMIT 1}";
	$ret = \QQuery($query, $conditions_binds ?: null, $from, $dataBlock, $skip_security, $filter_selector, $storage);
	
	return isset($ret->{$collection}[0]) ? $ret->{$collection}[0] : null;
}

function QQueryProperty($property, $query, $binds = null, QIModel $from = null, &$dataBlock = null, $skip_security = true, $filter_selector = null)
{
	$data = QModelQuery::BindQuery($property.".{{$query}}", $binds, $from, $dataBlock, $skip_security, $filter_selector);
	return $data ? $data->$property: null;
}

function QQueryItem($property, $query, $binds = null, QIModel $from = null, &$dataBlock = null, $skip_security = true, $filter_selector = null)
{
	$data = QModelQuery::BindQuery($property.".{{$query}}", $binds, $from, $dataBlock, $skip_security, $filter_selector);
	return isset($data->$property[0]) ? $data->$property[0] : null;
}

/**
 * 
 * @param string $query
 * @param mixed[] $binds
 * @param QIModel $from
 * @param QIModel[] $dataBlock
 * @return QIModel
 */
function QBindQuery($query, $binds = null, QIModel $from = null, &$dataBlock = null, $skip_security = true, $filter_query = null)
{
	// $query, $binds, $from = null, &$dataBlock = null, $skip_security = true, $filter_selector
	return QModelQuery::BindQuery($query, $binds, $from, $dataBlock, $skip_security, $filter_query);
}

/**
 * This way of querying skips security checks
 * 
 * @todo Deprecated
 * 
 * @param string $query
 * @param mixed[] $binds
 * @param QIModel $from
 * @param QIModel[] $dataBlock
 * @return QIModel
 */
function QRootQuery($query, $binds = null, QIModel $from = null, &$dataBlock = null)
{
	return QModelQuery::BindQuery($query, $binds, $from, $dataBlock, true);
}

/**
 * This way of querying skips security checks
 * 
 * @todo Deprecated
 * 
 * @param string $query
 * @param mixed[] $binds
 * @param QIModel $from
 * @param QIModel[] $dataBlock
 * @return QIModel
 */
function QRootBindQuery($query, $binds = null, QIModel $from = null, &$dataBlock = null)
{
	return QModelQuery::BindQuery($query, $binds, $from, $dataBlock, true);
}

/**
 * Escapes a value for DB
 * 
 * @param string $param
 * @return string
 */
function _mySc($param)
{
	return is_string($param) ? (($s = QApp::GetStorage()) ? $s->escapeString($param) : addslashes($param)) : $param;
}

/**
 * True if the input is either an array or a QIModelArray
 * 
 * @param QIModelArray $var
 * @return boolean
 */
function qis_array($var)
{
	return is_array($var) || ($var instanceof QIModelArray);
}

/**
 * Gets an URL for the specified tags and arguments
 * 
 * @param string $tag
 * @param mixed[] $args
 * 
 * @return string
 */
function qGetUrl($tag, $_arg0 = null, $_arg1 = null, $_arg2 = null, $_arg3 = null, $_arg4 = null, $_arg5 = null, $_arg6 = null, $_arg7 = null, $_arg8 = null, $_arg9 = null, $_arg10 = null, $_arg11 = null, $_arg12 = null, $_arg13 = null, $_arg14 = null, $_arg15 = null)
{
	return qUrl($tag, $_arg0, $_arg1, $_arg2, $_arg3, $_arg4, $_arg5, $_arg6, $_arg7, $_arg8, $_arg9, $_arg10, $_arg11, $_arg12, $_arg13, $_arg14, $_arg15);
}

/**
 * Outputs a PHP array into a file as the PHP code required to setup that array
 * 
 * @param array $array
 * @param string $var_name
 * @param string $file_path
 * 
 * @return boolean
 */
function qArrayToCodeFile($array, $var_name, $file_path)
{
	// $f = fopen($file_path, "wt");
	// if (!$f)
	// 	return false;
	
	file_put_contents($file_path, 
		qArrayToCode($array, $var_name, true));
	
	// fclose($f);
}

/**
 * Transforms a PHP array to the PHP code required to setup that array
 * 
 * @param array $array
 * @param string $var_name
 * @param boolean $add_php_tags
 * @param resource $stream
 * @param integer $depth
 * @param integer $force_index
 * @return string
 * @throws Exception
 */
function qArrayToCode($array, $var_name = null, $add_php_tags = true, $stream = null, $depth = 0, $force_index = false, $whitespace = true)
{
	if ($var_name && ($var_name[0] === "\$"))
		$var_name = substr($var_name, 1);
	
	$str = $stream ? null : "";
	if ($add_php_tags)
		$stream ? fwrite($stream, "<?php\n") : ($str .= "<?php\n");
	if ($var_name)
		$stream ? fwrite($stream, "\$".$var_name." = ") : ($str .= "\$".$var_name." = ");
	
	if ($array === null)
	{
		$stream ? fwrite($stream, "null") : ($str .= "null");
		if ($var_name)
			$stream ? fwrite($stream, $whitespace ? ";\n" : ";") : ($str .= $whitespace ? ";\n" : ";");
	}
	else if (is_array($array))
	{
		$empty = empty($array);
		
		$pad = $whitespace ? str_pad("", $depth + 1, "\t") : "";
		$stream ? fwrite($stream, "array(") : ($str .= "array(");
		if ((!$empty) && $whitespace)
			$stream ? fwrite($stream, "\n") : ($str .= "\n");

		$p = 0;
		foreach ($array as $k => $v)
		{
			if ($pad)
				$stream ? fwrite($stream, $pad) : ($str .= $pad);
			if ($force_index || ($k !== $p))
				$stream ? fwrite($stream, is_string($k) ? ("\"".qaddslashes($k)."\" => ") : $k." => ") : ($str .= is_string($k) ? ("\"".qaddslashes($k)."\" => ") : $k." => ");
			
			if (is_string($v))
				$stream ? fwrite($stream, ("\"".qaddslashes($v)."\"")) : ($str .= ("\"".qaddslashes($v)."\""));
			else if (is_int($v) || is_float($v))
				$stream ? fwrite($stream, $v) : ($str .= $v);
			else if (is_bool($v))
				$stream ? fwrite($stream, ($v ? "true" : "false")) : ($str .= ($v ? "true" : "false"));
			else if (is_null($v))
				$stream ? fwrite($stream, "null") : ($str .= "null");
			else if (is_array($v))
				$stream ? qArrayToCode($v, null, false, $stream, $depth + 1) : ($str .= qArrayToCode($v, null, false, null, $depth + 1, $force_index, $whitespace));
			else
				throw new Exception("ONLY Scalar types accepted");
			
			$stream ? fwrite($stream, $whitespace ? ",\n" : ",") : ($str .= $whitespace ? ",\n" : ",");
			if (is_int($k))
				$p = $k + 1;
		}
		
		if (!$empty)
			$stream ? fwrite($stream, $pad) : ($str .= $pad);
		$stream ? fwrite($stream, ")") : ($str .= ")");
		if ($depth === 0)
			$stream ? fwrite($stream, ";") : ($str .= ";");
	}
	
	if ($add_php_tags)
		$stream ? fwrite($stream, "\n?>") : ($str .= "\n?>");
	
	return $str;
}

/**
 * Fixes addslashes to add slash before the dolar sign ($)
 * 
 * @param string $val
 * @return string
 */
function qaddslashes($val)
{
	/** http://ro1.php.net/manual/en/language.types.string.php#language.types.string.syntax.double
		\n 	linefeed (LF or 0x0A (10) in ASCII)
		\r 	carriage return (CR or 0x0D (13) in ASCII)
		\t 	horizontal tab (HT or 0x09 (9) in ASCII)
		\v 	vertical tab (VT or 0x0B (11) in ASCII) (since PHP 5.2.5)
		\e 	escape (ESC or 0x1B (27) in ASCII) (since PHP 5.4.0)
		\f 	form feed (FF or 0x0C (12) in ASCII) (since PHP 5.2.5)
		\\ 	backslash
		\$ 	dollar sign
		\" 	double-quote
	 */
	
	return addcslashes($val, "\$\"\\\x00\r\n\t"); // str_replace("\$", "\\\$", addslashes($val));
}

/**
 * Parses a string into a associative array that would describe an entity
 * ex: Orders.*,Orders.Items.{Date,Product,Quantity},Orders.DeliveryAddresses.*
 * The {} can be used to nest properties relative to the parent
 * 
 * @param string $str
 * @param boolean $mark
 * 
 * @return array
 */
function qParseEntity(string $str, $mark = false, $expand_stars = false, $start_class = null, bool $for_listing = false)
{
	if ($for_listing)
		# only split on dot (`.`) if followed by `{` - whitespace accepted
		$tokens = preg_split("/(\s+|\,|\.(?=\\s*\\{)|\:|\{|\})/us", $str, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
	else
		$tokens = preg_split("/(\s+|\,|\.|\:|\{|\})/us", $str, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
	
	$entity = array();

	$ctx_ent = &$entity;
	$ctx_prev = null;
	$ctx_sel = &$entity;
	$selected = null;

	// . => go deeper
	// , => reset to last `level` in this context
	// { => start a new context
	// } => exit current context
	$has_star = false;

	foreach ($tokens as $tok)
	{
		$frts = $tok[0];
		switch ($frts)
		{
			case " ":
			case "\t":
			case "\n":
			case "\r":
			case "\v":
				break;
			case ".":
			{
				// there is nothing to do tbh
				break;
			}
			case ",":
			{
				$ctx_sel = &$ctx_ent;
				if ($selected !== null)
				{
					$selected[] = true;
					// make sure you unset and not assign to null as it is a reference
					unset($selected);
				}
				break;
			}
			case "{":
			{
				// creates a new context
				$ctx_prev = array(&$ctx_ent, $ctx_prev);
				$ctx_ent = &$ctx_sel;
				break;
			}
			case "}":
			{
				// closes the current context
				$ctx_ent = &$ctx_prev[0];
				$ctx_prev = &$ctx_prev[1];
				if ($selected !== null)
				{
					$selected[] = true;
					// make sure you unset and not assign to null as it is a reference
					unset($selected);
				}
				break;
			}
			default:
			{
				// identifier
				if ($expand_stars && (!$has_star) && (($tok === '*') || ($frts === '@')))
					$has_star = true;
				($ctx_sel[$tok] !== null) ? null : ($ctx_sel[$tok] = array());
				$ctx_sel = &$ctx_sel[$tok];
				$mark ? ($selected = &$ctx_sel) : null;
				break;
			}
		}
	}
	
	if ($selected !== null)
	{
		$selected[] = true;
		// make sure you unset and not assign to null as it is a reference
		unset($selected);
	}
	
	if ($expand_stars && $start_class && (($entity["*"] !== null) || $has_star))
		qExpandStars($entity, $start_class);
	
	return $entity;
}

function qParseEntity_for_listing(string $str, $mark = false, $expand_stars = false, $start_class = null)
{
	return qParseEntity($str, $mark, $expand_stars, $start_class, true);
}

function qExpandStars(&$entity, $class, $property = null)
{
	$add = [];
	if (is_array($class) && $class)
	{
		// multiple
		$types = [];
		foreach ($class as $sc)
			$types[$sc] = QModel::GetTypeByName($sc);
	}
	else if ($class)
	{
		// single
		$types = QModel::GetTypeByName($class);
	}
		
	$unset = null;
	foreach ($entity as $k => &$sub)
	{
		if ($k === "*")
		{
			if (is_array($types))
			{
				foreach ($types as $ty)
				{
					foreach ($ty->properties as $k => $v)
						$add[$k] = [];
				}
			}
			else
			{
				foreach ($types->properties as $k => $v)
					$add[$k] = [];
			}
		}
		else if ($k[0] === "@")
		{
			$unset[$k] = 1;
			
			$me_on = [];
			if ($k === "@M")
			{
				if (is_array($class))
				{
					foreach ($class as $c)
						$me_on[] = $property ? $c::GetPropertyModelEntity($property) : $c::GetModelEntity();
				}
				else
					$me_on[] = $property ? $class::GetPropertyModelEntity($property) : $class::GetModelEntity();
			}
			else if ($k === "@ML")
			{
				if (is_array($class))
				{
					foreach ($class as $c)
						$me_on[] = $property ? $c::GetPropertyListingEntity($property) : $c::GetListingEntity();
				}
				else
					$me_on[] = $property ? $class::GetPropertyListingEntity($property) : $class::GetListingEntity();
			}
			else if (substr($k, 0, 3) === '@M_')
			{
				if ($property)
					$meth_name = "GetPropertyEntityFor".substr($op, 3);
				else
					$meth_name = "GetEntityFor".substr($op, 3);
				$me_on = $class::$meth_name($property);
				
				if (is_array($class))
				{
					foreach ($class as $c)
						$me_on[] = $c::$meth_name($property);
				}
				else
					$me_on[] = $class::$meth_name($property);
			}
			if ($me_on)
			{
				foreach ($me_on as $_mon)
				{
					$mon = is_string($_mon) ? qParseEntity($_mon, true) : $_mon;
					foreach ($mon as $mk => $mv)
						$add[$mk] = $mv;
				}
			}
		}
		else if ($sub && ($sub_types = qMixedTypes($types, $k)))
		{
			qExpandStars($sub, $sub_types, $property);
		}
	}
	
	if ($add)
	{
		unset($entity["*"]);
		foreach ($add as $k => $v)
		{
			if ($entity[$k] === null)
				$entity[$k] = $v;
		}
	}
	
	if ($unset)
	{
		foreach ($unset as $k => $v)
			unset($entity[$k]);
	}
	
	return $entity;
}

function qMixedTypes($types, $property)
{
	$ret = [];
	if (is_array($types))
	{
		foreach ($types as $ty)
		{
			$prop = $ty->properties[$property];
			if ($prop)
			{
				if (($ref_types = $prop->getReferenceTypes()))
				{
					if (!$ret)
						$ret = $ref_types;
					else
					{
						foreach ($ref_types as $k => $v)
							$ret[$k] = $v;
					}
				}

				if ((($coll = $prop->getCollectionType())) && ($ref_types = $coll->getReferenceTypes()))
				{
					if (!$ret)
						$ret = $ref_types;
					else
					{
						foreach ($ref_types as $k => $v)
							$ret[$k] = $v;
					}
				}
			}
		}
	}
	else
	{
		$prop = $types->properties[$property];
		if ($prop)
		{
			if (($ref_types = $prop->getReferenceTypes()))
			{
				if (!$ret)
					$ret = $ref_types;
				else
				{
					foreach ($ref_types as $k => $v)
						$ret[$k] = $v;
				}
			}

			if ((($coll = $prop->getCollectionType())) && ($ref_types = $coll->getReferenceTypes()))
			{
				if (!$ret)
					$ret = $ref_types;
				else
				{
					foreach ($ref_types as $k => $v)
						$ret[$k] = $v;
				}
			}
		}	
	}
	return $ret ?: null;
}

function qGetEntityFromData($obj, &$entity = [])
{
	if (!$entity)
		$entity = [];

	$props = $obj->getModelType()->properties;
	
	foreach ($props as $key => $prop)
	{
		$value = $obj->{$key};

		if (!$value)
			continue;
		
		$entity[$key] = [];
		if ($value instanceof \QModel)
			qGetEntityFromData($value, $entity[$key]);
		else if ($value instanceof \QModelArray)
		{
			foreach ($value as $itm)
			{
				if ($itm instanceof \QModel)
					qGetEntityFromData($itm, $entity[$key]);
			}
		}
	}
	return $entity;
}

function qImplodeEntity($entity)
{
	$str = "";
	$pos = 0;
	foreach ($entity as $k => $ent)
	{
		if ($pos)
			$str .= ",";
		$str .= $k;
		if ($ent)
		{
			$str .= ".{";
			$str .= qImplodeEntity($ent);
			$str .= "}";
		}
		$pos = 1;
	}
	return $str;
}

function qImplodeEntityFormated($entity, $depth = 0)
{
	$str = "";
	$pos = 0;
	foreach ($entity as $k => $ent)
	{
		if ($pos)
			$str .= ",\n";
		$str .= str_pad("", $depth, "\t").$k;
		if ($ent)
		{
			$str .= ".{\n";
			$str .= qImplodeEntityFormated($ent, $depth + 1);
			$str .= "}";
		}
		$pos = 1;
	}
	return $str;
}

function qSelector_Remove_Ids(array $selector = null)
{
	if (($selector === null) || empty($selector))
		return $selector;
	else if (isset($selector['Id']))
		unset($selector['Id']);
	foreach ($selector as $k => $v)
	{
		if (($v === null) || empty($v))
		{
			# continue
		}
		else if (is_array($v))
			$selector[$k] = qSelector_Remove_Ids($v);
	}
	
	return $selector;
}

/**
 * @todo Review if we need it after we implement the new security
 * 
 * @param QIModel $var
 * @return \QIModel
 */
function qToSql($var)
{
	if ($var instanceof QIModel)
		return "\${".$var->getId().",".$var->getModelType()->getIntId()."}";
	else if (is_array($var))
	{
		$ret = array();
		foreach ($var as $v)
			$ret[] = qToSql($v);
		return implode(",", $ret);
	}
	else if (is_bool($var))
		return $var ? "TRUE" : "FALSE";
	else
		return $var;
}

/**
 * The undefined instance
 */
final class _QUndefined_
{
	private static $V;
	private function __construct(){}
	public static function Get()
	{
		return self::$V ?: (self::$V = new _QUndefined_());
	}
	public function __toString()
	{
		return "#undefined";
	}
}

$_QUndefined = _QUndefined_::Get();

function QUndefined()
{
	global $_QUndefined;
	return $_QUndefined;
}

function Q_SESSION_SET_ID($session_id)
{
	$doSessionDebug = false;
	if ($doSessionDebug)
	{
		$t1 = microtime(true);
		ob_start();
		$objDateTime = new \DateTime('NOW');
		$reqDate = $objDateTime->format("Y-m-d H:i:s.v");
		echo "Q_SESSION_SET_ID START: {$session_id} : {$reqDate} : {$_SERVER["REMOTE_ADDR"]}<br/>";
	}
	try
	{
		# @TODO : open session
		if (session_status() !== PHP_SESSION_ACTIVE)
		{
			if ($doSessionDebug)
				echo "Q_SESSION_SET_ID: OPEN NEW SESSION<br/>";
			session_start();
		}
		$current_session_id = session_id();
		if ($current_session_id != $session_id)
		{
			if ($doSessionDebug)
				echo "Q_SESSION_SET_ID CHANGE SESSION ID: {$current_session_id} : {$session_id}<br/>";
			session_id($session_id);
		}
	}
	finally
	{
		# @TODO : close session
		session_write_close();
	}
	if ($doSessionDebug)
	{
		echo "Q_SESSION_SET_ID END: " . $session_id . "<br/>TOOK: " . (microtime(true) - $t1) . " seconds<br/>-----------------------------------------------------<br/>";
		$str = ob_get_clean();
		file_put_contents("Q_SESSION_SET_ID_calls.html", $str, FILE_APPEND);
	}
	return $session_id;
}

function Q_SESSION_GET_ID()
{
	$doSessionDebug = false;
	if ($doSessionDebug)
	{
		$t1 = microtime(true);
		ob_start();
		$objDateTime = new \DateTime('NOW');
		$reqDate = $objDateTime->format("Y-m-d H:i:s.v");
		$t1 = microtime(true);
		echo "Q_SESSION_GET_ID START : {$reqDate} : {$_SERVER["REMOTE_ADDR"]}<br/>";
	}
	$session_id = null;
	try
	{
		# @TODO : open session
		if (session_status() !== PHP_SESSION_ACTIVE)
		{
			if ($doSessionDebug)
				echo "Q_SESSION_GET_ID: OPEN NEW SESSION<br/>";
			session_start();
		}
		$session_id = session_id();
	}
	finally
	{
		# @TODO : close session
		session_write_close();
	}
	if ($doSessionDebug)
	{
		echo "Q_SESSION_GET_ID END: " . $session_id . "<br/>TOOK: " . (microtime(true) - $t1) . " seconds<br/>-----------------------------------------------------<br/>";
		$str = ob_get_clean();
		file_put_contents("Q_SESSION_GET_ID_calls.html", $str, FILE_APPEND);
	}
	return $session_id;
}

function Q_SESSION($key, $value = null, bool $unset = false)
{
	$doSessionDebug = false;
	$cleaned = false;
	if ($doSessionDebug)
	{
		$t1 = microtime(true);
		ob_start();
		$objDateTime = new \DateTime('NOW');
		$reqDate = $objDateTime->format("Y-m-d H:i:s.v");
		echo "---------------------------------------------------------<br/>Q_SESSION START: {$reqDate} : {$_SERVER["REMOTE_ADDR"]}<br/>";
	}
	try
	{
		# @TODO : open session
		if (session_status() !== PHP_SESSION_ACTIVE)
		{
			#var_dump("OPEN SESSION ON Q_SESSION");
			session_start();
		}

		$set_value = (bool)(func_num_args() > 1);
		if (is_string($key) || is_int($key) || is_scalar($key) || is_null($key))
		{
			if ($doSessionDebug)
			{
				qvardump((microtime(true) - $t1) . " seconds", $key, $value, $unset, $set_value, $_SESSION[$key]);
				$str = ob_get_clean();
				file_put_contents("Q_SESSION_calls.html", $str, FILE_APPEND);
				$cleaned = true;
			}

			if ($unset)
				unset($_SESSION[$key]);
			else
				return $set_value ? ($_SESSION[$key] = $value) : $_SESSION[$key];
		}
		else if (is_array($key) && (!empty($key)))
		{
			$ref = &$_SESSION;
			$cont = (count($key) - 1); # one less than the count
			for ($i = 0; $i < $cont; $i++)
			{
				$i_key = $key[$i];
				if (! (is_string($i_key) || is_int($i_key) || is_scalar($i_key) || is_null($i_key)))
					throw new \Exception("Bad key specified for SESSION assignment [" . gettype($i_key) . "]");
				$ref = &$ref[$i_key];
			}

			$last_key = $key[$cont];

			if ($doSessionDebug)
			{
				qvardump((microtime(true) - $t1) . " seconds", $key, $value, $unset, $set_value, $_SESSION[$last_key]);
				$str = ob_get_clean();
				file_put_contents("Q_SESSION_calls.html", $str, FILE_APPEND);
				$cleaned = true;
			}

			if ($unset)
				unset($_SESSION[$last_key]);
			else
				return $set_value ? ($_SESSION[$last_key] = $value) : $_SESSION[$last_key];
		}
		else
			throw new \Exception("Bad key specified for SESSION assignment [" . gettype($key) . "]");
	}
	finally
	{
		# @TODO : close session
		session_write_close();
	}
	
	if ($doSessionDebug && (!$cleaned))
	{
		qvardump((microtime(true) - $t1) . " seconds - no output collected!");
		$str = ob_get_clean();
		file_put_contents("Q_SESSION_calls.html", $str, FILE_APPEND);
	}
}

function Q_SESSION_UNSET($key)
{
	return Q_SESSION($key, null, true);
}

/**
 * Handles a frame specific request
 * 
 * @todo Move into a specific request handler class
 * 
 * @param string $filter
 * @param QIModel $instance
 * @return mixed[]
 * @throws Exception
 */
function execQB($filter = null, $instance = null)
{
	$pos = 0;
	$ret = array();
	$request = null;
	
	while (($request = $_GET["_qb{$pos}"]) || ($request = $_POST["_qb{$pos}"]))
	{
		$request_key = "_qb{$pos}";
		
		$meta_str = $request["_q_"];
		if (empty($meta_str))
		{
			$pos++;
			continue;
		}
		
		if (Q_IS_TFUSE)
		{
			foreach ($request ?: [] as $rk => $rv)
			{
				if (is_scalar($rv))
					$request[$rk] = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $rv);
			}
		}

		list ($class, $method, $id) = explode(".", $meta_str, 3);
		if (($class !== "QApi") && (!qIsA($class, "QIModel")) && (!qIsA($class, "QViewBase")))
			throw new Exception("You may only call a QIModel or a QViewBase. You have called a `{$class}`");
	
		if ((($filter === null) || ($method === $filter)) && 
				($instance ? ((get_class($instance) === $class) && (($id === null) || ($id == (($instance instanceof QViewBase) ? $instance->getFullId() : $instance->getId())))) : true))
		{
			$m_type = QModel::GetTypeByName($class);
			if (!$m_type)
				throw new Exception("Type does not exists {$class}");
			else if (!method_exists($class, $method))
				throw new Exception("Method does not exists {$class}::{$method}");
			
			$m_type_meth = $m_type ? $m_type->methods[$method] : null;
			if ((!$m_type_meth) || (!$m_type->methodHasApiAccess($method)))
			{
				if (\QAutoload::GetDevelopmentMode())
					qvar_dumpk($m_type);
				throw new Exception("You do not have access to {$class}::{$method}");
			}
			
			if ((!$instance) && (!$m_type_meth->static))
			{
				$instance = new $class();
				if (!$instance->_qini)
					$instance->init(true);
			}

			unset($request["_q_"]);
			
			$fake_parent = null;
			
			$refs = [];
			if (isset($_FILES[$request_key]))
			{
				// name, type, tmp_name, error, size
				$files_rq = $_FILES[$request_key];
				$f_name = $files_rq["name"];
				$f_type = $files_rq["type"];
				$f_tmp_name = $files_rq["tmp_name"];
				$f_error = $files_rq["error"];
				$f_size = $files_rq["size"];
				
				$params = extractQbRequest($request, $fake_parent, null, $f_name, $f_type, $f_tmp_name, $f_error, $f_size, $refs);
			}
			else
				$params = extractQbRequest($request, $fake_parent, null, null, null, null, null, null, $refs);
			
			// catch any output in case of a render method
			
			$log_index = uniqid("", true);
			q_remote_log_sub_entry([[
				'Index' => $log_index,
				'Timestamp_ms' => (string)microtime(true),
				'Tags' => ['tag' => 'qb-exec', 'class' => $class, 'method' => $method, 'call' => "{$class}::{$method}", 'args' => $params],
				'Data' => [],
			]]);

			$ex = null;
			$output = null;
			try
			{
				ob_start();

				if ($m_type_meth->static)
					$ret[$pos] = call_user_func_array(array($class, $method), is_array($params) ? $params : [$params]);
				else
					$ret[$pos] = call_user_func_array(array($instance, $method), is_array($params) ? $params : [$params]);
					
			}
			catch (\Exception $ex)
			{
				q_remote_log_sub_entry([[
					'Index' => $log_index,
					'Timestamp_ms_end' => (string)microtime(true),
					'Tags' => ['error'],
					'Traces' => $ex->getTraceAsString(),
					'Is_Error' => true,
					'Data' => ['error' => $ex->getMessage(), 'full_trace' => $ex->getTrace()],
				]], [
					'Is_Error' => true,
				]);
				
				throw $ex;
			}
			finally
			{
				if (!$ex)
				{
					q_remote_log_sub_entry([[
						'Index' => $log_index,
						'Timestamp_ms_end' => (string)microtime(true),
						'Tags' => ['success'],
						#'Data' => ['return'.(($pos > 0) ? "[{$pos}]" : '') => $ret[$pos]],
					]]);
				}
				$output = ob_get_clean();
			}
			
			if (!empty($output))
			{				
				if ($ret[$pos] === null)
					$ret[$pos] = $output;
				# else if (\QAutoload::In_Debug_Mode())
				#	$ret["__hiddenOutput__"][] = $output;
			}
			
			if (\QAutoload::In_Debug_Mode())
			{
				$dbg_data = \QAutoload::Debug_Get_Data();
				if (is_array($dbg_data) && $dbg_data)
				{
					ob_start();
					qvar_dump($dbg_data);
					$ret["__hiddenOutput__"][] = ob_get_clean();
				}
			}

			// now unset it
			unset($_GET["_qb{$pos}"]);
			unset($_POST["_qb{$pos}"]);
		}

		$pos++;
	}
	
	if (QWebRequest::IsAjaxRequest() || QWebRequest::IsFastAjax())
	{
		if (QAutoload::$DebugPanel)
		{
			// we will need to send the debug info also
			ob_start();
			$dbg_panel = new QDebugPanelCtrl();
			// $this->addControl($dbg_panel, "debugPanel");
			$dbg_panel->init();
			$dbg_panel->renderRequestInfo();

			$ret_data = ob_get_clean();
			if ($ret_data)
				$ret["__debugData__"] = $ret_data;
		}
		if (QAutoload::$DebugStacks)
		{
			$ret["__debugStack__"] = QAutoload::$DebugStacks;
		}
		if (\QAutoload::In_Debug_Mode())
		{
			$ar = \QWebRequest::GetAjaxResponse();
			foreach ($ar['__hiddenOutput__'] ?: [] as $output_x)
				$ret["__hiddenOutput__"][] = $output_x;
		}
		
		// we have an AJAX request
		QWebRequest::SetAjaxResponse($ret);
	}
	return $ret;
}

/**
 * Extracts the data from the request.
 * 
 * @todo Move into a specific request handler class
 * 
 * @param type $data
 * @param type $parent
 * @param type $key
 * @param type $f_name
 * @param type $f_type
 * @param type $f_tmp_name
 * @param type $f_error
 * @param type $f_size
 * @return boolean|\QFile
 * @throws Exception
 */
function extractQbRequest($data, &$parent = null, $key = null, $f_name = null, $f_type = null, $f_tmp_name = null, $f_error = null, $f_size = null, &$refs = null)
{
	if (is_array($data))
	{
		$file_path = null;
		$file_params = null;
		
		$class = $data["_ty"];
		if ((($class === "QFile") || qIsA($class, "QFile")) && (!($parent instanceof QFile)))
		{
			//  && ($data["_ftype"] === "_file")) || (($parent instanceof QFile) && ($key === "Path"))
			$is_QFile = ($data["_ftype"] !== "_file");
			
			if ($is_QFile)
			{
				$f_name = $f_name["Path"];
				$f_type = $f_type["Path"];
				$f_tmp_name = $f_tmp_name["Path"];
				$f_error = $f_error["Path"];
				$f_size = $f_size["Path"];
			}
			
			if ($f_error["_dom"])
			{
				if ($f_error["_dom"] === UPLOAD_ERR_NO_FILE)
					return;
				throw new Exception("Upload failed for file {$f_name["_dom"]}.\nError: ".$f_error["_dom"]);
			}
			
			if ($f_name["_dom"])
			{
				// @storage.filePath
				$prop = null;
				if ($parent instanceof QIModelArray)
					$prop = $parent->getModelProperty();
				else if ($parent instanceof QIModel)
					$prop = $parent->getModelType()->properties[$key];

				$params = array("name" => $f_name["_dom"], "type" => $f_type["_dom"], "tmp_name" => $f_tmp_name["_dom"], "error" => $f_error["_dom"], "size" => $f_size["_dom"]);
				$file_params = $params;

				if ($prop && ($filePath = $prop->storage["filePath"]))
				{
					$full_path = realpath($filePath);
					if (!is_dir($full_path))
						throw new Exception("The @storage.filePath {$full_path} specified in ".$prop->parent->class.".".$prop->name." is missing");
					$full_path = rtrim($full_path, "/\\")."/";
					$fn = $params["name"];

				
					$save_path = q_move_uploaded_file($f_tmp_name["_dom"], $full_path, $fn, ($chmod = $prop->storage["fileMode"]), $prop->storage["uniqid_gen"] ?: false);
					$file_path = ($prop->storage["fileWithPath"]) ? $save_path : basename($save_path);

					if (!$is_QFile)
						return $file_path;
				}
				else if (!$is_QFile)
				{
					if ((!Q_IS_TFUSE) && ($parent instanceof QIModel))
						return (($handleU_ret = $parent->handleUpload($key, $params)) !== QUndefined()) ? $handleU_ret : $params;
					else
						return $params;
				}
			}
			// else noting to do , no file was provided
		}
		
		$params = null;
		if ($class && ($class !== "array") && ($class !== "Array"))
		{
			if (class_exists($class))
			{
				$obj_id = $data["_id"] ?: ($data["Id"] ?: ($data["id"] ?: $data["ID"]));
				$params = $obj_id ? ($refs[$obj_id][$class] ?: ($refs[$obj_id][$class] = new $class())) : new $class();
			}
			else
				$params = null;
		}
		else
			$params = [];

		if ($class && ($params === null))
		{
			if (\QAutoload::GetDevelopmentMode())
				qvar_dumpk(func_get_args());
			throw new Exception("Invalid class {$class}");
		}

		// class does not exist
		if ($params === null)
			return null;

		if ($class)
			unset($data["_ty"]);

		if ($params instanceof QIModel)
		{
			if (($d_id = $data["_id"]))
			{
				$id_val = extractQbRequest($d_id);
				if ($id_val !== null)
					$params->setId($id_val);
				unset($data["_id"]);
			}
			if (($d_ts = $data["_ts"]))
			{
				$ts_ = extractQbRequest($d_ts);
				if ($ts_ !== null)
					$params->_ts = (int)$ts_;
				unset($data["_ts"]);
			}
			if (($d_tsp = $data["_tsp"]))
			{
				$params->_tsp = extractQbRequest($d_tsp);
				// var_dump(get_class($params), $params->_tsp);
				unset($data["_tsp"]);
			}
			if (($d_tmpid = $data["_tmpid"]))
			{
				$params->_tmpid = extractQbRequest($d_tmpid);
				unset($data["_tmpid"]);
			}
			if (($d_rowi = $data["_rowi"]))
			{
				$params->_rowi = extractQbRequest($d_rowi);
				unset($data["_rowi"]);
			}
			if (($_singleSync = $data["_singleSync"]))
			{
				$params->_singleSync = extractQbRequest($_singleSync);
				unset($data["_singleSync"]);
			}

			if ($params instanceof QFile)
			{
				if ($data["_ftype"])
					unset($data["_ftype"]);
				if ($data["_file"])
					unset($data["_file"]);
			}
		}

		if (!is_array($params))
		{
			if ($params instanceof QIModelArray)
			{
				$collection_prop = null;
				if (($parent instanceof QIModel) && $key)
				{
					$collection_prop = $parent->getModelType()->properties[$key];
					if ($collection_prop)
						$params->setModelProperty($collection_prop);
				}
				
				if ($collection_prop)
				{
					$set_meth = "set{$collection_prop->name}_Item_";
					$parent->{"set{$collection_prop->name}"}($params);
					foreach ($data as $k => $v)
					{
						$i_val = extractQbRequest($v, $params, $k, $f_name ? $f_name[$k] : null, $f_type ? $f_type[$k] : null, $f_tmp_name ? $f_tmp_name[$k] : null, $f_error ? $f_error[$k] : null, $f_size ? $f_size[$k] : null, $refs);
						$parent->$set_meth($i_val, $k);
					}
				}
				else
				{
					foreach ($data as $k => $v)
						$params[$k] = extractQbRequest($v, $params, $k, $f_name ? $f_name[$k] : null, $f_type ? $f_type[$k] : null, $f_tmp_name ? $f_tmp_name[$k] : null, $f_error ? $f_error[$k] : null, $f_size ? $f_size[$k] : null, $refs);
				}
			}
			else
			{
				$params_props = $params->getModelType()->properties;
				
				foreach ($data as $k => $v)
				{
					if (($refl_prop = $params_props[$k]))
					{
						if (is_array($v))
						{
							$is_collection = $refl_prop->hasCollectionType();
							$all_irt = $is_collection ? $refl_prop->types->getAllInstantiableReferenceTypes() : 
												$refl_prop->getAllInstantiableReferenceTypes();
							$all_irt = ($all_irt && (count($all_irt) === 1)) ? reset($all_irt) : null;
							
							if ($all_irt)
							{
								if ($is_collection)
								{
									foreach ($v as $v_k__ => &$v_v__)
									{
										if (($v_k__[0] !== '_') && is_array($v_v__) && empty($v_v__['_ty']))
											$v_v__['_ty'] = $all_irt;
									}
									# must be qmodel array
									$v['_ty'] = 'QModelArray';
								}
								else
									# must be the specified type
									$v['_ty'] = $all_irt;
							}
						}
						
						$ex_v = extractQbRequest($v, $params, $k, $f_name ? $f_name[$k] : null, $f_type ? $f_type[$k] : null, $f_tmp_name ? $f_tmp_name[$k] : null, $f_error ? $f_error[$k] : null, $f_size ? $f_size[$k] : null, $refs);
						$params->{"set{$k}"}($ex_v);
					}
					else
					{
						$params->$k = extractQbRequest($v, $params, $k, $f_name ? $f_name[$k] : null, $f_type ? $f_type[$k] : null, $f_tmp_name ? $f_tmp_name[$k] : null, $f_error ? $f_error[$k] : null, $f_size ? $f_size[$k] : null, $refs);
						if (is_array($v) && ($k[0] !== '_') && \QAutoload::GetDevelopmentMode())
						{
							qvar_dumpk($params, $k, $v);
							throw new \Exception('Suspecting the type was not detected.');
						}
					}
				}
			}
		}
		else
		{
			$use_data = (($class === "Array") && is_array($tmp_di = $data["_items"])) ? $tmp_di : $data;
			foreach ($use_data as $k => $v)
			{
				$params[$k] = extractQbRequest($v, $params, $k, $f_name ? $f_name[$k] : null, $f_type ? $f_type[$k] : null, $f_tmp_name ? $f_tmp_name[$k] : null, $f_error ? $f_error[$k] : null, $f_size ? $f_size[$k] : null, $refs);
			}
		}
		
		if (($params instanceof QFile) && $file_path)
		{
			$params->Path = $file_path;
			
			$params->_upload = $file_params;
			if (!Q_IS_TFUSE)
				$params->handleUpload($key, $params->_upload);
		}

		return $params;
	}
	else if ($data === null)
	{
		return null;
	}
	else if (is_string($data))
	{
		if ($data[0] === "_")
			return (string)substr($data, 1);
		else if ($data === "true")
			return true;
		else if ($data === "false")
			return false;
		else if ($data === "null")
			return null;
		else if (is_numeric($data))
			return (strpos($data, ".") !== false) ? floatval($data) : intval($data);
		else 
			return $data;
	}
	else
		return $data;
}

/**
 * Checks that the input URL is in the path of the request
 * 
 * @param string $url
 * @return boolean
 */
function qurl_check($url)
{
	return QUrl::$Requested && (substr(QUrl::$Requested->url, 0, strlen($url)) === $url);
}

/**
 * 
 * @param string $property
 * @return QIModel|mixed
 */
function QData($property = null)
{
	return $property ? QApp::Data()->{$property} : QApp::Data();
}

/**
 * 
 * @param string $property
 * @return QIModel|mixed
 */
function QNewData()
{
	return QApp::QNewData();
}

/**
 * Gets a url based on a tag
 * 
 * @param string $tag
 * @param mixed $_arg0
 * @param mixed $_arg1
 * @param mixed $_arg2
 * @param mixed $_arg3
 * @param mixed $_arg4
 * @param mixed $_arg5
 * @param mixed $_arg6
 * @param mixed $_arg7
 * @return string
 */
function qUrl($tag, $_arg0 = null, $_arg1 = null, $_arg2 = null, $_arg3 = null, $_arg4 = null, $_arg5 = null, $_arg6 = null, $_arg7 = null, $_arg8 = null, $_arg9 = null, $_arg10 = null, $_arg11 = null, $_arg12 = null, $_arg13 = null, $_arg14 = null, $_arg15 = null)
{
	$url = null;
	return QApp::$UrlController->getUrlForTag_($tag, $url, $_arg0, $_arg1, $_arg2, $_arg3, $_arg4, $_arg5, $_arg6, $_arg7, $_arg8, $_arg9, $_arg10, $_arg11, $_arg12, $_arg13, $_arg14, $_arg15);
}

/**
 * Gets a standard MVVM bind
 * 
 * @param QIModel $object
 * @param string $rowid Rowid in case of an element within a collection
 * @return string
 */	
function qb($object, $rowid = null)
{
	if ($object instanceof QIModel)
		return "(".$object->getModelType()->class.(($id = $object->getId()) ? "|".$id : "").($rowid ?: "").")";
	return "";
}

/**
 * Debugs some variables
 * 
 * @return string
 */
function qDebug()
{
	ob_start();
	// QModel::DumpIt($self);
	qDebugStackInner(func_get_args(), false, false);
	return ob_get_clean();
}

/**
 * Better var_dump for objects/model
 * 
 * @return string
 */
function qvar_dumpk()
{
	ob_start();
	$ret = "";
	foreach (func_get_args() as $arg)
		$ret .= qDebugStackInner($arg, false, false);
	$ret = ob_get_clean();

	echo $ret;
	return $ret;
}

function qvar_get()
{
	ob_start();
	$ret = "";
	foreach (func_get_args() as $arg)
		$ret .= qDebugStackInner($arg, false, false);
	return ob_get_clean();
}

/**
 * Better var_dump for objects/model
 * 
 * @return string
 */
function qvar_dump()
{
	ob_start();
	$ret = "";
	foreach (func_get_args() as $arg)
		$ret .= qDebugStackInner($arg, false, false);
	$ret = ob_get_clean();
	
	if ((($hxrw = $_SERVER['HTTP_X_REQUESTED_WITH']) && (strtolower($hxrw) === 'xmlhttprequest')) || 
											(filter_input(INPUT_POST, "__qAjax__") || filter_input(INPUT_GET, "__qAjax__")))
		\QWebRequest::AddHiddenOutput($ret);
	else
		echo $ret;
	
	return $ret;
}

function qdumptofile()
{
	$f = fopen("dump.html", "a+");
	ob_start();
	foreach (func_get_args() as $arg)
		qDebugStackInner($arg, false, false);
	fwrite($f, ob_get_clean());
	fclose($f);
}

function qvdumptofile()
{
	$f = fopen("vdump.html", "a+");
	ob_start();
	var_dump(func_get_args());
	echo "<hr/>";
	fwrite($f, ob_get_clean());
	fclose($f);
}

/**
 * Better get_dump for objects/model
 * 
 * @return string
 */
function qget_dump()
{
	ob_start();
	foreach (func_get_args() as $arg)
		qDebugStackInner($arg, false, false);
	return ob_get_clean();
}

/**
 * Better get_dump for objects/model
 * 
 * @return string
 */
function qtrack_dump()
{
	ob_start();
	foreach (func_get_args() as $arg)
		qDebugStackInner($arg, false, false);
	return (\QAutoload::$DebugStacks[] = ob_get_clean());
}

/**
 * Better var_dump for objects/model
 * 
 * @return string
 */
function qVarDump()
{
	return call_user_func("qvar_dump", func_get_args());
}

/**
 * Better var_dump for objects/model
 * 
 * @return string
 */
function qVarDumpK()
{
	return call_user_func("qvar_dumpk", func_get_args());
}

/**
 * Escapes the name of a table
 * 
 * @param string $table_name
 * @return string
 */
function qEscTable($table_name)
{
	if (($dot = strpos($table_name, ".")) !== false)
		return "`".substr($table_name, 0, $dot)."`.`".substr($table_name, $dot + 1)."`";
	else
		return "`".$table_name."`";
}

/**
 * Replaces the \ in class name (if any) with - for filesystem compatibility
 * 
 * @param string $class
 * @return string
 */
function qClassToPath($class)
{
	return str_replace("\\", "-", $class);
}

/**
 * Replaces the \ in class name (if any) with _ for php var name compatibility
 * 
 * @param string $class
 * @return string
 */
function qClassToVar($class)
{
	return str_replace("\\", "_", $class);
}

/**
 * The reverse for qClassToPath
 * 
 * @param string $path_part
 * @return string
 */
function qPathToClass($path_part)
{
	return str_replace("-", "\\", $path_part);
}

/**
 * Gets the name of a class without the namespace
 * @param string $class
 * @return string
 */
function qClassWithoutNs($class)
{
	return (($nsp = strrpos($class, "\\")) === false) ? $class : substr($class, $nsp + 1);
}

/**
 * Extracts class name and namespace from a full class name
 * 
 * @param string $full_class
 * @return string[]
 */
function qClassShortAndNamespace($full_class)
{
	return (($nsp = strrpos($full_class, "\\")) === false) ? [$full_class, null] : [substr($full_class, $nsp + 1), substr($full_class, 0, $nsp)];
}

function qClassRelativeToNamespace($full_class, $namespace = null)
{
	if (!$namespace)
		return $full_class;
	$ns_len = strlen($namespace);
	return ($ns_len && (substr($full_class, 0, $ns_len) === $namespace)) ? substr($full_class, $ns_len + 1) : "\\".$full_class;
}

/**
 * 
 * @param string|object $object_class
 * @param string $class
 * @return boolean
 */
function qIsA($object_class, $class)
{
		if ($object_class === $class)
			return true;
		else if (is_object($object_class))
			return $object_class instanceof $class;
		else if (is_string($object_class) && (class_exists($object_class) || interface_exists($object_class)))
		{
			if (interface_exists($class))
			{
				$ci = class_implements($object_class);// ($extby = QAutoload::GetClassExtendedBy($class)) && $extby[$object_class];
				return ($ci && $ci[$class]);
			}
			else 
				return is_subclass_of($object_class, $class);
		}
		else
			return false;
}

/**
 * Encodes data for a request
 * 
 * @param mixed $data
 * @param string $class_name
 * @param string $method
 * @param string $fullId
 * @param integer $index
 * @return mixed
 */
function qbEncodeRequest($data, $class_name, $method, $fullId = null, $index = 0)
{
	return qbEncodeElement(array("_qb{$index}" => array("_q_" => $class_name.".".$method.($fullId ? ".".$fullId : ""), 0 => $data)));
}

/**
 * Encodes one element for a request
 * 
 * @param mixed $v
 * @param string|integer $indx
 * @param string $key
 * @param array $post
 * @return string
 */
function qbEncodeElement($v, $indx = null, $key = null, &$post = null)
{
	$ty = gettype($v);
	if ($post)
	{
		switch($ty)
		{
			case "NULL":
				return "null";
			case "string":
				return $post[$indx] = ((($key === "_ty") || ($key === "_q_")) ? "" : "_") . urlencode($v);
			case "integer":
			case "double":
				return $post[$indx] = urlencode($v);
			case "boolean":
				return $post[$indx] = ($v ? "true" :  "false");
			case "array":
			{
				$ret = "";
				foreach ($v as $k => $v_itm)
				{
					$k_index = $indx ? $indx . "[" . rawurlencode($k) . "]" : rawurlencode($k);
					qbEncodeElement($v_itm, $k_index, $k, $post);
				}
				return $post;
			}
			default:
				return null;
		}
	}
	else
	{
		switch($ty)
		{
			case "NULL":
				return "null";
			case "string":
				return "&{$indx}=" . ((($key === "_ty") || ($key === "_q_")) ? "" : "_") . urlencode($v);
			case "integer":
			case "double":
				return "&{$indx}=" . urlencode($v);
			case "boolean":
				return $v ? "&{$indx}=true" : "&{$indx}=false";
			case "array":
			{
				$ret = "";
				foreach ($v as $k => $v_itm)
				{
					$k_index = $indx ? $indx . "[" . rawurlencode($k) . "]" : rawurlencode($k);
					$ret .= qbEncodeElement($v_itm, $k_index, $k);
				}
				return $ret;
			}
			default:
				return null;
		}
	}
}

/**
 * Encodes one element for a request
 * 
 * @param mixed $v
 * @param string|integer $indx
 * @param string $key
 * @param array $post
 * @return string
 */
function qbArrayToUrl($v, $indx = null)
{
	$ty = gettype($v);
	switch($ty)
	{
		case "NULL":
			return "null";
		case "string":
		case "integer":
		case "double":
			return "&{$indx}=" . urlencode($v);
		case "boolean":
			return $v ? "&{$indx}=true" : "&{$indx}=false";
		case "array":
		{
			$ret = "";
			foreach ($v as $k => $v_itm)
			{
				$k_index = $indx ? $indx . "[" . rawurlencode($k) . "]" : rawurlencode($k);
				$ret .= qbArrayToUrl($v_itm, $k_index);
			}
			return $ret;
		}
		default:
			return null;
	}
}

/**
 * Empties a directory 
 * 
 * @param string $dir
 * @param boolean $self
 * @return boolean
 */
function qEmptyDir($dir, $self = false)
{
	$files = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
				RecursiveIteratorIterator::CHILD_FIRST);

	foreach ($files as $fileinfo)
		$fileinfo->isDir() ? rmdir($fileinfo->getRealPath()) : unlink($fileinfo->getRealPath());

	return $self ? rmdir($dir) : true;
}

/**
 * Gets the ID of a type
 * 
 * @param string $type
 * @return integer
 */
function getTypeId($type)
{
	if ($type[0] === strtolower($type[0]))
		return QModelType::GetScalarTypeId($type);
	else
		return QApp::GetStorage()->getTypeIdInStorage($type);
}

/**
 * @todo Deprecated
 * @deprecated 
 * 
 * @param string $html
 * @param string $bind
 * @return string
 */
function qb_inject_bind($html, $bind)
{
	if (($p = strpos($html, ">")) !== false)
	{
		if ($p && ($html[$p - 1] === "/"))
			$p--;
		return substr($html, 0, $p).$bind.substr($html, $p);
	}
	else
		return $html;
}

if (!function_exists('qmkdir'))
{
	/**
	 * Creates a directory using the result of umask() for permissions
	 * 
	 * @param string $path
	 * @param boolean $recursive
	 * @param integer $umask
	 * @return boolean
	 */
	function qmkdir($path, $recursive = true, $umask = null)
	{
		return empty($path) ? false : (is_dir($path) ? true : (mkdir($path, ($umask === null) ? (0777 & ~umask()) : $umask, $recursive)));
	}
}

/**
 * Extracts data from a JSON 
 * 
 * @param mixed $data
 * @param string $expected_type
 * @param QIModel $parent
 * @return mixed
 * @throws Exception
 */
function extractJsonRequest($data, $expected_type = null, QIModel $parent = null)
{
	if (is_object($data))
	{
		if (!$expected_type)
			return $data;
		
		if (!($expected_type && qIsA($expected_type, "QIModel")))
			throw new Exception("Invalid input data type");
		
		if ($parent)
		{
			$ret = $parent;
			$ty = get_class($parent);
		}
		else if (($ty = $data->_ty) && class_exists($ty) && qIsA($ty, $expected_type))
		{
			$ret = new $ty();
		}
		else
		{
			$ret = new $expected_type();
			$ty = $expected_type;
		}
		
		$m_ty = QModel::GetTypeByName($ty);
		
		if (($id = $data->_id) || ($id = $data->id) || ($id = $data->Id))
			$ret->setId($id);
		
		$ret->apiAdaptInput($data);
		
		foreach ($m_ty->properties as $p_name => $property)
		{
			$v = $data->$p_name;
			
			// enforce data types as accepted by the $property's definition
			if ($v === null)
			{
				$ret->set($p_name, null);
			}
			else if (is_object($v))
			{
				if (!$property->hasReferenceType())
					throw new Exception("Invalid input data");
				
				$ret->set($p_name, extractJsonRequest($v, q_reset($property->getReferenceTypes())));
			}
			else if (is_array($v))
			{
				if (!$property->hasCollectionType())
					throw new Exception("Invalid input data");
				
				$arr = new QModelArray();
				
				$exp_acc_ty = $property->getCollectionType();
				
				foreach ($v as $arr_v)
				{
					if (is_null($arr_v))
						$arr[] = null;
					else if (is_object($arr_v))
					{
						if (!$exp_acc_ty->hasReferenceType())
							throw new Exception("Invalid input data");

						$arr[] = extractJsonRequest($arr_v, q_reset($exp_acc_ty->getReferenceTypes()));
					}
					else if (is_array($arr_v))
						throw new Exception("Invalid input data");
					else
						$arr[] = $arr_v;
				}
						
				$ret->set($p_name, $arr);
			}
			else
			{
				if (!$property->hasScalarType())
					throw new Exception("Invalid input data");
				
				$ret->set($p_name, $v);
			}
		}
		
		return $ret;
	}
	/*
	else if (is_array($data))
	{
		// we will do no conversion
	}*/
	else // array and scalars that have no type
		return $data;
}

/**
 * Gets the object's internal reference
 * 
 * @param stdClass $obj
 * @return integer
 */
function qGetObjRef($obj)
{
	ob_start();
	debug_zval_dump($obj);
	$str = ob_get_clean();
	return (($p1 = strpos($str, "#")) !== false) ? ((($p2 = strpos($str, " ", $p1 + 1)) !== false) ? (int)substr($str, $p1 + 1, $p2 - $p1 - 1) : null) : null;
}

/**
 * The debug stack
 * 
 * @return array
 */
function qDebugStack()
{
	/* we may create too much confusion with this so I have disabled it
	 * $args = func_get_args();
	 * if ((func_num_args() === 1) && is_array($args[0]))
		$args = $args[0];*/
	return qDebugStackInner(func_get_args(), true, true);
}

/**
 * Inner function for qDebugStack
 * 
 * @param mixed[] $args
 * @param boolean $with_stack
 * @param boolean $on_shutdown
 */
function qDebugStackInner($args, $with_stack = false, $on_shutdown = false, string $title = '', bool $collapsed = false, bool $with_border = true, int $max_depth = 8)
{
	if ($max_depth < 1)
		return;
	
	if ($on_shutdown)
		ob_start();
	
	$css_class = "_dbg_".uniqid();
	
	?><div class="<?= $css_class ?>"><script type="text/javascript">
			if (!window._dbgFuncToggleNext)
			{
				window._dbgFuncToggleNext = function(dom_elem)
				{
					var next = dom_elem ? dom_elem.nextSibling : null;
					// skip until dom element
					while (next && (next.nodeType !== 1))
						next = next.nextSibling;
					if (!next)
						return;
					
					if ((next.offsetWidth > 0) || (next.offsetHeight > 0))
						next.style.display = 'none';
					else
						next.style.display = 'block';
				};
			}
		</script><style type="text/css">
		
		div.<?= $css_class ?> {
			font-family: monospace;
			font-size: 12px;
			<?php if ($with_border): ?>
			padding: 10px;
			margin: 10px;
			border: 2px dotted gray;
			<?php endif; ?>
		}
		
		div.<?= $css_class ?> h4 {
			font-size: 15px;
			margin: 5px 0px 5px 0px;
		}
		
		div.<?= $css_class ?> table {
			border-collapse: collapse;
			border: 1px solid black;
			padding: 3px;
		}
		
		div.<?= $css_class ?> table tr:first-child th {
			background-color: blue;
			color: white;
		}
		
		div.<?= $css_class ?> table th, div.<?= $css_class ?> table td {
			text-align: left;
			padding: 3px;
			border: 1px solid black;
			vertical-align: top;
		}

		div.<?= $css_class ?> table td {
			
		}
		
		div.<?= $css_class ?> ._dbg_params {
			cursor: pointer;
			color: blue;
		}
		
		div.<?= $css_class ?> pre {
			margin: 0;
		}
		
		<?php if ($collapsed): ?>
		div.<?= $css_class ?> pre div {
			display: none;
		}
		<?php else: ?>
		div.<?= $css_class ?> pre div > div {
			display: none;
		}
		<?php endif; ?>
		
		div.<?= $css_class ?> pre span._dbg_expand {
			cursor: pointer;
			color: blue;
		}
		
		div.<?= $css_class ?> pre span._dbg_s {
			color: green;
		}
		
		div.<?= $css_class ?> pre span._dbg_nl {
			color: red;
		}
		
		div.<?= $css_class ?> pre span._dbg_bl {
			color: orange;
		}
		
	</style><?php

	$stack = debug_backtrace();
	// remove this call
	array_shift($stack);
	// and previous
	array_shift($stack);
	
	$stack_1 = end($stack);
	$stack_1_file = $stack_1["file"] ?? null;
	
	// remove GetStack
	// array_pop($stack);
	
	// $stack = array_reverse($stack);
	$doc_root = $_SERVER["DOCUMENT_ROOT"];
	
	if ($title)
		echo "<h4>{$title}</h4>";
	
	// var_dump(array_keys($args));
	$bag = [];
	qDSDumpVar($args, $max_depth);

	if ($with_stack)
	{
		// 1. print stack
		?><h4>Stack</h4>
		<table>
			<tr>
				<th>Module</th>
				<th>Calling From</th>
				<th>Line</th>
				<th>Called Class</th>
				<th>Function</th>
				<th>Called in File</th>
				<th>Params</th>
			</tr>
			<tr>
				<th colspan="3"></th>
				<th colspan="4">Entry: <?= $stack_1_file ?></th>
			</tr>
			<?php

				foreach ($stack as $jump)
				{
					$file = $jump["file"];

					$file_module = QAutoload::GetModulePathForPath($file);
					$caption_path = $file_module ? "[".basename($file_module)."] ".substr($file, strlen($file_module)) : $file;

					$base_name = basename($file);
					$calling_class = $base_name;
					if (($base_name[0] === strtoupper($base_name[0])) && (substr($base_name, -4) === ".php"))
						$calling_class = substr($base_name, 0, -4);

					$file_short = (substr($file_module, 0, strlen($doc_root)) === $doc_root) ? substr($file_module, strlen($doc_root)) : $file_module;

					?><tr>
						<th><?= $file_module ? "[".basename($file_module)."]" : "" ?></th>
						<th><?= $calling_class ?></th>
						<th><?= $jump["line"] ?></th>
						<th><?= $jump["class"].((($jump["object"] instanceof QIModel) && ($jo_id = $jump["object"]->getId())) ? "#".$jo_id : "") ?></th>
						<td><?= $jump["function"] ?></td>
						<td><?= $caption_path.($file_short ? "<br/>".$file_short : "") ?></td>
						<td class="_dbg_params" onclick="_dbgFuncToggleNext(this.parentNode);">[Show]</th>
					</tr>
					<tr style="display: none;">
						<td colspan="3"></td>
						<td colspan="4"><?php qDSDumpVar($jump["args"]) ?></td>
					</tr>
					<?php
				}

			?>
		</table>
		<?php
	}
	?></div><?php
	
	if ($on_shutdown)
	{
		// AJAX request
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'))
		{
			QAutoload::$DebugStacks[] = ob_get_clean();
		}
		else
			register_shutdown_function("qDebugStackOutput", ob_get_clean());
	}
}

/**
 * Inner function for qDebugStackInner
 * 
 * @param mixed $var
 * @param integer $max_depth
 * @param object[] $bag
 * @param integer $depth
 */
function qDSDumpVar($var, $max_depth = 8, &$bag = null, $depth = 0, $accessModifier = null, $wasSet = null)
{
	if ($max_depth < 0)
		return;
	
	$ty = gettype($var);
	
	if (!$bag)
		$bag = array();
	
	if ($depth === 0)
		echo "<pre>\n";
	
	$pad = str_repeat("\t", $depth);
	
	switch ($ty)
	{
		case "string":
		{
			echo "[string(".strlen($var).")]".($accessModifier ? "[{$accessModifier}]" : "").($wasSet ? "[set]" : "").": ";
			echo "<span class='_dbg_s'>";
			// wordwrap ( string $str [, int $width = 75 [, string $break = "\n" [, bool $cut = false ]]] )
			if (strlen($var) > (1024 * 1024))
			{
				// very big !
				echo '"'.preg_replace(['/\\r/us', '/\\n/us'], ["\\r", "\n"], htmlspecialchars(substr($var, 0, 1024*1024))).' [... truncated ...]"';
			}
			else
				echo '"'.preg_replace(['/\\r/us', '/\\n/us'], ["\\r", "\n"], htmlspecialchars($var)).'"';
			echo "</span>";
			break;
		}
		case "NULL":
		{
			echo ($accessModifier ? "[{$accessModifier}]" : "").($wasSet ? "[set]" : "").": <span class='_dbg_nl'>[null]</span>";
			break;
		}
		case "integer":
		{
			echo "[int]".($accessModifier ? "[{$accessModifier}]" : "").($wasSet ? "[set]" : "").": ";
			echo $var;
			break;
		}
		case "double":
		{
			echo "[float]".($accessModifier ? "[{$accessModifier}]" : "").($wasSet ? "[set]" : "").": ";
			echo $var;
			break;
		}
		case "boolean":
		{
			echo "[bool]".($accessModifier ? "[{$accessModifier}]" : "").($wasSet ? "[set]" : "").": <span class='_dbg_bl'>";
			echo $var ? "true" : "false";
			echo "</span>";
			break;
		}
		case "array":
		{
			echo "<span class='_dbg_expand' onclick='_dbgFuncToggleNext(this);'>[array(".count($var).")]:</span>\n";
			echo "<div>";
			foreach ($var as $k => $v)
			{
				echo $pad."\t<b>".((is_string($k) && (strlen($k) === 0)) ? "''" : htmlspecialchars($k))."</b>";
				if ($max_depth)
					qDSDumpVar($v, $max_depth - 1, $bag, $depth + 1, $accessModifier, $wasSet);
				else
					echo "<span class='_dbg_nl'>*** too deep</span>";
				echo "\n";
			}
			echo "</div>";
			break;
		}
		case "object":
		{
			$obj_class = get_class($var);
			if (substr($obj_class, 0, strlen('class@anonymous')) === 'class@anonymous')
			{
				echo "#class@anonymous";
				break;
			}
			
			if ($obj_class === 'Generator')
			{
				echo "#Generator";
				break;
			}
			
			if ($obj_class === 'Closure')
			{
				echo "#Closure";
				break;
			}
			
			$ref_id = array_search($var, $bag, true);
			if ($ref_id === false)
			{
				end($bag);
				$ref_id = key($bag);
				$ref_id = ($ref_id === null) ? 0 : $ref_id + 1;
				
				$ref_id++;
				
				$bag[] = $var;
			}
			else
			{
				$ref_id++;
				
				echo "[{$obj_class}#{$ref_id}".($var->_id ? "; id:".$var->_id : ($var->Id ? "; Id:".$var->Id : ""))."]: <span class='_dbg_expand'>#ref</span>";
				return;
			}

			echo "<span class='_dbg_expand' onclick='_dbgFuncToggleNext(this);'>[{$obj_class}";
			if ($var instanceof \Closure)
				echo "]";
			else
				echo ($var instanceof QIModelArray ? "(".$var->count().")" : "").
						"#{$ref_id}".($var->_id ? "; id:".$var->_id : ($var->Id ? "; Id:".$var->Id : ""))."]"
						.($accessModifier ? "[{$accessModifier}]" : "");
			echo ":</span>\n";
			echo "<div>";

			$_isqm = ($var instanceof \QModel);
			$props = (array)$var; # $_isqm ? $var->getModelType()->properties : $var;
			
			$_refCls = $_isqm ? $var->getModelType()->getReflectionClass() : null;

			$null_props = [];
			
			if ($_isqm || ($var instanceof \QModelArray))
			{
				if ($var->_ts !== null)
				{
					echo $pad."\t<b>_ts: </b>";
					echo "<span class='_dbg_nl'>{$var->_ts}</span>";
					echo "\n";
				}
				if ($var->_tsp !== null)
				{
					echo $pad."\t<b>_tsp: </b>";
					echo "<span class='_dbg_nl'>". json_encode($var->_tsp)."</span>";
					echo "\n";
				}
				if ($var->_tsx !== null)
				{
					echo $pad."\t<b>_tsx: </b>";
					echo "<span class='_dbg_nl'>". json_encode($var->_tsx)."</span>";
					echo "\n";
				}
				if ($var->_wst !== null)
				{
					echo $pad."\t<b>_wst: </b>";
					echo "<span class='_dbg_nl'>". json_encode($var->_wst)."</span>";
					echo "\n";
				}
				if ($var->_rowi !== null)
				{
					echo $pad."\t<b>_rowi: </b>";
					echo "<span class='_dbg_nl'>". json_encode($var->_rowi)."</span>";
					echo "\n";
				}
			}
			
			foreach ($props as $_k => $v)
			{
				$p_name = $_k;
				if ($_k[0] === "\x00")
				{
					if (substr($_k, 0, 3) === "\x00*\x00")
					{
						$p_name = substr($_k, 3);
						$k = $_isqm ? $p_name : $p_name."(protected)";
					}
					else if (substr($_k, 0, 2 + strlen($obj_class)) === "\x00{$obj_class}\x00")
					{
						$p_name = substr($_k, 2 + strlen($obj_class));
						$k = $_isqm ? $p_name : $p_name."(private)";
					}
					else
						$p_name = $k = trim($_k, "\x00");
				}
				else
					$p_name = $k = $_k;
				
				if ($_isqm && (($p_name === "_typeIdsPath") || ($p_name === "_qini") || ($p_name === "_ty") || ($p_name === "_id") || ($p_name === "_wst") || ($p_name === "_ts") || ($p_name === "_tsx") || ($p_name === "_sc") || ($p_name === "Del__")))
					continue;
				
				$accessModifier = null;
				$wasSet = $_isqm ? $var->wasSet($k) : null;
				if ($_isqm && ($refP = $_refCls->hasProperty($p_name) ? $_refCls->getProperty($p_name) : null))
				{
					$accessModifier = $refP->isPublic() ? "public" : ($refP->isPrivate() ? "private" : ($refP->isProtected() ? "protected" : null));
				}

				if ($v !== null)
				{
					# echo $pad."\t<b>".((is_string($k) && (strlen($k) === 0)) ? "''" : htmlspecialchars($k))."</b>";
					echo $pad."\t<b>".((is_string($k) && (strlen($k) === 0)) ? "''" : htmlspecialchars($k))."</b>";
					if ($max_depth)
					{
						qDSDumpVar($v, $max_depth - 1, $bag, $depth + 1, $accessModifier, $wasSet);
					}
					else
						echo "<span class='_dbg_nl'>*** too deep</span>";
					echo "\n";
				}
				else
					$null_props[$p_name] = $p_name;
			}
			
			if ($null_props)
			{
				ksort($null_props);
				echo $pad."\t<b>Null props: ".implode(", ", $null_props)."</b>";
			}
			echo "</div>";
			break;
		}
		case "resource":
		{
			echo get_resource_type($var)." #".intval($var);
			break;
		}
		case "function":
		{
			echo "#Closure";
			break;
		}
		default:
		{
			// unknown type
			break;
		}
	}
	
	if ($depth === 0)
		echo "</pre>\n";
}

/**
 * Outputs the debug stack
 * For now we just echo
 * 
 * @param string $output
 */
function qDebugStackOutput($output)
{
	echo $output;
}

/**
 * Transforms variables into strings that can be safety injected into an SQL query 
 * 
 * @param mixed $c_bind
 * @param boolean $array_brackets
 * @return string
 * @throws Exception
 */
function _myScBind($c_bind, $array_brackets = true, $pure_null = false)
{
	$bind_ty = gettype($c_bind);
	switch ($bind_ty)
	{
		case "string":
			// return is_string($param) ? (($s = QApp::GetStorage()) ? $s->escapeString($param) : addslashes($param)) : $param;
			return "'".(($s = QApp::GetStorage()) ? $s->escapeString($c_bind) : addslashes($c_bind))."'";
		case "integer":
		case "double":
			return (string)$c_bind;
		case "boolean":
			return $c_bind ? "1" : "0";
		case "NULL":
			// dirty but needed for binds
			return $pure_null ? "NULL" : "0";
		case "array":
			// set should be like this: ('a,d'), ('d,a'), ('a,d,a'), ('a,d,d'), ('d,a,d')
			return $array_brackets ? "(".implode(",", array_map('_myScBind', $c_bind)).")" : implode(",", array_map('_myScBind', $c_bind));
		case "object":
		{
			if ($c_bind instanceof QIModel)
			{
				// var_dump($c_bind->getId(), QApp::GetStorage()->getTypeIdInStorage(get_class($c_bind)).",".$c_bind->getId());
				// return is_string($param) ? (($s = QApp::GetStorage()) ? $s->escapeString($param) : addslashes($param)) : $param;
				return "'".addslashes(QApp::GetStorage()->getTypeIdInStorage(get_class($c_bind)).",".$c_bind->getId())."'";
			}
			else
				return "'".addslashes($c_bind)."'";
		}
		default:
			throw new Exception("_myScBind :: Can not bind type: ".$bind_ty);
	}
}

/**
 * @todo
 */
function qTranslate($string)
{
	return $string;
}

/**
 * Just like token_get_all, but we consolidate/join T_NS_SEPARATOR with T_STRING
 * For example an expression like: \Namespace1\Class1
 * will be joined in one T_STRING
 * If there are whitespaces, they will be moved after T_STRING (for performance)
 * 
 * @param string $source
 * @return (string|array)[]
 */
function q_token_get_all($source, &$is_valid = null)
{
	$is_valid = true;
	if (PHP_VERSION_ID >= 80000)
	{
		$tokens = token_get_all($source);
		if (!is_array($tokens))
			return $tokens;
		$ret = [];
		foreach ($tokens as $tok)
		{
			if (is_array($tok))
			{
				if (($tok[0] === T_NAME_QUALIFIED) || ($tok[0] === T_NAME_FULLY_QUALIFIED))
				{
					$tok_line = isset($tok[2]) ? $tok[2] : null;
					$chunks = preg_split("/(\\\\)/uis", $tok[1], -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
					foreach ($chunks as $chunk)
						$ret[] = [($chunk === '\\') ? T_NS_SEPARATOR : T_STRING, $chunk, $tok_line];
					/*
					T_NAME_QUALIFIED ex: Foo\Bar;
					// Before: T_STRING T_NS_SEPARATOR T_STRING
					 */
					/*
					T_NAME_FULLY_QUALIFIED ex: \Foo;
					// Before: T_NS_SEPARATOR T_STRING
					 */
				}
				else if ($tok[0] === T_NAME_RELATIVE)
				{
					throw new \Exception('q_token_get_all error. T_NAME_RELATIVE not implemented.');
					# qvar_dumpk("T_NAME_RELATIVE", $tok);
					# die;
					/*
					namespace\Foo;
					// Before: T_NAMESPACE T_NS_SEPARATOR T_STRING
					 */
				}
				else
					$ret[] = $tok;
			}
			else
				$ret[] = $tok;
		}
		return $ret;
	}
	else
		return token_get_all($source);
}

/**
 * Calls for a render given a class name or instance, the name of the render method
 * and optional parameters and optional properties.
 * 
 * @param QWebControl|string $class_or_instance
 * @param string $method
 * @param mixed[] $params
 * @param mixed[] $properties
 * 
 * @return void
 * @throws Exception
 */
function qRender($class_or_instance, $method, $params = null, $properties = null)
{
	$obj = ($class_or_instance instanceof QWebControl) ? $class_or_instance : new $class_or_instance();
	if ($properties)
		$class_or_instance->extractFromArray($properties);
	
	$use_method = method_exists($obj, $method) ? $method : "render".ucfirst($method);
	if (!method_exists($obj, $use_method))
		throw new Exception("Invalid requested method ".(get_class($obj))."::{$method}() or ::{$use_method}()");
		
	return call_user_func_array([$obj, $use_method], $params);
}

/**
 * Gets the output of a render given a class name or instance, the name of the render method
 * and optional parameters and optional properties.
 * 
 * @param QWebControl|string $class_or_instance
 * @param string $method
 * @param mixed[] $params
 * @param mixed[] $properties
 * 
 * @return void
 * @throws Exception
 */
function qGetRender($class_or_instance, $method, $params = null, $properties = null)
{
	ob_start();
	qRender($class_or_instance, $method, $params, $properties);
	return ob_get_clean();
}


/**
 * The objective here is to limit the selector to the max selector and apply security
 * 
 * @param string $_className_
 * @param selector $selector
 * @param selector $_maxselector_
 * 
 * @return selector
 */
function qSecureSelector($selector, $_maxselector_ = null, $_className_ = null)
{
	// if ($_className_) also apply security
	// $data
	
	return $selector;
}

// @todo
function qSecurityCheck($value, $_selector_, $_class_ = null, $_method_ = null)
{
	// argument, or return ?!
	
	return $value;
}

/**
 * Intersects two selectors
 * 
 * @param selector $selector_1
 * @param selector $selector_2
 * 
 * @return selector
 */
function qIntersectSelectors($selector_1, $selector_2)
{
	if (is_string($selector_1))
		$selector_1 = qParseEntity($selector_1);
	if (is_string($selector_2))
		$selector_2 = qParseEntity($selector_2);
	
	return qIntersectSelectorsRec($selector_1, $selector_2);
}

/**
 * Recursive helper for qIntersectSelectors
 * 
 * @param selector $selector_1
 * @param selector $selector_2
 * 
 * @return selector
 */
function qIntersectSelectorsRec($selector_1, $selector_2)
{
	if ($selector_1 && $selector_2)
	{
		$result = [];
		// $all_1 = $selector_1["*"];
		$all_1 = $selector_1["*"];
		if ($all_1 !== null)
		{
			// we accept all from 2
			foreach ($selector_2 as $k => $v)
				$result[$k] = (($sv = $selector_1[$k]) !== null) ? qIntersectSelectorsRec($v, $sv) : [];
		}
		else if (($all_2 = $selector_2["*"]) !== null)
		{
			// we accept all from 1
			foreach ($selector_1 ?: [] as $k => $v)
				$result[$k] = (($sv = $selector_2[$k]) !== null) ? qIntersectSelectorsRec($v, $sv) : [];
		}
		else 
		{
			// there is no * on eiter side
			# !!!! PLEASE RESPECT THE ORDER HERE SO WE CAN INTERSECT AND COMPARE
			foreach ($selector_1 ?: [] as $k => $v)
			{
				if (($sv = $selector_2[$k]) !== null)
					$result[$k] = qIntersectSelectorsRec($v, $sv);
			}
		}
		return $result;
	}
	else
		return [];
}


function qSelectorsDiff($selector_1, $selector_2)
{
	if (is_string($selector_1))
		$selector_1 = qParseEntity($selector_1);
	if (is_string($selector_2))
		$selector_2 = qParseEntity($selector_2);
	return qSelectorsDiffRec($selector_1, $selector_2);
}

function qSelectorsDiffRec($selector_1, $selector_2)
{
	$difference = [];
	if (!empty($selector_1))
	{
		foreach ($selector_1 as $key => $value) 
		{
			if (is_array($value)) 
			{
				if(isset($selector_2[$key]) && is_array($selector_2[$key])) 
				{
					$new_diff = qSelectorsDiffRec($value, $selector_2[$key]);
					if (!empty($new_diff))
						$difference[$key] = $new_diff;
				}
				else
					$difference[$key] = $value;
			}
			else if (!array_key_exists($key, $selector_2) || ($selector_2[$key] !== $value))
				$difference[$key] = $value;
		}
	}
	else if (!empty($selector_2))
		$difference = $selector_2;
		
    return $difference;
}

/**
 * Gets a selector that represent all that $selector_2 has and it's missing in $selector_1
 * 
 * @param type $selector_1
 * @param type $selector_2
 * 
 * @return type
 */
function qSelectorsMissing($selector_1, $selector_2)
{
	if (is_string($selector_1))
		$selector_1 = qParseEntity($selector_1);
	if (is_string($selector_2))
		$selector_2 = qParseEntity($selector_2);
	return qSelectorsMissingRec($selector_1, $selector_2);
}

function qSelectorsMissingRec($selector_1, $selector_2)
{
	if (empty($selector_2))
		return [];
	else if (empty($selector_1))
		return $selector_2;
	else
	{
		$difference = [];
		foreach ($selector_2 as $key => $value)
		{
			$s1_value = $selector_1[$key];
			if ($s1_value === null)
				$difference[$key] = $value;
			else if (($s1_value !== null) && ($value !== null))
			{
				$new_diff = qSelectorsMissingRec($s1_value, $value);
				if (!empty($new_diff))
					$difference[$key] = $new_diff;
			}
		}
		
		return $difference;
	}
}

/**
 * Joins two selectors
 * 
 * @param selector $selector_1
 * @param selector $selector_2
 * 
 * @return selector
 */
function qJoinSelectors($selector_1, $selector_2)
{
	if (is_string($selector_1))
		$selector_1 = qParseEntity($selector_1);
	if (is_string($selector_2))
		$selector_2 = qParseEntity($selector_2);
	
	return qJoinSelectorsRec($selector_1, $selector_2);
}

/**
 * Recursive helper for qJoinSelectors
 * 
 * @param selector $selector_1
 * @param selector $selector_2
 * 
 * @return selector
 */
function qJoinSelectorsRec($selector_1, $selector_2)
{
	if ($selector_1 && $selector_2)
	{
		$result = $selector_1;
		foreach ($selector_2 as $k => $v)
		{
			if (($sv = $selector_1[$k]) !== null)
				$result[$k] = qJoinSelectorsRec($sv, $v);
			else
				$result[$k] = $v;
		}
		return $result;
	}
	else
		return $selector_1 ?: $selector_2;
}

function array_jump(&$arr, $pos)
{
	while ((($k = key($arr)) !== null) && ($k !== $pos))
	{
		next($arr);
	}
	
	return ($k === $pos);
}

function qrelative_path($path, $rel_to)
{
	$parts = preg_split("/(\\/)/us", $path, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
	$rel_parts = preg_split("/(\\/)/us", $rel_to, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
	
	$ret = "";
	$r = reset($rel_parts);
	$in_sync = true;
	foreach ($parts as $p)
	{
		if (!($in_sync && ($p === $r)))
		{
			$in_sync = false;
			$ret .= $p;
		}
		$r = next($rel_parts);
	}
	return $ret;
}

function _L($tag, $lang = null, $arg_1 = null, $arg_2 = null, $arg_3 = null, $arg_4 = null, $arg_5 = null, $arg_6 = null, $arg_7 = null, $arg_8 = null)
{
	$dt = QLanguage::$Data[$tag];
	if ($dt === null)
	{
		if (is_numeric($tag))
			return $tag;
		return _T($tag, $tag);
	}
	if ($lang === null)
		$lang = QApp::GetLanguage_Dim();
	$data = $dt[$lang];
	if ($data === null)
		$lang = QApp::GetDefaultLanguage_Dim();
	$data = $dt[$lang];
	if ($data === null)
		return $tag;
	if ($data instanceof Closure)
		return $data($arg_1, $arg_2, $arg_3, $arg_4, $arg_5, $arg_6, $arg_7, $arg_8);
	else
		return $data;
}

function qPathForTag($tag = null, $path = null)
{
	return QAutoload::GetPathForTag($tag = null, $path);
}

function qWebPathForTag($tag = null, $path = null)
{
	return QAutoload::GetWebPathForTag($tag, $path);
}

function getDiffCaption($d1, $d2)
{
	$interval = date_diff(new DateTime($d1), new DateTime($d2));
	$ret = "";
	if ($interval->y > 0)
		$ret .= $interval->y . " year" . (($interval->y > 1) ? "s" : "");
	else if ($interval->m > 0)
		$ret .= $interval->m . " month" . (($interval->m > 1) ? "s" : "");
	else if ($interval->d > 0)
		$ret .= $interval->d . " day" . (($interval->d > 1) ? "s" : "");
	else if ($interval->h > 0)
		$ret .= $interval->h . " hour" . (($interval->h > 1) ? "s" : "");
	else if ($interval->i > 0)
		$ret .= $interval->i . " minute" . (($interval->i > 1) ? "s" : "");
	else if ($interval->s > 0)
		$ret .= $interval->s . " second" . (($interval->s > 1) ? "s" : "");
	return $ret. " ago";
}

function q_var_export($data, bool $export_obj_nulls = false, bool $hide_array_keys = true)
{
	$obj_count_index = 1;
	return qVarExport($data, $export_obj_nulls, null, $obj_count_index, $hide_array_keys, false);
}

function qVarExport($data, $export_obj_nulls = false, \SplObjectStorage $refs = null, &$obj_count_index = 1, bool $hide_array_keys = false, bool $new_lines = true)
{
	$ty = gettype($data);
	switch($ty)
	{
		case "NULL":
		case "string":
		case "integer":
		case "double":
		case "boolean":
			return var_export($data, true);
		case "array":
		{
			if ($refs === null)
				$refs = new \SplObjectStorage();
			$ret = "[";
			$expected_k = 0;
			foreach ($data as $k => $v)
			{
				$use_expected = ($expected_k !== null) && ($expected_k === $k);
				$ret .= (($hide_array_keys && $use_expected) ? '' : (var_export($k, true) . "=>")) . qVarExport($v, $export_obj_nulls, $refs, $obj_count_index, $hide_array_keys, $new_lines).",";
				if ($use_expected)
					$expected_k++;
				else
					$expected_k = null; # we break out
			}
			$ret .= "]".($new_lines ? "\n" : "");
			return $ret;
		}
		case "object":
		{
			if ($refs === null)
				$refs = new \SplObjectStorage();
			else if ($refs->contains($data))
				return "qObjSetState(".var_export(get_class($data), true).", ".var_export($obj_count_index, true).", [], \$refs)";
			else
				$refs->attach($data);
			
			$ret = "qObjSetState(".var_export(get_class($data), true).", ".var_export($obj_count_index, true).", [";
			$obj_count_index++;
			$is_qmodel = ($data instanceof \QIModel);
			foreach ($data as $k => $v)
			{
				if ($is_qmodel && (($k === "_ty") || ($k === "_sc")))
					continue;
				if ((($v !== null) && ($v !== [])) || $export_obj_nulls)
					$ret .= var_export($k, true) . "=>" . qVarExport($v, $export_obj_nulls, $refs, $obj_count_index, $hide_array_keys, $new_lines) . ",";
			}
			$ret .= "], \$refs)".($new_lines ? "\n" : "");
			return $ret;
		}
		default:
			return var_export(null, true);
	}
}

function qObjSetState($class, $tmp_id, $array, &$refs = [])
{
	if ($refs)
		$obj = $refs[$tmp_id];
	if ($obj === null)
	{
		$obj = new $class();
		$refs[$tmp_id] = $obj;
	}
	if ($obj instanceof \QIModelArray)
	{
		foreach ($array as $k => $v)
			$obj[$k] = $v;
	}
	else
	{
		foreach ($array as $k => $v)
			$obj->$k = $v;
	}
	return $obj;
}

/**
 * Executes a query and calls the callback for each element
 * 
 * @param string $collection
 * @param string $query
 * @param mixed[] $binds
 * @param callable $callback
 * @param callable $batch_callback
 * @param QIModel[] $dataBlock 
 * 
 * @return QIModel
 * @throws Exception
 */
function QQueryEach(string $collection, string $query = null, $binds = null, callable $callback = null, callable $batch_callback = null, $limit = null, QIModel $from = null, &$dataBlock = null, $skip_security = true, $filter_selector = null)
{
	if ($limit === null)
		$limit = 1024;
	
	$props = explode(".", $collection);
	$limit_offset = 0;
	
	// $loops = 0;
	
	do
	{
		$called = false;
		
		$new_q = $collection.".{{$query} LIMIT {$limit_offset},{$limit}}";

		$data = QModelQuery::BindQuery($new_q, $binds, $from, $dataBlock, $skip_security, $filter_selector, /* $populate_only = */ false);

		$bag = [$data];
		
		foreach ($props as $_prop)
		{
			$prop = trim($_prop);
			$new_bag = [];
			foreach ($bag as $item)
			{
				if ($item->$prop !== null)
				{
					if (qis_array($item->$prop))
					{
						foreach ($item->$prop as $i)
							$new_bag[] = $i;
					}
					else
						$new_bag[] = $item->$prop;
				}
			}
			$bag = $new_bag;
		}
		
		if ($bag)
		{
			$called = true;
			if ($callback)
			{
				foreach ($bag as $fi)
					// @TODO also add elements on the way
					$callback($fi);
			}
			if ($batch_callback)
				$batch_callback($bag);
		}
		
		$limit_offset += $limit;
		
		unset($data, $bag, $new_bag);
		
		/*$loops++;
		
		var_dump($loops);
		var_dump("Memory Usage: ". (memory_get_usage(true)/1024)." KB | ".(memory_get_usage()/1024)." KB | ".(memory_get_peak_usage(true)/1024)." KB | ".(memory_get_peak_usage()/1024)." KB");
		
		if ((memory_get_usage(true)/1024) > 16384)
		{
			analize_mem_usage();
			// while(gc_collect_cycles());
		}*/
	}
	while ($called);
	
	unset($data, $bag, $new_bag);
}

function filePutContentsIfChanged_start()
{
	global $_filePutContentsIfChanged_;
	
	$_filePutContentsIfChanged_ = new stdClass();
	$_filePutContentsIfChanged_->files = [];
}

function filePutContentsIfChanged_commit(bool $roolback = false)
{
	global $_filePutContentsIfChanged_;
	if ($_filePutContentsIfChanged_ === null)
		return;
	
	foreach ($_filePutContentsIfChanged_->files ?: [] as $file_path => $file_m_time)
	{
		if ($roolback)
		{
			# restore the original file
			if (file_exists($file_path."._fpcic_bak"))
				file_put_contents($file_path, file_get_contents($file_path."._fpcic_bak"));
		}
		else if ((filesize($file_path) === filesize($file_path."._fpcic_bak")) && (file_get_contents($file_path) === file_get_contents($file_path."._fpcic_bak")))
		{
			# echo "Restore `{$file_path}` from ".filemtime($file_path)." TO {$file_m_time} <br/>\n";
			touch($file_path, $file_m_time);
		}
		else
		{
			# echo "OK! `{$file_path}` <br/>\n";
		}
		# in all cases release the backup
		unlink($file_path."._fpcic_bak");
	}
	
	$_filePutContentsIfChanged_ = null;
}

function filePutContentsIfChanged_roolback()
{
	filePutContentsIfChanged_commit(true);
}

function filePutContentsIfChanged($filename, $data, $create_dir = false)
{
	global $_filePutContentsIfChanged_;
	
	$data = is_string($data) ? $data : (string)$data;
	if (file_exists($filename) && (filesize($filename) === strlen($data)) && (file_get_contents($filename) === $data))
		// we say that there is no change
		return true;
	else
	{
		if ($create_dir && (!is_dir($dir = dirname($filename))))
			mkdir($dir, (0777 & ~umask()), true);
		
		if (($_filePutContentsIfChanged_ !== null) && file_exists($filename) && (!$_filePutContentsIfChanged_->files[realpath($filename)]))
		{
			file_put_contents($filename."._fpcic_bak", file_get_contents($filename));
			$_filePutContentsIfChanged_->files[realpath($filename)] = filemtime($filename);
		}
		
		return file_put_contents($filename, $data);
	}
}

if (!function_exists("Q_Firewall_Handle"))
{
	function Q_Firewall_Handle($limitsReached)
	{
		
	}
}

if (!function_exists("Q_Firewall_Handle"))
{
	function Q_Firewall_Handle($limitsReached)
	{
		
	}
}

if (!function_exists('Q_Firewall_Block'))
{
	function Q_Firewall_Block()
	{
		
	}
}

if (!function_exists('Q_Firewall_TriggerBlock'))
{
	function Q_Firewall_TriggerBlock($limitsReached)
	{
		return false;
	}
}

function Q_Minify_ProjectResources(array $extraDirs = [], bool $forceAll = false)
{
	$watchFolders = \QAutoload::GetWatchFolders();
	if ($extraDirs)
		$watchFolders = array_merge($watchFolders, $extraDirs);
	$filterFiles = function ($file)
	{
		$fileExt = pathinfo($file, PATHINFO_EXTENSION);
		return (
			(($fileExt === "js") && (!preg_match('/(.+)\.gen\.min\.js/', $file)) && (!preg_match('/(.+)\.gen_(.+)\.min.js/', $file))) || 
			(($fileExt === "css") && (!preg_match('/(.+)\.gen\.min\.css/', $file)) && (!preg_match('/(.+)\.gen_(.+)\.min.css/', $file)))
		);
	};

	foreach ($watchFolders ?: [] as $watchFolder)
	{
		// if force all - remve all the generated files
		if ($forceAll)
		{
			$rc = null;
			$out = null;
			$rs = exec("find {$watchFolder} -name '*.gen.min.js' -type f -delete", $out, $rc);

			$rc = null;
			$out = null;
			$rs = exec("find {$watchFolder} -name '*.gen_*.min.js' -type f -delete", $out, $rc);

			$rc = null;
			$out = null;
			$rs = exec("find {$watchFolder} -name '*.gen.min.css' -type f -delete", $out, $rc);

			$rc = null;
			$out = null;
			$rs = exec("find {$watchFolder} -name '*.gen_*.min.css' -type f -delete", $out, $rc);
		}
		$watchFolderResourcesFiles = Q_GetDirFiles($watchFolder, $filterFiles);
		foreach ($watchFolderResourcesFiles ?: [] as $resFile)
			Q_MinifyResource($resFile, true);
	}
}

function Q_GetDirFiles(string $dir, callable $filterCallback = null, array &$files = [])
{
	$dirFiles = scandir($dir);
    foreach($dirFiles ?: [] as $dirFile)
	{
		if (($dirFile === ".") || ($dirFile === ".."))
			continue;
        $dirFilePath = realpath($dir . DIRECTORY_SEPARATOR . $dirFile);
		// if is file
        if (is_file($dirFilePath) && ((!$filterCallback) || (call_user_func_array($filterCallback, [$dirFilePath]))))
		{
			$files[] = $dirFilePath;
        } 
		else if (is_dir($dirFilePath))
		{
            Q_GetDirFiles($dirFilePath, $filterCallback, $files);
        }
    }
    return $files;
}

function Q_MinifyJs_SingleFile($toMinifyFile, $toSaveFile)
{
	$rc = null;
	$rs = null;
	$out = null;
	$rs = exec(($exec_cmd = 'uglifyjs ' . $toMinifyFile . ' --compress --mangle -o '.escapeshellarg($toSaveFile)), $out, $rc);
	if (\QAutoload::GetDevelopmentMode())
	{
		if (!file_exists($toSaveFile))
		{
			echo '<div style="color: red;">' . $exec_cmd . '</div>';
			throw new \Exception('Q_MinifyJs::Unable to create: '. $toSaveFile . ' | for: '.var_export($toMinifyFile, true));
		}
	}
	return $rs;
}

function Q_MinifyCss_SingleFile(string $toMinifyFile, string $toSaveFile)
{
	$rc = null;
	$rs = null;
	$out = null;
	if ((!defined('USE_CSSO_FOR_MINIFY')) || (!USE_CSSO_FOR_MINIFY))
		$rs = exec('uglifycss --max-line-len 512 '.$toMinifyFile.' > '.escapeshellarg($toSaveFile), $out, $rc);
	else
		$rs = exec('cat '.$toMinifyFile.' | csso -o ' . escapeshellarg($toSaveFile), $out, $rc);
	if (\QAutoload::GetDevelopmentMode())
	{
		if (!file_exists($toSaveFile))
		{
			throw new \Exception('Q_MinifyJs::Unable to create (' . (
				(defined('USE_CSSO_FOR_MINIFY') && USE_CSSO_FOR_MINIFY) ? 'use csso' : 'use uglify') . '): ' . $toSaveFile . ' | for: '.var_export($toMinifyFile, true));
		}
	}
	return $rs;
}

function Q_MinifyResource(string $file, bool $canGenerateMin = false)
{
	$ext = pathinfo($file, PATHINFO_EXTENSION);
	$fileDir = pathinfo($file, PATHINFO_DIRNAME) . "/";
	$fileName = pathinfo($file, PATHINFO_FILENAME);
	$fileWithoutExt = $fileDir . $fileName;
	$minGenFileToUse = $fileWithoutExt . ".gen.min." . $ext;
	$minGenFileToGen = $fileWithoutExt . ".gen_" . filemtime($file) . ".min." . $ext;
	if ((!file_exists($minGenFileToGen)) || (!file_exists($minGenFileToUse)))
	{
		if ($canGenerateMin)
		{
			$rc = null;
			$out = null;
			$toRmGensPath = $fileName . ".gen_*.min." . $ext;
			$rc = null;
			$out = null;
			$rs = exec("find {$fileDir} -name '{$toRmGensPath}' -type f -delete", $out, $rc);
			if ($ext === 'js')
			{
				Q_MinifyJs_SingleFile(escapeshellarg($file), $minGenFileToGen);
			}
			else if ($ext ===   'css')
			{
				Q_MinifyCss_SingleFile(escapeshellarg($file), $minGenFileToGen);
			}
			copy($minGenFileToGen, $minGenFileToUse);
		}
		else if (\QAutoload::GetDevelopmentMode())
		{
			echo "<div style='color: red;'>File [{$file} -> {$minGenFileToGen} -> {$minGenFileToUse}] not generated</div>";
		}
	}
	return file_exists($minGenFileToUse) ? $minGenFileToUse : $file;
}

function Q_MinifyCss_NEW(array $files, string $temp_path = 'code/temp/res/', string $file_extension = 'gen.min.css')
{
	return Q_MinifyJs_NEW($files, $temp_path, $file_extension);
}

function Q_MinifyJs_NEW(array $files, string $temp_path = 'code/temp/res/', string $file_extension = 'gen.min.js')
{
	if (!$files)
		return false;

	$toMinifyFilesForKey = [];
	$toMinifyFiles = [];
	foreach ($files as $f)
	{
		if (!file_exists($f))
		{
			if (\QAutoload::GetDevelopmentMode())
			{
				throw new \Exception("File [{$f}] not found when trying to minify!");
			}
			continue;
		}
		$toMinifyFiles[] = $f;
		$toMinifyFilesForKey[] = $f;
	}

	// sort the files in order to get the same key each time we do the minify
	sort($toMinifyFilesForKey, SORT_ASC);
	foreach ($toMinifyFilesForKey as $f)
	{	
		$sk[] = realpath($f);
		$sk[] = filemtime($f);
		$sk[] = filesize($f);
	}

	$key = sha1(implode('\n', $sk));
	$temp_path = rtrim($temp_path, '\\/')."/";
	$toGenerateFile = $temp_path . $key . "." . $file_extension;

	$force_minify_resources = $_GET['force_minify_resources'];

	$cdn_path_changed_file = $temp_path . $key . ".cdn_changed_in_gen.txt";	
	$cdn_path_changed = (!file_exists($cdn_path_changed_file));
	if ($cdn_path_changed)
		$force_minify_resources = true;

	if ((!file_exists($toGenerateFile)) || $force_minify_resources)
	{
		if (!is_dir($temp_path))
			qmkdir($temp_path);
		$runningDir = substr(realpath(\QAutoload::GetTempWebPath()), 0, -strlen(\QAutoload::GetTempWebPath()));
		if (empty($runningDir))
			throw new \Exception("Running dir cannot be determined!");
		$runningDir = rtrim($runningDir, "\\/") . "/";
		$runningDirLen = strlen($runningDir);
		$genFileContent = "";
		foreach ($toMinifyFiles ?: [] as $file)
		{
			$isCustom = (substr($file, 0 , $runningDirLen) == $runningDir);
			$canGenerateMin = ($isCustom);
			$toUseFile = Q_MinifyResource($file, ($canGenerateMin));
			$genFileContent .= file_get_contents($toUseFile);
		}
		if (strlen($genFileContent))
			$genFileContent = \QWebRequest::ReplaceCdnUrl($genFileContent);
		file_put_contents($toGenerateFile, $genFileContent);
		if ($cdn_path_changed)
			file_put_contents($cdn_path_changed_file, "ok");
	}
	return $toGenerateFile;
}

function Q_MinifyJs(array $files, string $temp_path = 'code/temp/res/', string $file_extension = 'js', $addMinToExt = true)
{	
	if (!$files)
		return false;
	$sk = [];
	$esc_files = "";

	$toMinifyFiles = [];
	foreach ($files as $f)
	{
		if (!file_exists($f))
		{
			if (\QAutoload::GetDevelopmentMode())
			{
				throw new \Exception("File [{$f}] not found when trying to minify!");
			}
			continue;
		}
		$esc_files .= " " . escapeshellarg($f);
		$toMinifyFiles[] = $f;
	}

	// sort the files in order to get the same key each time we do the minify
	sort($toMinifyFiles, SORT_ASC);
	foreach ($toMinifyFiles as $f)
	{	
		$sk[] = realpath($f);
		$sk[] = filemtime($f);
		$sk[] = filesize($f);
	}

	$force_minify_resources = $_GET['force_minify_resources'];

	$key = sha1(implode('\n', $sk));
	$temp_path = rtrim($temp_path, '\\/')."/";
	$fp = $temp_path . $key . "." . ($addMinToExt ? "min." : "") . $file_extension;
	#var_dump($fp, file_exists($fp), \QAutoload::GetDevelopmentMode());
	if ((!file_exists($fp)) || $force_minify_resources)
	{
		if (!is_dir($temp_path))
			qmkdir($temp_path);
		if (\QAutoload::GetDevelopmentMode())
		{
			# file times
			$dump_files_new = [];
			foreach ($files ?: [] as $f)
				$dump_files_new[$f] = date("Y-m-d H:i:s", filemtime($f));
			#qvar_dump("Q_MinifyJs Running !!!", $file_extension, $fp, $dump_files_new);
		}
		if ($file_extension === 'js')
		{
			Q_MinifyJs_SingleFile($esc_files, $fp);
		}
		else if ($file_extension === 'css')
		{
			Q_MinifyCss_SingleFile($esc_files, $fp);
		}
		else
			return false;
	}
	return $fp;
}

function Q_MinifyCss(array $files, string $temp_path = 'code/temp/res/', string $file_extension = 'css')
{
	return Q_MinifyJs($files, $temp_path, $file_extension);
}

function Q_AsyncReqManager_Setup($class, $method, $args = null, $phpCgiPath = '/opt/php7/bin/php-cgi', $reqsFolder = "../temp/", $execScript = 'exec_cgi.php')
{
	if (!file_exists($execScript))
		return;

	// if we have the save folder defined - use it
	if (defined("Q_CGI_REQUESTS_FOLDER") && Q_CGI_REQUESTS_FOLDER)
		$reqsFolder = Q_CGI_REQUESTS_FOLDER;

	if (!is_dir($reqsFolder))
		qmkdir($reqsFolder);

	$reqidf = md5($class . $method . (($args && is_array($args)) ? implode("|", $args) : ""));
	$reqfp = rtrim($reqsFolder, "\\/") . "/" . $reqidf . "_request.txt";
	$reqpfp =  rtrim($reqsFolder, "\\/") . "/" . $reqidf . "_request_status.txt";

	file_put_contents($reqfp, serialize(["callback" => [$class, $method], "args" => $args]));
	file_put_contents($reqpfp, 1);

	$output = [];

	exec('bash -c "exec nohup setsid ' . $phpCgiPath . ' -f ' . realpath($execScript) . ' '
		. 'file="' . realpath($reqfp) . '" reqid="' . $reqidf . '" > /dev/null 2>&1 &"', $output);

	return $reqidf;
}

function Q_AsyncReqManager_ReqInProgress($reqidf, $reqsFolder = "../temp/")
{
	if (defined("Q_CGI_REQUESTS_FOLDER") && Q_CGI_REQUESTS_FOLDER)
		$reqsFolder = Q_CGI_REQUESTS_FOLDER;
	return file_exists(rtrim($reqsFolder, "\\/") . "/" . $reqidf . "_request_status.txt");
}

function Q_AsyncReqManager_ReleaseRequest($reqidf, $reqsFolder = "../temp/")
{
	if (defined("Q_CGI_REQUESTS_FOLDER") && Q_CGI_REQUESTS_FOLDER)
		$reqsFolder = Q_CGI_REQUESTS_FOLDER;

	if (file_exists(($fpath = rtrim($reqsFolder, "\\/") . "/" . $reqidf . "request.txt")))
		unlink($fpath);
	if (file_exists(($sfpath = rtrim($reqsFolder, "\\/") . "/" . $reqidf . "_request_status.txt")))
		unlink($sfpath);
}

function Q_AsyncReqManager_HasRunningReqs($reqsFolder = "../temp/")
{
	if (defined("Q_CGI_REQUESTS_FOLDER") && Q_CGI_REQUESTS_FOLDER)
		$reqsFolder = Q_CGI_REQUESTS_FOLDER;

	$files = scandir($reqsFolder);
	foreach ($files ?: [] as $file)
	{
		if (($file == ".") || ($file == ".."))
			continue;

		if (substr($file, -strlen("_request_status.txt")) == "_request_status.txt")
			return true;
	}
	return false;
}

function Q_GetJsonLastError()
{
	$errMsg = null;
	$lastJsonErr = json_last_error();
	switch ($lastJsonErr)
	{
		case JSON_ERROR_NONE:
		{
			$errMsg = null;
			break;
		}
		case JSON_ERROR_DEPTH:
		{
			$errMsg = 'Maximum stack depth exceeded';
			break;
		}
		case JSON_ERROR_STATE_MISMATCH:
		{
			$errMsg = 'Underflow or the modes mismatch';
			break;
		}
		case JSON_ERROR_CTRL_CHAR:
		{
			$errMsg = 'Unexpected control character found';
			break;
		}
		case JSON_ERROR_SYNTAX:
		{
			$errMsg = 'Syntax error, malformed JSON';
			break;
		}
		case JSON_ERROR_UTF8:
		{
			$errMsg = 'Malformed UTF-8 characters, possibly incorrectly encoded';
			break;
		}
		case JSON_ERROR_RECURSION:
		{
			$errMsg = 'Recurssion detected';
			break;
		}
		case JSON_ERROR_INF_OR_NAN:
		{
			$errMsg = 'Inf or nan detected';
			break;
		}
		case JSON_ERROR_UNSUPPORTED_TYPE:
		{
			$errMsg = 'Unsupported type';
			break;
		}
		default:
		{
			$errMsg = 'JSON_LAST_ERR: Unknown error';
			break;
		}
	}
	
	return $errMsg ? ["Code" => $lastJsonErr, "Message" => $errMsg] : null;
}

function full_path_to_web(string $fp = null)
{
	if ($fp === null)
		return null;
	return '/'.ltrim(substr($fp, strlen($_SERVER["DOCUMENT_ROOT"])), '/');
}

function qpreg_get(string $pattern, string $subject, array &$matches = null, int $flags = 0, int $offset = 0)
{
	$matches = null;
	$rc = preg_match($pattern, $subject, $matches, $flags, $offset);
	if ($rc === false)
		return false;
	else if ($rc === 0)
		return 0;
	$c_matches = count($matches);
	if ($c_matches === 1)
		// return general match
		return $matches[0];
	else if ($c_matches === 2)
		// return the first one marked
		return $matches[1];
	else 
		// return all matched and marked
		return array_slice($matches, 1);
}

/*
function qpreg_get_all(string $pattern, string $subject, array &$matches = null, int $flags = 0, int $offset = 0)
{
	$matches = null;
	$rc = preg_match_all($pattern, $subject, $matches, $flags, $offset);
	if ($rc === false)
		return false;
	
	var_dump($matches);
}
*/

function _trace(string $uid, array $config = null, \Closure $closure = null, $closure_context = null)
{
	# return (new \QTrace())->trace($uid, $config, $closure, $closure_context);
}

function _trace_s(string $static_class_name, string $uid, array $config = null, \Closure $closure = null)
{
	# return (new \QTrace())->trace($uid, $config, $closure, $static_class_name);
}

function q_get_lang()
{
	global $_T___INF, $_T___INF_LANG;
	if ($_T___INF === null)
		_T('test', 'test');
	return $_T___INF_LANG ?: null;
}

/**
 * TRANSLATE
 * 
 * @param type $uid
 * @param type $defaultText
 * @return type
 */
function _T($uid, $defaultText)
{
	global $_T___INF, $_T___INF_LANG, $_T___INF_DATA;
	if ($_T___INF === null)
	{
		// init
		$_T___INF = [];
		$c_user = class_exists('Omi\User') ? \Omi\User::GetCurrentUser(false, false) : null;
		if ($c_user && property_exists($c_user, 'UI_Language'))
		{
			if (!$c_user->wasSet('UI_Language'))
				$c_user->populate('UI_Language.Code');
			$ui_lang = $c_user->getUI_Language();
			if ($ui_lang && (!$ui_lang->wasSet('Code')))
				$ui_lang->populate('Code');
			$current_language = $ui_lang ? $ui_lang->getCode() : null;
			$_T___INF_LANG = $current_language ?: null;

			if ($_T___INF_LANG && file_exists("lang/{$_T___INF_LANG}.php"))
			{
				$_DATA__ = null;
				include("lang/{$_T___INF_LANG}.php");
				$_T___INF_DATA[$_T___INF_LANG] = $_DATA__;
			}
		}
		
		if ((!$_T___INF_LANG) && defined('Q_DEFAULT_USER_LANGUAGE') && Q_DEFAULT_USER_LANGUAGE && file_exists("lang/".Q_DEFAULT_USER_LANGUAGE.".php"))
		{
			# Q_DEFAULT_USER_LANGUAGE
			$_T___INF_LANG = Q_DEFAULT_USER_LANGUAGE;
			$_DATA__ = null;
			include("lang/{$_T___INF_LANG}.php");
			$_T___INF_DATA[$_T___INF_LANG] = $_DATA__;
		}
	}
	// UI_Language
	// $c_user = \Omi\User::GetCurrentUser();
	// qvar_dumpk($c_user);
	
	if ($_T___INF_LANG && $_T___INF_DATA)
	{
		$ret_text = (($txt = $_T___INF_DATA[$_T___INF_LANG][$uid]) !== null) ? $txt : 
					((($s_txt = $_T___INF_DATA[$_T___INF_LANG][$defaultText]) !== null) ? $s_txt : $defaultText);
	}
	else
		$ret_text = $defaultText;
	if (false && \QAutoload::GetDevelopmentMode()) # || ($_SERVER['REMOTE_ADDR'] === '176.24.78.34'))
	{
		# get the trace no matter what
		$dbg = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
		$last_trace = null;
		$called_in = null;
		foreach ($dbg as $trace)
		{
			if (($trace['function'] !== '_L') && ($trace['function'] !== '_T'))
			{
				# we stop
				$called_in = $trace;
				break;
			}
			$last_trace = $trace;
		}
		$lang_method = $last_trace['function'];
		$lang_line = $last_trace['line'];
		$lang_called_in_method = $called_in['function'];
		$lang_called_in_class = $called_in['class'];
		if ($lang_method === '_T')
		{
			$dbg_str = "uid:{$uid}";
		}
		else if ($lang_method === '_L')
		{
			# $dbg_str = "_L(\"".addslashes($defaultText)."\")\n<br/>{$lang_called_in_class}::{$lang_called_in_method} #{$lang_line}";
			$dbg_str = "_L(\"{$defaultText}\")";
		}
		# qvar_dumpk($lang_method, $lang_line, $lang_called_in_method, $lang_called_in_class, ($uid !== $defaultText) ? $uid : null, $defaultText);
		
		# $ret_text = "<span class='q_dbg_lang'>{$ret_text}<div>{$dbg_str}</div></span>";
		$ret_text = "{$ret_text} | {$dbg_str}";
	}
	return $ret_text;
}

function q_is_set_for_removal($item, $array = null)
{
	if (
		(($item instanceof \QIModel) && ($item->_ts == \QModel::TransformDelete)) 
		)
		return true;
	
	return false;
}

function q_find($what, $where)
{
	# q_find(['Service' => 'whatever', 'Type' => 'xxx', 'GroupName' => '???'], $elements[$k]->AssignedServices)
	
	return q_find_in_multiple($what, [$where]);
}

function q_find_in_multiple($what, $where)
{
	$find_key_value = null;
	$find_callback = null;
	$find_scalar = null;
	
	if (is_array($what))
	{
		# key => value condition
		$find_key_value = $what;
		/*
		foreach ($what as $k => $v)
		{
			$parts = explode(".", $k);
			$ref = &$find_key_value;
			foreach ($parts as $p)
			{
				$ref[$p] = [];
				$ref = &$ref[$p];
			}
			$ref = $v;
			unset($ref);
		}
		$find_key_value = $what;
		*/
	}
	else if (is_callable($what))
	{
		# callback condition
		$find_callback = $what;
		throw new \Exception('@TODO');
	}
	else if (is_scalar($what))
	{
		# just find one value
		$find_scalar = $what;
		throw new \Exception('@TODO');
	}
	
	$ret_vals = [];
	
	/*
	if ($find_key_value !== null)
	{
		$keys = [$find_key_value];
		$objs = [];
		foreach ($where as $o)
			$objs[] = [$o, $o];
		
		while ($keys && $objs)
		{
			$next_keys = [];
			$next_objs = [];
			
			foreach ($keys as $keys_cond)
			{
				foreach ($keys_cond as $k => $v)
				{
					if (is_scalar($v))
					{
						foreach ($objs as $o_data)
						{
							list($o, $ret_o) = $o_data;
							$o_val = is_array($o) ? $o[$k] : (is_object($o) ? $o->$k : null);
							# int,float,bool,string ("1" ? 1) ("1" ? true)
							$obj_found = ($o_val === $v) ? true : (
											(is_bool($v) || is_bool($o_val)) ? ((bool)$v === (bool)$o_val) : (
											(is_numeric($v) || is_numeric($o_val)) ? ((float)$v === (float)$o_val) : 
											((string)$v === (string)$o_val)));
							if ($obj_found)
								$ret_vals[] = $ret_o;
						}
					}
					else
					{
						$next_keys[] = $v;
						foreach ($objs as $o_data)
						{
							list($o, $ret_o) = $o_data;
							$next_o = is_array($o) ? $o[$k] : (is_object($o) ? $o->$k : null);
							if ($next_o)
								$next_objs[] = [$next_o, $ret_o];
						}
					}
				}
			}
			
			$keys = $next_keys;
			$objs = $next_objs;
		}
	}
	*/
	
	if ($find_key_value !== null)
	{	
		$objs = [];
		if (count($where) === 1)
			$objs = reset($where);
		else
		{
			foreach ($where as $o_list)
				foreach ($o_list as $o)
					$objs[] = $o;
		}
		
		foreach ($objs as $o_k => $o)
		{
			$cond_ok = true;
			foreach ($find_key_value as $k => $v)
			{
				$o_val = is_array($o) ? $o[$k] : (is_object($o) ? $o->$k : null);
				
				$cond_ok = ($o_val === $v) || 
							((is_bool($v) || is_bool($o_val)) && ((bool)$v === (bool)$o_val)) || 
							((is_numeric($v) || is_numeric($o_val)) && ((float)$v === (float)$o_val)) ||
							((string)$v === (string)$o_val);
				
				if (!$cond_ok)
					break;
			}
			if ($cond_ok)
				$ret_vals[$o_k] = $o;
		}
	}
	
	return $ret_vals;
}

function qformat_number($number, $locale = null)
{
	global $_q_base_num_format;

	$locale = $locale ?: (defined("Q_PRJ_LOCALE_CODE")? Q_PRJ_LOCALE_CODE : false);

	if (!$locale)
		return $number;
	
	$number = floatval(preg_replace("/[^-0-9\.]/","",$number));
	
	$fmt = $_q_base_num_format ?: ($_q_base_num_format = new NumberFormatter($locale, NumberFormatter::CURRENCY ));
	numfmt_set_symbol($fmt, NumberFormatter::CURRENCY_SYMBOL, "");
	
	return $fmt->format($number);
}

function q_merge_conf_data(array &$__CONF, string $attr, array $selector_value)
{
	foreach ($selector_value ?: [] as $key => $value)
	{
		$parts = explode(".", $key);
		$data = &$__CONF;
		foreach ($parts ?: [] as $p)
		{
			if (!isset($data[$p]))
				$data[$p] = [];
			$data = &$data[$p];
		}
		$data[$attr] = $value;
	}
}

function q_get_language_data(string $language = null)
{
	if ($language)
	{
		$_DATA__ = null;
		if (file_exists("lang/{$language}.php"))
			include("lang/{$language}.php");
		return $_DATA__;
	}
	else
	{
		$ret = [];
		
		$langs = \QQuery('Languages.{Code}')->Languages;
		if (!isset($langs[0]))
			$langs = [(object)["Code" => "RO"], (object)["Code" => "EN"]];
		foreach ($langs ?: [] as $lang)
		{
			$language = $lang->Code;
			$_DATA__ = null;
			if (file_exists("lang/{$language}.php"))
				include("lang/{$language}.php");
			else if (file_exists("lang/".strtoupper($language).".php"))
				include("lang/".strtoupper($language).".php");
			$ret[$language] = $_DATA__;
		}
		
		return $ret;
	}
}

function q_index_array(string $property = null, $elements = null, bool $as_array = false)
{
	if ($elements === null)
		return null;
	else if ($elements === false)
		return false;
	else if (is_array($elements))
	{
		$ret = [];
		foreach ($elements as $e)
			$ret[($property === null) ? $e : ((($x = $e->$property) instanceof \QIModel) ? $x->Id : (($x === null) ? 0 : $x))] = $e;
		return $ret;
	}
	else if (is_object($elements))
	{
		$ret = $as_array ? [] : (new $elements);
		foreach ($elements as $e)
			$ret[($property === null) ? $e : ((($x = $e->$property) instanceof \QIModel) ? $x->Id : (($x === null) ? 0 : $x))] = $e;
		return $ret;
	}
	else
		throw new \Exception("Import is not supported.");
}

function q_reset($list = null)
{
	if ($list === null)
		return null;
	else if (is_array($list))
	{
		/*if (\QAutoload::GetDevelopmentMode())
		{
			$dbg = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[0];
			echo "<pre>\nreset called &array line:{$dbg['line']}/{$dbg['file']}</pre>";
		}*/
		return reset($list);
	}
	else if (($list instanceof QModelArray))
	{
		/*
		if (\QAutoload::GetDevelopmentMode())
		{
			$dbg = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[0];
			echo "<pre>\nreset called &QARRAY line:{$dbg['line']}/{$dbg['file']}</pre>";
		}
		*/
		return $list->reset();
	}
	else
		throw new \Exception('Invalid argument.');
}

function q_is_remove(\QModelArray $array = null, int $pos = null)
{
	if (($array === null) || ($pos === null))
		return false;
	else if (($array->getTransformState($pos) & \QModel::TransformDelete) || 
				((($item = $array[$pos]) instanceof \QIModel) && ($item->getTransformState() & \QModel::TransformDelete)))
		return true;
	else
		return false;
}

function q_property_to_trans(string $view_name, string $label, string $property, string $val = null)
{
	$dotted = trim(str_replace(["[", "]"], [".", ""], trim($property, "'[] \t\n\r")));
	return $view_name."~".$label."~".$dotted.($val !== null ? "=".$val : "");
}

function q_parse_str_to_array(string $array_as_string = null, int $tokens_pos = 1, array $tokens = null, int $tokens_len = null)
{
	if ($tokens === null)
	{
		$tokens = token_get_all("<?php ".$array_as_string);
		if (!is_array($tokens))
			return false;
		$tokens_len = count($tokens);
	}
	
	$ret = null;
	$after_t_array = false;
	$array_end_wrap = null;
	
	for ($i = $tokens_pos; $i < $tokens_len; $i++)
	{
		$tok = $tokens[$i];
		$t_type = is_array($tok) ? $tok[0] : $tok;
		$break = false;
		switch ($t_type)
		{
			case T_WHITESPACE:
				break;
			case "(":
			{
				if ($after_t_array)
				{
					$after_t_array = false;
					break;
				}
				else
					return false;
			}
			case T_DOUBLE_ARROW:
				throw new \Exception('Not implemented atm.');
			case ",":
			{
				# next element
				break;
			}
			case ")":
			{
				if ($array_end_wrap === ')')
					$break = true;
				else
					return false; # parse error
				break;
			}
			case "]":
			{
				if ($array_end_wrap === ']')
					$break = true;
				else
					return false; # parse error
				break;
			}
			case "[":
			{
				if ($after_t_array)
					return false; # parse error
				else if ($ret === null)
				{
					$ret = [];
					$array_end_wrap = ']';
				}
				else
				{
					throw new \Exception('Not implemented atm.'); # recurse in a sub-array
					# q_parse_str_to_array(null, $i, $tokens, $tokens_len);
				}
				break;
			}
			case T_CONSTANT_ENCAPSED_STRING:
			{
				$ret[] = stripslashes(substr($tok[1], 1, -1));
				break;
			}
			case T_ARRAY:
			{
				if ($ret === null)
				{
					$ret = [];
					# then move after the (
					$after_t_array = true;
					$array_end_wrap = ')';
				}
				else
				{
					throw new \Exception('Not implemented atm.'); # recurse in a sub-array
					# q_parse_str_to_array(null, $i, $tokens, $tokens_len);
				}
				break;
			}
			default:
			{
				throw new \Exception('Parse error.');
			}
		}
		
		if ($break)
			break;
	}
	
	return $ret;
}

function q_url_encode(array $array, string $path = "", bool &$is_first = true)
{
	$q = "";
	
	$order_respected = (array_keys($array) === range(0, (count($array) - 1)));
	foreach ($array as $k => $v)
	{
		$c_path = $path ? $path."[".($order_respected ? '' : $k)."]" : $k;
		if (is_null($v))
		{
			if (!$is_first)
				$q .= "&";
			$q .= $c_path;
			$is_first = false;
		}
		else if (is_array($v))
		{
			$q .= q_url_encode($v, $c_path, $is_first);
		}
		else
		{
			if (!$is_first)
				$q .= "&";
			$q .= $c_path . "=" . (is_bool($v) ? (($v === true) ? "true" : "false") : urlencode($v));
			$is_first = false;
		}
	}
	return $q;
}

function q_move_uploaded_file(string $tmp_name, string $save_dir, string $file_name, string $upload_Mode = null, bool $unique_gen = false)
{
	if (!is_file($tmp_name))
		return false;

	# loop if exists
	# ../~uploads/uploads/file.txt-nx95hb294 | encrypt
	#$temp_saved_in = \QAutoload::GetRuntimeFolder() . "/temp/" . uniqid("temp-upload-", true);

	$mct = mime_content_type($tmp_name);

	// set here what type we accept
	if ((!isset(Q_Accepted_Uploads[$mct])) || (!($useExt = Q_Accepted_Uploads[$mct])))
	{
		return false;
	}

	if (is_array($useExt))
	{
		$useExt_arr = $useExt;
		$useExt = null;
		$fileExt = pathinfo($file_name, PATHINFO_EXTENSION);
		if (!($useExt = $useExt_arr[$fileExt]))
			return false;
	}

	$baseFn = pathinfo($file_name, PATHINFO_FILENAME);
	if ($unique_gen)
		$baseFn .= "_" . uniqid();

	$index = 0;
	while (file_exists(($save_path = $save_dir . ($new_file_name = ($baseFn . ($index ? "-".$index : "") . "." . $useExt)))))
		$index++;

	$rc = move_uploaded_file($tmp_name, $save_path);

	if ($rc && $upload_Mode)
		chmod($save_path, octdec($upload_Mode));

	return ($rc ? $save_path : false);

}

function q_get_memory_limit()
{
	$limit_string = ini_get('memory_limit');
	$unit = strtolower(mb_substr($limit_string, -1 ));
	$bytes = intval(mb_substr($limit_string, 0, -1), 10);
	switch ($unit)
	{
	  case 'k':
		 $bytes *= 1024;
		 break 1;
	  
	  case 'm':
		 $bytes *= 1048576;
		 break 1;
	  
	  case 'g':
		 $bytes *= 1073741824;
		 break 1;
	  
	  default:
		 break 1;
	}
	return $bytes;
}

function Q_Ip2Location()
{
	if (((!defined('Ip_Geo_MysqlUser')) || (!($mysqlUser = Ip_Geo_MysqlUser))) || 
		((!defined('Ip_Geo_MysqlPass')) || (!($mysqlPass = Ip_Geo_MysqlPass))) || 
		((!defined('Ip_Geo_MysqlDb')) || (!($mysqlDb = Ip_Geo_MysqlDb))))
		return false;

	$lock_path = "temp/ip2location.txt";
	if (!file_exists($lock_path))
	{
		$lock_f = fopen($lock_path, "wt");
		fwrite($lock_f, "QCodeMonitor lock");
		fclose($lock_f);
	}

	if ((!($file_lock_path = FRAME_FULL_PATH . "src/io/QFileLock.php")) || (!file_exists($file_lock_path)))
		throw new \Exception("File lock file not found!");

	require_once($file_lock_path);
	$lock = QFileLock::lock($lock_path, 5);
	if (!$lock)
		throw new \Exception("IP 2 Geo already running!");
	else
	{
		try
		{
			$mysqli = new \mysqli("127.0.0.1", $mysqlUser, $mysqlPass, $mysqlDb);
			if (($doMysqlAudit = (defined('Q_DO_MYSQL_AUDIT') && Q_DO_MYSQL_AUDIT)))
				$t1 = microtime(true);
			$res = $mysqli->query(($qs = "SELECT `id`, `ip` FROM `ip_geo`.`api_data` WHERE `api_called`='0' LIMIT 120;"));
			$exception = null;
			if ($res === false)
			{
				$exception = new \Exception($mysqli->error);
				#throw new \Exception($mysqli->error);
			}

			if ($doMysqlAudit)
			{
				#$db, $query, $ret, $t1, $type = 'select', $exception = null
				\q_doMysqlAudit("ip_geo", $qs, $res, $t1, 'select_custom');
			}

			if ($exception)
				throw $exception;
			
			while ($r = $res->fetch_assoc())
			{
				if (!$r["ip"])
					continue;
				$callUrl = "http://www.geoplugin.net/json.gp?ip=" . $r["ip"];
				$ch = q_curl_init_with_log($callUrl);
				q_curl_setopt_with_log($ch, CURLOPT_RETURNTRANSFER, true);
				q_curl_setopt_with_log($ch, CURLOPT_TIMEOUT, 3);
				q_curl_setopt_with_log($ch, CURLOPT_FOLLOWLOCATION, true);
				#$t1 = microtime(true);
				$ipDataRaw = q_curl_exec_with_log($ch);
				$failed = false;
				$countryCode = null;
				$toStoreResp = $ipDataRaw;
				#$toStoreResp = serialize($ipDataRaw);
				if ($ipDataRaw !== false)
				{
					if (($ipData = json_decode($ipDataRaw, true)))
					{
						$countryCode = $ipData["geoplugin_countryCode"] ?: null;
						#$toStoreResp = serialize($ipData);
					}
					else
						$failed = true;
				}
				else
					$failed = true;

				$exception = null;
				if ($doMysqlAudit)
					$t1 = microtime(true);
				$upRet = $mysqli->query(($q = "UPDATE `ip_geo`.`api_data` SET `api_called`=1, `api_ret_error`='" . ($failed ? 1 : 0) . "', " 
					. "`date_api_call`='" . date("Y-m-d H:i:s") . "', `api_ret`='" . $mysqli->real_escape_string($toStoreResp) . "', " 
					. "`country_code`='" . $mysqli->real_escape_string($countryCode) . "' WHERE `id`='" . $r['id'] . "';"));
				if ($upRet === false)
					$exception = new \Exception("UPDATE IP GEO ERR: " . $mysqli->error);

				if ($doMysqlAudit)
				{
					#$db, $query, $ret, $t1, $type = 'select', $exception = null
					q_doMysqlAudit("ip_geo", $q, $upRet, $t1, 'update_custom');
				}

				if ($exception)
					throw $exception;
			}
		}
		finally 
		{
			if ($lock)
				$lock->unlock();
		}
	}
}

function q_ExitOnMemoryLow()
{
	return false;
	#$t1 = microtime(true);
	$exec_ret = exec('cat /proc/meminfo | grep MemAvailable');
	$m = null;
	preg_match("/MemAvailable:(.*?)kB/", $exec_ret, $m);
	$availableMemory = ($m && $m[1]) ? trim($m[1]) : null;
	$gbAv = $availableMemory ? ($availableMemory * pow(10, -6)) : null;
	if ($gbAv && ($gbAv < QMemory_LIMIT_GB))
		q_die("Low on memory! Please reload the page!");
	#var_dump("\$availableMemory", $availableMemory . " kB", $gbAv . " GB", (microtime(true) - $t1) . " seconds!");
	#q_die('---q---');
}

function qIsValidDate($date)
{
	return true;
}

function qIsValidHour($hour)
{
	$hourp = explode(":", trim($hour));
	if (count($hourp) !== 3)
		return false;
	foreach ($hourp ?: [] as $pos => $hd)
	{
		if (strlen($hd) != 2)
			return false;
		$intVal = (int)$hd;
		if ((($pos === 0) && (($intVal < 0) || ($intVal > 23))) || (($pos > 0) && (($intVal < 0) || ($intVal > 59))))
			return false;
	}
	return true;
}

function br2nl($string)
{
    return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
}

function onMobile()
{
	return preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp' 
		. '|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|' 
		. 'windows (ce|phone)|xda|xiino/i', $_SERVER['HTTP_USER_AGENT'])||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($_SERVER['HTTP_USER_AGENT'],0,4));
}

function botDetected()
{
  return (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/bot|crawl|slurp|spider|curl|facebook|fetch|mediapartners/i', $_SERVER['HTTP_USER_AGENT']));
}

function qUnsetProps(&$data, $propsToUnset = [], $fromOtherStruct = false)
{
	$doLoopThrough = false;
	if ($data && ($data instanceof \QIModel))
	{
		if (($data instanceof \QModel))
		{
			foreach ($propsToUnset ?: [] as $prop)
			{
				$data->unsetProp($prop);
			}
		}
		$doLoopThrough = true;
	}
	else if (is_object($data) || is_array($data))
	{
		$doLoopThrough = true;
		if ($fromOtherStruct)
		{
			foreach ($propsToUnset ?: [] as $prop)
			{
				if (isset($data[$prop]))
					unset($data[$prop]);
			}
		}
	}

	if ($doLoopThrough)
	{
		foreach ($data ?: [] as $itmk => $itmv)
			qUnsetProps($data[$itmk], $propsToUnset, $fromOtherStruct);
	}
}

function q_remote_log_end($tag = null, \Exception $exception = null)
{
	global $__q_remote_log_curls, $__q_remote_log_end_done;
	
	if (!\QAutoload::In_Debug_Mode())
		return;
	
	# if ($__q_remote_log_end_done)
	#	return;
	
	$last_error = error_get_last();
	
	$still_running = [];
	foreach ($__q_remote_log_curls ?: [] as $p => $running_curl)
	{
		if (isset($running_curl[0]))
		{
			$info = curl_getinfo($running_curl[0]);
			if (isset($info['url']))
				$still_running[$p] = $info;
		}
	}
	
	$endtime = microtime(true);
	
	# $ob_content = ob_get_contents();
	$z_tags = [];
	$z_tags['@metrics'] = q_remote_log_get_metrics();
	
	q_remote_log_sub_entry([
			[
				'Timestamp_ms' => (string)$endtime,
				'Tags' => ['tag' => ($tag ?? 'end-of-request'), 'request-id' => \QWebRequest::Get_Request_Id()],
				'Traces' => ($exception ?? new \Exception())->getTraceAsString(),
				"Is_Error" => $exception ? true : null,
				'Data' => ["last_error" => $last_error, "running_curls_count" => count($still_running), "running_curls" => $still_running, /*, 'ob' => $ob_content */ 
							'full_trace' => $exception ? $exception->getTrace() : null],
			]
		], [
			'Timestamp_ms_end' => (string)$endtime,
			'Tags' => $z_tags,
			"Is_Error" => $exception ? true : null,
		]);
	
	# $__q_remote_log_end_done = true;
}

function q_remote_log_is_bot()
{
	return preg_match("/"
					.'(BLEXBot|crawler|applebot|bingbot|Googlebot|Yandex)'
				. "/uis", $_SERVER['HTTP_USER_AGENT']);
}

function q_remote_log_start()
{
	global $__q_remote_log_start_curl;
	
	if ((!\QAutoload::In_Debug_Mode()) || q_remote_log_is_bot())
		return;
	
	if (\QWebRequest::Get_Request_Id_For_Logs() && (\QWebRequest::Get_Request_Id() !== \QWebRequest::Get_Request_Id_For_Logs()))
	{
		q_remote_log_sub_entry([[
				'Timestamp_ms' => (string)$_SERVER['REQUEST_TIME_FLOAT'],
				'Tags' => ['tag' => 'sub-request-start', 'request-id' => \QWebRequest::Get_Request_Id(), '@metrics' => q_remote_log_get_metrics()],
				'Traces' => null,
				'Data' => [
					'Request_URI' => parse_url($_SERVER['SCRIPT_URI'], PHP_URL_PATH),
					'Cookies' => $_SERVER['HTTP_COOKIE'],
					'User_Agent' => $_SERVER['HTTP_USER_AGENT'],

					'HTTP_GET' => $_SERVER['REQUEST_URI'], # (!empty($_GET)) ? json_encode($_GET, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS | JSON_INVALID_UTF8_SUBSTITUTE) : null;
					#  php://input is a read-only stream that allows you to read raw data from the request body. php://input is not available with enctype="multipart/form-data". 
					'HTTP_POST' => file_get_contents('php://input'), # (!empty($_POST)) ? json_encode($_POST, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS | JSON_INVALID_UTF8_SUBSTITUTE) : null;
					'HTTP_FILES' => (!empty($_FILES)) ? json_encode($_FILES, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS | JSON_INVALID_UTF8_SUBSTITUTE) : null,
				],
			]]);
		return;
	}
	
	# $t0 = microtime(true);
	try
	{
		ob_start();
		
		if ($__q_remote_log_start_curl)
		{
			$curl = $__q_remote_log_start_curl;
			curl_reset($curl);
		}
		else
		{
			$curl = $__q_remote_log_start_curl = curl_init();
		}
				
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		
		{
			$tags = [];
			
			if (isset($_SERVER['CONTENT_TYPE']) && (strpos(strtolower($_SERVER['CONTENT_TYPE']), 'application/x-www-form-urlencoded') !== false) && isset($_POST['_qb0']['_q_']))
			{
				# we have some post
				$tags['call'] = $_POST['_qb0']['_q_']."(";
				$i = 0;
				while (isset($_POST['_qb0'][$i]) && is_string($_POST['_qb0'][$i]))
				{
					if ($i > 0)
						$tags['call'] .= ", ";
					$tags['call'] .= ($_POST['_qb0'][$i][0] === "_") ? json_encode(substr($_POST['_qb0'][$i], 1)) : $_POST['_qb0'][$i];
					$i++;
				}
				$tags['call'] .= ")";
			}
			
			$tags['@metrics'] = q_remote_log_get_metrics();
			
			
			# BASE_HREF
			$base_href = rtrim((BASE_HREF[0] === '/') ? substr(BASE_HREF, 1) : BASE_HREF, '/');
			$callback_url_str = "http".($_SERVER['HTTPS'] ? 's' : '')."://{$_SERVER['HTTP_HOST']}/".($base_href ? $base_href."/" : "")."debug-location";
			
			$post_data = [

				'Remote_Idf' => $_SERVER['SERVER_NAME'],
				'Remote_RId' => \QWebRequest::Get_Request_Id(),

				'Date' => date("Y-m-d H:i:s", $_SERVER['REQUEST_TIME_FLOAT']),
				'Timestamp_ms' => $_SERVER['REQUEST_TIME_FLOAT'],

				'Method' => $_SERVER['REQUEST_METHOD'],
				'IP_v4' => $_SERVER['REMOTE_ADDR'],
				'Is_Ajax' => \QWebRequest::IsAjaxRequest(),

				'Session_Id' => session_id() ?: null,

				'Request_URI' => parse_url($_SERVER['SCRIPT_URI'], PHP_URL_PATH),
				'Cookies' => $_SERVER['HTTP_COOKIE'],
				'User_Agent' => $user_agent,

				'HTTP_GET' => $_SERVER['REQUEST_URI'], # (!empty($_GET)) ? json_encode($_GET, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS | JSON_INVALID_UTF8_SUBSTITUTE) : null;
				#  php://input is a read-only stream that allows you to read raw data from the request body. php://input is not available with enctype="multipart/form-data". 
				'HTTP_POST' => file_get_contents('php://input'), # (!empty($_POST)) ? json_encode($_POST, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS | JSON_INVALID_UTF8_SUBSTITUTE) : null;
				'HTTP_FILES' => (!empty($_FILES)) ? json_encode($_FILES, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS | JSON_INVALID_UTF8_SUBSTITUTE) : null,
				'Tags' => empty($tags) ? null : json_encode($tags, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS | JSON_INVALID_UTF8_SUBSTITUTE),
				
				'__Callback_URL__' => $callback_url_str,
				'__key__' => 'x3n75gtcb8i27h2xgz2bfnmzh5bonf9j2hx568cgb13hnpcg7',
			];

			curl_setopt_array($curl, [
				CURLOPT_URL => "https://provision.travelfuse.ro/remote_log.php", # .(($_SERVER['REMOTE_ADDR'] === '82.78.175.39') ? '?show_output=1' : ''),
				CURLOPT_POSTFIELDS => gzcompress( json_encode($post_data, JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ),
				CURLOPT_HTTPHEADER => [
										# 'Content-Type: application/json',
									],
				CURLOPT_RETURNTRANSFER => 0,
			]);
			
			$rc = curl_exec($curl);

			register_shutdown_function(function () {
				
				# wrap it so it's executed last
				register_shutdown_function(function () {
					try
					{
						q_remote_log_end('register_shutdown_function');
						
						# cleanup
						# if ($_SERVER['REMOTE_ADDR'] === '82.78.175.39')
						# find /home/tfc_velmar_dreams_ro/temp/* -mmin +60 -delete
						# mkdir("../temp/request_logs/");
						# mkdir("../temp/php_pids/");
						
					}
					catch (\Exception $ex) {}
				});
			});
		}
	}
	catch (\Exception $ex)
	{
		# nothing
	}
	finally
	{
		#if ($_SERVER['REMOTE_ADDR'] === '82.78.175.39')
		#	ob_end_flush();
		ob_end_clean();
	}
	# $t1 = microtime(true);
	# if ($_SERVER['REMOTE_ADDR'] === '82.78.175.39')
	{
		# qvar_dump($t1 - $t0, $post_data);
	}
	# q_remote_log_end
}

function q_curl_multi_start($sr_handles_list, $multi_object)
{
	global $__q_remote_log_curls;
	
	$traces_list = [];
	foreach ($sr_handles_list as $sr_key => $sr_handle)
	{
		$found = false;
		if (isset($__q_remote_log_curls[$sr_key][0]) && ($__q_remote_log_curls[$sr_key][0] === $sr_handle))
		{
			$found = $__q_remote_log_curls[$sr_key];
		}
		else
		{
			foreach ($__q_remote_log_curls as $curl_data)
			{
				if (($sr_handle !== null) && ($curl_data[0] === $sr_handle))
				{
					$found = $curl_data;
					break;
				}
			}
			if (!$found)
			{
				$found = [$sr_handle, null, []];
				if (!isset($__q_remote_log_curls[$sr_key]))
					$__q_remote_log_curls[$sr_key] = $found;
				else
					$__q_remote_log_curls[] = $found;
			}
		}
		
		$z_data = ['url' => ($found[1] ?: ($found[2][CURLOPT_URL] ?? null))];
		if ($found[2][CURLOPT_POSTFIELDS] !== null)
			$z_data['post'] = $found[2][CURLOPT_POSTFIELDS];
		if (isset($found[2]))
		{
			$z_curl_opts = $found[2];
			unset($z_curl_opts[CURLOPT_POSTFIELDS]);
			$z_data['curl_opts'] = $z_curl_opts;
		}
		
		$traces_list[] = [
			'Index' => sha1((string)$sr_key),
			'Timestamp_ms' => (string)microtime(true),
			'Tags' => ['tag' => 'curl_multi_exec', 'start'],
			'Traces' => (new \Exception())->getTraceAsString(),
			'Data' => $z_data,
		];
	}
	
	q_remote_log_sub_entry($traces_list);

}

function q_curl_multi_exec_with_log($multi_handle, int &$still_running = null, $multi_object = null)
{
	global $__q_remote_log_curls;
	# if ($_SERVER['REMOTE_ADDR'] === '82.78.175.39')
	{
		# throw new \Exception('eeeex!');
		# ob_start();
		# curl_multi_info_read($multi_handle);
		# file_put_contents("", ob_get_clean();
	}
	# ok ...
	
	$rc = curl_multi_exec($multi_handle, $still_running);
	$q_messages = [];
	
	while (($msg = curl_multi_info_read($multi_handle)))
	{
		$q_messages[] = $msg;
		try
		{
			if (($handle = $msg["handle"]) && (($msg["result"] === CURLE_OK) || ($msg["result"] === CURLE_PARTIAL_FILE) || curl_errno($handle))) # error
			{
				# is_array($__q_remote_log_curls) && 
				$is_error = curl_errno($handle);
				# we got some data
				$found_curl = null;
				foreach ($__q_remote_log_curls as $pos => $curl_data)
				{
					if (($handle !== null) && ($curl_data[0] === $handle))
					{
						$found_curl = $curl_data;
						unset($__q_remote_log_curls[$pos]);
						break;
					}
				}
				
				if (!$found_curl)
					$found_curl = [$handle, curl_getinfo($handle, CURLINFO_EFFECTIVE_URL), []];
				
				if ($handle && ($multi_object instanceof \Omi\Util\SoapClientContext))
					$under_index = $multi_object->getRequestIdByHandle($handle);
				else
					$under_index = null;

				$content_data = null;
				if ($is_error)
				{
					$content_data = [curl_errno($handle), curl_error($handle), curl_getinfo($handle)];
				}
				else
					$content_data = curl_multi_getcontent($handle);
				
				$z_tags = ['tag' => 'curl_multi_exec'];
				
				$full_data = [];
				
				if ($is_error)
				{
					$z_tags['error'] = [$is_error, curl_error($handle)];
					$z_tags['error_code'] = curl_errno($handle);
					
					$full_data = ['response' => false, 'curl_error' => curl_error($handle), 'curl_getinfo@response' => curl_getinfo($handle)];
				}
				else
				{
					$full_data = ['response' => base64_encode(gzcompress($content_data)), 'curl_getinfo@response' => curl_getinfo($handle)];
				}

				$using_index_z = ($under_index !== null) ? sha1((string)$under_index) : null;
				q_remote_log_sub_entry([
					[
						'Index' => $using_index_z,
						'Timestamp_ms_end' => $using_index_z ? (string)microtime(true) : null,
						'Tags' => $z_tags,
						'Is_Error' => $is_error ? true : null,
						'Traces' => (new \Exception())->getTraceAsString(),
						'Data' => $full_data,
					]
				]);
			}
		}
		catch (\Exception $ex)
		{
			# ignore it
		}
	}
	
	return [$rc, $q_messages];
}

function q_curl_reset_with_log($curl)
{
	global $__q_remote_log_curls;
	if ($__q_remote_log_curls === null)
		$__q_remote_log_curls = [];
	
	$found = false;
	foreach ($__q_remote_log_curls as $pos => $exist_handle_data)
	{
		list ($exist_curl) = $exist_handle_data;
		if ($exist_curl === $curl)
		{
			$__q_remote_log_curls[$pos] = [$curl, null, []];
			$found = true;
			break;
		}
	}
	if (!$found)
		$__q_remote_log_curls[] = [$curl, null, []];
	
	return curl_reset($curl);
}

function q_curl_init_with_log($url = null)
{
	global $__q_remote_log_curls;
	if ($__q_remote_log_curls === null)
		$__q_remote_log_curls = [];

	$curl = curl_init($url);
	
	$__q_remote_log_curls[] = [$curl, null, []]; # handle, url, opts
	
	return $curl;
}

function q_curl_setopt_with_log($handle, $option, $value)
{
	global $__q_remote_log_curls;
	if (is_array($__q_remote_log_curls))
	{
		foreach ($__q_remote_log_curls as $pos => $curl_data)
		{
			if ($curl_data[0] === $handle)
			{
				$__q_remote_log_curls[$pos][2][$option] = $value;
				break;
			}
		}
	}
	return curl_setopt($handle, $option, $value);
}

function q_curl_setopt_array_with_log($handle, $options)
{
	global $__q_remote_log_curls;
	if (is_array($__q_remote_log_curls) && is_array($options))
	{
		foreach ($__q_remote_log_curls as $pos => $curl_data)
		{
			if ($curl_data[0] === $handle)
			{
				$__q_remote_log_curls[$pos][2] += $options;
				break;
			}
		}
	}
	return curl_setopt_array($handle, $options);
}

function q_curl_exec_with_log($handle)
{
	global $__q_remote_log_curls;
	
	$curl_data = null;
	$curl_pos = null;
	foreach ($__q_remote_log_curls ?: [] as $pos => $curl_d)
	{
		if ($curl_d[0] === $handle)
		{
			$curl_data = $curl_d;
			$curl_pos = $pos;
		}
	}
	
	$index = sha1(uniqid("", true));
	
	$z_data = ['url' => ($curl_data[1] ?: ($curl_data[2][CURLOPT_URL] ?? null))];
	if ($curl_data[2][CURLOPT_POSTFIELDS] !== null)
		$z_data['post'] = $curl_data[2][CURLOPT_POSTFIELDS];
	if (isset($curl_data[2]))
	{
		$z_curl_opts = $curl_data[2];
		unset($z_curl_opts[CURLOPT_POSTFIELDS]);
		$z_data['curl_opts'] = $z_curl_opts;
	}
	
	q_remote_log_sub_entry([
		[
			'Index' => $index,
			'Timestamp_ms' => (string)microtime(true),
			'Tags' => ['tag' => 'curl_exec'],
			'Traces' => (new \Exception())->getTraceAsString(),
			'Data' => $z_data,
		]
	]);
	
	$rc = curl_exec($handle);
	
	if ($rc === false)
	{
		q_remote_log_sub_entry([
			[
				'Index' => $index,
				'Timestamp_ms_end' => (string)microtime(true),
				'Tags' => ['tag' => 'curl_exec', 'error', 'error_code' => curl_errno($handle)],
				'Traces' => (new \Exception())->getTraceAsString(),
				'Is_Error' => true,
				'Data' => ['response' => false, 'curl_getinfo@response' => curl_getinfo($handle), 'curl_error' => curl_error($handle)],
			]
		]);
	}
	else
	{
		q_remote_log_sub_entry([
			[
				'Index' => $index,
				'Timestamp_ms_end' => (string)microtime(true),
				'Tags' => ['tag' => 'curl_exec'],
				'Traces' => (new \Exception())->getTraceAsString(),
				'Data' => ['response' => base64_encode(gzcompress($rc)), 'curl_getinfo@response' => curl_getinfo($handle)],
			]
		]);
	}
	
	if ($curl_pos !== null)
		unset($__q_remote_log_curls[$curl_pos]);
	
	return $rc;
}

function q_remote_log_sub_entry(array $traces = null, array $post_data = null)
{
	global $__q_remote_log_start_curl;
	
	if ((!\QAutoload::In_Debug_Mode()) || q_remote_log_is_bot())
		return;

	# if ($_SERVER['REMOTE_ADDR'] !== '82.78.175.39')
	#	return;
	
	if (($traces === null) && ($post_data === null))
		return; # nothing to do
	
	# $user_agent = $_SERVER['HTTP_USER_AGENT'];
	
	# $t0 = microtime(true);
	try
	{
		# file_put_contents('test_alex_logs.json', json_encode($traces)."\n==========================\n", FILE_APPEND);
		
		ob_start();
		
		if ($__q_remote_log_start_curl)
		{
			$curl = $__q_remote_log_start_curl;
			curl_reset($curl);
		}
		else
		{
			$curl = $__q_remote_log_start_curl = curl_init();
		}
		
		if (!empty($traces))
			$traces = q_remote_log_sanitize($traces, 6);
		
		foreach ($traces as &$trace)
		{
			if (empty($trace['Index']))
				$trace['Index'] = uniqid("", true);
			
			if (!is_string($trace['Data']))
				$trace['Data'] = json_encode($trace['Data'], JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
			
			$z_tags = $trace['Tags'];
			if (!is_array($z_tags))
				$z_tags = ['tag' => $z_tags];
			$z_tags['@metrics'] = q_remote_log_get_metrics();
			
			if (!isset($z_tags['request-id']))
				$z_tags['request-id'] = \QWebRequest::Get_Request_Id();
			
			$trace['Tags'] = json_encode($z_tags, JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		}
		
		if ($post_data === null)
			$post_data = [];
		$post_data['Remote_RId'] = \QWebRequest::Get_Request_Id_For_Logs();
		if (!empty($traces))
			$post_data['Traces'] = $traces;
		
		if (($post_data['Tags'] !== null) && (!is_string($post_data['Tags'])))
			$post_data['Tags'] = json_encode($post_data['Tags'], JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		
		$rc_json = json_encode($post_data, JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		
		$rid = \QWebRequest::Get_Request_Id_For_Logs();
		
		if (!is_dir("../temp/"))
			mkdir("../temp/");
		if (!is_dir("../temp/request_logs/"))
			mkdir("../temp/request_logs/");
		if (!is_dir("../temp/request_logs/{$rid}/"))
			mkdir("../temp/request_logs/{$rid}/");
		
		file_put_contents("../temp/request_logs/{$rid}/".uniqid("", true), gzcompress($rc_json));
		/*
		curl_setopt_array($curl, [
			CURLOPT_URL => "https://provision.travelfuse.ro/remote_log.php",
			CURLOPT_POSTFIELDS => gzcompress($rc_json),
			CURLOPT_HTTPHEADER => [
									# 'Content-Type: application/json',
								],
			CURLOPT_RETURNTRANSFER => 0,
		]);

		$rc = curl_exec($curl);
		*/
		# if ($_SERVER['REMOTE_ADDR'] === '82.78.175.39')
	}
	catch (\Exception $ex)
	{
		# nothing
	}
	finally
	{
		ob_end_clean();
	}
}

function q_remote_log_sanitize($data, int $max_depth = 1, bool $top_level = true)
{
	if ($max_depth <= 0)
		return null;
	
	if (is_scalar($data) || ($data === null))
		return $data;
	else if (is_array($data) || is_object($data))
	{
		$ret = [];
		if (is_object($data))
			$ret['__obj'] = get_class($data);
		
		if (is_object($data) && (!($data instanceof \QIModelArray)))
		{
			if ($data instanceof \QIModel)
			{
				foreach ($data as $k => $v)
				{
					$rv = q_remote_log_sanitize($data->$k, $max_depth - 1, false);
					if ($rv !== null)
						$ret[$k] = $rv;
				}
			}
			else
			{
				foreach ($data as $k => $v)
					$ret[$k] = q_remote_log_sanitize($data->$k, $max_depth - 1, false);
			}
		}
		else
		{
			foreach ($data as $k => $v)
				$ret[$k] = q_remote_log_sanitize($v, $max_depth - 1, false);
		}
		
		return $ret;
	}
	else if (is_resource($data))
	{
		# bad luck
		return "#resource";
	}
	else
	{
		# bad luck
		return "#".gettype($data);
	}
}

function q_die($message = null)
{
	if (function_exists('q_remote_log_end'))
		q_remote_log_end('end-via-die');
	
	if ($message !== null)
		echo $message;
	die();
}

function q_remote_log_get_metrics()
{
	return [
		'pid' => getmypid(),
		'memory_get_usage' => memory_get_usage(),
		'memory_get_peak_usage' => memory_get_peak_usage(),
		'memory_limit' => ini_get('memory_limit'),
		'max_execution_time' => ini_get('max_execution_time'),
		'REQUEST_TIME_FLOAT' => $_SERVER['REQUEST_TIME_FLOAT'],
	];
}

function q_log_process_status()
{
	if (!is_dir("../temp/"))
		mkdir("../temp/");
	if (!is_dir("../temp/php_pids/"))
		mkdir("../temp/php_pids/");
	$pid = getmypid();
	$data = [
		'pid' => $pid,
		'status' => 'running',
		'time' => $_SERVER['REQUEST_TIME_FLOAT'],
		'date' => DateTime::createFromFormat('U.u', $_SERVER['REQUEST_TIME_FLOAT'])->format("Y-m-d H:i:s.u"),
		'rid' => (defined('Q_REQUEST_ID') && Q_REQUEST_ID) ? Q_REQUEST_ID : null,
		'sid' => session_id(),
		'user' => $_SERVER['USER'],
	];
	
	file_put_contents("../temp/php_pids/{$pid}.json", json_encode($data, JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
	
	$cwd = getcwd();
	
	register_shutdown_function(function () use ($data, $pid, $cwd) {
				# wrap it so it's executed last
				register_shutdown_function(function () use ($data, $pid, $cwd) {
					try
					{
						chdir($cwd);
						$data['status'] = 'shutdown';
						$data['finish_time'] = microtime(true);
						$data['finish_date'] = DateTime::createFromFormat('U.u', microtime(true))->format("Y-m-d H:i:s.u");
						file_put_contents("../temp/php_pids/{$pid}.json", json_encode($data, JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
					}
					catch (\Exception $ex) {}
				});
			});
}

function q_log_get_logs(string $rid)
{
	try
	{
		$max_size_send = 1024 * 1024;
		
		if (empty($rid))
			return false;
		
		$dir = realpath("../temp/request_logs/{$rid}/")."/";
		if (!is_dir($dir))
			return false;
		
		$order_by_time = [];
		foreach (scandir($dir) ?: [] as $file)
		{
			$f_path = $dir . $file;
			if (($file === '.') || ($file === '..') || (!is_file($f_path)))
				continue;
			
			$order_by_time[$file] = $f_path;
		}
		
		# order chronologically
		ksort($order_by_time);
		
		$size_sent = 0;
		
		foreach ($order_by_time as $f_path)
		{
			$tmp_size = filesize($f_path);
			echo $tmp_size."\n";
			readfile($f_path);
			echo "\n";
			unlink($f_path);
			
			$size_sent += $tmp_size; # make sure we send at least one 
			if ($size_sent > $max_size_send)
				break;
		}
		
		# if ($_SERVER['REMOTE_ADDR'] === '82.78.175.39')
		{
			$rp = realpath("../temp/request_logs/");
			if ($rp && is_dir($rp))
			{
				exec('find '.escapeshellarg($rp."/").' -mmin +60 -delete'); # delete older than 60 mins
			}
			$rp = realpath("../temp/php_pids/");
			if ($rp && is_dir($rp))
			{
				exec('find '.escapeshellarg($rp."/").' -mmin +60 -delete'); # delete older than 60 mins
			}
		}

	}
	catch (\Exception $ex)
	{
		return false;
	}
}

function _TEXT($tag)
{
	return \Omi\Cms\Text::GetByTag($tag);
}

/*
function q_insert(string $collection, array $records)
{
	$data = (\QApp::NewData())::FromArray($records);
	qvar_dump($data);
}

function q_insert_one(string $collection, array $record)
{
	return q_insert($collection, [$record]);
}
*/
