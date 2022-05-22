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
 * Class to interact with Weatherstack.
 * @package weather
 */
class weatherstack extends \Weather\API
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

        $this->api_name = 'Weatherstack';
        $this->api_code = 'weatherstack';
        $this->configs = array(
            'api_key',
        );
        parent::__construct($loc);

        $this->url = 'http://api.weatherstack.com/current?' .
            'access_key=' . $_CONF_WEATHER['api_key_weatherstack'] .
            '&units=m';

        if (!empty($loc)) {
            // Get the weather for the specified location, if requested
            $this->Get($loc);
        }
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

        if ($loc['type'] == 'coord') {
            $this->setLocation(implode(',', $loc['parts']));
            $type = 'LatLon';
        } elseif ($loc['type'] == 'address') {
            $parts = $loc['parts'];
            if (isset($parts['postal']) && !empty($parts['postsl'])) {
                $type = 'Zipcode';
                $this->setLocation($parts['postal']);
            } else {
                $type = 'city';
                if (is_string($parts)) {
                    $parts = array('city' => $parts);
                }
                if (isset($parts['postal'])) {
                    // remove if present but empty
                    unset($parts['postal']);
                }
                if (empty($parts['country']) && isset($_CONF_WEATHER['def_country'])) {
                    $parts['country'] = $_CONF_WEATHER['def_country'];
                }
                $this->setLocation(implode(',', $parts));
            }
        }
        return $this->url . "&type=$type&query={$this->location}";
    }


    private function setLocation($loc_string)
    {
        $this->location = urlencode($loc_string);
        return $this;
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
        if (isset($this->response->error)) {
            $tmp = $this->response->error;
            $this->logError("Error: {$tmp->type} - {$tmp->info}");
            $this->logError("Searching for {$this->location}");
            $this->error = 1;
        } else {
            $this->current = $this->response->current;
            $this->location = $this->response->location;
            if (!is_object($this->current)) {
                $this->logError('Invalid current data, should be object.');
                $this->error = 1;
                return false;
            } else {
                $this->fc_text = $this->response->current->weather_descriptions[0];
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
        $city = $this->location->name;
        if (!empty($this->location->region)) {
            $city .= ', ' . $this->location->region;
        }
        $this->data = new WeatherData;
        $this->data->status = 0;    // got good weather
        $this->data->city = $city;
        $this->data->api = $this->api_code;
        $this->data->Current->temp_F = self::CtoF((float)$this->current->temperature);
        $this->data->Current->temp_C = $this->current->temperature;
        $this->data->Current->condition = (string)$this->current->weather_descriptions[0];
        $this->data->Current->icon = (string)$this->current->weather_icons[0];
        $this->data->Current->icon_name = (string)$this->current->weather_descriptions[0];
        $this->data->Current->humidity = (string)$this->current->humidity;
        $this->data->Current->wind_M = self::KtoM($this->current->wind_speed) . 'mph ' .
            self::Deg2Dir($this->current->wind_dir);
        $this->data->Current->wind_K = (string)$this->current->wind_speed . 'kph ' .
            self::Deg2Dir($this->current->wind_dir);
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
            'Weatherstack',
            'http://weatherstack.com',
            array(
                'target' => '_blank',
            )
        );
        return $retval;
    }

}
