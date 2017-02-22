<?php
/**
*   Upgrade the plugin
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2012 Lee Garner <lee@leegarner.com>
*   @package    weather
*   @version    1.0.0
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*               GNU Public License v2 or later
*   @filesource
*/

global $_CONF, $_CONF_WEATHER, $_DB_dbms;

/** Include default values for new config items */
require_once "{$_CONF['path']}plugins/{$_CONF_WEATHER['pi_name']}/install_defaults.php";

/**
*   Sequentially perform version upgrades.
*   @param current_ver string Existing installed version to be upgraded
*   @return integer Error code, 0 for success
*/
function weather_do_upgrade($current_ver)
{
    global $_CONF_WEATHER;

    $error = 0;

    // Get the config instance, several upgrades might need it
    $c = config::get_instance();

    if ($current_ver < '1.0.4') {
        // Provider - apixu.com
        $c->add('fs_provider_apixu', NULL, 'fieldset',
                0, 30, NULL, 0, true, $_CONF_WEATHER['pi_name']);
        $c->add('api_key_apixu', '', 'text',
                0, 10, 0, 100, true, $_CONF_WEATHER['pi_name']);
    }

    if ($current_ver < '0.1.3') {
        $error = weather_upgrade_0_1_3();
        if ($error)
            return $error;
    }

    if ($current_ver < '1.0.0') {
        $error = weather_upgrade_1_0_0();
        if ($error)
            return $error;
    }

    return $error;
}


/**
*   Execute the SQL statement to perform a version upgrade.
*   An empty SQL parameter will return success.
*
*   @param string   $version  Version being upgraded to
*   @param array    $sql      SQL statement to execute
*   @return integer Zero on success, One on failure.
*/
function weather_do_upgrade_sql($version, $sql='')
{
    global $_TABLES, $_CONF_WEATHER;

    // If no sql statements passed in, return success
    if (!is_array($sql))
        return 0;

    // Execute SQL now to perform the upgrade
    COM_errorLOG("--Updating Weather Plugin to version $version");
    foreach ($sql as $stmt) {
        COM_errorLOG("Weather Plugin $version update: Executing SQL => " . current($sql));
        DB_query($stmt, '1');
        if (DB_error()) {
            COM_errorLog("SQL Error during Weather plugin update",1);
            return 1;
            break;
        }
    }

    return 0;
}


/**
*   Upgrade to version 0.1.3
*   Implements WorldWeatherOnline provider, adds api key and
*   English/Metric selections to plugin configuration.
*
*   @return integer 0, no sql to upgrade here
*/
function weather_upgrade_0_1_3()
{
    global $_TABLES, $_CONF_WEATHER, $_WEA_DEFAULT;

    // Add new configuration items
    $c = config::get_instance();
    if ($c->group_exists($_CONF_WEATHER['pi_name'])) {
        $c->add('api_key',$_WEA_DEFAULT['api_key'], 'text',
                0, 0, NULL, 70, true, 'weather');
        $c->add('k_m',$_WEA_DEFAULT['k_m'], 'select',
                0, 0, 14, 80, true, 'weather');
        $c->add('f_c',$_WEA_DEFAULT['f_c'], 'select',
                0, 0, 15, 90, true, 'weather');
    }
    return 0;
}

/**
*   Upgrade to version 1.0.0
*   Implements Weather Underground provider, adds provider selection
*   to plugin configuration.
*
*   @return integer 0, no sql to upgrade here
*/
function weather_upgrade_1_0_0()
{
    global $_TABLES, $_CONF_WEATHER, $_WEA_DEFAULT;

    $sql = array(
        "UPDATE {$_TABLES['conf_values']} SET
            name='api_key_wwo', fieldset=10
            WHERE name='api_key' AND group_name='weather'",
    );
    $status =  weather_do_upgrade_sql('1.0.0', $sql);
    if ($status > 0) return $status;

    // Add new configuration items
    $c = config::get_instance();
    if ($c->group_exists($_CONF_WEATHER['pi_name'])) {
        $c->add('provider',$_WEA_DEFAULT['provider'], 'select',
                0, 0, 16, 75, true, 'weather');
        // Provider - World Weather Online
        $c->add('fs_provider_wwo', NULL, 'fieldset', 0, 10, NULL, 0,
                true, 'weather');
        // Provider - Weather Underground
        $c->add('fs_provider_wu', NULL, 'fieldset', 0, 20, NULL, 0,
                true, 'weather');
        $c->add('api_key_wu', '', 'text', 0, 20, 0, 200, true, 'weather');
        $c->add('ref_key_wu', '', 'text', 0, 20, 0, 210, true, 'weather');
    }

    return 0;
}

?>
