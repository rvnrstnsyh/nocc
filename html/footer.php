<!-- start of $Id: footer.php 2255 2010-06-28 07:46:41Z gerundt $ -->
<?php
if (!isset($conf->loaded)) die('Hacking attempt');

if (!isset($theme)) //if the $theme variable NOT set...
    $theme = new NVLL_Theme($_SESSION['nvll_theme']);

$custom_footer = $theme->getCustomFooter();
if (file_exists($custom_footer)) {
    include $custom_footer;
} else {
?>
    </div>
    <div id="footer">
        <a href="http://nvll.sourceforge.net" target="_blank">
            <img src="<?php echo $theme->getPath(); ?>/img/button.png" id="footerLogo" alt="Powered by NOCC" title="Powered by NOCC" />
        </a>
    </div>
    <?php
    if (NVLL_DEBUG_LEVEL > 0) {
        define('NVLL_END_TIME', microtime(true));

        $time = NVLL_END_TIME - NVLL_START_TIME;
        $usage = memory_get_usage() / 1024;
        $peakUsage = memory_get_peak_usage() / 1024;

        printf('<p class="debug">Time: <strong>%.2f sec</strong> - Memory Usage: <strong>%.2f KB</strong> - Memory Peak Usage: <strong>%.2f KB</strong></p>', $time, $usage, $peakUsage);
    }
    ?>
    </body>

    </html>
<?php
}
?>
<!-- end of $Id: footer.php 2255 2010-06-28 07:46:41Z gerundt $ -->