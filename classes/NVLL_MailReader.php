<?php

/**
 * Class for reading a mail
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

require_once 'NVLL_SMTP.php';
require_once 'NVLL_MailStructure.php';
require_once 'NVLL_MailPart.php';
require_once 'NVLL_MailParts.php';
require_once 'NVLL_HeaderInfo.php';
require_once 'NVLL_Header.php';

/**
 * Reading details from a mail
 */
class NVLL_MailReader
{
    /**
     * Message Number
     * @var integer
     * @access private
     */
    private $_messageNumber;

    /**
     * Mail parts
     * @var NVLL_MailParts
     * @access private
     */
    private $_mailparts;

    /**
     * Charset
     * @var string
     * @access private
     */
    private $_charset;
    /**
     * Size in kilobyte
     * @var integer
     * @access private
     */
    private $_size;
    /**
     * Has attachments?
     * @var boolean
     * @access private
     */
    private $_hasAttachments;

    /**
     * Message ID
     * @var string
     * @access private
     */
    private $_messageid;
    /**
     * Subject
     * @var string
     * @access private
     */
    private $_subject;
    /**
     * "From" address
     * @var string
     * @access private
     */
    private $_fromaddress;
    /**
     * "To" address
     * @var string
     * @access private
     */
    private $_toaddress;
    /**
     * "Cc" address
     * @var string
     * @access private
     */
    private $_ccaddress;
    /**
     * "Reply-To" address
     * @var string
     * @access private
     */
    private $_replytoaddress;
    /**
     * Timestamp
     * @var integer
     * @access private
     */
    private $_timestamp;
    /**
     * Is unseen?
     * @var bool
     * @access private
     */
    private $_isunseen;
    /**
     * Is flagged?
     * @var bool
     * @access private
     */
    private $_isflagged;

    /**
     * Header
     * @var NVLL_Header
     * @access private
     */
    private $_header;

    /**
     * Initialize the mail reader
     * @param integer $msgno Message number
     * @param NVLL_IMAP $pop IMAP/POP3 class
     * @param bool $fullDetails Read full details?
     */
    public function __construct($msgno, &$pop, $fullDetails = true)
    {
        $this->_messageNumber = $msgno;

        //--------------------------------------------------------------------------------
        // Get values from structure...
        //--------------------------------------------------------------------------------
        $mailstructure = $pop->fetchstructure($msgno);

        $this->_mailparts = null;
        if ($fullDetails == true) { //if read full details...
            $this->_mailparts = new NVLL_MailParts($mailstructure);
        }

        $this->_charset = $mailstructure->getCharset('ISO-8859-1');
        $this->_size = $mailstructure->getSize();

        $this->_hasAttachments = false;
        if ($mailstructure->getInternetMediaType()->isMultipart() || $mailstructure->getInternetMediaType()->isApplication()) { //if "multipart" or "application" message...
            if (!$mailstructure->getInternetMediaType()->isAlternative() && !$mailstructure->getInternetMediaType()->isRelated()) {
                $this->_hasAttachments = true;
            }
        }
        //--------------------------------------------------------------------------------

        //--------------------------------------------------------------------------------
        // Get values from header info...
        //--------------------------------------------------------------------------------
        $mailheaderinfo = $pop->headerinfo($msgno, $this->_charset);

        $this->_messageid = $mailheaderinfo->getMessageId();
        $this->_subject = $mailheaderinfo->getSubject();
        $this->_fromaddress = $mailheaderinfo->getFromAddress();
        $this->_toaddress = $mailheaderinfo->getToAddress();
        $this->_ccaddress = $mailheaderinfo->getCcAddress();
        $this->_replytoaddress = $mailheaderinfo->getReplyToAddress();
        $this->_timestamp = $mailheaderinfo->getTimestamp();

        $this->_isunseen = false;
        $this->_isflagged = false;
        if ($pop->is_imap()) {
            $this->_isunseen = $mailheaderinfo->isUnseen();
            $this->_isflagged = $mailheaderinfo->isFlagged();
        }
        //--------------------------------------------------------------------------------

        //--------------------------------------------------------------------------------
        // Get header...
        //--------------------------------------------------------------------------------
        $this->_header = $pop->fetchheader($msgno);
        //--------------------------------------------------------------------------------
    }

    /**
     * Get the message number
     * @return integer Message number
     */
    public function getMessageNumber()
    {
        return $this->_messageNumber;
    }

    /**
     * Get the body part
     * @return NVLL_MailPart Body part
     */
    public function getBodyPart()
    {
        if (!empty($this->_mailparts)) { //if mail parts exists...
            return $this->_mailparts->getBodyPart();
        }
        return null;
    }

    /**
     * Get the attachment parts
     * @return array Attachment parts
     */
    public function getAttachmentParts()
    {
        if (!empty($this->_mailparts)) { //if mail parts exists...
            return $this->_mailparts->getAttachmentParts();
        }
        return array();
    }

    /**
     * Get the charset from the mail
     * @return string Charset
     */
    public function getCharset()
    {
        return $this->_charset;
    }

    /**
     * Get the size from the mail in kilobyte
     * @return integer Size in kilobyte
     */
    public function getSize()
    {
        return $this->_size;
    }

    /**
     * Has the mail attachments?
     * @return boolean Has attachments?
     */
    public function hasAttachments()
    {
        return $this->_hasAttachments;
    }

    /**
     * Get the message id from the mail
     * @return string Message id
     */
    public function getMessageId()
    {
        return $this->_messageid;
    }

    /**
     * Get the subject from the mail
     * @return string Subject
     */
    public function getSubject()
    {
        return $this->_subject;
    }

    /**
     * Get the "From" address from the mail
     * @return string "From" address
     */
    public function getFromAddress()
    {
        return $this->_fromaddress;
    }

    /**
     * Get the "To" address from the mail
     * @return string "To" address
     */
    public function getToAddress()
    {
        return $this->_toaddress;
    }

    /**
     * Get the "Cc" address from the mail
     * @return string "Cc" address
     */
    public function getCcAddress()
    {
        return $this->_ccaddress;
    }

    /**
     * Get the "Reply-To" address from the mail
     * @return string "Reply-To" address
     */
    public function getReplyToAddress()
    {
        return $this->_replytoaddress;
    }

    /**
     * Get the date (in Unix time) from the mail
     * @return integer Date in Unix time
     */
    public function getTimestamp()
    {
        return $this->_timestamp;
    }

    /**
     * Is the mail unseen?
     * @return boolean Is unseen?
     */
    public function isUnseen()
    {
        return $this->_isunseen;
    }

    /**
     * Is the mail unseen on a UCB POP Server?
     * 
     * Check "Status" line with UCB POP Server to see if this is a new message.
     * This is a non-RFC standard line header.
     *
     * @return boolean Is unseen on a UCB POP Server?
     */
    public function isUnseenUcb()
    {
        if ($this->_header->getStatus() == '') {
            return true;
        }
        return false;
    }

    /**
     * Is the mail flagged?
     * @return boolean Is flagged?
     */
    public function isFlagged()
    {
        return $this->_isflagged;
    }

    /**
     * Get the RFC2822 format header from the mail
     * @return string RFC2822 format header
     */
    public function getHeader()
    {
        return $this->_header->getHeader();
    }

    /**
     * Get the priority from the mail
     * @return integer Priority
     */
    public function getPriority()
    {
        return $this->_header->getPriority();
    }

    /**
     * Get the (translated) priority text from the mail
     * @return string Priority text
     */
    public function getPriorityText()
    {
        return $this->_header->getPriorityText();
    }

    /**
     * Is the mail a HTML mail?
     * @todo Drop property, if we have a getContentType() or getFullMimeType() property
     * @return bool Is HTML mail?
     */
    public function isHtmlMail()
    {
        return preg_match('|text/html|i', $this->_header->getHeader());
    }

    /**
     * Is the mail a SPAM mail?
     * @return bool Is SPAM mail?
     */
    public function isSpam()
    {
        return $this->_header->hasSpamFlag();
    }
}
