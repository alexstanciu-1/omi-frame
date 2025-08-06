<?php

namespace Omi\Comm;

/**
 * @author Alex
 *
 * @storage.table Companies
 * 
 * @model.captionProperties Name,Address.City.Name
 * 
 * @class.name Company
 */
abstract class Company_mods_model_ extends \QModel
{
	/**
	 * @var int
	 */
	protected $Id;
	/**
	 * @var string
	 */
	protected $Name;
	/**
	 * @var string
	 */
	protected $Code;
	/**
	 * Registration number
	 * 
	 * @var string
	 */
	protected $Reg_No;
	/**
	 * @var string
	 */
	protected $VAT_No;
	/**
	 * @var boolean
	 */
	protected $VAT_Payer;
	/**
	 * @storage.filter ["@CALL" => "Omi\\Util\\AddressSearch::Search_Address_For_DropDown"]
	 * @storage.optionsPool Addresses
	 * @display.controls on
	 * 
	 * @var \Omi\Address
	 */
	protected $Address;	
	/**
	 * @var \Omi\Comm\Bank_Account[]
	 */
	protected $Bank_Accounts;
	/**
	 * @var string[]
	 */
	protected $Emails_List;
	/**
	 * @var string[]
	 */
	protected $Phones_List;
	/**
	 * @var \Omi\Person[]
	 */
	protected $Contacts;
	/**
	 * @storage.optionsPool Companies
	 * 
	 * @var Company[]
	 */
	protected $Accessible_By;
	/**
	 * @storage.optionsPool Companies
	 * 
	 * @storage.collection Companies_Accessible_By,Accessible_By,Companies
	 * 
	 * @var Company[]
	 */
	protected $Has_Access_To;
	/**
	 * @storage.oneToMany Owner
	 * 
	 * @var \Omi\User[]
	 */
	protected $Users;
	/**
	 * @storage.oneToMany Owner
	 * 
	 * @var \Omi\Mail_Sender[]
	 */
	protected $Mail_Senders;
	
	/**
	 * @var \Omi\TFH\Contact_Information[]
	 */
	protected $Contact_Emails_List;
	/**
	 * @var \Omi\TFH\Contact_Information[]
	 */
	protected $Contact_Phones_List;
	
	/**
	 * @var boolean
	 */
	protected $Terms_Accepted;
	/**
	 * @storage.type VARCHAR(32)
	 * 
	 * @var string
	 */
	protected $Terms_Accepted_IP;
	/**
	 * @var datetime
	 */
	protected $Terms_Accepted_Date;
	/**
	 * @storage.oneToMany Owner
	 * 
	 * @var \Omi\Mail_Sender
	 */
	protected $Mail_Sender;
	/**
	 * @var file
	 */
	protected $Logo;

	/**
	 * Model caption
	 * 
	 * @param string $tag
	 * 
	 * @return type
	 */
	public function getModelCaption($tag = null)
	{
		return $this->Name.(isset($this->Address->City->Name) ? ", ".$this->Address->City->Name : "");
	}
		
	/**
	 * Gets a default for a listing selector if none was specified
	 * 
	 * @return string
	 */
	public static function GetListingQuery($selector = null)
	{
		$selector = $selector ?: static::GetListingEntity();
        
		$q = (is_array($selector) ? qImplodeEntity($selector) : $selector)." "
				. "WHERE 1 "
				. "??Id?<AND[Id=?]"
				. "??Name?<AND[Name LIKE (?)]"
				. "??QINSEARCH_Name?<AND[Name LIKE (?)]"
				. "??WHR_Search?<AND[Name LIKE (?)]"
				. "??WHR_Is_Property_Owner?<AND[(Is_Property_Owner)]"
				. "??WHR_Is_Channel_Owner?<AND[(Is_Channel_Owner)]"
				. " ??WHR_Owner_Id?<AND[ Id=? ] "
			. " GROUP BY Id "
			. " ORDER BY "
				. "??OBY_Id?<,[Id ?@]"
				. "??OBY_Name?<,[Name ?@]"
			. " ??LIMIT[LIMIT ?,?]";
        
		return $q;
	}
	
	/**
	 * @param type $selector
	 * @param type $transform_state
	 * @param type $_bag
	 * @return type
	 */
	public function beforeCommitTransaction($selector = null, $transform_state = null, &$_bag = null, $is_starting_point = true, $appProp = null)
	{
		$ret = parent::beforeCommitTransaction($selector, $transform_state, $_bag, $is_starting_point, $appProp);
		if ($is_starting_point && ($appProp === 'Companies') && (!($this->_tsx & \QModel::TransformDelete)))
		{
			if ($this->isNew() && ($this->Id ?? null))
			{
				# ensure accessible by
				$already_in = \QQuery("Companies.{Accessible_By.Id WHERE Id=? AND Accessible_By.Id=? GROUP BY Id}", [$this->Id, $this->Id])->Companies;
				if (!isset($already_in[0]->Id)) {
					$obj = new $this;
					$obj->setId($this->Id);
					$obj->setAccessible_By(new \QModelArray());
					$obj->Accessible_By[] = $obj;
					$obj->db_save('Accessible_By.Id');
				}
			}
		}
		return $ret;
	}
	
	public static function resync_access(array $companies_ids = null, string $app_property = 'Companies')
	{
		$debug = true;
		if ($debug) {
			echo "<pre>\n";
		}
		
		$from_id = 0;
		$limit = 200;
		$t_zero = microtime(true);
		$loops_count = 0;
		
		# Accessible_By
		# Has_Access_To
		
		$query = "{$app_property}.{Name,Accessible_By.Id
					
					WHERE 
						Id > ?
						".($companies_ids ? " AND (Id IN (?) OR Accessible_By.Id IN (?))" : "")."
						
					GROUP BY Id 
					ORDER BY Id ASC
					LIMIT {$limit}}";
		$binds = [$from_id];
		if ($companies_ids) {
			$binds[] = $companies_ids;
			$binds[] = $companies_ids;
		}
		
		do
		{
			$binds[0] = $from_id;
			$data = \QQuery($query, $binds);
			
			$changed = new \QModelArray();
			
			foreach ($data->{$app_property} as $company) {

				$should_have_access = [];
			
				if ($company->Id > $from_id) {
					$from_id = $company->Id;
				}
				
				if ($debug) {
					echo "#{$company->Id} | {$company->Name}\n";
				}
				
				$should_have_access[$company->Id] = $company->Id;
				foreach ($company->Accessible_By ?? [] as $acc_by) {
					$should_have_access[$acc_by->Id] = $acc_by->Id;
				}
				
				$changed_access = new \QModelArray();
				$changed_access_pos = 0;
				if (!$company->Accessible_By) {
					$company->setAccessible_By(new \QModelArray());
				}
				else {
					foreach ($company->Accessible_By as $acc_by) {
						if ($should_have_access[$acc_by->Id] ?? false) {
							unset($should_have_access[$acc_by->Id]);
						}
						else {
							$changed_access[$changed_access_pos] = $acc_by;
							$changed_access->setTransformState(\QModel::TransformDelete, $changed_access_pos);
							$changed_access_pos++;
						}
					}
				}
				foreach ($should_have_access as $company_id) {
					$new_access = new $company;
					$new_access->setId($company_id);
					$changed_access[$changed_access_pos++] = $company;
				}
				
				if ($changed_access_pos > 0) {
					$changed[] = ($ch_prop = new $company);
					$ch_prop->setId($company->Id);
					$ch_prop->setAccessible_By($changed_access);
				}
			}
			
			if (q_reset($changed)) {

				$app = \QApp::NewData();
				$app->{"set{$app_property}"}($changed);
				
				$t0 = microtime(true);
				$app->db_save("{$app_property}.Accessible_By.Id");
				$t1 = microtime(true);
				# qvar_dump($t1 - $t0, $from_id, q_count($data->Properties), q_count($changed));
			}
			else {
				# qvar_dump("NO CHANGE!", $from_id, q_count($data->Properties), q_count($changed));
			}
			
			$loops_count++;
		}
		while (q_count($data->{$app_property}) >= $limit);
		
		if ($debug) {
			qvar_dump((memory_get_peak_usage()/1024/1024)." MB", microtime(true) - $t_zero, $loops_count);
			echo "\n\n<hr/>DONE !!!</pre>\n";
		}
	}
}

