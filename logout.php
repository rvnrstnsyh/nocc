<?php

/**
 * Logout
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

require_once dirname(__FILE__) . '/classes/NVLL_Session.php';

NVLL_Session::start();

if (isset($_SESSION['send_backup'])) $send_backup = $_SESSION['send_backup'];
if (file_exists(dirname(__FILE__) . '/config/conf.php')) {
    require_once dirname(__FILE__) . '/config/conf.php';
    // code extraction from conf.php, legacy code support
    if ((file_exists(dirname(__FILE__) .  '/functions/config_check.php')) && (!function_exists('get_default_from_address'))) {
        require_once dirname(__FILE__) .  '/functions/config_check.php';
    }
} else {
    print("The main configuration file ('" . dirname(__FILE__) . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "conf.php') couldn't be found!<br />Please copy the '" . dirname(__FILE__) . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "conf.php.dist' file to '" . dirname(__FILE__) . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "conf.php'.");
    die();
}

require_once dirname(__FILE__) .  '/functions/miscellaneous.php';

clear_attachments();

NVLL_Session::remove_session_file();
NVLL_Session::destroy();
NVLL_Session::start();

if (isset($send_backup)) $_SESSION['send_backup'] = $send_backup;

require_once dirname(__FILE__) .  '/functions/proxy.php';
Header('Location: ' . $conf->base_url . 'index.php?' . NVLL_Session::getUrlGetSession());
