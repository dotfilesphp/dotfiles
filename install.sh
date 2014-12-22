#!/bin/bash
# install: installation script for dotfiles.
# Author: Anthonius Munthi <me@itstoni.com>

is_command(){
    local command="$1"
    if ! command_loc="$(type -p "$command")" || [ -z "$command_loc" ]; then
        return 1
    else
        return 0
    fi
}

# git
if (is_command "git"); then
    cp ~/dotfiles/git/gitconfig ~/.gitconfig
    cp ~/dotfiles/git/gitignore ~/.gitignore
    echo 'git configured'
fi

# VIM
if (is_command "vim"); then
    cp -r ~/dotfiles/vim ~/.vim
    #vim +BundleInstall +qall
    echo 'Vim runtime configuration and scripts installed'
fi

# zsh
if (is_command "zsh" ); then
    cp ~/dotfiles/zsh/zshrc ~/.zshrc
    echo '.zshrc added'
fi

# bash
if (is_command "bash"); then
    cp ~/dotfiles/bash/bashrc ~/.bashrc
    echo '.bashrc added'
fi

# wget
if (is_command "wget"); then
    cp ~/dotfiles/wget/wgetrc ~/.wgetrc
    echo '.wgetrc added'
fi

# wget
if (is_command "curlrc"); then
    cp ~/dotfiles/wget/curlrc ~/.curlrc
    echo '.wgetrc added'
fi

# screen
if (is_command "screen"); then
    cp ~/dotfiles/screen/screenrc ~/.screenrc
    echo '.screenrc added'
fi

# phpbrew configuration - must be done after zsh or bash configuration
if (is_command "phpbrew"); then
    if (is_command "zsh"); then
        echo "source ~/.phpbrew/bashrc" >> ~/.zshrc
        echo 'phpbrew configured'
    elif (is_command "bash"); then
        echo "source ~/.phpbrew/bashrc" >> ~/.bashrc
        echo 'phpbrew configured'
    fi
fi

# atom
if (is_command "atom"); then
    cp -r ~/dotfiles/atom ~/.atom
    echo "Atom runtime configuration installed"
fi

exit 0
