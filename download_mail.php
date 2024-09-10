<?php

/**
 * File for downloading the mail as attachment
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

if (!isset($HTTP_USER_AGENT)) $HTTP_USER_AGENT = $_SERVER['HTTP_USER_AGENT'];

require_once dirname(__FILE__) .  '/classes/NVLL_IMAP.php';

require_once dirname(__FILE__) .  '/common.php';

$mail = $_REQUEST['mail'];

try {
    $pop = new NVLL_IMAP();
    $mailheaderinfo = $pop->headerinfo($mail, $ev);
    $subject = $mailheaderinfo->getSubject();
    $file = $pop->fetchmessage($mail);
    $pop->close();
    $filename = ($subject) ? preg_replace('{[\[\]\\/:\*\?"<>\|;]}', '_', str_replace('&nbsp;', ' ', $subject)) . ".eml" : "no_subject.eml";
    $isIE = $isIE6 = 0;

    // Set correct http headers.
    // Thanks to Squirrelmail folks :-)
    if (strstr($HTTP_USER_AGENT, 'compatible; MSIE ') !== false && strstr($HTTP_USER_AGENT, 'Opera') === false) $isIE = 1;
    if (strstr($HTTP_USER_AGENT, 'compatible; MSIE 6') !== false && strstr($HTTP_USER_AGENT, 'Opera') === false) $isIE6 = 1;
    if ($isIE) {
        $filename = rawurlencode($filename);
        header("Pragma: public");
        header("Cache-Control: no-store, max-age=0, no-cache, must-revalidate"); // HTTP/1.1
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Cache-Control: private");
        //set the inline header for IE, we'll add the attachment header later if we need it
        header("Content-Disposition: inline; filename=$filename");
    }

    header("Content-Type: application/octet-stream; name=\"$filename\"");
    header("Content-Disposition: attachment; filename=\"$filename\"");

    if ($isIE && !$isIE6) {
        header("Content-Type: application/download; name=\"$filename\"");
    } else {
        header("Content-Type: application/octet-stream; name=\"$filename\"");
    }

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
