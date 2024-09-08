<?php

/**
 * Login
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

//If a previous authentification cookie was set, we use it to bypass login window.

require_once './common.php';
require_once './utils/captcha.php';

if (isset($_REQUEST['_vmbox']) && $_REQUEST['_vmbox'] == "RSS") {
  header("Location: " . $conf->base_url);
  exit();
}

if (isset($_SESSION['restart_session']) && $_SESSION['restart_session'] == true) {
  header("Location: " . $conf->base_url . "api.php?" . NVLL_Session::getUrlGetSession());
  exit();
}

require_once './utils/check.php';
require './html/header.php';
?>

<form method="POST" action="api.php?<?php echo NVLL_Session::getUrlGetSession(); ?>" id="nvll_webmail_login" accept-charset="UTF-8">
  <div id="loginBox">
    <h2><?php echo i18n_message($html_welcome, $conf->nvll_name); ?></h2>
    <input type="hidden" name="service" value="login" />
    <input type="hidden" name="folder" value="INBOX" />
    <table>
      <tr>
        <th><label for="user"><?php echo $html_user_label; ?></label></th>
        <td>
          <input class="button" type="text" name="user" id="user" size="25" placeholder="e.g. chernobyl, chernobyl@nvll.me" value="<?php if (isset($REMOTE_USER)) echo $REMOTE_USER; ?>" />
          <?php if (count($conf->domains) > 1) {
            //Add fill-in domain
            if (isset($conf->typed_domain_login))
              echo '<label for="fillindomain">@</label> <input class="button" type="text" name="fillindomain" id="fillindomain">';
            else if (isset($conf->vhost_domain_login) && $conf->vhost_domain_login == true) {
              $i = 0;
              while (!empty($conf->domains[$i]->in)) {
                if (strpos($_SERVER['HTTP_HOST'], $conf->domains[$i]->domain))
                  echo '<input type="hidden" name="domain_index" id="domain_index" value="' . $i . '" />' . "\n";
                $i++;
              }
            } else {
              echo '<label for="domain_index">@</label> <select class="button" name="domain_index" id="domain_index">';
              $i = 0;
              while (!empty($conf->domains[$i]->in)) {
                if (isset($conf->domains[$i]->show_as) && strlen($conf->domains[$i]->show_as) > 0) {
                  if (!isset($_SESSION['send_backup']) || $_SESSION['send_backup']['nvll_domain_index'] == $i) {
                    echo "<option value=\"$i\">" . $conf->domains[$i]->show_as . '</option>';
                  }
                } else {
                  if (!isset($_SESSION['send_backup']) || $_SESSION['send_backup']['nvll_domain_index'] == $i) {
                    echo "<option value=\"$i\">" . $conf->domains[$i]->domain . '</option>';
                  }
                }
                $i++;
              }
              echo '</select>' . "\n";
            }
          } else {
            echo '<input type="hidden" name="domain_index" value="0" id="domain_index" />' . "\n";
          }
          ?>
        </td>
      </tr>
      <tr>
        <th><label for="passwd"><?php echo $html_passwd_label ?></label></th>
        <td>
          <input class="button" type="password" name="passwd" id="passwd" size="25" />
        </td>
      </tr>
      <?php if ($conf->domains[0]->in == '') {
        echo '<tr>';
        echo '<th><label for="server">' . $html_server_label . '</label></th>';
        echo '<td>';
        echo '<input class="button" type="text" name="server" id="server" value="mail.example.com" size="15" /><br /><input class="button" type="text" size="4" name="port" value="143" />';
        echo '<select class="button" name="servtype" onchange="updateLoginPort()">';
        echo '<option value="imap">IMAP</option>';
        echo '<option value="notls">IMAP (no TLS)</option>';
        echo '<option value="ssl">IMAP SSL</option>';
        echo '<option value="ssl/novalidate-cert">IMAP SSL (self signed)</option>';
        echo '<option value="pop3">POP3</option>';
        echo '<option value="pop3/notls">POP3 (no TLS)</option>';
        echo '<option value="pop3/ssl">POP3 SSL</option>';
        echo '<option value="pop3/ssl/novalidate-cert">POP3 SSL (self signed)</option>';
        echo '</select>';
        echo '</td>';
        echo '</tr>';
      }

      if ($conf->hide_lang_select_from_login_page == false) { ?>
        <tr>
          <th><label for="lang"><?php echo $html_lang_label ?></label></th>
          <td>
            <select class="button" name="lang" id="lang" onchange="updateLoginPage('<?php echo NVLL_Session::getUrlGetSession(); ?>')">
              <?php
              echo '<option value="default"';
              if (!isset($_REQUEST['lang']) || $_REQUEST['lang'] == "default") {
                echo ' selected="selected"';
              }
              echo '>' . convertLang2Html($html_default) . '</option>';
              foreach ($lang_array as $_lang_key => $_lang_var) {
                if (file_exists('lang/' . $_lang_var->filename . '.php')) {
                  echo '<option value="' . $_lang_var->filename . '"';
                  if (isset($_REQUEST['lang']) && $_REQUEST['lang'] != "default" && $_SESSION['nvll_lang'] == $_lang_var->filename) {
                    echo ' selected="selected"';
                  }
                  echo '>' . convertLang2Html($_lang_var->label) . '</option>';
                }
              }
              ?>
            </select>
          </td>
        </tr>
      <?php }

      if ($conf->use_theme == true && $conf->hide_theme_select_from_login_page == false) { ?>
        <tr>
          <th><label for="theme"><?php echo $html_theme_label ?></label></th>
          <td>
            <select class="button" name="theme" id="theme" onchange="updateLoginPage('<?php echo NVLL_Session::getUrlGetSession(); ?>')">
              <?php
              echo '<option value="default"';
              if (!isset($_REQUEST['lang']) || $_REQUEST['lang'] == "default") echo ' selected="selected"';

              echo '>' . convertLang2Html($html_default) . '</option>';
              $themes = new NVLL_Themes('./themes/', $_SESSION['nvll_theme']);

              foreach ($themes->getThemeNames() as $themeName) { //for all theme names...
                echo '<option value="' . $themeName . '"';
                if (isset($_REQUEST['theme']) && $_REQUEST['theme'] != "default" && $themeName == $_SESSION['nvll_theme']) echo ' selected="selected"';
                echo '>' . $themeName . '</option>';
              }

              unset($themeName);
              unset($themes);
              ?>
            </select>
          </td>
        </tr>
      <?php }

      if (isset($conf->prefs_dir) && $conf->prefs_dir != '') { ?>
        <tr>
          <th></th>
          <td class="remember">
            <input type="checkbox" name="remember" id="remember" value="true" />
            <label for="remember">
              <?php echo convertLang2Html($html_remember) ?>
              <br>
              <span><?php echo convertLang2Html($html_remember_desc); ?>
                <a href=""><?php echo convertLang2Html($html_why); ?>?
                </a></span>
            </label>
          </td>
        </tr>

        <?php if ($conf->use_captcha): ?>
          <tr>
            <td><?php echo send_captcha(); ?></td>
          </tr>
        <?php endif; ?>


        <?php if (isset($_SESSION['send_backup'])): ?>
          <tr>
            <td colspan="2">
              <br />
              <?php echo "<span style=\"color:red\">" . $html_send_recover . "</span>"; ?>
              <br />
              <a href="index.php?<?php echo NVLL_Session::getUrlGetSession(); ?>&discard=1">
                <?php echo $html_send_discard; ?>
              </a>
              <br />
            </td>
          </tr>
        <?php endif; ?>
      <?php } ?>
    </table>
    <p><input name="enter" class="button" type="submit" value="<?php echo $html_login ?>" /></p>
  </div>
</form>

<script type="text/javascript">
  document.getElementById("nvll_webmail_login").user.focus();
  document.getElementById("nvll_webmail_login").passwd.value = '';
</script>
<?php

require './html/footer.php';
