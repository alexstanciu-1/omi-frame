<?php

# this will overwrite all security rules if set

# property.property. = ['user/tag'] = ['read' => 0, 'write' => 0]

function q_security_new(string $element, string $perms = 'view', \Omi\User $user = null)
{
	# if (!\QAutoload::GetDevelopmentMode())
	#	return true;
	
	if (!$user)
		$user = \Omi\User::GetCurrentUser();
	if (!$user)
		return true;
	
	$tag = $user->_security_tag;
	$security_info = \Q_Security_New_Profile::$Data;
	
	if (!$tag)
	{
		$is_buyer = $user->Is_Channel_Owner;
		$tag = $user->_security_tag = (($is_buyer ? 'buyer' : 'owner') . ":" . $user->Type);
	}
	
	# buyer:Admin
	# owner:Admin
	# owner:H2B_SuperAdmin
	
	$p = $security_info[$tag][$element];
	
	# a bit dirty, @TODO - cleanup and remove this bit of code
	{
		if ($p === null)
			$p = $security_info[":menu:".$tag][$element];
	}
	
	if ($p === null)
		return true;
	else 
		return $p[$perms];
}

class Q_Security_New_Profile
{
	public static $Data;
}

# q_security_new(':menu:Customers')

Q_Security_New_Profile::$Data = [
	
	'*' => [
		# rules for everybody
	],
	
	'buyer:*' => [
		# 'Customers.CallRate' => ['view' => true, 'add' => true, 'edit' => true, 'delete' => true],
		# ':menu:Customers' => [],
	],
	
	'owner:*' => [
		# 'Customers.CallRate' => ['view' => true],
		# ':menu:Customers' => [],
	],
];

// make sure we disable sub-items

foreach (Q_Security_New_Profile::$Data ?: [] as $profile_name => $elements)
{
	foreach ($elements ?: [] as $k => $v)
	{
		# only if not allowed to view
		if (($v !== []) && ($v !== ['view' => false]))
			continue;
	}
}

# identical ones 

