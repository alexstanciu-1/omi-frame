<?php


/**
 * @class.name QSqlTableColumn
 */
abstract class QSqlTableColumn_frame_ extends QStorageTableColumn 
{
	
	/**
	 * The int data type as defined by mysql (other implementations should convert)
	 *
	 */
	const TypeInt			= 1;
	/**
	 * The tinyint data type as defined by mysql (other implementations should convert)
	 *
	 */
	const TypeTinyint		= 18;
	/**
	 * The smallint data type as defined by mysql (other implementations should convert)
	 *
	 */
	const TypeSmallint		= 19;
	/**
	 * The mediumint data type as defined by mysql (other implementations should convert)
	 *
	 */
	const TypeMediumint		= 20;
	/**
	 * The bigint data type as defined by mysql (other implementations should convert)
	 *
	 */
	const TypeBigint		= 2;
	/**
	 * The decimal data type as defined by mysql (other implementations should convert)
	 *
	 */
	const TypeDecimal		= 3;
	/**
	 * The float data type as defined by mysql (other implementations should convert)
	 *
	 */
	const TypeFloat			= 4;
	/**
	 * The double data type as defined by mysql (other implementations should convert)
	 *
	 */
	const TypeDouble		= 5;
	/**
	 * The bit data type as defined by mysql (other implementations should convert)
	 *
	 */
	const TypeBit			= 6;
	/**
	 * The bool data type as defined by mysql (other implementations should convert)
	 *
	 */
	const TypeBool			= 7;
	/**
	 * The char data type as defined by mysql (other implementations should convert)
	 *
	 */
	const TypeChar			= 8;
	/**
	 * The varchar data type as defined by mysql (other implementations should convert)
	 *
	 */
	const TypeVarchar		= 9;
	/**
	 * The text data type as defined by mysql (other implementations should convert)
	 *
	 */
	const TypeText			= 10;
	/**
	 * The blob data type as defined by mysql (other implementations should convert)
	 *
	 */
	const TypeBlob			= 11;
	/**
	 * The blob data type as defined by mysql (other implementations should convert)
	 *
	 */
	const TypeLongBlob		= 31;
	/**
	 * The enum data type as defined by mysql (other implementations should convert)
	 *
	 */
	const TypeEnum			= 12;
	/**
	 * The set data type as defined by mysql (other implementations should convert)
	 *
	 */
	const TypeSet			= 13;
	/**
	 * The date data type as defined by mysql (other implementations should convert)
	 *
	 */
	const TypeDate			= 14;
	/**
	 * The datetime data type as defined by mysql (other implementations should convert)
	 *
	 */
	const TypeDatetime		= 15;
	/**
	 * The timestamp data type as defined by mysql (other implementations should convert)
	 *
	 */
	const TypeTimestamp		= 16;
	/**
	 * The time data type as defined by mysql (other implementations should convert)
	 *
	 */
	const TypeTime			= 17;
	/**
	 * The text data type as defined by mysql (other implementations should convert)
	 *
	 */
	const TypeMediumText	= 22;
	/**
	 * The text data type as defined by mysql (other implementations should convert)
	 *
	 */
	const TypeLongText		= 21;
	/**
	 * The current timestamp value
	 *
	 */
	const CurrentTimestamp = -999999;
	/**
	 * The name of the column
	 *
	 * @var string
	 */
	public $name;
	/**
	 * The type of the column as defined by the QSqlTableColumn type constants
	 *
	 * @var integer
	 */
	public $type;
	/**
	 * The length of the filed (only applies for chars and decimal likes: 14,4)
	 *
	 * @var integer|string
	 */
	public $length;
	/**
	 * The values for "enum" or "set", only applies to them
	 *
	 * @var string
	 */
	public $values;
	/**
	 * The default value for the column. Uses strings for constant values and integer 
	 * constants for timestamp or others
	 *
	 * @var scalar
	 */
	public $default;
	/**
	 * The charset of the column (only applies to char types)
	 *
	 * @var string
	 */
	public $charset;
	/**
	 * The collation of the column (only applies to char types)
	 *
	 * @var string
	 */
	public $collation;
	/**
	 * Sets if the column is unsigned
	 *
	 * @var boolean
	 */
	public $unsigned;
	/**
	 * Sets if the column can accept null values
	 *
	 * @var boolean
	 */
	public $null;
	/**
	 * Puts an autoincrement on the column
	 *
	 * @var boolean
	 */
	public $auto_increment;
	/**
	 * The column's comments
	 *
	 * @var string
	 */
	public $comment;
	/**
	 * The table of the column
	 *
	 * @var QSqlTable
	 */
	public $table;
	
	/**
	 * The constructor of the column
	 *
	 * @param QSqlTable $table The table of the column
	 * @param string $name The name of the column
	 * @param integer $type The type of the column as defined by the QSqlTableColumn type constants
	 * @param string $length The length of the filed (only applies for chars)
	 * @param string $values The values for "enum" or "set", only applies to them
	 * @param scalar $default The default value for the column. Uses strings for constant values and integer constants for timestamp or others
	 * @param string $charset The charset of the column (only applies to char types)
	 * @param string $collation The collation of the column (only applies to char types)
	 * @param boolean $unsigned Sets if the column is unsigned
	 * @param boolean $null Sets if the column can accept null values
	 * @param boolean $auto_increment Puts an autoincrement on the column
	 * @param string $comments The column's comment
	 */
	public function __construct(QSqlTable $table = null, $name = null, $type = null, $length = null, $values = null, 
		$default = null, $charset = null, $collation = null, 
		$unsigned = null, $null = null, $auto_increment = null, $comment = null)
	{
		$this->table = $table;
		$this->name = $name;
		$this->type = $type;
		$this->length = $length;
		$this->values = $values;
		$this->default = $default;
		$this->charset = $charset;
		$this->collation = $collation;
		$this->unsigned = $unsigned;
		$this->null = $null;
		$this->auto_increment = $auto_increment;
		$this->comment = $comment;
	}
	
	/**
	 * Gets the list of changed properties
	 * 
	 * @return string[]
	 */
	public function getChangedProperties()
	{
		if ($this->_ols)
		{
			$list = array();
			foreach ($this->_ols as $k => $v)
			{
				if ($v !== $this->$k)
				{
					// var_dump(get_class($this));
					switch ($k)
					{
						case "values":
						{
							if ($v || $this->$k)
							{
								if (($this->type === static::TypeEnum) || ($this->type === static::TypeSet))
								{
									# qvar_dumpk($this);
									$arr_v = q_parse_str_to_array("array({$v})");
									$arr_this_k = q_parse_str_to_array("[".$this->$k."]");
									if ($arr_v !== $arr_this_k)
										$list[] = $k;
								}
								// to review this one for better comparison
								else if ((string)$v !== (string)$this->$k)
								{
									$list[] = $k;
								}
							}
							break;
						}
						case "default":
						{
							$cmp_v = $v;
							$cmp_this_k = $this->$k;
							if (is_string($cmp_v))
							{
								if ((strtolower($cmp_v) === 'current_timestamp()') || (strtolower($cmp_v) === 'current_timestamp'))
									$cmp_v = 'current_timestamp';
							}
							if (is_string($cmp_this_k))
							{
								if ((strtolower($cmp_this_k) === 'current_timestamp()') || (strtolower($cmp_this_k) === 'current_timestamp'))
									$cmp_this_k = 'current_timestamp';
							}
							
							if (($cmp_v || $cmp_this_k) && ((string)$cmp_v !== (string)$cmp_this_k))
							{
								$list[] = $k;
							}
							break;
						}
						case "charset":
						case "collation":
						{
							if ((!empty($this->$k)) || (!empty($v)))
							{
								$comp_len = $this->$k ?: (($k === 'charset') ? $this->table->charset : $this->table->collation);
								if (($v || $comp_len) && ($v."" !== $comp_len.""))
								{
									$list[] = $k;
								}
							}
							break;
						}
						case "length":
						{
							// var_dump($this->table->name.".".$this->name."(".$v.")/(".$this->$k.")");
							$comp_len = (($this->$k === false) ? null : $this->$k) ?? $this->get_default_numeric_length();
							if ($v != $comp_len)
							{
								# qvar_dumpk($k, $v, $this->$k);
								$list[] = $k;
							}
							break;
						}
						case "comment":
						{
							# we will not alter for a comment
							break;
						}
						default:
						{
							// var_dump($k, $v, $this->$k);
							$list[] = $k;
							break;
						}
					}
				}
			}
			if (empty($list))
				return null;
			else 
				return $list;
		}
		else
			return null;
	}
	
	function get_default_numeric_length()
	{
		switch ($this->type)
		{
			case static::TypeInt:
				return $this->unsigned ? 10 : 11;
			
			case static::TypeSmallint:
				return $this->unsigned ? 5 : 6;
			
			case static::TypeBigint:
				return $this->unsigned ? 20 : 20;
				
			case static::TypeTinyint:
				return $this->unsigned ? 3 : 4;
			
			case static::TypeFloat:

			case static::TypeDate:
			case static::TypeDatetime:
			case static::TypeVarchar:
			case static::TypeText:
			case static::TypeMediumText:
			case static::TypeLongText:
			case static::TypeEnum:
			case static::TypeSet:
			case static::TypeBlob:
			case static::TypeLongBlob:
			case static::TypeTimestamp:
			case static::TypeTime:
			{
				return null;
			}
			default:
			{
				if (\QAutoload::GetDevelopmentMode())
					qvar_dumpk($this->type, $this);
				throw new \Exception('Unknown type.');
			}
		}
	}
	
	/*
bit => 1,
tinyint => 4,
tinyint_u => 3,
smallint => 6,
smallint_u => 5,
mediumint => 9,
mediumint_u => 8,
int => 11,
int_u => 10,
bigint => 20,
bigint_u => 20,
decimal => [10,0],
decimal_u => [10,0],
dec => [10,0],
dec_u => [10,0],
	 */
}

