<?php
/**
 */
namespace Weather\Models;


class WeatherData
{
    public $city = '';
    public $date_time = '';
    public $ts = 0;
    public $api = '';
    public $status = 99;    // assume error
    public $current = array(
        'temp_f' => '',
        'temp_c' => '',
        'condition' => '',
        'icon'  => '',
        'icon_name' => '',
        'humidity' => '',
        'wind_M' => '',
        'wind_K' => '',
    );
    public $forcast = array();


    public function __construct()
    {
        $this->date_time = date('Y-m-d H:i:s');
        $this->ts = time();
    }

}

