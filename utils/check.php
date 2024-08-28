<?php

/**
 * Check environment
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

unset($ev);
// PHP version
if (!function_exists('version_compare') || version_compare(PHP_VERSION, '5.0.0', '<')) { //if older as PHP 5.0.0...
  $ev = new NVLL_Exception("You don't seem to be running PHP 5, you need at least PHP 5.0 to run NVLL.");
  require './html/header.php';
  require './html/error.php';
  require './html/footer.php';
  exit;
}

// Mandatory modules
if (!extension_loaded('imap')) {
  $ev = new NVLL_Exception("The IMAP module does not seem to be installed on this PHP setup, please see NVLL's documentation.");
}

if (!extension_loaded('iconv')) {
  $ev = new NVLL_Exception("The iconv module does not seem to be installed on this PHP setup, please see NVLL's documentation.");
}

if (!extension_loaded('mbstring')) {
  $ev = new NVLL_Exception("The mbstring module does not seem to be installed on this PHP setup, please see NVLL's documentation.");
}

// PHP setup
if (ini_get('register_globals') == true) {
  $ev = new NVLL_Exception("Please set \"register_globals\" to \"Off\" within your \"php.ini\" file in order for NVLL to run. If you don't have access to \"php.ini\", please consult the FAQ in order to fix this problem.");
}

// NVLL setup
if (empty($conf->tmpdir)) {
  $ev = new NVLL_Exception("\"\$conf->tmpdir\" is not set in \"config/conf.php\". NVLL cannot run.");
}

if (!empty($conf->prefs_dir) && !is_dir($conf->prefs_dir)) {
  $ev = new NVLL_Exception("\"\$conf->prefs_dir\" is set in \"config/conf.php\" but doesn't exists. You must create \"\$conf->prefs_dir\" ($conf->prefs_dir) in order for NVLL to run.");
}

if (!isset($conf->master_key) || $conf->master_key == '') {
  $ev = new NVLL_Exception("\"\$conf->master_key\" must be set in \"config/conf.php\" in order for NVLL to run.");
}

if (!isset($conf->column_order) || $conf->column_order == '') {
  $ev = new NVLL_Exception("\"\$conf->column_order\" must be set in \"config/conf.php\" in order for NVLL to run.");
}

if (isset($conf->contact_ldap)) {
  // Disable LDAP feature, if enabled but NOT supported
  if (($conf->contact_ldap === true) && !extension_loaded('ldap')) {
    $conf->contact_ldap = false;
  }

  // Disable LDAP, if LDAP SSL is not supported
  if (($conf->contact_ldap === true) && ($conf->contact_ldap_options['ssl'] === true) && !extension_loaded('openssl')) {
    $conf->contact_ldap = false;
    $conf->contact_ldap_options['ssl'] = false;
  }
}

// Display error message
if (isset($ev) && NVLL_Exception::isException($ev)) {
  require './html/header.php';
  require './html/error.php';
  require './html/footer.php';
  exit;
}
