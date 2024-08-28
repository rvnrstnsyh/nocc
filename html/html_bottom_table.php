<?php if (!isset($conf->loaded)) die('Hacking attempt'); ?>

</table>
<?php include 'menu_inbox_status.php'; ?>
<?php include 'menu_inbox_bottom_opts.php'; ?>
<!-- end of Message list block -->
</div>
<?php if ($pages > 1) { ?>
  <div class="bottomNavigation">
    <table>
      <tr>
        <td class="inbox">&nbsp;</td>
        <td class="inbox right">
          <?php echo $page_line ?>
        </td>
      </tr>
    </table>
  </div>
<?php } ?>