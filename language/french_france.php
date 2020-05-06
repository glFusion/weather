<?php
/**
*   Default French language file for the Weather plugin
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
'fs_provider_wunlocked' => 'Weather Unlocked',
'fs_provider_openweather' => 'OpenWeather',
'fs_provider_weatherstack' => 'Weatherstack',
);

$LANG_configsections['weather'] = array(
    'label'                 => 'Weather',
    'title'                 => 'Weather Configuration'
);

$LANG_confignames['weather'] = array(
'anon_access' => 'Anonymous Access',
'cache_minutes' => 'Minutes to cache weather data',
'default_loc' => 'Default location',
'blk_show_loc' => 'Location to show in PHP block',
'usermenu_option' => 'Show the plugin on the "Plugins" menu?',
'api_key' => 'Provider API key',
'f_c' => 'Temperature units',
'k_m' => 'Windspeed units',
'provider' => 'Provider',
'api_key_weatherstack' => 'API Key',
'api_key_openweather' => 'API Key',
'api_key_wunlocked' => 'API Key',
'app_id_wunlocked' => 'Application ID',
'def_country' => 'Default Country (2-letter code)',
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
    16  => array(
        'Weather Underground' => 'wu',
        'APIXU' => 'apixu',
    ),
);

?>
