<?php
/**
 * Upgrade the plugin.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2012-2022 Lee Garner <lee@leegarner.com>
 * @package     weather
 * @version     v1.1.0
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */

global $_CONF, $_CONF_WEATHER, $_DB_dbms;

/** Include default values for new config items */
require_once __DIR__ . '/sql/mysql_install.php';
global $_SQL_UPGRADE;
use glFusion\Log\Log;

/**
 * Sequentially perform version upgrades.
 *
 * @param   boolean $dvlp   True if this is a development update
 * @return  boolean     True on success, False on failure
 */
function weather_do_upgrade($dvlp=false)
{
    global $_CONF_WEATHER, $_PLUGIN_INFO, $_WEA_DEFAULT, $_TABLES;

    if (isset($_PLUGIN_INFO[$_CONF_WEATHER['pi_name']])) {
        if (is_array($_PLUGIN_INFO[$_CONF_WEATHER['pi_name']])) {
            // glFusion > 1.6.5
            $current_ver = $_PLUGIN_INFO[$_CONF_WEATHER['pi_name']]['pi_version'];
        } else {
            // legacy
            $current_ver = $_PLUGIN_INFO[$_CONF_WEATHER['pi_name']];
        }
    } else {
        return false;
    }
    $installed_ver = plugin_chkVersion_weather();

    if (!COM_checkVersion($current_ver, '1.0.0')) {
        $current_ver = '1.0.0';
        if (!weather_do_upgrade_sql($current_ver)) return false;
        if (!weather_do_set_version($current_ver)) return false;
    }

    if (!COM_checkVersion($current_ver, '1.0.3')) {
        $current_ver = '1.0.3';
        if (!weather_do_set_version($current_ver)) return false;
    }

    if (!COM_checkVersion($current_ver, '1.1.0')) {
        $current_ver = '1.0.4';
        if (!weather_do_set_version($current_ver)) return false;
    }

    if (!COM_checkVersion($current_ver, '2.0.0')) {
        $current_ver = '2.0.0';
        if (!weather_do_upgrade_sql($current_ver, $dvlp)) return false;
        if (!weather_do_set_version($current_ver)) return false;
    }
    if (!COM_checkVersion($current_ver, '2.0.2')) {
        $current_ver = '2.0.2';
        /*$sql = "SELECT name, value, type FROM {$_TABLES['conf_values']}
            WHERE group_name = 'weather'
            AND name IN ('api_key_openweather', 'api_key_weatherstack', 'api_key_wunlocked', 'app_id_wunlocked');";
        $res = DB_query($sql);
        while ($A = DB_fetchArray($res, false)) {
            if ($A['type'] != 'passwd') {
                $value = DB_escapeString(@serialize(COM_encrypt(@unserialize($A['value']))));
                DB_query("UPDATE {$_TABLES['conf_values']}
                    SET type = 'passwd', value = '$value'
                    WHERE group_name = 'weather' AND name = '{$A['name']}'");
            }
        }*/
        if (!weather_do_set_version($current_ver)) return false;
    }

    // Final version update to catch updates that don't go through
    // any of the update functions, e.g. code-only updates
    if (!COM_checkVersion($current_ver, $installed_ver)) {
        if (!weather_do_set_version($installed_ver)) {
            Log::write('system', Log::ERROR,
                $_CONF_WEATHER['pi_display_name'] .
                " Error performing final update $current_ver to $installed_ver"
            );
            return false;
        }
    }

    // Sync the config items
    require_once __DIR__ . '/install_defaults.php';
    plugin_updateconfig_weather();

    Log::write('system', Log::INFO, "Successfully updated the {$_CONF_WEATHER['pi_display_name']} Plugin");
    \Weather\Cache::clear();
    return true;
}


/**
 * Execute the SQL statement to perform a version upgrade.
 * An empty SQL parameter will return success.
 *
 * @param   string  $version        Version being upgraded to
 * @param   boolean $ignore_errors  True to ignore sql errors and continue
 * @return  boolean     True for success, False for failure
 */
function weather_do_upgrade_sql($version, $ignore_errors=false)
{
    global $_TABLES, $_CONF_WEATHER, $_SQL_UPGRADE;

    // If no sql statements passed in, return success
    if (!isset($_SQL_UPGRADE[$version]) || !is_array($_SQL_UPGRADE[$version])) {
        return true;
    }

    // Execute SQL now to perform the upgrade
    Log::write('system', Log::INFO, "--Updating Weather Plugin to version $version");
    foreach ($_SQL_UPGRADE[$version] as $sql) {
        Log::write('system', Log::INFO, "Weather Plugin $version update: Executing SQL => $sql");
        DB_query($sql, '1');
        if (DB_error()) {
            Log::write('system', Log::ERROR, "SQL Error during Weather plugin update. SQL: $sql");
            if (!$ignore_errors) return false;
        }
    }
    return true;
}


/**
 * Update the plugin version number in the database.
 * Called at each version upgrade to keep up to date with
 * successful upgrades.
 *
 * @param   string  $ver    New version to set
 * @return  boolean         True on success, False on failure
 */
function weather_do_set_version($ver)
{
    global $_TABLES, $_CONF_WEATHER;

    // now update the current version number.
    $sql = "UPDATE {$_TABLES['plugins']} SET
            pi_version = '{$_CONF_WEATHER['pi_version']}',
            pi_gl_version = '{$_CONF_WEATHER['gl_version']}',
            pi_homepage = '{$_CONF_WEATHER['pi_url']}'
        WHERE pi_name = '{$_CONF_WEATHER['pi_name']}'";

    $res = DB_query($sql, 1);
    if (DB_error()) {
        Log::write('system', Log::ERROR, "Error updating the {$_CONF_WEATHER['pi_display_name']} Plugin version");
        return false;
    } else {
        return true;
    }
}


/**
 * Upgrade to version 0.1.3.
 * Implements WorldWeatherOnline provider, adds api key and
 * English/Metric selections to plugin configuration.
 *
 * @return  integer 0, no sql to upgrade here
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
 * Upgrade to version 1.0.0.
 * Implements Weather Underground provider, adds provider selection
 * to plugin configuration.
 *
 * @return  integer 0, no sql to upgrade here
 */
function weather_upgrade_1_0_0()
{
    global $_TABLES, $_CONF_WEATHER, $_WEA_DEFAULT;

    $status =  weather_do_upgrade_sql('1.0.0');
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


/**
 * Remove deprecated files.
 * Errors in unlink() and rmdir() are ignored.
 */
function weather_remove_old_files()
{
    global $_CONF;

    $paths = array(
        // private/plugins/weather
        __DIR__ => array(
            //2.0.0
            'classes/apiBase.class.php',
            'classes/api_apixu.class.php',
            'classes/api_wu.class.php',
            'classes/api_wwo.class.php',
            'classes/google_api.inc.php',
        ),
        // public_html/paypal
        $_CONF['path_html'] . 'weather' => array(
        ),
        // admin/plugins/paypal
        $_CONF['path_html'] . 'admin/plugins/paypal' => array(
        ),
    );

    foreach ($paths as $path=>$files) {
        foreach ($files as $file) {
            @unlink("$path/$file");
        }
    }
}

?>
