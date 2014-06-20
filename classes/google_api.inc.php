<?php 
/**
*   Class to interface with Google's weather API
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2011 Lee Garner <lee@leegarner.com>
*   @package    weather
*   @version    0.1.3
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*               GNU Public License v2 or later
*   @filesource
*/


/**
*   Class to manage Google weather queries
*   @package weather
*/
class Weather
{
    // Our variables are all available publically
    public $response;
    public $location;
    public $current;
    public $forecast;
    public $info;
    public $http_code;
    public $error = 0;

    private $have_fopen = false;
    private $have_curl = false;

    /**
    *   Constructor.
    *   Get the weather for the location string, if specified
    *
    *   @param  string  $loc    Optional location to retrieve.
    */
    function __construct($loc = '')
    {
        global $_CONF;

        $iso_lang = empty($_CONF['iso_lang']) ? 'en' : 
                    urlencode($_CONF['iso_lang']);
        $this->url = 
            "http://www.google.com/ig/api?hl={$iso_lang}&referrer=googlecalendar&weather=";

        if (in_array('curl', get_loaded_extensions())) {
            // CURL is preferred since it handles other character sets better.
            $this->have_curl = true;
        } elseif (ini_get('allow_url_fopen') == 1) {
            $this->have_fopen = true;
        }

        if (!empty($loc)) {
            // Get the weather for the specified location, if requested
            $this->Get($loc);
        }
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
        if (!empty($loc)) {
            // Sanitize the location
            $this->location = trim(urlencode($loc));
        }

        if (empty($this->location)) {
            $this->error = WEATHER_ERR_NOTFOUND;
            return false;
        }
        $url = $this->url . $this->location;
        $xml_str = $this->GetWeather($url);
        if (empty($xml_str) || !substr($xml_str, 0, 5) != '<?xml') {
            $this->error = WEATHER_ERR_API;
            COM_errorLog("Empty weather data from Google");
            return false;
        }

        /*$xml_str = str_replace(
            '<?xml version="1.0"?>', 
            '<?xml version="1.0" encoding="ISO-8859-1"?>', 
            $xml_str);*/

        try {
            $xml = new SimplexmlElement($xml_str);
        } catch (Exception $e) {
            $this->error = WEATHER_ERR_API;
            COM_errorLog("error creating xml element: $xml_str");
            return false;
        }
        if ($xml->weather->problem_cause) {
            $this->error = WEATHER_ERR_API;
            return false;
        } else {
            $this->response = $xml->weather;
            if (!$this->Parse()) {
                $this->error = WEATHER_ERR_API;
                return false;
            }
        }
        return true;
    }


    /**
    *   Parse the returned weather information.
    *   This function just puts the forecast info into some "shortcut"
    *   variables.
    *
    *   @return boolean     True if all values are objects, false otherwise
    */
    private function Parse()
    {
        $this->info = $this->response->forecast_information;
        $this->current = $this->response->current_conditions;
        $this->forecast = $this->response->forecast_conditions;

        //if (!is_array($this->info) || !is_array($this->current) ||
        //    !is_array($this->forecast)) {
        if (!is_object($this->info) || !is_object($this->current) ||
            !is_object($this->forecast)) {
            $this->error = WEATHER_ERR_API;
            return false;
        } else {
            return true;
        }
    }
    

    /**
    *   Fetch data from a remote server using php-curl
    *
    *   @param  string  $url    URL to retrieve
    *   @return string      Data from website
    */
    private function GetWeather($url)
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
            //curl_setopt($ch, CURLOPT_REFERRER,      'googlecalendar');
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
    *   Return the URL for a give icon string.
    *   If the provide string is a relative URL, then www.google.com is prepended.
    *
    *   @param  string  $icon   Icon URL returned from the weather API
    *   @return string          Fully-qualified URL to the icon image
    */
    public static function getIcon($icon)
    {
        if (function_exists('CUSTOM_weatherIcon')) {
            $icon = CUSTOM_weatherIcon($icon);
        } elseif ($icon[0] == '/') {
            $icon = 'http://www.google.com' . $icon;
        }
        return $icon;
    }


    /**
    *   Return the linkback url to the weather provider.
    *   Google does not require this.
    *
    *   @return string  Linkback tag
    */
    public static function linkback()
    {
        return '';
    }


}   // class Weather

?>
