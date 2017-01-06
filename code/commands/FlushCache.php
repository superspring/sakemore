<?php
/**
 * This class gives the ability to flush any of Silverstripe's major caches.
 *
 * To extend functionality in this class
 */
class FlushCacheRole extends DataExtension
{
    const CMD_FLUSHCACHE = 'clear';

    /**
     * Tell sake more about this command.
     */
    public function commands(&$list)
    {
        $list[self::CMD_FLUSHCACHE] = array($this, 'flushCache');
    }

    /**
     * Gives sake a brief for the help section.
     */
    public function help_brief(&$details)
    {
        $details[self::CMD_FLUSHCACHE] = "Flush's one or more of Silverstripe's caches. Expects type of cache as parameter";
    }

    /**
     * Gives sake a list of the parameters used.
     */
    public function help_parameters(&$details)
    {
        // Get the list of commands.
        $commands = $this->availableCommands();

        $details[self::CMD_FLUSHCACHE] = array(
            'Type - A choice of: all, ' . implode(', ', array_keys($commands)),
        );
    }

    /**
     * Gives sake a list of examples of how to use this command.
     */
    public function help_examples(&$examples)
    {
        // Add the generic 'all' command.
        $examples[self::CMD_FLUSHCACHE] = array(
            self::CMD_FLUSHCACHE . ' all - Clears all the caches',
        );

        // Add the rest of the commands.
        $commands = $this->availableCommands();
        foreach ($commands as $command => $specifics) {
            $examples[self::CMD_FLUSHCACHE][] = sprintf(
                '%s %s - %s',
                self::CMD_FLUSHCACHE, $command, $specifics['description']
            );
        }
    }

    /**
     * Gets a list of the flush commands avilable.
     *
     * This can be extended with 'flushCache_commands' which alters the command set.
     * The data is structed: array(
     *   '[name of cache]' => array(
     *     'description' => '[a one-line description of the parameter]',
     *     'reference'   => array([object], '[method name]'),
     *   ),
     * );
     */
    protected function availableCommands()
    {
        // Prepares the list of build-in cache flush commands.
        $existing = array(
            'image' => array(
                'description' => 'Finds cached images and deletes them',
                'reference'   => array($this, 'flushImage'),
            ),
            'template' => array(
                'description' => 'Clears cached templates',
                'reference'   => array($this, 'flushTemplate'),
            ),
            'language' => array(
                'description' => 'Finds cached languages from Zend translate and clears them',
                'reference'   => array($this, 'flushLanguage'),
            ),
            'manifest' => array(
                'description' => 'Rebuilds the manifest and finds new files',
                'reference'   => array($this, 'flushManifest'),
            ),
            'combined' => array(
                'description' => 'Rebuilds js and css files which have been combined together',
                'reference'   => array($this, 'flushCombined'),
            ),
        );

        // Allow other modules to extend this.
        $obj = new FlushCache();
        $obj->extendExtension($existing);

        // Done.
        return $existing;
    }

    /**
     * Flushes internal caches.
     *
     * @param string $username
     *   The username to find.
     * @param string $password
     *   The new password, plain text.
     */
    public function flushCache($type = null)
    {
        // Prepare variables.
        $commands = $this->availableCommands();

        // Generically add flush=all to help the scripts.
        $_GET['flush'] = 'all';

        // Validate the input.
        if (!$type) {
            return 'Choose a type of cache to clear. See "sake more help" for more details';
        } elseif ($type == 'all') {
            // Run all the cache hooks.
            foreach ($commands as $type => $specifics) {
                // These are printed here so it is displayed in real-time.
                $response = $this->flushCache($type);
                printf("%s", $response);
            }
            return null;
        } elseif (!array_key_exists($type, $commands)) {
            return sprintf('Unexpected parameter "%s". See "sake more help" for more details', $type);
        } else {
            // Prepare to run the fucntion.
            $object = null;
            $reference = $commands[$type]['reference'];
            list($class, $function) = $reference;
            if (is_object($class)) {
                // Passed an object instead of a static class? Ok.
                $object = $class;
                $class = get_class($object);
            }

            // Execute the flush command.
            $method = new ReflectionMethod($class, $function);
            $response = $method->invokeArgs($object, array());

            // Use whatever message is returned.
            if ($response) {
                $response .= "\n";
            }
            return sprintf("Clearing %s cache\n%s\n", $type, $response);
        }
    }

    /**
     * Finds and deletes the _resampled directory contents for cached images.
     */
    public function flushImage()
    {
        // Get a list of all the folders.
        $folders = array();

        // Since we're running inside the framework directory.
        $prefix = '..';
        $dirs = $this->getDirList($prefix);

        // Find the direcotires with '_resampled' in their name.
        $cachedimages = array();
        foreach ($dirs as $dir) {
            if (preg_match(sprintf(
                '@%s[^%s]*_resampled%s@',
                DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR
            ), $dir . DIRECTORY_SEPARATOR)) {
                // Examine these directories for files which match the image format.
                $files = $this->getFileList($dir);
                foreach ($files as $file) {
                    $filename = basename($file);
                    if (preg_match('/\-/', $filename)) {
                        // Temporarily only output the command that would otherwise delete the files.
                        printf("rm %s\n", $file);
                    }
                }
            }
        }
    }

    /**
     * Recursively gets a list of directories.
     */
    protected function getDirList($dir)
    {
        // Go through each entry in the directory.
        for ($dirs = array(), $handle = opendir($dir); ($file = readdir($handle)) !== false;) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;

            // Skip certain entries.
            if ($file == '.' || $file == '..') {
                continue;
            }

            // If it's a directory, add it to the list.
            elseif (is_dir($path)) {
                // Add it to the list.
                $dirs[] = $path;

                // Add all sub folders too.
                foreach ($this->getDirList($path) as $subdir) {
                    $dirs[] = $subdir;
                }
            }
        }

        // Clean up.
        closedir($handle);
        return $dirs;
    }

    /**
     * Non-recursively gets a list of files from a given directory.
     */
    protected function getFileList($dir)
    {
        // Go through each entry in the directory.
        for ($files = array(), $handle = opendir($dir); ($file = readdir($handle)) !== false;) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;

            // Skip certain entries.
            if ($file == '.' || $file == '..') {
                continue;
            }

            // If it's a file, add it to the list.
            elseif (is_file($path)) {
                $files[] = $path;
            }
        }

        // Clean up.
        closedir($handle);
        return $files;
    }

    /**
     * Clears cached template files.
     */
    public function flushTemplate()
    {
        // Rebuild the template manifest.
        $template = new SS_TemplateManifest(BASE_PATH, false, true);

        // Clear the template cache.
        SSViewer::flush_template_cache();
    }

    /**
     * Clears the cache for multiple languages.
     *
     * If using the Zend translation module for multiple languages.
     */
    public function flushLanguage()
    {
        // Can only be done if Zend_Translate is loaded.
        if (class_exists('Zend_Translate')) {
            $cache = Zend_Translate::getCache();
            // ...and there is a cache to clear.
            if ($cache) {
                $cache->clean(Zend_Cache::CLEANING_MODE_ALL);
                return 'Successfully cleared';
            }
        }
        return 'No cached data found';
    }

    /**
     * Rebuilds the config and class manifests.
     */
    public function flushManifest()
    {
        // Rebuild the config manifest
        $configManifest = new SS_ConfigManifest(BASE_PATH, false, true);

        // Rebuild the class manifest.
        $manifest = new SS_ClassManifest(BASE_PATH, false, true);
    }

    /**
     * Rebuilds the cache for combined CSS and JS files.
     */
    public function flushCombined()
    {
        // Split any the combined files.
        Requirements::process_combined_files();
    }
}

/**
 * This classes sole purpose is to allow extensions.
 *
 * Example usage:
 *   In _config.php put: Object::add_extension('FlushCache', 'MyNewModule');
 *   In MyNewModule.php put: function flushCache_commands(&$commands) { ... }
 *   Commands must follow the format specified in FlushCacheRole::availableCommands.
 */
class FlushCache extends Object
{
    public function extendExtension(&$commands)
    {
        $this->extend('flushCache_commands', $commands);
    }
}
