<?php 
/**
*   Class to interface with apixu.com's weather API
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2016 Lee Garner <lee@leegarner.com>
*   @package    weather
*   @version    1.0.4
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*               GNU Public License v2 or later
*   @filesource
*/

require_once dirname(__FILE__) . '/base_api.class.php';

/**
*   Class to use apixu.com weather provider
*   @since  version 1.0.4
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
        $this->api_code = 'apixu';
        $this->max_days = 10;

        parent::__construct($loc);

        $this->url = 'https://api.apixu.com/v1/forecast.json?key=' .
                $_CONF_WEATHER['api_key_apixu'] .
                 '&days=' . $this->fc_days . '&q=';

        if (!empty($loc)) {
            // Get the weather for the specified location, if requested
            $this->Get($loc);
        }
    }


    /**
    *   Format a url for this provider.
    *   Just appends a urlencoded location to the base url
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
    *   variables.
    *
    *   @return boolean     True if all values are objects, false otherwise
    */
    protected function Parse()
    {
        $disp_loc = array(
            $this->response->location->name,
            $this->response->location->region,
        );
        $disp_loc = implode(', ', $disp_loc);
        $this->info = $disp_loc;
        $this->current = $this->response->current;
        $this->forecast = $this->response->forecast->forecastday;
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
    *   depending on the format of the weather provider.
    *
    *   Returns the data array as well as setting the values in $this->data
    *   to be publicly consumed.
    *
    *   @return array   Data array
    */
    public function getData()
    {
        global $_USER, $_CONF_WEATHER, $LANG_APIXU_CONDITIONS, $LANG_DIRECTIONS;

        $this->data = array(
            'info' => array(
                'city'  => $this->info,
                'date_time' => $this->response->location->localtime,
                'ts' => $this->response->location->localtime_epoch,
            ),
            'current' => array(
                'temp_f'   => (string)$this->current->temp_f,
                'temp_c'  => (string)$this->current->temp_c,
                'condition' => $LANG_APIXU_CONDITIONS[(string)$this->current->condition->text],
                'icon'  => (string)$this->current->condition->icon,
                'icon_name' => $LANG_APIXU_CONDITIONS[(string)$this->current->condition->text],
                'humidity' => (string)$this->current->humidity,
                'wind_M' => (string)$this->current->wind_mph. 'mph ' .
                        $LANG_DIRECTIONS[(string)$this->current->wind_dir],
                'wind_K' => (string)$this->current->wind_kph . 'km/h ' .
                        $LANG_DIRECTIONS[(string)$this->current->wind_dir],
            ),
            'forecast' => array(),
        );

        if (is_array($this->forecast)) {
            // Hack to make sure there's a valid timezone. Use the local
            // timezone if available, otherwise the user timezone.
            if (empty($this->response->location->tz_id)) {
                $this->response->location->tz_id = $_USER['tzid'];
            }
            // Create Date object for getting the day name from the timestamp
            $D = new Date('now', $this->response->location->tz_id);
            for ($i = 0; $i < $this->fc_days; $i++) {
                if (!isset($this->forecast[$i])) break;
                $fc = $this->forecast[$i];
                $D->setTimestamp($fc->date_epoch);
                $this->data['forecast'][] = array(
                    'day'    => $D->format('l', true),
                    'lowF'   => (string)$fc->day->mintemp_f,
                    'highF'  => (string)$fc->day->maxtemp_f,
                    'lowC'   => (string)$fc->day->mintemp_c,
                    'highC'  => (string)$fc->day->maxtemp_c,
                    'condition' => $LANG_APIXU_CONDITIONS[(string)$fc->day->condition->text],
                    'icon'  => (string)$fc->day->condition->icon,
                    'icon_name' => $LANG_APIXU_CONDITIONS[(string)$fc->day->condition->text],
                    'wind_M' => (string)$fc->day->maxwind_mph . 'mph ',
                    'wind_K' => (string)$fc->day->maxwind_kph . 'km/h ',
                    'fc_text_F' => '',
                    'fc_text_C' => '',
                );
            }
        }
        return $this->data;
    }

}   // class Weather

?>
