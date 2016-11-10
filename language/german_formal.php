<?php
/**
*   Default german_formal language file for the Weather plugin
*	Translated by matrox66
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2011 Lee Garner <lee@leegarner.com>
*   @package    weather
*   @version    1.0.0
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*               GNU Public License v2 or later
*   @filesource
*/

$LANG_WEATHER = array(
'pi_title'      => 'Wetter',
'version'       => 'ver.',
'curr_cond_for' => 'Aktuelles Wetter für',
'search_instr'  => 'Die suche über Postleitzahl führt zu den besten Ergebnissen.',
'rem_this_item' => 'Eintrag ausblenden',
'wind'          => 'Wind',
'powered_by'    => 'Powered by',
'purge_cache'   => 'Zwischenspeicher löschen',
'err_purge_cache' => 'Fehler beim löschen des Zwischenspeicher',
'cache_purged'  => 'Zwischenspeicher gelöscht',
'menu_hlp'  => array(
    'default' => 'Drücke "Zwischenspeicher löschen" um alle gespeicherten Einträge zu löschen. Dies sollte bei jeder Änderung des Anbieters durchgeführt werden.',
    ),
);

$PLG_weather_MESSAGE1 = 'Der gesuchte Ort konnte nicht gefunden werden.';
$PLG_weather_MESSAGE2 = 'Es gab Fehler bei der Abfrage der Wetterdaten.';
$PLG_weather_MESSAGE3 = 'API-Key nicht vorhanden';
$PLG_weather_MESSAGE4 = 'Es gab Fehler beim updaten des Plugin';

$LANG_configsubgroups['weather'] = array(
    'sg_main'               => 'Einstellungen',
);

$LANG_fs['weather'] = array(
    'fs_main'               => 'Allgemeine Einstellungen',
    'fs_provider_wwo'       => 'Anbieter: World Weather Online',
    'fs_provider_wu'        => 'Anbieter: Weather Underground',
);

$LANG_configsections['weather'] = array(
    'label'                 => 'Wetter',
    'title'                 => 'Wetter Konfiguration'
);

$LANG_confignames['weather'] = array(
    'anon_access'       => 'Zugriff für Gäste',
    'displayblocks'     => 'glFusion Blöcke anzeigen',
    'cache_minutes'     => 'Zwischenspeicher in Minuten',
    'default_loc'       => 'Voreingestellter Ort',
    'blk_show_loc'      => 'Wetterdaten im PHP-Block',
    'usermenu_option'   => 'Plugin im "Plugins" Menü anzeigen?',
    'api_key'           => 'API-Key des Anbieter',
    'f_c'               => 'Einheit für Temperatur',
    'k_m'               => 'Einheit für Windgeschwindigkeit',
    'provider'          => 'Anbieter',
    'api_key_wwo'       => 'API-Key',
    'api_key_wu'        => 'API-Key',
    'ref_key_wu'        => 'Referrer-Key',
);

$LANG_configselects['weather'] = array(
    0   => array(   'Richtig' => 1, 'Falsch' => 0),
    1   => array(   'Ja' => 1, 'Nein' => 0),
    2   => array(   'Keine' => 0,
                    'Anzeigen'  => 1,
                    'Anzeigen & Suchen' => 2,
            ),
    3   => array(   'Voreingestellter Ort' => 1,
                    'Persönlicher Ort wenn vorhanden' => 2,
                    'Persönlicher Ort sonst Voreingestellter Ort' => 3,
            ),
    13  => array(   'Linke Blöcke' => 1, 
                    'Rechte Blöcke' => 2, 
                    'Linke & Rechte Blöcke' => 3, 
                    'Keine' => 0,
            ),
    14  => array(   'Milen pro Stunde' => 'M', 'Kilometer pro Stunde' => 'K' ),
    15  => array(   'Farenheit' => 'F', 'Celsius' => 'C'),
    16  => array(   'World Weather Online' => 'wwo',
                    'Weather Underground' => 'wu',
            ),
);

?>
