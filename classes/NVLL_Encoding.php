<?php

/**
 * Class for wrapping the encoding from a imap_fetchstructure() object
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

/**
 * Wrapping the encoding from a imap_fetchstructure() object
 * 
 * @todo: Add is7Bit() function.
 * @todo: Add is8Bit() function.
 * @todo: Add isBinary() function.
 * @todo: Add isBase64() function.
 * @todo: Add isQuotedPrintable() function.
 * @todo: Add isOther() function.
 */
class NVLL_Encoding
{
    /**
     * Encoding
     * @var integer
     * @access private
     */
    private $_encoding;

    /**
     * Initialize the wrapper
     * @param integer $encoding Encoding
     */
    public function __construct($encoding = null)
    {
        $this->_encoding = -1;
        //if valid type...
        if (is_int($encoding)) $this->_encoding = $encoding;
    }

    /**
     * ...
     * @return string Encoding text
     */
    public function __toString()
    {
        switch ($this->_encoding) {
            case 0:
                return '7BIT';
            case 1:
                return '8BIT';
            case 2:
                return 'BINARY';
            case 3:
                return 'BASE64';
            case 4:
                return 'QUOTED-PRINTABLE';
            case 5:
                return 'OTHER';
        }
        return '';
    }

    /**
     * Encode data to Base64URL
     * @param string $data
     * @return boolean|string
     */
    public static function base64url_encode($data)
    {
        // First of all you should encode $data to Base64 string
        $b64 = base64_encode($data);
        // Make sure you get a valid result, otherwise, return FALSE, as the base64_encode() function do
        if ($b64 === false) return false;
        // Convert Base64 to Base64URL by replacing “+” with “-” and “/” with “_”
        $url = strtr($b64, '+/', '-_');
        // Remove padding character from the end of line and return the Base64URL result
        return rtrim($url, '=');
    }

    /**
     * Decode data from Base64URL
     * @param string $data
     * @param boolean $strict
     * @return boolean|string
     */
    public static function base64url_decode($data, $strict = false)
    {
        // Convert Base64URL to Base64 by replacing “-” with “+” and “_” with “/”
        $b64 = strtr($data, '-_', '+/');
        // Decode Base64 string and return the original data
        return base64_decode($b64, $strict);
    }
}
