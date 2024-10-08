<?php

/**
 * Class for wrapping a mail address
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

/**
 * Wrapping a mail address
 */
class NVLL_MailAddress
{
    /**
     * Name
     * @var string
     * @access private
     */
    private $_name;

    /**
     * Address
     * @var string
     * @access private
     */
    private $_address;

    /**
     * Initialize the mail address wrapper
     * @param string $mailAddress Mail address (with or without Name)
     * @param string $mailName Mail name (optional)
     */
    function __construct($mailAddress, $mailName = '')
    {
        $this->_name = '';
        $this->_address = '';

        if (isset($mailAddress) && is_string($mailAddress) && !empty($mailAddress)) { //if mail address is set...
            //TODO: http://code.iamcal.com/php/rfc822/
            $pos1 = strrpos($mailAddress, '<');
            $pos2 = strrpos($mailAddress, '>');
            if ($pos1 !== false && $pos2 !== false) { //if "<" AND ">" are found...
                $name = trim(substr($mailAddress, 0, $pos1));
                $name = trim($name, '"\''); //trim " AND '
                $address = trim(substr($mailAddress, ($pos1 + 1), ($pos2 - $pos1 - 1)));

                //TODO: Check if is valid address!
                $this->_name = $name;
                $this->_address = $address;
            } else { //if NOT "<" AND ">" are found...
                //TODO: Check if is valid address!
                $this->_address = trim($mailAddress);
            }

            //TODO: Drop MISSING_MAILBOX@SYNTAX_ERROR address?
        }
        if (is_string($mailName) && !empty($mailName)) { //if name is set...
            $this->_name = $mailName;
        }
    }

    /**
     * Get the name
     * @return string Name
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Has a name?
     * @return bool Has name?
     */
    public function hasName()
    {
        return !empty($this->_name);
    }

    /**
     * Set the name
     * @param string $name Name
     */
    public function setName($name)
    {
        if (isset($name) && is_string($name)) $this->_name = $name;
    }

    /**
     * Get the address
     * @return string Address
     */
    public function getAddress()
    {
        return $this->_address;
    }

    /**
     * Has a address?
     * @return string Has address?
     */
    public function hasAddress()
    {
        return !empty($this->_address);
    }

    /**
     * Set the address
     * @param string $address Address
     */
    public function setAddress($address)
    {
        if (isset($address) && is_string($address)) $this->_address = $address;
    }

    /**
     * ...
     */
    public function __toString()
    {
        if (!$this->hasAddress()) return '';
        if ($this->hasName()) {
            $name = $this->getName();
            if (strpos($name, ' ') !== false) {
                return '"' . $name . '" <' . $this->getAddress() . '>';
            } else {
                return $name . ' <' . $this->getAddress() . '>';
            }
        }
        return $this->getAddress();
    }

    /**
     * Is valid mail address?
     * @param string $address Mail address
     * @return bool Valid?
     * @static
     */
    public static function isValidAddress($address)
    {
        if (strlen($address) > 254)  return false;
        if (!filter_var($address, FILTER_VALIDATE_EMAIL)) return false;

        list($user, $domain) = explode('@', $address);

        if (!checkdnsrr($domain, 'MX') && !checkdnsrr($domain, 'A')) return false;

        return true;
    }

    /**
     * Are mail address are equal?
     * @param string $a Mail address A (with or without Name)
     * @param string $b Mail address B (with or without Name)
     * @return int -1 (Invalid) or 0 (Unequal) or 1 (Equal);
     */
    public static function compareAddress($a, $b)
    {
        if (!isset($a) || !isset($b)) return -1;

        $mailAddressA = new NVLL_MailAddress($a);
        if (!$mailAddressA->hasAddress()) return -1;

        $mailAddressB = new NVLL_MailAddress($b);
        if (!$mailAddressB->hasAddress()) return -1;

        $addressA = mb_strtolower($mailAddressA->getAddress(), 'UTF-8');
        $addressB = mb_strtolower($mailAddressB->getAddress(), 'UTF-8');

        if ($addressA == $addressB) return 1;
        return 0;
    }

    /**
     * Chops the address part "<foo@bar.org>" from a full mail address "Foo Bar <foo@bar.org>"
     * @param string $mailAddress Mail address
     * @return string Name (or address)
     * @static
     */
    public static function chopAddress($mailAddress)
    {
        return preg_replace('|\s+<\S+@\S+>|', '', $mailAddress);
    }
}
