<!-- start of $Id: html_inbox.php 2567 2013-08-06 10:44:40Z oheil $ -->
<?php
if (!isset($conf->loaded)) die('Hacking attempt');

$even_odd_class = ($tmp['index'] % 2) ? 'even' : 'odd';

$unread_class = '';
if ($_SESSION['ucb_pop_server'] || $pop->is_imap()) {
  if ($tmp['unread'] == true) { //if unread...
    $unread_class = ' unread';
  }
}

// Merge flagged classes if the item is flagged.
$row_class = $even_odd_class . $unread_class . $spam_class;

if ($tmp['flagged']) { //if Flagged...
  $row_class .= ' flagged';
}

$spam_class = '';
if ($tmp['spam'] == true) { //if SPAM...
  $spam_class = ' spam' . ucfirst($even_odd_class); //spamOdd or spamEven
}

$target_blank = '';
if (isset($user_prefs->seperate_msg_win) && $user_prefs->seperate_msg_win) {
  $target_blank = ' target="_blank"';
}

echo '<tr class="' . $row_class . '">';
echo '<td class="column0">';
echo '  <input type="checkbox" name="msg-' . $tmp['number'] . '" value="Y" />';
echo '</td>';

foreach ($conf->column_order as $column) { //For all columns...
  echo '<td class="column' . $column;
  if ($_SESSION['nocc_sort'] == $column) echo ' sorted';
  echo '">';
  switch ($column) {
    case '1': //From...
      echo '<a href="action.php?' . NOCC_Session::getUrlGetSession() . '&action=compose&amp;mail_to=' . convertMailData2Html($tmp['from']) . '" title="' . convertMailData2Html($tmp['from']) . '">' . convertMailData2Html(display_address($tmp['from']), 55) . '</a>&nbsp;';
      break;
    case '2': //To...
      echo convertMailData2Html(display_address($tmp['to']), 55);
      break;
    case '3': //Subject...
      echo '<a href="action.php?' . NOCC_Session::getUrlGetSession() . '&action=aff_mail&amp;mail=' . $tmp['number'] . '&amp;verbose=' . $conf->use_verbose_by_default . '&amp;title=';
      echo $tmp['subject'] ? convertMailData2Html($tmp['subject']) : $html_nosubject;
      echo '"' . $target_blank . '>';
      echo $tmp['subject'] ? convertMailData2Html($tmp['subject'], 55) : $html_nosubject;
      echo '</a>';
      break;
    case '4': //Date...
      echo $tmp['date'] . '&nbsp;' . $tmp['time'];
      break;
    case '5': //Size...
      echo $tmp['size'] . $html_kb;
      break;
    case '6': //Read/Unread...
      if ($tmp['unread'] == true) { //if unread...
        if ($conf->use_icon) {
          echo '<img src="themes/' . $_SESSION['nocc_theme'] . '/img/svg/unread.svg" alt="" />';
        } else {
          echo '+U';
        }
      } else { //if read...
        echo '';
      }
      break;
    case '7': //Attachment...
      if ($tmp['attach'] == true) { //if has attachments...
        if ($conf->use_icon) {
          echo '<img src="themes/' . $_SESSION['nocc_theme'] . '/img/svg/has-attachment.svg" alt="" />';
        } else {
          echo '+A';
        }
      } else { //if NOT has attachments...
        echo '';
      }
      break;
    case '8': //Priority Text...
      echo $tmp['priority_text'];
      break;
    case '9': //Priority Number...
      echo '<span title="' . $html_priority_label . ' ' . $tmp['priority_text'] . '">' . '+' . $tmp['priority'] . '</span>';
      break;
    case '10': //Flagged...
      if ($tmp['flagged']) {
        echo '';
      }
      break;
    case '11': //SPAM...
      if ($tmp['spam']) {
        // echo $html_spam;
        echo '+S';
      }
      break;
  }
  echo '</td>';
}
echo '</tr>';
?>
<!-- end of $Id: html_inbox.php 2567 2013-08-06 10:44:40Z oheil $ -->