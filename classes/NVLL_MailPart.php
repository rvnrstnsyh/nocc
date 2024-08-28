<?php

/**
 * Class for wrapping a mail part
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

require_once 'NVLL_MailStructure.php';

/**
 * Wrapping a mail part
 */
class NVLL_MailPart
{
    /**
     * Part structure
     * @var NVLL_MailStructure
     * @access private
     */
    private $partStructure;

    /**
     * Part number
     * @var string
     * @access private
     */
    private $partNumber;

    /**
     * if using Horde/Imap we need the parts mime ID to fetch body
     * @var string
     * @access private
     */
    private $mimeId;

    /**
     * Initialize the wrapper
     * @param NVLL_MailStructure $partStructure Part structure
     * @param string $partNumber Part number
     * @todo Throw exception, if no vaild mail structure?
     */
    public function __construct($partStructure, $partNumber, $isHorde = false)
    {
        $this->partStructure = $partStructure;
        $this->partNumber = $partNumber;
        $this->mimeId = "";
        if ($isHorde) {
            $this->mimeId = $partStructure->getStructure()->getMimeId();
        }
    }

    /**
     * Get the part structure
     * @return NVLL_MailStructure Part structure
     */
    public function getPartStructure()
    {
        return $this->partStructure;
    }

    /**
     * Get the part number
     * @return string Part number
     */
    public function getMimeId()
    {
        return $this->mimeId;
    }

    /**
     * Get the part number
     * @return string Part number
     */
    public function getPartNumber()
    {
        return $this->partNumber;
    }

    /**
     * Get the internet media type (MIME type)
     * @return NVLL_InternetMediaType Internet media type
     */
    public function getInternetMediaType()
    {
        return $this->partStructure->getInternetMediaType();
    }

    /**
     * Get the transfer encoding
     * @return NVLL_Encoding Transfer encoding
     */
    public function getEncoding()
    {
        return $this->partStructure->getEncoding();
    }

    /**
     * Get the size from the part in kilobyte
     * @return integer Size in kilobyte
     */
    public function getSize()
    {
        return $this->partStructure->getSize();
    }
}
