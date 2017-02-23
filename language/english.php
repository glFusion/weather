<?php
/**
*   Default English language file for the Weather plugin
*
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
'Sunny'                             => 'Sunny',
'Clear'                             => 'Clear',
'Partly cloudy'                     => 'Partly cloudy',
'Cloudy'                            => 'Cloudy',
'Overcast'                          => 'Overcast',
'Mist'                              => 'Mist',
'Patchy rain nearby'                => 'Patchy rain nearby',
'Patchy snow nearby'                => 'Patchy snow nearby',
'Patchy sleet nearby'               => 'Patchy sleet nearby',
'Patchy freezing drizzle nearby'    => 'Patchy freezing drizzle nearby',
'Thundery outbreaks in nearby'      => 'Thundery outbreaks in nearby',
'Blowing snow'                      => 'Blowing snow',
'Blizzard'                          => 'Blizzard',
'Fog'                               => 'Fog',
'Freezing fog'                      => 'Freezing fog',
'Patchy light drizzle'              => 'Patchy light drizzle',
'Light drizzle'                     => 'Light drizzle',
'Freezing drizzle'                  => 'Freezing drizzle',
'Heavy freezing drizzle'            => 'Heavy freezing drizzle',
'Patchy light rain'                 => 'Patchy light rain',
'Light rain'                        => 'Light rain',
'Moderate rain at times'            => 'Moderate rain at times',
'Moderate rain'                     => 'Moderate rain',
'Heavy rain at times'               => 'Heavy rain at times',
'Heavy rain'                        => 'Heavy rain',
'Light freezing rain'               => 'Light freezing rain',
'Moderate or heavy freezing rain'   => 'Moderate or heavy freezing rain',
'Light sleet'                       => 'Light sleet',
'Moderate or heavy sleet'           => 'Moderate or heavy sleet',
'Patchy light snow'                 => 'Patchy light snow',
'Light snow'                        => 'Light snow',
'Patchy moderate snow'              => 'Patchy moderate snow',
'Moderate snow'                     => 'Moderate snow',
'Patchy heavy snow'                 => 'Patchy heavy snow',
'Heavy snow'                        => 'Heavy snow',
'Ice pellets'                       => 'Ice pellets',
'Light rain shower'                 => 'Light rain shower',
'Moderate or heavy rain shower'     => 'Moderate or heavy rain shower',
'Torrential rain shower'            => 'Torrential rain shower',
'Light sleet showers'               => 'Light sleet showers',
'Moderate or heavy sleet showers'   => 'Moderate or heavy sleet showers',
'Light snow showers'                => 'Light snow showers',
'Moderate or heavy snow showers'    => 'Moderate or heavy snow showers',
'Light showers of ice pellets'      => 'Light showers of ice pellets',
'Moderate or heavy showers of ice pellets'      => 'Moderate or heavy showers of ice pellets',
'Patchy light rain in area with thunder'        => 'Patchy light rain in area with thunder',
'Moderate or heavy rain in area with thunder'   => 'Moderate or heavy rain in area with thunder',
'Patchy light snow in area with thunder'        => 'Patchy light snow in area with thunder',
'Moderate or heavy snow in area with thunder'   => 'Moderate or heavy snow in area with thunder'
);

$LANG_DIRECTIONS = array(
"E"     =>"E",
"N"     =>"N",
"W"     =>"W",
"S"     =>"S",
"NE"    =>"NE",
"SE"    =>"SE",
"NW"    =>"NW",
"SW"    =>"SW",
"ENE"   =>"ENE",
"WNW"   =>"WNW",
"ESE"   =>"ESE",
"WSW"   =>"WSW",
"NNE"   =>"NNE",
"SSE"   =>"SSE",
"NNW"   =>"NNW",
"SSW"   =>"SSW",
"VAR"   =>"---"
);

?>
