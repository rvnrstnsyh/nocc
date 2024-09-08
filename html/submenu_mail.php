<?php
if (!isset($conf->loaded)) die('Hacking attempt');

$url_session = NVLL_Session::getUrlGetSession();
$display_images = (isset($_REQUEST['display_images']) && $_REQUEST['display_images'] == 1) ? '1' : '0';
$msgnum = $content['msgnum'];
$original = (isset($_REQUEST['original']) && $_REQUEST['original'] == 1) ? '1' : '0';
$verbose = (isset($_REQUEST['verbose']) && $_REQUEST['verbose'] == 1) ? '1' : '0';
$as_html = (isset($_REQUEST['as_html']) && $_REQUEST['as_html'] == 1) ? '1' : '0';

$apiBaseUrl = 'api.php?' . $url_session . '&';
$deleteBaseUrl = 'delete.php?' . $url_session . '&';
?>

<div class="submenu">
  <ul>
    <li>
      <a href="<?php echo $apiBaseUrl . NVLL_Request::Params(['service' => 'compose']) ?>">
        <?php echo convertLang2Html($html_new_msg) ?>
      </a>
    </li>
    <li>
      <a href="<?php echo $apiBaseUrl . NVLL_Request::Params(['service' => 'reply', 'mail' => $msgnum, 'display_images' => $display_images]) ?>">
        <?php echo convertLang2Html($html_reply) ?>
      </a>
    </li>
    <li>
      <a href="<?php echo $apiBaseUrl . NVLL_Request::Params(['service' => 'reply_all', 'mail' => $msgnum, 'display_images' => $display_images]) ?>">
        <?php echo convertLang2Html($html_reply_all) ?>
      </a>
    </li>
    <li>
      <a href="<?php echo $deleteBaseUrl . NVLL_Request::Params(['mark_mode' => 'unseen', 'mail' => $msgnum, 'only_one' => '1']) ?>">
        <?php echo convertLang2Html($html_unseen) ?>
      </a>
    </li>
    <li>
      <a href="<?php echo $deleteBaseUrl . NVLL_Request::Params(['mark_mode' => 'flag', 'mail' => $msgnum, 'only_one' => '1']) ?>">
        <?php echo convertLang2Html($html_flag) ?>
      </a>
    </li>
    <li>
      <a href="<?php echo $deleteBaseUrl . NVLL_Request::Params(['mark_mode' => 'unflag', 'mail' => $msgnum, 'only_one' => '1']) ?>">
        <?php echo convertLang2Html($html_unflag) ?>
      </a>
    </li>
    <li>
      <a href="<?php echo $apiBaseUrl . NVLL_Request::Params(['service' => 'forward', 'mail' => $msgnum]) ?>">
        <?php echo convertLang2Html($html_forward) ?>
      </a>
    </li>
    <li>
      <?php if ($pop->is_imap() && $pop->get_folder_count() > 1) {
        $html_target_select = $pop->html_folder_select('target_folder', '');
      ?>
        <form method="POST" action="delete.php">
          <input type="hidden" name="mail" value="<?php echo $msgnum; ?>" />
          <input type="hidden" name="only_one" value="1" />
          <input type="submit" class="button" name="move_mode" value="<?php echo convertLang2Html($html_move); ?>" />
          <input type="submit" class="button" name="copy_mode" value="<?php echo convertLang2Html($html_copy); ?>" />
          <?php echo $html_target_select; ?>
        </form>
      <?php } ?>
    </li>
    <?php
    $current_url = "api.php?$url_session&" . NVLL_Request::Params([
      'service' => 'aff_mail',
      'mail' => $msgnum,
      'verbose' => $verbose,
      'as_html' => $as_html,
      'display_images' => $display_images
    ]);
    if ($original === '0') { ?>
      <li>
        <a href="<?php echo $current_url ?>&<?php echo NVLL_Request::Params(['original' => '1']) ?>">
          <?php echo convertLang2Html($html_show_original) ?>
        </a>
      </li>
    <?php } else { ?>
      <li>
        <a href="<?php echo $current_url ?>">
          <?php echo convertLang2Html($html_show_formatted) ?>
        </a>
      </li>
    <?php } ?>
    <li>
      <a href="down_mail.php?<?php echo $url_session ?>&<?php echo NVLL_Request::Params(['mail' => $msgnum]) ?>">
        <?php echo convertLang2Html($html_down_mail) ?>
      </a>
    </li>
    <li>
      <a href="<?php echo $deleteBaseUrl . NVLL_Request::Params(['delete_mode' => '1', 'mail' => $msgnum, 'only_one' => '1']) ?>" onclick="return confirm('<?php echo $html_del_msg ?>');">
        <?php echo convertLang2Html($html_delete) ?>
      </a>
    </li>
  </ul>
</div>