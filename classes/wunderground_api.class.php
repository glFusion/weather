<?php 
/**
*   Class to interface with Weather Underground's weather API
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
*   Class to manage Weather Underground
*   @since  version 1.0.0
*   @package weather
*/
class Weather
{
    // Our variables are all available publically, though typically not used
    public $response;
    public $location;
    public $current;
    public $forecast;
    public $fc_text;
    public $info;
    public $http_code;
    public $error = 0;
    public $api_name;

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
        global $_CONF, $_CONF_WEATHER;

        $this->api_name = 'Weather Underground';

        if (empty($_CONF_WEATHER['api_key_wu'])) {
            if (SEC_inGroup('Root')) {
                $this->error = WEATHER_ERR_KEYMISSING;
            } else {
                $this->error = WEATHER_ERR_API;
            }
            return;
        }

        //$iso_lang = empty($_CONF['iso_lang']) ? 'en' : 
        //            rawurlencode($_CONF['iso_lang']);
        $this->url = 'http://api.wunderground.com/api/' .
                $_CONF_WEATHER['api_key_wu'] .
                '/conditions/forecast10day/q/';
                //'/geolookup/conditions/forecast10day/q/';
 
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
            $this->location = $loc;
        }

        if (empty($this->location)) {
            COM_errorLog('Empty location provided');
            $this->error = WEATHER_ERR_NOTFOUND;
            return false;
        }

        $url = $this->url . trim(rawurlencode($this->location)) . '.json';
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
            COM_errorLog('error parsing json object');
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
    protected function Parse()
    {
        $this->info = $this->response->current_observation->display_location;
        $this->current = $this->response->current_observation;
        $this->forecast = $this->response->forecast->simpleforecast->forecastday;
        $this->fc_text = $this->response->forecast->txt_forecast->forecastday;
        if (!is_object($this->current)) {
            COM_errorLog('Invalid current data, should be object ');
            COM_errorLog('Current object: ' . print_r($this->current, true));
            return false;
        } elseif (!is_array($this->forecast)) {
            COM_errorLog('Invalid forecast data, should be array:');
            COM_errorLog('Forecaset array: ' . print_r($this->forecast, true));
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
                'city'  => $this->info->full,
                //'postal' => (string)$w->info->postal_code['data'],
                'date_time' => date('Y-m-d H:i:s'),
                'ts' => $this->current->observation_epoch,
            ),
            'current' => array(
                'temp_f'   => (string)$this->current->temp_f,
                'temp_c'  => (string)$this->current->temp_c,
                'condition' => (string)$this->current->weather,
                'icon'  => (string)$this->current->icon_url,
                'icon_name' => (string)$this->current->icon,
                'humidity' => (string)$this->current->relative_humidity,
                'wind_M' => (string)$this->current->wind_mph. 'mph ' .
                        (string)$this->current->wind_dir,
                'wind_K' => (string)$this->current->wind_kph . 'kph ' .
                        (string)$this->current->wind_dir,
            ),
            'forecast' => array(),
        );
        if (is_array($this->forecast)) {
            for ($i = 0; $i < 5; $i++) {
                // Weather Underground provides only 3 or 10-day forecasts.
                // We want 5 days.
                if (!isset($this->forecast[$i])) break;
                $fc = $this->forecast[$i];
                $t = 2 * $i;    // index into text descriptions, 2 per entry
                $data['forecast'][] = array(
                    'day'    => $fc->date->weekday_short,
                    'lowF'   => (string)$fc->low->fahrenheit,
                    'highF'  => (string)$fc->high->fahrenheit,
                    'lowC'   => (string)$fc->low->celsius,
                    'highC'  => (string)$fc->high->celsius,
                    'condition' => (string)$fc->conditions,
                    'icon'  => (string)$fc->icon_url,
                    'icon_name' => (string)$this->current->icon,
                    'wind_M' => (string)$fc->avewind->mph . 'mph ' .
                                (string)$fc->avewind->dir,
                    'wind_K' => (string)$fc->avewind->kph . 'kph ' .
                                (string)$fc->avewind->dir,
                    'fc_text_F' => (string)$this->fc_text[$t]->fcttext .
                            ' ' . (string)$this->fc_text[$t+1]->fcttext,
                    'fc_text_C' => (string)$this->fc_text[$t]->fcttext_metric .
                            ' ' . (string)$this->fc_text[$t+1]->fcttext_metric,
                );
            }
        }
        $this->data = $data;
        return $data;
    }


    /**
    *   Return the linkback url to the weather provider.
    *   Weather Underground requires this for the free API
    *
    *   @param  string  $format     'page' or 'block' for horiz or vert image
    *   @return string  Linkback tag
    */
    public static function linkback($format='page')
    {
        global $_CONF_WEATHER;

        if (isset($_CONF_WEATHER['ref_key_wu'])
            && !empty($_CONF_WEATHER['ref_key_wu'])
        ) {
            $refkey = '?apiref=' . rawurlencode($_CONF_WEATHER['ref_key_wu']);
        } else {
            $refkey = '';
        }

        /*if ($format == 'block') {
            $img = '<img src="' . WEATHER_URL .
                '/images/wunderground_logo_4c_vert_107.jpg" ' .
                'width="107" height="64" />';
        } else {*/
            $img = '<img src="' . WEATHER_URL .
                '/images/wunderground_logo_4c_horiz_90.jpg" ' .
                'width="90" height="21" alt="Wunderground Logo" />';
        //}

        $retval = '<a class="piWeatherLinkback" ' .
                'href="http://www.wunderground.com/' . $refkey . '" ' .
                'title="Powered by Weather Underground" ' .
                'target="_blank">' . $img . '</a>';
        return $retval;
    }


    /**
    *   Return the URL for a give icon string.
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
