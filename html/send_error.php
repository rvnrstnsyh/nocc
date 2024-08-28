<?php if (!isset($conf->loaded)) die('Hacking attempt'); ?>

<p class="inbox"><?php echo convertLang2Html($html_error_occurred) . ' : ' . convertLang2Html($ev->getMessage()); ?></p>