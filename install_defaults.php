<?php
/**
 * Configuration defaults for the Weather plugin.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2011-2020 Lee Garner <lee@leegarner.com>
 * @package     weather
 * @version     v2.0.0
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */

if (!defined ('GVERSION')) {
    die('This file can not be used on its own!');
}

/**
 * Weather default settings.
 *
 * Initial Installation Defaults used when loading the online configuration
 * records. These settings are only used during the initial installation
 * and not referenced any more once the plugin is installed
 */
global $weatherConfigData;
$weatherConfigData = array(
    array(
        'name' => 'sg_main',
        'default_value' => NULL,
        'type' => 'subgroup',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => NULL,
        'sort' => 0,
        'set' => true,
        'group' => 'weather',
    ),
    array(
        'name' => 'fs_main',
        'default_value' => NULL,
        'type' => 'fieldset',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => NULL,
        'sort' => 0,
        'set' => true,
        'group' => 'weather',
    ),
    array(
        'name' => 'anon_access',
        'default_value' => '1',
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 2,
        'sort' => 10,
        'set' => true,
        'group' => 'weather',
    ),
    array(
        'name' => 'displayblocks',
        'default_value' => '3',
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 13,
        'sort' => 20,
        'set' => true,
        'group' => 'weather',
    ),
    array(
        'name' => 'cache_minutes',
        'default_value' => '120',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 30,
        'set' => true,
        'group' => 'weather',
    ),
    array(
        'name' => 'default_loc',
        'default_value' => '',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 40,
        'set' => true,
        'group' => 'weather',
    ),
    array(
        'name' => 'blk_show_loc',
        'default_value' => '3',
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 3,
        'sort' => 50,
        'set' => true,
        'group' => 'weather',
    ),
    array(
        'name' => 'usermenu_option',
        'default_value' => '1',
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 2,
        'sort' => 50,
        'set' => true,
        'group' => 'weather',
    ),
    array(
        'name' => 'provider',
        'default_value' => '',
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 16,
        'sort' => 60,
        'set' => true,
        'group' => 'weather',
    ),
    array(
        'name' => 'k_m',
        'default_value' => 'M',
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 14,
        'sort' => 70,
        'set' => true,
        'group' => 'weather',
    ),
    array(
        'name' => 'f_c',
        'default_value' => 'F',
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 15,
        'sort' => 80,
        'set' => true,
        'group' => 'weather',
    ),
    array(
        'name' => 'def_country',
        'default_value' => 'US',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 90,
        'set' => true,
        'group' => 'weather',
    ),
    array(
        'name' => 'log_level',
        'default_value' => '200',
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 18,
        'sort' => 100,
        'set' => true,
        'group' => 'weather',
    ),

    array(
        'name' => 'fs_provider_openweather',
        'default_value' => NULL,
        'type' => 'fieldset',
        'subgroup' => 0,
        'fieldset' => 10,
        'selection_array' => NULL,
        'sort' => 0,
        'set' => true,
        'group' => 'weather',
    ),
    array(
        'name' => 'api_key_openweather',
        'default_value' => '',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 10,
        'selection_array' => 0,
        'sort' => 10,
        'set' => true,
        'group' => 'weather',
    ),

    array(
        'name' => 'fs_provider_wunlocked',
        'default_value' => NULL,
        'type' => 'fieldset',
        'subgroup' => 0,
        'fieldset' => 20,
        'selection_array' => NULL,
        'sort' => 0,
        'set' => true,
        'group' => 'weather',
    ),
    array(
        'name' => 'api_key_wunlocked',
        'default_value' => '',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 20,
        'selection_array' => 0,
        'sort' => 10,
        'set' => true,
        'group' => 'weather',
    ),
    array(
        'name' => 'app_id_wunlocked',
        'default_value' => '',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 20,
        'selection_array' => 0,
        'sort' => 20,
        'set' => true,
        'group' => 'weather',
    ),

    array(
        'name' => 'fs_provider_weatherstack',
        'default_value' => NULL,
        'type' => 'fieldset',
        'subgroup' => 0,
        'fieldset' => 30,
        'selection_array' => NULL,
        'sort' => 0,
        'set' => true,
        'group' => 'weather',
    ),
    array(
        'name' => 'api_key_weatherstack',
        'default_value' => '',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 30,
        'selection_array' => 0,
        'sort' => 10,
        'set' => true,
        'group' => 'weather',
    ),
);


/**
 * Initialize Weather plugin configuration.
 * Creates the entries for the configuration if they don't already
 * exist.
 *
 * @return boolean     true: success; false: an error occurred
 */
function plugin_initconfig_weather()
{
    global $weatherConfigData;

    $c = config::get_instance();
    if (!$c->group_exists('weather')) {
        USES_lib_install();
        foreach ($weatherConfigData AS $cfgItem) {
            _addConfigItem($cfgItem);
        }
    }
    return true;
}


/**
 * Sync the configuration in the DB to the above configs
 */
function plugin_updateconfig_weather()
{
    global $weatherConfigData;

    USES_lib_install();
    _update_config('weather', $weatherConfigData);
}

?>
