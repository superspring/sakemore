<?php
/**
 * This class defines the base 'more' URL used with sake.
 */
class More extends Controller {
	/**
	 * All sake more commands are prefixed with the 'more' URL.
	 */
	public $url_base = 'more';

	/**
	 * Prepare to run a sake more command.
	 */
	public function init() {
		// Ensure it's being run via the command line.
		if (!$this->isCli()) {
			// If it's not, redirect to the homepage.
			Director::redirect('/');
			parent::init();
		}

		// What're the command and arguments?
		if (!array_key_exists('args', $_GET)) {
			$cmdargs = array();
		}
		else {
			$cmdargs = $_GET['args'];
		}

		// Validate them.
		if (empty($cmdargs)) {
			return $this->showError('Expected parameter. Type "sake more help" for a list of commands');
		}
		// Get all the potential commands.
		$allcommands = $this->availableCommands();
		// Which command is being called?
		$cmd = array_shift($cmdargs);
		if (!array_key_exists($cmd, $allcommands)) {
			return $this->showError("Unable to find command '$cmd'. Run 'sake more help' for list of commands");
		}

		// Run the given command.
		if (count($allcommands[$cmd]) == 2) {
			list($class, $function) = $allcommands[$cmd];
			$argarray = false;
		}
		else {
			list($class, $function, $argarray) = $allcommands[$cmd];
		}

		// Passing the arguments as one array or individual arguments?
		if ($argarray) {
			// One single array? Useful for when using unlimited arguments.
			$cmdargs = array($cmdargs);
		}

		$object = null;
		if (is_object($class)) {
			// Passed an object instead of a static class? Ok.
			$object = $class;
			$class = get_class($object);
		}
		$method = new ReflectionMethod($class, $function);
		$response = $method->invokeArgs($object, $cmdargs);

		// Print the response.
		printf("%s\n", $response);
		die();
	}

	/**
	 * Determines if this script is being run via the command line.
	 */
	protected function isCli() {
		if (!defined('PHP_SAPI')) {
			return false;
		}
		return PHP_SAPI === 'cli';
	}

	/**
	 * Gets a list of available commands.
	 */
	protected function availableCommands() {
		/*
		 * key - This is unique to each command.
		 *     - The command - 'sake more [command] [args]'
		 * value is an array
		 *   1. $this - The object to call or null for a static call.
		 *   2. method name - The function to call on that object/class.
		 *   3. (Optional) - settings - array.
		 *   - 'args' => 'many' (default) or 'single' - Are all arguments passed as a single array or individual function arguments?
		 */
		$list = array(
			'help' => array($this, 'getHelp'),
		);
		$this->extend('commands', $list);
		return $list;
	}

	/**
	 * Displays errors.
	 */
	protected function showError($msg) {
		// @todo Add arguments and translation.
		printf("%s\n", $msg);
		die();
	}

	/**
	 * Display available help information.
	 */
	public function getHelp() {
		// Prepare variables.
		$briefs = array();
		$parameters = array();
		$examples = array();

		// Get help details.
		$this->extend('help_brief', $briefs);
		$this->extend('help_parameters', $parameters);
		$this->extend('help_examples', $examples);

		$commands = array_unique(array_merge(
			array_keys($briefs), array_keys($parameters), array_keys($examples)
		));

		// Display the data.
		echo "Help details for sake:\n";
		foreach ($commands as $command) {
			echo "\n";
			if (array_key_exists($command, $briefs)) {
				printf("%s - %s\n", $command, $briefs[$command]);
			}
			if (array_key_exists($command, $parameters)) {
				$pre = 'Arguments:';
				foreach ($parameters[$command] as $parameter) {
					printf("%s %s\n", $pre, $parameter);
					$pre = '          ';
				}
			}
			if (array_key_exists($command, $examples)) {
				$pre = 'Example:';
				foreach ($examples[$command] as $example) {
					printf("%s sake more %s\n", $pre, $example);
					$pre = '        ';
				}
			}
		}
	}
}
