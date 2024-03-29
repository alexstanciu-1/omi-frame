<?php

class QModelTypeTimestamp extends QModelTypeScalar 
{
	/**
	 * The instance of the object
	 *
	 * @var QModelTypeTimestamp
	 */
	protected static $type;
	
	/**
	 * The protected constructor
	 *
	 */
	protected function __construct()
	{
		
	}
	
	/**
	 * Gets the instance of the type
	 * 
	 * @return QModelTypeTimestamp
	 */
	public static function GetType()
	{
		return self::$type["timestamp"] ?: (self::$type["timestamp"] = new QModelTypeTimestamp());
	}
	/**
	 * Assigns a value to the passed variable. 
	 * If the function returns true(boolean) than it was a success.
	 *
	 * @param timestamp $target
	 * @param mixed $value
	 * 
	 * @return boolean
	 */
	public function assign(&$target, $value)
	{
		if (is_numeric($value))
		{
			$target = $value;
			return true;
		}
		else if (is_string($value))
		{
			$time = strtotime($value);
			if ($time !== false)
			{
				$target = $time;
				return true;
			}
			else 
				return false;
		}
		else 
			return false;
			
	}
	/**
	 * Tests if the two values are the same
	 *
	 * @param timestamp $variable_1
	 * @param mixed $variable_2
	 * 
	 * @return boolean
	 */
	public function equals($variable_1, $variable_2)
	{
		return $variable_1 == $variable_2;
	}
	/**
	 * Checks if the variable can be converted (is valid) to this scalar type
	 *
	 * @param mixed $variable
	 * 
	 * @return boolean
	 */
	public function isCompatible($variable)
	{
		return (is_numeric($variable) || (is_string($variable) && (strtotime($variable) !== false)));
	}
	/**
	 * In case of a pseudotype (or other cases) we may need to serialize the variable before pushing it to storage
	 * This method can be useful in a lot of cases. Optional parameters can be added for numeric, date types and so on
	 *
	 * @param timestamp $variable
	 * 
	 * @return string
	 */
	public function toString($variable)
	{
		return (string)$variable;
	}
	
	// TYPE INFORMATION FROM HERE ON
	
	/**
	 * Gets the Id of the scalar
	 *
	 * @return integer
	 */
	public function getTypeId()
	{
		return QModelType::ModelTypeTimestamp;
	}
	/**
	 * Gets the name of the data type
	 *
	 * @return string
	 */
	public function getTypeName()
	{
		return "timestamp";
	}
	/**
	 * Gets the aliases of the type
	 *
	 * @return string[]
	 */
	public function getAliases()
	{
		return null;
	}
	/**
	 * Gets the supported php types where the scalar can be stored in order of preferences
	 *
	 * @return integer[]
	 */
	public function getSupportedPhpTypes()
	{
		return array("integer", "float", "string");
	}
	/**
	 * Gets the supported sql data types where the scalar can be stored in order of preferences
	 *
	 * @return integer[]
	 */
	public function getSupportedSqlDataTypes()
	{
		return array(QSqlTableColumn::TypeTimestamp, QSqlTableColumn::TypeBigint, QSqlTableColumn::TypeDatetime, 
						QSqlTableColumn::TypeVarchar, QSqlTableColumn::TypeChar);
	}
	/**
	 * Reports if this type is a pseudo type
	 *
	 * @return boolean
	 */
	public function isPseudoType()
	{
		return true;
	}
}
