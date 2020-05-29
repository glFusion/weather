<?php
/**
 * Base class for interfacing with weather providers.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2012-2020 Lee Garner <lee@leegarner.com>
 * @package     weather
 * @version     v2.0.0
 * @since       v1.0.4
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Weather;


/**
 * Base weather class
 * @since   v1.0.4
 * @package weather
 */
class API
{
    // Our variables are all available publicly, though probably
    // never used
    /**
     * Response from the API provider.
     * @var string */
    public $response;

    /**
     * Location being requested.
     * @var string */
    public $location;

    /**
     * Current weather info.
     * @var object */
    public $current;

    /**
     * Weather forcast.
     * @var object */
    public $forecast;

    /**
     * Information returned from API provider.
     * @var object */
    public $info;

    /**
     * Error status.
     * @var integer */
    public $error = 0;

    /**
     * Raw data received from provider.
     * @var array */
    public $data = array();     // Standard data array

    /**
     * Name of api provider.
     * @var string */
    public $api_name;

    /** Short code of api provider, e.g. `wu` for `Weather Underground`.
     * NULL if an invalid provider was requested.
     * @var string */
    public $api_code = NULL;

    /**
     * API provider endpoing.
     * @var string */
    public $url;

    /**
     * HTTP coded returned by provider.
     * @var integer */
    public $http_code;

    /**
     * Number of days to retrieve in forecast.
     * @var interger */
    public $fc_days = 5;

    /**
     * Provider configuration items.
     * @var array */
    public $configs = array();

    /**
     * Flag to indicate that fopen() can be used.
     * @var boolean */
    protected $have_fopen = false;

    /**
     * Flag to indicate that Curl is available.
     * @var boolean */
    protected $have_curl = false;


    /**
     * Constructor.
     * Get the weather for the location string, if specified
     *
     * @param   string  $loc    Optional location. Used by child class
     * @return  boolean     True on successful creation, False on config error
     */
    public function __construct($loc = '')
    {
        global $_CONF_WEATHER;

        if (empty($_CONF_WEATHER['api_key_' . $this->api_code])) {
            if (SEC_inGroup('Root')) {
                $this->error = WEATHER_ERR_KEYMISSING;
            } else {
                $this->error = WEATHER_ERR_API;
            }
            return false;
        }
        if (in_array('curl', get_loaded_extensions())) {
            // CURL is preferred since it handles other character sets better.
            $this->have_curl = true;
        } elseif (ini_get('allow_url_fopen') == 1) {
            $this->have_fopen = true;
        }
        return true;
    }


    /**
     * Get an instance of an api provider
     *
     * @param   string  $provider   Provider classname
     * @return  object              Provider object
     */
    public static function getInstance($provider=NULL)
    {
        global $_CONF_WEATHER;
        static $inst = array();

        // Use the configured provider if none specified (normal usage)
        if ($provider === NULL) $provider = $_CONF_WEATHER['provider'];

        if (!array_key_exists($provider, $inst)) {
            if (is_file(__DIR__ . '/api/' . $provider . '.class.php')) {
                $cls = __NAMESPACE__ . '\\api\\' . $provider;
                $inst[$provider] = new $cls;
            } else {
                $inst[$provider] = new self;
            }
        }
        return $inst[$provider];
    }


    /**
     * Get the weather for a given location.
     * If $loc is not specified, use the current saved location.
     * Creates the URL, fetches the weather data, then parses it.
     *
     * @uses    self::_makeUrl()
     * @uses    self::FetchWeather()
     * @uses    self::Parse()
     * @param   string  $loc    Optional location to retrieve.
     * @return  boolean     True on success, False on failure
     */
    public function Get($loc = '')
    {
        $loc = self::makeParams($loc);
        $url = $this->_makeUrl($loc);
        if (!$url) {
            return false;
        }

        $json = $this->FetchWeather($url);
        if (empty($json)) {
            $this->error = WEATHER_ERR_API;
            COM_errorLog('Empty weather data from ' . $this->api_name);
            return false;
        }

        $resp_obj = json_decode($json);
        if (!is_object($resp_obj)) {
            $this->error = WEATHER_ERR_API;
            COM_errorLog("error decoding json: $json");
            return false;
        }
        $this->response = $resp_obj;
        if (!$this->Parse()) {
            $this->error = WEATHER_ERR_API;
            return false;
        }
        return true;
    }


    /**
     * Format a url for a provider.
     * Base function just appends a urlencoded location to the base url
     *
     * @param   string  $loc    Location
     * @return  string      Full API URL
     */
    protected function _makeUrl($loc)
    {
        $this->location = $loc;
        return $this->url . rawurlencode($this->location);
    }


    /**
     * Parse the returned weather information.
     * This function just puts the forecast info into some "shortcut"
     * variables. Each API is different.
     *
     * @return  boolean     True if all values are objects, false otherwise
     */
    protected function Parse()
    {
    }


    /**
     * Fetch data from a remote server using php-curl.
     * The Parse() function of the class instance is used to parse
     * the data into class variables.
     *
     * @param   string  $url    URL to retrieve
     * @return  string      Data from website
     */
    protected function FetchWeather($url)
    {
        global $_CONF_WEATHER;

        if ($this->have_curl) {
            $agent = 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-GB; ' .
                'rv:1.9.1) Gecko/20090624 Firefox/3.5 (.NET CLR ' .
                '3.5.30729)';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,            $url);
            curl_setopt($ch, CURLOPT_USERAGENT,      $agent);
            curl_setopt($ch, CURLOPT_HEADER,         0);
            curl_setopt($ch, CURLOPT_ENCODING,       'gzip');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_FAILONERROR,    1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT,        10);
            //curl_setopt($ch, CURLOPT_TIMEOUT_MS,        1);
            /*curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Accept-Charset: utf-8',
            ) );*/
            //curl_setopt($ch, CURLOPT_VERBOSE,        1);
            if (isset($_CONF_WEATHER['curlopts']) &&
                    is_array($_CONF_WEATHER['curlopts'])) {
            foreach ($_CONF_WEATHER['curlopts'] as $name=>$value) {
                    curl_setopt($ch, $name, $value);
                }
            }
            $result = curl_exec($ch);
            // Check the return value of curl_exec(), too
            $this->http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (curl_errno($ch) || $result == false) {
                COM_errorLog(sprintf('Weather\API::FetchWeather() Error: %d %s',
                    curl_errno($ch), curl_error($ch)));
            }
            curl_close($ch);
            if ($this->http_code != '200') {
                $result = '';
            }
        } elseif ($this->have_fopen) {
            $result = file_get_contents($url, 0);
        } else {
            $result = '';
            COM_errorLog('WEATHER: Missing url_fopen and curl support');
        }
        return $result;
    }


    /**
     * Get the data into standard arrays for cache storage and display.
     * Collects data from the info, current and forecast variables
     * depending on the format of the weather provider
     *
     * @return  array   Data array
     */
    public function getData()
    {
    }


    /**
     * Return the linkback url to the weather provider.
     * World Weather Online requires this for the free API
     *
     * @param   string  $format     Not used, text only is returned
     * @return  string  Linkback tag
     */
    public function linkback($format='page')
    {
        return '';
    }


    /**
     * Return the URL for a given icon string.
     * Allows the site admin to customize the icon display.
     * CUSTOM_weatherIcon() must be able to parse icon URLs for this
     * provider.
     *
     * @param   string  $icon   Icon URL returned from the weather API
     * @return  string          Fully-qualified URL to the icon image
     */
    public function getIcon($icon)
    {
        if (function_exists('CUSTOM_weatherIcon')) {
            $icon = CUSTOM_weatherIcon($icon);
        }
        return $icon;
    }


    /**
     * Retrieve weather information.
     * Checks the cache table first for a recent entry.  If not found,
     * get weather info from Google and update the cache.
     *
     * @uses    self::updateCache()
     * @param   string  $loc    Location to get
     * @param   string  $extra  Optional extra ID for cache key
     * @return  mixed   Array of weather information, or integer error code
     */
    public function getWeather($loc, $extra='')
    {
        global $_TABLES, $_CONF_WEATHER;

        $key = md5(@serialize($loc));
        if ($extra != '') {
            $key .= '_' . $extra;
        }
        $A = Cache::get($key);
        if (!empty($A)) {
            // Got current cache data, return it
            $retval = $A;
        } else {
            // Try to get new data from the provider
            $this->Get($loc);
            if ($this->error > 0) {
                $retval = $this->error;
            } else {
                // Got good data from the weather API, use it and update cache
                $retval = $this->getData();
                Cache::set($key, $retval);
            }
        }
        return $retval;
    }


    /**
     * Sanitize values. Recurse $var if it is an array.
     *
     * @param   mixed   $var    Value or array to sanitize
     * @return  mixed           Sanitized version of $var
     */
    public static function _sanitize($var)
    {
        if (is_array($var)) {
            //run each array item through this function (by reference)
            foreach ($var as &$val) {
                //COM_errorLog("Sanitizing $val");
                $val = self::_sanitize($val);
            }
        } else if (is_string($var)) {   //clean strings
            $var = COM_checkHTML($var);
        } else if (is_null($var)) {   //convert null variables to SQL NULL
            $var = "NULL";
        } else if (is_bool($var)) {   // convert boolean variables to binary boolean
            $var = ($var) ? 1 : 0;
        }
        return $var;
    }


    /**
     * Convert Celsius to Farenheight.
     *
     * @param   float   $temp       Temperature in Celsius
     * @return  float       Temperature in Farenheight
     */
    public static function CtoF($temp)
    {
        return (9/5) * $temp + 32;
    }


    /**
     * Convert Kilometers to Miles.
     *
     * @param   float   $speed      Speed in KPH
     * @return  float       Speed in MPH
     */
    public static function KtoM($speed)
    {
        return sprintf('%.1f', $speed * 0.6213712);
    }


    /**
     * Convert compass degrees to text description.
     *
     * @param   float   $degrees    Compass degrees
     * @return  float       Textual representation of the degrees
     */
    public static function Deg2Dir($degrees)
    {
        $map = array(
            11.25 => 'N',
            33.75 => 'NNE',
            56.25 => 'NE',
            78.75 => 'ENE',
            101.25 => 'E',
            123.75 => 'ESE',
            146.25 => 'SE',
            168.75 => 'SE',
            191.25 => 'S',
            213.75 => 'SSW',
            236.25 => 'SW',
            258.75 => 'SSW',
            281.25 => 'W',
            303.75 => 'WNW',
            326.25 => 'NW',
            348.75 => 'NNW',
            360 => 'N',
        );
        foreach ($map as $deg=>$dir) {
            if ($degrees <= $deg) {
                break;
            }
        }
        return $dir;
    }


    /**
     * Format the parameters into the expected array format.
     * Expected format is
     *
     * ```
     *  'loc' => array(
     *      'type' => coord|address,
     *      'parts' => array(
     *          'street' => street_address,
     *          'city' => city_name,
     *          'state' => state_name,
     *          'country' => country_code,
     *          'postal' => postal_code,
     *      ),
     *  );
     * ```
     *
     * @param   array   $args   Argument array passed from the caller
     * @return  array       Array of properly-formatted arguments for the API
     */
    public static function makeParams($args)
    {
        // Move the arguments under the 'loc' element if not done by
        // the caller.
        if (!isset($args['loc'])) {
            $args = array(
                'loc' => $args,
            );
        }
        if (isset($args['type'])) {
            // Already been processed
            return $args;
        } elseif (
            isset($args['loc']['parts']['lat']) &&
            isset($args['loc']['parts']['lng'])) {
            $type = 'coord';
            $final_parts = $args['loc']['parts'];
        } elseif (isset($args['loc']) && is_string($args['loc'])) {
            $parts = preg_split('/[\s,]+/', $args['loc']);
            // Check if latitude and longitude are specified
            if (
                count($parts) == 2 &&
                is_numeric($parts[0]) &&
                is_numeric($parts[1])
            ) {
                $type = 'coord';
                $final_parts = array(
                    'lat' => (float)$parts[0],
                    'lng' => (float)$parts[1],
                );
            } else {
                // TODO: possible address parsing
                $type = 'address';
                if (is_array($args['loc'])) {
                    if (!isset($args['loc']['country'])) {
                        $args['loc']['country'] = $_CONF_WEATHER['def_country'];
                    }
                }
                $final_parts = $args['loc'];
            }
        } else {
            $type = 'address';
            $final_parts = array();
        }
        // assume address parts. Should already be keyed by
        // field name.
        $loc = array(
            'type' => $type,
            'parts' => $final_parts,
        );
        return $loc;
    }


    /**
     * Check if a valid provider was instantiated.
     *
     * @return  boolean     True if valid, False if not
     */
    public function isValid()
    {
        return $this->api_code !== NULL;
    }

}   // class Weather\API

?>
