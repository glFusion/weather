<?php
/**
 * Class to interface with Weatherstack's API.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2020 Lee Garner <lee@leegarner.com>
 * @package     weather
 * @version     v1.1.2
 * @since       v1.1.2
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Weather\api;


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

        $this->url = 'http://api.openweathermap.org/data/2.5/forecast?' .
            'appid=' . $_CONF_WEATHER['api_key_openweather'] .
            '&units=metric';

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

        $query = '';
        $parts = $loc['parts']; // for clarity
        if ($loc['type'] == 'city') {
            if (isset($parts['postal']) && !empty($parts['postal'])) {
                $this->location = $parts['postal'];
                $query = 'zip=' . $this->location;
            } else {
                unset($parts['postal']); // not used
                if (!isset($parts['country']) || empty($parts['country'])) {
                    $parts['country'] = $_CONF_WEATHER['def_country'];
                }
                $this->location = implode(',', $parts);
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
            COM_errorLog("OpenWeather error: {$this->response->message}");
            COM_errorLog("Searching for {$this->location}");
            $this->error = 1;
        } else {
            $this->info = $this->response->city;
            $this->current = $this->response->list[0];
            $this->location = $this->response->city;
            if (!is_object($this->location)) {
                COM_errorLog('Invalid current data, should be object ');
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
        $data = array(
            'info' => array(
                'city'  => $city,
                'date_time' => date('Y-m-d H:i:s'),
                'ts' => $this->current->dt,
                'api' => $this->api_code,
            ),
            'current' => array(
                'temp_f'   => self::CtoF((float)$this->current->main->temp),
                'temp_c'  => (string)$this->current->main->temp,
                'condition' => (string)$this->current->weather[0]->description,
                'icon'  => '//openweathermap.org/img/wn/' . $icon . '@2x.png',
                'icon_name' => (string)$this->current->weather[0]->description,
                'humidity' => (string)$this->current->main->humidity,
                'wind_M' => self::KtoM($this->current->wind->speed) . 'mph ' .
                    self::Deg2Dir($this->current->wind->deg),
                'wind_K' => (string)$this->current->wind->speed . 'kph ' .
                    self::Deg2Dir($this->current->wind->deg),
            ),
            'forecast' => array(),
        );
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
            $data['forecast'][] = array(
                'day'    => $Dt->format('D'),
                'lowF'   => self::CtoF($fc->main->temp_min),
                'highF'  => self::CtoF($fc->main->temp_max),
                'lowC'   => $fc->main->temp_min,
                'highC'  => $fc->main->temp_max,
                'condition' => $fc->weather[0]->description,
                'icon'  => '//openweathermap.org/img/wn/' . $icon . '@2x.png',
                'icon_name' => (string)$fc->weather[0]->description,
                'wind_M' => self::KtoM($fc->wind->speed) . 'mph ' .
                    self::Deg2Dir($fc->wind->deg),
                'wind_K' => (string)$fc->wind->speed . 'kph ' .
                    self::Deg2Dir($fc->wind->deg),
                'fc_text_F' => '',
                'fc_text_C' => '',
            );
        }
        $this->data = $data;
        return $data;
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

?>
