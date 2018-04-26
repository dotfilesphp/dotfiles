Feature: Add dotfiles to backup list
    In order to backup dotfiles
    As user
    I should able to add dot files to backup

    Scenario: Add files to backup
        Given I have dotfile ".bashrc"
        And I have dotfile ".foo"
        And I execute add command with "bashrc" argument
        Then I should see "copy from"
        And I should see ".bashrc"

        When I execute add command with "foo"
        Then I should see "copy from"
        And I should see ".foo"

    Scenario: Add files with contents
        Given I have dotfile ".bashrc" with:
        """
        #
        # this is bash rc content
        #
        """
        And I have backup defaults patch "bashrc" with:
        """
        #
        # Patch defaults
        #
        """
        And I execute add command with "bashrc"
        When I execute restore command
        Then I should see "+patch .bashrc"
        And Dotfile ".bashrc" should contain "> dotfiles-patch"
        And Dotfile ".bashrc" should contain "< dotfiles-patch"

    Scenario: Add files for machine only
        Given I have dotfile ".bashrc" with:
        """
        source /etc/skel/bashrc
        """
        When I execute "add -m bashrc"
        Then I should see "copy"
        And I should see "docker/home/bashrc"

    Scenario: Multiple bashrc files patch
        Given I have dotfile ".bashrc" with:
        """
        #
        # Default bashrc
        #
        """
        And  I have backup defaults patch "bashrc" with:
        """
        # Patch Defaults
        """
        And I have backup machine patch "bashrc" with:
        """
        # Patch Machine
        """
        When I execute restore command
        Then I should see "+patch .bashrc"
        And Dotfile ".bashrc" should contain "Default bashrc"
        And Dotfile ".bashrc" should not contain "Patch Machine"
        And Dotfile ".bashrc" should not contain "Patch Default"
        And Dotfile ".bashrc" should contain 'source "/root/.dotfiles/bashrc"'
        And Dotfile ".dotfiles/bashrc" should contain "Patch Defaults"
        And Dotfile ".dotfiles/bashrc" should contain "Patch Machine"
        And Dotfile ".dotfiles/bashrc" should contain ".dotfiles/bin"
