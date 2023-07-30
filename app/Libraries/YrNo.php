<?php namespace App\Libraries;
/**
 * YrNo library
 * ------------
 * A library for working with weather data from Yr.no for Codeigniter 4
 *
 * @author Elix <elix.dev@gmx.com>
 * @version 1.3
 *
 * Use:
 * ------------
 * $this->weather = new \App\Libraries\YrNo();                // Initialize library
 * $this->weather->setLatitude('49.1547');                    // Latitude settings
 * $this->weather->setLongitude('19.4678');                   // Longitude settings
 * $this->weather->setAltitude('254');                        // Altitude settings (not required)
 * $this->weather->setUserAgent('Test Weather App v1.0');     // User agent settings (must be unique)
 *
 * $json = $this->weather->getData();                         // Getting json data (for cache etc...)
 * $this->weather->parseData( $json );                        // Proccessing data
 *
 * $time = new Time('now', 'UTC', 'cs_CZ');                   // Setting time for forecast
 * $temperature = $this->weather->getTemperature( $time );    // Getting temperature value in celsius
 * etc...
 */
use CodeIgniter\I18n\Time;

class YrNo {

	/**
	 * Yr.no api URL for forecast
	 */
	private $api_url;

	/**
	 * Latitude
	 */
	private $lat;

	/**
	 * Longitude
	 */
	private $lon;

	/**
	 * Altitude
	 */
	private $altitude;

	/**
	 * Location variable
	 */
	private $location;

	/**
	 * Cache timeout in seconds
	 */
	private $cache_timeout;


	/**
	 * User agent for request
	 */
	private $user_agent;

	/**
	 * JSON data from Yr.no
	 */
	private $json;

	/**
	 * Array data from JSON
	 */
	private $data;



	/**
	 * Class constructor
	 */
	public function __construct() {

		// Prague, Czech republic
		$this->lat = '50.0880';
		$this->lon = '14.4207';

		// Altitude in meters (not required)
		$this->altitude = '';

		// Building API url
		$this->api_url = $this->_getApiUrl();

		// User agent (REQUIRED!!!)
		$this->userAgent = 'YrNo PHP Library for Codeigniter 4/v1.2';

		// Output JSON data
		$this->json = false;

		log_message('info', 'Library YrNo initialized.');
  }



	/**
	 * Setting Latitude
	 *
	 * TODO: Only max 4 digits on end
	 * @param string Latitude
	 * @return void
	 */
	public function setLatitude( $lat ) {
		$this->lat = $lat;
	}



	/**
	 * Setting Longitude
	 *
	 * TODO: Only max 4 digits on end
	 * @param string Longitude
	 * @return void
	 */
	public function setLongitude( $lon ) {
		$this->lon = $lon;
	}



	/**
	 * Setting Altitude
	 *
	 * @param string Altitude
	 * @return void
	 */
	public function setAltitude( $alt ) {
		$this->altitude = $alt;
	}



	/**
	 * Setting User agent
	 *
	 * @param string User agent
	 * @return void
	 */
	public function setUserAgent( $ua ) {
		$this->user_agent = $ua;
	}



	/**
	 * Get JSON file for creating cache
	 *
	 * @return string
	 */
	public function getData() {
		$this->json = $this->_download();
		if( $this->json !== false && !empty($this->json) ) {
			log_message('info', 'Weather data from Yr.no succesfully downloaded.');
			return (string) $this->json ;
		}else{
			log_message('error', 'Downloading Weather data from Yr.no failed!');
			$this->json = false;
			return '';
		}
	}



	/**
	 * Parsing JSON data to Array
	 *
	 * @param string JSON data
	 * @return array
	 */
	public function parseData( $json = false ) {
		if( !$json ) {
			$this->data = json_decode( $this->json, true );
		}else{
			$this->data = json_decode( $json, true );
		}

		if( $this->data !== false ) {
			log_message('info', 'Weather data from Yr.no succesfully decoded.');
			return (array) $this->data;
		}else{
			log_message('error', 'Decoding Weather data from Yr.no failed!');
			return false;
		}


	}



	/**
	 * Get Temperature
	 *
	 * @param object new Time('now', 'UTC', 'cs_CZ');
	 * @return string Temperature
	 */
	public function getTemperature( $datetime ) {
		$datestring = $datetime->toDateString() . 'T' . $datetime->getHour() . ':00:00Z';
		$datestring = $this->_modifyHour( $datestring );

		if( $this->data !== false ) {
			foreach( $this->data['properties']['timeseries'] as $hours) {
				if( $hours['time'] == $datestring ) {
					if( isset($hours['data']['instant']['details']['air_temperature']) ){
						return (string) $hours['data']['instant']['details']['air_temperature'];
					}
				}
			}
		}
		log_message('warning', 'Temperature value from Yr.no not exists!');
		return false;
	}



	/**
	 * Get air pressure in hPa
	 *
	 * @param object new Time('now', 'UTC', 'cs_CZ');
	 * @return string Pressure
	 */
	public function getPressure( $datetime ) {
		$datestring = $datetime->toDateString() . 'T' . $datetime->getHour() . ':00:00Z';
		$datestring = $this->_modifyHour( $datestring );

		if( $this->data !== false ) {
			foreach( $this->data['properties']['timeseries'] as $hours) {
				if( $hours['time'] == $datestring ) {
					if( isset($hours['data']['instant']['details']['air_pressure_at_sea_level']) ){
						return (string) $hours['data']['instant']['details']['air_pressure_at_sea_level'];
					}
				}
			}
		}
		log_message('warning', 'Pressure value from Yr.no not exists!');
		return false;
	}



	/**
	 * Get cloud area fraction in %
	 *
	 * @param object new Time('now', 'UTC', 'cs_CZ');
	 * @return string Cloud area fraction
	 */
	public function getCloudAreaFraction( $datetime ) {
		$datestring = $datetime->toDateString() . 'T' . $datetime->getHour() . ':00:00Z';
		$datestring = $this->_modifyHour( $datestring );

		if( $this->data !== false ) {
			foreach( $this->data['properties']['timeseries'] as $hours) {
				if( $hours['time'] == $datestring ) {
					if( isset($hours['data']['instant']['details']['cloud_area_fraction']) ){
						return (string) $hours['data']['instant']['details']['cloud_area_fraction'];
					}
				}
			}
		}
		log_message('warning', 'Cloud area fraction value from Yr.no not exists!');
		return false;
	}




	/**
	 * Get cloud area fraction low in %
	 *
	 * @param object new Time('now', 'UTC', 'cs_CZ');
	 * @return string Cloud area low fraction
	 */
	public function getCloudAreaFractionLow( $datetime ) {
		$datestring = $datetime->toDateString() . 'T' . $datetime->getHour() . ':00:00Z';
		$datestring = $this->_modifyHour( $datestring );

		if( $this->data !== false ) {
			foreach( $this->data['properties']['timeseries'] as $hours) {
				if( $hours['time'] == $datestring ) {
					if( isset($hours['data']['instant']['details']['cloud_area_fraction_low']) ){
						return (string) $hours['data']['instant']['details']['cloud_area_fraction_low'];
					}
				}
			}
		}
		log_message('warning', 'Cloud area fraction low value from Yr.no not exists!');
		return false;
	}



	/**
	 * Get cloud area fraction medium in %
	 *
	 * @param object new Time('now', 'UTC', 'cs_CZ');
	 * @return string Cloud area medium fraction
	 */
	public function getCloudAreaFractionMedium( $datetime ) {
		$datestring = $datetime->toDateString() . 'T' . $datetime->getHour() . ':00:00Z';
		$datestring = $this->_modifyHour( $datestring );

		if( $this->data !== false ) {
			foreach( $this->data['properties']['timeseries'] as $hours) {
				if( $hours['time'] == $datestring ) {
					if( isset($hours['data']['instant']['details']['cloud_area_fraction_medium']) ){
						return (string) $hours['data']['instant']['details']['cloud_area_fraction_medium'];
					}
				}
			}
		}
		log_message('warning', 'Cloud area fraction medium value from Yr.no not exists!');
		return false;
	}



	/**
	 * Get cloud area fraction high in %
	 *
	 * @param object new Time('now', 'UTC', 'cs_CZ');
	 * @return string Cloud area high fraction
	 */
	public function getCloudAreaFractionHigh( $datetime ) {
		$datestring = $datetime->toDateString() . 'T' . $datetime->getHour() . ':00:00Z';
		$datestring = $this->_modifyHour( $datestring );

		if( $this->data !== false ) {
			foreach( $this->data['properties']['timeseries'] as $hours) {
				if( $hours['time'] == $datestring ) {
					if( isset($hours['data']['instant']['details']['cloud_area_fraction_high']) ){
						return (string) $hours['data']['instant']['details']['cloud_area_fraction_high'];
					}
				}
			}
		}
		log_message('warning', 'Cloud area fraction high value from Yr.no not exists!');
		return false;
	}



	/**
	 * Get fog area fraction in %
	 *
	 * @param object new Time('now', 'UTC', 'cs_CZ');
	 * @return string Fog area high fraction
	 */
	public function getFogAreaFraction( $datetime ) {
		$datestring = $datetime->toDateString() . 'T' . $datetime->getHour() . ':00:00Z';
		$datestring = $this->_modifyHour( $datestring );

		if( $this->data !== false ) {
			foreach( $this->data['properties']['timeseries'] as $hours) {
				if( $hours['time'] == $datestring ) {
					if( isset($hours['data']['instant']['details']['fog_area_fraction']) ){
						return (string) $hours['data']['instant']['details']['fog_area_fraction'];
					}
				}
			}
		}
		log_message('warning', 'Fog area fraction value from Yr.no not exists!');
		return false;
	}



	/**
	 * Get dew point in °C
	 *
	 * @param object new Time('now', 'UTC', 'cs_CZ');
	 * @return string Dew point in °C
	 */
	public function getDewPoint( $datetime ) {
		$datestring = $datetime->toDateString() . 'T' . $datetime->getHour() . ':00:00Z';
		$datestring = $this->_modifyHour( $datestring );

		if( $this->data !== false ) {
			foreach( $this->data['properties']['timeseries'] as $hours) {
				if( $hours['time'] == $datestring ) {
					if( isset($hours['data']['instant']['details']['dew_point_temperature']) ){
						return (string) $hours['data']['instant']['details']['dew_point_temperature'];
					}
				}
			}
		}
		log_message('warning', 'Dew point value from Yr.no not exists!');
		return false;
	}



	/**
	 * Get humidity in %
	 *
	 * @param object new Time('now', 'UTC', 'cs_CZ');
	 * @return string Humidity in %
	 */
	public function getHumidity( $datetime ) {
		$datestring = $datetime->toDateString() . 'T' . $datetime->getHour() . ':00:00Z';
		$datestring = $this->_modifyHour( $datestring );

		if( $this->data !== false ) {
			foreach( $this->data['properties']['timeseries'] as $hours) {
				if( $hours['time'] == $datestring ) {
					if( isset($hours['data']['instant']['details']['relative_humidity']) ){
						return (string) $hours['data']['instant']['details']['relative_humidity'];
					}
				}
			}
		}
		log_message('warning', 'Humidity value from Yr.no not exists!');
		return false;
	}



	/**
	 * Get UV index
	 *
	 * @param object new Time('now', 'UTC', 'cs_CZ');
	 * @return string UV index
	 */
	public function getUVindex( $datetime ) {
		$datestring = $datetime->toDateString() . 'T' . $datetime->getHour() . ':00:00Z';
		$datestring = $this->_modifyHour( $datestring );

		if( $this->data !== false ) {
			foreach( $this->data['properties']['timeseries'] as $hours) {
				if( $hours['time'] == $datestring ) {
					if( isset($hours['data']['instant']['details']['ultraviolet_index_clear_sky']) ){
						return (string) $hours['data']['instant']['details']['ultraviolet_index_clear_sky'];
					}
				}
			}
		}
		log_message('warning', 'UV index value from Yr.no not exists!');
		return false;
	}

	/**
	 * Get wind speed in m/s
	 *
	 * @param object new Time('now', 'UTC', 'cs_CZ');
	 * @return string Wind speed in m/s
	 */
	public function getWindSpeed( $datetime ) {
		$datestring = $datetime->toDateString() . 'T' . $datetime->getHour() . ':00:00Z';
		$datestring = $this->_modifyHour( $datestring );

		if( $this->data !== false ) {
			foreach( $this->data['properties']['timeseries'] as $hours) {
				if( $hours['time'] == $datestring ) {
					if( isset($hours['data']['instant']['details']['wind_speed']) ){
						return (string) $hours['data']['instant']['details']['wind_speed'];
					}
				}
			}
		}
		log_message('warning', 'Wind speed value from Yr.no not exists!');
		return false;
	}

	/**
	 * Get wind direction in °
	 *
	 * @param object new Time('now', 'UTC', 'cs_CZ');
	 * @return string Wind direction in °
	 */
	public function getWindDirectionInDegrees( $datetime ) {
		$datestring = $datetime->toDateString() . 'T' . $datetime->getHour() . ':00:00Z';
		$datestring = $this->_modifyHour( $datestring );

		if( $this->data !== false ) {
			foreach( $this->data['properties']['timeseries'] as $hours) {
				if( $hours['time'] == $datestring ) {
					if( isset($hours['data']['instant']['details']['wind_from_direction']) ){
						return (string) $hours['data']['instant']['details']['wind_from_direction'];
					}
				}
			}
		}
		log_message('warning', 'Wind direction value from Yr.no not exists!');
		return false;
	}



	/**
	 * Get icon name
	 *
	 * https://api.met.no/weatherapi/weathericon/2.0/documentation
	 *
	 * @param object new Time('now', 'UTC', 'cs_CZ');
	 * @return string Icon name
	 */
	public function getIconName( $datetime ) {
		//d($datetime);

		$datestring = $datetime->toDateString() . 'T' . $datetime->getHour() . ':00:00Z';
		$datestring = $this->_modifyHour( $datestring );
		if( $this->data !== false ) {
			foreach( $this->data['properties']['timeseries'] as $hours) {
				if( $hours['time'] == $datestring ) {
					if( isset($hours['data']['next_1_hours']['summary']['symbol_code']) ){
						return (string) $hours['data']['next_1_hours']['summary']['symbol_code'];
					}
				}
			}
		}
		log_message('warning', 'Icon name value from Yr.no not exists!');
		return 'noicon';
	}



	/**
	 * Get last update JSON data
	 *
	 * @return string
	 */
	public function getLastUpdate() {
		if( $this->data !== false ) {
			if( isset($this->data['properties']['meta']['updated_at']) ){
				$newDate = $this->data['properties']['meta']['updated_at'];
				return (string) $newDate;
			}
		}
		log_message('warning', 'Last update value from Yr.no not exists!');
		return false;
	}



	/**
	 * Get wind direction in °
	 *
	 * @param object new Time('now', 'UTC', 'cs_CZ');
	 * @return string Wind direction in °
	 */
	public function getWindDirectionInWorldSides( $datetime ) {
		$degrees = $this->getWindDirectionInDegrees( $datetime );
		if( $degrees != false ) {
			$cwd = (float) $degrees;
			$ss = '';
			if( $cwd >= 0 && $cwd <= 22.5){
				$ss = 'N';
			}elseif( $cwd > 22.5 && $cwd <= 67.5){
				$ss = 'NE';
			}elseif( $cwd > 67.5 && $cwd <= 112.5){
				$ss = 'E';
			}elseif( $cwd > 112.5 && $cwd <= 157.5){
				$ss = 'SE';
			}elseif( $cwd > 157.5 && $cwd <= 202.5){
				$ss = 'S';
			}elseif( $cwd > 202.5 && $cwd <= 247.5){
				$ss = 'SW';
			}elseif( $cwd > 247.5 && $cwd <= 292.5){
				$ss = 'W';
			}elseif( $cwd > 292.5 && $cwd <= 337.5){
				$ss = 'NW';
			}elseif( $cwd > 337.5 && $cwd <= 360){
				$ss = 'N';
			}else{
				$ss = false;
				log_message('warning', 'Wind direction value from Yr.no is wrong!');
			}
			return $ss;
		}
		log_message('warning', 'Wind direction value from Yr.no not exists!');
		return false;
	}



	/**
	 * Get precipitation amount in mm
	 *
	 * @param object new Time('now', 'UTC', 'cs_CZ');
	 * @return string Precipitation amount in mm
	 */
	public function getPrecipitationAmount( $datetime ) {
		$datestring = $datetime->toDateString() . 'T' . $datetime->getHour() . ':00:00Z';
		$datestring = $this->_modifyHour( $datestring );

		if( $this->data !== false ) {
			foreach( $this->data['properties']['timeseries'] as $hours) {
				if( $hours['time'] == $datestring ) {
					if( isset($hours['data']['next_1_hours']['details']['precipitation_amount']) ){
						return (string) $hours['data']['next_1_hours']['details']['precipitation_amount'];
					}
				}
			}
		}
		log_message('warning', 'Precipitation Amount value from Yr.no not exists!');
		return false;
	}



	/**
	 * Download JSON data from Yr.no
	 *
	 * @return string|false
	 */
    private function _download() {
		$ua = $this->_getUserAgent();
		$opts = [
			'http' => [
				'method' => 'GET',
				'header' => "User-Agent: $ua\r\n",
			],
		];

		$context = stream_context_create( $opts );
		return (string) @file_get_contents( $this->_getApiUrl(), false, $context );
	}



	/**
	 * Modify hour from format X into XX
	 *
	 * @param string Hour
	 * @return string Modifying hour
	 */
	private function _modifyHour( $hour ) {
		$old = array('T0:', 'T1:', 'T2:', 'T3:', 'T4:', 'T5:', 'T6:', 'T7:', 'T8:', 'T9:');
		$new = array('T00:', 'T01:', 'T02:', 'T03:', 'T04:', 'T05:', 'T06:', 'T07:', 'T08:', 'T09:');
		return (string) str_replace($old, $new, $hour);
	}



	/**
	 * Get complete Yr.no API URL
	 *
	 * @return string
	 */
	private function _getApiUrl() {
		// Building API url
		$this->location = 'lat=' . $this->lat . '&lon=' . $this->lon;
		$this->api_url = 'https://api.met.no/weatherapi/locationforecast/2.0/complete?' . $this->location;

		// setting altitude for API url if not empty
		if( !empty( $this->altitude ) ) {
			$this->api_url = $this->api_url . '&altitude=' . $this->altitude;
		}
		return (string) $this->api_url;
	}



	/**
	 * Get User agent for fetching data
	 *
	 * @return string
	 */
	private function _getUserAgent() {
		return (string) $this->user_agent;
	}
}
