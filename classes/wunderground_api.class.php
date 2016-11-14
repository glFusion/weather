<?php 
/**
*   Class to interface with Weather Underground's weather API
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2012 Lee Garner <lee@leegarner.com>
*   @package    weather
*   @version    1.0.4
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*               GNU Public License v2 or later
*   @filesource
*/

require_once dirname(__FILE__) . '/base_api.class.php';

/**
*   Class to manage Weather Underground
*   @since  version 1.0.0
*   @package weather
*/
class Weather extends WeatherBase
{
    /**
    *   Constructor.
    *   Get the weather for the location string, if specified
    *
    *   @param  string  $loc    Optional location to retrieve.
    */
    public function __construct($loc = '')
    {
        global $_CONF, $_CONF_WEATHER;

        $this->api_name = 'APIXU';
        $this->api_code = 'wu';

        parent::__construct($loc);

        $this->url = 'http://api.wunderground.com/api/' .
                $_CONF_WEATHER['api_key_wu'] .
                '/conditions/forecast10day/q/';

        if (!empty($loc)) {
            // Get the weather for the specified location, if requested
            $this->Get($loc);
        }
    }


    /**
    *   Format a url for this provider.
    *   Separates the location string on commas, removes extra whitespace,
    *   and converts internal spaces to underscores. Then the components are
    *   assembled in reverse order separated by slashes.
    *   Example: "Los Angeles, CA" becomes "CA/Los_Angeles.json"
    *
    *   @param  string  $loc    Location
    *   @return string      Full API URL
    */
    protected function _makeUrl($loc)
    {
        $this->location = $loc;
        $loc = explode(',', $this->location);
        foreach ($loc as $idx=>$loc_elem) {
            $loc_elem = trim($loc_elem);
            $loc[$idx] = str_replace(' ', '_', $loc_elem);
        }
        $loc = array_reverse($loc);
        $loc = implode('/', $loc);
        return $this->url . $loc . '.json';
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
    *   Get the data into standard arrays for cache storage and display.
    *   Collects data from the info, current and forecast variables
    *   depending on the format of the weather provider
    *
    *   @return array   Data array
    */
    public function getData()
    {
        global $_CONF_WEATHER;

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
            // Weather Underground provides only 3 or 10-day forecasts.
            // We want 5 days.
            for ($i = 0; $i < $this->fc_days; $i++) {
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

}   // class Weather

?>
