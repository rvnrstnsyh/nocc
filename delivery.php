<?php

/**
 * This file send a message
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

require_once './common.php';

$mail_from = get_default_from_address();
$domain_index = $_SESSION['nvll_domain_index'];
$domain_allow_change = isset($conf->domains[$domain_index]->allow_address_change) ? $conf->domains[$domain_index]->allow_address_change : null;
$global_allow_change = $conf->allow_address_change;
$can_change_address = ($domain_allow_change !== null) ? $domain_allow_change : $global_allow_change;

if ($can_change_address) $mail_from = NVLL_Request::getStringValue('mail_from');

$mail_to = NVLL_Request::getStringValue('mail_to');
$mail_cc = NVLL_Request::getStringValue('mail_cc');
$mail_bcc = NVLL_Request::getStringValue('mail_bcc');
$mail_subject = NVLL_Request::getStringValue('mail_subject');
$mail_body = NVLL_Request::getStringValue('mail_body');

if (strlen(NVLL_Request::getStringValue('nvll_attach_array')) > 0) {
    $mail_attachment = unserialize(base64_decode(NVLL_Request::getStringValue('nvll_attach_array')));
    if (!is_array($mail_attachment)) unset($mail_attachment);
}

$mail_receipt = isset($_REQUEST['receipt']);
$mail_priority = NVLL_Request::getStringValue('priority');
$domain_index = NVLL_Request::getStringValue('saved_domain_index');
$send_backup = array();
$send_backup['nvll_domain_index'] = $domain_index;
$send_backup['mail_from'] = $mail_from;
$send_backup['mail_to'] = $mail_to;
$send_backup['mail_cc'] = $mail_cc;
$send_backup['mail_bcc'] = $mail_bcc;
$send_backup['mail_subject'] = $mail_subject;
$send_backup['mail_body'] = $mail_body;

if (isset($mail_attachment)) $send_backup['mail_attachment'] = $mail_attachment;

$send_backup['mail_receipt'] = $mail_receipt;
$send_backup['mail_priority'] = $mail_priority;
$_SESSION['send_backup'] = $send_backup;

if (!isset($_SESSION['nvll_loggedin'])) {
    require_once './utils/proxy.php';
    header('Location: ' . $conf->base_url . 'logout.php?' . NVLL_Session::getUrlGetSession());
    return;
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    clear_attachments();
    require_once './utils/proxy.php';
    header('Location: ' . $conf->base_url . 'api.php?' . NVLL_Session::getUrlGetSession());
    return;
}

require_once './classes/NVLL_MIME.php';
require_once './classes/NVLL_SMTP.php';

if (ini_get("file_uploads") && isset($_FILES['mail_attachment'])) $mail_attachment = $_FILES['mail_attachment'];
if (NVLL_Session::getSendHtmlMail()) $mail_body = '<html><head></head><body>' . $mail_body . '</body></html>';

switch ($_REQUEST['sendaction']) {
    case unhtmlentities($html_attach):
        // Counting the attachments number in the array
        if (!isset($_SESSION['nvll_attach_array'])) $_SESSION['nvll_attach_array'] = array();

        $attach_array = $_SESSION['nvll_attach_array'];
        // Check if "$conf->tmpdir" exists
        if (!is_dir($conf->tmpdir)) {
            $ev = new NVLL_Exception($html_tmp_directory_doesnt_exist);
            require './html/header.php';
            require './html/error.php';
            require './html/footer.php';
            break;
        }

        if (!isset($_FILES['mail_attachment']) || empty($_FILES['mail_attachment']['tmp_name']) || !file_exists($_FILES['mail_attachment']['tmp_name'])) {
            $ev = new NVLL_Exception($html_tmp_file_doesnt_exist);
            require './html/header.php';
            require './html/error.php';
            require './html/footer.php';
            break;
        }

        $tmpFile = $conf->tmpdir . '/' . basename($mail_attachment['tmp_name'] . NVLL_Encoding::base64url_encode(random_bytes(32)) . '.att');
        // Adding the new file to the array
        if (@move_uploaded_file($mail_attachment['tmp_name'], $tmpFile)) {
            $attachedFile = new NVLL_AttachedFile($tmpFile, basename($mail_attachment['name']), $mail_attachment['size'], $mail_attachment['type']);
            $attach_array[] = $attachedFile;
        } else {
            $ev = new NVLL_Exception($html_file_upload_attack);
            require './html/header.php';
            require './html/error.php';
            require './html/footer.php';
            break;
        }

        // Registering the attachments array into the session
        $_SESSION['nvll_attach_array'] = $attach_array;
        $_SESSION['send_backup']['mail_attachment'] = $attach_array;
        // Displaying the sending form with the new attachments array
        //header("Content-type: text/html; Charset=UTF-8");
        require './html/header.php';
        require './html/menu_inbox.php';
        require './html/compose.php';
        require './html/menu_inbox.php';
        require './html/footer.php';
        break;

    case unhtmlentities($html_cancel):
        $mail_to = "";
        $mail_cc = "";
        $mail_bcc = "";
        $mail_subject = "";
        $mail_body = "";
        $mail_receipt = "";
        $mail_priority = "";

        if (isset($_SESSION['send_backup']) && $_SESSION['nvll_domain_index'] == $_SESSION['send_backup']['nvll_domain_index']) unset($_SESSION['send_backup']);

        clear_attachments();
        require_once './utils/proxy.php';
        header('Location: ' . $conf->base_url . 'api.php?' . NVLL_Session::getUrlGetSession());
        //require './html/header.php';
        //require './html/menu_inbox.php';
        //require './html/compose.php';
        //require './html/menu_inbox.php';
        //require './html/footer.php';

        break;
    case unhtmlentities($html_send):
        $mail = new NVLL_MIME();
        $mail->crlf = $conf->crlf;
        $mail->smtp_server = $_SESSION['nvll_smtp_server'];
        $mail->smtp_port = $_SESSION['nvll_smtp_port'];
        //TODO: No nice to use $mail->from as temp variable!
        $mail->from = cut_address(trim($mail_from), 'UTF-8');
        $mail->from = $mail->from[0];
        $mail->priority = $_REQUEST['priority'];
        $mail->receipt = isset($_REQUEST['receipt']);
        $mail->headers = '';

        if (!empty($_REQUEST['mail_messageid'])) {
            $mail->headers .= 'References: ' . urldecode($_REQUEST['mail_messageid']) . $mail->crlf;
            $mail->headers .= 'In-Reply-To: ' . urldecode($_REQUEST['mail_messageid']) . $mail->crlf;
        }

        $mail->headers .= 'User-Agent: ' . $conf->nvll_name . ' <' . $conf->nvll_url . '>';
        $mail->to = cut_address(trim($mail_to), 'UTF-8');
        $mail->cc = cut_address(trim($mail_cc), 'UTF-8');
        $mail->bcc = cut_address(trim($mail_bcc), 'UTF-8');
        $user_prefs = NVLL_Session::getUserPrefs();

        if ($user_prefs->getBccSelf()) array_unshift($mail->bcc, $mail->from);
        if ($user_prefs->getCollect() == 1 || $user_prefs->getCollect() == 3) {
            require_once './classes/NVLL_Contacts.php';

            $path = $conf->prefs_dir . '/' . preg_replace("/(\\\|\/)/", "_", NVLL_Session::getUserKey()) . '.contacts';
            $contacts_object = new NVLL_Contacts();
            $all_to = $mail_to . ";" . $mail_cc . ";" . $mail_bcc;
            $contacts = $contacts_object->add_contact($path, $all_to);

            if (count($contacts) <= $conf->contact_number_max) $contacts_object->saveList($path, $contacts, $conf, $ev);
            //ignore exception as emails should be send anyways
        }

        if ($mail_subject != '') {
            $mail_subject = trim($mail_subject);
            $mail->subject = '=?UTF-8?B?' . base64_encode($mail_subject) . '?=';
        }

        // Append advertisement tag, if set
        // Wrap outgoing message if needed
        $wrap_msg = $user_prefs->getWrapMessages();
        if ($mail_body != '') {
            if ($wrap_msg) {
                $mail->body = wrap_outgoing_msg($mail_body, $wrap_msg, $mail->crlf);
            } else {
                $mail->body = $mail_body;
            }
        }

        if (isset($conf->ad)) {
            $ad = $conf->ad;
            $crlf = $mail->crlf;

            if ($user_prefs->getSendHtmlMail()) {
                $crlf = "<br />";
                $ad = preg_replace("/\r\n/", "\n", $ad);
                $ad = preg_replace("/\r/", "\n", $ad);
                $ad = preg_replace("/\n/", $crlf, $ad);
            }

            if ($mail_body != '') {
                $mail->body = $mail->body . $crlf . $crlf . $ad;
            } else {
                $mail->body = $ad;
            }
        }
        // Handle dots for SMTP protocol
        $mail->body = escape_dots($mail->body);
        // Getting the attachments
        if (isset($_SESSION['nvll_attach_array'])) {
            $attach_array = $_SESSION['nvll_attach_array'];
            for ($i = 0; $i < count($attach_array); $i++) {
                $attachedFile = $attach_array[$i];
                // If the temporary file exists, attach it
                if ($attachedFile->exists()) {
                    $content = $attachedFile->getContent();
                    // add it to the message, by default it is encoded in base64
                    $mail->add_attachment($content, quoted_printable_decode($attachedFile->getName()), $attachedFile->getMimeType(), 'base64', '');
                }
            }
        }
        // Add original message as attachment?
        if (isset($_REQUEST['forward_msgnum']) && $_REQUEST['forward_msgnum'] != "") {
            try {
                $pop = new NVLL_IMAP();
            } catch (Exception $ex) {
                //TODO: Show error without NVLL_Exception!
                $ev = new NVLL_Exception($ex->getMessage());
                require './html/header.php';
                require './html/error.php';
                require './html/footer.php';
                break;
            }

            $mail_list = explode('$', $_REQUEST['forward_msgnum']);
            for ($msg_num = 0; $msg_num < count($mail_list); $msg_num++) {
                $forward_msgnum = $mail_list[$msg_num];
                $origmsg = $pop->fetchmessage($forward_msgnum);
                // Attach it
                //TODO: Move filename to a own variable!
                if (count($mail_list) == 1) {
                    $mail->add_attachment($origmsg, 'orig_msg.eml', 'message/rfc822', '', '');
                } else {
                    $mail->add_attachment($origmsg, 'orig_msg_' . $msg_num . '.eml', 'message/rfc822', '', '');
                }
            }
        }

        if (!isset($_SESSION['last_send'])) {
            $ev = $mail->send($conf);
            $_SESSION['last_send'] = time();
        } else if ($_SESSION['last_send'] + $conf->send_delay < time()) {
            $ev = $mail->send($conf);
            $_SESSION['last_send'] = time();
        } else {
            $ev = new NVLL_Exception(i18n_message($lang_err_send_delay, $conf->send_delay));
        }

        header("Content-type: text/html; Charset=UTF-8");
        if (NVLL_Exception::isException($ev)) {
            // Error while sending the message, display an error message
            require './html/header.php';
            require './html/menu_inbox.php';
            require './html/send_error.php';
            require './html/menu_inbox.php';
            require './html/footer.php';
        } else {
            if (isset($_SESSION['nvll_attach_array'])) {
                $attach_array = $_SESSION['nvll_attach_array'];
                for ($i = 0; $i < count($attach_array); $i++) {
                    $attachedFile = $attach_array[$i];
                    if ($attachedFile->exists()) {
                        $attachedFile->delete();
                    }
                }
            }
            if (isset($_SESSION['send_backup']) && $_SESSION['nvll_domain_index'] == $_SESSION['send_backup']['nvll_domain_index']) unset($_SESSION['send_backup']);
            clear_attachments();
            // Redirect user to inbox
            require_once './utils/proxy.php';
            header("Location: " . $conf->base_url . "api.php?" . NVLL_Session::getUrlGetSession() . '&accepted_for_delivery=true');
        }
        break;
    case unhtmlentities($html_attach_delete):
        // Rebuilding the attachments array with only the files the user wants to keep
        $tmp_array = array();
        $attach_array = $_SESSION['nvll_attach_array'];

        for ($i = 0; $i < count($attach_array); $i++) {
            $attachedFile = $attach_array[$i];
            if (!isset($_REQUEST['file-' . $i])) {
                $tmp_array[] = $attachedFile;
            } else {
                $attachedFile->delete();
            }
        }

        // Registering the new attachments array into the session
        $_SESSION['nvll_attach_array'] = $tmp_array;
        $_SESSION['send_backup']['mail_attachment'] = $attach_array;

        // Displaying the sending form with the new attachment array 
        header("Content-type: text/html; Charset=UTF-8");
        require './html/header.php';
        require './html/menu_inbox.php';
        require './html/compose.php';
        require './html/menu_inbox.php';
        require './html/footer.php';
        break;
    default:
        // Nothing was set in the sendaction (e.g. no javascript enabled)
        header("Content-type: text/html; Charset=UTF-8");
        $ev = new NVLL_Exception($html_no_sendaction);
        require './html/header.php';
        require './html/menu_inbox.php';
        require './html/send_error.php';
        require './html/menu_inbox.php';
        require './html/footer.php';
        break;
}
