<?php
if (!isset($conf->loaded)) die('Hacking attempt');

$url_session = NVLL_Session::getUrlGetSession();
$display_images = (!empty($_REQUEST['display_images']) && $_REQUEST['display_images'] == 1) ? '1' : '0';
$msgnum = $content['msgnum'];
?>

<div class="submenu">
  <ul>
    <li>
      <a href="action.php?<?php echo $url_session ?>&action=compose"><?php echo convertLang2Html($html_new_msg) ?></a>
    </li>
    <li>
      <a href="action.php?<?php echo $url_session ?>&action=reply&amp;mail=<?php echo $msgnum ?>&amp;display_images=<?php echo $display_images ?>"><?php echo convertLang2Html($html_reply) ?></a>
    </li>
    <li>
      <a href="action.php?<?php echo $url_session ?>&action=reply_all&amp;mail=<?php echo $msgnum ?>&amp;display_images=<?php echo $display_images ?>"><?php echo convertLang2Html($html_reply_all) ?></a>
    </li>
    <li>
      <a href="delete.php?<?php echo $url_session ?>&mark_mode=unseen&amp;mail=<?php echo $msgnum ?>&amp;only_one=1"><?php echo convertLang2Html($html_unseen) ?></a>
    </li>
    <li>
      <a href="delete.php?<?php echo $url_session ?>&mark_mode=flag&amp;mail=<?php echo $msgnum ?>&amp;only_one=1"><?php echo convertLang2Html($html_flag) ?></a>
    </li>
    <li>
      <a href="delete.php?<?php echo $url_session ?>&mark_mode=unflag&amp;mail=<?php echo $msgnum ?>&amp;only_one=1"><?php echo convertLang2Html($html_unflag) ?></a>
    </li>
    <li>
      <a href="action.php?<?php echo $url_session ?>&action=forward&amp;mail=<?php echo $msgnum ?>"><?php echo convertLang2Html($html_forward) ?></a>
    </li>
    <li>
      <?php if ($pop->is_imap() && $pop->get_folder_count() > 1) {
        $html_target_select = $pop->html_folder_select('target_folder', '');
      ?>
        <form action="delete.php" method="POST">
          <input type="hidden" name="mail" value="<?php echo $msgnum; ?>" />
          <input type="hidden" name="only_one" value="1" />
          <input type="submit" class="button" name="move_mode" value="<?php echo convertLang2Html($html_move); ?>" />
          <input type="submit" class="button" name="copy_mode" value="<?php echo convertLang2Html($html_copy); ?>" />
          <?php echo $html_target_select; ?>
        </form>
      <?php } ?>
    </li>
    <li>
      <a href="down_mail.php?<?php echo $url_session ?>&mail=<?php echo $msgnum ?>"><?php echo convertLang2Html($html_down_mail) ?></a>
    </li>
    <li>
      <a href="delete.php?<?php echo $url_session ?>&delete_mode=1&amp;mail=<?php echo $msgnum ?>&amp;only_one=1" onclick="return confirm('<?php echo $html_del_msg ?>');"><?php echo convertLang2Html($html_delete) ?></a>
    </li>
  </ul>
</div>