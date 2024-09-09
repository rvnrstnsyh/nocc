<?php

/**
 * Class for wrapping the $_SESSION array
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

require_once 'NVLL_Encoding.php';
require_once 'NVLL_UserPrefs.php';
require_once 'NVLL_Validators.php';

/**
 * Wrapping the $_SESSION array
 */
class NVLL_Session
{

	/**
	 * Start the session
	 * @static
	 */
	public static function start($persistent = 0)
	{
		global $conf;

		$cookie_lifetime = 0;
		if ($persistent == 1) {
			$cookie_lifetime = 60 * 60 * 24 * 7 * 4; // 4 weeks
			if (isset($conf->max_session_lifetime)) $cookie_lifetime = $conf->max_session_lifetime;
		}

		$session_has_expired = 0;
		self::remove_old_sessions();

		if (!isset($_REQUEST['_vmbox']) || (strlen($_REQUEST['_vmbox']) > 0 && preg_match("/^NEXT_/", $_REQUEST['_vmbox']))) {
			foreach ($_COOKIE as $cookie_key => $cookie_value) {
				if (preg_match("/^NEXT_/", $cookie_key)) {
					$_vmbox = $cookie_key;

					session_name($_vmbox);
					session_set_cookie_params($cookie_lifetime, '/', '', true, true);
					session_start();

					$_SESSION['_vmbox'] = $_vmbox;
					//
					// The currently provided RSS URL (right next to INBOX) allows to view list of emails without authentification.
					// With the following if/then/else switch one can seen/answer/... the email
					//   without authentication and with the result of a complete logged in NVLL session.
					// This mechanism would allow complete sessions only using the RSS URL and without athentication.
					// Unclear what the RSS URL should be used for and what should be possible with it.
					// This is here and elsewhere tagged with
					//   RSS-QUESTION
					//
					//if( isset($_SESSION['rss']) && $_SESSION['rss'] ) {
					//}
					//else {
					if (isset($_SESSION['send_backup']) && ! isset($_GET['discard'])) {
						$send_backup = $_SESSION['send_backup'];
						session_write_close();
						self::new_session($persistent);
					}
					self::destroy();
					//}
				}
			}
		}

		$found_session = false;
		//
		//   RSS-QUESTION
		//
		//if( isset($_SESSION['rss']) && $_SESSION['rss'] ) {
		//	$found_session=true;
		//}
		//else {
		if (isset($_REQUEST['_vmbox']) && strlen($_REQUEST['_vmbox']) > 0) {
			$_vmbox = $_REQUEST['_vmbox'];

			session_name($_vmbox);
			session_set_cookie_params($cookie_lifetime, '/', '', true, true);
			session_start();

			if (isset($_SESSION['send_backup']) && ! isset($_GET['discard'])) $send_backup = $_SESSION['send_backup'];

			$_vmboxvalue = session_id();
			$_SESSION['_vmbox'] = $_vmbox;
			$_SESSION['_vmboxvalue'] = $_vmboxvalue;

			if ($_SESSION['_vmbox'] == "RSS") {
				$found_session = true;
			} else if (isset($_SESSION['nvll_loggedin']) && $_SESSION['nvll_loggedin']) {
				$_SESSION['restart_session'] = true;
				$found_session = true;
			} else if (self::load_session()) {
				$_SESSION['restart_session'] = true;
				$found_session = true;
			} else {
				self::destroy();
				if (preg_match("/^IM_/", $_vmbox)) {
					$session_has_expired = 1;
				}
			}

			if (isset($_SESSION['_vmbox']) && $_SESSION['_vmbox'] == "RSS") {
				//
			} else {
				if (!$found_session) {
					self::new_session($persistent);
					if (isset($send_backup)) {
						$_SESSION['send_backup'] = $send_backup;
					}
				} else {
					// Regenerate session ID if needed
					self::regenerate_session();
				}

				if ($found_session && self::check_session_age()) {
					self::destroy();
					$session_has_expired = 1;
					$found_session = false;
				}

				if ($found_session && isset($conf->check_client_ip) && $conf->check_client_ip) {
					if ($_SESSION['remote_addr'] != $_SERVER['REMOTE_ADDR']) {
						self::destroy();
						$found_session = false;
						$session_has_expired = 2;
					}
				}
			}
		} else {
			foreach ($_COOKIE as $cookie_key => $cookie_value) {
				if (preg_match("/^IM_/", $cookie_key)) {
					$_vmbox = $cookie_key;

					session_name($_vmbox);
					session_set_cookie_params($cookie_lifetime, '/', '', true, true);
					session_start();

					if (isset($_SESSION['send_backup'])) $send_backup = $_SESSION['send_backup'];

					$_vmboxvalue = session_id();
					$_SESSION['_vmbox'] = $_vmbox;
					$_SESSION['_vmboxvalue'] = $_vmboxvalue;
					$_SESSION['restart_session'] = true;

					if (isset($_SESSION['nvll_loggedin']) && $_SESSION['nvll_loggedin']) {
						$found_session = true;
						break;
					} else if (self::load_session()) {
						$found_session = true;
						break;
					} else {
						self::destroy();
					}
				}
			}

			if ($found_session && self::check_session_age()) {
				self::destroy();
				$session_has_expired = 1;
				$found_session = false;
			}

			if ($found_session && isset($conf->check_client_ip) && $conf->check_client_ip) {
				if ($_SESSION['remote_addr'] != $_SERVER['REMOTE_ADDR']) {
					self::destroy();
					$found_session = false;
					$session_has_expired = 2;
				}
			}
		}
		//
		//   RSS-QUESTION
		//
		//}
		if (!$found_session) {
			self::new_session($persistent);
			if (isset($send_backup)) {
				$_SESSION['send_backup'] = $send_backup;
			}
		}

		if (!isset($_SESSION['persistent'])) $_SESSION['persistent'] = -1;
		if ($persistent == 1) $_SESSION['persistent'] = 1;

		self::remove_old_session_tmp_file();
		$_SESSION['remote_addr'] = $_SERVER['REMOTE_ADDR'];

		return $session_has_expired;
	}

	/**
	 * Regenerate session ID periodically
	 * @static
	 */
	public static function regenerate_session()
	{
		global $conf;

		$regenerationInterval = 900; // 15 minutes.

		if (!isset($_SESSION['last_regeneration'])) $_SESSION['last_regeneration'] = time();
		if (time() - $_SESSION['last_regeneration'] > $regenerationInterval) {
			// Regenerate the session ID
			if (session_status() === PHP_SESSION_ACTIVE) {
				$oldSessionId = session_id();
				session_regenerate_id(true);
				$newSessionId = session_id();

				// Update session file name if necessary
				if (!empty($conf->prefs_dir)) {
					$oldFile = $conf->prefs_dir . '/' . $oldSessionId . '.session';
					$newFile = $conf->prefs_dir . '/' . $newSessionId . '.session';
					if (file_exists($oldFile)) rename($oldFile, $newFile);
				}

				// Update session variables
				$_SESSION['_vmboxvalue'] = $newSessionId;
				$_SESSION['last_regeneration'] = time();
			}
		}
	}

	/**
	 * Manage session time outs, don't rely on servers session gc or on user cookies
	 * @static
	 */
	public static function check_session_age()
	{
		global $conf;

		$session_expired = true;
		$max_session_age = 60 * 60 * 12;  //12 hours

		if (isset($conf->min_session_lifetime)) $max_session_age = $conf->min_session_lifetime;
		if (isset($_SESSION['persistent']) && $_SESSION['persistent'] == 1) {
			$max_session_age = 60 * 60 * 24 * 7 * 4;  //4 weeks
			if (isset($conf->max_session_lifetime)) $max_session_age = $conf->max_session_lifetime;
		}

		if (isset($_SESSION['creation_time'])) {
			if (time() - $_SESSION['creation_time'] <= $max_session_age) $session_expired = false;
			if (!$session_expired && (! isset($_SESSION['persistent']) || $_SESSION['persistent'] != 1)) $_SESSION['creation_time'] = time();
		}
		return $session_expired;
	}

	/**
	 * Remove old saved sessions
	 * @static
	 */
	public static function remove_old_sessions()
	{
		global $conf;

		$max_age = 60 * 60 * 24 * 7 * 4;  //4 weeks

		if (isset($conf->max_session_lifetime)) $max_age = $conf->max_session_lifetime;
		if (!isset($conf->prune_sessions) || ! $conf->prune_sessions == 0) {
			if (!empty($conf->prefs_dir)) {
				$old_session_files = glob($conf->prefs_dir . '/' . "IM_*");
				if (is_array($old_session_files) && count($old_session_files) > 0) {
					foreach ($old_session_files as $filename) {
						$last_mod = filemtime($filename);
						$age = time() - $last_mod;
						if ($age > $max_age) unlink($filename);
					}
				}
			}
		}
	}

	/**
	 * Remove old session tmp files
	 * @static
	 */
	public static function remove_old_session_tmp_file()
	{
		global $conf;

		if (!empty($conf->tmpdir) && isset($_SESSION['_vmbox']) && strlen($_SESSION['_vmbox']) > 0) {
			$available_session_files = glob($conf->tmpdir . '/' . $_SESSION['_vmbox'] . "_*");
			if (is_array($available_session_files) && count($available_session_files) > 0) {
				foreach ($available_session_files as $filename) {
					$_vmbox = preg_replace("/\.session$/", "", $filename);
					if (isset($_SESSION[$_vmbox]) && $_SESSION[$_vmbox] > 0) {
						$_SESSION[$_vmbox] = $_SESSION[$_vmbox] - 1;
					} else {
						unset($_SESSION[$_vmbox]);
						unlink($filename);
					}
				}
			}
		}

		if (!empty($conf->tmpdir)) {
			$old_session_files = glob($conf->tmpdir . '/' . "IM_*");
			if (is_array($old_session_files) && count($old_session_files) > 0) {
				foreach ($old_session_files as $filename) {
					$last_mod = filemtime($filename);
					$age = time() - $last_mod;
					$max_age = 60 * 60 * 1;  // 1 hour.
					if ($age > $max_age) {
						unlink($filename);
					}
				}
			}

			$old_session_files = glob($conf->tmpdir . '/' . "php*.att");
			if (is_array($old_session_files) && count($old_session_files) > 0) {
				foreach ($old_session_files as $filename) {
					$last_mod = filemtime($filename);
					$age = time() - $last_mod;
					$max_age = 60 * 60 * 24 * 1;  // 1 day.
					if ($age > $max_age) {
						unlink($filename);
					}
				}
			}
		}
	}

	/**
	 * Get next session name
	 * @static
	 */
	public static function get_next_session_name()
	{
		$current_name = session_name();
		$set_next = false;
		$next_name = "";

		foreach ($_COOKIE as $cookie_key => $cookie_value) {
			if (preg_match("/^IM_/", $cookie_key)) {
				if ($set_next) {
					$next_name = $cookie_key;
					break;
				}
				if ($current_name == $cookie_key) $set_next = true;
			}
		}

		if (strlen($next_name) == 0) $next_name = 'NEXT_' . NVLL_Encoding::base64url_encode(random_bytes(32));

		$next_name = "_vmbox=" . $next_name;
		return $next_name;
	}

	/**
	 * Rename current session
	 * @static
	 */
	public static function rename_session()
	{
		$old_vmbox = session_name();
		if (preg_match("/^NEXT_/", $old_vmbox)) {
			$_vmbox = 'IM_' . NVLL_Encoding::base64url_encode(random_bytes(32));
			//session_name($_vmbox);
			session_regenerate_id(true);

			$_vmboxvalue = session_id();
			$_SESSION['_vmbox'] = $_vmbox;
			$_SESSION['_vmboxvalue'] = $_vmboxvalue;

			setcookie($old_vmbox, '', time() - 3600, '/', '', true, true);
			//return true;
			return $_vmbox;
		} else {
			//return false;
			return "";
		}
	}

	/**
	 * Start a new  session
	 * @static
	 */
	public static function new_session($persistent = 0)
	{
		global $conf;

		// Ensure the session is not already active
		if (session_status() === PHP_SESSION_ACTIVE) session_write_close();

		$cookie_lifetime = 0;
		if ($persistent == 1) {
			$cookie_lifetime = 60 * 60 * 24 * 7 * 4; // 4 weeks.
			if (isset($conf->max_session_lifetime)) $cookie_lifetime = $conf->max_session_lifetime;
		}

		$_vmbox = 'NEXT_' . NVLL_Encoding::base64url_encode(random_bytes(32));

		// Set session name and cookie parameters before starting the session
		session_name($_vmbox);
		session_set_cookie_params($cookie_lifetime, '/', '', true, true); // Added 'secure' and 'httponly' flags
		// Now start the session
		session_start();

		$_vmboxvalue = session_id();
		$_SESSION['_vmbox'] = $_vmbox;
		$_SESSION['_vmboxvalue'] = $_vmboxvalue;
		$_SESSION['creation_time'] = time();
	}

	/**
	 * Save a session
	 * @static
	 */
	public static function save_session()
	{
		global $conf;

		if (!empty($conf->prefs_dir)) {
			// generate string with session information
			$save_string = session_id();
			$save_string .= " " . $_SESSION['nvll_user'];
			$save_string .= " " . $_SESSION['nvll_passwd'];
			$save_string .= " " . $_SESSION['nvll_login'];
			$save_string .= " " . $_SESSION['nvll_lang'];
			$save_string .= " " . $_SESSION['nvll_smtp_server'];
			$save_string .= " " . $_SESSION['nvll_smtp_port'];
			$save_string .= " " . $_SESSION['nvll_theme'];
			$save_string .= " " . $_SESSION['nvll_domain'];
			$save_string .= " " . $_SESSION['nvll_domain_index'];
			$save_string .= " " . $_SESSION['imap_namespace'];
			$save_string .= " " . $_SESSION['nvll_servr'];
			$save_string .= " " . $_SESSION['nvll_folder'];
			$save_string .= " " . $_SESSION['smtp_auth'];
			$save_string .= " " . $_SESSION['ucb_pop_server'];
			$save_string .= " " . $_SESSION['quota_enable'];
			$save_string .= " " . $_SESSION['quota_type'];
			$save_string .= " " . $_SESSION['creation_time'];
			$save_string .= " " . $_SESSION['persistent'];
			$save_string .= " " . $_SESSION['remote_addr'];
			// encode string to base64
			$save_string = base64_encode($save_string);
			// save string to file
			$filename = $conf->prefs_dir . '/' . $_SESSION['_vmbox'] . '.session';

			if (file_exists($filename) && !is_writable($filename)) return false;
			if (!is_writable($conf->prefs_dir)) return false;

			$file = fopen($filename, 'w');
			if (!$file) return false;

			fwrite($file, $save_string . "\n");
			fclose($file);

			return true;
		}
		return false;
	}

	/**
	 * Load a saved session file
	 * @static
	 */
	public static function load_session_file($_vmbox)
	{
		global $conf;

		if (empty($conf->prefs_dir)) return false;

		$filename = $conf->prefs_dir . '/' . $_vmbox . '.session';
		if (!file_exists($filename)) return false;

		$file = fopen($filename, 'r');
		if (!$file) return false;

		$line = trim(fgets($file, 1024));
		fclose($file);

		return $line;
	}

	/**
	 * Load a saved session into SESSION
	 * @static
	 */
	public static function load_session()
	{
		global $conf;

		if (empty($conf->prefs_dir)) return false;

		$_vmbox = session_name();
		$line = self::load_session_file($_vmbox);

		if (!$line) return false;

		list(
			$session_id,
			$_SESSION['nvll_user'],
			$_SESSION['nvll_passwd'],
			$_SESSION['nvll_login'],
			$_SESSION['nvll_lang'],
			$_SESSION['nvll_smtp_server'],
			$_SESSION['nvll_smtp_port'],
			$_SESSION['nvll_theme'],
			$_SESSION['nvll_domain'],
			$_SESSION['nvll_domain_index'],
			$_SESSION['imap_namespace'],
			$_SESSION['nvll_servr'],
			$_SESSION['nvll_folder'],
			$_SESSION['smtp_auth'],
			$_SESSION['ucb_pop_server'],
			$_SESSION['quota_enable'],
			$_SESSION['quota_type'],
			$_SESSION['creation_time'],
			$_SESSION['persistent'],
			$_SESSION['remote_addr']
		) = explode(" ", base64_decode($line));
		$_SESSION['nvll_folder'] = isset($_REQUEST['nvll_folder']) ? $_REQUEST['nvll_folder'] : 'INBOX';

		if (session_id() == $session_id) {
			// Regenerate session ID if needed after loading
			self::regenerate_session();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Remove a saved session file
	 * @static
	 */
	public static function remove_session_file()
	{
		global $conf;

		if (isset($conf->prefs_dir)) {
			$_vmbox = session_name();
			$filename = $conf->prefs_dir . '/' . $_vmbox . '.session';

			if (file_exists($filename)) unlink($filename);
		}
	}

	/**
	 * Destroy the session
	 * @param bool $forceSessionStart Force session start?
	 * @static
	 */
	public static function destroy($forceSessionStart = false)
	{
		$_vmbox = 'NVLLSESSID';

		if (isset($_SESSION['_vmbox']) && strlen($_SESSION['_vmbox']) > 0) $_vmbox = $_SESSION['_vmbox'];
		//session_name($_vmbox);
		self::remove_session_file();

		if ($forceSessionStart) {
			session_set_cookie_params(0, '/', '', true, true);
			session_start();
		}

		setcookie($_vmbox, '', time() - 3600, '/', '', true, true);
		$_SESSION = array();
		session_destroy();
	}

	/**
	 * Create session cookie
	 * @static
	 */
	public static function createCookie($persistent = 0)
	{
		global $conf;

		$cookie_lifetime = 0;
		if ($persistent == 1) {
			$cookie_lifetime = time() + 60 * 60 * 24 * 7 * 4; // 4 weeks.
			if (isset($conf->max_session_lifetime)) {
				$cookie_lifetime = time() + $conf->max_session_lifetime;
			}
		}

		$_vmbox = 'NVLLSESSID';
		if (isset($_SESSION['_vmbox']) && strlen($_SESSION['_vmbox']) > 0) $_vmbox = $_SESSION['_vmbox'];

		$_vmboxvalue = session_id();
		setcookie($_vmbox, $_vmboxvalue, $cookie_lifetime, '/', '', true, true);
	}

	/**
	 * Delete session cookie
	 * @static
	 */
	public static function deleteCookie()
	{
		$_vmbox = 'NVLLSESSID';
		if (isset($_SESSION['_vmbox']) && strlen($_SESSION['_vmbox']) > 0) $_vmbox = $_SESSION['_vmbox'];
		setcookie($_vmbox, '', time() - 3600, '/', '', true, true);
	}

	/**
	 * Get the URL query from the session
	 * @return string URL query
	 * @static
	 */
	public static function getUrlQuery()
	{
		#return session_name() . '=' . session_id();
		return "";
	}

	/**
	 * Get the URL session GET part
	 * @return string URL GET part
	 * @static
	 */
	public static function getUrlGetSession()
	{
		return "_vmbox=" . session_name();
	}

	/**
	 * Get the user key from the session
	 * @return string User key
	 * @static
	 */
	public static function getUserKey()
	{
		return $_SESSION['nvll_user'] . '@' . $_SESSION['nvll_domain'];
	}

	/**
	 * Get the SMTP server from the session
	 * @return string SMTP server
	 * @static
	 */
	public static function getSmtpServer()
	{
		if (isset($_SESSION['nvll_smtp_server'])) return $_SESSION['nvll_smtp_server'];
		return '';
	}

	/**
	 * Set the SMTP server from the session
	 * @param string $value SMTP server
	 * @static
	 */
	public static function setSmtpServer($value)
	{
		$sanitizedValue = NVLL_Validators::validateSmtpServer($value);
		if ($sanitizedValue !== false) {
			$_SESSION['nvll_smtp_server'] = $sanitizedValue;
		} else {
			throw new InvalidArgumentException("Invalid SMTP server address");
		}
	}

	/**
	 * Get quota enabling from the session
	 * @return bool Quota enabled?
	 * @static
	 */
	public static function getQuotaEnable()
	{
		if (isset($_SESSION['quota_enable']) && $_SESSION['quota_enable']) return true;
		return false;
	}

	/**
	 * Set quota enabling from the session
	 * @param bool $value Quota enabled?
	 * @static
	 */
	public static function setQuotaEnable($value)
	{
		$_SESSION['quota_enable'] = $value;
	}

	/**
	 * Get quota type (STORAGE or MESSAGE) from the session
	 * @return string Quota type
	 * @static
	 * @todo Check for STORAGE or MESSAGE?
	 */
	public static function getQuotaType()
	{
		if (isset($_SESSION['quota_type'])) return $_SESSION['quota_type'];
		return 'STORAGE';
	}

	/**
	 * Set quota type (STORAGE or MESSAGE) from the session
	 * @param string $value Quota type
	 * @static
	 * @todo Check for STORAGE or MESSAGE?
	 */
	public static function setQuotaType($value)
	{
		$_SESSION['quota_type'] = $value;
	}

	/**
	 * Exists user preferences in the session?
	 * @return boolean Exists user preferences?
	 * @static
	 */
	public static function existsUserPrefs()
	{
		if (isset($_SESSION['nvll_user_prefs'])) {
			if ($_SESSION['nvll_user_prefs'] instanceof NVLL_UserPrefs) return true;
		}
		return false;
	}

	/**
	 * Get user preferences from the session
	 * @return NVLL_UserPrefs User preferences
	 * @static
	 */
	public static function getUserPrefs()
	{
		if (self::existsUserPrefs()) return $_SESSION['nvll_user_prefs'];
		return new NVLL_UserPrefs('');
	}

	/**
	 * Set user preferences from the session
	 * @param NVLL_UserPrefs $value User preferences
	 * @static
	 * @todo Check for NVLL_UserPrefs?
	 */
	public static function setUserPrefs($value)
	{
		if ($value instanceof NVLL_UserPrefs) {
			$_SESSION['nvll_user_prefs'] = $value;
		} else {
			throw new InvalidArgumentException("Invalid user preferences object");
		}
	}

	/**
	 * Get HTML mail sending from the session
	 * @return bool User preferences
	 * @static
	 */
	public static function getSendHtmlMail()
	{
		if (isset($_SESSION['html_mail_send']) && $_SESSION['html_mail_send']) return true;
		return false;
	}

	/**
	 * Set HTML mail sending from the session
	 * @param bool $value User preferences
	 * @static
	 */
	public static function setSendHtmlMail($value)
	{
		$_SESSION['html_mail_send'] = $value;
	}
}
