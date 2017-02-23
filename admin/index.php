<?php
/**
*   Administrator interface for the Weather plugin.
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2012 Lee Garner <lee@leegarner.com>
*   @package    weather
*   @version    1.0.1
*   @license    http://opensource.org/licenses/gpl-2.0.php
*               GNU Public License v2 or later
*   @filesource
*/

/** Include required glFusion common functions */
require_once '../../../lib-common.php';

/** Include system admin functions */
USES_lib_admin();

/**
*   Create the admin menu block.
*   @param  string  $desc_text  Description text to appear in the menu.
*   @return string  HTML for the menu block
*/
function WEA_adminMenu($view = '')
{
    global $LANG_ADMIN, $LANG_WEATHER, $_CONF, $_CONF_WEATHER;

    if (empty($view)) $view = 'default';
    $desc_text = $LANG_WEATHER['menu_hlp'][$view];

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home']),
        array('url' => WEATHER_ADMIN_URL . '/index.php?purge=x',
              'text' => $LANG_WEATHER['purge_cache']),
    );

    $header_str = $LANG_WEATHER['pi_title'] . ' ' . $LANG_WEATHER['version'] .
        ' ' . $_CONF_WEATHER['pi_version'];

    $retval .= COM_startBlock($header_str, '', COM_getBlockTemplate('_admin_block', 'header'));
    $retval .= ADMIN_createMenu($menu_arr, $desc_text, '');

    return $retval;
}


/*
* Main
*/

// If plugin is installed but not enabled, display an error and exit gracefully
if (!in_array('weather', $_PLUGINS)) {
    /** Include the 404 error page if needed */
    COM_404();
    exit;
}

// Only let admin users access this page
if (!plugin_isadmin_weather()) {
    // Someone is trying to illegally access this page
    COM_errorLog("Someone has tried to illegally access the dailyquote Admin page.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: $REMOTE_ADDR",1);
    COM_404();
}

$action = '';
$expected = array(
    'purge',
);
foreach($expected as $provided) {
    if (isset($_POST[$provided])) {
        $action = $provided;
        break;
    } elseif (isset($_GET[$provided])) {
    	$action = $provided;
        break;
    }
}
$content = '';      // initialize variable for page content

switch ($action) {
case 'purge':       // Purge the cache
    $sql = "TRUNCATE {$_TABLES['weather_cache']}";
    DB_query($sql, 1);
    if (DB_error()) {
        $msg = $LANG_WEATHER['err_purge_cache'];
    } else {
        $msg = $LANG_WEATHER['cache_purged'];
    }
    break;

default:
    break;
}

$display = COM_siteHeader();
if (!empty($msg)) {
    $display .= COM_showMessageText($msg);
}
$display .= WEA_adminMenu($view);
$display .= $content;
$display .= COM_endBlock();
$display .= COM_siteFooter();
echo $display;

?>
