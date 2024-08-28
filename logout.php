<?php

/**
 * Logout
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

require_once './classes/NVLL_Session.php';

NVLL_Session::start();

if (isset($_SESSION['send_backup'])) {
    $send_backup = $_SESSION['send_backup'];
}

if (file_exists('./config/conf.php')) {
    require_once './config/conf.php';

    // code extraction from conf.php, legacy code support
    if ((file_exists('./utils/config_check.php')) && (!function_exists('get_default_from_address'))) {
        require_once './utils/config_check.php';
    }
} else {
    print("The main configuration file (./config/conf.php) couldn't be found! <p />Please rename the file './config/conf.php.dist' to './config/conf.php'. ");
    die();
}

require_once './utils/functions.php';
clear_attachments();
NVLL_Session::remove_session_file();
NVLL_Session::destroy();
NVLL_Session::start();
if (isset($send_backup)) {
    $_SESSION['send_backup'] = $send_backup;
}
require_once './utils/proxy.php';
Header('Location: ' . $conf->base_url . 'index.php?' . NVLL_Session::getUrlGetSession());
