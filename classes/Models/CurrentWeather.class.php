<?php
/**
 * Current weather data model.
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
 * Class to standardize the current weather data from API providers.
 * @package weater */
class CurrentWeather
{
    /** Temperature in Farenheit.
     * @var string */
    public $temp_F = '';

    /** Temperature in Celsius.
     * @var string */
    public $temp_C = '';

    /** Description of current conditions (Windy, Sunny, etc.).
     * @var string */
    public $condition = '';

    /** Weather icon URL.
     * @var string */
    public $icon  = '';

    /** Weather icon name.
     * @var string */
    public $icon_name = '';

    /** Current humidity in percent.
     * @var string */
    public $humidity = '';

    /** Wind speed in MPH.
     * @var string */
    public $wind_M = '';

    /** Wind speed in KPH.
     * @var string */
    public $wind_K = '';
}

