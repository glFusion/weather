<?php
/**
 * Class to hold the current and forecasted data models.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2022 Lee Garner <lee@leegarner.com>
 * @package     weather
 * @version     v2.0.3
 * @since       v2.0.3
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Weather\Models;


/**
 * Class to represent weather data received from an API provider.
 * @package weather
 */
class WeatherData
{
    /** City returned.
     * @var string */
    public $city = '';

    /** Date/Time string of data.
     * @var string */
    public $date_time = '';

    /** Timestamp.
     * @var integer */
    public $ts = 0;

    /** API name.
     * @var string */
    public $api = '';

    /** API return status.  Assume an error occurred.
     * @var integer */
    public $status = 99;

    /** Current weather data model.
     * @var object */
    public $Current = NULL;

    /** Array of Forecast weather data models.
     * @var array */
    public $Forecasts = array();


    /**
     * Set defautl values and create a CurrentWeather object.
     */
    public function __construct()
    {
        global $_CONF;

        $this->date_time = $_CONF['_now']->toMySQL(true);
        $this->ts = time();
        $this->Current = new CurrentWeather;
    }

}

