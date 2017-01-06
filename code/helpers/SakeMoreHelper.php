<?php
/**
 * This class contains helper methods designed for sake scripts.
 */
class SakeMoreHelper
{
    /**
     * Runs the given datbase command.
     *
     * @param string $command
     *   The full bash command to run, escaped as appropriate.
     *
     * @return int
     *   Exit code.
     */
    public static function runCLI($command)
    {
        // Prepare variables.
        $pipes = array();

        // Open the process.
        $process = proc_open($command, array(
            0 => STDIN,
            1 => STDOUT,
            2 => STDERR,
        ), $pipes);

        // Get details on it.
        $status = proc_get_status($process);
        $exit_code = proc_close($process);

        // Return it's status.
        return $status['running'] ? $exit_code : $status['exitcode'];
    }
}
