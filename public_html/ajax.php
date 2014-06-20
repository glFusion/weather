<?php
/**
*   Common user-facing AJAX functions
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2011 Lee Garner <lee@leegarner.com>
*   @package    weather
*   @version    0.1.0
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*               GNU Public License v2 or later
*   @filesource
*/

if (!isset($_SESSION)) session_start();

if (!isset($_SESSION['glWeather']) || !is_array($_SESSION['glWeather']))
    exit;

$sessid = (int)$_GET['remsess'];
unset($_SESSION['glWeather'][$sessid]);
exit;

?>
