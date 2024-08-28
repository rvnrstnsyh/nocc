<?php if (!isset($conf->loaded)) die('Hacking attempt'); ?>
<?php if (isset($list_of_folders) && $list_of_folders != '') { ?>
  <div id="inboxStatus">
    <?php echo convertLang2Html($html_new_msg_in) . $list_of_folders ?>
  </div>
<?php } ?>