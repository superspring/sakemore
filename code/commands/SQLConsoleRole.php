<?php

namespace SilverStripe\Sakemore\Commands;

use DataExtension;
use Silverstripe\Sakemore\Helpers\SakeMoreHelper;

/**
 * Provides shell access to the database used by the site.
 */
class SQLConsoleRole extends DataExtension {
	const CMD_SQLCONSOLE = 'sql';

	/**
	 * Tell sake more about this command.
	 */
	public function commands(&$list) {
		$list[self::CMD_SQLCONSOLE] = array($this, 'startConsole');
	}

	/**
	 * Gives sake a brief for the help section.
	 */
	public function help_brief(&$details) {
		$details[self::CMD_SQLCONSOLE] = 'Provides direct access to the given database.';
	}

	/**
	 * Gives sake a list of the parameters used.
	 */
	public function help_parameters(&$details) {
		// @todo Add in which environment to use.
	}

	/**
	 * Gives sake a list of examples of how to use this command.
	 */
	public function help_examples(&$examples) {
		$examples[self::CMD_SQLCONSOLE] = array(
			self::CMD_SQLCONSOLE,
		);
	}

	/**
	 * Attempts to start a SQL console.
	 */
	public function startConsole() {
		// Get the details for the current database.
		$cmd = $this->getDatabaseCommand();
		// Run the console.
		SakeMoreHelper::runCLI($cmd);
	}

	/**
	 * Gets the current database details for the current environment.
	 */
	protected function getDatabaseCommand() {
		// Prepare variables.
		global $databaseConfig;
		$command = array();

		// Provide the command required to run the interface.
		switch ($databaseConfig['type']) {
			case 'MySQLDatabase':
				$command[] = 'mysql';
				foreach (array(
					'database' => 'database',
					'server'   => 'host',
					'port'     => 'port',
					'username' => 'user',
					'password' => 'password',
				) as $config_key => $command_key) {
					if (array_key_exists($config_key, $databaseConfig)) {
						$command[] = sprintf('--%s=%s', $command_key, escapeshellarg($databaseConfig[$config_key]));
					}
				}
				break;

			case 'PostgreSQLDatabase':
				$command[] = sprintf('PGPASSWORD=%s psql', escapeshellarg($databaseConfig['password']));
				foreach (array(
					'database' => 'dbname',
					'server'   => 'host',
					'port'     => 'port',
					'username' => 'username',
				) as $config_key => $command_key) {
					if (array_key_exists('server', $databaseConfig)) {
						$command[] = sprintf('--%s=%s', $command_key, escapeshellarg($databaseConfig[$config_key]));
					}
				}
				break;

			default:
				// @todo Add in other database types.
		}

		return implode(' ', $command);
	}
}
