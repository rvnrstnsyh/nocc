<?php
if (!isset($conf->loaded)) die('Hacking attempt');

$arrow = ($_SESSION['nvll_sortdir'] == 0) ? 'up' : 'down';
$new_sortdir = ($_SESSION['nvll_sortdir'] == 0) ? 1 : 0;
$skip = (isset($_REQUEST['skip'])) ? $_REQUEST['skip'] : '0';
$pages = $pop->get_page_count($num_msg);
$page_line = '';

if ($pages > 1) $page_line = get_page_nav($pages, $skip);

$folder_line = "";
$reapply_filters = '';

if ($pop->is_imap()) {
  if ($pop->get_folder_count() > 1) {
    $folder_line = "<form method=\"POST\" action=\"api.php?" . NVLL_Session::getUrlGetSession() . "\"><div><label for=\"folder\">$html_other_folders:</label>  \n";
    //$folder_line .= $pop->html_folder_select('folder', $_SESSION['nvll_folder']);
    $folder_line .= $pop->html_folder_select('folder', $_SESSION['goto_folder']);
    $folder_line .= "<input type=\"submit\" class=\"button\" name=\"submit\" value=\"$html_gotofolder\" />";
    $folder_line .= "</div></form>";
  }

  if ($_SESSION['nvll_folder'] == 'INBOX') {
    $reapply_filters = '<form method="POST" action="api.php?' . NVLL_Session::getUrlGetSession() . '">
      <input type="hidden" name="reapply_filters" value="1">
      <input class="button" type="submit" value="' . $html_reapply_filters . '">
    </form>';
  }
}
?>

<div class="messageSummary">
  <table>
    <tr>
      <td class="left">
        <?php if (isRssAllowed()) { ?>
          <a class="rss" href="<?php echo $rss_url ?>" title="RSS"><span class="rssText">(RSS)</span></a>
        <?php } ?>

        <?php if ($pop->is_imap()) { ?>
          <span class="currentInbox"><?php buildfolderlink($_SESSION['nvll_folder']); ?></span>
        <?php } else { ?>
          <span class="currentInbox"><?php echo $html_inbox; ?></span>
        <?php } ?>

        <?php
        if (NVLL_Session::getQuotaEnable() == true) {
          //TODO: Move quota to a other place? Separate more from message number!
          $quotausage = new NVLL_QuotaUsage($_SESSION['quota']);
          if ($quotausage->isSupported()) { //if quota usage is supported...
            if ($_SESSION['quota_type'] == 'STORAGE') {
              echo '<span class="currentQuota">' . $quotausage->getFormattedStorageUsage() . '</span><span class="maxQuota"> / ' . $quotausage->getFormattedStorageLimit() . '</span>';
            } else {
              echo '<span class="currentQuota">' . $quotausage->getMessageUsage() . ' ' . $html_msgs . '</span><span class="maxQuota"> / ' . $quotausage->getMessageLimit() . ' ' . $html_msgs . '</span>';
            }
          }
        }
        ?>
      </td>
      <td class="titlew right">
        <?php
        $message_text = ($num_msg == 1) ? $html_msg : $html_msgs;
        echo $num_msg . ' ' . $message_text;
        ?>
      </td>
    </tr>
  </table>
</div>
<?php if (($pop->is_imap()) || ($pages > 1)) { ?>
  <div class="topNavigation">
    <table>
      <tr>
        <td class="inbox left">
          <?php echo $folder_line ?>
        </td>
        <td class="inbox center">
          <?php echo $reapply_filters ?>
        </td>
        <td class="inbox right">
          <?php echo $page_line ?>
        </td>
      </tr>
    </table>
  </div>
<?php } ?>
<div class="messageList">
  <!-- Message list bloc -->
  <form method="POST" action="delete.php?<?php echo NVLL_Session::getUrlGetSession(); ?>" id="delete_form">
    <?php include 'menu_inbox_top_opts.php'; ?>
    <table id="inboxTable">
      <tr>
        <th class="column0"></th>
        <?php
        foreach ($conf->column_order as $column) { //For all columns...
          switch ($column) {
            case '1':
              $column_title = $html_from;
              break;
            case '2':
              $column_title = $html_to;
              break;
            case '3':
              $column_title = $html_subject;
              break;
            case '4':
              $column_title = $html_date;
              break;
            case '5':
              $column_title = $html_size;
              break;
            case '6':
              $column_title = '';
              break;
            case '7':
              $column_title = '';
              break;
              //TODO: Make "Priority", "Flagged" and "SPAM" columns sortable!
            case '8':
              $column_title = '';
              break;
            case '9':
              $column_title = '';
              break;
            case '10':
              $column_title = '';
              break;
            case '11':
              $column_title = '';
              break;
          }
          echo '<th class="column' . $column;
          if ($_SESSION['nvll_sort'] == $column) echo ' sorted';
          echo '">';
          if ($column_title != '') { //If we have a column title...
            echo '<a href="api.php?' . NVLL_Session::getUrlGetSession() . '&sort=' . $column . '&amp;sortdir=' . $new_sortdir . '">' . $column_title . '</a>';
            if ($_SESSION['nvll_sort'] == $column) {
              echo '&nbsp;';
              echo '<a href="api.php?' . NVLL_Session::getUrlGetSession() . '&sort=' . $column . '&amp;sortdir=' . $new_sortdir . '">';
              echo '  <img src="themes/' . $_SESSION['nvll_theme'] . '/img/' . $arrow . '.png" class="sort" alt="' . $html_sort . '" title="' . $html_sort_by . ' ' . $column_title . '" />';
              echo '</a>';
            }
          } else { //If we NOT have a column title...
            if ($column == '8') { //If "Priority Text" column...
              echo $html_priority;
            } elseif ($column == '9') { //If "Priority Number" column...
              echo '<span title="' . $html_priority . '"></span>';
            } elseif ($column == '10') { //If "Flagged" column...
              echo '<span title="' . $html_flagged . '"></span>';
            } elseif ($column == '11') { //If "SPAM" column...
              echo $html_spam;
            }
          }
          echo '</th>';
        }
        ?>
      </tr>