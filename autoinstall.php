<?php
/**
 * Automatic installation functions for the glFusion Weather plugin
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2011-2013 Lee Garner <lee@leegarner.com>
 * @package     weather
 * @version     v1.0.1
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 */
if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

/**
 *  @global string $_DB_dbms
 */
global $_DB_dbms;
global $_CONF_WEATHER;

/** Include plugin configuration */
require_once $_CONF['path'].'plugins/weather/weather.php';

/**
 *  Include required glFusion installation library
 */
require_once $_CONF['path'].'plugins/weather/sql/'.$_DB_dbms.'_install.php';

/**
 *  @global array $INSTALL_plugin['weather']
 */
$INSTALL_plugin['weather'] = array(
    'installer' => array(
        'type' => 'installer',
        'version'   => '1',
        'mode'      => 'install',
    ),
    'plugin' => array(
        'type' => 'plugin',
        'name'      => $_CONF_WEATHER['pi_name'],
        'ver'       => $_CONF_WEATHER['pi_version'],
        'gl_ver'    => $_CONF_WEATHER['gl_version'],
        'url'       => $_CONF_WEATHER['pi_url'],
        'display'   => $_CONF_WEATHER['pi_display_name'],
    ),
    array(
        'type'      => 'table',
        'table'     => $_TABLES['weather_cache'],
        'sql'       => $_SQL['weather_cache'],
    ),
    array(
        'type'      => 'group',
        'group'     => 'weather Admin',
        'desc'      => 'This group can administer the Weather plugin',
        'variable'  => 'admin_group_id',
        'addroot'   => true,
    ),
    array(
        'type'      => 'feature',
        'feature'   => 'weather.admin',
        'desc'      => 'Can administer the Weather plugin',
        'variable'  => 'admin_feature_id',
    ),
    array(
        'type'      => 'feature',
        'feature'   => 'weather.view',
        'desc'      => 'Can view the Weather plugin',
        'variable'  => 'view_feature_id',
    ),
    array(
        'type'      => 'mapping',
        'group'     => 'admin_group_id',
        'feature'   => 'admin_feature_id',
        'log'       => 'Adding admin feature to the admin group',
    ),
    array(
        'type'      => 'mapping',
        'findgroup' => 'Logged-in Users',
        'feature'   => 'view_feature_id',
        'log'       => 'Adding viewer feature to the users group',
    ),
    array(
        'type'          => 'block',
        'name'          => 'weather_current',
        'title'         => 'Current Weather',
        'phpblockfn'    => 'phpblock_weather_current',
        'block_type'    => 'phpblock',
        'group_id'      => 'admin_group_id',
        'is_enabled'    => 0,
    ),
);


/**
 * Puts the datastructures for this plugin into the glFusion database.
 * Note: Corresponding uninstall routine is in functions.inc.
 *
 * @return  boolean True if successful False otherwise
 */
function plugin_install_weather()
{
    global $INSTALL_plugin, $_CONF_WEATHER;

    $pi_display_name    = $_CONF_WEATHER['pi_display_name'];

    COM_errorLog("Attempting to install the $pi_display_name plugin", 1);

    $ret = INSTALLER_install($INSTALL_plugin[$_CONF_WEATHER['pi_name']]);
    if ($ret > 0) {
        return false;
    } else {
        return true;
    }
}


/**
 * Loads the configuration records for the Online Config Manager.
 *
 * @return  boolean     true = proceed with install, false = an error occured
 */
function plugin_load_configuration_weather()
{
    global $_CONF;

    require_once $_CONF['path'] . 'plugins/weather/install_defaults.php';

    return plugin_initconfig_weather();
}

?>
