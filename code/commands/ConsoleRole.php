<?php

namespace SilverStripe\Sakemore\Commands;

use DataExtension;
use Silverstripe\Sakemore\Helpers\SakeMoreHelper;

/**
 * Provides an interactive PHP interface to the site.
 */
class ConsoleRole extends DataExtension {
	const CMD_CONSOLE = 'console';

	/**
	 * Tell sake more about this command.
	 */
	public function commands(&$list) {
		$list[self::CMD_CONSOLE] = array($this, 'startConsole');
	}

	/**
	 * Gives sake a brief for the help section.
	 */
	public function help_brief(&$details) {
		$details[self::CMD_CONSOLE] = 'Provides a PHP shell with Silverstripe loaded.';
	}

	/**
	 * Gives sake a list of the parameters used.
	 */
	public function help_parameters(&$details) {
		// @todo Add in how much bootstrapping to do.
	}

	/**
	 * Gives sake a list of examples of how to use this command.
	 */
	public function help_examples(&$examples) {
		$examples[self::CMD_CONSOLE] = array(
			self::CMD_CONSOLE,
		);
	}

	/**
	 * Attempts to start a PHP console.
	 */
	public function startConsole() {
		// Prepare variables.
		$sakebase = dirname(dirname(__DIR__));
		$sitebase = dirname($sakebase);

		// Find the PHPSH script.
		$path = $sakebase . '/thirdparty/phpsh/phpsh';

		// Run this helper script to initialise Silverstripe.
		$arg = sprintf('%s/scripts/consolerole-init.php', $sakebase);

		// Run the console.
		$cmd = $path . ' ' . $arg;
		SakeMoreHelper::runCLI($cmd);
	}
}
