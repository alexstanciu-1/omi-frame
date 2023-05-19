<?php

require_once __DIR__."/_props_security_func.php";

$__DATA = [
	
		'@actions' => [
			'set_owner' => "q_TFH_props_security_func_set_owner",
			'set_owner_via_property' => 'q_TFH_props_security_func_set_owner_via_property',
			'set_owner_self' => "q_TFH_props_security_func_set_owner_self",
		],
		'@conditions' => [
			# 'is_new' => function (\Omi\User $c_user, \Omi\TFH\Property $object, string $app_property) {return $object->isNew(true, $app_property);},
		],
		'@enforcements' => [
			'tfh-box' => [
				[
					# 'condition' => 'is_new',
					'action' => 'set_owner',
				]
			],
			'tfh-box-via-property' => [
				[
					# 'condition' => 'is_new',
					'action' => 'set_owner_via_property',
				]
			],
			'tfh-self' => [
				[
					# 'condition' => 'is_new',
					'action' => 'set_owner_self',
				]
			],
		],
	
		'Properties' => [
				'rule' => '@H2B_Superadmin,tfh-box,@H2B_Channel',
				'enforcements' => 'tfh-box',
			],
		'Properties_Rooms' => [
				'rule' => '@H2B_Superadmin,tfh-box-via-property,@H2B_Channel',
				'enforcements' => 'tfh-box-via-property',
			],
		'Room_Occupancies' => [
				'rule' => '@H2B_Superadmin,tfh-box',
				'enforcements' => 'tfh-box',
			],
		'Age_Intervals' => [
				'rule' => '@H2B_Superadmin,tfh-box-via-property',
				'enforcements' => 'tfh-box-via-property',
			],
		'Seasons' => [
				'rule' => '@H2B_Superadmin,tfh-box',
				'enforcements' => 'tfh-box',
			],
		'Languages_Spoken' => [
				# 'rule' => '@H2B_Superadmin,tfh-box',
				# 'enforcements' => 'tfh-box',
			],
	
		'Special_Deals' => [
				'rule' => '@H2B_Superadmin,tfh-box-via-property',
				'enforcements' => 'tfh-box-via-property',
			],
		'Offers' => [
				'rule' => '@H2B_Superadmin,tfh-box',
				'enforcements' => 'tfh-box',
			],
		'Orders' => [
				'rule' => '@H2B_Superadmin,tfh-box,tfh-box-via-property',
				'enforcements' => 'tfh-box',
			],
		'Cancellation_Policies' => [
				'rule' => '@H2B_Superadmin,tfh-box',
				'enforcements' => 'tfh-box',
			],
		'Payment_Policies' => [
				'rule' => '@H2B_Superadmin,tfh-box',
				'enforcements' => 'tfh-box',
			],
		'Rate_Plans' => [
				'rule' => '@H2B_Superadmin,tfh-box',
				'enforcements' => 'tfh-box',
			],
		'Restrictions' => [
				'rule' => '@H2B_Superadmin,tfh-box',
				'enforcements' => 'tfh-box',
			],
		'Price_Profiles' => [
				'rule' => '@H2B_Superadmin,tfh-box',
				'enforcements' => 'tfh-box',
			],
		'Companies' => [
				'rule' => '@H2B_Superadmin,tfh-self',
				'enforcements' => 'tfh-self',
			],
		'Users' => [
				'rule' => '@H2B_Superadmin,tfh-box',
				'enforcements' => 'tfh-box',
			],
	
		'App' => [
			'rule' => '#deny',
			'enforcements' => '#deny',
		],
	
		'Services_Calendar' => [
			'rule' => '@H2B_Superadmin,tfh-box',
			'enforcements' => 'tfh-box',
		],
	
		'Account_Configurations' => [
			'rule' => '@H2B_Superadmin,tfh-box',
			'enforcements' => 'tfh-box',
		],
	
		'Request_Logs' => [
			'rule' => '@H2B_Superadmin',
		],
	
		'API_Systems' => [
			# 'rule' => '@H2B_Superadmin',
		],
		'Favorite_Orders' => [
			'rule' => '@H2B_Superadmin,tfh-box',
			'enforcements' => 'tfh-box',
		],
	];

