<?php

function q_TFH_props_security_func_set_owner(\Omi\User $c_user, \QModelArray $objects, string $app_property)
{
	$H2B_Superadmin = ($c_user->Type === 'H2B_Superadmin') ? true : false;
	
	$ids_to_check = [];
	$existing_owners = [];
	foreach ($objects as $object)
	{
		if (($id = $object->getId()))
			$ids_to_check[$id] = $id;
	}
	
	if ($ids_to_check)
	{
		$props_in_db = \QQuery($app_property.'.{Owner.Id WHERE Id IN (?)}', [array_values($ids_to_check)])->$app_property;
		foreach ($props_in_db ?: [] as $p_in_db)
			$existing_owners[(int)$p_in_db->Id] = isset($p_in_db->Owner) ? (int)$p_in_db->Owner->Id : false;
	}
	
	$objects->populate("Owner.Id");
	$user_owner = $c_user->Owner;
	if (!isset($user_owner->Id))
		throw new \Exception('Missing user owner');
	
	$obj_id_to_own_id = []; # to fix populate bug !
	foreach ($objects as $object)
	{
		if (isset($object->Owner->Id))
			$obj_id_to_own_id[$object->getId()] = $object->Owner->Id;
	}
	
	foreach ($objects as $object)
	{
		$obj_id = (($oid = $object->getId()) && ($oid > 0)) ? (int)$oid : null;
		# $obj_owner_id = isset($object->Owner) && (($oid = $object->Owner->getId()) && ($oid > 0)) ? (int)$oid : null;
		$obj_owner_id = ((int)$obj_id_to_own_id[$object->getId()]) ?: null;
		
		if ($obj_id)
		{
			$db_owner_id = $existing_owners[$obj_id];
			if ($db_owner_id === false)
				throw new \Exception('Missing owner in DB for `'.$app_property.'`: '.$obj_id);
			else if ($db_owner_id === null)
				throw new \Exception('Missing `'.$app_property.'` in DB: '.$obj_id);
			else if ($db_owner_id !== $obj_owner_id)
				throw new \Exception("Not allowed to change owner.");
			else if ((!$H2B_Superadmin) && ($obj_owner_id !== (int)$user_owner->Id))
				throw new \Exception('Bad owner in DB!');
		}
		else
		{
			if (isset($object->_ts) && ($object->_ts & \QModel::TransformCreate)) # $object->isNew(true, $app_property)
			{
				if ($obj_owner_id)
				{
					if ((!$H2B_Superadmin) && ($obj_owner_id !== (int)$user_owner->Id))
						throw new \Exception('Not allowed to set that owner.');
				}
				else
					$object->setOwner($user_owner);
			}
			else
				throw new \Exception('Missing owner on existing object');
		}
	}
}

function q_TFH_props_security_func_set_owner_via_property(\Omi\User $c_user, \QModelArray $objects, string $app_property)
{
	$objects->populate("Property.Owner.Id");
	$objects_props = new \QModelArray();
	foreach ($objects as $object)
	{
		if (!$object->Property->Id)
			throw new \Exception('Missing property ID');
		$objects_props[] = $object->Property;
	}

	q_TFH_props_security_func_set_owner($c_user, $objects_props, "Properties");
}

function q_TFH_props_security_func_set_owner_self(\Omi\User $c_user, \QModelArray $objects, string $app_property)
{
	$H2B_Superadmin = ($c_user->Type === 'H2B_Superadmin') ? true : false;
	
	$ids_to_check = [];
	$existing_owners = [];
	foreach ($objects as $object)
		if (($id = $object->getId()))
			$ids_to_check[$id] = $id;
	if ($ids_to_check)
	{
		$props_in_db = \QQuery($app_property.'.{Id WHERE Id IN (?)}', [array_values($ids_to_check)])->$app_property;
		foreach ($props_in_db ?: [] as $p_in_db)
			$existing_owners[(int)$p_in_db->Id] = (int)$p_in_db->Id;
	}
	
	$user_owner = $c_user->Owner;
	if (!isset($user_owner->Id))
		throw new \Exception('Missing user owner');
	
	foreach ($objects as $object)
	{
		$obj_id = (($oid = $object->getId()) && ($oid > 0)) ? (int)$oid : null;
		
		if ($obj_id)
		{
			$db_owner_id = $existing_owners[$obj_id];
			if ($db_owner_id === false)
				throw new \Exception('Missing ID in DB for `'.$app_property.'`: '.$obj_id);
			else if ($db_owner_id === null)
				throw new \Exception('Missing `'.$app_property.'` in DB: '.$obj_id);
			else if ((!$H2B_Superadmin) && ($db_owner_id !== (int)$user_owner->Id))
				throw new \Exception('Bad owner in DB!');
		}
		else
		{
			if (isset($object->_ts) && ($object->_ts & \QModel::TransformCreate)) # $object->isNew(true, $app_property)
			{
				if (!$H2B_Superadmin)
					throw new \Exception('Not allowed to create a new company.');
			}
			else
				throw new \Exception('Missing owner on existing object');
		}
	}
}
