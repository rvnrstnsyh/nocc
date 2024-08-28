# RETRMail

[![Made with PHP](https://img.shields.io/badge/php-v7.4.30-blue)](https://www.php.net/releases/7_4_30.php)

This is a modified version of an existing program, and GitHub has done an excellent job explaining its source.

## Installation

    1)  Download retrmail-x.y.tar.gz (where x and y are version numbers)
        (or retrmail-x.y.zip for Windows' users).
        https://github.com/rvnrstnsyh/retrmail

    2)  untar/unzip retrmail into the directory you want.

    3)  Change to the 'retrmail/config' directory, copy the 'conf.php.dist' file
        to a new 'conf.php' file, set your settings in the 'conf.php' file and
        leave the dist file intact.

        conf.php.dist contains a large number of default values that you'll
        need to configure for your system, such as the default IMAP/POP3
        server, whether or not users can pick a different server, etc...
        The file is fairly well documented, so you should be able to pick out
        what you need to change fairly easily. Be sure to check the $prefs_dir
        and $tmpdir is writable by the user the webserver is running as.

    4)  (Optional step)
        if you run with suEXEC, you'll need to run './addcgipath path' to add
        the parser line at the beginning of PHP files.

    5)  Delete ./addcgipath.sh. For security reasons, it might be run by
        external users although "sh" files are never interpreted by Web
        servers with default configuration.

**_© 2024 NVLL_**
