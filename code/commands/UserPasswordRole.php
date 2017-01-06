<?php
/**
 * This class gives the ability to change a user's password.
 */
class UserPasswordRole extends DataExtension
{
    const CMD_CHANGEPWD = 'pwd';

    /**
     * Tell sake more about this command.
     */
    public function commands(&$list)
    {
        $list[self::CMD_CHANGEPWD] = array($this, 'changePassword');
    }

    /**
     * Gives sake a brief for the help section.
     */
    public function help_brief(&$details)
    {
        $details[self::CMD_CHANGEPWD] = 'Allows a user to change their password. Expects two parameters: username and password.';
    }

    /**
     * Gives sake a list of the parameters used.
     */
    public function help_parameters(&$details)
    {
        $details[self::CMD_CHANGEPWD] = array(
            "Username - which member's password to change",
            'Password - the new password for this member',
        );
    }

    /**
     * Gives sake a list of examples of how to use this command.
     */
    public function help_examples(&$examples)
    {
        $examples[self::CMD_CHANGEPWD] = array(
            self::CMD_CHANGEPWD . ' admin new_password',
            self::CMD_CHANGEPWD . ' user@email.com "a new password"',
        );
    }

    /**
     * Change the password.
     *
     * @param string $username
     *   The username to find.
     * @param string $password
     *   The new password, plain text.
     */
    public function changePassword($username = null, $password = null)
    {
        // Validate the input.
        if (!$username || !$password) {
            return 'Unable to change password. Invalid username or password';
        }

        // Find the user.
        $member = Member::get_one('Member', sprintf(
            '"%s" = \'%s\'', Member::get_unique_identifier_field(), Convert::raw2sql($username)
        ));
        if (!$member) {
            return "Unable to find user '$username'.";
        }

        // Modify the user.
        $member->Password = $password;
        $member->write();
    }
}
