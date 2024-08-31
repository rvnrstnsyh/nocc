<?php

/**
 * File for RSS stream
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

require_once './classes/NVLL_Session.php';
require_once './classes/NVLL_UserPrefs.php';

$from_rss = true;
$_REQUEST['_vmbox'] = "RSS";

NVLL_Session::start();

$_SESSION['nvll_user'] = base64_decode($_REQUEST['nvll_user']);
$_SESSION['nvll_passwd'] = base64_decode($_REQUEST['nvll_passwd']);
$_SESSION['nvll_login'] = base64_decode($_REQUEST['nvll_login']);
$_SESSION['nvll_lang'] = base64_decode($_REQUEST['nvll_lang']);
$_SESSION['nvll_smtp_server'] = base64_decode($_REQUEST['nvll_smtp_server']);
$_SESSION['nvll_smtp_port'] = base64_decode($_REQUEST['nvll_smtp_port']);
$_SESSION['nvll_theme'] = base64_decode($_REQUEST['nvll_theme']);
$_SESSION['nvll_domain'] = base64_decode($_REQUEST['nvll_domain']);
$_SESSION['nvll_domain_index'] = base64_decode($_REQUEST['nvll_domain_index']);
$_SESSION['imap_namespace'] = base64_decode($_REQUEST['imap_namespace']);
$_SESSION['nvll_servr'] = base64_decode($_REQUEST['nvll_servr']);
$_SESSION['nvll_folder'] = base64_decode($_REQUEST['nvll_folder']);
$_SESSION['smtp_auth'] = base64_decode($_REQUEST['smtp_auth']);
$_SESSION['ucb_pop_server'] = base64_decode($_REQUEST['ucb_pop_server']);
$_SESSION['quota_enable'] = base64_decode($_REQUEST['quota_enable']);
$_SESSION['quota_type'] = base64_decode($_REQUEST['quota_type']);

//
//   RSS-QUESTION
//
//$_SESSION['rss'] = true;

if (!NVLL_Session::existsUserPrefs()) {
  //TODO: Move to NVLL_Session::loadUserPrefs()?
  $user_key = NVLL_Session::getUserKey();
  NVLL_Session::setUserPrefs(NVLL_UserPrefs::read($user_key, $ev));
  if (NVLL_Exception::isException($ev)) {
    echo "<p>User prefs error ($user_key): " . $ev->getMessage() . "</p>";
    exit(1);
  }
}

require_once './common.php';
require_once './classes/NVLL_IMAP.php';
require_once './classes/NVLL_RSSFeed.php';

if (! isRssAllowed()) {
  exit;
}

try {
  $pop = new NVLL_IMAP();
} catch (Exception $ex) {
  //TODO: Show error without NVLL_Exception!
  $ev = new NVLL_Exception($ex->getMessage());
  require './html/error.php';
  exit;
}

$tab_mail = array();
if ($pop->num_msg() > 0) {
  //TODO: Remove later try/catch block!
  try {
    $tab_mail = inbox($pop, 0);
  } catch (Exception $ex) {
    $ev = new NVLL_Exception($ex->getMessage());
  }
}
$tab_mail_bak = $tab_mail;

if (NVLL_Exception::isException($ev)) {
  require './html/error.php';
  exit;
}

$rssfeed = new NVLL_RSSFeed();
$rssfeed->setTitle('Non-Violable Liberty Layers | Webmail - ' . $_SESSION['nvll_folder'] . ' ' . $_SESSION['nvll_login']);
$rssfeed->setDescription('Your mailbox');
$rssfeed->setLink($conf->base_url);
while ($tmp = array_shift($tab_mail)) { //for all mails...
  try {
    $content = aff_mail($pop, $tmp['number'], false);

    $mail_summery = '';
    if ($tmp['attach'] == true) { //if has attachments...
      $mail_summery .= '<img src="' . $conf->base_url . 'themes/' . $_SESSION['nvll_theme'] . '/img/attach.png" alt="" />';
    }
    $mail_summery .= $html_size . ': ' . $tmp['size'] . ' ' . $html_kb . '<br /><br />';

    $rssDescription = $mail_summery . substr(strip_tags($content['body'], '<br />'), 0, 200) . '&hellip;';

    $rssContent = $mail_summery . $content['body'];

    $rssfeeditem = new NVLL_RSSFeed_Item();
    $rssfeeditem->setTitle(htmlspecialchars($tmp['subject'], ENT_COMPAT | ENT_SUBSTITUTE));
    $rssfeeditem->setDescription($rssDescription);
    $rssfeeditem->setTimestamp($content['timestamp']);
    $rssfeeditem->setContent($rssContent);
    $rssfeeditem->setLink($conf->base_url . 'api.php?' . NVLL_Session::getUrlGetSession() . '&amp;service=aff_mail&amp;mail=' . $tmp['number'] . '&amp;verbose=0&amp;rss=true');
    $rssfeeditem->setCreator(htmlspecialchars($tmp['from'], ENT_COMPAT | ENT_SUBSTITUTE));
    $rssfeed->addItem($rssfeeditem);
  } catch (Exception $ex) {
    //Do nothing!
  }
}
$rssfeed->sendToBrowser();
