<?php
/**
*   Default Spanish language file for the Weather plugin
*	Translated by matrox66 Feb.2017
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2011 Lee Garner <lee@leegarner.com>
*   @package    weather
*   @version    1.0.0
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*               GNU Public License v2 or later
*   @filesource
*/

$LANG_WEATHER = array(
'pi_title'      => 'Weather',
'version'       => 'ver.',
'curr_cond_for' => 'Current Conditions for',
'search_instr'  => 'Searching by zip code and location results in the best results.<br />You can also enter coordinates (e.g. 52.507863,13.426145 for Berlin)',
'rem_this_item' => 'Remove this Item',
'wind'          => 'Wind',
'powered_by'    => 'Powered by',
'purge_cache'   => 'Purge Cache',
'err_purge_cache' => 'Error purging cache table',
'cache_purged'  => 'Cache table purged',
'menu_hlp'  => array(
    'default' => 'Click "Purge Cache" to immediately clear the cache. This should be done whenever the weather provider is changed.',
    ),
);

$PLG_weather_MESSAGE1 = 'The requested location could be not found.';
$PLG_weather_MESSAGE2 = 'There was an error retrieving weather data.';
$PLG_weather_MESSAGE3 = 'Missing API key.';
$PLG_weather_MESSAGE4 = 'An error occurred updating the plugin';

$LANG_configsubgroups['weather'] = array(
    'sg_main'               => 'Main Settings',
);

$LANG_fs['weather'] = array(
    'fs_main'               => 'General Settings',
    'fs_provider_wwo'       => 'World Weather Online',
    'fs_provider_wu'        => 'Weather Underground',
    'fs_provider_apixu'     => 'APIXU',
);

$LANG_configsections['weather'] = array(
    'label'                 => 'Weather',
    'title'                 => 'Weather Configuration'
);

$LANG_confignames['weather'] = array(
    'anon_access'       => 'Anonymous Access',
    'displayblocks'     => 'Display glFusion Blocks',
    'cache_minutes'     => 'Minutes to cache weather data',
    'default_loc'       => 'Default location',
    'blk_show_loc'      => 'Location to show in PHP block',
    'usermenu_option'   => 'Show the plugin on the "Plugins" menu?',
    'api_key'           => 'Provider API key',
    'f_c'               => 'Temperature units',
    'k_m'               => 'Windspeed units',
    'provider'          => 'Provider',
    'api_key_wwo'       => 'API Key',
    'api_key_wu'        => 'API Key',
    'ref_key_wu'        => 'Referrer Key',
    'api_key_apixu'     => 'API Key',
);

$LANG_configselects['weather'] = array(
    0   => array(   'True' => 1, 'False' => 0),
    1   => array(   'Yes' => 1, 'No' => 0),
    2   => array(   'None' => 0,
                    'View'  => 1,
                    'View & Search' => 2,
            ),
    3   => array(   'Default location only' => 1,
                    'Personal only, if available' => 2,
                    'Personal, fallback to default' => 3,
            ),
    13  => array(   'Left Blocks' => 1, 
                    'Right Blocks' => 2, 
                    'Left & Right Blocks' => 3, 
                    'None' => 0,
            ),
    14  => array(   'Miles per Hour' => 'M', 'Kilometres per Hour' => 'K' ),
    15  => array(   'Farenheit' => 'F', 'Celsius' => 'C'),
    16  => array(   'World Weather Online' => 'wwo',
                    'Weather Underground' => 'wu',
                    'APIXU' => 'apixu',
            ),
);

$LANG_APIXU_CONDITIONS = array(
'Sunny'											=> 'Soleado',
'Clear'											=> 'Despejado',
'Partly cloudy'									=> 'Parcialmente nublado',
'Cloudy'										=> 'Nublado',
'Overcast'										=> 'Cielo cubierto',
'Mist'											=> 'Neblina',
'Patchy rain nearby'							=> 'Lluvia  moderada a intervalos',
'Patchy snow nearby'							=> 'Nieve moderada a intervalos en las aproximaciones',
'Patchy sleet nearby'							=> 'Aguanieve moderada a intervalos en las aproximaciones',
'Patchy freezing drizzle nearby'				=> 'Llovizna helada a intervalos en las aproximaciones',
'Thundery outbreaks in nearby'					=> 'Cielos tormentosos en las aproximaciones',
'Blowing snow'									=> 'Chubascos de nieve',
'Blizzard'										=> 'Ventisca',
'Fog'											=> 'Niebla moderada',
'Freezing fog'									=> 'Niebla helada',
'Patchy light drizzle'							=> 'Llovizna a intervalos',
'Light drizzle'									=> 'Llovizna',
'Freezing drizzle'								=> 'Llovizna helada',
'Heavy freezing drizzle'						=> 'Fuerte llovizna helada',
'Patchy light rain'								=> 'Lluvias ligeras a intervalos',
'Light rain'									=> 'Ligeras lluvias',
'Moderate rain at times'						=> 'Periodos de lluvia moderada',
'Moderate rain'									=> 'Lluvia moderada',
'Heavy rain at times'							=> 'Periodos de fuertes lluvias',
'Heavy rain'									=> 'Fuertes lluvias',
'Light freezing rain'							=> 'Ligeras lluvias heladas',
'Moderate or heavy freezing rain'				=> 'Lluvias heladas fuertes o moderadas',
'Light sleet'									=> 'Ligeras precipitaciones de aguanieve',
'Moderate or heavy sleet'						=> 'Aguanieve fuerte o moderada',
'Patchy light snow'								=> 'Nevadas ligeras a intervalos',
'Light snow'									=> 'Nevadas ligeras',
'Patchy moderate snow'							=> 'Nieve moderada a intervalos',
'Moderate snow'									=> 'Nieve moderada',
'Patchy heavy snow'								=> 'Nevadas intensas',
'Heavy snow'									=> 'Fuertes nevadas',
'Ice pellets'									=> 'Granizo',
'Light rain shower'								=> 'Ligeras precipitaciones',
'Moderate or heavy rain shower'					=> 'Lluvias fuertes o moderadas',
'Torrential rain shower'						=> 'Lluvias torrenciales',
'Light sleet showers'							=> 'Ligeros chubascos de aguanieve',
'Moderate or heavy sleet showers'				=> 'Chubascos de aguanieve fuertes o moderados',
'Light snow showers'							=> 'Ligeras precipitaciones de nieve',
'Moderate or heavy snow showers'				=> 'Chubascos de nieve fuertes o moderados',
'Light showers of ice pellets'					=> 'Ligeros chubascos acompañados de granizo',
'Moderate or heavy showers of ice pellets'		=> 'Chubascos fuertes o moderados acompañados de granizo',
'Patchy light rain in area with thunder'		=> 'Intervalos de lluvias ligeras con tomenta en la región',
'Moderate or heavy rain in area with thunder'	=> 'Lluvias con tormenta fuertes o moderadas en la región',
'Patchy light snow in area with thunder'		=> 'Nieve moderada con tormenta en la región',
'Moderate or heavy snow in area with thunder'	=> 'Nieve moderada o fuertes nevadas con tormenta en la región'
);

$LANG_DIRECTIONS = array(
"E"		=>"E",
"N"		=>"N",
"W"		=>"O",
"S"		=>"S",
"NE"	=>"NE",
"SE"	=>"SE",
"NW"	=>"NO",
"SW"	=>"SO",
"ENE"	=>"ENE",
"WNW"	=>"ONO",
"ESE"	=>"ESE",
"WSW"	=>"OSO",
"NNE"	=>"NNE",
"SSE"	=>"SSE",
"NNW"	=>"NNO",
"SSW"	=>"SSO",
"VAR"	=>"---"
);

?>
