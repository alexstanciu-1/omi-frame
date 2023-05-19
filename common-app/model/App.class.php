<?php

namespace Omi;

/**
 * @storage.table $App
 *
 * @class.name App
 */
abstract class App_mods_model_ extends \QModel
{
	public static $FromAlias;
	public static $_USE_SECURITY_FILTERS = false;
	public static $_USE_FILTER_BY_OWNERSHIP = true;
	public static $_SYNC_ITEMS_ON_PROCESS = false;
	
	/**
	 * @var Identity[]
	 */
	protected $Identities;
	/**
	 * @var Address[]
	 */
	protected $Addresses;
	/**
	 * @storage.views MyAccountFullResponse,MyAccount,Logins,User_Mail_Sender
	 * 
	 * @var User[]
	 */
	protected $Users;
	/**
	 * @var FailedLogin[]
	 */
	protected $FailedLogins;
	/**
	 * @var LoginLog[]
	 */
	protected $LoginsLog;
	/**
	 * @var Request_Log[]
	 */
	protected $Request_Logs;
	
	/**
	 * @var Country[]
	 */
	protected $Countries;
	/**
	 * @var County[]
	 */
	protected $Counties;
	/**
	 * @var City[]
	 */
	protected $Cities;	
	
	/**
	 * @var \Omi\Person[]
	 */
	protected $Contacts;
	/**
	 * @var \Omi\Language[]
	 */
	protected $Languages_Spoken;
	/**
	 * @var \Omi\Language[]
	 */
	protected $Languages;
	/**
	 * @storage.views Property_Owners,Channels,Company_Mail_Sender
	 * 
	 * @var \Omi\Comm\Company[]
	 */
	protected $Companies;
	/**
	 * @storage.views UserCreateAccount
	 * 
	 * @var \Omi\Comm\Registration_Request[]
	 */
	protected $Registration_Requests;
	
	/**
	 * @storage.views Age_Intervals_App
	 * 
	 * @storage.none
	 * 
	 * @var App
	 */
	protected $App;

	/**
	 * @param string $viewTag
	 */
	public static function GetListBinds($viewTag)
	{
		$ret = [];
	
		switch ($viewTag)
		{
			default :
			{
				$ret = [];
				break;
			}
		}
		
		return $ret;
	}
	
	/**
	 * @api.enable
	 * 
	 * @param string $viewTag
	 */
	public static function GetListDefaultBinds($viewTag)
	{
		$ret = null;
		switch ($viewTag)
		{
			case "Orders":
			{
				$ret = [
					"OBY_Date" => "DESC"
				];
				break;
			}
			case "Users":
			{
				$ret = [
					"OBY_Id" => "DESC"
				];
				break;
			}
			case "Registration_Requests":
			{
				$ret = [
					"OBY_Id" => "DESC"
				];
				break;
			}
			default :
			{
				$ret = [];
				break;
			}
		}
		
		return $ret;
	}
    
	/**
	 * Returns accepted filters for a view
     * 
	 * @param type $viewTag
	 */
	public static function GetAcceptedFilters($viewTag = null)
	{
		switch ($viewTag)
		{
			case "FailedLogins":
			{
				$ret = ['Username', 'Ip', 'Ban'];
				break;
			}
			case "Orders":
			{
				$ret = ['Buyer', 'Date', 'Reference', 'Channel'];
				break;
			}
			case "Users":
			{
				$ret = ['Username'];
				break;
			}
			case "Services":
			{
				$ret = ["Owner"];
				break;
			}
			default :
			{
				$ret = null;
				break;
			}			
		}
		
		return $ret;
	}
	
	/**
	 * Returns the entity that will be used when listing views are generated
	 * @param string $viewTag
	 */
	public static function GetEntityForGenerateList($viewTag = null)
	{
		$ret = null;
		switch ($viewTag)
		{
			case "FailedLogins":
			{
				$ret = [
					"LastTry" => [],
					"Ip" => [],
					"Username" => [],
					"Ban" => [],
					"Count" => [],
				];
                break;
			}
			case "Users":
			{
				$ret = qParseEntity('Username,Type,Person,Active,Owner');
                break;
			}
			case "Languages":
			{
				$ret = qParseEntity('Code,Name');
                break;
			}
			case "MyAccount":
			{
				$ret = qParseEntity('Person');
                break;
			}
            case "Addresses":
			{
				$ret = qParseEntity("StreetNumber,Street,PostCode,City,Country");
				break;
			}
			case "Languages_Spoken":
			{
				$ret = qParseEntity("Name");
				break;
			}
			case "Offer_Categories":
			{
				$ret = qParseEntity("Name");
				break;
			}
			case "Offers":
			case "Services":
			{
				$ret = qParseEntity("Name, Owner");
				break;
			}
			
			case "Services_Discount":
			{
				$ret = qParseEntity("Name, Owner");
				break;
			}
			
			case "Services_Calendar":
			{
				$ret = qParseEntity("Name");
				break;
			}
			
			case 'Special_Deals':
			{
				$ret = qParseEntity("Type,Date_Start,Date_End,Property");
				break;
			}
			case "Countries":
			{
				$ret = ["Code" => [], "Name" => []];
				break;
			}
			case "Counties":
			{
				$ret = ["Country" => [], "Code" => [], "Name" => []];
				break;
			}
			case "Cities":
			{
				$ret = ["Country" => [], "County" => [], "Code" => [], "Name" => []];
				break;
			}
			
			case 'MyAccount':
			{
				$ret = qParseEntity('Name, Username, Email, Type, BackendAccess, Active');
				break;
			}
			
			case 'Companies':
			{
				$ret = qParseEntity('Name, Reg_No, VAT_No, VAT_Payer, Address.{StreetNumber,Street,PostCode,City,Country}');
				break;
			}
			
			case 'Registration_Requests':
			{
				$ret = qParseEntity(
					'Company.{'
						. 'Name, Reg_No, VAT_No, Address'
					. '}, '
					. 'User.{'
						. 'Email, '
						. 'Active, '
						. 'Confirmed_Activation, '
						. 'Person.{'
							. 'Firstname, '
							. 'Name'
						. '}'
					. '}, '
					. 'IP, '
					. 'Date'
				);
				break;
			}
			
            case 'Orders':
            {
                $ret = qParseEntity('
					Date,Reference, 
					Buyer.{
						Firstname, 
						Name, 
						Phone, 
						Email,
						IdentityCardNumber,
						Address.{
							PostCode,
							Building,
							Street,
							StreetNumber,
							City.Name,
							Country.Code
						}
					},
					Buyer_Company.{
						Name,
						Code,
						Reg_No,
						VAT_No,
						Address.{
							PostCode,
							Building,
							Street,
							StreetNumber,
							City.Name,
							Country.Code
						}
					},
					Items.{
						Caption,
						Offer.Code,
						Quantity,
						Total_Price,
					},
					Total_Price, 
					Currency_Code, 
					Status,
				');
                break;
            }
			
			// DEFAULT
			default :
			{
				$ret = [];
				break;
			}
		}
		
		// return properties
		return $ret;
	}
	
	/**
	 * Returns the entity that will be used when form views are generated
	 * @param string $viewTag
	 */
	public static function GetEntityForGenerateForm($viewTag = null)
	{
		$ret = null;
		
		switch ($viewTag)
		{
			case "Request_Logs":
			{
				$ret = qParseEntity("Date,Method,IP_v4,Is_Ajax,Is_Fast_Call,Request_URI,User_Agent,Cookies,HTTP_GET,HTTP_POST,HTTP_FILES");
                break;
			}
			case "FailedLogins":
			{
				$ret = [
					"LastTry" => [],
					"Ip" => [],
					"Username" => [],
					"Ban" => [],
					"Count" => [],
				];
                break;
			}
			case "Languages":
			{
				$ret = qParseEntity('Code,Name');
                break;
			}
			case "Store_Locations":
			{
				$ret = [
					"Addresses" => ["City" => ["Name" => []]],
					"Properties_Indexes" => [
						"Property" => ["Name" => []],
						"Index" => [],
					]
				];
                break;
			}
			case "Properties_Addresses":
			{
				$ret = [
					"Name" => [],
					"Address" => [],
					"Store_Locations" => ["Addresses" => ["City" => ["Name" => []]]],
				];
                break;
			}
			case "Users":
			{
				$ret = qParseEntity(
					'Username, '
					. 'Email, '
					. 'Password, '
					. 'Type, '
					. 'Person.{'
						. 'Title, '
						. 'Firstname, '
						. 'Name, '
						. 'Email, '
						. 'Phone'
					. '}, '
					// . 'UI_Language, '
					. 'Active, '
					. 'Owner, '
					. 'Api_Key,'
					. 'Mail_Sender.{'
						. 'Host, '
						. 'Port, '
						. 'Username, '
						. 'Password, '
						. 'Email, '
						. 'Encryption, '
						. 'FromAlias, '
						. 'ReplyTo'
					. '}'
				);
                break;
			}
			case 'User_Mail_Sender':
			{
				$ret = qParseEntity(
					'Mail_Sender.{'
						. 'Host, '
						. 'Port, '
						. 'Username, '
						. 'Password, '
						// . 'Email, '
						. 'Encryption, '
						. 'FromAlias, '
						. 'Connection_Active, '
						. 'Email_Header_Text, '
						. 'Email_Footer_Text'
						// . 'ReplyTo'
					. '}, '
					. 'Username');
				break;
			}			
			case "Age_Intervals_App":
			{
				$ret = qParseEntity("Age_Intervals.{Name, To, Active, Property}");
				break;
			}
			
            case "Addresses":
			{
				$ret = qParseEntity("StreetNumber,Street,PostCode,City,Country, Latitude, Longitude");
				break;
			}
			case "Languages_Spoken":
			{
				$ret = qParseEntity("Name");
				break;
			}
			case "Cache_Views":
			{
				$ret = qParseEntity("Id,Name");
				break;
			}
			case "List_Offers":
			{
				$ret = qParseEntity("Search.{City, Cities,Check_In_From,Check_In_To,Nights_From,Nights_To,Properties,Price_From,Price_To,Adults,Children_Ages,Price_Mode,Stars_From}");
				break;
			}
			case "Checkout_Orders":
			{
				$ret = qParseEntity(""
					. "Offers.{"
						. "Property, "
						. "Property_Room, "
						. "Rate_Plan, "
						. "Meal, "
						. "Total_Price, "
						. "Comission, "
						. "Currency_Code, "
						. "Checkin_Date, "
						. "NIghts, "
						. "Occupants.{"
							. "Gender, "
							. "Firstname, "
							. "Name, "
							. "BirthDate"
						. "}"
					. "}, "
					. "Buyer.{"
						. "Firstname, "
						. "Name, "
						. "Phone, "
						. "Email"
					. "}"
				. "");
				break;
			}
			case "Restrictions":
			{
				$ret = qParseEntity("Name,Check_In_Locked,Check_Out_Locked,Min_Length_Of_Stay,Min_Relative_Length_Of_Stay,Min_Length_Of_Stay_From_Arrival,Max_Length_Of_Stay_From_Arrival,"
					. "Min_Advance_Reservation, "
					. "Max_Advance_Reservation, "
					. "Owner");
				break;
			}
			
			case "Payment_Policies":
			{
				$ret = qParseEntity(""
					. "Name, "
					. "Fee_Mode, "
					. "Owner, "
					. "Items.{"
						. "Date_Type, "
						. "Days_Before_Checkin, "
						. "Before_Date, "
						. "Fee_Type, "
						. "Fee_Percent, "
						. "Fee_Value"
					. "}");
				break;
			}
			
			case "Cancellation_Policies":
			{
				$ret = qParseEntity(""
					. "Name,"
					. "Owner,"
					. "Fee_Mode, "
					. "Items.{"
						. "Date_Type, "
						. "Days_Before_Checkin, "
						. "Before_Date, "
						. "Fee_Type, "
						. "Fee_Percent, "
						. "Fee_Value"
					. "}");
				break;
			}
			
			case "Room_Occupancies":
			{
				$ret = qParseEntity(""
					. "Name, "
					. "Owner, "
					. "Persons_Max, "
					. "Infant_Limits.{"
						. "Default_From, "
						. "Default_To,"
						# . "Min, "
						. "Max, "
					. "}, "
					. "Toddler_Limits.{"
						. "Default_From, "
						. "Default_To,"
						# . "Min, "
						. "Max, "
					. "}, "
					. "Child_Limits.{"
						. "Default_From, "
						. "Default_To,"
						# . "Min, "
						. "Max, "
					. "}, "
					. "Adolescent_Limits.{"
						. "Default_From, "
						. "Default_To,"
						# . "Min, "
						. "Max, "
					. "}, "
					. "Adult_Limits.{"
						. "Default_From, "
						. "Default_To,"
						. "Min, "
						. "Max, "
					. "}, "
					. "Rooms.{Name, Property.Name},"
				. "");
				break;
			}
			case "Offer_Categories":
			{
				$ret = qParseEntity("Name");
				break;
			}
			case "Offers":
			case "Services":
			{
				$ret = qParseEntity(""
					. "Name, "
					. "Category, "
					. "TFH_Type, "
					. "TFH_Meal_Type, "
					. "TFH_Bed_Type, "
					. "From_Age, "
					. "To_Age, "
					. "Max_Quantity, "
					. "TFH_Price_Mode, "
					. "TFH_PP_Mode_Property, "
					. "TFH_PP_Mode_Property_Rate, "
					. "TFH_PP_Mode_Property_Room, "
					. "TFH_PP_Mode_Property_Room_Rate, "
					. "From_Age, "
					. "To_Age, "
					. "Owner,"
					. "Price_Profile_Items.{"
						. "Price_Profile, "
						. "Price, "
						. "Active,"
						. "TFH_Property.Currency"
					. "},"
					. "Description_HTML"
				. "");
				break;
			}
			
			case "Services_Discount":
			{
				$ret = qParseEntity("Name, Discounts.{Discount_Type, Percent, TFH_Fixed, TFH_From_Age, TFH_To_Age, TFH_Age_Interval}");
				break;
			}
			
			case "Services_Calendar":
			{
				$ret = qParseEntity("Name, Services, Dates.{Date, Status},Owner");
				break;
			}
			
			case "Properties_Rooms":
			{
				$ret = qParseEntity("
					Name, 
					Property, 
					Standard_Type,
					Occupancy,
					Count, 
					Content_Description_HTML, 
					Size, 
					Bathroom_Count,
					Bathroom_Private,
					Bathroom_In_Room,
					Smoking_Policy,
					Room_Location,
					Property_Room_Beds.{
						Bed_Type,
						Number_Of_Beds
					},
					Extra_Beds_Limits.{
						Max_Cribs, 
						Max_Child_Beds, 
						Max_Adult_Beds, 
						Max_Total
					}, 
					Extra_Beds_Limits_Per_Room.{
						Max_Cribs, 
						Max_Child_Beds, 
						Max_Adult_Beds, 
						Max_Total, 
						Room
					},
                    Property_Room_Facil_Top.{
                        Air_Conditioning,
                        Balcony,
                        Bath,
                        View,
                        Flat_Screen_TV,
                        Electric_Kettle,
                        Soundproofing,
                        Toilet_Paper,
                        Towels,
                        WiFi
                    },
                    Property_Room_Facil_Other.{
                        Free_Toiletries,
                        Toilet,
                        Bath_or_Shower,
                        Towels,
                        Bathrobe,
                        Slippers,
                        Bed_Sheets,
                        Socket_by_the_Bed,
                        Office_Desk,
                        TV,
                        Phone,
                        Ironing_Facilities,
                        Satellite_Channels,
                        Tea_Coffee_Maker,
                        Iron,
                        Pay_Channels,
                        Heating,
                        Hairdryer,
                        Fan,
                        Kettle_Cup,
                        Cable_Channels,
                        Wake_up_Service,
                        Safe_Box,
                        Wardrobe_or_Closet,
                        Upper_Floors_Accessible_by_Elevator,
                        Clothes_Holder,
                        Toilet_Paper,
                        Hand_Sanitizer,
                        Air_Conditioning,
                        Radio,
                        Microwave_Oven,
                        Kitchen_Utensils,
                        Kitchenette,
                        IPod_Dock,
                        Relaxation_Area
                    }");
				break;
            }
			case "Properties_Rooms_Extra_Beds":
			{
				$ret = qParseEntity("
					Name, 
					Extra_Beds_Limits.{
						Max_Cribs, 
						Max_Child_Beds, 
						Max_Adult_Beds, 
						Max_Total
					}");
				break;
            }
			case 'Property_Room_Facilities':
            case "Properties_Rooms_Facilities":
            {
                $ret = qParseEntity("Name, Property_Room_Facil_Top.{
                        Air_Conditioning,
                        Balcony,
                        Bath,
                        View,
                        Flat_Screen_TV,
                        Electric_Kettle,
                        Soundproofing,
                        Toilet_Paper,
                        Towels,
                        WiFi
                    },
                    Property_Room_Facil_Other.{
                        Free_Toiletries,
                        Toilet,
                        Bath_or_Shower,
                        Towels,
                        Bathrobe,
                        Slippers,
                        Bed_Sheets,
                        Socket_by_the_Bed,
                        Office_Desk,
                        TV,
                        Phone,
                        Ironing_Facilities,
                        Satellite_Channels,
                        Tea_Coffee_Maker,
                        Iron,
                        Pay_Channels,
                        Heating,
                        Hairdryer,
                        Fan,
                        Kettle_Cup,
                        Cable_Channels,
                        Wake_up_Service,
                        Safe_Box,
                        Wardrobe_or_Closet,
                        Upper_Floors_Accessible_by_Elevator,
                        Clothes_Holder,
                        Toilet_Paper,
                        Hand_Sanitizer,
                        Air_Conditioning,
                        Radio,
                        Microwave_Oven,
                        Kitchen_Utensils,
                        Kitchenette,
                        IPod_Dock,
                        Relaxation_Area
                    }");
                break;
            }
			
			case 'Room_Occupancy_Details':
			{
				$ret = qParseEntity(""
					. "Name, "
					. "Owner, "
					. "Persons_Max, "
					. "Infant_Limits.{"
						. "Default_From, "
						. "Default_To, "
						# . "Min, "
						. "Max"
					. "}, "
					. "Toddler_Limits.{"
						. "Default_From, "
						. "Default_To, "
						# . "Min, "
						. "Max"
					. "}, "
					. "Child_Limits.{"
						. "Default_From, "
						. "Default_To, "
						# . "Min, "
						. "Max"
					. "}, "
					. "Adolescent_Limits.{"
						. "Default_From, "
						. "Default_To, "
						# . "Min, "
						. "Max"
					. "}, "
					. "Adult_Limits.{"
						. "Default_From, "
						. "Default_To, "
						. "Min, "
						. "Max"
					. "}, "
					. "Occupancy_Pricing.{"
						# . "Type, "
						. "Infant_Count,Toddler_Count,Child_Count,Adolescent_Count,Adult_Count, Property, "
						. "Percent, "
						. "Fixed, "
						. "Extra_Bed, "
						. "Rate_Plan.{"
							. "Name"
						. "}"
					. "},"
					. "Rooms.{"
						. "Name"
					. "}");
				break;
			}
			case 'Room_Occupancy_Details_Rate':
			{
				# Infant_Count,Toddler_Count,Child_Count,Adolescent_Count,Adult_Count
				$ret = qParseEntity(""
					. "Name, "
					. "Occupancy_Pricing.{"
						. "Rate_Plan, "
						. "Infant_Count,Toddler_Count,Child_Count,Adolescent_Count,Adult_Count,"
						. "Percent, "
						. "Fixed, "
					. "}");
				break;
			}
			
			case 'Room_Occupancy_Pricing':
			{
				$ret = qParseEntity("Type, Rate_Plan");
				break;
			}
			
			case 'Properties_Rooms_Occupancy_Enforcements': 
			{
				$ret = qParseEntity(""
					. "Name,"
					. "Occupancy.{Name},"
					. "Occupancy_Enforcement.{"
						. "Offer_Enforcement_Items.{"
							. "Condition,"
							. "Formula,"
							. "Quantity,"
							. "Discount,"
							. "Action"
						. "}"
					. "}");
				break;
			}
			
			case 'Properties_Rooms_Beds_Enforcements': 
			{
				$ret = qParseEntity(""
					. "Name,"
					. "Beds_Enforcement.{"
						. "Offer_Enforcement_Items.{"
							. "Condition,"
							. "Formula,"
							. "Quantity,"
							. "Discount,"
							. "Action"
						. "}"
					. "}");
				break;
			}
			
			case "Room_Set_Requests":
			{
				# Rooms_Count,Rooms_Status,
				$ret = qParseEntity("
					Room, 
					Season, 
					Date_Start, 
					Date_End,
					Weekday_Mon,
					Weekday_Tue,
					Weekday_Wed,
					Weekday_Thu,
					Weekday_Fri,
					Weekday_Sat,
					Weekday_Sun,
					Rooms_Count,
					Rooms_Status
				");
				break;
			}
			
			case "Rate_Set_Requests":
			{
				$ret = qParseEntity("
					Room,
					Rate_Plan,
					Season,
					Date_Start,
					Date_End,
					Weekday_Mon,
					Weekday_Tue,
					Weekday_Wed,
					Weekday_Thu,
					Weekday_Fri,
					Weekday_Sat,
					Weekday_Sun,
					Price,
					Rooms_Count,
					Rate_Status,
					Do_Delete,
					Restrictions.{
						Min_Length_Of_Stay,
						Min_Length_Of_Stay_From_Arrival,
						Max_Length_Of_Stay_From_Arrival,
						Min_Advance_Reservation,
						Max_Advance_Reservation,
						Check_In_Locked,
						Check_Out_Locked,
						Min_Relative_Length_Of_Stay
					}
				");
				break;
			}
			
			case "Rate_Plans":
			{
				$ret = ["Name" => [], "Active" => [], "Meal_Option" => [], "Meal_Service" => [], "Occupancy" => [], "Restrictions" => [], 
					"Cancellation_Policy" => [], "Payment_Policy" => [], "Currency" => [],
					"Owner" => [],
					"Extra_Services" => ['Offer' => [], 'Quantity' => []],
					'Accessible_To' => ['Property' => ['Name' => []], 'Channel' => ['Name' => []], 'Enabled' => [], 
										'Enable_All_Channels' => []]];
				break;
			}
			case "Property_Age_Intervals": 
			{
				$ret = qParseEntity('Name, Age_Intervals.{Name, To, Active}');
				break;
			}
			case 'Property_Facilities_Channel':
            case 'Properties_Facilities':
            {
                $ret = qParseEntity("Name,Property_Facil_Top.{
                        Swimming_Pool,
                        Air_Conditioning,
                        Non_Smoking_Rooms,
                        Sauna,
                        Hot_tub_jacuzzi,
                        Terrace,
                        Bar,
                        Restaurant,
                        Garden,
                        WiFi
                    },
                    Property_Facil_Activities.{
                        Tennis_Equipment,
                        Badminton_Equipment,
                        Beach,
                        Billiards,
                        Table_Tennis,
                        Darts,
                        Squash,
                        Bowling,
                        Mini_Golf,
                        Golf_Course,
                        Water_Park,
                        Water_Sport_Facilities,
                        Windsurfing,
                        Diving,
                        Snorkelling,
                        Canoeing,
                        Fishing,
                        Horse_Riding,
                        Cycling,
                        Hiking,
                        Skiing,
                        Archery,
                        Aerobics,
                        Tennis_Court
                    },
                    Property_Facil_Food_Drink.{
                        Kid_Meals,
                        Kid_friendly_Buffet,
                        On_site_Coffee_House,
                        Restaurant,
                        Snack_Bar,
                        BBQ_Facilities,
                        Special_Diet_Menus,
                        Room_Service,
                        Breakfast_In_The_Room
                    },
                    Property_Facil_Pool_And_Wellness.{
                        Water_Slide,
                        Pool_Beach_Towels,
                        Sun_Loungers_or_Beach_Chairs,
                        Sun_Umbrellas,
                        Beauty_Services,
                        Spa_Facilities,
                        Spa_Lounge_Relaxation_Area,
                        Spa_Wellness_Packages,
                        Spa_and_Wellness_Centre,
                        Fitness,
                        Fitness_Classes,
                        Personal_Trainer,
                        Yoga_Classes,
                        Kids_Pool,
                        Massage
                    },
                    Property_Facil_Transport.{
                        Secured_Parking,
                        Street_Parking,
                        Parking_Garage,
                        Valet_Parking,
                        Airport_Shuttle,
                        Car_Hire
                    },
                    Property_Facil_Reception_Services.{
                        Concierge_Service,
                        Facil_24_Hour_Front_Desk,
                        Private_Check_in_Check_out,
                        Express_Check_in_Check_out,
                        Tour_Desk,
                        Currency_Exchange,
                        ATM_Cash_Machine_on_Site,
                        Luggage_Storage,
                        Safety_Deposit_Box
                    },
                    Property_Facil_Common_Areas.{
                        Outdoor_Furniture,
                        Outdoor_Fireplace,
                        Indoor_Fireplace,
                        Picnic_Area,
                        Sun_Terrace,
                        Shared_Kitchen,
                        Shared_Lounge_TV_Area,
                        Games_Room,
                        Library
                    },
                    Property_Facil_Entertainment_And_Family_Services.{
                        Board_Games_Puzzles,
                        Books_DVDs_Music_for_Children,
                        Indoor_Play_Area,
                        Children_Television_Networks,
                        Kids_Outdoor_Play_Equipment,
                        Baby_Safety_Gates,
                        Strollers,
                        Evening_Entertainment,
                        Nightclub_DJ,
                        Casino,
                        Karaoke,
                        Entertainment_Staff,
                        Kids_Club,
                        Childrens_Playground,
                        Babysitting_Child_Services
                    },
                    Property_Facil_Cleaning_Services.{
                        Dry_Cleaning,
                        Ironing_Service,
                        Laundry,
                        Daily_Housekeeping,
                        Shoeshine,
                        Trouser_Press
                    },
                    Property_Facil_Business_Facilities.{
                        Meeting_Banquet_Facilities,
                        Business_Centre
                    },
                    Property_Facil_General.{
                        Designated_Smoking_Area,
                        Non_Smoking_Throughout,
                        Allergy_Free_Room,
                        Adult_Only,
                        Key_Access,
                        Key_Card_Access,
                        Digital_Key_Access,
                        Facilities_For_Disabled_Guests,
                        Soundproof_Rooms,
                        Lift,
                        VIP_Room_Facilities,
                        Facil_24_Hour_Security,
                        CCTV_in_Common_Areas,
                        CCTV_Outside_Property,
                        Security_Alarm,
                        Smoke_Alarms
                    }");
                break;
            }
			case 'Property_Media_Channel':
            case "Properties_Media": 
            {
                $ret = qParseEntity('Name, Content_Image.{Path, Alt}, Content_Images.{Path, Alt, Order}, Content_Video_Embeds.{Name,Path, Order}');
                break;
            }
			case "Property_Media_Channel":
			{
				$ret = qParseEntity('Name, Content_Image.{Path, Alt}, Content_Images.{Path, Alt, Order}, Content_Video_Embeds.{Name,Path, Order}');
				break;
			}
			
			case 'Property_Owner_Channel': 
			{
				$ret = qParseEntity(
					'Name, '
					. 'Owner.{'
						. 'Name, '
						. 'Reg_No, '
						. 'VAT_No, '
						. 'VAT_Payer, '
						. 'Address.{'
							. 'StreetNumber, '
							. 'Street, '
							. 'PostCode, '
							. 'City, '
							. 'Country'
						. '}, '
						. 'Bank_Accounts.{'
							. 'Bank_Name, '
							. 'IBAN, '
							. 'Currency'
						. '}, '
						. 'Contacts.{'
							. 'Title, '
							. 'Name, '
							. 'Firstname, '
							. 'Email, '
							. 'Phone,'
							. 'Role,'
							. 'IsDefault'
						. '}, '
						. 'Contact_Emails_List.{'
							. 'Name, '
							. 'Department, '
							. 'IsDefault'
						. '}, '
						. 'Contact_Phones_List.{'
							. 'Name, '
							. 'Department, '
							. 'IsDefault'
						. '}, '
					. '}'
				);
				
				break;
			}
			
			case 'Property_Room_Media':
			case "Properties_Rooms_Media": 
            {
                $ret = qParseEntity('Name, Content_Images.{Path, Alt, Order}, Content_Video_Embeds.{Name,Path, Order}');
                break;
            }
			case "Properties":
			{
				$ret = qParseEntity(
					"Name, "
					. "Owner, "
					. "Address.{"
						. "StreetNumber, "
						. "Street, "
						. "PostCode, "
						. "City, "
						. "Country, "
						. "Longitude, "
						. "Latitude"
					. "},"
					. "Currency, "
					. "Comission, "
					. "API_Managed, "
					. "Id, "
					. "Stars, "
					. "Type, "
					. "Active, "
					. "Classification_Certificate, "
					. "Classification_Certificate_Number, "
					. "Classification_Certificate_Issued_Date, "
					. "Check_In_Time, "
					. "Check_In_Time_Hour, "
					. "Check_In_Time_Minutes, "
					. "Check_Out_Time, "
					. "Check_Out_Time_Hour, "
					. "Check_Out_Time_Minutes, "
					. "Building_Info_Total_Rooms,"
					. "Building_Info_Floors_Count,Languages_Spoken,Content_Description_HTML,"
					. "Content_Images,Content_Video_Embeds,"
						. "	Property_Facil_Top.{Swimming_Pool,Air_Conditioning,
													Non_Smoking_Rooms,
													Sauna,
													Hot_tub_jacuzzi,
													Terrace,
													Bar,
													Restaurant,
													Garden,
													WiFi},
							Property_Facil_Activities.{Tennis_Equipment,
										Badminton_Equipment,
										Beach,
										Billiards,
										Table_Tennis,
										Darts,
										Squash,
										Bowling,
										Mini_Golf,
										Golf_Course,
										Water_Park,
										Water_Sport_Facilities,
										Windsurfing,
										Diving,
										Snorkelling,
										Canoeing,
										Fishing,
										Horse_Riding,
										Cycling,
										Hiking,
										Skiing,
										Archery,
										Aerobics,
										Tennis_Court}");
				break;
			}
			
			case "Property_Channel": 
			{
				$ret = qParseEntity(
					"Name, "
					. "Owner, "
					. "Address.{"
						. "StreetNumber, "
						. "Street, "
						. "PostCode, "
						. "City, "
						. "Country, "
						. "Longitude, "
						. "Latitude"
					. "},"
					. "Stars, "
					. "Type, "
					. "Comission,"
					. "Currency, "
					. "Classification_Certificate, "
					. "Classification_Certificate_Number, "
					. "Classification_Certificate_Issued_Date, "
					. "Check_In_Time, "
					. "Check_In_Time_Hour, "
					. "Check_In_Time_Minutes, "
					. "Check_Out_Time, "
					. "Check_Out_Time_Hour, "
					. "Check_Out_Time_Minutes, "
					. "Building_Info_Total_Rooms,"
					. "Building_Info_Floors_Count,Languages_Spoken,Content_Description_HTML,"
					. "Content_Images, "
					. "Content_Video_Embeds, "
					. "Property_Facil_Top.{"
						. "Swimming_Pool,
						Air_Conditioning,
						Non_Smoking_Rooms,
						Sauna,
						Hot_tub_jacuzzi,
						Terrace,
						Bar,
						Restaurant,
						Garden,
						WiFi
					},
							Property_Facil_Activities.{
								Tennis_Equipment,
								Badminton_Equipment,
								Beach,
								Billiards,
								Table_Tennis,
								Darts,
								Squash,
								Bowling,
								Mini_Golf,
								Golf_Course,
								Water_Park,
								Water_Sport_Facilities,
								Windsurfing,
								Diving,
								Snorkelling,
								Canoeing,
								Fishing,
								Horse_Riding,
								Cycling,
								Hiking,
								Skiing,
								Archery,
								Aerobics,
								Tennis_Court
							}");
				break;
			}
			
			case "Property_Service_Channel":
			{
				$ret = qParseEntity('Name,Services.{Name}');
				break;
			}
			
			case "Property_Room_Channel":
			{
				$ret = qParseEntity('Name, Rooms.{Name, Standard_Type, Count, Occupancy.{Name}}');
				break;
			}
			
			case "Properties_Access":
			{
				$ret = qParseEntity("Name,Accessible_To");
				break;
			}
			case "Countries":
			{
				$ret = ["Code" => [], "Name" => []];
				break;
			}
			case "Counties":
			{
				$ret = ["Country" => [],"Code" => [], "Name" => []];
				break;
			}
			case "Cities":
			{
				$ret = ["Country" => [], "County" => [], "Code" => [], "Name" => [], "TFH_Block_Search" => [],
								"TFH_Search_City" => [],];
				break;
			}
			case "Test":
			{
				$ret = [];
				$ret['TestProp'] = [];
				break;
			}
			
			case 'MyAccount':
			{
				$ret = qParseEntity(
					'Username, '
					. 'Email, '
					. 'Password, '
					. 'Type, '
					. 'Person.{'
						. 'Title, '
						. 'Firstname, '
						. 'Name, '
						. 'Email, '
						. 'Phone'
					. '}, '
					// . 'UI_Language, '
					. 'Active,Owner, Api_Key');
				break;
			}
			
			case 'Companies':
			{
				$ret = qParseEntity('Name, '
					. 'Terms_Accepted, '
					. 'Terms_Accepted_IP, '
					. 'Terms_Accepted_Date, '
					. 'Reg_No, '
					. 'VAT_No, '
					. 'VAT_Payer, '
					. 'Logo, '
					. 'Address.{'
						. 'StreetNumber,Street,PostCode,City,Country'
					. '}, '
					. 'Bank_Accounts.{Bank_Name, IBAN, Currency}, '
					. 'Contacts.{'
						. 'Title, '
						. 'Name, '
						. 'Firstname, '
						. 'Email, '
						. 'Phone,'
						. 'Role,'
						. 'IsDefault'
					. '}, '
					. 'Contact_Emails_List.{'
						. 'Name, '
						. 'Department, '
						. 'IsDefault'
					. '}, '
					. 'Contact_Phones_List.{'
						. 'Name, '
						. 'Department, '
						. 'IsDefault'
					. '}, '
					. 'Accessible_By, '
					. 'Mail_Sender.{'
						. 'Host, '
						. 'Port, '
						. 'Username, '
						. 'Password, '
						. 'Email, '
						. 'Encryption, '
						. 'FromAlias, '
						. 'ReplyTo'
					. '}');
				break;
			}
			
			case 'Company_Mail_Sender':
			{
				$ret = qParseEntity(
					'Mail_Sender.{'
						. 'Host, '
						. 'Port, '
						. 'Username, '
						. 'Password, '
						// . 'Email, '
						. 'Encryption, '
						. 'FromAlias, '
						. 'Connection_Active, '
						. 'Email_Header_Text, '
						. 'Email_Footer_Text'
						// . 'ReplyTo'
					. '}, '
					. 'Name');
				break;
			}
			
			case 'UserCreateAccount':
			case 'Registration_Requests':
			{
				$ret = qParseEntity(
					'Company.{'
						. 'Name, '
						. 'Terms_Accepted, '
						. 'Terms_Accepted_IP, '
						. 'Terms_Accepted_Date, '
						. 'VAT_No, '
						. 'Reg_No, '
						. 'Address.{'
							. 'StreetNumber,Street,PostCode,City,Country'
						. '}, '
						. 'Is_Property_Owner, '
						. 'Is_Channel_Owner, '
					. '}, '
					. 'User.{'
						. 'Username, '
						. 'Email, '
						. 'Phone, '
						. 'Password, '
						. 'Type, '
						. 'Active,'
						. 'Confirmed_Activation, '
						. 'Owner, '
						. 'ActivationCode, '
						. 'Api_Key, '
						. 'Confirmed_Activation, '
						. 'Person.{'
							. 'Firstname, '
							. 'Name, '
							. 'Email, '
							. 'Phone'
						. '}'
					. '}, '
					. 'IP, '
					. 'Date'
				);
				
				break;
			}
			
			case 'Property_Owners':
			{
				$ret = qParseEntity('Name, '
					. 'Terms_Accepted, '
					. 'Terms_Accepted_IP, '
					. 'Terms_Accepted_Date, '
					. 'Reg_No, VAT_No, VAT_Payer, '
					. 'Address.{StreetNumber,Street,PostCode,City,Country}, '
					. 'Properties.Name, '
					. 'Bank_Accounts.{Bank_Name, IBAN, Currency}, '
					. 'Contacts.{'
						. 'Title, '
						. 'Name, '
						. 'Firstname, '
						. 'Email, '
						. 'Phone,'
						. 'Role,'
						. 'IsDefault'
					. '}, '
					. 'Contact_Emails_List.{'
						. 'Name, '
						. 'Department, '
						. 'IsDefault'
					. '}, '
					. 'Contact_Phones_List.{'
						. 'Name, '
						. 'Department, '
						. 'IsDefault'
					. '}, '
					. 'Accessible_By');
				break;
			}
			
			case 'Property_Contracts':
			{
				$ret = qParseEntity('Name, '
					. 'Channel_Contracts.{'
						. 'Channel.Name, '
						. 'Custom_Comission, '
						. 'Comission, '
						. 'Contract_Was_Signed, '
						. 'Signed_Contract, '
						. 'Property_Signed_Contract, '
						. 'Enable_Channel'
					. '}');
				break;
			}
			
			case 'Property_Room_To_Rates':
			{
				$ret = qParseEntity('Name, '
					. 'Rooms.{'
						. 'Name, '
						. 'Rate_Plans.Name'
					. '}');
				break;
			}
			
			case 'Property_View_Contract':
				{
				$ret = qParseEntity('Name, '
					. 'Contract.{'
						. 'Agreement_Type, '
						. 'File, Contract_Upload_Date, Contract_Upload_IP, Contract_Upload_User.Username, '
						. 'Contract_Presigned'
					. '}, '
					. 'Channel_Contracts.{'
						. 'Signed_Contract,'
						. 'Terms_Accepted_Date,'
						. 'Terms_Accepted_IP,'
						. 'Terms_Accepted_User.Username,'
						. 'Contract_Signed_Date,'
						. 'Contract_Signed_IP,'
						. 'Contract_Signed_User.Username, '
						. 'Property_Signed_Contract, '
						. 'Number_Int, '
						. 'Number'
					. '}');
				break;
			}
			case 'Contracts': 
			{
				$ret = qParseEntity('Name, '
					. 'Contract.{'
						. 'Agreement_Type, '
						. 'File, '
						. 'Contract_Presigned, '
						. 'Contract_Upload_Date, '
						. 'Contract_Upload_IP, '
						. 'Contract_Upload_User.Username'
					. '}');
				break;
			}
			
			case 'Channels':
			{
				$ret = qParseEntity('Name, '
					. 'Terms_Accepted, '
					. 'Terms_Accepted_IP, '
					. 'Terms_Accepted_Date, '
					. 'Agency_Name, '
					. 'Reg_No, VAT_No, VAT_Payer, '
					. 'Address.{'
						. 'StreetNumber, '
						. 'Street, '
						. 'PostCode, '
						. 'City, '
						. 'Country'
					. '}, '
					. 'Properties.Name, '
					. 'Bank_Accounts.{'
						. 'Bank_Name, '
						. 'IBAN, Currency'
					. '}, '
					. 'Contacts.{'
						. 'Title, '
						. 'Name, '
						. 'Firstname, '
						. 'Email, '
						. 'Phone,'
						. 'Role,'
						. 'IsDefault'
					. '}, '
					. 'Contact_Emails_List.{Name, Department, IsDefault}, '
					. 'Contact_Phones_List.{Name, Department, IsDefault}');
				break;
			}
			
			case 'Account_Configurations':
			{
				$ret = qParseEntity(
					'Owner, '
					. 'Set_Company_Data, '
					. 'Create_Property, '
					. 'Set_Age_Intervals, '
					. 'Create_Occupancy, '
					. 'Create_Room, '
					. 'Create_Meal_Services, '
					. 'Set_Payment_Policy, '
					. 'Set_Cancellation_Policy, '
					. 'Create_Rate_Plan, '
					. 'Add_Rate_Set_Request, '
					. 'Active'
				);
				break;
			}
			
            case 'Orders':
            {
                $ret = qParseEntity('
					Reference, 
					Total_Price, 
					Currency_Code, 
					Status, 
					Notes, 
					Date,
					Status_Change_Date,
					Last_Modified_Date,
					Property.{
						Comission, 
						Channel_Contracts.{
							Channel.Name, 
							Comission
						}
					},
					Channel, 
					Buyer.{
						Firstname, 
						Name, 
						Phone, 
						Email,
						IdentityCardNumber,
						Address.{
							PostCode,
							Building,
							Street,
							StreetNumber,
							City.Name,
							Country.Code
						}
					},
					Buyer_Company.{
						Name,
						Code,
						Reg_No,
						VAT_No,
						Address.{
							PostCode,
							Building,
							Street,
							StreetNumber,
							City.Name,
							Country.Code
						}
					},
					Items.{
						Caption,
						Offer.Code,
						Quantity,
						Total_Price,
						Config.{
							Property.{
								Name
							},
							Room.{
								Name
							},
							Occupants.{
								Gender,
								Type, 
								First_Name,
								Last_Name,
								Date_Of_Birth,
								Age_At_Checkin
							},
							Restrictions.{
								Check_In_Locked,
								Check_Out_Locked,
								Min_Length_Of_Stay,
								Min_Relative_Length_Of_Stay,
								Min_Length_Of_Stay_From_Arrival,
								Max_Length_Of_Stay_From_Arrival,
								Min_Advance_Reservation,
								Max_Advance_Reservation
							},
							Check_In,
							Nights,
							Meal_Option,
							Meal_Name,
							Payment_Policy,
							Cancellation_Policy
						}
					},
					Documents.File
				');
                break;
            }
			case "Price_Profiles":
			{
				$ret = qParseEntity("Name,
					Items.{
						Offer,
						Active,
						TFH_Property,
						TFH_Room,
						TFH_Rate,
						TFH_Mode,
						Price
					}");
				break;
			}
			case 'Price_Profile_Property':
			{
				$ret = qParseEntity("Name, Owner, Items.{Offer.Name, Price, TFH_Property.Currency, Active}");
				break;
			}
			
			case 'Price_Profile_Property_Room':
			{
				$ret = qParseEntity("Name, Owner, Items.{TFH_Room, Offer, Price, TFH_Property.Currency, Active}");
				break;
			}

			case 'Price_Profile_Property_Rate':
			{
				$ret = qParseEntity("Name, Owner, Items.{TFH_Rate, Offer, Price, TFH_Property.Currency, Active}");
				break;
			}
			
			case 'Price_Profile_Property_Room_Rate':
			{
				$ret = qParseEntity("Name, Owner, Items.{TFH_Room, TFH_Rate, Offer, Price, TFH_Property.Currency}");
				break;
			}
			
			case 'Age_Intervals':
			{
				$ret = qParseEntity("Name, From, To, Owner, Active, Property");
				break;
			}
			
			case 'Seasons': 
            {
                $ret = qParseEntity('Name, Owner, Season_Time.{From, To}');
                break;
            }
			
			case 'Special_Deals':
			{
				$ret = qParseEntity("Property, "
					. "Date_Start, "
					. "Date_End, "
					. "Type, "
					. "Owner, "
					. "Min_Days_Until_Checkin, "
					. "Max_Days_Until_Checkin, "
					. "Checkin_Restricted, "
					. "Checkin_From_Date, "
					. "Checkin_Until_Date, "
					. "Special_Offer.{"
						. "Discount_Type, "
						. "Percent,"
						. "Fixed,"
						. "Payment_Policy,"
						. "Cancellation_Policy"
					. "},"
					. "Available_On_All_Rooms,"
					. "Available_On_All_Rates,"
					. "TFH_Rooms.{"
						. "Name"
					. "},"
					. "TFH_Rate_Plans.{"
						. "Name"
					. "}");				
				break;
			}
            
			// DEFAULT
			default : 
			{
				$ret = [];
				break;
			}
		}

		return $ret;
	}
	
	
	
	/**
	 * Returns the entity that will be used when views are generated
	 * @param string $viewTag
	 */
	public static function GetListEntity($viewTag = null)
	{
		$ret = null;
		
		switch ($viewTag)
		{
			case 'Registration_Requests':
			{
				$ret = qParseEntity(
					'Company.{'
						. 'Name, Reg_No, VAT_No, Address'
					. '}, '
					. 'User.{'
						. 'Email, '
						. 'Active, '
						. 'Confirmed_Activation, '
						. 'Person.{'
							. 'Firstname, '
							. 'Name'
						. '}'
					. '}, '
					. 'IP, '
					. 'Date'
				);
				break;
			}
		
			case 'Orders':
            {
                $ret = qParseEntity('
					Buyer.{
						Firstname, 
						Name, 
						Phone, 
						Email,
						IdentityCardNumber,
						Address.{
							PostCode,
							Building,
							Street,
							StreetNumber,
							City.Name,
							Country.Code
						}
					}, 
					Items.{
						Caption,
						Offer.Code,
						Quantity,
						Total_Price,
						Config.{
							Property.{
								Name
							},
							Room.{
								Name
							},
							Occupants.{
								Gender,
								Type, 
								First_Name,
								Last_Name,
								Date_Of_Birth,
								Age_At_Checkin
							},
							Restrictions.{
								Check_In_Locked,
								Check_Out_Locked,
								Min_Length_Of_Stay,
								Min_Relative_Length_Of_Stay,
								Min_Length_Of_Stay_From_Arrival,
								Max_Length_Of_Stay_From_Arrival,
								Min_Advance_Reservation,
								Max_Advance_Reservation
							},
							Check_In,
							Nights,
							Meal_Option,
							Meal_Name,
						}
					},
					Reference, 
					Total_Price, 
					Currency_Code, 
					Status, 
					Channel.Name,
					Property.Name,
					Date
				');
                break;
            }
			case "Test":
			{
				$ret = qParseEntity("Prop");
				break;
			}
			default:
			{
				$ret = [];
			}
		}	
		return $ret;
	}

	/**
	 * @api.enable
	 * 
	 * Returns the entity that will be used when views are generated
	 * 
	 * @param string $viewTag
	 */
	public static function GetFormEntity($viewTag = null)
	{
		$ret = null;
		switch ($viewTag)
		{
			case 'Price_Profile_Property':
			case 'Price_Profile_Property_Room_Rate':
			case 'Price_Profile_Property_Rate':
			case 'Price_Profile_Property_Room':
			{
				$ret = static::GetEntityForGenerateForm($viewTag);
				$ret['Items']['TFH_Property']['Currency'] = [];
				$ret['Items']['TFH_Property']['Name'] = [];
				
				# $ret = qParseEntity("Name, Owner, Items.{TFH_Room, Offer, Price, TFH_Property.Currency}");
				break;
			}
			
			case "Test" :
			{
				$ret = [];
				break;
			}
			default:
			{
				$ret = [];
				break;
			}			
		}	
		return $ret;
	}
	
	/**
	 * @param string $viewTag
	 */
	public static function GetFormBinds($viewTag)
	{
		$ret = null;
		switch ($viewTag)
		{
			default :
			{
				$ret = [];
				break;
			}
		}
		return $ret;
	}
	
	/**
	 * @api.enable
	 * 
	 * @param string $viewTag
	 */
	public static function GetFormDefaultBinds($viewTag)
	{
		$ret = null;
		switch ($viewTag)
		{
			default :
			{
				$ret = [];
				break;
			}
		}
		return $ret;
	}
	
	public static function GetEntityForGenerateForm_Final($viewTag = null)
	{
		return static::GetEntityForGenerateForm($viewTag);
	}
	public static function GetEntityForGenerateList_Final($viewTag = null)
	{
		return static::GetEntityForGenerateList($viewTag);
	}

	public static function GetFormEntity_Final($viewTag = null)
	{
		return static::GetFormEntity($viewTag);
	}
	public static function GetListEntity_Final($viewTag = null)
	{
		return static::GetListEntity($viewTag);
	}
	
	/**
	 * @api.enable
	 * 
	 * @return boolean
	 */
	public static function IsHolder()
	{
		return true;
	}
	
	/**
	 * @api.enable
	 */
	public static function GetHolder()
	{
		return null;
	}
	
	/**
	 * @api.enable
	 */
	public static function GetCurrentOwner()
	{
		return null;
	}

	public static function GetSecurityUser(bool $force_redo = false)
	{
		return \Omi\User::GetCurrentUser();
	}
	
	/**
	 * @api.enable
	 * 
	 * @param type $captchaResponse
	 * @return type
	 */
	public static function IsHuman($captchaResponse)
	{
		$curl = curl_init("https://www.google.com/recaptcha/api/siteverify");
		curl_setopt($curl, CURLOPT_POST, true);

		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query([
			"secret" => (defined("RECAPTCHA_SECRET_KEY") && RECAPTCHA_SECRET_KEY) ? RECAPTCHA_SECRET_KEY : '',
			"response" => $captchaResponse
		]));

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		$resp = curl_exec($curl);

		if ($resp === false)
			throw new \Exception("request failed for recaptcha! Reason: " . curl_error($curl));

		//$info = curl_getinfo($curl);		
		return ($decodedResp = ($resp ? json_decode($resp, true) : null)) ? $decodedResp["success"] : false;
	}
	
	/**
	 * 
	 * @param string $from
	 * @param string $selector
	 * @param array $parameters
	 * 
	 * @return QIModel
	 */
	public static function Before_API_Query($from, $selector = null, $parameters = null, $only_first = false, $id = null)
	{
		return [false, null];
	}
	
}
