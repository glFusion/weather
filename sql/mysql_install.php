<?php
/**
*   Database installation for the Weather plugin.
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2011 Lee Garner <lee@leegarner.com>
*   @package    weather
*   @version    0.1.0
*   @license    http://opensource.org/licenses/gpl-2.0.php
*               GNU Public License v2 or later
*   @filesource
*/
global $_TABLES;
$_SQL = array(
    // Create the cache table
    'weather_cache' => "CREATE TABLE {$_TABLES['weather_cache']} (
        `location` varchar(100) NOT NULL,
        `uid` int(11) NOT NULL DEFAULT '0',
        `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `data` text,
        PRIMARY KEY (`location`)
        ) ENGINE=MyISAM",
);

$_SQL_UPGRADE = array(
    '1.1.2' => array(
        "ALTER TABLE {$_TABLES['weather_cache']}
            CHANGE location location varchar(100) NOT NULL",
    ),
// Drop table when glFusion 1.8.0+ is targeted
/*    '1.1.0' => array(
        "DROP TABLE IF EXISTS {$_TABLES['weather_cache']}",
    ),
*/
);

?>
