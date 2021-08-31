<?php
/**
 * Table definitions and other static config variables.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2009-2021 Lee Garner <lee@leegarner.com>
 * @package     weather
 * @version     v2.0.1
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */

/**
 * Global array of table names from glFusion.
 * @global array $_TABLES
 */
global $_TABLES;

/**
 * Global table name prefix.
 * @global string $_DB_table_prefix
 */
global $_DB_table_prefix;

$_table_prefix = $_DB_table_prefix . 'weather';

// Deprecated cache table, delete when only glFusion cache is supported
$_TABLES['weather_cache']       = $_table_prefix . '_cache';

$_CONF_WEATHER['pi_name'] = 'weather';
$_CONF_WEATHER['pi_version'] = '2.0.2';
$_CONF_WEATHER['gl_version'] = '1.7.8';
$_CONF_WEATHER['pi_url'] = 'http://www.leegarner.com';
$_CONF_WEATHER['pi_display_name'] = 'Weather';
