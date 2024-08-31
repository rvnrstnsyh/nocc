<?php
if (!isset($conf->loaded)) die('Hacking attempt');
if ($pop->is_imap() && $conf->prefs_dir) {
  $service = NVLL_Request::getStringValue('service');
  $selected = 0;

  switch ($service) {
    case '':
    case 'setprefs':
      $selected = 1;
      break;
    case 'filters':
      $selected = 2;
      break;
  } ?>

  <div class="submenu">
    <ul>
      <?php if ($selected == 1) echo '<li class="selected">';
      else echo '<li>'; ?>
      <a href="api.php?<?php echo NVLL_Session::getUrlGetSession(); ?>&service=setprefs"><?php echo convertLang2Html($html_preferences) ?></a>
      </li>
      <?php if ($selected == 2) echo '<li class="selected">';
      else echo '<li>'; ?>
      <a href="api.php?<?php echo NVLL_Session::getUrlGetSession(); ?>&service=filters"><?php echo convertLang2Html($html_manage_filters_link) ?></a>
      </li>
    </ul>
  </div>
<?php } ?>