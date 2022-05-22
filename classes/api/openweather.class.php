<?php
/**
 * Class to interface with Weatherstack's API.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2020-2021 Lee Garner <lee@leegarner.com>
 * @package     weather
 * @version     v2.0.2
 * @since       v1.1.2
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Weather\api;
use Weather\Models\WeatherData;
use Weather\Models\Forecast;


/**
 * Class to interact with Openweather.
 * @package weather
 */
class openweather extends \Weather\API
{
    /**
     * Constructor.
     * Get the weather for the location string, if specified.
     *
     * @param   string  $loc    Optional location to retrieve.
     */
    public function __construct($loc = '')
    {
        global $_CONF, $_CONF_WEATHER;

        $this->api_name = 'OpenWeather';
        $this->api_code = 'openweather';
        $this->configs = array(
            'api_key',
        );
        parent::__construct($loc);

        $this->url = 'https://api.openweathermap.org/data/2.5/forecast?' .
            'appid=' . $_CONF_WEATHER['api_key_openweather'] .
            '&units=metric';

        if (!empty($loc)) {
            // Get the weather for the specified location, if requested
            $this->Get($loc);
        }
        $this->fc_days = 4;     // Only get 4 days of forecasts
    }


    /**
     * Format a url for this provider.
     *
     * @param   string  $loc    Location
     * @return  string      Full API URL
     */
    protected function _makeUrl($loc)
    {
        global $_CONF_WEATHER;

        $query = '';
        $parts = $loc['parts']; // for clarity
        if ($loc['type'] == 'city' || $loc['type'] == 'address') {
            if (isset($parts['postal']) && !empty($parts['postal'])) {
                $this->location = $parts['postal'];
                $query = 'zip=' . $this->location;
            } elseif (is_array($parts)) {
                unset($parts['postal']); // not used
                if (!isset($parts['country']) || empty($parts['country'])) {
                    $parts['country'] = $_CONF_WEATHER['def_country'];
                }
                $this->location = implode(',', $parts);
                $query = 'q=' . $this->location;
            } else {
                $this->location = $parts;
                $query = 'q=' . $this->location;
            }
        } elseif ($loc['type'] == 'coord') {
            $this->location = implode($parts);
            $query = 'lat=' . $parts['lat'] . '&lon=' . $parts['lng'];
        }
        return $this->url . '&' . $query;
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
        if ($this->response->cod != '200') {
            $this->logError("Error: {$this->response->message}");
            $this->logError("Searching for {$this->location}");
            $this->error = 1;
        } else {
            $this->info = $this->response->city;
            $this->current = $this->response->list[0];
            $this->location = $this->response->city;
            if (!is_object($this->location)) {
                $this->logError('Invalid current data, should be object');
                $this->logError('received ' . $var_export($this->location,true));
                $this->error = 1;
                return false;
            } else {
                $this->fc_text = $this->current->weather[0]->description;
                return true;
            }
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
        global $_CONF;

        $city = $this->location->name;
        if (!empty($this->location->country)) {
            $city .= ', ' . $this->location->country;
        }
        $icon = $this->current->weather[0]->icon;
        $this->data = new WeatherData;
        $this->data->status = 0;    // got good weather
        $this->data->city = $city;
        $this->data->ts = $this->current->dt;
        $this->data->api = $this->api_code;
        $this->data->Current->temp_F = self::CtoF((float)$this->current->main->temp);
        $this->data->Current->temp_C = (string)$this->current->main->temp;
        $this->data->Current->condition = (string)$this->current->weather[0]->description;
        $this->data->Current->icon = '//openweathermap.org/img/wn/' . $icon . '@2x.png';
        $this->data->Current->icon_name = (string)$this->current->weather[0]->description;
        $this->data->Current->humidity = (string)$this->current->main->humidity;
        $this->data->Current->wind_M = self::KtoM($this->current->wind->speed) . 'mph ' .
            self::Deg2Dir($this->current->wind->deg);
        $this->data->Current->wind_K = (string)$this->current->wind->speed . 'kph ' .
            self::Deg2Dir($this->current->wind->deg);
        $days = 0;
        $cbrk_dt = '';
        for ($i = 1; $i < (int)$this->response->cnt && $days < $this->fc_days; $i++) {
            $fc = $this->response->list[$i];
            $Dt = new \Date($fc->dt, $_CONF['timezone']);
            $dt_str = $Dt->format('Y-m-d');
            if ($cbrk_dt == $dt_str) {
                continue;
            }

            $days++;
            $cbrk_dt = $dt_str;
            $icon = $fc->weather[0]->icon;
            $Forecast = new Forecast;
            $Forecast->day = $Dt->format('D');
            $Forecast->lowF = self::CtoF($fc->main->temp_min);
            $Forecast->highF = self::CtoF($fc->main->temp_max);
            $Forecast->lowC = $fc->main->temp_min;
            $Forecast->highC = $fc->main->temp_max;
            $Forecast->condition = $fc->weather[0]->description;
            $Forecast->icon = '//openweathermap.org/img/wn/' . $icon . '@2x.png';
            $Forecast->icon_name = (string)$fc->weather[0]->description;
            $Forecast->wind_M = self::KtoM($fc->wind->speed) . 'mph ' .
                    self::Deg2Dir($fc->wind->deg);
            $Forecast->wind_K = (string)$fc->wind->speed . 'kph ' .
                    self::Deg2Dir($fc->wind->deg);
            $this->data->Forecasts[] = $Forecast;
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
            'OpenWeather',
            'https://openweather.co.uk',
            array(
                'target' => '_blank',
            )
        );
        return $retval;
    }

}
