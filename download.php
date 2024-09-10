<?php

/**
 * File for downloading the attachments
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

if (!isset($HTTP_USER_AGENT)) $HTTP_USER_AGENT = $_SERVER['HTTP_USER_AGENT'];

require_once dirname(__FILE__) .  '/classes/NVLL_IMAP.php';

require_once dirname(__FILE__) .  '/common.php';

try {
	$pop = new NVLL_IMAP();
	$mime = $_REQUEST['mime'];
	$filename = $_REQUEST['filename'];
	$mail = $_REQUEST['mail'];
	$transfer = $_REQUEST['transfer'];
	$part = $_REQUEST['part'];
	$filename = base64_decode($filename);
	$filename = preg_replace('{[\\/:\*\?"<>\|;]}', '_', str_replace('&#32;', ' ', $filename));

	//    $isIE = $isIE6 = 0;

	//probably outdated
	// Set correct http headers.
	// Thanks to Squirrelmail folks :-)
	//    if (strstr($HTTP_USER_AGENT, 'compatible; MSIE ') !== false &&
	//      strstr($HTTP_USER_AGENT, 'Opera') === false) {
	//        $isIE = 1;
	//    }

	//    if (strstr($HTTP_USER_AGENT, 'compatible; MSIE 6') !== false &&
	//      strstr($HTTP_USER_AGENT, 'Opera') === false) {
	//        $isIE6 = 1;
	//    }

	//    if ($isIE) {
	//        $filename=rawurlencode($filename);
	//        header("Pragma: public");
	//        header("Cache-Control: no-store, max-age=0, no-cache, must-revalidate"); // HTTP/1.1
	//        header("Cache-Control: post-check=0, pre-check=0", false);
	//        header("Cache-Control: private");
	//
	//        //set the inline header for IE, we'll add the attachment header later if we need it
	//        header("Content-Disposition: inline; filename=$filename");
	//    }

	if (preg_match('/\.pdf$/', $filename)) {
		header("Content-Type: application/pdf; name=\"$filename\"");
		header("Content-Disposition: inline; filename=\"$filename\"");
	} elseif (preg_match('/\.html$/', $filename)) {
		header("Content-Type: text/html; name=\"$filename\"");
		header("Content-Disposition: inline; filename=\"$filename\"");
	} elseif (preg_match('/\.jpe?g$/', $filename)) {
		header("Content-Type: image/jpeg; name=\"$filename\"");
		header("Content-Disposition: inline; filename=\"$filename\"");
	} elseif (preg_match('/\.png$/', $filename)) {
		header("Content-Type: image/png; name=\"$filename\"");
		header("Content-Disposition: inline; filename=\"$filename\"");
	} elseif (preg_match('/\.gif$/', $filename)) {
		header("Content-Type: image/gif; name=\"$filename\"");
		header("Content-Disposition: inline; filename=\"$filename\"");
	} elseif (preg_match('/\.bmp$/', $filename)) {
		header("Content-Type: image/bmp; name=\"$filename\"");
		header("Content-Disposition: inline; filename=\"$filename\"");
	} elseif (preg_match('/\.tiff$/', $filename)) {
		header("Content-Type: image/tiff; name=\"$filename\"");
		header("Content-Disposition: inline; filename=\"$filename\"");
	} else {
		header("Content-Type: application/octet-stream; name=\"$filename\"");
		header("Content-Disposition: attachment; filename=\"$filename\"");
	}

	//    if ($isIE && !$isIE6) {
	//        header("Content-Type: application/download; name=\"$filename\"");
	//    } else {
	//        header("Content-Type: application/octet-stream; name=\"$filename\"");
	//    }

	$rfc822 = false;

	if ($mime == "message-rfc822") $rfc822 = true;

	$file = $pop->fetchbody($mail, $part, $part, false, $rfc822);
	$file = NVLL_IMAP::decode($file, $transfer);
	$pop->close();

	header('Content-Length: ' . strlen($file));
	echo ($file);
} catch (Exception $ex) {
	//TODO: Show error without NVLL_Exception!
	$ev = new NVLL_Exception($ex->getMessage());
	require dirname(__FILE__) . '/html/header.php';
	require dirname(__FILE__) . '/html/error.php';
	require dirname(__FILE__) . '/html/footer.php';
	return;
}
