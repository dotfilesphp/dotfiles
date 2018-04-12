Feature: Install


    Scenario: Patch bashrc files
        Given I execute restore command
        Then Dotfile ".dotfiles/bashrc" should contain "export PHPBREW_SET_PROMPT=1"
        And Dotfile ".dotfiles/bashrc" should contain "export PHPBREW_RC_ENABLE=1"
        And Dotfile ".dotfiles/bashrc" should contain 'source "/root/.phpbrew/bashrc"'
