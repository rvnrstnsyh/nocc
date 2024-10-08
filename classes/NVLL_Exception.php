<?php

/**
 * Class for exceptions
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

/**
 * Very simplistic version of PEAR.php
 */
class NVLL_Exception
{
    /**
     * Message
     * @var string
     */
    private $message = '';

    /**
     * Initialize the exception
     * @param string $message Message
     */
    public function __construct($message = 'unknown error')
    {
        $this->message = $message;
    }

    /**
     * Get the message from the exception
     * @return string Message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * ...
     * @param object $data Data
     * @return boolean Is exception?
     */
    public static function isException($data)
    {
        return (bool)(is_object($data) && ((get_class($data) == "NVLL_Exception") || (get_class($data) == "nvllexception")));
    }
}
