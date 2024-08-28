<?php

/**
 * Class for wrapping the $_REQUEST array
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

/**
 * Wrapping the $_REQUEST array
 */
class NVLL_Request
{
    /**
     * Get a string value from the request
     * @param string $key Key
     * @param string $defaultValue Default vaule
     * @return string Value
     * @static
     */
    public static function getStringValue($key, $defaultValue = '')
    {
        if (isset($_REQUEST[$key])) {
            //if (get_magic_quotes_gpc()) {  // returns always false since php 5.4
            //    return stripslashes($_REQUEST[$key]);
            //}
            return $_REQUEST[$key];
        }
        return $defaultValue;
    }

    /**
     * Get a bool value from the request
     * @param string $key Key
     * @param bool $defaultValue Default vaule
     * @return bool Value
     * @static
     */
    public static function getBoolValue($key, $defaultValue = false)
    {
        if (isset($_REQUEST[$key])) {
            return NVLL_Request::convertToBool($_REQUEST[$key]);
        }
        return NVLL_Request::convertToBool($defaultValue);
    }

    /**
     * Convert value to bool
     * @param mixed $value Value
     * @return bool Bool value
     * @static
     */
    public static function convertToBool($value)
    {
        if ($value === true || $value === 1 || strtolower($value) === 'true' || $value === '1') {
            return true;
        }
        return false;
    }
}
