# Forecast from Yr.no for Codeigniter 4

A library for working with weather data from Yr.no API service for Codeigniter 4

## Usage:
```php
$this->weather = new \App\Libraries\YrNo();                // Initialize library
$this->weather->setLatitude('49.1547');                    // Latitude settings
$this->weather->setLongitude('19.4678');                   // Longitude settings
$this->weather->setUserAgent('Test Weather App v1.0');     // User agent settings [more info](https://developer.yr.no/doc/GettingStarted/)

$json = $this->weather->getData();                         // Getting json data (for cache etc...)
$this->weather->parseData( $json );                        // Proccessing data

$time = new Time('now', 'UTC');                            // Setting time for forecast
$temperature = $this->weather->getTemperature( $time );    // Getting temperature value in celsius

$time = $time->addHours(2);                                // Getting temperature in 2 hours
$temperature = $this->weather->getTemperature( $time );
etc...
```

## Available public methods:

### Library settings
`$this->weather->setLatitude('49.1547')` Latitude settings

`$this->weather->setLatitude('19.4678')` Longitude settings

`$this->weather->setAltitude('254')` Altitude settings

`$this->weather->setUserAgent('User agent string')` User agent settings


### Data output
`$this->weather->getTemperature( $time )`: Temperature (°C)

`$this->weather->getPressure( $time )`: Pressure (hPa)

`$this->weather->getHumidity( $time )`: Humidity (%)

`$this->weather->getWindSpeed( $time )`: Wind speed (m/s)

`$this->weather->getWindDirectionInWorldSides( $time )`: Wind direction from world side (N or SE etc.)

`$this->weather->getWindDirectionInDegrees( $time )`: Wind direction in degrees (0-360°)

`$this->weather->getFogAreaFraction( $time )`: Fog

`$this->weather->getDewPoint( $time )`: Dew point (°C)

`$this->weather->getCloudAreaFraction( $time )`: Clouds all (%)

`$this->weather->getCloudAreaFractionLow( $time )`: Clouds low (%)

`$this->weather->getCloudAreaFractionMedium( $time )`: Clouds medium (%)

`$this->weather->getCloudAreaFractionHigh( $time )`: Clouds high (%)

`$this->weather->getUVindex( $time )`: UV index

`$this->weather->getPrecipitationAmount( $time )`: Precipitation amount (mm)

`$this->weather->getIconName( $time )`: Name of the weather icon ([https://api.met.no/weatherapi/weathericon/2.0/documentation](https://api.met.no/weatherapi/weathericon/2.0/documentation))

`$this->weather->getLastUpdate()`: Last forecast update time

`$this->weather->getData()`: JSON data from Yr.no API


## Support
**Forecast from Yr.no** is open source and free. Donate for coffee or just like that:

BTC: `bc1q03v5la7uvcwxr7z4qn03ex6n5edju6zv4n6ppt`

## License
**Forecast from Yr.no** is open source software licensed under the [MIT license](https://tldrlegal.com/license/mit-license).
