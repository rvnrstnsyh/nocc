<?php if (!isset($conf->loaded)) die('Hacking attempt'); ?>

<div class="error">
  <table class="errorTable">
    <tr class="errorTitle">
      <td><?php echo convertLang2Html($html_error_occurred) ?></td>
    </tr>
    <tr class="errorText">
      <td>
        <p><?php echo convertLang2Html($ev->getMessage()); ?></p>
        <p>
          <?php if (isset($_SESSION['nvll_loggedin']) && $_SESSION['nvll_loggedin']) { ?>
            <a href=""><?php echo convertLang2Html($html_retry) ?></a>&nbsp;&nbsp;
            <a href="logout.php?<?php echo NVLL_Session::getUrlGetSession(); ?>"><?php echo convertLang2Html($html_logout) ?></a>
          <?php
          } else { ?>
            <a href="logout.php?<?php echo NVLL_Session::getUrlGetSession(); ?>"><?php echo convertLang2Html($html_back) ?></a>
          <?php } ?>
        </p>
      </td>
    </tr>
  </table>
</div>