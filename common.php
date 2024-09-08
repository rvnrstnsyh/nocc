<?php

/**
 * Stuff that is always checked or run or initialised for every hit
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

define('NVLL_DEBUG_LEVEL', 0);
if (NVLL_DEBUG_LEVEL > 0) define('NVLL_START_TIME', microtime(true));

if (version_compare(phpversion(), '7.4.30', '<')) {
    if (!defined('ENT_SUBSTITUTE')) define('ENT_SUBSTITUTE', 8);
}

// Define variables
if (!isset($from_rss)) $from_rss = false;
if (file_exists('./config/conf.php')) {
    require_once './config/conf.php';
    // code extraction from conf.php, legacy code support
    if ((file_exists('./utils/config_check.php')) && (!function_exists('get_default_from_address'))) require_once './utils/config_check.php';
} else {
    //TODO: Make error msg translateble and show nicer error...
    print("The main configuration file (./config/conf.php) couldn't be found! <p />Please rename the file './config/conf.php.dist' to './config/conf.php'. ");
    die();
}

require_once './classes/NVLL_Body.php';
require_once './classes/NVLL_Themes.php';
require_once './classes/NVLL_Domain.php';
require_once './classes/NVLL_Request.php';
require_once './classes/NVLL_Session.php';
require_once './classes/NVLL_Security.php';
require_once './classes/NVLL_Languages.php';
require_once './classes/NVLL_UserPrefs.php';
require_once './classes/NVLL_UserFilters.php';
require_once './classes/NVLL_AttachedFile.php';

require_once './utils/functions.php';
require_once './utils/crypt.php';
require_once './utils/translation.php';

$conf->nvll_name = 'Non-Violable Liberty Layers (NVLL)';
$conf->nvll_version = '1.9.15-dev';
$conf->nvll_url = 'https://nvll.me';

$pwd_to_encrypt = false;
if (isset($_REQUEST['service']) && $_REQUEST['service'] == 'login') $pwd_to_encrypt = true;

$persistent = 0;
if (isset($_REQUEST['remember']) && $_REQUEST['remember'] == true) $persistent = 1;

$session_has_expired = 0;
if ($from_rss == false) $session_has_expired = NVLL_Session::start($persistent);

// Set defaults
if (isset($_REQUEST['folder'])) $_SESSION['nvll_folder'] = $_REQUEST['folder'];
if (!isset($_SESSION['nvll_folder'])) $_SESSION['nvll_folder'] = $conf->default_inbox_folder;
if (isset($_POST['folder']) || ! isset($_SESSION['goto_folder'])) $_SESSION['goto_folder'] = $_SESSION['nvll_folder'];
// Have we changed sort order?
if (!isset($_SESSION['nvll_sort'])) $_SESSION['nvll_sort'] = $conf->default_sort;
if (!isset($_SESSION['nvll_sortdir'])) $_SESSION['nvll_sortdir'] = $conf->default_sortdir;
// Override session variables from request, if supplied
if (isset($_REQUEST['user']) && !isset($_SESSION['nvll_loggedin'])) {
    unset($_SESSION['nvll_login']);
    $_SESSION['nvll_user'] = NVLL_Request::getStringValue('user');

    if (!isset($conf->utf8_decode) || $conf->utf8_decode) {
        if (mb_detect_encoding($_SESSION['nvll_user'], 'UTF-8', true) == "UTF-8") {
            //deprecated in php8.2
            //$_SESSION['nvll_user'] = utf8_decode($_SESSION['nvll_user']);
            $_SESSION['nvll_user'] = iconv('UTF-8', 'ISO-8859-1', $_SESSION['nvll_user']);
        }
    }
}

if (isset($_REQUEST['passwd'])) {
    $_SESSION['nvll_passwd'] = NVLL_Request::getStringValue('passwd');

    if (!isset($conf->utf8_decode) || $conf->utf8_decode) {
        if (mb_detect_encoding($_SESSION['nvll_passwd'], 'UTF-8', true) == "UTF-8") {
            //deprecated in php8.2
            // $_SESSION['nvll_passwd'] = utf8_decode($_SESSION['nvll_passwd']);
            $_SESSION['nvll_passwd'] = iconv('UTF-8', 'ISO-8859-1', $_SESSION['nvll_passwd']);
        }
    }
    $pwd_to_encrypt = true;
}
// Encrypt session password and store into session encrypted password
if ($pwd_to_encrypt == true) $_SESSION['nvll_passwd'] = encpass($_SESSION['nvll_passwd'], $conf->master_key);

if (isset($_REQUEST['sort'])) $_SESSION['nvll_sort'] = NVLL_Request::getStringValue('sort');
if (isset($_REQUEST['sortdir'])) $_SESSION['nvll_sortdir'] = NVLL_Request::getStringValue('sortdir');

//--------------------------------------------------------------------------------
// Set and load the language...
//--------------------------------------------------------------------------------
$languages = new NVLL_Languages('./languages/', $conf->default_lang);

//TODO: Check $_REQUEST['lang'] also when force_default_lang?
if (isset($_REQUEST['lang'])) { //if a language is requested...
    //if the language exists...
    if ($languages->setSelectedLangId($_REQUEST['lang']) || $_REQUEST['lang'] == "default") $_SESSION['nvll_lang'] = $languages->getSelectedLangId();
}
if (isset($_SESSION['nvll_lang']) && $_SESSION['nvll_lang'] != "default") { //if session language already set...
    $languages->setSelectedLangId($_SESSION['nvll_lang']);
} else { //if session language NOT already set...
    if (!isset($conf->force_default_lang) || !$conf->force_default_lang) { //if NOT force default language...
        $languages->detectFromBrowser();
    } else {
        if (isset($conf->default_lang)) {
            $languages->setSelectedLangId($conf->default_lang);
        } else {
            $languages->setSelectedLangId('en');
        }
    }
    if (!isset($_SESSION['nvll_lang']) || $_SESSION['nvll_lang'] != "default") $_SESSION['nvll_lang'] = $languages->getSelectedLangId();
}

$lang = $languages->getSelectedLangId();

require './languages/en.php';
if ($lang != 'en') { //if NOT English...
    $lang_file = './languages/' . basename($lang) . '.php';
    if (is_file($lang_file)) {
        require $lang_file;
    }
}

//--------------------------------------------------------------------------------

//--------------------------------------------------------------------------------
// Set the theme...
//--------------------------------------------------------------------------------

$themes = new NVLL_Themes('./themes/', $conf->default_theme);

//TODO: Check $_REQUEST['theme'] also when NOT use_theme?
if (isset($_REQUEST['theme']) && isset($conf->use_theme) && $conf->use_theme) {
    //if the theme exists...
    if ($themes->setSelectedThemeName($_REQUEST['theme'])) $_SESSION['nvll_theme'] = $themes->getSelectedThemeName();
}

$default_theme_set = false;
if (!isset($_SESSION['nvll_theme'])) { //if session theme NOT already set...
    $_SESSION['nvll_theme'] = $themes->getDefaultThemeName();
    $default_theme_set = true;
}
//--------------------------------------------------------------------------------

if (isset($_SESSION['nvll_passwd']) && $_SESSION['nvll_passwd'] === false) {
    $ev = new NVLL_Exception($lang_strong_encryption_required . ".");
    require './html/header.php';
    require './html/error.php';
    require './html/footer.php';
    exit;
}

if ($session_has_expired > 0) {
    $_SESSION['nvll_login'] = "";

    if ($session_has_expired == 1) $ev = new NVLL_Exception($html_session_expired);
    if ($session_has_expired == 2) $ev = new NVLL_Exception($html_session_expired . " " . $html_session_ip_changed);

    require './html/header.php';
    require './html/error.php';
    require './html/footer.php';
    exit;
}

// Start with default smtp server/port, override later
if (empty($_SESSION['nvll_smtp_server'])) $_SESSION['nvll_smtp_server'] = $conf->default_smtp_server;
if (empty($_SESSION['nvll_smtp_port'])) $_SESSION['nvll_smtp_port'] = $conf->default_smtp_port;
// Default login to just the username
if (isset($_SESSION['nvll_user']) && !isset($_SESSION['nvll_login'])) $_SESSION['nvll_login'] = $_SESSION['nvll_user'];
// Check allowed chars for login
if (
    isset($_SESSION['nvll_login']) && $_SESSION['nvll_login'] != ''
    && isset($conf->allowed_char) && $conf->allowed_char != ''
    && !preg_match("|" . $conf->allowed_char . "|", $_SESSION['nvll_login'])
) {
    $ev = new NVLL_Exception($html_wrong);
    require './html/header.php';
    require './html/error.php';
    require './html/footer.php';
    exit;
}

// Were we provided with a fillindomain to use?
if (isset($_REQUEST['fillindomain']) && isset($conf->typed_domain_login)) {
    for ($count = 0; $count < count($conf->domains); $count++) {
        if ($_REQUEST['fillindomain'] == $conf->domains[$count]->domain) $_REQUEST['domain_index'] = $count;
    }
}

// Were we provided with a domain_index to use
if (isset($_REQUEST['domain_index']) && !(isset($_REQUEST['server']))) {
    $domain_index = $_REQUEST['domain_index'];

    if (!isset($conf->domains[$domain_index])) {
        $ev = new NVLL_Exception($lang_could_not_connect);
        require './html/header.php';
        require './html/error.php';
        require './html/footer.php';
        exit;
    }

    $domain = new NVLL_Domain($conf->domains[$domain_index]);
    $_SESSION['nvll_domain'] = $conf->domains[$domain_index]->domain;
    $_SESSION['nvll_domain_index'] = $domain_index;
    $_SESSION['nvll_servr'] = $conf->domains[$domain_index]->in;
    $_SESSION['nvll_smtp_server'] = $conf->domains[$domain_index]->smtp;
    $_SESSION['nvll_smtp_port'] = $conf->domains[$domain_index]->smtp_port;
    $_SESSION['smtp_auth'] = $conf->domains[$domain_index]->smtp_auth_method;
    $_SESSION['imap_namespace'] = $conf->domains[$domain_index]->imap_namespace;
    $_SESSION['ucb_pop_server'] = $conf->domains[$domain_index]->have_ucb_pop_server;
    $_SESSION['quota_enable'] = $conf->domains[$domain_index]->quota_enable;
    $_SESSION['quota_type'] = $conf->domains[$domain_index]->quota_type;

    // Check allowed logins
    if (!$domain->isAllowedLogin($_SESSION['nvll_login'])) {
        //php.log,syslog message to be used against brute force attempts e.g. with fail2ban
        //don't change text or rules may fail
        $log_string = 'NVLL: failed login from rhost=' . $_SERVER['REMOTE_ADDR'] . ' to server=' . $_SESSION['nvll_servr'] . ' as user=' . $_SESSION['nvll_login'] . '';
        error_log($log_string);

        if (isset($conf->syslog) && $conf->syslog) syslog(LOG_INFO, $log_string);
        $ev = new NVLL_Exception($html_login_not_allowed);

        require './html/header.php';
        require './html/error.php';
        require './html/footer.php';
        exit;
    }

    //Do we have login aliases?
    $_SESSION['nvll_login'] = $domain->replaceLoginAlias($_SESSION['nvll_login']);
    // Do we provide the domain with the login?
    if ($domain->useLoginWithDomain()) {
        if ($domain->hasLoginWithDomainCharacter()) {
            $_SESSION['nvll_login'] .= $domain->getLoginWithDomainCharacter() . $_SESSION['nvll_domain'];
        } else if (preg_match("|([A-Za-z0-9]+)@([A-Za-z0-9]+)|", $_SESSION['nvll_login'], $regs)) {
            $_SESSION['nvll_login'] = $_SESSION['nvll_login'];
            $_SESSION['nvll_domain'] = $regs[2];
        } else {
            $_SESSION['nvll_login'] .= '@' . $_SESSION['nvll_domain'];
        }
        $_SESSION['nvll_login_mailaddress'] = $_SESSION['nvll_login'];
        //TODO: Drop $_SESSION['nvll_login_with_domain'] first, if we drop get_default_from_address() and "config_check.php"!
        $_SESSION['nvll_login_with_domain'] = true;
    }

    //append prefix to login
    $_SESSION['nvll_login'] = $domain->addLoginPrefix($_SESSION['nvll_login']);
    //append suffix to login
    $_SESSION['nvll_login'] = $domain->addLoginSuffix($_SESSION['nvll_login']);

    unset($domain);
}

// Or did the user provide the details themselves
if (isset($_REQUEST['server'])) {
    $server = NVLL_Request::getStringValue('server');
    $servtype = strtolower($_REQUEST['servtype']);
    $port = NVLL_Request::getStringValue('port');
    $servr = $server . '/' . $servtype . ':' . $port;
    // Use as default domain for user's address
    $_SESSION['nvll_domain'] = $server;
    $_SESSION['nvll_servr'] = $servr;
}

// Cache the user's preferences/filters
if (isset($_SESSION['nvll_user']) && isset($_SESSION['nvll_domain'])) {
    //is user in auto update list?
    if (isset($conf->auto_update['user'][0])) {
        if ($conf->auto_update['user'][0] == "all" || in_array($_SESSION['nvll_user'] . '@' . $_SESSION['nvll_domain'], $conf->auto_update['user'])) $_SESSION['auto_update'] = true;
    }

    //TODO: Move to NVLL_Session::loadUserPrefs()?
    $ev = null;
    $user_key = NVLL_Session::getUserKey();

    // Preferences
    if (!NVLL_Session::existsUserPrefs()) {
        //TODO: Move to NVLL_Session::loadUserPrefs()?
        NVLL_Session::setUserPrefs(NVLL_UserPrefs::read($user_key, $ev));
        if (NVLL_Exception::isException($ev)) {
            echo "<p>User prefs error ($user_key): " . $ev->getMessage() . "</p>";
            exit(1);
        }
    }

    $user_prefs = NVLL_Session::getUserPrefs();

    //--------------------------------------------------------------------------------
    // Set and load the user prefs language...
    //--------------------------------------------------------------------------------
    if (!isset($_SESSION['nvll_lang']) || (isset($_SESSION['nvll_lang']) && $_SESSION['nvll_lang'] == 'default')) {
        if (isset($user_prefs->lang) && $user_prefs->lang != '' && $user_prefs->lang != 'default') {
            $userLang = $languages->getSelectedLangId();
            if ($languages->setSelectedLangId($user_prefs->lang)) { //if the language exists...
                $userLang = $languages->getSelectedLangId();
                //if (($userLang != 'en') && ($userLang != $lang)) { //if NOT English AND current language...
                if ($userLang != $lang) { //if NOT current language...
                    $_SESSION['nvll_lang'] = $languages->getSelectedLangId();
                    $lang = $languages->getSelectedLangId();
                    require './languages/' . $lang . '.php';
                }
            }
            unset($userLang);
        }
    }
    unset($languages);
    //--------------------------------------------------------------------------------

    //--------------------------------------------------------------------------------
    // Set the user prefs theme...
    //--------------------------------------------------------------------------------
    if ($default_theme_set || !isset($_SESSION['nvll_theme']) || (isset($_SESSION['nvll_theme']) && $_SESSION['nvll_theme'] == 'default')) {
        if (isset($conf->use_theme) && ($conf->use_theme == true)) { //if allow theme changing...
            if (isset($user_prefs->theme) && $user_prefs->theme != '' && $user_prefs->theme != 'default') {
                if ($themes->setSelectedThemeName($user_prefs->theme)) { //if the theme exists...
                    $_SESSION['nvll_theme'] = $themes->getSelectedThemeName();
                }
            }
        }
    }
    unset($themes);
    //--------------------------------------------------------------------------------

    // Filters
    if (!empty($conf->prefs)) {
        if (!isset($_SESSION['nvll_user_filters'])) {
            $_SESSION['nvll_user_filters'] = NVLL_UserFilters::read($user_key, $ev);
            if (NVLL_Exception::isException($ev)) {
                echo "<p>User filters error ($user_key): " . $ev->getMessage() . "</p>";
                exit(1);
            }
        }
        $user_filters = $_SESSION['nvll_user_filters'];
    }
}

require_once './config/conf_lang.php';
require_once './config/conf_charset.php';

// allow PHP script to consume more memory than default setting for
// big attachments
if (isset($conf->memory_limit) && $conf->memory_limit != '') @ini_set("memory_limit", $conf->memory_limit);
