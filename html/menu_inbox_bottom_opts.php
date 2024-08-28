<?php if (!isset($conf->loaded)) die('Hacking attempt'); ?>

<table>
  <tr>
    <td class="menuOpts left">
      <input type="button" class="button" value="<?php echo convertLang2Html($html_select_all); ?>" onselect="InvertCheckedMsgs()" onclick="InvertCheckedMsgs()" />
    </td>
    <td class="menuOpts center">
      <?php
      if ($pop->is_imap() && $pop->get_folder_count() > 1) {
        $html_bottom_target_select = $pop->html_folder_select('bottom_target_folder', '');
      ?>
        <input type="submit" class="button" name="bottom_move_mode" value="<?php echo convertLang2Html($html_move); ?>" /> <?php echo convertLang2Html($html_or); ?>
        <input type="submit" class="button" name="bottom_copy_mode" value="<?php echo convertLang2Html($html_copy); ?>" />
        <label for="bottom_target_folder"><?php echo convertLang2Html($html_messages_to); ?></label>
        <?php echo $html_bottom_target_select; ?>
        <label for="bottom_target_folder"><?php echo convertLang2Html($html_folder); ?></label>
      <?php
      }
      ?>
    </td>
    <td class="menuOpts right">
      <?php
      if ($pop->is_imap()) {
      ?>
        <input type="submit" name="bottom_set_flag" class="button" value="<?php echo convertLang2Html($html_mark_as); ?>" />
        <select class="button" name="bottom_mark_mode">
          <option value="read"><?php echo convertLang2Html($html_read); ?></option>
          <option value="unread"><?php echo convertLang2Html($html_unread); ?></option>
          <option value="flag"><?php echo convertLang2Html($html_flag); ?></option>
          <option value="unflag"><?php echo convertLang2Html($html_unflag); ?></option>
        </select>
      <?php
      }
      ?>
      <input type="submit" name="bottom_forward_mode" class="button" value="<?php echo $html_forward; ?>" />
      <input type="submit" name="bottom_delete_mode" class="button" value="<?php echo $html_delete; ?>" onclick="if (confirm('<?php echo $html_del_msg; ?>')) return true; else return false;" />
    </td>
  </tr>
</table>
</form>