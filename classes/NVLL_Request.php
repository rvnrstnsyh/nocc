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
     * @param string $defaultValue Default value
     * @return string Value
     * @static
     */
    public static function getStringValue($key, $defaultValue = '')
    {
        return $_REQUEST[$key] ?? $defaultValue;
    }

    /**
     * Get a bool value from the request
     * @param string $key Key
     * @param bool $defaultValue Default value
     * @return bool Value
     * @static
     */
    public static function getBoolValue($key, $defaultValue = false)
    {
        return isset($_REQUEST[$key]) ? self::convertToBool($_REQUEST[$key]) : self::convertToBool($defaultValue);
    }

    /**
     * Convert value to bool
     * @param mixed $value Value
     * @return bool Bool value
     * @static
     */
    public static function convertToBool($value)
    {
        return in_array($value, [true, 1, '1', 'true', 'on', 'yes'], true);
    }

    /**
     * Generate consistent URL parameters
     * @param array $params Associative array of parameter keys and values
     * @param array $defaultParams Default parameters to include (optional)
     * @return string Consistent URL parameter string
     * @static
     */
    public static function Params(array $params, array $defaultParams = [])
    {
        $allParams = array_merge($defaultParams, $params);
        $baseParams = [
            'service' => '',
            'mail' => '',
            'verbose' => '',
            'as_html' => '',
            'original' => '',
            'display_images' => '',
            // Add any other common parameters here
        ];

        foreach ($allParams as $key => $value) {
            if (array_key_exists($key, $baseParams)) {
                $baseParams[$key] = $value;
            } else {
                $baseParams[$key] = $value; // Add non-standard params at the end
            }
        }

        $urlParams = [];
        foreach ($baseParams as $key => $value) {
            if ($value !== '' && $value !== '0' && $value !== false && $value !== null) {
                $urlParams[] = urlencode($key) . '=' . urlencode($value);
            }
        }

        return implode('&', $urlParams);
    }
}
