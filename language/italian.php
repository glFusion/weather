<?php
/**
*   Default Italian language file for the Weather plugin
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
'Sunny'											=> 'Soleggiato',
'Clear'											=> 'Sereno',
'Partly cloudy'									=> 'Parzialmente nuvoloso',
'Cloudy'										=> 'Nuvoloso',
'Overcast'										=> 'Coperto',
'Mist'											=> 'Foschia',
'Patchy rain nearby'							=> 'Pioggia a tratti nelle vicinanze',
'Patchy snow nearby'							=> 'Neve a tratti nelle vicinanze',
'Patchy sleet nearby'							=> 'Nevischio a tratti nelle vicinanze',
'Patchy freezing drizzle nearby'				=> 'Pioviggine congelantesi a tratti nelle vicinanze',
'Thundery outbreaks in nearby'					=> 'Precipitazioni temporalesche nelle vicinanze',
'Blowing snow'									=> 'Turbinio di neve',
'Blizzard'										=> 'Blizzard',
'Fog'											=> 'Nebbia',
'Freezing fog'									=> 'Nebbia congelantesi',
'Patchy light drizzle'							=> 'Pioviggine debole a tratti',
'Light drizzle'									=> 'Pioviggine debole',
'Freezing drizzle'								=> 'Pioviggine congelantesi',
'Heavy freezing drizzle'						=> 'Pioviggine congelantesi forte',
'Patchy light rain'								=> 'Pioggia debole a tratti',
'Light rain'									=> 'Pioggia debole',
'Moderate rain at times'						=> 'Pioggia moderata a tratti',
'Moderate rain'									=> 'Pioggia moderata',
'Heavy rain at times'							=> 'Pioggia forte a tratti',
'Heavy rain'									=> 'Pioggia forte',
'Light freezing rain'							=> 'Pioggia congelantesi debole',
'Moderate or heavy freezing rain'				=> 'Pioggia congelantesi moderata o forte',
'Light sleet'									=> 'Nevischio debole',
'Moderate or heavy sleet'						=> 'Nevischio moderato o forte',
'Patchy light snow'								=> 'Neve debole a tratti',
'Light snow'									=> 'Neve debole',
'Patchy moderate snow'							=> 'Neve moderata a tratti',
'Moderate snow'									=> 'Neve moderata',
'Patchy heavy snow'								=> 'Neve forte a tratti',
'Heavy snow'									=> 'Neve forte',
'Ice pellets'									=> 'Pioggia gelata',
'Light rain shower'								=> 'Precipitazioni piovose deboli',
'Moderate or heavy rain shower'					=> 'Precipitazioni piovose moderate o forti',
'Torrential rain shower'						=> 'Precipitazioni piovose torrenziali',
'Light sleet showers'							=> 'Precipitazioni deboli di nevischio',
'Moderate or heavy sleet showers'				=> 'Precipitazioni di nevischio moderate o forti',
'Light snow showers'							=> 'Precipitazioni nevose leggere',
'Moderate or heavy snow showers'				=> 'Precipitazioni nevose moderate o leggere',
'Light showers of ice pellets'					=> 'Precipitazioni deboli di pioggia gelata',
'Moderate or heavy showers of ice pellets'		=> 'Precipitazioni moderate o forti di pioggia gelata',
'Patchy light rain in area with thunder'		=> 'Pioggia debole a tratti in zona e tuoni',
'Moderate or heavy rain in area with thunder'	=> 'Pioggia moderata o forte in zona e tuoni',
'Patchy light snow in area with thunder'		=> 'Neve debole a tratti in zona e tuoni',
'Moderate or heavy snow in area with thunder'	=> 'Neve moderata o forte in zona e tuoni'
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
