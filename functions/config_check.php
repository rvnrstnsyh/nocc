<?php

/**
 * Check configuration
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

require_once dirname(__FILE__) . '/../classes/NVLL_MailAddress.php';

// This function allows you to customise the default e-mail address
function get_default_from_address()
{
	global $conf;

	if (!NVLL_Session::existsUserPrefs()) return '';

	$user_prefs = NVLL_Session::getUserPrefs();
	$mailAddress = $user_prefs->getMailAddress();

	if (!$mailAddress->hasAddress()) {
		if (isset($_SESSION['nvll_login_mailaddress'])) {
			$mailAddress->setAddress($_SESSION['nvll_login_mailaddress']);
		} else {
			if (isset($_SESSION['nvll_login']) && strlen($_SESSION['nvll_login']) > 0 && isset($_SESSION['nvll_domain']) && strlen($_SESSION['nvll_domain']) > 0) {
				$user_part = $_SESSION['nvll_login'];

				if (isset($conf->domains[$_SESSION['nvll_domain_index']]->from_part) && strlen($conf->domains[$_SESSION['nvll_domain_index']]->from_part) > 0) {
					$reg = $conf->domains[$_SESSION['nvll_domain_index']]->from_part;
					$reg = preg_replace("/\\\/", '\\\\\\', $reg);
					$user_part = preg_replace("/^" . $reg . "$/", "$1", $user_part);
				}

				if (filter_var($user_part, FILTER_VALIDATE_EMAIL) || strpos($user_part, '@') !== false) {
					$mailAddress->setAddress($user_part);
				} else {
					$mailAddress->setAddress($user_part . "@" . $_SESSION['nvll_domain']);
				}
			}
		}
	}
	//(string)... is not compatible with php 5.1 or lower
	return $mailAddress->__toString();
	//return (string)$mailAddress;
}

// Detect base url
if (!isset($conf->base_url) || $conf->base_url == '') {
	$path_info = pathinfo($_SERVER['SCRIPT_NAME']);
	if (substr($path_info['dirname'], -1, 1) == '/') {
		$dir_name = $path_info['dirname'];
	} else {
		$dir_name = $path_info['dirname'] . '/';
	}

	//Prevent a buggy behavior from PHP under Windows
	if ($path_info['dirname'] == '\\') $dir_name = '/';

	$conf->base_url = 'http';
	if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') $conf->base_url .=  's';
	$conf->base_url .= '://' . $_SERVER['HTTP_HOST'] . $dir_name;
}
