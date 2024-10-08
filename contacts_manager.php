<?php
/**
 * Manage contacts
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

require_once dirname(__FILE__) .  '/classes/NVLL_Contacts.php';

require_once dirname(__FILE__) .  '/common.php';
require_once dirname(__FILE__) .  '/functions/proxy.php';

header("Content-type: text/html; Charset=UTF-8");

try {
    $pop = new NVLL_IMAP();
} catch(Exception $ex) {
    //TODO: Show error without NVLL_Exception!
    $ev = new NVLL_Exception($ex->getMessage());
    require dirname(__FILE__) . '/html/header.php';
    require dirname(__FILE__) . '/html/error.php';
    require dirname(__FILE__) . '/html/footer.php';
    exit;
}

$pop->close();
$theme = new NVLL_Theme($_SESSION['nvll_theme']);
// Load the contact list
$path = $conf->prefs_dir . '/' . preg_replace("/(\\\|\/)/", "_", NVLL_Session::getUserKey()) . '.contacts';
$contactlists = array();
$contacts = NVLL_Contacts::loadList($path, $contactlists);
$all_lists = array();
$query_str = NVLL_Session::getUrlQuery();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $lang ?>" lang="<?php echo $lang ?>">
<head>
  <title>Non-Violable Liberty Layers | Webmail - <?php echo i18n_message($html_contact_list, $_SESSION['nvll_user']); ?></title>
  <link href="<?php echo $theme->getStylesheet(); ?>" rel="stylesheet" type="text/css" />
  <link href="<?php echo $theme->getFavicon(); ?>" rel="shortcut icon" type="image/x-icon" />
  <meta content="text/html; charset=UTF-8" http-equiv="Content-Type" />
  <script type="text/javascript">
    function prompt_delete (email, id) {
      if (confirm("<?php echo unhtmlentities($html_delete) ?> `" + email + "' <?php echo unhtmlentities($html_contact_del) ?> ?")) {
        var url = '<?php echo "contacts_manager.php?" . $query_str . "&" . NVLL_Session::getUrlGetSession() ?>&service=delete&id=' + id;
        document.location.href = url;
      }
    }
  </script>
</head>

<body id="popup" dir="<?php echo $lang_dir; ?>"><a name="top"></a>
  <?php if (!isset($conf->contact_number_max) || $conf->contact_number_max == 0) { ?>
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
    <?php
    exit;
}

$service = isset($_GET['service']) ? $_GET['service'] : '';
switch ($service) {
    case "add_prompt":
        if (isset($_GET['id'])) {
            //$tab = array_pad(explode("\t", $contacts[$_GET['id']]), -4, "");
            $tab = $contacts[$_GET['id']];
        } ?>

    <div class="contactAdd">
        <form id="form2" method="POST" action="<?php echo "contacts_manager.php?" . NVLL_Session::getUrlGetSession() . "&" . $query_str ?>&amp;service=add">
            <table>
                <tr>
                <td colspan="2" class="contactsTitle">
                    <?php echo i18n_message($html_contact_list, $_SESSION['nvll_user']); ?>
                </td>
                </tr>
                <tr>
                <?php if (!isset($_GET['modif'])) $_GET['modif'] = false; ?>
                <td colspan="2" class="contactsSubTitle">
                    <?php echo ($_GET['modif']) ? $html_contact_mod : $html_contact_add ?>
                </td>
                </tr>
                <?php if (count($contacts) < $conf->contact_number_max || $_GET['modif']) { ?>
                <?php if ($tab[5] == 0) { ?>
                    <tr>
                    <td class="contactsAddLabel"><label for="first"><?php echo convertLang2Html($html_contact_first) ?>:</label></td>
                    <td class="contactsAddData"><input class="button" name="first" type="text" id="first" value="<?php if (isset($tab[0])) {
                            echo htmlspecialchars($tab[0], ENT_COMPAT | ENT_SUBSTITUTE);
                        } ?>"/>
                    </td>
                    </tr>
                <?php } if ($tab[5] == 0) { ?>
                <tr>
                <td class="contactsAddLabel"><label for="last"><?php echo convertLang2Html($html_contact_last) ?>:</label></td>
                <td class="contactsAddData"><input class="button" name="last" type="text" id="last" value="<?php if (isset($tab[1])) {
                        echo htmlspecialchars($tab[1], ENT_COMPAT | ENT_SUBSTITUTE);
                    } ?>"/>
                </td>
                </tr> <?php } else { ?>
                <tr>
                <td class="contactsAddLabel"><label for="last"><?php echo convertLang2Html($html_contact_listname) ?>:</label></td>
                <td class="contactsAddData"><input class="button" name="last" type="text" id="last" value="<?php if (isset($tab[1])) {
                        echo htmlspecialchars($tab[1], ENT_COMPAT | ENT_SUBSTITUTE);
                    } ?>"/>
                </td>
                </tr> <?php } if ($tab[5] == 0) { ?>
                <tr>
                <td class="contactsAddLabel"><label for="nick"><?php echo convertLang2Html($html_contact_nick) ?>:</label></td>
                <td class="contactsAddData"><input class="button" name="nick" type="text" id="nick" value="<?php if (isset($tab[2])) {
                            echo htmlspecialchars($tab[2], ENT_COMPAT | ENT_SUBSTITUTE);
                    } ?>"/>
                </td>
                </tr> <?php } ?>
                <?php if ($tab[5] == 0) { ?>
                <tr>
                <td class="contactsAddLabel"><label for="email"><?php echo convertLang2Html($html_contact_mail) ?>:</label></td>
                <td class="contactsAddData"><input class="button" name="email" type="text" id="email" value="<?php if (isset($tab[3])) {
                        echo htmlspecialchars($tab[3], ENT_COMPAT | ENT_SUBSTITUTE);
                    } ?>"/></td>
                </tr> <?php } else {
                    echo '<tr>';
                    echo '<td class="contactsAddLabel"><label for="email">' . convertLang2Html($html_contact_mail) . ':</label></td>';
                    $all_emails = array();
                    semisplit_address_list($tab[3], $all_emails, $sep = ';');
                    for ($j = 0;$j < count($all_emails);$j++) {
                        $j == 0 ? $tab[3] = $all_emails[$j] : $tab[3] = $tab[3] . "\n" . $all_emails[$j];
                    }
                    $tab[3] = $tab[3] . "\n";
                    echo '<td class="contactsAddData"><input type="hidden" name="isList" value="isList" /><textarea class="button" name="email" type="textarea" id="email" cols="70" rows="10">' . htmlspecialchars($tab[3], ENT_COMPAT | ENT_SUBSTITUTE) . '</textarea></td>';
                    echo '</tr>';
                } ?>
                <tr>
                <td colspan="2" class="prefsSubmitButtonsRight">
                    <input type="button" name="Submit2" value="<?php echo convertLang2Html($html_cancel) ?>" class="button" onclick="self.history.go (-1);"/>
                    <input type="hidden" name="modif" value="<?php echo $_GET['modif'] ?>"/>
                    <input type="hidden" name="id" value="<?php if (!isset($_GET['id'])) {
                            echo '0';
                        } else {
                            echo $_GET['id'];
                        } ?>"/>
                    <input type="submit" name="Submit4" value="<?php echo ($_GET['modif']) ? convertLang2Html($html_modify) : convertLang2Html($html_add) ?>" class="button"/></td>
                </tr>
                <?php } else { ?>
                <tr>
                    <td>
                        <div class="error">
                        <table class="errorTable">
                            <tr class="errorTitle">
                            <td><?php echo convertLang2Html($html_error_occurred) ?></td>
                            </tr>
                            <tr class="errorText">
                            <td>
                                <p><?php echo i18n_message($html_contact_err1, $conf->contact_number_max) ?></p>
                                <p><?php echo convertLang2Html($html_contact_err2) ?>.</p>
                                <p><a href="contacts_manager.php<?php echo "?" . NVLL_Session::getUrlGetSession(); ?>"><?php echo $html_back; ?></a></p>
                            </td>
                            </tr>
                        </table>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </form>
    </div>
  <?php
        break;
    case "addlist":
        $listname = '';
        if (isset($_POST['addlist']) && strlen($_POST['addlist']) > 0 && isset($_POST['listname']) && strlen($_POST['listname']) > 0) {
            $listname = trim($_POST['listname']);
        } elseif (isset($_POST['addlist2']) && strlen($_POST['addlist2']) > 0 && isset($_POST['listname2']) && strlen($_POST['listname2']) > 0) {
            $listname = trim($_POST['listname2']);
        }

        $listname = str_replace('\t', '', $listname);
        $listname = stripslashes($listname);
        $listname = preg_replace('/[\'"<>]/', '', $listname);
        $modify_listids = array();

        if (strlen($listname) > 0 && isset($_POST['emails4list']) && is_array($_POST['emails4list']) && count($_POST['emails4list']) > 0) {
            for ($i = 0;$i < count($contacts);$i++) {
                if ($listname == $contacts[$i][1]) {
                    $modify_listids[] = $i;
                }
            }

            $new_email_list = '';
            foreach ($_POST['emails4list'] as $email) $new_email_list = $new_email_list . '; ' . $email;

            $new_email_list = preg_replace('/^; /', '', $new_email_list);
            if (count($modify_listids) == 0) {
                $line = array('', $listname, '', $new_email_list, '', 1);
                array_push($contacts, $line);
                NVLL_Contacts::saveList($path, $contacts, $conf, $ev);
                if (NVLL_Exception::isException($ev)) {
                    require dirname(__FILE__) . '/html/error.php';
                    require dirname(__FILE__) . '/html/footer.php';
                    break;
                }
                $contacts = NVLL_Contacts::loadList($path);
            } else {
                foreach ($modify_listids as $listid) {
                    $old_email_list = $contacts[$listid][3];
                    $new_email_list = $old_email_list . '; ' . $new_email_list;
                    //remove duplicated entries
                    $new_emails = array();
                    semisplit_address_list($new_email_list, $new_emails, ';');
                    $new_email_list = '';
                    $unique_emails = array();

                    foreach ($new_emails as $email) {
                        if (!isset($unique_emails[trim($email) ])) {
                            $new_email_list = $new_email_list . '; ' . $email;
                            $unique_emails[trim($email) ] = true;
                        }
                    }

                    $new_email_list = preg_replace('/^; /', '', $new_email_list);
                    $line = array(
                        '',
                        $listname,
                        '',
                        $new_email_list,
                        '',
                        1
                    );
                    $contacts[$listid] = $line;
                }
                NVLL_Contacts::saveList($path, $contacts, $conf, $ev);
                if (NVLL_Exception::isException($ev)) {
                    require dirname(__FILE__) . '/html/error.php';
                    require dirname(__FILE__) . '/html/footer.php';
                    break;
                }
                $contacts = NVLL_Contacts::loadList($path);
            }
        }
        echo '<script type="text/javascript">self.location.href="contacts_manager.php?' . NVLL_Session::getUrlGetSession() . '&' . $query_str . '";</script>';
        break;

    case "add":
        if (!empty($_POST['email'])) {
            // The following foreach block performs some sanity checking and
            // cleanup.
            foreach (array('first', 'last', 'nick', 'email') as $contact_element) {
                //We should strip slashes here// Assume magic quotes are off, manually apply stripslashes if needed
                $_POST[$contact_element] = stripslashes($_POST[$contact_element]);
                // Strip tabs that COULD be inserted into fields(causing corrupted
                // DB)
                $_POST[$contact_element] = str_replace('\t', '', $_POST[$contact_element]);
                //Maybe more sanity checking needs to be done???
                if (!isset($_POST['isList']) || $contact_element != 'email') {
                    //dont allow "<>, as it corrupts html outpout
                    $_POST[$contact_element] = preg_replace('/[\'"<>]/', '', $_POST[$contact_element]);
                }
            }
            //email should only be xxx@xxx.xx.xx
            $isList = 0;
            if (!isset($_POST['isList'])) {
                $matches = array();
                if (preg_match("/^.*(\S+?@\S+?).*/U", $_POST['email'], $matches)) {
                    $_POST['email'] = $matches[1];
                }
            } else {
                $_POST['email'] = preg_replace('/\s*\n\s*/', '; ', trim($_POST['email']));
                $_POST['first'] = '';
                $_POST['nick'] = '';
                $isList = 1;
            }

            if (count($contacts) < $conf->contact_number_max && empty($_POST['modif'])) {
                //$line = $_POST['first'] . "\t" . $_POST['last'] . "\t" . $_POST['nick'] . "\t" . $_POST['email'];
                $line = array($_POST['first'], $_POST['last'], $_POST['nick'], $_POST['email'], '', $isList);
                array_push($contacts, $line);
                NVLL_Contacts::saveList($path, $contacts, $conf, $ev);

                if (NVLL_Exception::isException($ev)) {
                    require dirname(__FILE__) . '/html/error.php';
                    require dirname(__FILE__) . '/html/footer.php';
                    break;
                }
            }

            if (!empty($_POST['modif'])) {
                if (isset($_POST['id']) && isset($contacts[$_POST['id']])) {
                    //$line = $_POST['first'] . "\t" . $_POST['last'] . "\t" . $_POST['nick'] . "\t" . $_POST['email'];
                    $line = array($_POST['first'], $_POST['last'], $_POST['nick'], $_POST['email'], '', $isList);
                    $contacts[$_POST['id']] = $line;
                    NVLL_Contacts::saveList($path, $contacts, $conf, $ev);
                    if (NVLL_Exception::isException($ev)) {
                        require dirname(__FILE__) . '/html/error.php';
                        require dirname(__FILE__) . '/html/footer.php';
                        break;
                    }
                }
            }
            $contacts = NVLL_Contacts::loadList($path);
        } else {
            echo "<script type=\"text/javascript\">alert (\"Error : Email Field is empty.\");</script>";
            echo "<script type=\"text/javascript\">self.history.go (-1)</script>";
        } ?>
  <script type="text/javascript">self.location.href="<?php echo "contacts_manager.php?" . NVLL_Session::getUrlGetSession() . "&" . $query_str ?>";</script>
  <?php ;
        break;

    case "delete":
        $new_contacts = array();
        for ($i = 0;$i < count($contacts);++$i) if ($_GET['id'] != $i) $new_contacts[] = $contacts[$i];
        NVLL_Contacts::saveList($path, $new_contacts, $conf, $ev);
        if (NVLL_Exception::isException($ev)) {
            require dirname(__FILE__) . '/html/error.php';
            require dirname(__FILE__) . '/html/footer.php';
            break;
        }
        $contacts = NVLL_Contacts::loadList($path);;

    default:
        // Default show the contacts
        $show_lists_only = false;
        if (isset($_GET['listonly']) && $_GET['listonly'] == 1)  {
            $show_lists_only = true;
        }
        if (count($contacts) > 10) {
            $count2list = array();
            $all_rulers = array();
            $ruler_top = '<a href="#top">&nbsp;&nbsp;' . convertLang2Html($html_contact_ruler_top) . '&nbsp;&nbsp;</a>-';
            if ($show_lists_only) {
                $ruler_listonly = '-<a href="contacts_manager.php?' . NVLL_Session::getUrlGetSession() . '">&nbsp;&nbsp;' . convertLang2Html($html_contact_all) . '&nbsp;&nbsp;</a>';
            } else {
                $ruler_listonly = '-<a href="contacts_manager.php?' . NVLL_Session::getUrlGetSession() . '&listonly=1">&nbsp;&nbsp;' . convertLang2Html($html_contact_listonly) . '&nbsp;&nbsp;</a>';
            }
            NVLL_Contacts::create_rulers($contacts, $ruler_top, $ruler_listonly, $all_rulers, $count2list, $show_lists_only);
        }
        if (count($contacts) < $conf->contact_number_max) {
            echo '<form id="addlistForm" method="POST" action="contacts_manager.php?' . NVLL_Session::getUrlGetSession() . '&service=addlist">';
            echo '<p class="contactsAddLink">';
            echo '<input class="button" type="textbox" name="listname" id="listname" value="" /><input class="button" type="submit" name="addlist" id="addlist" value="' . convertLang2Html($html_contact_list_add) . '" onclick="return check_list(\'listname\');" />';
            echo '<a style="padding-left:100px;" href="contacts_manager.php?service=add_prompt&amp;' . $query_str . '&' . NVLL_Session::getUrlGetSession() . '">' . convertLang2Html($html_contact_add) . '</a>';
        }
        else {
            echo '<p class="contactsAddLink">';
            echo i18n_message($html_contact_err1, $conf->contact_number_max) . convertLang2Html($html_contact_err2);
        }

        echo '</p>';
        echo '<div class="contactsList">';
        echo '<table>';

        $header = '<tr class="contactsListHeader">' . '<th></th>' . '<th nowrap>' . convertLang2Html($html_contact_first) . '</th>' . '<th nowrap>' . convertLang2Html($html_contact_last) . ' / ' . convertLang2Html($html_contact_listname) . '</th>' . '<th nowrap>' . convertLang2Html($html_contact_nick) . '</th>' . '<th nowrap>' . convertLang2Html($html_contact_mail) . '</th>' . '<th colspan="2">&nbsp;</th>' . '</tr>';
        if (count($contacts) <= 10) {
            echo $header;
        }

        $ruler_count = 0;
        for ($i = 0;$i < count($contacts);++$i) {
            //$tab = array_pad(explode("\t", $contacts[$i]), -4, "");
            $tab = $contacts[$i];
            $checkbox_value = $tab[3];

            if ($tab[5] == 0) {
                if (strlen($tab[0]) > 0 && strlen($tab[1]) > 0) {
                    $checkbox_value = '"' . $tab[0] . ' ' . $tab[1] . '" <' . $checkbox_value . '>';
                } elseif (strlen($tab[0]) == 0 && strlen($tab[1]) > 0) {
                    $checkbox_value = '"' . $tab[1] . '" <' . $checkbox_value . '>';
                } elseif (strlen($tab[0]) > 0 && strlen($tab[1]) == 0) {
                    $checkbox_value = '"' . $tab[0] . '" <' . $checkbox_value . '>';
                }
            }

            $checkbox_value = htmlspecialchars($checkbox_value, ENT_COMPAT | ENT_SUBSTITUTE);
            if (count($contacts) > 10) {
                if (isset($count2list[strval($i) ])) {
                    echo $all_rulers[$ruler_count];
                    $ruler_count++;
                    echo $header;
                }
            }

            if ($tab[5] == 1) {
                //its a list of emails
                $all_emails = array();
                semisplit_address_list($tab[3], $all_emails, $sep = ';');
                $list_count = min(3, count($all_emails));
                for ($j = 0;$j < $list_count;$j++) {
                    $j == 0 ? $tab[3] = $all_emails[$j] : $tab[3] = $tab[3] . '; ' . $all_emails[$j];
                }
                if (count($all_emails) >= 3) {
                    $tab[3] = $tab[3] . '; ...';
                }
                $all_lists[] = htmlspecialchars($tab[1], ENT_COMPAT | ENT_SUBSTITUTE);
            }

            if ($tab[5] == 1 || !$show_lists_only) { ?>
                <tr class="<?php echo ($i % 2) ? "contactsListEven" : "contactsListOdd" ?>">
                    <td><input type="checkbox" value="<?php echo $checkbox_value; ?>" name="emails4list[]" title="<?php echo convertLang2Html($html_contact_listcheck_title); ?>" /></td>
                    <td><?php echo ($tab[0]) ? htmlspecialchars($tab[0], ENT_COMPAT | ENT_SUBSTITUTE) : "&nbsp;"; ?></td>
                    <td><?php if ($tab[5] == 1) {
                            echo '<span onmouseover="this.style.cursor=\'pointer\'" onmouseout="this.style.cursor=\'default\'" onclick="document.getElementById(\'listname\').value=\'' . htmlspecialchars($tab[1], ENT_COMPAT | ENT_SUBSTITUTE) . '\';document.getElementById(\'listname2\').value=\'' . htmlspecialchars($tab[1], ENT_COMPAT | ENT_SUBSTITUTE) . '\';">';
                        }
                        echo ($tab[1]) ? htmlspecialchars($tab[1], ENT_COMPAT | ENT_SUBSTITUTE) : "&nbsp;";
                        if ($tab[5] == 1) {
                            echo '</span>';
                        } ?>
                    </td>
                    <td><?php echo ($tab[2]) ? htmlspecialchars($tab[2], ENT_COMPAT | ENT_SUBSTITUTE) : "&nbsp;"; ?></td>
                    <td><?php echo htmlspecialchars($tab[3], ENT_COMPAT | ENT_SUBSTITUTE); ?></td>
                    <td>
                    <input type="button" name="Submit5" value="<?php echo $html_modify ?>" class="button" onclick="self.location.href='<?php echo "contacts_manager.php?" . NVLL_Session::getUrlGetSession() . "&" . $query_str ?>&amp;service=add_prompt&amp;id=<?php echo $i ?>&amp;modif=1'"/>
                    </td>
                    <td>
                    <input type="button" name="Submit" value="<?php echo $html_delete ?>" class="button" onclick="prompt_delete ('<?php echo ($tab[5] == 0) ? $tab[3] : $tab[1]; ?>', <?php echo $i ?>)"/>
                    </td>
                </tr>
            <?php }
        } ?>
    </table>
  </div>
  <p class="contactsAddLink">
    <?php if (count($contacts) < $conf->contact_number_max) {
            // echo '<input class="button" type="textbox" name="listname2" id="listname2" value="" /><input class="button" type="submit" name="addlist2" id="addlist2" value="' . convertLang2Html($html_contact_list_add) . '" onclick="return check_list(\'listname2\');" />';
            // echo '<a style="padding-left:100px;" href="contacts_manager.php?service=add_prompt&amp;' . $query_str . '&' . NVLL_Session::getUrlGetSession() . '">' . convertLang2Html($html_contact_add) . '</a></p>';
        } else {
            echo i18n_message($html_contact_err1, $conf->contact_number_max) . convertLang2Html($html_contact_err2) . '</p>';
        }

        if (count($contacts) < $conf->contact_number_max) echo '</form>';
        
        echo '<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />' . "\n";
        echo '<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />' . "\n";
        echo '<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />' . "\n";
        echo '<div style="text-align:center;"><a href="#top">Top</a></div>' . "\n";
    ?>
  <?php } // switch
    ?>
  <script type="text/javascript">
	function check_list(id) {
		var name="";
		name=document.getElementById(id).value;

		if (name.length==0 ) return false;

		var all=[<?php foreach ($all_lists as $list_name) echo '"' . $list_name . '",' . "\n"; echo '""' . "\n"; ?>];
		if (all.indexOf(name)>=0 ) return confirm(<?php echo "'" . convertLang2Html($html_contact_add_confirm) . "'"; ?>);

		return true;
	}
  </script>
</body>
</html>
