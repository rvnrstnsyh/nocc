<?php

/**
 * Class for sending a mail with SMTP
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

require_once dirname(__FILE__) . '/NVLL_Exception.php';

/**
 * Sending a mail with SMTP
 */
class NVLL_SMTP
{
	var $smtp_server;
	var $port;
	var $from;
	var $to;
	var $cc;
	var $bcc;
	var $subject;
	var $data;
	var $pipelining;
	var $pipelining_count;

	/**
	 * Initialize the class
	 */
	public function __construct()
	{
		$this->smtp_server = '';
		$this->port = '';
		$this->from = '';
		$this->to = array();
		$this->cc = array();
		$this->bcc = array();
		$this->subject = '';
		$this->data = '';
		$this->pipelining = false;
		$this->pipelining_count = 0;
	}

	public function check_response($cmd, $smtp, &$response)
	{
		$error = false;
		$response = '';

		if ($this->pipelining && ($cmd == "MAIL" || $cmd == "RCPT" || $cmd == "DATA")) $this->pipelining_count++;
		if (! $this->pipelining || ($cmd != "MAIL" && $cmd != "RCPT")) {
			do {
				$line = fgets($smtp, 1024);
				if ($line != false) {
					if ($this->pipelining && $this->pipelining_count > 0) $this->pipelining_count--;
					if (substr($line, 4, 10) == "PIPELINING") {
						$this->pipelining = true;
						$this->pipelining_count = 0;
					}

					$response = $response . $cmd . ':' . trim($line) . " | ";
					if ($line[0] != '2' && $line[0] != '3') $error = true;
				}
			} while ($line != false && (
				empty($line) || substr($line, 3, 1) == '-' || ($this->pipelining && $this->pipelining_count > 0)
			));
		}
		return $error;
	}

	public function smtp_open()
	{
		global $conf;
		global $html_smtp_error_no_conn;
		global $html_smtp_error_unexpected;

		// $smtp = fsockopen($this->smtp_server, $this->port, $errno, $errstr); 
		$context = stream_context_create();
		$domain_index = $_SESSION['nvll_domain_index'];

		if (isset($conf->domains[$domain_index]->smtp_allow_self_signed) && $conf->domains[$domain_index]->smtp_allow_self_signed == true) {
			stream_context_set_option($context, "ssl", "allow_self_signed", $conf->domains[$domain_index]->smtp_allow_self_signed);
		}
		if (isset($conf->domains[$domain_index]->smtp_verify_peer) && $conf->domains[$domain_index]->smtp_verify_peer == false) {
			stream_context_set_option($context, "ssl", "verify_peer", $conf->domains[$domain_index]->smtp_verify_peer);
		}
		if (isset($conf->domains[$domain_index]->smtp_verify_peer_name) && $conf->domains[$domain_index]->smtp_verify_peer_name == false) {
			stream_context_set_option($context, "ssl", "verify_peer_name", $conf->domains[$domain_index]->smtp_verify_peer_name);
		}
		if (isset($conf->domains[$domain_index]->smtp_peer_name) && $conf->domains[$domain_index]->smtp_peer_name != "") {
			stream_context_set_option($context, "ssl", "peer_name", $conf->domains[$domain_index]->smtp_peer_name);
		}
		if (isset($conf->domains[$domain_index]->smtp_security_level) && $conf->domains[$domain_index]->smtp_security_level >= 0) {
			stream_context_set_option($context, "ssl", "security_level", $conf->domains[$domain_index]->smtp_security_level);
		}

		$remote_socket = $this->smtp_server . ":" . $this->port;
		$smtp = stream_socket_client($remote_socket, $errno, $errstr, ini_get("default_socket_timeout"), STREAM_CLIENT_CONNECT, $context);

		if (!$smtp) return new NVLL_Exception($html_smtp_error_no_conn . ' : ' . $errstr);

		$response = "";
		if ($this->check_response("OPEN", $smtp, $response)) return new NVLL_Exception($html_smtp_error_unexpected . ' : ' . $response);

		return $smtp;
	}

	public function smtp_helo($smtp)
	{
		global $html_smtp_error_unexpected;

		fputs($smtp, "helo " . $_SERVER['SERVER_NAME'] . "\r\n");

		$response = "";
		if ($this->check_response("HELO", $smtp, $response)) return new NVLL_Exception($html_smtp_error_unexpected . ' : ' . $response);
		return (true);
	}

	public function smtp_ehlo($smtp)
	{
		global $html_smtp_error_unexpected;

		fputs($smtp, "ehlo " . $_SERVER['SERVER_NAME'] . "\r\n");

		$response = "";
		if ($this->check_response("EHLO", $smtp, $response)) return new NVLL_Exception($html_smtp_error_unexpected . ' : ' . $response);
		return true;
	}

	public function smtp_auth($smtp)
	{
		global $conf;
		global $html_smtp_error_unexpected;

		require_once dirname(__FILE__) . '/../functions/crypt.php';

		if (
			isset($_SESSION['nvll_domain_index'])
			&& isset($conf->domains[$_SESSION['nvll_domain_index']]->smtp_user)
			&& strlen($conf->domains[$_SESSION['nvll_domain_index']]->smtp_user) > 0
		) {
			$user = $conf->domains[$_SESSION['nvll_domain_index']]->smtp_user;
			$password = $conf->domains[$_SESSION['nvll_domain_index']]->smtp_password;
		} else {
			$user = $_SESSION['nvll_login'];
			$password = decpass($_SESSION['nvll_passwd'], $conf->master_key);
		}

		if (
			isset($conf->domains[$_SESSION['nvll_domain_index']]->smtp_user_without_domain)
			&& $conf->domains[$_SESSION['nvll_domain_index']]->smtp_user_without_domain == true
		) {
			$domain_char = '@';
			if (
				isset($conf->domains[$_SESSION['nvll_domain_index']]->login_with_domain_character)
				&& strlen($conf->domains[$_SESSION['nvll_domain_index']]->login_with_domain_character) > 0
			) {
				$domain_char = $conf->domains[$_SESSION['nvll_domain_index']]->login_with_domain_character;
			}
			$user = preg_replace("/" . $domain_char . ".*?$/", "", $user);
		}

		switch ($_SESSION['smtp_auth']) {
			case 'LOGIN':
				fputs($smtp, "auth login\r\n");

				$response = "";
				if ($this->check_response("LOGIN", $smtp, $response)) return new NVLL_Exception($html_smtp_error_unexpected . ' : ' . $response);

				fputs($smtp, base64_encode($user) . "\r\n");

				$response = "";
				if ($this->check_response("LOGIN USER", $smtp, $response)) return new NVLL_Exception($html_smtp_error_unexpected . ' : ' . $response);

				fputs($smtp, base64_encode($password) . "\r\n");

				$response = "";
				if ($this->check_response("LOGIN PASS", $smtp, $response)) return new NVLL_Exception($html_smtp_error_unexpected . ' : ' . $response);

				return (true);
				break;
			case 'NTLM':
				fputs($smtp, "helo " . $_SERVER['SERVER_NAME'] . "\r\n");

				$response = "";
				if ($this->check_response("STARTTLS HELO", $smtp, $response)) return new NVLL_Exception($html_smtp_error_unexpected . ' : ' . $response);

				fputs($smtp, "AUTH NTLM\r\n");

				$response = "";
				if ($this->check_response("STARTTLS AUTH NTLM", $smtp, $response)) return new NVLL_Exception($html_smtp_error_unexpected . ' : ' . $response);

				$message = NTLM_type1message();
				fputs($smtp, base64_encode($message) . "\r\n");
				$response = "";

				if ($this->check_response("NTLMSSP", $smtp, $response)) return new NVLL_Exception($html_smtp_error_unexpected . ' : ' . $response);

				$matches = array();
				if (preg_match("/^NTLMSSP:334\s+(.*)\s+|$/", $response, $matches)) $response = $matches[1];

				$response = base64_decode($response);
				$message = NTLM_type3message($response, "", "", $user, $password);

				fputs($smtp, base64_encode($message) . "\r\n");

				$response = "";
				if ($this->check_response("NTLMSSP", $smtp, $response)) return new NVLL_Exception($html_smtp_error_unexpected . ' : ' . $response);

				return (true);
				break;
			case 'TLS':
				fputs($smtp, "STARTTLS\r\n");

				$response = "";
				if ($this->check_response("STARTTLS", $smtp, $response)) return new NVLL_Exception($html_smtp_error_unexpected . ' : ' . $response);

				//stream_socket_enable_crypto( $smtp,true,STREAM_CRYPTO_METHOD_SSLv23_CLIENT);
				stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
				fputs($smtp, "helo " . $_SERVER['SERVER_NAME'] . "\r\n");

				$response = "";
				if ($this->check_response("STARTTLS HELO", $smtp, $response)) return new NVLL_Exception($html_smtp_error_unexpected . ' : ' . $response);

				fputs($smtp, "auth login\r\n");

				$response = "";
				if ($this->check_response("STARTTLS LOGIN", $smtp, $response)) return new NVLL_Exception($html_smtp_error_unexpected . ' : ' . $response);

				fputs($smtp, base64_encode($user) . "\r\n");

				$response = "";
				if ($this->check_response("STARTTLS LOGIN USER", $smtp, $response)) return new NVLL_Exception($html_smtp_error_unexpected . ' : ' . $response);

				fputs($smtp, base64_encode($password) . "\r\n");

				$response = "";
				if ($this->check_response("STARTTLS LOGIN PASS", $smtp, $response)) return new NVLL_Exception($html_smtp_error_unexpected . ' : ' . $response);

				return (true);
				break;
			case 'PLAIN':
				fputs($smtp, "auth plain " . base64_encode($user . chr(0) . $user . chr(0) . $password) . "\r\n");

				$response = "";
				if ($this->check_response("PLAIN", $smtp, $response)) return new NVLL_Exception($html_smtp_error_unexpected . ' : ' . $response);

				return (true);
				break;
			case '':
				break;
		}
		return true;
	}

	public function smtp_mail_from($smtp)
	{
		global $html_smtp_error_unexpected;

		fputs($smtp, "MAIL FROM:$this->from\r\n");

		$response = "";
		if ($this->check_response("MAIL", $smtp, $response)) return new NVLL_Exception($html_smtp_error_unexpected . ' : ' . $response);
		return true;
	}

	public function smtp_rcpt_to($smtp)
	{
		global $html_smtp_error_unexpected;

		// Modified by nicocha to use to, cc and bcc field
		while ($tmp = array_shift($this->to)) {
			if ($tmp == '' || $tmp == '<>') continue;

			fputs($smtp, "RCPT TO:$tmp\r\n");

			$response = "";
			if ($this->check_response("RCPT", $smtp, $response)) return new NVLL_Exception($html_smtp_error_unexpected . ' : ' . $response);
		}
		while ($tmp = array_shift($this->cc)) {
			if ($tmp == '' || $tmp == '<>') continue;

			fputs($smtp, "RCPT TO:$tmp\r\n");

			$response = "";
			if ($this->check_response("RCPT", $smtp, $response)) return new NVLL_Exception($html_smtp_error_unexpected . ' : ' . $response);
		}
		while ($tmp = array_shift($this->bcc)) {
			if ($tmp == '' || $tmp == '<>') continue;

			fputs($smtp, "RCPT TO:$tmp\r\n");

			$response = "";
			if ($this->check_response("RCPT", $smtp, $response)) return new NVLL_Exception($html_smtp_error_unexpected . ' : ' . $response);
		}
		return true;
	}

	public function smtp_data($smtp)
	{
		global $html_smtp_error_unexpected;

		fputs($smtp, "DATA\r\n");

		$response = "";
		if ($this->check_response("DATA", $smtp, $response)) return new NVLL_Exception($html_smtp_error_unexpected . ' : ' . $response);

		fputs($smtp, "$this->data");
		fputs($smtp, "\r\n.\r\n");

		$response = "";
		if ($this->check_response("RCVD DATA", $smtp, $response)) return new NVLL_Exception($html_smtp_error_unexpected . ' : ' . $response);
		return true;
	}

	public function smtp_quit($smtp)
	{
		global $html_smtp_error_unexpected;

		fputs($smtp, "QUIT\r\n");

		$response = "";
		if ($this->check_response("QUIT", $smtp, $response)) return new NVLL_Exception($html_smtp_error_unexpected . ' : ' . $response);
		return true;
	}

	public function send()
	{
		$smtp = $this->smtp_open();
		if (NVLL_Exception::isException($smtp)) return $smtp;

		unset($ev);

		$ev = $this->smtp_ehlo($smtp);
		if (NVLL_Exception::isException($ev)) return $ev;

		unset($ev);

		$ev = $this->smtp_auth($smtp);
		if (NVLL_Exception::isException($ev)) return $ev;

		unset($ev);

		$ev = $this->smtp_mail_from($smtp);
		if (NVLL_Exception::isException($ev)) return $ev;

		unset($ev);

		$ev = $this->smtp_rcpt_to($smtp);
		if (NVLL_Exception::isException($ev)) return $ev;

		unset($ev);

		$ev = $this->smtp_data($smtp);
		if (NVLL_Exception::isException($ev)) return $ev;

		unset($ev);

		$ev = $this->smtp_quit($smtp);
		if (NVLL_Exception::isException($ev)) return $ev;
	}
}
