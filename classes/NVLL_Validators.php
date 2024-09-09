<?php

/**
 * Class for input validators
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

class NVLL_Validators
{
  /**
   * Validate and sanitize a username
   * @param string $username
   * @return string|false Sanitized username or false if invalid
   */
  public static function validateUsername($username)
  {
    $username = trim($username);
    if (preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) return $username;
    return false;
  }

  /**
   * Validate an email address
   * @param string $email
   * @return string|false Validated email or false if invalid
   */
  public static function validateEmail($email)
  {
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) return $email;
    return false;
  }

  /**
   * Validate and sanitize a domain name
   * @param string $domain
   * @return string|false Sanitized domain or false if invalid
   */
  public static function validateDomain($domain)
  {
    $domain = trim($domain);
    if (preg_match('/^(?:[-A-Za-z0-9]+\.)+[A-Za-z]{2,6}$/', $domain)) return $domain;
    return false;
  }

  /**
   * Validate and sanitize an SMTP server address
   * @param string $server
   * @return string|false Sanitized server address or false if invalid
   */
  public static function validateSmtpServer($server)
  {
    $server = trim($server);
    if (filter_var($server, FILTER_VALIDATE_DOMAIN) || filter_var($server, FILTER_VALIDATE_IP)) return $server;
    return false;
  }

  /**
   * Validate a port number
   * @param int $port
   * @return int|false Validated port number or false if invalid
   */
  public static function validatePort($port)
  {
    $port = filter_var($port, FILTER_VALIDATE_INT, [
      'options' => ['min_range' => 1, 'max_range' => 65535]
    ]);
    return $port !== false ? $port : false;
  }

  /**
   * Sanitize a string for output (prevent XSS)
   * @param string $string
   * @return string Sanitized string
   */
  public static function sanitizeOutput($string)
  {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
  }
}
