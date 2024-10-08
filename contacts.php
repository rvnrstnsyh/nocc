<?php

/**
 * Show contacts
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

require_once dirname(__FILE__) . '/classes/NVLL_Contacts.php';

require_once dirname(__FILE__) .  '/common.php';
require_once dirname(__FILE__) .  '/functions/proxy.php';

header("Content-type: text/html; Charset=UTF-8");

try {
  $pop = new NVLL_IMAP();
} catch (Exception $ex) {
  //TODO: Show error without NVLL_Exception!
  $ev = new NVLL_Exception($ex->getMessage());
  require dirname(__FILE__) . '/html/header.php';
  require dirname(__FILE__) . '/html/error.php';
  require dirname(__FILE__) . '/html/footer.php';
  exit;
}

$pop->close();
$theme = new NVLL_Theme($_SESSION['nvll_theme']);
$_SESSION['nvll_loggedin'] = 1;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $lang ?>" lang="<?php echo $lang ?>">

<head>
  <title>Non-Violable Liberty Layers | Webmail - <?php echo i18n_message($html_contact_list, $_SESSION['nvll_user']); ?></title>
  <link href="<?php echo $theme->getStylesheet(); ?>" rel="stylesheet" type="text/css" />
  <link href="<?php echo $theme->getFavicon(); ?>" rel="shortcut icon" type="image/x-icon" />
  <meta content="text/html; charset=UTF-8" http-equiv="Content-Type" />
  <script type="text/javascript">
    function toggleemail(bt, email) {
      var field = window.opener.document.getElementById("sendform").<?php echo $_GET['field']; ?>.value;

      if (bt.value == '<?php echo unhtmlentities($html_add) ?>') {
        if (field == '') {
          window.opener.document.getElementById("sendform").<?php echo $_GET['field']; ?>.value = email;
        } else {
          window.opener.document.getElementById("sendform").<?php echo $_GET['field']; ?>.value = field + "; " + email;
        }
      } else {
        //var f = '';
        //tbl = field.split("; ");
        //for (i = 0; i < tbl.length; ++i)
        //{
        //  if (f != '' && tbl[i] != email)
        //    f += '; ';
        //  if (tbl[i] != email)
        //    f += tbl[i];
        //}
        var current = window.opener.document.getElementById("sendform").<?php echo $_GET['field']; ?>.value;
        var f = current.replace(email + "; ", "");
        f = f.replace("; " + email, "");
        f = f.replace(email, "");
        window.opener.document.getElementById("sendform").<?php echo $_GET['field']; ?>.value = f;
      }
    }

    function toggle(bt) {
      if (bt.value == '<?php echo unhtmlentities($html_add) ?>')
        bt.value = '<?php echo unhtmlentities($html_delete) ?>';
      else
        bt.value = '<?php echo unhtmlentities($html_add) ?>';
    }
  </script>
</head>

<body id="popup" dir="<?php echo $lang_dir; ?>"><a name="top"></a>
  <?php
  // By default display user contacts
  if (empty($_REQUEST['is_ldap'])) {
    if (!isset($conf->contact_number_max) || $conf->contact_number_max == 0) { ?>
      <div class="error">
        <table class="errorTable">
          <tr class="errorTitle">
            <td><?php echo convertLang2Html($html_error_occurred); ?></td>
          </tr>
          <tr class="errorText">
            <td>
              <p><?php echo convertLang2Html($html_contact_err3); ?></p>
            </td>
          </tr>
        </table>
      </div>
    <?php exit;
    }
  }
  if ($conf->contact_ldap === true) {
    if (empty($_REQUEST['is_ldap'])) {
      $contacts_ldap = false;
    } else {
      $contacts_ldap = true;
    }
    ?>
    <div id="header">
      <ul>
        <li>
          <?php
          $tab_title_personal = i18n_message($html_contact_list, $_SESSION['nvll_user']);

          // toggle activated tab
          if ($contacts_ldap === true) {
            print('<a href="contacts.php?field=' . $_GET['field'] . '&' . NVLL_Session::getUrlGetSession() . '">' . $tab_title_personal . '</a>');
          } else {
            echo $tab_title_personal;
          }
          ?>
        </li>
        <li>
          <?php
          $tab_title_group = i18n_message($html_contacts, $conf->contact_ldap_options['group_title']);

          // toggle activated tab
          if ($contacts_ldap === false) {
            print('<a href="contacts.php?is_ldap=1&field=' . $_GET['field'] . '&' . NVLL_Session::getUrlGetSession() . '">' . $tab_title_group . '</a>');
          } else {
            echo $tab_title_group;
          }
          ?>
        </li>
      </ul>
    </div>

    <?php
    // enable/disable search input
    if ((!empty($_REQUEST['is_ldap'])) && (!empty($conf->contact_ldap_options['search']))) { ?>
      <div id="contact_search">
        <form action="contacts.php?<?php echo NVLL_Session::getUrlGetSession(); ?>">
          <input type="hidden" name="is_ldap" value="1" />
          <input type="text" maxsize="16" size="16" name="ldap_filter" />
          <?php
          // print search by ... select box
          if (!empty($conf->contact_ldap_options['search_options']) && is_array($conf->contact_ldap_options['search_options'])) {
            // open select box
            $output_search_options = '<select name="search_field">';

            foreach ($conf->contact_ldap_options['search_options'] as $search_opt_key => $search_opt_value) {
              // remember last search option
              if ($search_opt_key === $_REQUEST['search_field']) {
                $output_search_selected = 'selected="selected"';
              } else {
                $output_search_selected = '';
              }

              // create option row for select box
              $output_search_options .= '<option value="' . $search_opt_key . '" ' . $output_search_selected . '>' . $search_opt_value . '</option>';
            }

            // close select box
            $output_search_options .= '</select>';

            // output select box
            print($output_search_options);
          }
          ?>
          <button type="submit" value="<?php echo $html_search ?>"><span><?php echo $html_search ?></span></button>
        </form>
      </div>
  <?php }
  } ?>
  <div class="contactsList">
    <p class="contactsTitle">
      <?php echo i18n_message($html_contact_list, $_SESSION['nvll_user']); ?>
    </p>

    <table>
      <?php
      if ($lang_dir === 'ltr') {
        $header = '<tr class="contactsListHeader">' .
          '<th>' . convertLang2Html($html_contact_first) . '</th>' .
          '<th>' . convertLang2Html($html_contact_last) . '</th>' .
          '<th>' . convertLang2Html($html_contact_nick) . '</th>' .
          '<th>' . convertLang2Html($html_contact_mail) . '</th>' .
          '<th></th>' .
          '</tr>';
      } else {
        $header = '<tr class="contactsListHeader">' .
          '<th>' . convertLang2Html($html_contact_mail) . '</th>' .
          '<th>' . convertLang2Html($html_contact_nick) . '</th>' .
          '<th>' . convertLang2Html($html_contact_last) . '</th>' .
          '<th>' . convertLang2Html($html_contact_first) . '</th>' .
          '<th></th>' .
          '</tr>';
      }
      ?>

      <?php
      function generateRulerLinks($show_lists_only, $field, $sessionUrl, $html_contact_all, $html_contact_listonly)
      {
        $ruler_listonly = $show_lists_only
          ? '-<a href="contacts.php?' . $sessionUrl . '&field=' . $field . '">&nbsp;&nbsp;' . convertLang2Html($html_contact_all) . '&nbsp;&nbsp;</a>'
          : '-<a href="contacts.php?' . $sessionUrl . '&field=' . $field . '&listonly=1">&nbsp;&nbsp;' . convertLang2Html($html_contact_listonly) . '&nbsp;&nbsp;</a>';

        return $ruler_listonly;
      }

      function generateEmailShow($tab, &$list_string)
      {
        if ($tab[5] == 1) {
          $all_emails = array();
          semisplit_address_list($tab[3], $all_emails, ';');
          $list_count = min(3, count($all_emails));
          $email_show = implode('; ', array_slice($all_emails, 0, $list_count));
          if (count($all_emails) >= 3) {
            $email_show .= '; ...';
          }
          $list_string = preg_replace('/"/', '\"', $tab[3]);
        } else {
          $email_show = $tab[3];
          $list_string = '';
        }
        return $email_show;
      }

      if (empty($_REQUEST['is_ldap'])) {
        $userKey = preg_replace("/(\\\|\/)/", "_", NVLL_Session::getUserKey());
        $path = $conf->prefs_dir . '/' . $userKey . '.contacts';
        $contacts = NVLL_Contacts::loadList($path);
        $contactCount = count($contacts);
        $show_lists_only = !empty($_GET['listonly']) && $_GET['listonly'] == 1;

        if ($contactCount <= 10) {
          echo $header;
        } else {
          $count2list = array();
          $all_rulers = array();
          $ruler_top = '<a href="#top">&nbsp;&nbsp;' . convertLang2Html($html_contact_ruler_top) . '&nbsp;&nbsp;</a>-';
          $sessionUrl = NVLL_Session::getUrlGetSession();
          $ruler_listonly = generateRulerLinks($show_lists_only, $_GET['field'], $sessionUrl, $html_contact_all, $html_contact_listonly);
          NVLL_Contacts::create_rulers($contacts, $ruler_top, $ruler_listonly, $all_rulers, $count2list, $show_lists_only);
        }

        $ruler_count = 0;
        foreach ($contacts as $i => $tab) {
          $email_show = '';
          $list_string = '';
          if ($contactCount > 10 && isset($count2list[strval($i)])) {
            echo $all_rulers[$ruler_count++];
            echo $header;
          }

          if ($tab[5] == 1 || !$show_lists_only) {
            $email_show = generateEmailShow($tab, $list_string);
            $rowClass = ($i % 2) ? "contactsListEven" : "contactsListOdd";
            $nameFirst = htmlspecialchars($tab[0] ?? "&nbsp;", ENT_COMPAT | ENT_SUBSTITUTE);
            $nameLast = htmlspecialchars($tab[1] ?? "&nbsp;", ENT_COMPAT | ENT_SUBSTITUTE);
            $nickname = htmlspecialchars($tab[2] ?? "&nbsp;", ENT_COMPAT | ENT_SUBSTITUTE);
            $emailShow = htmlspecialchars($email_show, ENT_COMPAT | ENT_SUBSTITUTE);
            $buttonValue = unhtmlentities($html_add);
            $buttonId = 'btn' . $i;

            $output = $tab[5] == 1
              ? "\\\"" . trim($tab[0] . " " . $tab[1]) . "\\\" <" . trim($tab[3]) . ">"
              : (strlen(trim($tab[0])) > 0 || strlen(trim($tab[1])) > 0
                ? "\\\"" . trim($tab[0] . " " . $tab[1]) . "\\\" <" . trim($tab[3]) . ">"
                : (strlen(trim($tab[2])) > 0
                  ? "\\\"" . trim($tab[2]) . "\\\" <" . trim($tab[3]) . ">"
                  : trim($tab[3])
                )
              );

            echo $lang_dir === 'ltr'
              ? "<tr class=\"$rowClass\">
                        <td class=\"contactNameFirst\">$nameFirst</td>
                        <td class=\"contactNameLast\">$nameLast</td>
                        <td class=\"contactNickname\">$nickname</td>
                        <td class=\"contactEmail\">$emailShow</td>
                        <td><input type=\"button\" name=\"Submit\" id=\"$buttonId\" value=\"$buttonValue\" class=\"button\" onclick='toggleemail(document.getElementById(\"$buttonId\"),\"$output\");toggle(document.getElementById(\"$buttonId\"));' /></td>
                    </tr>"
              : "<tr class=\"$rowClass\">
                        <td><input type=\"button\" name=\"Submit\" id=\"$buttonId\" value=\"$buttonValue\" class=\"button\" onclick='toggleemail(document.getElementById(\"$buttonId\"),\"$output\");toggle(document.getElementById(\"$buttonId\"));' /></td>
                        <td class=\"contactEmail\">$emailShow</td>
                        <td class=\"contactNickname\">$nickname</td>
                        <td class=\"contactNameLast\">$nameLast</td>
                        <td class=\"contactNameFirst\">$nameFirst</td>
                    </tr>";
          }
        }
      } else {
        // otherwise display LDAP 'group contacts'

        echo $header;

        if ($conf->contact_ldap === true) {
          // First we process our configuration, then we try to connect the LDAP server
          // and at last we try to fetch our data.
          //
          // The script will exit if an error occurs, even before any data will be processed!

          // Change DSN for SSL support
          if ($conf->contact_ldap_options['ssl'] === true) {
            $contact_host = 'ldaps://' . $conf->contact_ldap_options['host'];
          } else {
            $contact_host = 'ldap://' . $conf->contact_ldap_options['host'];
          }

          // Add different port to host, if available
          if (!empty($conf->contact_ldap_options['port'])) $contact_host .= ':' . $conf->contact_ldap_options['port'];

          // convert attributes to array, if not already one
          if (!is_array($conf->contact_ldap_options['attributes'])) $conf->contact_ldap_options['attributes'] = explode(',', $conf->contact_ldap_options['attributes']);

          if (!empty($conf->contact_ldap_options['suffix'])) {
            $contact_suffix = '@' . $conf->contact_ldap_options['suffix'];
          } else {
            $contact_suffix = '@' . $conf->domains[0]->domain;
          }

          // get attributes
          $contact_list_uid = $conf->contact_ldap_options['attributes'][0];

          if (empty($conf->contact_ldap_options['attributes'][2])) {
            $contact_name_split = true;
            $contact_list_name = $conf->contact_ldap_options['attributes'][1];
          } else {
            $contact_name_split = false;
            $contact_list_name_first = $conf->contact_ldap_options['attributes'][1];
            $contact_list_name_last = $conf->contact_ldap_options['attributes'][2];
          }

          if (!empty($conf->contact_ldap_options['attributes'][3])) {
            $contact_list_email = $conf->contact_ldap_options['attributes'][3];
          }

          // set filter / search options (does not work, at the moment)
          $contact_filter = sprintf($conf->contact_ldap_options['filter'], '*');
          $contact_filter_suffix = '';

          if (!empty($_REQUEST['ldap_filter'])) {

            if (empty($_REQUEST['search_field'])) {
              if (preg_match('/([a-zA-Z0-9])/', $_REQUEST['ldap_filter']) && strlen($_REQUEST['ldap_filter']) == 1) {
                $contact_filter_suffix = '*';

                $contact_filter = sprintf($conf->contact_ldap_options['filter'], $_REQUEST['ldap_filter'] . $contact_filter_suffix);
              }
            } else {
              if (preg_match('/([a-zA-Z0-9_.-])/', $_REQUEST['search_field'])) {
                $contact_filter = '(|(' . $_REQUEST['search_field'] . '=*' . $_REQUEST['ldap_filter'] . '*))';
              }
            }
          }

          // LDAP connection
          $contact_connection = ldap_connect($contact_host)
            or die("{$lang_could_not_connect}: {$conf->contact_ldap_options['host']}");

          // LDAP authentication
          if ($conf->contact_ldap_options['anonymous'] === false) {
            if (!empty($conf->contact_ldap_options['bind_dn']) && !empty($conf->contact_ldap_options['bind_pass'])) {
              $contact_bind = ldap_bind($contact_connection, $conf->contact_ldap_options['bind_dn'], $conf->contact_ldap_options['bind_pass']);
            }
          }

          // LDAP search
          $contact_search = ldap_search($contact_connection, $conf->contact_ldap_options['dn'], $contact_filter, $conf->contact_ldap_options['attributes'])
            or die("{$lang_could_not_connect}: {$conf->contact_ldap_options['host']}");

          // Sort LDAP search by:
          if (!empty($conf->contact_ldap_options['search_sortby'])) {
            $contact_search = ldap_search($contact_connection, $dn, $filter, $attributes);
            $entries = ldap_get_entries($contact_connection, $contact_search);

            if ($entries['count'] > 0) {
              usort($entries, function ($a, $b) use ($conf) {
                $field = $conf->contact_ldap_options['search_sortby'];
                return strcmp($a[$field][0], $b[$field][0]);
              });
            }
          }

          // LDAP get the data
          $contact_list = ldap_get_entries($contact_connection, $contact_search);
          $contact_output = '';

          $i = 0;
          foreach ($contact_list as $list_val) {
            $_uid = trim($list_val['count']);
            if ($i === 1) {
              print('<div id="contact-amount"><span id="contact-count">' . i18n_message($html_contact_count, $contact_list['count']) . '</span></div>');
            }

            // filter out hostnames (for  windows like hostname$)
            if ((!empty($_uid)) && (!preg_match('/\$/', $list_val[$contact_list_uid][0]))) {

              // explode fullname if needed
              if ($contact_name_split) {
                $contact_name = explode(" ", $list_val[$contact_list_name][0]);
              } else {
                $contact_name[0] = $list_val[$contact_list_name_first][0];
                $contact_name[1] = $list_val[$contact_list_name_last][0];
              }

              // check if email is provided in LDAP entry
              if (isset($contact_list_email)) {
                $contact_email = $list_val[$contact_list_email][0];
              } else {
                $contact_email = $list_val[$contact_list_uid][0] . $contact_suffix;
              }

              if ($lang_dir === 'ltr') { ?>
                <tr class="<?php echo ($i % 2) ? "contactsListEven" : "contactsListOdd" ?>">
                  <td class="contactNameFirst"><?php echo unhtmlentities($contact_name[0]); ?></td>
                  <td class="contactNameLast"><?php echo unhtmlentities($contact_name[1]); ?></td>
                  <td class="contactNickname"><?php echo unhtmlentities($list_val[$contact_list_uid][0]); ?></td>
                  <td class="contactEmail"><?php echo unhtmlentities($contact_email); ?></td>
                  <td><input type="button" name="Submit" id="<?php echo 'btn' . $i ?>" value="<?php echo unhtmlentities($html_add) ?>" class="button" onclick="toggleemail (document.getElementById('<?php echo 'btn' . $i ?>'), '<?php echo trim($contact_email); ?>');toggle (document.getElementById('<?php echo 'btn' . $i ?>'));" /></td>
                </tr>
              <?php } else { ?>
                <tr class="<?php echo ($i % 2) ? "contactsListEven" : "contactsListOdd" ?>">
                  <td><input type="button" name="Submit" id="<?php echo 'btn' . $i ?>" value="<?php echo unhtmlentities($html_add) ?>" class="button" onclick="toggleemail (document.getElementById('<?php echo 'btn' . $i ?>'), '<?php echo trim($contact_email); ?>');toggle (document.getElementById('<?php echo 'btn' . $i ?>'));" /></td>
                  <td class="contactEmail"><?php echo unhtmlentities($contact_email); ?></td>
                  <td class="contactNickname"><?php echo unhtmlentities($list_val[$contact_list_uid][0]); ?></td>
                  <td class="contactNameLast"><?php echo unhtmlentities($contact_name[1]); ?></td>
                  <td class="contactNameFirst"><?php echo unhtmlentities($contact_name[0]); ?></td>
                </tr> <?php } // ltr/rtl end
                    $i++;
                  }
                }

                if ($contact_list['count'] == 0) {
                  print('<tr><td colspan="4">' . $html_contact_none  . '</td></tr>');
                }
              } else {
                // bye bye!!!
                print('<a href="contacts.php?' . NVLL_Session::getUrlGetSession() . '">' . $html_back . '</a>');
              }
            } ?>
    </table>
    <?php
    echo '<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />';
    echo '<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />';
    echo '<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />';
    echo '<div style="text-align:center;"><a href="#top">Top</a></div>' . "\n";
    ?>
  </div>
</body>

</html>