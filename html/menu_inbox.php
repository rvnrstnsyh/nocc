<?php
if (!isset($conf->loaded)) die('Hacking attempt');

$service = NVLL_Request::getStringValue('service');
$selected = 0;

switch ($service) {
  case '':
  case 'login':
  case 'cookie':
    $selected = 1;
    $line = '<a href="api.php?' . NVLL_Session::getUrlGetSession() . '&service=compose">' . $html_new_msg . '</a>';
    break;
  case 'compose':
    $selected = 2;
    $line = '<span>' . $html_new_msg . '</span>';
    break;
  case 'reply':
    $selected = 2;
    $line = '<span>' . $html_reply . '</span>';
    break;
  case 'reply_all':
    $selected = 2;
    $line = '<span>' . $html_reply_all . '</span>';
    break;
  case 'forward':
    $selected = 2;
    $line = '<span>' . $html_forward . '</span>';
    break;
  case 'managefolders':
    $selected = 3;
    $line = '<a href="api.php?' . NVLL_Session::getUrlGetSession() . '&service=compose">' . $html_new_msg . '</a>';
    break;
}
?>

<div class="mainmenu">
  <ul>
    <?php if ($selected != 1 && $user_prefs->getUseInboxFolder()) { ?>
      <li><a href="api.php?<?php echo NVLL_Session::getUrlGetSession(); ?>"><?php echo convertLang2Html($html_back); ?></a></li>
    <?php } ?>
    <?php if ($selected == 1) echo '<li class="selected">';
    else echo '<li>'; ?>
    <?php
    $jumpInbox = "";
    if ($user_prefs->getUseInboxFolder() && strlen($user_prefs->getInboxFolderName()) > 0) {
      $jumpInbox = "&folder=" . $user_prefs->getInboxFolderName();
    }
    ?>
    <a href="api.php?<?php echo NVLL_Session::getUrlGetSession() . $jumpInbox; ?>"><?php echo convertLang2Html($html_inbox); ?><span class="inbox_changed" style="display:none;color:darkred;">!</span></a>
    </li>
    <?php if ($selected == 2) echo '<li class="selected">';
    else echo '<li>'; ?>
    <?php echo $line ?>
    </li>
    <?php if ($_SESSION['is_imap']) { ?>
      <?php if ($selected == 3) echo '<li class="selected">';
      else echo '<li>'; ?>
      <a href="api.php?<?php echo NVLL_Session::getUrlGetSession(); ?>&service=managefolders" title="<?php echo convertLang2Html($html_manage_folders_link); ?>"><?php echo convertLang2Html($html_folders); ?></a>
      </li>
    <?php } ?>
    <?php if ($conf->prefs_dir && isset($conf->contact_number_max) && $conf->contact_number_max != 0) { ?>
      <li>
        <a href="javascript:void(0);" onclick="window.open('contacts_manager.php?<?php echo NVLL_Session::getUrlGetSession(); ?>&<?php echo NVLL_Session::getUrlQuery(); ?>','','scrollbars=yes, resizable=yes, width=700, height=350')"><?php echo i18n_message($html_contacts, ''); ?></a>
      </li>
    <?php } ?>
    <?php if (isset($_GET['successfulsend']) && $_GET['successfulsend']) { ?>
      <li class="success-message">
        <?php echo convertLang2Html($html_send_confirmed); ?>
      </li>
    <?php } ?>
  </ul>
</div>