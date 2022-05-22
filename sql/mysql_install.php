<?php
/**
 * Database installation for the Weather plugin.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2011-2022 Lee Garner <lee@leegarner.com>
 * @package     weather
 * @version     v2.0.3
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
global $_TABLES;

$_SQL_UPGRADE = array(
    '1.1.2' => array(
        "ALTER TABLE {$_TABLES['weather_cache']}
            CHANGE location location varchar(100) NOT NULL",
    ),
    '2.0.3' => array(
        "DROP TABLE IF EXISTS {$_TABLES['weather_cache']}",
    ),
);

