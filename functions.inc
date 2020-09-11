<?php
/**
 * Common functions for the Weather plugin.
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
    die ('This file can not be used on its own.');
}

// Initialize the configuration
require_once $_CONF['path_system'] . 'classes/config.class.php';
$w_config = config::get_instance();
$_CONF_WEATHER = $w_config->get_config('weather');

// Get the static config items
require_once __DIR__ . '/weather.php';

// Define commonly-used paths
define('WEATHER_PI_PATH',
        $_CONF['path'] . 'plugins/' . $_CONF_WEATHER['pi_name']);
define('WEATHER_URL',
        $_CONF['site_url'] . '/' . $_CONF_WEATHER['pi_name']);
define('WEATHER_ADMIN_URL',
        $_CONF['site_admin_url'] . '/plugins/' . $_CONF_WEATHER['pi_name']);
define('WEATHER_CLASSPATH',  WEATHER_PI_PATH . '/classes');

// Define access levels
define('WEATHER_ACCESS_NONE', 0);   // no access
define('WEATHER_ACCESS_VIEW', 1);   // view access
define('WEATHER_ACCESS_SEARCH', 2); // can submit searches

// Define which locations to show in the php block
define('WEATHER_BLK_DEFAULT', 1);   // Default
define('WEATHER_BLK_PERSONAL', 2);  // Personal

// Define return codes to match message IDs
define('WEATHER_ERR_NOTFOUND', 1);
define('WEATHER_ERR_API', 2);
define('WEATHER_ERR_KEYMISSING', 3);

// Include local configuration overrides
if (file_exists(WEATHER_PI_PATH . '/config.php')) {
    include_once WEATHER_PI_PATH . '/config.php';
}

// Include the language file
$langpath = WEATHER_PI_PATH . '/language';
$langfile = "{$_CONF['language']}.php";
if (!is_file("$langpath/$langfile")) {
    $langfile = 'english_utf-8.php';
}
include_once "$langpath/$langfile";

// Include the custom language file, if any
if (is_file("$langpath/custom/$langfile")) {
    include_once "$langpath/custom/$langfile";
}

// Some housekeeping. Make sure config values have good data
$_CONF_WEATHER['cache_minutes'] = max((int)$_CONF_WEATHER['cache_minutes'], 15);

spl_autoload_register(function ($class)
{
    // project-specific namespace prefix
    $prefix = 'Weather\\';

    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }

    // base directory for the namespace prefix
    $base_dir = __DIR__ . '/classes/';

    // get the relative class name
    $relative_class = substr($class, $len);

    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.class.php';

    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});


/**
 * Return the current code (not necessarily installed) version.
 *
 * @return  string  Current version
 */
function plugin_chkVersion_weather()
{
    global $_CONF_WEATHER;
    return $_CONF_WEATHER['pi_version'];
}


/**
 * Returns the items for this plugin that should appear on the main menu.
 *
 * @return  array   Associative array of menu items to be added to menu
 */
function Xplugin_getmenuitems_weather()
{
    global $LANG_WEATHER, $_CONF_WEATHER;

    $menuitems = array();
	if (
        $_CONF_WEATHER['usermenu_option'] &&
        ( SEC_hasRights('weather.view') ||
            ( COM_isAnonUser() &&
            $_CONF_WEATHER['anon_access'] & WEATHER_ACCESS_VIEW )
        )
    ) {
    	$menuitems[$LANG_WEATHER['pi_title']] = WEATHER_URL . '/index.php';
    }
    return $menuitems;
}


/**
 * Automatically uninstall the plugin.
 *
 * @return  array   Array of plugin elements to remove
 */
function plugin_autouninstall_weather()
{
    global $_CONF_WEATHER;

    $out = array (
        'tables'    => array(
                        'weather_cache',
                    ),
        'groups'    => array($_CONF_WEATHER['pi_name'] . ' Admin'),
        'features'  => array(
                        $_CONF_WEATHER['pi_name'] . '.admin',
                        $_CONF_WEATHER['pi_name'] . '.view',
                    ),
        'php_blocks' => array('phpblock_weather_current'),
        'vars'      => array($_CONF_WEATHER['pi_name'] . '_gid')
    );

    \Weather\Cache::clear();
    return $out;

}


/**
 * Perform actions needed when the plugin is enabled or disabled.
 * - Turns off PHP blocks if disabling. Blocks must be turned on manually.
 *
 * @param   boolean $enabling   True to enable plugin, False to disable
 */
function plugin_enablestatechange_weather($enabling)
{
    global $_TABLES;

    $enabling = $enabling ? 1 : 0;

    // turns block off if on, but will not turn it on if off.
    if ($enabling != 1) {
        DB_query ("UPDATE {$_TABLES['blocks']}
                    SET is_enabled = 0
                    WHERE (type = 'phpblock')
                    AND (phpblockfn LIKE 'phpblock_weather_%')");
    }
}


/**
 * Weather block to show user default or global default for anonymous users.
 * If a location is requested, then that location is displayed regardless
 * of the configuration.
 * Because of how the block manager handles parameters, the $loc string
 * will need to be a single string, no commas (e.g. a postal code).
 *
 * @param   array   $A      Array of block information (optional)
 * @param   string  $loc    Loctation to display (optional)
 * @return  string          Formatted html for block
 */
function phpblock_weather_current($A=array(), $loc='')
{
    global $_CONF_WEATHER, $_TABLES, $_USER;

    // If a specific location isn't requested, figure out a default
    if (empty($loc)) {
        if (COM_isAnonUser()) {
            if (!($_CONF_WEATHER['blk_show_loc'] & WEATHER_BLK_DEFAULT))
                return '';
            else
                $loc = $_CONF_WEATHER['default_loc'];
        } else {
            // Show the personal block, if configured & available
            if ($_CONF_WEATHER['blk_show_loc'] & WEATHER_BLK_PERSONAL) {
                $loc = DB_getItem($_TABLES['userinfo'], 'location',
                        "uid='{$_USER['uid']}'");
            }
            if (empty($loc)) {
                // Fallback to default, if allowed
                if (!($_CONF_WEATHER['blk_show_loc'] & WEATHER_BLK_DEFAULT))
                    return '';
                else
                    $loc = $_CONF_WEATHER['default_loc'];
            }
        }
    }

    $A = \Weather\API::getInstance()->getWeather($loc);
    if (!is_array($A) || !isset($A['current'])) {
        return '';
    }

    $dt = COM_getUserDateTimeFormat($A['info']['ts']);
    $T = new Template(WEATHER_PI_PATH . '/templates');
    $T->set_file('block', 'phpblock.thtml');
    $KM = $_CONF_WEATHER['k_m'] == 'K' ? 'K' : 'M';
    $T->set_var(array(
        'curr_wind' => $A['current']['wind_' . $KM],
        'temp_f'    => $A['current']['temp_f'],
        'temp_c'    => $A['current']['temp_c'],
        'curr_cond' => $A['current']['condition'],
        'city'      => $A['info']['city'],
        //'icon_url'  => Weather\api::getIcon($A['current']['icon']),
        'icon_url'  => \Weather\API::getInstance()->getIcon($A['current']['icon']),
        'icon_name' => $A['current']['icon_name'],
        'date'      => $dt[0],
        //'linkback'  => Weather\api::linkback('block'),
        'linkback'  => \Weather\API::getInstance()->linkback('block'),
    ) );
    $T->parse('output', 'block');
    $retval = $T->finish($T->get_var('output'));
    return $retval;
}


/**
 * Get additional CSS into the site header.
 * Checks first for a layout-specific version, then a custom plugin version,
 * and finally the default if nothing else found.
 *
 * @return  array   List of paths to Javascript files
 */
function plugin_getheadercss_weather()
{
    global $_CONF, $_CONF_WEATHER;

    $styles = array();
    $files = array('style.css');

    $layoutpath = $_CONF['path_layout'].'plugins/'.$_CONF_WEATHER['pi_name'].'/';
    foreach ($files as $file) {
        if (@file_exists($layoutpath . $file)) {
            // check for custom layout first
        	$styles[] = $layoutpath . $file;
        } elseif (@file_exists(WEATHER_PI_PATH . '/css/custom/' . $file)) {
            // then custom plugin style
            $styles[] = WEATHER_PI_PATH . '/css/custom/' . $file;
        } else {
            // finally use the default plugin style
            $styles[] = WEATHER_PI_PATH . '/css/' . $file;
        }
    }

	return $styles;
}


/**
 * Implements the [weather:] autotag.
 *
 * @param   string  $op         Operation to perform
 * @param   string  $content    Page Content
 * @param   string  $autotag    Autotag parameters
 * @return  string              HTML generated by autotag
 */
function plugin_autotags_weather($op, $content = '', $autotag = '')
{
    global $_CONF, $_TABLES, $_CONF_WEATHER;

    $HTML = '';

    switch ($op) {
    case 'tagname':
        $retval = $_CONF_WEATHER['pi_name'];
        break;
    case 'parse':
        $HTML = '';
        $option = COM_applyFilter($autotag['parm1']);
        switch ($option) {
        case 'current':
            $loc = $autotag['parm2'];
            if (!empty($loc)) {
                $A = \Weather\API::getInstance()->getWeather($loc);
                if (!is_array($A))
                    return $content;

                $dt = COM_getUserDateTimeFormat($A['info']['ts']);
                $T = new Template(WEATHER_PI_PATH . '/templates');
                $T->set_file('block', 'autotag_current.thtml');
                $T->set_var(array(
                    'curr_wind' => $A['current']['wind_condition'],
                    'temp_f'    => $A['current']['temp_f'],
                    'temp_c'    => $A['current']['temp_c'],
                    'curr_cond' => $A['current']['condition'],
                    'city'      => $A['info']['city'],
                    //'icon_url'  => Weather\api::getIcon($A['current']['icon']),
                    'icon_url'  => \Weather\API::getInstance()->getIcon($A['current']['icon']),
                    'icon_name' => $A['current']['icon_name'],
                    'date'      => $dt[0],
                ) );
                $T->parse('output', 'block');
                $HTML = $T->finish($T->get_var('output'));
            }
            break;

        case 'full':
            $loc = $autotag['parm2'];
            if (!empty($loc)) {
                $HTML = WEATHER_embed($loc);
            }
            break;
        } // switch($option)

        $retval = str_replace($autotag['tagstr'], $HTML, $content);
        break;

    default:
        $retval = $content;
        break;

    }   // switch ($op)

    return $retval;

}


/**
 * API function to get a weather block embedded in a page.
 *
 * @param   mixed   $loc        Location to show, or array of data
 * @param   boolean $linkback   True if a single embedded display
 * @return  string      HTML for weather panel
 */
function WEATHER_embed($loc, $linkback = true)
{
    global $_CONF, $_CONF_WEATHER;

    $retval = '';
    $T = new Template(WEATHER_PI_PATH . '/templates');
    $T->set_file('embed', 'embed.thtml');

    $API = Weather\API::getInstance();
    if (!$API->isValid()) {
        return '';
    }
    $A = $API->getWeather($loc['loc']);
    if (is_array($A)) {
        $dt = COM_getUserDateTimeFormat($A['info']['ts']);
        $FC = $_CONF_WEATHER['f_c'] == 'C' ? 'C' : 'F';
        $KM = $_CONF_WEATHER['k_m'] == 'K' ? 'K' : 'M';
        $T->set_var(array(
            'curr_wind' => $A['current']['wind_' . $KM],
            'curr_icon_url' => $API->getIcon($A['current']['icon']),
            'curr_icon_name' => $A['current']['icon_name'],
            'temp_f'    => $A['current']['temp_f'],
            'temp_c'    => $A['current']['temp_c'],
            'curr_cond' => $A['current']['condition'],
            'city'      => $A['info']['city'],
            'date'      => $dt[0],
            'curr_humidity' => $A['current']['humidity'],
            'linkback'  => $linkback ? $API->linkback() : '',
        ) );
        $T->set_block('embed', 'ForeCastItem', 'fcItem');
        foreach ($A['forecast'] as $day) {
            $T->set_var(array(
                'high'  => round($day['high' . $FC]),
                'low'   => round($day['low' . $FC]),
                'day'   => $day['day'],
                'condition' => $day['condition'],
                'icon_name' => $day['icon_name'],
                'icon_url' => $API->getIcon($day['icon']),
                'wind'  => $day['wind_' . $KM],
                'humidity' => isset($day['humidity']) ? $day['humidity'] : '',
                'fc_text' => !empty($day['fc_text_' . $FC]) ? $day['fc_text_' . $FC] : '',
            ) );
            $T->parse('fcItem', 'ForeCastItem', true);
        }
        $T->parse('output', 'embed');
        $retval = $T->finish($T->get_var('output'));
    }
    return $retval;
}


/**
 * Get the site header, with or without left blocks according to config.
 *
 * @see     function COM_siteHeader()
 * @param   string  $title  Optional text for the title bar
 * @param   string  $meta   Optional additional meta text
 * @return  string          HTML for site header
 */
function WEATHER_siteHeader($title='', $meta='')
{
    global $_CONF_WEATHER;

    $retval = '';

    switch( $_CONF_WEATHER['displayblocks'] ) {
    case 0 :    // none
    case 2 :    // right only
        $retval .= COM_siteHeader('none', $title, $meta);
        break;
    case 1 :    // left only
    case 3 :    // both
    default :
        $retval .= COM_siteHeader('menu', $title, $meta);
        break;
    }
    return $retval;
}


/**
 * Get the site footer, with or without right blocks according to config.
 *
 * @see     function COM_siteFooter()
 * @return  string      HTML for site footer
 */
function WEATHER_siteFooter()
{
    global $_CONF, $_CONF_WEATHER;

    $retval = '';

    switch( $_CONF_WEATHER['displayblocks'] ) {
    case 0 :    // none
    case 1 :    // left only
        $retval .= COM_siteFooter();
        break;
    case 2 :    // right only
    case 3 :    // left and right
    default :
        $retval .= COM_siteFooter( true );
        break;
    }
    return $retval;
}

/**
 * Get the link to a documentation file.
 * Returns the URL to the file, or to the directory if no file requested.
 *
 * @see     plugin_getconfigelementhelp_weather()
 * @param   string  $file       HTML file
 * @param   string  $doclang    Documentation language
 * @return  string              URL to document directory or file
 */
function WEATHER_getDocURL($file='', $doclang='english')
{
    global $_CONF_WEATHER;

    $doc_path = WEATHER_PI_PATH . "/docs/$doclang/";
    $doc_url = '';

    if ($file != '') {
        $file .= '.html';
        if (!file_exists($doc_path . $file)) {
            $doclang = 'english';
        }
        $doc_url = WEATHER_URL . "/docs/$doclang/$file";
    } else {
        // Just return the URL to the directory
        if (!is_dir($doc_path)) {
            $doclang = 'english';
        }
        $doc_url = WEATHER_URL . "/docs/$doclang/";
    }

    return $doc_url;
}


/**
 * Get the URL to the localized configuration help file.
 *
 * @see     WEATHER_getDocURL()
 * @param   string  $option     Configuration option to display
 * @param   string  $doclang    Documentation language requested
 * @return  array               Array of (url, window_type)
 */
function plugin_getconfigelementhelp_weather($option, $doclang='english')
{
    $doc_url = WEATHER_getDocURL('config', $doclang);
    if (empty($doc_url))
        return false;

    if (!empty($option)) {
        $doc_url .= '#desc_' . $option;
    }
    return array($doc_url, 0);
}


/**
 * Upgrade the plugin
 * Calls weather_do_upgrade() to perform the upgrade if not already
 * at the current revision.
 *
 * @return  boolean True on success, error code on failure
 */
function plugin_upgrade_weather()
{
    require_once __DIR__ . '/upgrade.inc.php';
    return weather_do_upgrade();
}


/**
 * This will put an option for the plugin in the command and control block.
 *
 * Add the plugin name, icon and link to the command and control block in
 * moderation.php
 *
 * @return  array  Array containing (plugin name, admin url, url of plugin icon)
 */
function plugin_cclabel_weather()
{
    global $LANG_WEATHER;

    if (plugin_isadmin_weather()) {
        return array(
            $LANG_WEATHER['pi_title'],
            WEATHER_ADMIN_URL . '/index.php',
            plugin_geticon_weather(),
        );
    } else {
        return '';
    }
}


/**
 * Returns the URL of the plugin's icon.
 *
 * @return  string  URL of the icon
 */
function plugin_geticon_weather()
{
    return WEATHER_URL . '/images/weather.png';
}


/**
 * Adds the plugin to the Admin menu.
 *
 * @return  array   Array(pi_name, admin url, # of items) or '' for none.
 */
function plugin_getadminoption_weather()
{
    global $LANG_WEATHER;

    if (plugin_isadmin_weather()) {
        $retval = array(
            $LANG_WEATHER['pi_title'],
            WEATHER_ADMIN_URL . '/index.php',
            '',
        );
    } else {
        $retval = '';
    }
    return $retval;
}


/**
 * Determine if the current user is a plugin admin.
 *
 * @see     SEC_hasRights()
 * @return  boolean     True for an administrator, False if not
 */
function plugin_isadmin_weather()
{
    static $retval = NULL;
    if ($retval === NULL) {
        $retval = SEC_hasRights('weather.admin');
    }
    return $retval;
}


/**
 * Get the display for a single weather item.
 * Includes description and forecast.
 *
 * @param   array   $args       array('loc'=>address info)
 * @param   mixed   $output     HTML for display
 * @param   mixed   $svc_msg    Not used
 * @return  string      Our configured currency code.
 */
function service_embed_weather($args, &$output, &$svc_msg)
{
    // If the requester doesn't specifically disable the linkback,
    // assume that it must be provided.
    if (!isset($args['linkback'])) {
        $args['linkback'] = true;
    }
    $output = WEATHER_embed($args, $args['linkback']);
    return PLG_RET_OK;
}

?>
