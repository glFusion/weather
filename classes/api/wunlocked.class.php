<?php
/**
 * Class to interface with Weather Unlocked's weather API.
 * https://developer.weatherunlocked.com/
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2018-2021 Lee Garner <lee@leegarner.com>
 * @package     weather
 * @version     v2.0.2
 * @since       v2.0.0
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Weather\api;
use Weather\Models\WeatherData;
use Weather\Models\Forecast;


/**
 * Class for Weather Unlocked.
 * @since   v2.0.0
 * @package weather
 */
class wunlocked extends \Weather\API
{
    /**
     * URL parameters to follow location.
     * @var string */
    private $params = '';


    /**
     * Constructor.
     * Get the weather for the location string, if specified.
     *
     * @param   string  $loc    Optional location to retrieve.
     */
    public function __construct($loc = '')
    {
        global $_CONF, $_CONF_WEATHER;

        $this->api_name = 'WeatherUnlocked';
        $this->api_code = 'wunlocked';
        $this->configs = array(
            'app_id',
            'api_key',
        );

        parent::__construct($loc);
        $this->url = 'http://api.weatherunlocked.com/api/forecast/';
        $this->params = '?app_id=' . $_CONF_WEATHER['app_id_wunlocked'] .
            '&app_key=' . $_CONF_WEATHER['api_key_wunlocked'];

        if (!empty($loc)) {
            // Get the weather for the specified location, if requested
            $this->Get($loc);
        }
    }


    /**
     * Format a url for this provider.
     * Separates the location string on commas, removes extra whitespace,
     * and converts internal spaces to underscores. Then the components are
     * assembled in reverse order separated by slashes.
     * Example: "Los Angeles, CA" becomes "CA/Los_Angeles.json"
     *
     * @param   string  $loc    Location
     * @return  string      Full API URL
     */
    protected function _makeUrl($loc)
    {
        global $_CONF_WEATHER;

        if ($loc['type'] == 'coord') {
            $this->location = implode(',', $loc['parts']);
        } elseif (($loc['type'] = 'city' || $loc['type'] == 'address')) {
            if (is_array($loc['parts'])) {
                if (!empty($loc['parts']['postal'])) {
                    if (empty($loc['parts']['country'])) {
                        $loc['parts']['country'] = $_CONF_WEATHER['def_country'];
                    }
                    $this->location = $loc['parts']['country'] . '.' . $loc['parts']['postal'];
                }
            } else {
                $status = LGLIB_invokeService('locator', 'getCoords', $loc['parts'], $coords, $svc_msg);
                if ($status == PLG_RET_OK) {
                    $this->location = implode(',', $coords);
                }
            }
        } else {
            $this->location = urlencode($loc['parts']);
        }
        return $this->url . $this->location . $this->params;
    }


    /**
     * Parse the returned weather information.
     * This function just puts the forecast info into some "shortcut"
     * variables.
     *
     * @return  boolean     True if all values are objects, false otherwise
     */
    protected function Parse()
    {
        $this->info = NULL;
        $this->current = $this->response->Days[0];
        $this->forecast = $this->response->Days;
        if (!is_object($this->current)) {
            $this->logError('Invalid current data, should be object ');
            $this->error = 1;
            return false;
        } elseif (!is_array($this->forecast)) {
            $this->logError('Invalid forecast data, should be array:');
            $this->logError('received: ' . var_export($this->forecast),true);
            $this->error = 1;
            return false;
        } else {
            return true;
        }
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
        global $_CONF, $_CONF_WEATHER;

        $icon = $this->current->Timeframes[0]->wx_icon;
        $this->data = new WeatherData;
        $this->data->status = 0;    // got good weather
        $this->data->city  = '';
        $this->data->api = $this->api_code;
        $this->data->Current->temp_F = (string)$this->current->Timeframes[0]->temp_f;
        $this->data->Current->temp_C = (string)$this->current->Timeframes[0]->temp_c;
        $this->data->Current->condition = (string)$this->current->Timeframes[0]->wx_desc;
        $this->data->Current->icon = WEATHER_URL . '/images/icons/' . $icon;
        $this->data->Current->icon_name = $icon;
        $this->data->Current->humidity = (string)$this->current->Timeframes[0]->humid_pct;
        $this->data->Current->wind_M = (string)$this->current->Timeframes[0]->windspd_mph . 'mph ' .
                        (string)$this->current->Timeframes[0]->winddir_compass;
        $this->data->Current->wind_K = (string)$this->current->Timeframes[0]->windspd_kmh . 'kph ' .
                        (string)$this->current->Timeframes[0]->winddir_compass;

        if (is_array($this->forecast)) {
            // Weather Underground provides only 3 or 10-day forecasts.
            // We want 5 days.
            for ($i = 1; $i < $this->fc_days; $i++) {
                if (!isset($this->forecast[$i])) {
                    break;
                }
                $fc = $this->forecast[$i];
                list($day, $month, $year) = explode('/', $fc->date);
                $Dt = new \Date($year . '-' . $month . '-' . $day, $_CONF['timezone']);
                $icon = (string)$fc->Timeframes[3]->wx_icon;
                $Forecast = new Forecast;
                $Forecast->day = $Dt->format('D');
                $Forecast->lowF = (string)$fc->temp_min_f;
                $Forecast->highF = (string)$fc->temp_max_f;
                $Forecast->lowC = (string)$fc->temp_min_c;
                $Forecast->highC = (string)$fc->temp_max_c;
                $Forecast->condition = (string)$fc->Timeframes[3]->wx_desc;
                $Forecast->icon = WEATHER_URL . '/images/icons/' . $icon;
                $Forecast->icon_name = $icon;
                $Forecast->wind_M = (string)$fc->Timeframes[3]->windspd_mph . 'mph ' .
                    (string)$fc->Timeframes[3]->winddir_compass;
                $Forecast->wind_K = (string)$fc->Timeframes[3]->windspd_kmh . 'kph ' .
                    (string)$fc->Timeframes[3]->winddir_compass;
                $this->data->Forecasts[] = $Forecast;
            }
        }
        return $this->data;
    }


    /**
     * Return the linkback url to the weather provider.
     * Weather Underground requires this for the free API
     *
     * @param   string  $format     'page' or 'block' for horiz or vert image
     * @return  string  Linkback tag
     */
    public function linkback($format='page')
    {
        $retval = COM_createLink(
            $this->api_name,
            'https://developer.weatherunlocked.com/',
            array(
                'target' => '_blank',
            )
        );
        return $retval;
    }

}
