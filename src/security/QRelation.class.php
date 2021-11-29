<?php

/**
 * @todo
 * 
 * A user may represent a group
 *
 * @storage.table $GroupRelations
 * @api.todo
 * @class.name QRelation
 */
abstract class QRelation_frame_ extends QModel
{
	
	/**
	 * The name of the relation
	 * It identifies the relation
	 *
	 * @var string
	 */
	public $Name;
	/**
	 *
	 * @var QUserGroup
	 */
	public $Subject;
	/**
	 *
	 * @var QUserGroup[]
	 */
	public $Groups;
}
