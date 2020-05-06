<?php
/**
 * Default Dutch language file for the Weather plugin.
 *
 * @author      Eric Starkenburg <zilverballs@home.nl>
 * @copyright   Copyright (c) 2011 Lee Garner <lee@leegarner.com>
 * @package     weather
 * @version     v0.1.0
 * @license     http://opensource.org/licenses/gpl-2.0.php 
 *              GNU Public License v2 or later
 * @filesource
 */

/**
 * Dutch language array.
 * @var array */
$LANG_WEATHER = array(
'pi_title'      => 'Weerbericht',
'curr_cond_for' => 'Actuele weersomstandigheden',
'search_instr'  => 'Zoeken op Postcode geeft het beste resultaat.',
'rem_this_item' => 'Verwijder dit Item',
'version' => 'ver.',
'wind' => 'Wind',
'powered_by' => 'Powered by',
'purge_cache' => 'Purge Cache',
'err_purge_cache' => 'Error purging cache table',
'cache_purged' => 'Cache table purged',
'menu_hlp'  => array(
    'default' => 'Click "Purge Cache" to immediately clear the cache. This should be done whenever the weather provider is changed.',
    ),
);

$PLG_weather_MESSAGE1 = 'De gevraagde locatie is niet gevonden.';

$LANG_configsubgroups['weather'] = array(
    'sg_main'               => 'Hoofd Instellingen',
);

$LANG_fs['weather'] = array(
    'fs_main'               => 'Algemene Instellingen',
'fs_provider_wunlocked' => 'Weather Unlocked',
'fs_provider_openweather' => 'OpenWeather',
'fs_provider_weatherstack' => 'Weatherstack',
);

$LANG_configsections['weather'] = array(
    'label'                 => 'Weerbericht',
    'title'                 => 'Weerbericht Instellingen'
);

$LANG_confignames['weather'] = array(
    'anon_access'       => 'Anonieme Bezoekers',
    'displayblocks'     => 'Toon glFusion Blokken',
    'cache_minutes'     => 'Aantal minuten om weerbericht gegevens te cachen',
    'default_loc'       => 'Standaard Locatie',
    'blk_show_loc'      => 'Toon Locatie in PHP blok',
    'usermenu_option'   => 'Toon de Plugin in het "Plugins" menu?',
    'api_key'           => 'Provider API key',
    'f_c'               => 'Temperature units',
    'k_m'               => 'Windspeed units',
    'provider'          => 'Provider',
    'api_key_weatherstack' => 'API Key',
'api_key_openweather' => 'API Key',
'api_key_wunlocked' => 'API Key',
'app_id_wunlocked' => 'Application ID',
'def_country' => 'Default Country (2-letter code)',
);

$LANG_configselects['weather'] = array(
    0   => array(   'Ja' => 1, 'Nee' => 0),
    1   => array(   'Ja' => 1, 'Nee' => 0),
    2   => array(   'Geen' => 0,
                    'Bekijken'  => 1,
                    'Bekijken & Zoeken' => 2,
            ),
    3   => array(   'Alleen de standaard Locatie' => 1,
                    'Alleen persoonlijk, indien beschikbaar' => 2,
                    'Persoonlijk, terugvallen op standaard' => 3,
            ),
    13  => array(   'Linker Blokken' => 1, 
                    'Rechter Blokken' => 2, 
                    'Linker & Rechter Blokken' => 3, 
                    'Geen' => 0,
            ),
    14  => array(   'Miles per Hour' => 'M', 'Kilometres per Hour' => 'K' ),
    15  => array(   'Farenheit' => 'F', 'Celsius' => 'C'),
    16  => array(
        'Weatherstack' => 'weatherstack',
        'OpenWeather' => 'openweather',
        'Weather Unlocked' => 'wunlocked',
    ),
);

?>
