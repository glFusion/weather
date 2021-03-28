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
        } elseif ($loc['type'] = 'city' && is_array($loc['parts'])) {
            if (!empty($loc['parts']['postal'])) {
                if (empty($loc['parts']['country'])) {
                    $loc['parts']['country'] = $_CONF_WEATHER['def_country'];
                }
                $this->location = $loc['parts']['country'] . '.' . $loc['parts']['postal'];
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
        $data = array(
            'info' => array(
                'city'  => '',
                'date_time' => date('Y-m-d H:i:s'),
                'ts' => time(),
                'api' => $this->api_code,
            ),
            'current' => array(
                'temp_f'   => (string)$this->current->Timeframes[0]->temp_f,
                'temp_c'  => (string)$this->current->Timeframes[0]->temp_c,
                'condition' => (string)$this->current->Timeframes[0]->wx_desc,
                'icon'  => WEATHER_URL . '/images/icons/' . $icon,
                'icon_name' => $icon,
                'humidity' => (string)$this->current->Timeframes[0]->humid_pct,
                'wind_M' => (string)$this->current->Timeframes[0]->windspd_mph . 'mph ' .
                        (string)$this->current->Timeframes[0]->winddir_compass,
                'wind_K' => (string)$this->current->Timeframes[0]->windspd_kmh . 'kph ' .
                        (string)$this->current->Timeframes[0]->winddir_compass,
            ),
            'forecast' => array(),
        );
        if (is_array($this->forecast)) {
            // Weather Underground provides only 3 or 10-day forecasts.
            // We want 5 days.
            for ($i = 1; $i < $this->fc_days; $i++) {
                if (!isset($this->forecast[$i])) break;
                $fc = $this->forecast[$i];
                list($day, $month, $year) = explode('/', $fc->date);
                $Dt = new \Date($year . '-' . $month . '-' . $day, $_CONF['timezone']);
                $icon = (string)$fc->Timeframes[3]->wx_icon;
                $data['forecast'][] = array(
                    'day'    => $Dt->format('D'),
                    'lowF'   => (string)$fc->temp_min_f,
                    'highF'  => (string)$fc->temp_max_f,
                    'lowC'   => (string)$fc->temp_min_c,
                    'highC'  => (string)$fc->temp_max_c,
                    'condition' => (string)$fc->Timeframes[3]->wx_desc,
                    'icon'  => WEATHER_URL . '/images/icons/' . $icon,
                    'icon_name' => $icon,
                    'wind_M' => (string)$fc->Timeframes[3]->windspd_mph . 'mph ' .
                                (string)$fc->Timeframes[3]->winddir_compass,
                    'wind_K' => (string)$fc->Timeframes[3]->windspd_kph . 'kph ' .
                                (string)$fc->Timeframes[3]->winddir_compass,
                    'fc_text_F' => '',
                    'fc_text_C' => '',
                );
            }
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
            $this->api_name,
            'https://developer.weatherunlocked.com/',
            array(
                'target' => '_blank',
            )
        );
        return $retval;
    }

}
