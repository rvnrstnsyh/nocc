<?php if (!isset($conf->loaded)) die('Hacking attempt'); ?>

<table>
  <tr>
    <td class="menuOpts left">
      <input type="button" class="button" value="<?php echo convertLang2Html($html_select_all); ?>" onselect="InvertCheckedMsgs()" onclick="InvertCheckedMsgs()" />
    </td>
    <td class="menuOpts center">
      <?php if ($pop->is_imap() && $pop->get_folder_count() > 1) {
        $html_target_select = $pop->html_folder_select('target_folder', '');
      ?>
        <input type="submit" class="button" name="move_mode" value="<?php echo convertLang2Html($html_move); ?>" /> <?php echo convertLang2Html($html_or); ?>
        <input type="submit" class="button" name="copy_mode" value="<?php echo convertLang2Html($html_copy); ?>" />
        <label for="target_folder"><?php echo convertLang2Html($html_messages_to); ?></label>
        <?php echo $html_target_select; ?>
        <label for="target_folder"><?php echo convertLang2Html($html_folder); ?></label>
      <?php } ?>
    </td>
    <td class="menuOpts right">
      <?php if ($pop->is_imap()) { ?>
        <input type="submit" name="set_flag" class="button" value="<?php echo convertLang2Html($html_mark_as); ?>" />
        <select class="button" name="mark_mode">
          <option value="seen"><?php echo convertLang2Html($html_seen); ?></option>
          <option value="unseen"><?php echo convertLang2Html($html_unseen); ?></option>
          <option value="flag"><?php echo convertLang2Html($html_flag); ?></option>
          <option value="unflag"><?php echo convertLang2Html($html_unflag); ?></option>
        </select>
      <?php } ?>
      <input type="submit" name="forward_mode" class="button" value="<?php echo $html_forward; ?>" />
      <input type="submit" name="delete_mode" class="button" value="<?php echo $html_delete; ?>" onclick="return confirmDelete();" />
      <input type="checkbox" name="bypass_trash" id="bypass_trash" value="true" />
      <label for="bypass_trash"><?php echo convertLang2Html($html_bypass_trash); ?></label>
    </td>
  </tr>
</table>