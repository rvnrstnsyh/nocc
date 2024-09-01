<?php

/**
 * Crypt functions
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

/**
 * Returns NTLM Type 3 message
 * @param string $type2message the Type 2 message from the server as a binary string
 * @param string $realm the clients realm/domain (not empty)
 * @param string $workstation the clients host name (not empty)
 * @param string $user the SMTP user name
 * @param string $password the users password
 * @return string $message the binary string composed NTLM Type 3 message to send to the server
 */
function NTLM_type3message($type2message = "", $realm = "", $workstation = "", $user = "", $password = "")
{
	// https://davenport.sourceforge.net/ntlm.html
	if (strlen($realm) == 0) $realm = "unknown";
	if (strlen($workstation) == 0) $workstation = "unknown";
	if (strlen($type2message) == 0) return "";

	$tn_sbuffer = unpack("vlength/vsize/Voffset", substr($type2message, 12, 8));
	$flags = substr($type2message, 20, 4);
	$domain = $realm;

	if ($flags && 0x0100) {  // Flag Target Type Domain (0x00010000) in little endian: 0x00000100
		$tn_data = substr($type2message, $tn_sbuffer['offset'], $tn_sbuffer['size']);
		$domain = mb_convert_encoding($tn_data, "ASCII", "UCS-2LE");
	}

	$challenge = substr($type2message, 24, 8);
	$pw_uni = mb_convert_encoding($password, "UCS-2LE");
	$message = "";

	// Separate conditions for better readability
	$openssl_loaded = extension_loaded("openssl");
	$openssl_encrypt_exists = function_exists("openssl_encrypt");
	$hash_loaded = extension_loaded("hash");
	$hash_function_exists = function_exists("hash");
	$md4_algo_available = in_array("md4", hash_algos());
	$des_ecb_available = count(preg_grep("/^des-ecb$/i", openssl_get_cipher_methods(true))) > 0;

	// Combine all conditions
	if ($openssl_loaded && $openssl_encrypt_exists && $hash_loaded && $hash_function_exists && $md4_algo_available && $des_ecb_available) {
		$md4 = hash("md4", $pw_uni, true);
		$pad = $md4 . str_repeat(chr(0), 21 - strlen($md4));
		$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length("des-ecb"));

		for ($i = 0; $i < 21; $i += 7) {
			$packed = "";
			for ($p = $i; $p < $i + 7; $p++) $packed .= str_pad(decbin(ord(substr($pad, $p, 1))), 8, "0", STR_PAD_LEFT);
			$key = "";
			for ($p = 0; $p < strlen($packed); $p += 7) {
				$s = substr($packed, $p, 7);
				$b = $s . ((substr_count($s, "1") % 2) ? "0" : "1");
				$key .= chr(bindec($b));
			}
			$message .= openssl_encrypt($challenge, "des-ecb", $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);
		}
	} else {
		return "";
	}

	$r_unicode = mb_convert_encoding($domain, "UCS-2LE");
	$r_length = strlen($r_unicode);
	$r_offset = 64;
	$u_unicode = mb_convert_encoding($user, "UCS-2LE");
	$u_length = strlen($u_unicode);
	$u_offset = $r_offset + $r_length;
	$ws_unicode = mb_convert_encoding($workstation, "UCS-2LE");
	$ws_length = strlen($ws_unicode);
	$ws_offset = $u_offset + $u_length;
	$lm = mb_convert_encoding("", "UCS-2LE");
	$lm_length = strlen($lm);
	$lm_offset = $ws_offset + $ws_length;
	$ntlm = $message;
	$ntlm_length = strlen($ntlm);
	$ntlm_offset = $lm_offset + $lm_length;
	$session = "";
	$session_length = strlen($session);
	$session_offset = $ntlm_offset + $ntlm_length;
	$message = "NTLMSSP\0" .
		"\x03\x00\x00\x00" .
		pack("v", $lm_length) .
		pack("v", $lm_length) .
		pack("V", $lm_offset) .
		pack("v", $ntlm_length) .
		pack("v", $ntlm_length) .
		pack("V", $ntlm_offset) .
		pack("v", $r_length) .
		pack("v", $r_length) .
		pack("V", $r_offset) .
		pack("v", $u_length) .
		pack("v", $u_length) .
		pack("V", $u_offset) .
		pack("v", $ws_length) .
		pack("v", $ws_length) .
		pack("V", $ws_offset) .
		pack("v", $session_length) .
		pack("v", $session_length) .
		pack("V", $session_offset) .
		"\x01\x02\x00\x00" .
		$r_unicode .
		$u_unicode .
		$ws_unicode .
		$lm .
		$ntlm;

	return $message;
}

/**
 * Returns NTLM Type 1 message
 * @param string $realm the clients realm/domain (not empty)
 * @param string $workstation the clients host name (not empty)
 * @return string $message the binary string composed NTLM Type 1 message to send to the server
 */
function NTLM_type1message($realm = "", $workstation = "")
{
	// https://davenport.sourceforge.net/ntlm.html
	if (strlen($realm) == 0) $realm = "unknown";
	if (strlen($workstation) == 0) $workstation = "unknown";

	$r_length = strlen($realm);
	$ws_length = strlen($workstation);
	$ws_offset = 32;
	$r_offset = $ws_offset + $ws_length;
	$message = "NTLMSSP\0" .
		"\x01\x00\x00\x00" .
		"\x07\x32\x00\x00" .
		pack("v", $r_length) .
		pack("v", $r_length) .
		pack("V", $r_offset) .
		pack("v", $ws_length) .
		pack("v", $ws_length) .
		pack("V", $ws_offset) .
		$workstation .
		$realm;
	return $message;
}

function encrXOR($string, $key)
{
	for ($i = 0; $i < strlen($string); $i++) {
		for ($j = 0; $j < strlen($key); $j++) {
			$string[$i] = $string[$i] ^ $key[$j];
		}
	}
	return $string;
}

function decrXOR($string, $key)
{
	for ($i = 0; $i < strlen($string); $i++) {
		for ($j = 0; $j < strlen($key); $j++) {
			$string[$i] = $key[$j] ^ $string[$i];
		}
	}
	return $string;
}

/**
 * Checks if required extensions and functions are available
 * @return array Associative array of availability flags
 */
function checkAvailability()
{
	static $availability = null;
	if ($availability === null) {
		$availability = [
			'openssl' => extension_loaded("openssl") &&
				function_exists("openssl_encrypt") &&
				function_exists("openssl_decrypt") &&
				in_array("aes-256-gcm", openssl_get_cipher_methods()),
			'sodium' => extension_loaded("sodium") &&
				function_exists("sodium_crypto_aead_aes256gcm_encrypt") &&
				function_exists("sodium_memzero") &&
				sodium_crypto_aead_aes256gcm_is_available(),
			'hash' => extension_loaded("hash") &&
				function_exists("hash") &&
				in_array("sha256", hash_algos())
		];
	}
	return $availability;
}

/**
 * Returns encrypted password
 * @param string $passwd Password
 * @param string $rkey Master key
 * @return string|false Encrypted password or false on failure
 */
function encpass($passwd, $rkey)
{
	$availability = checkAvailability();

	if (!$availability['hash']) return false;

	$key = hash("SHA256", $rkey, true);
	if ($availability['openssl']) {
		$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length("aes-256-gcm"));
		$tag = "";
		$encrypted = openssl_encrypt($passwd, "aes-256-gcm", $key, OPENSSL_RAW_DATA, $iv, $tag);
		return base64_encode($iv . $tag . $encrypted);
	} elseif ($availability['sodium']) {
		$nonce = random_bytes(SODIUM_CRYPTO_AEAD_AES256GCM_NPUBBYTES);
		$encrypted = sodium_crypto_aead_aes256gcm_encrypt($passwd, "", $nonce, $key);
		$result = base64_encode($nonce . $encrypted);
		sodium_memzero($passwd);
		sodium_memzero($key);
		return $result;
	}
	return false; // don't allow unsecure encryption
}

/**
 * Returns decrypted password
 * @param string $cipher Cipher
 * @param string $rkey Master key
 * @return string|false Decrypted password or false on failure
 */
function decpass($cipher, $rkey)
{
	$availability = checkAvailability();

	if (!$availability['hash']) return false;

	$key = hash("SHA256", $rkey, true);
	$decoded = base64_decode($cipher);
	if ($availability['openssl']) {
		$iv_length = openssl_cipher_iv_length("aes-256-gcm");
		$iv = substr($decoded, 0, $iv_length);
		$tag = substr($decoded, $iv_length, 16);
		$ciphertext = substr($decoded, $iv_length + 16);
		return openssl_decrypt($ciphertext, "aes-256-gcm", $key, OPENSSL_RAW_DATA, $iv, $tag);
	} elseif ($availability['sodium']) {
		$nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_AEAD_AES256GCM_NPUBBYTES, '8bit');
		$ciphertext = mb_substr($decoded, SODIUM_CRYPTO_AEAD_AES256GCM_NPUBBYTES, null, '8bit');
		$decrypted = sodium_crypto_aead_aes256gcm_decrypt($ciphertext, "", $nonce, $key);
		sodium_memzero($ciphertext);
		sodium_memzero($key);
		return $decrypted;
	}
	return false; // don't allow unsecure decryption
}
