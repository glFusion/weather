<?php
/**
*   Common functions for the Weather plugin.
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2011-2012 Lee Garner <lee@leegarner.com>
*   @package    weather
*   @version    1.0.0
*   @license    http://opensource.org/licenses/gpl-2.0.php
*               GNU Public License v2 or later
*   @filesource
*/
if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

// Initialize the configuration
require_once $_CONF['path_system'] . 'classes/config.class.php';
$w_config = config::get_instance();
$_CONF_WEATHER = $w_config->get_config('weather');

// Get the static config items
require_once dirname(__FILE__) . '/weather.php';

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
    $langfile = 'english.php';
}
include_once "$langpath/$langfile";

// Include the custom language file, if any
if (is_file("$langpath/custom/$langfile")) {
    include_once "$langpath/custom/$langfile";
}

// Some housekeeping. Make sure config values have good data
$_CONF_WEATHER['cache_minutes'] = max((int)$_CONF_WEATHER['cache_minutes'], 15);
$_CONF_WEATHER['_is_uikit'] = $_SYSTEM['framework'] == 'uikit' ? true : false;
$_CONF_WEATHER['_iconset'] = $_CONF_WEATHER['_is_uikit'] ? 'uk-icon' : 'fa fa';

function USES_weather_upgrade()
{   require_once WEATHER_PI_PATH . '/upgrade.inc.php';  }

/**
*   Load the weather provider API class based on the plugin configuration
*   @since  1.0.0
*/
function USES_weather_class_api()
{
    global $_CONF_WEATHER;

    switch ($_CONF_WEATHER['provider']) {
    case 'wu':
    default:    // wwo no longer supported
        require_once WEATHER_CLASSPATH . '/wunderground_api.class.php';
        break;
    case 'goog':
        // included more as a comment, Google API is deprecated
        require_once WEATHER_CLASSPATH . '/google_api.inc.php';
        break;
    case 'wwo':
        require_once WEATHER_CLASSPATH . '/worldweather_api.class.php';
        break;
    case 'apixu':
        require_once WEATHER_CLASSPATH . '/apixu_api.class.php';
        break;
    }
}


/**
*   Return the current code (not necessarily installed) version
*
*   @return string      Current version
*/
function plugin_chkVersion_weather()
{
    global $_CONF_WEATHER;
    return $_CONF_WEATHER['pi_version'];
}


/**
*   Returns the items for this plugin that should appear on the main menu.
*
*   @return array   Associative array of menu items to be added to menu
*/
function plugin_getmenuitems_weather()
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
*   Automatically uninstall the plugin
*
*   @return array Array of plugin elements to remove
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

    return $out;

}


/**
*   Perform actions needed when the plugin is enabled or disabled.
*   - Turns off PHP blocks if disabling. Blocks must be turned on manually.
*
*   @param  boolean $enabling   True to enable plugin, False to disable
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


/*
*   Weather block to show user default or global default for anonymous users.
*   If a location is requested, then that location is displayed regardless
*   of the configuration.
*   Because of how the block manager handles parameters, the $loc string
*   will need to be a single string, no commas (e.g. a postal code).
*
*   @param  array   $A      Array of block information (optional)
*   @param  string  $loc    Loctation to display (optional)
*   @return string          Formatted html for block
*/
function phpblock_weather_current($A=array(), $loc='')
{
    global $_CONF_WEATHER, $_TABLES, $_USER;
    USES_weather_class_api();

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

    $A = WEATHER_getWeather($loc);
    if (!is_array($A))
        return '';

    $dt = COM_getUserDateTimeFormat($A['info']['ts']);
    $T = new Template(WEATHER_PI_PATH . '/templates');
    $T->set_file('block', 'phpblock.thtml');
    $T->set_var(array(
        'curr_wind' => $A['current']['wind_condition'],
        'temp_f'    => $A['current']['temp_f'],
        'temp_c'    => $A['current']['temp_c'],
        'curr_cond' => $A['current']['condition'],
        'city'      => $A['info']['city'],
        'icon_url'  => Weather::getIcon($A['current']['icon']),
        'icon_name' => $A['current']['icon_name'],
        'date'      => $dt[0],
        'linkback'  => Weather::linkback('block'),
    ) );
    $T->parse('output', 'block');
    $retval = $T->finish($T->get_var('output'));
    return $retval;
}


/**
*   Get additional CSS into the site header.
*   Checks first for a layout-specific version, then a custom plugin version,
*   and finally the default if nothing else found.
*
*   @return array   List of paths to Javascript files
*/
function plugin_getheadercss_weather()
{
    global $_CONF, $_CONF_FRM;

    $styles = array();
    $files = array('style.css');

    $layoutpath = $_CONF['layout_path'].'plugins/'.$_CONF_FRM['pi_name'].'/';
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
*   Implements the [weather:] autotag.
*
*   @param  string  $op         Operation to perform
*   @param  string  $content    Page Content
*   @param  string  $autotag    Autotag parameters
*   @return string              HTML generated by autotag
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
                USES_weather_class_api();
                $A = WEATHER_getWeather($loc);
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
                    'icon_url'  => Weather::getIcon($A['current']['icon']),
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
*   Retrieve weather information.
*   Checks the cache table first for a recent entry.  If not found,
*   get weather info from Google and update the cache.
*
*   @uses   WEATHER_updateCache()
*   @param  string  $loc    Location to get
*   @return mixed   Array of weather information, or integer error code
*/
function WEATHER_getWeather($loc)
{
    global $_TABLES, $_CONF_WEATHER;

    // cache_minutes is already sanitized as an intgeger
    $db_loc = strtolower(COM_sanitizeId($loc, false));
    $sql = "SELECT * FROM {$_TABLES['weather_cache']}
            WHERE location = '$db_loc'
            AND ts > NOW() - INTERVAL {$_CONF_WEATHER['cache_minutes']} MINUTE";
    $res = DB_query($sql);
    if ($res && DB_numRows($res) == 1) {
        $A = DB_fetchArray($res, false);
    } else {
        $A = array();
    }

    if (!empty($A)) {
        // Got current cache data, return it
        $retval = @unserialize($A['data']);
    } else {
        // Try to get new data from the provider
        USES_weather_class_api();
        $w = new Weather($loc);
        if ($w->error > 0) {
            if (!empty($A)) {
                // Got old data from cache, better than nothing
                $retval = @unserialize($A['data']);
            } else {
                $retval = $w->error;
            }
        } else {
            // Got good data from the weather API, use it and update cache
            $retval = $w->getData();
            WEATHER_updateCache($db_loc, $retval);
        }
    }
    return $retval;
}


/**
*   Updates the weather cache table and cleans out stale entries.
*
*   @param  string  $loc    Location to use as the key, already database-safe
*   @param  array   $data   Weather data to be saved
*/
function WEATHER_updateCache($loc, $data)
{
    global $_TABLES, $_USER, $_CONF_WEATHER;

    $data = DB_escapeString(serialize(WEATHER_sanitize($data)));

    // Delete any stale entries and the current location to be replaced
    // cache_minutes is already sanitized as an intgeger
    DB_query("DELETE FROM {$_TABLES['weather_cache']}
            WHERE ts < NOW() - INTERVAL {$_CONF_WEATHER['cache_minutes']} MINUTE
            OR location = '$loc'");

    // Insert the new record to be cached
    DB_query("INSERT INTO {$_TABLES['weather_cache']}
                (location, uid, data)
            VALUES
                ('$loc', '{$_USER['uid']}', '$data')");
}


/**
*   API function to get a weather block embedded in a page.
*
*   @param  mixed   $loc        Location to show, or array of data
*   @param  boolean $linkback   True if a single embedded display
*   @return string      HTML for weather panel
*/
function WEATHER_embed($loc, $linkback = true)
{
    global $_CONF, $_CONF_WEATHER;

    USES_weather_class_api();

    $retval = '';
    $T = new Template(WEATHER_PI_PATH . '/templates');
    $T->set_file('embed', 'embed.thtml');
    if (is_array($loc)) {       // Already got the weather into this array
        $A = $loc;
    } else {                    // Need to read the location's weather
        $A = WEATHER_getWeather($loc);
    }

    if (is_array($A)) {
        $dt = COM_getUserDateTimeFormat($A['info']['ts']);
        $FC = $_CONF_WEATHER['f_c'] == 'C' ? 'C' : 'F';
        $KM = $_CONF_WEATHER['k_m'] == 'K' ? 'K' : 'M';
        $T->set_var(array(
            'curr_wind' => $A['current']['wind_' . $KM],
            'curr_icon_url' => Weather::getIcon($A['current']['icon']),
            'curr_icon_name' => $A['current']['icon_name'],
            'temp_f'    => $A['current']['temp_f'],
            'temp_c'    => $A['current']['temp_c'],
            'curr_cond' => $A['current']['condition'],
            'city'      => $A['info']['city'],
            'date'      => $dt[0],
            'curr_humidity' => $A['current']['humidity'],
            'linkback'  => $linkback ? Weather::linkback() : '',
        ) );
        $T->set_block('embed', 'ForeCastItem', 'fcItem');
        foreach ($A['forecast'] as $day) {
            $T->set_var(array(
                'high'  => $day['high' . $FC],
                'low'   => $day['low' . $FC],
                'day'   => $day['day'],
                'condition' => $day['condition'],
                'icon_name' => $day['icon_name'],
                'icon_url' => Weather::getIcon($day['icon']),
                'wind'  => $day['wind_' . $KM],
                'humidity' => $day['humidity'],
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
*   Get the site header, with or without left blocks according to config.
*
*   @see    function COM_siteHeader()
*   @param  string  $title  Optional text for the title bar
*   @param  string  $meta   Optional additional meta text
*   @return string          HTML for site header
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
*   Get the site footer, with or without right blocks according to config.
*
*   @see    function COM_siteFooter()
*   @return string      HTML for site footer
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
*   Sanitize values.  Recurse $var if it is an array.
*
*   @param  mixed   $var    Value or array to sanitize
*   @return mixed           Sanitized version of $var
*/
function WEATHER_sanitize($var)
{
    if (is_array($var)) {
        //run each array item through this function (by reference)      
        foreach ($var as &$val) {
            $val = WEATHER_sanitize($val, $quotes);
        }
    } else if (is_string($var)) {   //clean strings
        $var = COM_checkHTML($var);
    } else if (is_null($var)) {   //convert null variables to SQL NULL
        $var = "NULL";
    } else if (is_bool($var)) {   // convert boolean variables to binary boolean
        $var = ($var) ? 1 : 0;
    }
    return $var;
}


/**
*   Get the link to a documentation file.
*   Returns the URL to the file, or to the directory if no file requested.
*
*   @see    plugin_getconfigelementhelp_weather()
*   @param  string  $file       HTML file
*   @param  string  $doclang    Documentation language
*   @return string              URL to document directory or file
*/
function WEATHER_getDocURL($file='', $doclang='english')
{
    global $_CONF_WEATHER;

    $doc_path = WEATHER_PI_PATH . "/docs/$doclang/";
    $doc_url = '';

    if ($file != '') {
        if (!$_CONF_WEATHER['_is_uikit']) $file .= '.legacy';
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
*   Get the URL to the localized configuration help file.
*
*   @see    WEATHER_getDocURL()
*   @param  string  $option     Configuration option to display
*   @param  string  $doclang    Documentation language requested
*   @return array               Array of (url, window_type)
*/
function plugin_getconfigelementhelp_weather($option, $doclang='english')
{
    $doc_url = WEATHER_getDocURL('config', $doclang);
    if (empty($doc_url))
        return false;

    if (!empty($option)) {
        $file .= '#desc_' . $option;
    }
    return array($doc_url . $file, 0);
}


/**
*   Upgrade the plugin
*   Calls weather_do_upgrade() to perform the upgrade if not already
*   at the current revision.
*
*   @return boolean True on success, error code on failure
*/
function plugin_upgrade_weather()
{
    global $_TABLES, $_CONF_WEATHER;

    $pi_name = $_CONF_WEATHER['pi_name'];

    // Get the current version, compare to the version in the plugin code
    $current_ver = DB_getItem($_TABLES['plugins'], 'pi_version',
        "pi_name = '$pi_name'");
    if (!$current_ver) {
        COM_errorLog("Error getting the $pi_name Plugin version",1);
        return '3';
    }

    // if we're at the current version, what are we doing here?
    if ($current_ver >= $_CONF_WEATHER['pi_version']) {
        COM_errorLog("Current version $current_ver is already up to date");
        return '6';
    }

    // If we're still here, import the upgrade functions.
    // ... when there's a real upgrade (not code only) to be done
    USES_weather_upgrade();
    $error = weather_do_upgrade($current_ver);
    if ($error) {
        COM_errorLog("Error updating the $pi_name Plugin version",1);
        return '4';
    }

    // now update the current version number.
    DB_query("UPDATE {$_TABLES['plugins']} SET
            pi_version = '{$_CONF_WEATHER['pi_version']}',
            pi_gl_version = '{$_CONF_WEATHER['gl_version']}',
            pi_homepage = '{$_CONF_WEATHER['pi_url']}'
        WHERE pi_name = '$pi_name';", 1);
    if (DB_error()) {
        COM_errorLog("Error updating the $pi_name Plugin version", 1);
        return '5';
    }

    // Clear the cache
    DB_query("TRUNCATE {$_TABLES['weather_cache']}", 1);
    if (DB_error()) {
        COM_errorLog("Error clearing the $pi_name Plugin cache", 1);
        return '5';
    }

    COM_errorLog("Succesfully updated the $pi_name Plugin!", 1);
    return true;
}


/**
* This will put an option for the plugin in the command and control block
* on moderation.php
*
* Add the plugin name, icon and link to the command and control block in
* moderation.php
*
* @return   array  Array containing (plugin name, admin url, url of plugin icon)
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
*   Returns the URL of the plugin's icon.
*
*   @return string      URL of the icon
*/
function plugin_geticon_weather()
{
    return WEATHER_URL . '/images/weather.png';
}


/**
*   Adds the plugin to the Admin menu.
*
*   @return array   Array(pi_name, admin url, # of items) or '' for none.
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
*   Support merging local accounts into remote accounts
*
*   @param  integer $origUID    Original (remote) user ID
*   @param  integer $destUID    Merged (local) user ID
*/
function plugin_user_move_weather($origUID, $destUID)
{
    global $_TABLES;

    $origUID = (int)$origUID;
    $destUID = (int)$destUID;

    DB_query("UPDATE {$_TABLES['weather_cache']}
            SET uid = $destUID WHERE uid = $origUID", 1);
}


/**
*   Determine if the current user is a plugin admin
*
*   @see SEC_hasRights()
*   @return boolean     True for an administrator, False if not
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
*   Return the URL for a give icon string.
*   If the provide string is a relative URL, then www.google.com is prepended.
*
*   @param  string  $icon   Icon URL returned from the weather API
*   @return string          Fully-qualified URL to the icon image
*/
function WEATHER_getIcon($icon)
{
    if (function_exists('CUSTOM_weatherIcon')) {
        $icon = CUSTOM_weatherIcon($icon);
    } elseif ($icon[0] == '/') {
        $icon = 'http://www.google.com' . $icon;
    }
    return $icon;
}


/**
*   Get the display for a single weather item, including description
*   and forecast.
*
*   @param  array   $args   array('loc'=>address string)
*   @param  mixed   &$output    HTML for display
*   @return string      Our configured currency code.
*/
function service_embed_weather($args, &$output, &$svc_msg)
{
    // If the requester doesn't specifically disable the linkback,
    // assume that it must be provided.
    if (!isset($args['linkback'])) {
        $args['linkback'] = true;
    }
    $output = WEATHER_embed($args['loc'], $args['linkback']);
    return PLG_RET_OK;
}

?>
