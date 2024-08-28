<?php

/**
 * Class Horde_Autoloader for Horde/Imap classes
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

class Horde_Autoloader
{
	public function __construct()
	{
		spl_autoload_register(array($this, 'load_class'));
	}
	public static function register()
	{
		new Horde_Autoloader();
	}
	public function load_class($class_name)
	{
		global $conf;
		global $lang_horde_require_failed;

		$file = str_replace('_', DIRECTORY_SEPARATOR, $class_name) . '.php';
		$file = str_replace('\\', DIRECTORY_SEPARATOR, $file);
		$file_abs = "";
		$found = false;
		if (isset($conf->horde_imap_client_path) && strlen($conf->horde_imap_client_path) > 0) {
			$file_abs = preg_replace("/\\" . DIRECTORY_SEPARATOR . "*horde\\" . DIRECTORY_SEPARATOR . "*$/i", "", $conf->horde_imap_client_path);
			$file_abs = $file_abs . DIRECTORY_SEPARATOR . $file;
			$file_abs = str_replace('\\', DIRECTORY_SEPARATOR, $file_abs);
			$file_abs = str_replace('/', DIRECTORY_SEPARATOR, $file_abs);
			if (file_exists($file_abs)) {
				$found = true;
			}
		} else {
			if ($file_abs = stream_resolve_include_path($file)) {
				$found = true;
			}
		}
		if ($found) {
			require_once($file_abs);
		} else {
			$log_string = "NVLL: autoloader can't find include file for class " . $class_name;
			error_log($log_string);
			if (isset($conf->syslog) && $conf->syslog) {
				syslog(LOG_INFO, $log_string);
			}
			// this may be a problem if other autoloaders are registered
			throw new Exception($lang_horde_require_failed . "($class_name)");
		}
	}
}
Horde_Autoloader::register();
