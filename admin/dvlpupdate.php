<?php
/**
 * Apply updates to Weather during development.
 * Calls upgrade function with "ignore_errors" set so repeated SQL statements
 * won't cause functions to abort.
 *
 * Only updates from the previous released version.
 *
 * @author      Mark R. Evans mark AT glfusion DOT org
 * @copyright   Copyright (c) 2018-2022 Lee Garner <lee@leegarner.com>
 * @package     weather
 * @version     v2.0.2
 * @since       v1.1.2
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */

require_once '../../../lib-common.php';
use glFusion\Log\Log;

if (!SEC_inGroup('Root')) {
    // Someone is trying to illegally access this page
    Log::write('system', Log::ERROR,
        "Someone has tried to access the Weather Development Code Upgrade Routine without proper permissions.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: " . $_SERVER['REMOTE_ADDR']
    );
    COM_404();
    exit;
}
require_once WEATHER_PI_PATH . '/upgrade.inc.php';   // needed for set_version()
if (function_exists('CACHE_clear')) {
    CACHE_clear();
}
\Weather\Cache::clear();

// Force the plugin version to the previous version and do the upgrade
$_PLUGIN_INFO['weather']['pi_version'] = '1.1.1';
weather_do_upgrade(true);

// need to clear the template cache so do it here
if (function_exists('CACHE_clear')) {
    CACHE_clear();
}
header('Location: '.$_CONF['site_admin_url'].'/plugins.php?msg=600');
exit;

?>
