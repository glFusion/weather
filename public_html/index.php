<?PHP
/**
*   Entry point for the Weather plugin.
*   Allows the user to view several weather forcasts.  Forecasts are cached
*   to minimize calls to the weather API
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2011-2012 Lee Garner <lee@leegarner.com>
*   @package    weather
*   @version    0.1.3
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*               GNU Public License v2 or later
*   @filesource
*/

// Import glFusion core
require_once '../lib-common.php';

if (!in_array('weather', $_PLUGINS)) {
    COM_404();
}

$isAnon = COM_isAnonUser() ? true : false;
if (SEC_hasRights('weather.view')) {
    $access = WEATHER_ACCESS_VIEW | WEATHER_ACCESS_SEARCH;
} elseif ($isAnon) {
    $access = $_CONF_WEATHER['anon_access'];
}
if ($access < WEATHER_ACCESS_VIEW) {
    COM_404();
}

$loc = '';

// Show the search form if the user has access to it.
if ($access & WEATHER_ACCESS_SEARCH) {
    $T = new Template(WEATHER_PI_PATH . '/templates');
    $T->set_file('form', 'search.thtml');
    $T->parse('output', 'form');
    $content .= $T->finish($T->get_var('output'));

    // If the user is allowed to search, and supplied a location, then use it.
    // Otherwise, get the user's profile location (non-anonymous only).
    if (isset($_REQUEST['loc']) && !empty($_REQUEST['loc'])) {
        // Get the requested location if submitted
        $loc = WEATHER_stripslashes($_REQUEST['loc']);
    }
}

// If a specific location isn't requested, figure out a default
if (empty($loc)) {
    if ($isAnon) {
        if ($_CONF_WEATHER['blk_show_loc'] & WEATHER_BLK_DEFAULT) {
            $loc = $_CONF_WEATHER['default_loc'];
        }
    } else {
        // Show the personal block, if configured & available
        if ($_CONF_WEATHER['blk_show_loc'] & WEATHER_BLK_PERSONAL) {
            $loc = strtolower(
                    DB_getItem($_TABLES['userinfo'], 'location',
                    "uid='{$_USER['uid']}'"));
        }
        if (empty($loc) && 
                ($_CONF_WEATHER['blk_show_loc'] & WEATHER_BLK_DEFAULT)) {
            // Fallback to default, if allowed
            $loc = strtolower($_CONF_WEATHER['default_loc']);
        }
    }
}

$Session = SESS_getVar('glWeather');
if ($Session === 0) $Session = array();

if (!empty($loc)) {
    $key = array_search($loc, $Session);
    if ($key !== false) {
        unset($Session[$key]);
    }
    $Session[] = $loc;
}
SESS_setVar('glWeather', $Session);

$T = new Template(WEATHER_PI_PATH . '/templates');
$T->set_file('index', 'index.thtml');
$T->set_block('index', 'EmbedBlock', 'eBlk');
$msg = '';
foreach ($Session as $key=>$loc) {
    $weather = WEATHER_getWeather($loc);
    if (!is_array($weather)) {
        unset($Session[$key]);    // how'd bad weather get here?
        $msg = $weather;
        continue;
    }
    $embed = WEATHER_embed($weather, false);
    $T->set_var(array(
        'embed' => $embed,
        'divid' => $key,
        'linkback' => Weather::linkback(),
    ) );
    $T->parse('eBlk', 'EmbedBlock', true);
}
$T->parse('output', 'index');
$content .= $T->finish($T->get_var('output'));

$display .= WEATHER_siteHeader();
if (!empty($msg)) {
    $display .= COM_showMessage($msg, $_CONF_WEATHER['pi_name']);
}
$display .= $content;
$display .= WEATHER_siteFooter();
echo $display;
exit;


/**
*   Strips slashes if magic_quotes_gpc is on.
*
*   @param  mixed   $var    Value or array of values to strip.
*   @return mixed           Stripped value or array of values.
*/
function WEATHER_stripslashes($var)
{
	if (GVERSION < '1.3.0' && get_magic_quotes_gpc()) {
		if (is_array($var)) {
			return array_map('WEATHER_stripslashes', $var);
		} else {
			return stripslashes($var);
		}
	} else {
		return $var;
	}
}

?>
