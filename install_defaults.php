<?php
/**
*   Configuration defaults for the Weather plugin.
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2011-2012 Lee Garner <lee@leegarner.com>
*   @package    weather
*   @version    1.0.1
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*               GNU Public License v2 or later
*   @filesource
*/

if (!defined ('GVERSION')) {
    die('This file can not be used on its own!');
}

/**
*   Weather default settings
*
*   Initial Installation Defaults used when loading the online configuration
*   records. These settings are only used during the initial installation
*   and not referenced any more once the plugin is installed
*/

global $_WEA_DEFAULT;
$_WEA_DEFAULT = array(
    'anon_access'       => '1', // 0=none,1=view,2=search
    'displayblocks'     => '3',
    'cache_minutes'     => '120',
    'default_loc'       => 'Los Angeles, CA',
    'blk_show_loc'      => '3', // Personal, fallback to default
    'usermenu_option'   => '1',
    'provider'          => 'wu',
    'k_m'               => 'M',     // Windspeed: M = MPH, K = KPH
    'f_c'               => 'F',     // F = Farenheit, C = Celsius
);


/**
*   Initialize Weather plugin configuration
*   Creates the entries for the configuration if they don't already
*   exist. 
*
*   @return boolean     true: success; false: an error occurred
*/
function plugin_initconfig_weather()
{
    global $_CONF_WEATHER, $_WEA_DEFAULT;

    $pi_name = $_CONF_WEATHER['pi_name'];
    $c = config::get_instance();
    if (!$c->group_exists($pi_name)) {

        $c->add('sg_main', NULL, 'subgroup', 0, 0, NULL, 0, true, $pi_name);
        $c->add('fs_main', NULL, 'fieldset', 0, 0, NULL, 0, true, $pi_name);

        $c->add('anon_access',$_WEA_DEFAULT['anon_access'], 'select',
                0, 0, 2, 10, true, $pi_name);
        $c->add('displayblocks',$_WEA_DEFAULT['displayblocks'], 'select',
                0, 0, 13, 30, true, $pi_name);
        $c->add('cache_minutes',$_WEA_DEFAULT['cache_minutes'], 'text',
                0, 0, NULL, 40, true, $pi_name);
        $c->add('default_loc',$_WEA_DEFAULT['default_loc'], 'text',
                0, 0, NULL, 50, true, $pi_name);
        $c->add('blk_show_loc',$_WEA_DEFAULT['blk_show_loc'], 'select',
                0, 0, 3, 60, true, $pi_name);
        $c->add('usermenu_option',$_WEA_DEFAULT['usermenu_option'], 'select',
                0, 0, 1, 70, true, $pi_name);
        $c->add('provider',$_WEA_DEFAULT['provider'], 'select',
                0, 0, 16, 75, true, $pi_name);
        $c->add('k_m',$_WEA_DEFAULT['k_m'], 'select',
                0, 0, 14, 80, true, $pi_name);
        $c->add('f_c',$_WEA_DEFAULT['f_c'], 'select',
                0, 0, 15, 90, true, $pi_name);

        // Provider - World Weather Online
        $c->add('fs_provider_wwo', NULL, 'fieldset', 0, 10, NULL, 0, true, $pi_name);
        $c->add('api_key_wwo', '', 'text', 0, 10, 0, 100, true, $pi_name);

        // Provider - Weather Underground
        $c->add('fs_provider_wu', NULL, 'fieldset', 0, 20, NULL, 0, true, $pi_name);
        $c->add('api_key_wu', '', 'text', 0, 20, 0, 200, true, $pi_name);
        $c->add('ref_key_wu', '', 'text', 0, 20, 0, 210, true, $pi_name);

        // Provider - axpiu.com
        $c->add('fs_provider_axpiu', NULL, 'fieldset', 0, 10, NULL, 0, true, $pi_name);
        $c->add('api_key_axpiu', '', 'text', 0, 10, 0, 100, true, $pi_name);
    }

    return true;
}
?>
