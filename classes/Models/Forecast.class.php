<?php
/**
 * Weather forecast data model.
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
 * Class to standardize forecasted weather data.
 * @package weather
 * */
class Forecast
{
    /** Name of the day.
     * @var string */
    public $day = '';

    /** Expected low temp in Farenheit.
     * @var string */
    public $lowF = '';

    /** Expected high temp in Farenheit.
     * @var string */
    public $highF = '';

    /** Expected low temp in Celsius.
     * @var string */
    public $lowC = '';

    /** Expected high temp in Celsius.
     * @var string */
    public $highC = '';

    /** Description of the weather conditions.
     * @var string */
    public $condition = '';

    /** Icon URL.
     * @var string */
    public $icon = '';

    /** Icon name.
     * @var string */
    public $icon_name = '';

    /** Wind speed in MPH.
     * @var string */
    public $wind_M = '';

    /** Wind speed in KPH.
     * @var string */
    public $wind_K = '';

    /** Textual description of temperature in Farenheit.
     * @var string */
    public $text_F = '';

    /** Textual description of temperature in Celsius.
     * @var string */
    public $text_C = '';
}

