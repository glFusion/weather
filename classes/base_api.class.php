<?php 
/**
*   Base class for interfacing with weather providers
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2012 Lee Garner <lee@leegarner.com>
*   @package    weather
*   @version    1.0.4
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*               GNU Public License v2 or later
*   @filesource
*/


/**
*   Base weather class
*   @since  version 1.0.4
*   @package weather
*/
abstract class WeatherBase
{
    // Our variables are all available publicly, though probably
    // never used
    public $response;
    public $location;
    public $current;
    public $forecast;
    public $info;
    public $error = 0;
    public $data = array();     // Standard data array
    public $api_name;
    public $api_code;
    public $url;
    public $http_code;
    public $fc_days = 5;        // Number of days to retrieve

    protected $have_fopen = false;
    protected $have_curl = false;

    /**
    *   Constructor.
    *   Get the weather for the location string, if specified
    *
    *   @param  string  $loc    Optional location. Used by child class
    *   @return boolean     True on successful creation, False on config error
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
    *   Get the weather for a given location
    *   If $loc is not specified, use the current saved location
    *
    *   @param  string  $loc    Optional location to retrieve.
    *   @return boolean     True on success, False on failure
    */
    public function Get($loc = '')
    {
        $url = $this->_makeUrl($loc);
        $json = $this->GetWeather($url);

        if (empty($json)) {
            $this->error = WEATHER_ERR_API;
            COM_errorLog('Empty weather data from ' . $this->api_name);
            return false;
        }

        $A = json_decode($json);
        if (!is_object($A)) {
            $this->error = WEATHER_ERR_API;
            COM_errorLog("error decoding json: $json");
            return false;
        }
        $this->response = $A;
        if (!$this->Parse()) {
            $this->error = WEATHER_ERR_API;
            return false;
        }

        return true;
    }


    /**
    *   Format a url for a provider.
    *   Base function just appends a urlencoded location to the base url
    *
    *   @param  string  $loc    Location
    *   @return string      Full API URL
    */
    protected function _makeUrl($loc)
    {
        $this->location = $loc;
        return $this->url . rawurlencode($this->location);
    }

 
    /**
    *   Parse the returned weather information.
    *   This function just puts the forecast info into some "shortcut"
    *   variables. Each API is different.
    *
    *   @return boolean     True if all values are objects, false otherwise
    */
    protected abstract function Parse();


    /**
    *   Fetch data from a remote server using php-curl.
    *   The Parse() function of the class instance is used to parse
    *   the data into class variables.
    *
    *   @param  string  $url    URL to retrieve
    *   @return string      Data from website
    */
    protected function GetWeather($url)
    {
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
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
            curl_setopt($ch, CURLOPT_TIMEOUT,        8);
            /*curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Accept-Charset: utf-8',
                ) );*/
            //curl_setopt($ch, CURLOPT_VERBOSE,        1);

            $result = curl_exec($ch);
            $this->http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
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
    *   Get the data into standard arrays for cache storage and display.
    *   Collects data from the info, current and forecast variables
    *   depending on the format of the weather provider
    *
    *   @return array   Data array
    */
    public abstract function getData();


    /**
    *   Return the linkback url to the weather provider.
    *   World Weather Online requires this for the free API
    *
    *   @param  string  $format     Not used, text only is returned
    *   @return string  Linkback tag
    */
    public static function linkback($format='page')
    {
        return '';
    }


    /**
    *   Return the URL for a given icon string.
    *   Allows the site admin to customize the icon display.
    *   CUSTOM_weatherIcon() must be able to parse icon URLs for this
    *   provider.
    *
    *   @param  string  $icon   Icon URL returned from the weather API
    *   @return string          Fully-qualified URL to the icon image
    */
    public static function getIcon($icon)
    {
        if (function_exists('CUSTOM_weatherIcon')) {
            $icon = CUSTOM_weatherIcon($icon);
        }
        return $icon;
    }

}   // class Weather

?>
