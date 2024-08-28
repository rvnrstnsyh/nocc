<?php

/**
 * Class for wrapping mail parts
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

require_once 'NVLL_MailStructure.php';
require_once 'NVLL_MailPart.php';

/**
 * Wrapping mail parts
 */
class NVLL_MailParts
{
	/**
	 * Body part
	 * @var NVLL_MailPart
	 * @access private
	 */
	private $_bodyPart;

	/**
	 * Attachments parts
	 * @var array
	 * @access private
	 */
	private $_attachmentParts;

	/**
	 * Initialize the wrapper
	 * @param NVLL_MailStructure $mailstructure Mail structure
	 * @todo Throw exception, if no vaild structure?
	 */
	public function __construct($mailstructure)
	{
		$this->_bodyPart = null;
		$this->_attachmentParts = array();

		$parts = array();
		$this->_fillArrayWithParts($parts, $mailstructure);
		$body_index = -1;

		if (!empty($parts)) {
			$not_attachment_parts = array();
			for ($i = 0; $i < count($parts); $i++) {
				$bodyPart = $parts[$i];
				if (! $bodyPart->getPartStructure()->isAttachment()) {
					if ($bodyPart->getInternetMediaType()->isHtmlText() || $bodyPart->getInternetMediaType()->isPlainText()) {
						$not_attachment_parts[] = $i;
					}
					if ($bodyPart->getInternetMediaType()->isHtmlText()) {
						$body_index = $i;
					}
					if ($body_index == -1 && $bodyPart->getInternetMediaType()->isPlainText()) {
						$body_index = $i;
					}
				}
			}

			if ($body_index >= 0) $this->_bodyPart = $parts[$body_index];

			$count = 0;
			foreach ($not_attachment_parts as $i) {
				array_splice($parts, $i - $count, 1);
				$count++;
			}

			$this->_attachmentParts = $parts;
		}
	}

	/**
	 * Get the body part
	 * @return NVLL_MailPart Body part
	 */
	public function getBodyPart()
	{
		return $this->_bodyPart;
	}

	/**
	 * Get the attachment parts
	 * @return array Attachment parts
	 */
	public function getAttachmentParts()
	{
		return $this->_attachmentParts;
	}

	/**
	 * ...
	 * Based on a function from matt@bonneau.net
	 * @param array $parts Parts array
	 * @param NVLL_MailStructure $mailstructure Mail structure
	 * @param string $partNumber Part number
	 * @access private
	 * @todo Rewrite!
	 */
	private function _fillArrayWithParts(&$parts, $mailstructure, $partNumber = '', $skip_message = false)
	{
		//$this_part = $mailstructure->getStructure();
		$mailstructure_parts = $mailstructure->getParts();
		$parts_info = $mailstructure->getPartsInfo();
		$internetMediaType = $mailstructure->getInternetMediaType();
		if ($internetMediaType->isMultipart()) { //if multipart...
			//$num_parts = count($this_part->parts);
			$num_parts = count($mailstructure_parts);
			$found_plain = false;
			$found_html = false;
			if ($internetMediaType->isAlternativeMultipart()) {
				// check if alternative consists of PLAIN and HTML, if yes we skip the PLAIN
				for ($i = 0; $i < $num_parts; $i++) {
					//if( $this_part->parts[$i]->subtype == "PLAIN" ) {
					if ($mailstructure_parts[$i]->subtype == "PLAIN") {
						$found_plain = true;
					}
					//if( $this_part->parts[$i]->subtype == "HTML" ) {
					if ($mailstructure_parts[$i]->subtype == "HTML") {
						$found_html = true;
					}
				}
			}
			for ($i = 0; $i < $num_parts; $i++) {
				$subtype = strtolower($mailstructure_parts[$i]->subtype);

				if ($partNumber != '') {
					if (substr($partNumber, -1) != '.') $partNumber = $partNumber . '.';
				}
				if ($found_plain == true && $found_html == true) {
					if ($subtype != "plain") {
						$this->_fillArrayWithParts($parts, new NVLL_MailStructure($mailstructure_parts[$i], $parts_info), $partNumber . ($i + 1), $skip_message);
					}
				} else {
					$this->_fillArrayWithParts($parts, new NVLL_MailStructure($mailstructure_parts[$i], $parts_info), $partNumber . ($i + 1), $skip_message);
				}
			}
		} else if ($internetMediaType->isMessage()) { //if message...
			if ($internetMediaType->isRfc822Message()) { //if RFC822 message...
				if (empty($partNumber)) $partNumber = '1';

				$part = new NVLL_MailPart($mailstructure, $partNumber);
				array_unshift($parts, $part);
				$skip_message = true;
			}

			$num_parts = -1;
			if (isset($mailstructure_parts[0]->parts)) $num_parts = count($mailstructure_parts[0]->parts);
			for ($i = 0; $i < $num_parts; $i++) {
				$tmp_part = ($mailstructure_parts[0]->parts)[$i];
				$this->_fillArrayWithParts($parts, new NVLL_MailStructure($tmp_part, $parts_info), $partNumber . '.' . ($i + 1), $skip_message);
			}
		} else {
			if (empty($partNumber)) $partNumber = '1';
			$part = new NVLL_MailPart($mailstructure, $partNumber);
			if ($mailstructure->isAttachment() || !$skip_message || ! $internetMediaType->isPlainOrHtmlText()) array_unshift($parts, $part);
		}
	}
}
