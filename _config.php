<?php
/**
 * Adds the current working directory to the list.
 */
global $_FILE_TO_URL_MAPPING;
$basepath = dirname(getcwd());
$_FILE_TO_URL_MAPPING[$basepath] = 'http://localhost';
if (!array_key_exists('HTTP_HOST', $_SERVER)) {
  $_SERVER['HTTP_HOST'] = 'localhost';
}
