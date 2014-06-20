<?php 
/**
*   Class to interface with WorldWeatherOnline's weather API
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2012 Lee Garner <lee@leegarner.com>
*   @package    weather
*   @version    1.0.0
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*               GNU Public License v2 or later
*   @filesource
*/


/**
*   Class to manage World Weather Online
*   @since  version 0.1.3
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
    private $city;

    /**
    *   Constructor.
    *   Get the weather for the location string, if specified
    *
    *   @param  string  $loc    Optional location to retrieve.
    */
    function __construct($loc = '')
    {
        global $_CONF, $_CONF_WEATHER;

        if (empty($_CONF_WEATHER['api_key_wwo'])) {
            if (SEC_inGroup('Root')) {
                $this->error = WEATHER_ERR_KEYMISSING;
            } else {
                $this->error = WEATHER_ERR_API;
            }
            return;
        }

        //$iso_lang = empty($_CONF['iso_lang']) ? 'en' : 
        //            rawurlencode($_CONF['iso_lang']);
        //$this->url = 'http://free.worldweatheronline.com/feed/weather.ashx?format=json&num_of_days=5&key=' . $_CONF_WEATHER['api_key_wwo'];
        $this->url = 'http://api.worldweatheronline.com/free/v1/weather.ashx?format=json&num_of_days=5&key=' . $_CONF_WEATHER['api_key_wwo'];
        //$this->locUrl = 'http://www.worldweatheronline.com/feed/search.ashx?format=json&num_of_results=3&popular=yes&key=' . $_CONF_WEATHER['api_key_wwo'];
        $this->locUrl = 'http://api.worldweatheronline.com/free/v1/search.ashx?format=json&num_of_results=3&popular=yes&key=' . $_CONF_WEATHER['api_key_wwo'];
 
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
            $this->location = trim(rawurlencode($loc));
        }

        if (empty($this->location)) {
            $this->error = WEATHER_ERR_NOTFOUND;
            return false;
        }

        // Look up the location to get nice city, state display
        $json = $this->getWeather($this->locUrl . '&query=' . $this->location);
        if (!empty($json)) {
            $loc_info = json_decode($json);
        }
        if (is_object($loc_info)) {
            $info = $loc_info->search_api->result[0];
            $this->city = $info->areaName[0]->value;
            if (isset($info->region[0]->value)) {
                $this->city .= ', ' . $info->region[0]->value;
            }
            $this->location = $info->latitude . ',' . $info->longitude;
        }
        $url = $this->url . '&q=' . $this->location;
        $json = $this->GetWeather($url);
        if (empty($json)) {
            $this->error = WEATHER_ERR_API;
            COM_errorLog("Empty weather data from WorldWeatherOnline");
            return false;
        }

        //$A = json_decode($json, true);
        //if (!is_array($A)) {
        $A = json_decode($json);
        if (!is_object($A)) {
            $this->error = WEATHER_ERR_API;
            COM_errorLog("error creating xml element: $xml_str");
            return false;
        }
        $this->response = $A->data;
        if (!$this->Parse()) {
            $this->error = WEATHER_ERR_API;
            return false;
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
        $this->info = $this->response->request[0];
        $this->info->city = $this->city;
        $this->current = $this->response->current_condition[0];
        $this->forecast = $this->response->weather;
        if (!is_object($this->current) || !is_array($this->forecast)) {
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
    public function getData()
    {
        //list($city, $country) = explode(', ', $this->info->query);
        $data = array(
            'info' => array(
                'city'  => $this->info->city,
                //'postal' => (string)$w->info->postal_code['data'],
                'date_time' => date('Y-m-d') . (string)$this->current->observation_time,
                'ts' => time(),
            ),
            'current' => array(
                'temp_f'   => (string)$this->current->temp_F,
                'temp_c'  => (string)$this->current->temp_C,
                'condition' => (string)$this->current->weatherDesc[0]->value,
                'icon'  => (string)$this->current->weatherIconUrl[0]->value,
                'humidity' => (string)$this->current->humidity,
                'wind_M' => (string)$this->current->windspeedMiles . 'mph ' .
                        (string)$this->current->winddir16Point,
                'wind_K' => (string)$this->current->windspeedKmph . 'kph ' .
                        (string)$this->current->winddir16Point,
            ),
            'forecast' => array(),
        );
        if (is_array($this->forecast)) {
            foreach ($this->forecast as $fc) {
                $data['forecast'][] = array(
                    'day'    => date('D', strtotime($fc->date)),
                    'lowF'   => (string)$fc->tempMinF,
                    'highF'  => (string)$fc->tempMaxF,
                    'lowC'   => (string)$fc->tempMinC,
                    'highC'  => (string)$fc->tempMaxC,
                    'condition' => (string)$fc->weatherDesc[0]->value,
                    'icon'  => (string)$fc->weatherIconUrl[0]->value,
                    'wind_M' => (string)$fc->windspeedMiles . 'mph ' .
                                (string)$fc->winddir16Point,
                    'wind_K' => (string)$fc->windspeedKmph . 'kph ' .
                                (string)$fc->winddir16Point,
                );
            }
        }
        return $data;
    }


    /**
    *   Return the linkback url to the weather provider.
    *   World Weather Online requires this for the free API
    *
    *   @param  string  $format     Not used, text only is returned
    *   @return string  Linkback tag
    */
    public static function linkback($format='page')
    {
        return '<a class="piWeatherLinkback" ' .
            'href="http://www.worldweatheronline.com/" ' .
            'title="Free local weather content provider" ' .
            'target="_blank">World Weather Online</a>';
    }


    /**
    *   Return the URL for a give icon string.
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
