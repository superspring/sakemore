<?php
/**
 * This is a trimmed down version of framework/main.php. It's purpose is to initialise the framework
 * without executing a URL.
 */

// Load core.
require_once('core/Core.php');

// Connect to database
require_once('model/DB.php');

// Redirect to the installer if no database is selected
if(!isset($databaseConfig) || !isset($databaseConfig['database']) || !$databaseConfig['database']) {
	// Not installed? Throw an error.
	die("Unable to initialise Silverstripe without a database\n");
}

DB::connect($databaseConfig);
DataModel::set_inst(new DataModel());
