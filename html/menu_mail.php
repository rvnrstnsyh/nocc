<?php if (!isset($conf->loaded)) die('Hacking attempt'); ?>

<div class="mainmenu">
  <ul>
    <?php if ($user_prefs->getUseInboxFolder()) { ?>
      <li><a href="api.php?<?php echo NVLL_Session::getUrlGetSession(); ?>"><?php echo convertLang2Html($html_back); ?></a></li>
    <?php } ?>
    <li>
      <?php
      $jumpInbox = "";
      if (
        $user_prefs->getUseInboxFolder()
        && strlen($user_prefs->getInboxFolderName()) > 0
      ) {
        $jumpInbox = "&folder=" . $user_prefs->getInboxFolderName();
      }
      ?>
    <li>
      <a href="api.php?<?php echo NVLL_Session::getUrlGetSession() . $jumpInbox; ?>"><?php echo convertLang2Html($html_inbox); ?><span class="inbox_changed" style="display:none;color:darkred;">!</span></a>
    </li>
    <li class="selected">
      <span><?php echo convertLang2Html($html_msg) ?></span>
    </li>
    <?php if ($_SESSION['is_imap']) { ?>
      <li>
        <a href="api.php?<?php echo NVLL_Session::getUrlGetSession(); ?>&service=managefolders" title="<?php echo convertLang2Html($html_manage_folders_link); ?>"><?php echo convertLang2Html($html_folders); ?></a>
      </li>
    <?php } ?>
    <?php if ($conf->prefs_dir && isset($conf->contact_number_max) && $conf->contact_number_max != 0) { ?>
      <li>
        <a href="javascript:void(0);" onclick="window.open('contacts_manager.php?<?php echo NVLL_Session::getUrlGetSession(); ?>&<?php echo NVLL_Session::getUrlQuery(); ?>','','scrollbars=yes,resizable=yes,width=900,height=400')"><?php echo i18n_message($html_contacts, ''); ?></a>
      </li>
    <?php } ?>
  </ul>
</div>