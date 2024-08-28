<?php

/**
 * This file just delete the selected message(s)
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

require_once './common.php';
require_once './classes/NVLL_SMTP.php';

$ev = null;

try {
    $pop = new NVLL_IMAP();
} catch (Exception $ex) {
    //TODO: Show error without NVLL_Exception!
    $ev = new NVLL_Exception($ex->getMessage());
    require './html/header.php';
    require './html/error.php';
    require './html/footer.php';
    return;
}

$num_messages = $pop->num_msg();
$url_session = NVLL_Session::getUrlGetSession();
$url = "action.php?{$url_session}";
$user_prefs = NVLL_Session::getUserPrefs();
$referrer = $_SERVER['HTTP_REFERER'] ?? '';
// Work out folder and target_folder
$folder = $_SESSION['nvll_folder'];
$target_folder = $_REQUEST['target_folder'] ?? '';
$bottom_target_folder = $_REQUEST['bottom_target_folder'] ?? '';

if (isset($_REQUEST['only_one'])) {
    $mail = $_REQUEST['mail'];
    $mark_mode = $_REQUEST['mark_mode'] ?? '';
    $verbose = '0';
    $display_images = '0';

    if ($referrer) {
        $parsedUrl = parse_url($referrer);
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $queryParams);
            $verbose = $queryParams['verbose'] ?? '0';
            $display_images = $queryParams['display_images'] ?? '0';
        }
    }

    if (isset($_REQUEST['move_mode']) && $target_folder !== $folder) {
        $pop->mail_move($mail, $target_folder);
        $mail--;
    } elseif (isset($_REQUEST['copy_mode']) && $target_folder !== $folder) {
        $pop->mail_copy($mail, $target_folder);
    } elseif (isset($_REQUEST['mark_mode'])) {
        switch ($mark_mode) {
            case 'unread':
                $pop->mail_mark_unread($mail);
                break;
            case 'flag':
                $pop->mail_mark_flag($mail);
                break;
            case 'unflag':
                $pop->mail_mark_unflag($mail);
                break;
        }
    } elseif (isset($_REQUEST['delete_mode'])) {
        $_SESSION['message_deleted'] = "true";
        $target_folder = $_SESSION['imap_namespace'] . $user_prefs->getTrashFolderName();

        if ($pop->is_imap() && $user_prefs->getUseTrashFolder() && $_SESSION['nvll_folder'] !== $target_folder) {
            $pop->mail_move($mail, $target_folder);
        } else {
            $pop->delete($mail);
        }

        $mail = max(0, $mail - 1);
    }

    $url = $mark_mode === 'unread' || $mail === 0
        ? "action.php?{$url_session}"
        : "action.php?{$url_session}&action=aff_mail&mail={$mail}&verbose={$verbose}&display_images={$display_images}";
} else {
    $msg_to_forward = '';
    for ($i = $num_messages; $i >= 1; $i--) {
        if (isset($_REQUEST['msg-' . $i])) {
            if (isset($_REQUEST['move_mode']) && $target_folder != $folder) {
                $pop->mail_move($i, $target_folder);
            }

            if (isset($_REQUEST['bottom_move_mode']) && $bottom_target_folder != $folder) {
                $pop->mail_move($i, $bottom_target_folder);
            }

            if (isset($_REQUEST['copy_mode']) && $target_folder != $folder) {
                $pop->mail_copy($i, $target_folder);
            }

            if (isset($_REQUEST['bottom_copy_mode']) && $bottom_target_folder != $folder) {
                $pop->mail_copy($i, $bottom_target_folder);
            }

            if (isset($_REQUEST['forward_mode']) || isset($_REQUEST['bottom_forward_mode'])) {
                $msg_to_forward .= '$' . $i;
            }

            if (isset($_REQUEST['delete_mode']) || isset($_REQUEST['bottom_delete_mode'])) {
                $_SESSION['message_deleted'] = "true";
                $target_folder = $_SESSION['imap_namespace'] . $user_prefs->getTrashFolderName();

                if ($pop->is_imap() && $user_prefs->getUseTrashFolder() && $_SESSION['nvll_folder'] != $target_folder) {
                    $pop->mail_move($i, $target_folder);
                } else {
                    $pop->delete($i);
                }
            }

            $mark_actions = [
                'set_flag' => $_REQUEST['mark_mode'] ?? '',
                'bottom_set_flag' => $_REQUEST['bottom_mark_mode'] ?? ''
            ];

            foreach ($mark_actions as $flag_type => $mode) {
                if (isset($_REQUEST[$flag_type])) {
                    switch ($mode) {
                        case 'read':
                            $pop->mail_mark_read($i);
                            break;
                        case 'unread':
                            $pop->mail_mark_unread($i);
                            break;
                        case 'flag':
                            $pop->mail_mark_flag($i);
                            break;
                        case 'unflag':
                            $pop->mail_mark_unflag($i);
                            break;
                    }
                }
            }
        }
    }

    if ($msg_to_forward != '') {
        $msg_to_forward = substr($msg_to_forward, 1);
        $url = "action.php?{$url_session}&action=forward&mail={$msg_to_forward}";
    }
}

$pop->close();

if (NVLL_Exception::isException($ev)) {
    require './html/header.php';
    require './html/error.php';
    require './html/footer.php';
    return;
}

// Redirect user to index
// TODO: redirect user to next message
require_once './utils/proxy.php';

header('Location: ' . $conf->base_url . $url);
