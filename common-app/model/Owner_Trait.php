<?php

namespace Omi;

trait Owner_Trait
{
	/**
	 * @storage.optionsPool Companies
	 * @storage.admin.readonly_IF ($grid_mode !== 'add')
	 * @storage.admin.render_IF (($_ttmp_usr = \Omi\User::GetCurrentUser()) && $_ttmp_usr->Type === 'H2B_Superadmin')
	 * @validation mandatory
	 * 
	 * @var \Omi\Comm\Company
	 */
	protected $Owner;
	
	/**
	 * Gets a default for a listing selector if none was specified
	 * 
	 * @return string
	 */
	public static function GetListingQuery($selector = null)
	{
		$selector = $selector ?: static::GetListingEntity();
        
		# Channel_Contracts.{Channel
		
		$q = (is_array($selector) ? qImplodeEntity($selector) : $selector)." "
				# . " SELECT {} "
				. " WHERE 1 "
				. "??Id?<AND[Id=?]"
				. "??Id_IN?<AND[Id IN (?)]"
				
			. " GROUP BY Id "
			. " ORDER BY "
				. "??OBY_Id?<,[Id ?@]"
				// . "??OBY_Name?<,[Name ?@]"
				// . "??OBY_Active?<,[Active ?@]"
			. " ??LIMIT[LIMIT ?,?]";
        
		return $q;
	}
}
