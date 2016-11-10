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

require_once '../lib-common.php';

$Session = SESS_getVar('glWeather');
if (!is_array($Session))
    exit;

$sessid = (int)$_GET['remsess'];
unset($Session[$sessid]);
SESS_setVar('glWeather', $Session);
exit;

?>
