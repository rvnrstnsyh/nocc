<?php

/**
 * File for viewing the images
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

require_once './common.php';

try {
    $pop = new NVLL_IMAP();
    $mail = $_REQUEST['mail'];
    $num = $_REQUEST['num'];
    $transfer = $_REQUEST['transfer'];
    $mime = $_REQUEST['mime'];
    $img = $pop->fetchbody($mail, $num);
    $img = NVLL_IMAP::decode(removeUnicodeBOM($img), $transfer);
    $pop->close();

    if (preg_match("/^image/", $mime)) {
        header('Content-type: ' . $mime);
    } else {
        header('Content-type: image/' . $mime);
    }
    echo $img;
} catch (Exception $ex) {
    //TODO: Show error without NVLL_Exception!
    $ev = new NVLL_Exception($ex->getMessage());
    require './html/header.php';
    require './html/error.php';
    require './html/footer.php';
    return;
}
