Sake More
=========

A command line tool for interfacing with Silverstripe.

This tool provides a developer or system administrator with Silverstripe functionality from the command-line.
The system can be easily extended but out of the box you get the following functionality:

 * A PHP console with Silverstripe fully loaded - You can even query the database directly with Silverstripe commands - eg var_dump(Page::get_one('Page'));
 * Command line access to the database your site is using, no need to remember the password or other details.
 * The ability to clear your caches. This includes image caches, theme templates, language files, manifest and aggregated CSS/JS.
 * Changing any users password - forgotten your password? Easily change it
 * Database dumps - This can be combined with a cron job to do regular database dumps. Pipe the output of this command into a file or similar.

Example usage
-------------

Get a list of the commands available

    $ sake more help
    Help details for sake:
    
    sql-dump - Dumps the current database to the console.
    Example: sake more sql-dump
    
    clear - Flush's one or more of Silverstripe's caches. Expects type of cache as parameter
    Arguments: Type - A choice of: all, image, template, language, manifest, combined
    Example: sake more clear all - Clears all the caches
             sake more clear image - Finds cached images and deletes them
             sake more clear template - Clears cached templates
             sake more clear language - Finds cached languages from Zend translate and clears them
             sake more clear manifest - Rebuilds the manifest and finds new files
             sake more clear combined - Rebuilds js and css files which have been combined together
    
    console - Provides a PHP shell with Silverstripe loaded.
    Example: sake more console
    
    sql - Provides direct access to the given database.
    Example: sake more sql
    
    pwd - Allows a user to change their password. Expects two parameters: username and password.
    Arguments: Username - which member's password to change
               Password - the new password for this member
    Example: sake more pwd admin new_password
             sake more pwd user@email.com "a new password"

Get direct access to a PHP console - similar to 'php -a' but with tab completion available.

    $ sake more console
    Commandline: php -q [...]/sakemore/thirdparty/phpsh/phpsh.php [...]/sakemore/scripts/consolerole-init.php
    phpsh (c)2006 by Charlie Cheever and Dan Corson and Facebook, Inc.
    type 'h' or 'help' to see instructions & features
    New Feature: You can use the -c option to turn off coloring
    php> [here you can run any PHP command and have full access to all of Silverstripe's functionality]

Get direct access to your SQL CLI

    $ sake more sql
    psql (9.1.9, server 9.1.12)
    SSL connection (cipher: DHE-RSA-AES256-SHA, bits: 256)
    Type "help" for help.
    
    sakemore_dev=>

Installation
------------

 * Install your site with composer - http://doc.silverstripe.org/framework/en/installation/composer
 * Set up your database, etc
 * Run: "sudo apt-get install php5-cli python"
 * Run: "composer require silverstripe/sakemore dev-master"
 * Run: "sake more help"
