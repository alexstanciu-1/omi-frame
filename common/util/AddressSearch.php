<?php

namespace Omi\Util;

class AddressSearch extends \QModel
{
	private static $WADL_URL = 'http://ws.postcoder.com/pcw/';

	private static $GET_ADDR_IO_URL = "https://api.getAddress.io/v2/";

	private static $DefaultCountryCode = 'RO';

	private static $UseSystem = POSTCODE_USE_SYSTEM;

	private static $IntegratedSystems = [
		"worldaddresses"		=> "worldaddresses",
		"getaddress.io"			=> "getaddress.io",
		"postcodeapi.com.au"	=> "postcodeapi.com.au",
		"google-maps"			=> "google-maps",
	];

	/**
	 * Cache expire time in hours
	 *
	 * @var int
	 */
	private static $Cache_Expire = 10;
	
	/**
	 * @api.enable
	 */
	public static function Search_Address_For_DropDown($from, $selector = null, $binds = null)
	{
		if ($binds['WHR_Search'] && ($query = trim($binds['WHR_Search'], " %\t\n\r")) && (strlen($query) >= 3))
		{
			$query = str_replace("%", " ", $query);
			
			$appOwnerCuntryCode = $binds['country_code'] ?: ((defined('APP_OWNER_COUNTRY_CODE') ? APP_OWNER_COUNTRY_CODE : 'RO'));

			if (!($country = QQuery("Countries.{Code, Name WHERE Code=?}", $appOwnerCuntryCode)->Countries[0]))
				throw new \Exception("Country not found: ".$appOwnerCuntryCode);

			$items = static::SetupGroup($query, $country);
			
			return $items;
		}
		else
			return [];
	}
	
	/**
	 * @api.enable
	 * 
	 * @param string $postCode
	 * @param Country $country
	 * @return \Omi\AddressGroup
	 */
	public static function SetupGroup($postCode, $country, $group = null)
	{
		$resp = static::Search($postCode, $country ? $country->Code : null);
		
		if (!$resp || ($isEx = ($resp instanceof \Exception)))
		{
			# if ($isEx)
			# $resp = $resp->getMessage()."\n".$resp->getFile()."\n".$resp->getLine()."\n".$resp->getCode()."\n".$resp->getTraceAsString();
			throw $resp ?: new \Exception('Address search API Error.');
		}

		try
		{
			$addresses = static::Decode($resp, $postCode);
			$addresses_model = static::Decode_to_Address($country, $addresses);
		}
		catch (\Exception $ex)
		{
			throw $ex;
		}
		return $addresses_model;
	}

	/**
	*	Function: setSearchKey
	*
	*	Sets the searchKey value
	*	
	*	Parameters:
	*
	*	$value - Pass a string value with the search key supplied by allies. 
	*/
	public function setSearchKey($sk)
	{
		if (empty($sk) || !is_string($sk))
			throw new Exception("Invalid Search Key");
		$this->searchKey = $sk;
	}
	/**
	*	Function: setCountryCode
	*
	*	Sets the dataSet value
	*	
	*	Parameters:
	*
	*		$value - Pass a string value with the dataset you would like to include in your search results. 
	*/
	public function setCountryCode($countryCode)
	{
		if (empty($countryCode) || !is_string($countryCode))
			throw new Exception("Invalid Country Code");
		$this->CountryCode = $countryCode;
	}

	public static function Decode($resp, $postCode)
	{
		if (!static::$UseSystem || !isset(static::$IntegratedSystems[static::$UseSystem]))
			throw new \Exception("No search system set!");

		if (static::$UseSystem === "worldaddresses")
			return static::Decode_WorldAddesses($resp, $postCode);
		else if (static::$UseSystem === "getaddress.io")
			return static::Decode__GetAddressIo($resp, $postCode);
		else if (static::$UseSystem === "postcodeapi.com.au")
			return static::Decode__GetAddressPostCodeApiAU($resp, $postCode);
		else if (static::$UseSystem === "google-maps")
			return static::Decode_GoogleMaps($resp, $postCode);
		else
			throw new \Exception("not implemented!");
	}
	
	protected static function Decode_WorldAddesses($resp, $postCode)
	{
		return json_decode($resp);
	}
	
	protected static function Decode_GoogleMaps($resp, $postCode)
	{
		if (!$resp)
			return null;
		
		$json = json_decode($resp);
		if ((!$json) || (!$json->results))
			return null;
		
		$ret = null;
		
		foreach ($json->results as $ret_addr)
		{
			$address_components = $ret_addr->address_components;
			if (!$address_components)
				continue;
			$indexed = [];
			foreach ($address_components as $ac)
			{
				if (!$ac->types)
					continue;
				foreach ($ac->types as $ac_type)
				{
					$indexed[$ac_type] = $ac->long_name ?? $ac->short_name;
				}
			}

			$addr = new \stdClass();
			if ($indexed['street_number'])
				$addr->number = $indexed['street_number'];
			if ($indexed['route'])
				$addr->street = $indexed['route'];
			if ($indexed['locality'])
				$addr->posttown = $indexed['locality'];
			if ($indexed['administrative_area_level_1'])
				$addr->county = $indexed['administrative_area_level_1'];
			if ($indexed['administrative_area_level_1'])
				$addr->county = $indexed['administrative_area_level_1'];
			if ($indexed['country'])
				$addr->country = $indexed['country'];
			if ($indexed['postal_code'])
				$addr->postcode = $indexed['postal_code'];
			
			if (isset($ret_addr->geometry->location->lat))
				$addr->latitude = $ret_addr->geometry->location->lat;
			if (isset($ret_addr->geometry->location->lng))
				$addr->longitude = $ret_addr->geometry->location->lng;
			
			$ret[] = $addr;
		}
		
		return $ret;
	}

	protected static function Decode__GetAddressIo($resp, $postCode)
	{
		$data = json_decode($resp, true);
		$ret = null;

		if ($data && $data["Addresses"])
		{
			$ret = [];
			foreach ($data["Addresses"] as $addrLine)
			{
				$addrData = explode(",", $addrLine);
				$addr = new \stdClass();
				$addr->county = trim(array_pop($addrData));

				$town = trim(array_pop($addrData));

				$locality = trim(array_pop($addrData));
				//$addr->posttown = $locality ?: $town;

				$addr->posttown = $town;
				
				$addr->organisation = trim(array_shift($addrData));
				$addr->street = trim(array_shift($addrData));
				
				$addr->premise = trim(array_pop($addrData));
				$addr->number = trim(array_pop($addrData));
				
				$addr->postcode = $postCode;

				$addr->longitude = $data["Longitude"];
				$addr->latitude = $data["Latitude"];			
				
				$addr->summaryline = "";

				if ($addr->organisation)
					$addr->summaryline .= (strlen($addr->summaryline) > 0 ? ", " : "") . $addr->organisation;

				if ($addr->number)
					$addr->summaryline .= (strlen($addr->summaryline) > 0 ? ", " : "") . $addr->number;
				
				if ($addr->premise)
					$addr->summaryline .= (strlen($addr->summaryline) > 0 ? ", " : "") . $addr->premise;
				
				if ($addr->street)
					$addr->summaryline .= (strlen($addr->summaryline) > 0 ? ", " : "") . $addr->street;
				
				if ($locality)
					$addr->summaryline .= (strlen($addr->summaryline) > 0 ? ", " : "") . $locality;
				
				if ($town)
					$addr->summaryline .= (strlen($addr->summaryline) > 0 ? ", " : "") . $town;
				
				if ($addr->county)
					$addr->summaryline .= (strlen($addr->summaryline) > 0 ? ", " : "") . $addr->county;
				
				if ($addr->postcode)
					$addr->summaryline .= (strlen($addr->summaryline) > 0 ? ", " : "") . $addr->postcode;

				$ret[] = $addr;
			}
		}

		return $ret;
	}
	
	protected static function Decode__GetAddressPostCodeApiAU($resp, $postCode)
	{
		$data = json_decode($resp, true);

		$ret = [];
		foreach ($data ?: [] as $addrData)
		{
			$addrData = (object)$addrData;
			$addr = new \stdClass();

			// set postcode
			$addr->postcode = $addrData->postcode;

			if ($addrData->state)
			{
				$state = (object)$addrData->state;
				$addr->county = trim($state->name);
				$addr->countyCode = trim($state->abbreviation);
			}

			if ($addrData->locality)
				$addr->posttown = $addrData->locality;

			$addr->longitude = $addrData->longitude;
			$addr->latitude = $addrData->latitude;
			//$addr->details = $addrData->name;
			$addr->premise = $addrData->name;

			$addr->summaryline = "";

			//if ($addr->details)
			//	$addr->summaryline .= (strlen($addr->summaryline) > 0 ? ", " : "") . $addr->details;
			
			if ($addr->premise)
				$addr->summaryline .= (strlen($addr->summaryline) > 0 ? ", " : "") . $addr->premise;

			if ($addr->posttown)
				$addr->summaryline .= (strlen($addr->summaryline) > 0 ? ", " : "") . $addr->posttown;

			if ($addr->county)
				$addr->summaryline .= (strlen($addr->summaryline) > 0 ? ", " : "") . $addr->county;

			if ($addr->postcode)
				$addr->summaryline .= (strlen($addr->summaryline) > 0 ? ", " : "") . $addr->postcode;

			$ret[] = $addr;
		}
		return $ret;
	}

    /**
	 * @api.enable
	 * 
	 * @param string $postCode
	 * @return string
	 */
    public static function Search($postCode, $countryCode = null)
    {
		if (!is_dir("cache/postcodes_search/"))
			qmkdir("cache/postcodes_search/");

		if (!static::$UseSystem || !isset(static::$IntegratedSystems[static::$UseSystem]))
			throw new \Exception("No search system set!");

		if (static::$UseSystem === "worldaddresses")
			return static::Search__WorldAddesses($postCode, $countryCode);
		else if (static::$UseSystem === "getaddress.io")
			return static::Search__GetAddressIo($postCode, $countryCode);
		else if (static::$UseSystem === "postcodeapi.com.au")
			return static::Search__GetAddressPostCodeApiAU($postCode, $countryCode);
		else if (static::$UseSystem === "google-maps")
			return static::Search__GoogleMaps($postCode, $countryCode);
		else
			throw new \Exception("not implemented!");
    }
	/**
	 * 
	 * @param type $postCode
	 * @param type $countryCode
	 * @return \Exception
	 * @throws \Exception
	 */
	protected static function Search__GetAddressIo($postCode, $countryCode = null)
	{
		if (!defined('_GET_ADDR_IO_SEARCH_KEY_') || (!($search_key = _GET_ADDR_IO_SEARCH_KEY_)))
			throw new \Exception("Key not set!");
		
		if (!($postCode = trim($postCode)))
			return null;

		if ($countryCode === null)
			$countryCode = defined('APP_OWNER_COUNTRY_CODE') ? APP_OWNER_COUNTRY_CODE : static::$DefaultCountryCode;

		//try to execute the code between the first set of curly braces '{', '}'
		$response = null;
		try 
		{
			// create the url for this
			$URL = static::$GET_ADDR_IO_URL . '/' . strtolower($countryCode) . '/' . rawurlencode($postCode)."?api-key=" . $search_key;

			$cache_file = "cache/postcodes_search/" . md5($URL) . ".json";
			
			if (file_exists($cache_file) && ((time() - filemtime($cache_file)) < (static::$Cache_Expire * 60 * 60)))
			{
				$response = file_get_contents($cache_file);
			}
			else
			{
				// use cURL to send the request and get the response
				$session = curl_init($URL);

				// Tell cURL to return the request data
				curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

				// use application/json to specify json return values.
				$headers = array('Content-Type: application/json');
				curl_setopt($session, CURLOPT_HTTPHEADER, $headers);

				// Execute cURL on the session handle
				$response = curl_exec($session);

				// Close the cURL session
				curl_close($session);
				
				// cache postcode data
				file_put_contents($cache_file, $response);
			}
		}
		catch (\Exception $e) 
		{
			$response = $e;
		}
		return $response;
	}
	/**
	 * 
	 * @param type $postCode
	 * @param type $countryCode
	 * @return \Exception
	 * @throws \Exception
	 */
	protected static function Search__WorldAddesses($postCode, $countryCode = null)
	{
		if (!defined('_WORLD_ADDRESSES_SEARCH_KEY_') || (!($search_key = _WORLD_ADDRESSES_SEARCH_KEY_)))
			throw new \Exception("Key not set!");
		
		if (!($postCode = trim($postCode)))
			return null;

		if ($countryCode === null)
			$countryCode = defined('APP_OWNER_COUNTRY_CODE') ? APP_OWNER_COUNTRY_CODE : static::$DefaultCountryCode;

		//try to execute the code between the first set of curly braces '{', '}'
		$response = null;
		try 
		{
			//$searchKey = 'PCWSG-CJDNW-MW3XN-6375E';   // your search key
			//$searchterm  = 'NR147PZ';                 // string to use for an address search

			//echo 'PostCoder Web V3 PHP Client Snippet<br><br>';
			// build the URL, using the 'address' search method:
			//http://ws.postcoder.com/pcw/[api-key]/address/uk/[postcode-or-address-fragment]

			$URL = static::$WADL_URL . $search_key . '/address/' . $countryCode . '/' . rawurlencode($postCode)."?format=json";
			
			$cache_file = "cache/postcodes_search/" . md5($URL) . ".json";
			
			if (file_exists($cache_file) && ((time() - filemtime($cache_file)) < (static::$Cache_Expire * 60 * 60)))
			{
				$response = file_get_contents($cache_file);
			}
			else
			{
				// use cURL to send the request and get the response
				$session = curl_init($URL);

				// Tell cURL to return the request data
				curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

				// use application/json to specify json return values.
				$headers = array('Content-Type: application/json');
				curl_setopt($session, CURLOPT_HTTPHEADER, $headers);

				// Execute cURL on the session handle
				$response = curl_exec($session);

				// Close the cURL session
				curl_close($session);
				
				// cache postcode data
				file_put_contents($cache_file, $response);
			}
		}
		catch (\Exception $e) 
		{ 
			$response = $e;
		}
		return $response;
	}
	
	/**
	 * 
	 * @param type $postCode
	 * @param type $countryCode
	 * @return \Exception
	 * @throws \Exception
	 */
	protected static function Search__GoogleMaps($postCode, $countryCode = null)
	{
		if (!defined('_GOOGLE_MAPS_API_KEY_') || (!($search_key = _GOOGLE_MAPS_API_KEY_)))
			throw new \Exception("Key not set!");
		
		if (!($postCode = trim($postCode)))
			return null;

		if ($countryCode === null)
			$countryCode = defined('APP_OWNER_COUNTRY_CODE') ? APP_OWNER_COUNTRY_CODE : static::$DefaultCountryCode;

		//try to execute the code between the first set of curly braces '{', '}'
		$response = null;
		try 
		{
			//$searchKey = 'PCWSG-CJDNW-MW3XN-6375E';   // your search key
			//$searchterm  = 'NR147PZ';                 // string to use for an address search

			//echo 'PostCoder Web V3 PHP Client Snippet<br><br>';
			// build the URL, using the 'address' search method:
			//http://ws.postcoder.com/pcw/[api-key]/address/uk/[postcode-or-address-fragment]
			
			// https://maps.googleapis.com/maps/api/place/textsearch/output?parameters
			$URL = 'https://maps.googleapis.com/maps/api/geocode/json?key='.urlencode(_GOOGLE_MAPS_API_KEY_).'&address='.urlencode($postCode.($countryCode ? " ".$countryCode : ""));
			// $URL = 'https://maps.googleapis.com/maps/api/place/textsearch/json?key='.urlencode(_GOOGLE_MAPS_API_KEY_).'&query='.urlencode($postCode.($countryCode ? " ".$countryCode : ""));
			
			// $URL = static::$WADL_URL . $search_key . '/address/' . $countryCode . '/' . rawurlencode($postCode)."?format=json";
			
			$cache_file = "cache/postcodes_search/" . md5($URL) . ".json";
			
			if ((file_exists($cache_file) && ((time() - filemtime($cache_file)) < (static::$Cache_Expire * 60 * 60))))
			{
				$response = file_get_contents($cache_file);
			}
			else
			{
				// use cURL to send the request and get the response
				$session = curl_init($URL);

				// Tell cURL to return the request data
				curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

				// use application/json to specify json return values.
				$headers = array('Content-Type: application/json; charset=UTF-8');
				curl_setopt($session, CURLOPT_HTTPHEADER, $headers);

				// Execute cURL on the session handle
				$response = curl_exec($session);
				
				// Close the cURL session
				curl_close($session);
				
				// cache postcode data
				file_put_contents($cache_file, $response);
			}
		}
		# catch (\Exception $e) 
		finally
		{ 
			# $response = $e;
		}
		
		return $response;
	}
	
	
	protected static function Search__GetAddressPostCodeApiAU($postCode, $countryCode = null)
	{
		if (!($postCode = trim($postCode)))
			return null;

		if ($countryCode === null)
			$countryCode = defined('APP_OWNER_COUNTRY_CODE') ? APP_OWNER_COUNTRY_CODE : static::$DefaultCountryCode;
		
		//try to execute the code between the first set of curly braces '{', '}'
		$response = null;
		try 
		{
			//http://v0.postcodeapi.com.au/suburbs.json?q=4350
			$URL = 'http://v0.postcodeapi.com.au/suburbs.json?q=' . rawurlencode($postCode);

			$cache_file = "cache/postcodes_search/" . md5($URL) . ".json";

			if (file_exists($cache_file) && ((time() - filemtime($cache_file)) < (static::$Cache_Expire * 60 * 60)))
			{
				$response = file_get_contents($cache_file);
			}
			else
			{
				// use cURL to send the request and get the response
				$curl = curl_init($URL);

				// Tell cURL to return the request data
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

				// use application/json to specify json return values.
				$headers = array('Content-Type: application/json');
				curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

				// Execute cURL on the session handle
				$response = curl_exec($curl);

				// Close the cURL session
				curl_close($curl);
				
				// cache postcode data
				file_put_contents($cache_file, $response);
			}
		}
		catch (\Exception $e) 
		{
			$response = $e;
		}

		return $response;
	}
	
	/**
	 * 
	 * @param string $postCode
	 */
	public function performRequest($postCode)
	{
		
	}
	
	
	protected static function Decode_to_Address($country, $addresses)
	{
		$cities = [];
		$counties = [];
		$cachedAddresses = [];
		
		$ret_addresses = new \QModelArray();
		
		foreach ($addresses as $addr)
		{
			$address = new \Omi\Address();
			$address->setCaption($addr->summaryline);

			if ($addr->organisation)
				$address->setOrganization($addr->organisation);

			if ($addr->buildingname)
				$address->setBuilding($addr->buildingname);

			if ($addr->subbuildingname)
				$address->setSubBuilding($addr->subbuildingname);

			if ($addr->street)
				$address->setStreet($addr->street);
			
			if ($addr->details)
				$address->setDetails($addr->Details);

			if ($addr->number)
				$address->setStreetNumber($addr->number);

			if ($addr->premise && ($addr->premise != $addr->number))
				$address->setPremise($addr->premise);

			if ($addr->postcode)
				$address->setPostcode($addr->postcode);
			
			if ($addr->latitude)
				$address->setLatitude($addr->latitude);
			if ($addr->longitude)
				$address->setLongitude($addr->longitude);
			
			if (defined('Q_TF_MAPS_USE_PLACEID') && Q_TF_MAPS_USE_PLACEID)
			{
				if (isset($addr->latitude) && $addr->latitude && isset($addr->longitude) && $addr->longitude)
				{
					# $countryCode = strtolower(defined('APP_OWNER_COUNTRY_CODE') ? APP_OWNER_COUNTRY_CODE : static::$DefaultCountryCode);
					if (!isset($address->City))
					{
						$address->City = new \Omi\City();
						$address->City->setName($addr->posttown);
					}
					if (!isset($address->County))
					{
						$address->County = new \Omi\County();
						$address->County->setName($addr->county);
					}
					if (!isset($address->Country))
					{
						$address->Country = new \Omi\Country();
						$address->Country->setName($addr->country);
					}
					
					static::geodecode_populate_place_id($address, false, true);
				}
			}
			else
			{
				# OLD WAY
				$address->setCountry($country);

				if ($addr->county)
				{
					$county = $counties[$addr->county] ? $counties[$addr->county] : null;
					if (!$county)
					{
						$counties = \QApi::Query("Counties", "Name", ["Name" => $addr->county, "Country" => $country->getId()]);
						$county = $counties ? $counties[0] : null;
					}

					if (!$county)
					{
						$county = new \Omi\County();
						$county->setName($addr->county);
						if ($addr->countyCode)
							$county->setCode($addr->countyCode);
						$county->setCountry($country);
						$_appret = \QApi::Merge("Counties", $county->toArray("Name, Code, Country.Id"));
						$county = $_appret->Counties ? $_appret->Counties[0] : null;
					}

					$counties[$addr->county] = $county;
					$address->setCounty($county);
				}

				if ($addr->posttown)
				{
					$city = $cities[$addr->posttown] ? $cities[$addr->posttown] : null;

					if (!$city)
					{
						$cities = \QApi::Query("Cities", "Name", ["Name" => $addr->posttown, "County" => $address->County->Id, "Country" => $country->getId()]);
						$city = $cities ? $cities[0] : null;	
					}

					if (!$city)
					{
						$city = new \Omi\City();
						$city->setName($addr->posttown);
						$city->setCounty($address->County);
						$city->setCountry($country);

						$_appret = \QApi::Merge("Cities", $city->toArray("Name, County.Id, Country.Id"));
						$city = $_appret->Cities ? $_appret->Cities[0] : null;
					}

					if ($address->County)
						$city->setCounty($address->County);

					$cities[$addr->posttown] = $city;
					$address->setCity($city);
				}
			}
			
			$ret_addresses[] = $address;
		}

		return $ret_addresses;
	}
	
	static function google_geodecode_parts(float $latitude, float $longitude, string $language = 'ro', string $api_key = null, array $components_filter = null)
	{
		$lng_lat = $latitude . ',' . $longitude;
		if (($api_key === null) && defined('_GOOGLE_MAPS_API_KEY_') && _GOOGLE_MAPS_API_KEY_)
			$api_key = _GOOGLE_MAPS_API_KEY_;

		$ret = [];

		foreach ([	'locality' => ["administrative_area_level_2", "Cities", "*,County.*,Country.*"], 
					'administrative_area_level_2' => [null, "Cities", "*,County.*,Country.*"], 
					'administrative_area_level_1' => [null, "Counties", "*,Country.*"], 
					'country' => [null, "Countries", "*"]] 
														as $geo_type => $geo_inf)
		{
			if ($components_filter && (!in_array($geo_type, $components_filter)))
				# skip if filtered
				continue;

			list($alternative_geo_type) = $geo_inf;

			$URL = 'https://maps.googleapis.com/maps/api/geocode/json?key='.urlencode($api_key)

						.'&latlng='.urlencode($lng_lat)
						.'&language='. urlencode($language)
						.'&result_type='.urlencode($geo_type)
					;
			
			$resp = static::google_maps_request($URL);

			$place_id = (isset($resp->results[0]->place_id) && isset($resp->results[0]->types) && in_array($geo_type, $resp->results[0]->types)) ? 
					trim($resp->results[0]->place_id) : false;

			$ret[$geo_type] = $place_id;
			$ret['_results_'][] = $resp;

			if (!$place_id && $alternative_geo_type && ($resp->status === "ZERO_RESULTS"))
			{
				/*$URL = 'https://maps.googleapis.com/maps/api/geocode/json?key='.urlencode($api_key)

						.'&latlng='.urlencode($lng_lat)
						.'&language='. urlencode($language)
						.'&result_type='.urlencode($alternative_geo_type)
						;
				*/

				$alternaive_place_id = static::google_geodecode_parts($latitude, $longitude, $language, $api_key, [$alternative_geo_type]);

				if (isset($alternaive_place_id[$alternative_geo_type]))
				{
					$ret[$geo_type] = $alternaive_place_id[$alternative_geo_type];
					if (isset($alternaive_place_id['_results_']))
						$ret['_results_'] = $alternaive_place_id['_results_'];
				}
			}
		}

		return $ret;
	}
	
	static function google_maps_request(string $URL)
	{
		global $q_google_maps_request_session;
		// use cURL to send the request and get the response
		if (!$q_google_maps_request_session)
			$q_google_maps_request_session = curl_init();
		else
			curl_reset($q_google_maps_request_session);

		$session = $q_google_maps_request_session;

		curl_setopt_array($session, [
			CURLOPT_URL => $URL,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => ['Content-Type: application/json; charset=UTF-8'],
		]);

		// Execute cURL on the session handle
		$response = curl_exec($session);

		# qvar_dumpk('$response', $response);

		$jsd = is_string($response) ? json_decode($response) : null;

		if (is_object($jsd) && isset($jsd->status) && (($jsd->status === 'OK') || ($jsd->status === 'ZERO_RESULTS')))
			return $jsd;
		else
			return false;
	}
	
	public static function geodecode_populate_place_id(\Omi\Address $address, bool $force = false, bool $insert_in_app = false)
	{
		$latitude = $address->Latitude;
		$longitude = $address->Longitude;
		
		if ((!$latitude) || (!$longitude))
			return false;
		
		if ($address->Id)
			$address->populate('City.Place_Id,County.Place_Id,Country.Place_Id');

		if (($force || (!isset($address->Country->Place_Id))))
		{
			$ret = static::google_geodecode_parts($latitude, $longitude, 'ro', null, ['country']);
			if (isset($ret['country']))
			{
				if (!$address->Country)
					$address->Country = new \Omi\Country();
				
				# if ((!isset($address->Country->Name)) && isset($ret["_results_"][0]->results[0]->address_components[0]->long_name))
				$address->Country->setName($ret["_results_"][0]->results[0]->address_components[0]->long_name);
			
				$address->Country->setPlace_Id($ret['country']);
				$address->Country->setPlace_Mtime(date('Y-m-d H:i:s'));
				
				$in_app = \QQuery_First_By_Filter("Countries", ["Place_Id" => $address->Country->Place_Id], "Id,Name,Place_Id");
				if (isset($in_app->Id))
				{
					$prev_country = $address->Country;
					
					if ($address->Country->Id != $in_app->Id)
					{
						$address->setCountry($in_app);
						if ($address->Id)
							$address->db_save('Country.Id');
					}
					
					# a fix
					if (isset($prev_country->Name))
					{
						$address->Country->setName($prev_country->Name);
						$address->Country->db_save('Name');
					}
				}
				else if ($insert_in_app)
				{
					\QApi::Merge("Countries", $address->Country, "Id,Name,Place_Id");
					# qvar_dumpk('$mr', $mr);
				}
				else if (isset($address->Country->Id))
					$address->Country->db_save('Name,Place_Id,Place_Mtime');
			}
		}
		
		$county_ret = null;
		
		if (($force || (!isset($address->County->Place_Id))))
		{
			$ret = $county_ret = static::google_geodecode_parts($latitude, $longitude, 'ro', null, ['administrative_area_level_1']);
			if (isset($ret['administrative_area_level_1']))
			{
				if (!$address->County)
					$address->County = new \Omi\County();
				
				# if ((!isset($address->County->Name)) && isset($ret["_results_"][0]->results[0]->address_components[0]->long_name))
				$address->County->setName($ret["_results_"][0]->results[0]->address_components[0]->long_name);
				
				$address->County->setPlace_Id($ret['administrative_area_level_1']);
				$address->County->setPlace_Mtime(date('Y-m-d H:i:s'));
				
				$in_app = \QQuery_First_By_Filter("Counties", ["Place_Id" => $address->County->Place_Id], "Id,Name,Place_Id");
				if (isset($in_app->Id))
				{
					$prev_county = $address->County;
					
					# $address->County->setId($in_app->Id);
					if ($address->County->Id != $in_app->Id)
					{
						$address->setCounty($in_app);
						if ($address->Id)
							$address->db_save('County.Id');
					}
					
					# a fix
					if (isset($prev_county->Name))
					{
						$address->County->setName($prev_county->Name);
						$address->County->db_save('Name');
					}
				}
				else if ($insert_in_app)
					\QApi::Merge("Counties", $address->County, "Id,Name,Place_Id");
				else if (isset($address->County->Id))
					$address->County->db_save('Name,Place_Id,Place_Mtime');
			}
		}
		
		if (($force || (!isset($address->City->Place_Id))))
		{
			$ret = static::google_geodecode_parts($latitude, $longitude, 'ro', null, ['locality']);
			
			if ((!isset($ret['locality'])) && defined('Q_TF_MAPS_CREATE_CITY_FROM_COUNTY') && Q_TF_MAPS_CREATE_CITY_FROM_COUNTY)
			{
				# address outside city , setup a fake city out of the county
				if (!$county_ret)
					throw new \Exception('City setup is mandatory based on the `Q_TF_MAPS_CREATE_CITY_FROM_COUNTY` but no solution was found.');
				
				if (\QAutoload::GetDevelopmentMode())
					qvar_dumpk('$county_ret', $county_ret);
				
				throw new \Exception('setup city based on county');
			}
			
			if (isset($ret['locality']))
			{
				if (!$address->City)
					$address->City = new \Omi\City();
				
				# if ((!isset($address->City->Name)) && (isset($ret["_results_"][0]->results[0]->address_components[0]->long_name)))
				$address->City->setName($ret["_results_"][0]->results[0]->address_components[0]->long_name);
				
				$address->City->setPlace_Id($ret['locality']);
				$address->City->setPlace_Mtime(date('Y-m-d H:i:s'));
				
				$in_app = \QQuery_First_By_Filter("Cities", ["Place_Id" => $address->City->Place_Id], "Id,Name,Place_Id");
				if (isset($in_app->Id))
				{
					$prev_city = $address->City;
					
					if ($address->City->Id != $in_app->Id)
					{
						$address->setCity($in_app);
						if ($address->Id)
							$address->db_save('City.Id');
					}
					# a fix
					if (isset($prev_city->Name))
					{
						$address->City->setName($prev_city->Name);
						$address->City->db_save('Name');
					}
				}
				else if ($insert_in_app)
					\QApi::Merge("Cities", $address->City, "Id,Name,Place_Id");
				else if (isset($address->City->Id))
					$address->City->db_save('Name,Place_Id,Place_Mtime');
			}
		}
		
		if ($address->County->Id)
		{
			$address->City->setCounty($address->County);
			if (isset($address->City->Id))
				$address->City->db_save('County');
		}
		if ($address->Country)
		{
			$address->City->setCountry($address->Country);
			if (isset($address->City->Id))
				$address->City->db_save('Country');
			$address->County->setCountry($address->Country);
			if (isset($address->County->Id))
				$address->County->db_save('Country');
		}
	}
}
