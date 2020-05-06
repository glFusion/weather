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
            '&units=m' .
            '&query=';

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
            $this->location = implode(',', $loc['parts']);
        } elseif ($loc['type'] = 'city') {
            $parts = $loc['parts'];
            if (!empty($parts['postsl'])) {
                $this->location = $parts['postal'];
            } else {
                unset($parts['postal']);
                if (empty($parts['country'])) {
                    $parts['country'] = $_CONF_WEATHER['def_country'];
                }
                $this->location = implode(',', $parts);
            }
        }
        return $this->url . $this->location;
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
            COM_errorLog("WS error: {$tmp->type} - {$tmp->description}");
            COM_errorLog("Searching for {$this->location}");
        }
        $this->info = $this->response->current_observation->display_location;
        $this->current = $this->response->current;
        $this->location = $this->response->location;
        if (!is_object($this->current)) {
            COM_errorLog('Invalid current data, should be object ');
            //COM_errorLog('Current object: ' . print_r($this->current, true));
            return false;
        } else {
            $this->fc_text = $this->response->current->weather_description[0];
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
        $city = $this->location->name;
        if (!empty($this->location->region)) {
            $city .= ', ' . $this->location->region;
        }
        $data = array(
            'info' => array(
                'city'  => $city,
                'date_time' => date('Y-m-d H:i:s'),
                'ts' => $this->current->observation_time,
            ),
            'current' => array(
                'temp_f'   => self::CtoF((float)$this->current->temperature),
                'temp_c'  => (string)$this->current->temperature,
                'condition' => (string)$this->current->weather_descriptions[0],
                'icon'  => (string)$this->current->weather_icons[0],
                'icon_name' => (string)$this->current->weather_descriptions[0],
                'humidity' => (string)$this->current->humidity,
                'wind_M' => self::KtoM($this->current->wind_speed) . 'mph ' .
                        (string)$this->current->wind_dir,
                'wind_K' => (string)$this->current->wind_speed . 'kph ' .
                        (string)$this->current->wind_dir,
            ),
            'forecast' => array(),
        );
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
            'Weatherstack',
            'http://weatherstack.com',
            array(
                'target' => '_blank',
            )
        );
        return $retval;
    }

}

?>
