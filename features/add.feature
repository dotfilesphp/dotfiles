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
        And Dotfile ".bashrc" should contain "Patch defaults"
        And Dotfile ".bashrc" should contain "this is bash rc content"
        And Dotfile ".bashrc" should contain "> dotfiles-patch"
        And Dotfile ".bashrc" should contain "< dotfiles-patch"
