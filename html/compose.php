<?php
if (!isset($conf->loaded)) die('Hacking attempt');

$html_mail = $user_prefs->getSendHtmlMail();
$has_images = false;

if (isset($content['body'])) $has_images = NVLL_Security::hasDisabledHtmlImages($content['body']);

// out-commented: dont allow display_image in edit mode, all user input would be lost
$display_images = (isset($_REQUEST['display_images']) && $_REQUEST['display_images'] == 1) ? '1' : '0';
$service_request = "";

if (isset($_REQUEST['service'])) $service_request = $_REQUEST['service'];

$req_mail = "";
if (isset($_REQUEST['mail'])) $req_mail = $_REQUEST['mail'];

$req_display_images = "";
if (isset($_REQUEST['display_images'])) $req_display_images = $_REQUEST['display_images'];

// Default e-mail address on send form
$mail_from = get_default_from_address();
?>

<div class="send">
  <br>
  <fieldset>
    <legend><?php echo strtoupper(convertLang2Html($html_new_msg)); ?></legend>
  </fieldset>
  <!-- If 'file_uploads=Off', we must set formtype to "normal" otherwise it won't work -->
  <form method="POST" action="delivery.php?<?php echo NVLL_Session::getUrlGetSession(); ?>&service=<?php echo $service_request; ?>&mail=<?php echo $req_mail; ?>&display_images=<?php echo $req_display_images; ?>" id="sendform" enctype="<?php echo (ini_get("file_uploads")) ? "multipart/form-data" : "normal" ?>" onsubmit="return(validate(this));">
    <?php
    if (isset($broken_forwarding) && !$broken_forwarding) {
      if (isset($forward_msgnum)) { ?>
        <div><input type="hidden" name="forward_msgnum" value="<?php echo $forward_msgnum ?>" /></div>
    <?php }
    }

    // include old messageid
    // to keep 'treeview' of mailinglist threads etc.
    if (!empty($mail_messageid)) print('<div><input type="hidden" name="mail_messageid" value="' . $mail_messageid . '" /></div>');
    if (isset($_SESSION['nvll_domain_index']) && $_SESSION['nvll_domain_index'] >= 0) print('<div><input type="hidden" name="saved_domain_index" value="' . $_SESSION['nvll_domain_index'] . '" /></div>');
    ?>

    <table>
      <?php
      $isForward = isset($_GET['service']) && $_GET['service'] == 'forward';
      $hasAttachments = isset($_SESSION['nvll_attach_array']) && count($_SESSION['nvll_attach_array']) > 0;

      if ($isForward || $hasAttachments): ?>
        <tr>
          <td class="sendLabel">&nbsp;</td>
          <td class="sendData"></td>
        </tr>
      <?php endif; ?>
      <tr>
        <td class="sendLabel"><label <?php echo $conf->domains[$_SESSION['nvll_domain_index']]->allow_address_change ? 'for="mail_from"' : '' ?>><?php echo $html_from_label ?></label></td>
        <td class="sendData">
          <?php
          $prefs_show_email = (
            (isset($conf->domains[$_SESSION['nvll_domain_index']]->allow_address_change) && $conf->domains[$_SESSION['nvll_domain_index']]->allow_address_change)
            || (! isset($conf->domains[$_SESSION['nvll_domain_index']]->allow_address_change) && $conf->allow_address_change)
          );
          if ($prefs_show_email) { ?>
            <input class="button" type="text" name="mail_from" id="mail_from" size="60" value="<?php echo htmlspecialchars($mail_from, ENT_COMPAT | ENT_SUBSTITUTE) ?>" />
          <?php } else {
            echo htmlspecialchars($mail_from, ENT_COMPAT | ENT_SUBSTITUTE);
          } ?>
        </td>
      </tr>
      <tr>
        <td class="sendLabel"><label for="mail_to"><?php echo $html_to_label ?></label></td>
        <td class="sendData">
          <input class="button" type="text" name="mail_to" id="mail_to" size="60" value="<?php echo (isset($mail_to) ? stripslashes(htmlspecialchars($mail_to, ENT_COMPAT | ENT_SUBSTITUTE)) : ''); ?>" />
          <?php if ($conf->prefs_dir && isset($conf->contact_number_max) && $conf->contact_number_max != 0) { ?>
            <a href="javascript:void(0);" onclick="window.open('contacts.php?<?php echo NVLL_Session::getUrlGetSession(); ?>&field=mail_to&amp;<?php echo NVLL_Session::getUrlQuery(); ?>','','scrollbars=yes, resizable=yes, width=700, height=350')"><?php echo $html_select_contacts ?></a>
          <?php } ?>
        </td>
      </tr>
      <tr>
        <td class="sendLabel"><label for="mail_cc"><?php echo $html_cc_label ?></label></td>
        <td class="sendData">
          <input class="button" type="text" name="mail_cc" id="mail_cc" size="60" value="<?php echo (isset($mail_cc) ? htmlspecialchars($mail_cc, ENT_COMPAT | ENT_SUBSTITUTE) : '') ?>" />
          <?php if ($conf->prefs_dir && isset($conf->contact_number_max) && $conf->contact_number_max != 0) { ?>
            <a href="javascript:void(0);" onclick="window.open('contacts.php?<?php echo NVLL_Session::getUrlGetSession(); ?>&field=mail_cc&amp;<?php echo NVLL_Session::getUrlQuery(); ?>','','scrollbars=yes, resizable=yes, width=700, height=350')"><?php echo $html_select_contacts ?></a>
          <?php } ?>
        </td>
      </tr>
      <tr>
        <td class="sendLabel"><label for="mail_bcc"><?php echo $html_bcc_label ?></label></td>
        <td class="sendData">
          <input class="button" type="text" name="mail_bcc" id="mail_bcc" size="60" value="<?php echo (isset($mail_bcc) ? htmlspecialchars($mail_bcc, ENT_COMPAT | ENT_SUBSTITUTE) : '') ?>" />
          <?php if ($conf->prefs_dir && isset($conf->contact_number_max) && $conf->contact_number_max != 0) { ?>
            <a href="javascript:void(0);" onclick="window.open('contacts.php?<?php echo NVLL_Session::getUrlGetSession(); ?>&field=mail_bcc&amp;<?php echo NVLL_Session::getUrlQuery(); ?>','','scrollbars=yes, resizable=yes, width=700, height=350')"><?php echo $html_select_contacts ?></a>
          <?php } ?>
        </td>
      </tr>
      <tr>
        <td class="sendLabel"><label for="mail_subject"><?php echo $html_subject_label ?></label></td>
        <td class="sendData">
          <input class="button" type="text" name="mail_subject" id="mail_subject" size="60" maxlength="200" value="<?php echo (isset($mail_subject) ? htmlspecialchars($mail_subject, ENT_COMPAT | ENT_SUBSTITUTE) : '') ?>" />
        </td>
      </tr>
      <!-- If 'file_uploads=Off', we mustn't present the ability to do attachments -->
      <?php if (ini_get("file_uploads")) { ?>
        <?php if (isset($_GET['service']) && $_GET['service'] != 'forward' || (isset($broken_forwarding) && $broken_forwarding)) { ?>
          <tr>
            <td class="sendLabel"><label for="mail_attachment"><?php echo $html_att_label ?></label></td>
            <td class="sendData">
              <input class="button" type="file" name="mail_attachment" id="mail_attachment" size="40" value="" style="max-width:180px" />
              <input type="submit" class="button" onclick="btnClicked=this" name="sendaction" value="<?php echo $html_attach ?>" />
            </td>
          </tr>
        <?php } ?>
      <?php } ?>
      <tr>
        <td class="sendLabel"><label for="priority"><?php echo $html_priority_label ?></label></td>
        <td class="sendData">
          <select class="button" name="priority" id="priority">
            <?php
            $sel = 3;
            if (isset($mail_priority) && $mail_priority == "1 (Highest)") $sel = 1;
            if (isset($mail_priority) && $mail_priority == "2 (High)") $sel = 2;
            if (isset($mail_priority) && $mail_priority == "3 (Normal)") $sel = 3;
            if (isset($mail_priority) && $mail_priority == "4 (Low)") $sel = 4;
            if (isset($mail_priority) && $mail_priority == "5 (Lowest)") $sel = 5;
            ?>
            <option value="1 (Highest)" <?php if ($sel == 1) echo 'selected="selected"'; ?>><?php echo $html_highest ?></option>
            <option value="2 (High)" <?php if ($sel == 2) echo 'selected="selected"'; ?>><?php echo $html_high ?></option>
            <option value="3 (Normal)" <?php if ($sel == 3) echo 'selected="selected"'; ?>><?php echo $html_normal ?></option>
            <option value="4 (Low)" <?php if ($sel == 4) echo 'selected="selected"'; ?>><?php echo $html_low ?></option>
            <option value="5 (Lowest)" <?php if ($sel == 5) echo 'selected="selected"'; ?>><?php echo $html_lowest ?></option>
          </select>
          <input name="receipt" id="receipt" type="checkbox" <?php if (isset($mail_receipt) && $mail_receipt) echo "checked"; ?> />
          <label for="receipt"><?php echo $html_receipt ?></label>
        </td>
      </tr>
      <?php
      if ($hasAttachments) {
        echo '<tr>';
        echo '<td>&nbsp;</td>';
        echo '<td class="sendData">';
        $attach_array = $_SESSION['nvll_attach_array'];
        $attach_array_serialized = base64_encode(serialize($attach_array));
        echo '<div><input type="hidden" name="nvll_attach_array" value="' . $attach_array_serialized . '" /></div>';
        echo '<table id="attachTable">';
        echo '<tr>';
        echo '<th></th>';
        echo '<th>' . $html_filename . '</th>';
        echo '<th>' . $html_size . '</th>';
        echo '</tr>';
        $totalsize = 0;
        for ($i = 0; $i < count($attach_array); $i++) {
          $totalsize += $attach_array[$i]->getSize();
          //$att_name = NVLL_IMAP::utf8($attach_array[$i]->getName());
          $att_name = $attach_array[$i]->getName();
          echo '<tr>';
          echo '<td>';
          echo '<input type="checkbox" name="file-' . $i . '" id="file-' . $i . '" />';
          echo '</td>';
          echo '<td><label for="file-' . $i . '">' . htmlentities($att_name, ENT_COMPAT, 'UTF-8') . '</label></td>';
          echo '<td>' . $attach_array[$i]->getSize() . ' ' . $html_kb . '</td>';
          echo '</tr>';
        }
        echo '<tr>';
        echo '<th colspan="2">';
        echo '<input type="submit" class="button" onclick="btnClicked=this" name="sendaction" value="' . $html_attach_delete . '" />';
        echo '</th>';
        // FIXME: this should be in one message with $totalsize as a parameter
        echo '<th>' . $totalsize . ' ' . $html_kb . '</th>';
        echo '</tr>';
        echo '</table>';
        echo '</td>';
        echo '</tr>';
      } else {
        if (isset($broken_forwarding) && !$broken_forwarding) {
          if ($isForward) {
            echo '<tr>';
            echo '<td>&nbsp;</td>';
            echo '<td style="text-align:start;"><span class="inbox success-message-bg">' . $html_forward_info . '</span></td>';
            echo '</tr>';
          } else {
            echo '&nbsp;';
          }
        } else {
          echo '&nbsp;';
        }
      } ?>
      <?php
      if ($has_images && $display_images != 1 && $html_mail) {
        echo ('<tr><td colspan="2"><div class="nopic">');
        echo ($html_images_warning);
        echo ('<br/>');
        // out-commented: dont allow display_image in edit mode, all user input would be lost
        //echo('<a href="api.php?'.NVLL_Session::getUrlGetSession().'&service='.$service.'&mail='.$content['msgnum'].'&verbose='.$verbose.'&display_images=1">'.$html_images_display.'</a>');
        echo ('</td></tr></div>');
      }
      echo '<tr>' ?>
      <td>&nbsp;</td>
      <td class="sendData">
        <textarea name="mail_body" cols="59" rows="20" placeholder="..."><?php echo (isset($mail_body) ? htmlspecialchars($mail_body, ENT_COMPAT | ENT_SUBSTITUTE) : '') ?></textarea>
      </td>
      </tr>
      <tr>
        <td></td>
        <td>
          <p class="sendButtons">
            <input type="submit" class="button" onclick="btnClicked=this" name="sendaction" value="<?php echo $html_send ?>" />
            &nbsp;&nbsp;
            <input type="submit" class="button" onclick="btnClicked=this" name="sendaction" value="<?php echo $html_cancel ?>" />
          </p>
        </td>
      </tr>
    </table>
  </form>
</div>

<script type="text/javascript">
  let btnClicked;

  function validate(f) {
    if (checkSendDelay() == false) return (false);
    if (btnClicked.value == "<?php echo unhtmlentities($html_cancel) ?>") {
      retVal = confirm("<?php echo $reset_clicked ?>");
      if (retVal == false) return false;
      return true;
    }

    if (btnClicked.value == "<?php echo unhtmlentities($html_attach) ?>") {
      if (f.elements['mail_attachment'].value == "") {
        alert('<?php echo unhtmlentities($html_attach_none) ?>');
        return (false);
      } else {
        return (true);
      }
    }

    if (btnClicked.value == "<?php echo unhtmlentities($html_attach_delete) ?>") return (true);
    if (window.RegExp) {
      let reg = new RegExp("[0-9A-Za-z]+", "g");
      if (!reg.test(f.elements['mail_to'].value)) {
        alert("<?php echo $to_empty ?>");
        f.elements['mail_to'].focus();
        return (false);
      }
    }

    if (f.elements['mail_attachment'].value != "") {
      alert("<?php echo unhtmlentities($html_attach_forget) ?>")
      return (false);
    }
    return (true);
  }

  function checkSendDelay() {
    let thisdate = new Date();
    let send_delay = <?php echo ($conf->send_delay) ?>;
    let last_send

    <?php if (isset($_SESSION['last_send'])) { ?>
      last_send = <?php echo ($_SESSION['last_send']) ?>;
    <?php } else { ?>
      last_send = 0;
    <?php } ?>

    if (last_send + send_delay < (thisdate.getTime() / 1000)) {
      return (true);
    } else {
      alert('<?php echo (i18n_message($lang_err_send_delay, $conf->send_delay)) ?>');
      return (false);
    }
    return false;
  }
</script>