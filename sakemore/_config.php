<?php
/**
 * Adds the current working directory to the list.
 */
global $_FILE_TO_URL_MAPPING;
$basepath = dirname(getcwd());
$_FILE_TO_URL_MAPPING[$basepath] = 'http://localhost';

// This is a list of commands for 'sake more'.
Object::add_extension('More', 'UserPasswordRole');
Object::add_extension('More', 'SQLConsoleRole');
Object::add_extension('More', 'ConsoleRole');
