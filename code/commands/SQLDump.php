<?php

namespace SilverStripe\Sakemore\Commands;

use DataExtension;
use Silverstripe\Sakemore\Helpers\SakeMoreHelper;

/**
 * Provides shell access to the database used by the site.
 */
class SQLDump extends DataExtension {
	const CMD_SQLDUMP = 'sql-dump';

	/**
	 * Tell sake more about this command.
	 */
	public function commands(&$list) {
		$list[self::CMD_SQLDUMP] = array($this, 'startDump');
	}

	/**
	 * Gives sake a brief for the help section.
	 */
	public function help_brief(&$details) {
		$details[self::CMD_SQLDUMP] = 'Dumps the current database to the console.';
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
		$examples[self::CMD_SQLDUMP] = array(
			self::CMD_SQLDUMP,
		);
	}

	/**
	 * Attempts to dump the database to the console.
	 */
	public function startDump() {
		// Get the details for the current database.
		$cmd = $this->getDatabaseCommand();
		// Run the command.
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
				$command[] = 'mysqldump';
				foreach (array(
					'server'   => 'host',
					'port'     => 'port',
					'username' => 'user',
					'password' => 'password',
				) as $config_key => $command_key) {
					if (array_key_exists($config_key, $databaseConfig)) {
						$command[] = sprintf('--%s=%s', $command_key, escapeshellarg($databaseConfig[$config_key]));
					}
				}
				$command[] = escapeshellarg($databaseConfig['database']);
				break;

			case 'PostgreSQLDatabase':
				$command[] = sprintf('PGPASSWORD=%s pg_dump', escapeshellarg($databaseConfig['password']));
				foreach (array(
					'server'   => 'host',
					'port'     => 'port',
					'username' => 'username',
				) as $config_key => $command_key) {
					if (array_key_exists('server', $databaseConfig)) {
						$command[] = sprintf('--%s=%s', $command_key, escapeshellarg($databaseConfig[$config_key]));
					}
				}
				$command[] = escapeshellarg($databaseConfig['database']);
				break;

			default:
				// @todo Add in other database types.
		}

		return implode(' ', $command);
	}
}
