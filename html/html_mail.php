<div class="mailNav">
  <table class="head">
    <?php
    if (!isset($conf->loaded)) die('Hacking attempt');

    $url_session = NVLL_Session::getUrlGetSession();
    $original = (isset($_REQUEST['original']) && $_REQUEST['original'] == 1) ? '1' : '0';
    $verbose = (isset($_REQUEST['verbose']) && $_REQUEST['verbose'] == 1) ? '1' : '0';
    $as_html = (isset($_REQUEST['as_html']) && $_REQUEST['as_html'] == 1) ? '1' : '0';
    $display_images = (isset($_REQUEST['display_images']) && $_REQUEST['display_images'] == 1) ? '1' : '0';
    $has_images = NVLL_Security::hasDisabledHtmlImages($content['body']);

    if ($original === '0' && $conf->use_verbose) {
      if ($verbose === '1') {
        // Display verbose header
        echo '<tr><td colspan="2">';
        echo '<pre class="mailVerboseHeader">' . htmlspecialchars(trim($content['header']), ENT_COMPAT | ENT_SUBSTITUTE) . '</pre>';
        echo '</td></tr>';
        if ($content['att'] != '') echo $content['att'];
      } else {
        // Display normal header
        echo '<tr><th class="mailHeaderLabel">' . $html_from_label . '</th><td class="mailHeaderData">' . htmlspecialchars($content['from'], ENT_COMPAT | ENT_SUBSTITUTE) . '</td></tr>';

        if (NVLL_MailAddress::compareAddress($content['from'], $content['reply_to']) == 0) {
          echo '<tr><th class="mailHeaderLabel">' . $html_reply_to_label . '</th><td class="mailHeaderData">' . htmlspecialchars($content['reply_to'], ENT_COMPAT | ENT_SUBSTITUTE) . '</td></tr>';
        }

        if ($content['to'] != '') {
          echo '<tr><th class="mailHeaderLabel">' . $html_to_label . '</th><td class="mailHeaderData">' . htmlspecialchars($content['to'], ENT_COMPAT | ENT_SUBSTITUTE) . '</td></tr>';
        }

        if ($content['cc'] != '') {
          echo '<tr><th class="mailHeaderLabel">' . $html_cc_label . '</th><td class="mailHeaderData">' . htmlspecialchars($content['cc'], ENT_COMPAT | ENT_SUBSTITUTE) . '</td></tr>';
        }

        echo '<tr><th class="mailHeaderLabel">' . $html_subject_label . '</th><td class="mailHeaderData">' . htmlspecialchars($content['subject'] ?: $html_nosubject, ENT_COMPAT | ENT_SUBSTITUTE) . '</td></tr>';

        if (isset($content['flagged']) && $content['flagged']) {
          echo '<tr><th class="mailHeaderLabel">' . $html_status . '</th><td class="mailHeaderData"><span style="color: red; font-weight: bold;">' . $html_flagged  . '</span></td></tr>';
        }

        echo '<tr><th class="mailHeaderLabel">' . $html_date_label . '</th><td class="mailHeaderData">' . $content['complete_date'] . '</td></tr>';

        $priority = '';
        switch ($content['priority']) {
          case 1:
            $priority = $html_highest;
            break;
          case 2:
            $priority = $html_high;
            break;
          case 4:
            $priority = $html_low;
            break;
          case 5:
            $priority = $html_lowest;
            break;
        }

        if ($priority != '') echo '<tr><th class="mailHeaderLabel">' . $html_priority_label . '</th><td class="mailHeaderData">' . $priority . '</td></tr>';
        if ($content['att'] != '') echo $content['att'];

        // Encoding form
        echo '<tr><th class="mailHeaderLabel">' . $html_encoding_label . '</th><td class="mailHeaderData">';
        echo '<form method="POST" action="api.php?' . $url_session . '&charset=1' . '" id="encoding"><div>';
        echo '<input type="hidden" name="service" value="' . $_REQUEST['service'] . '"/>';
        echo '<input type="hidden" name="mail" value="' . $_REQUEST['mail'] . '"/>';
        echo '<input type="hidden" name="verbose" value="' . $_REQUEST['verbose'] . '"/>';
        echo '<select class="button" name="user_charset">';

        $group = '';
        $optgroupOpen = false;

        foreach ($charset_array as $charset) {
          if ($charset->group != $group) {
            if ($optgroupOpen) echo '</optgroup>';
            if ($charset->group != '') {
              echo '<optgroup label="' . $charset->group . '">';
              $optgroupOpen = true;
            }
            $group = $charset->group;
          }

          $selected = (isset($_REQUEST['user_charset']) && $_REQUEST['user_charset'] == $charset->charset)
            || ((!isset($_REQUEST['user_charset']) || $_REQUEST['user_charset'] == '') && strtolower($content['charset']) == strtolower($charset->charset));

          echo '<option value="' . $charset->charset . '"' . ($selected ? ' selected="selected"' : '') . '>' . $charset->label . '</option>';
        }

        if ($optgroupOpen) echo '</optgroup>';

        echo '</select>&nbsp;&nbsp;<input name="submit" class="button" type="submit" value="' . $html_submit . '" />';
        echo '</div></form>';
        echo '</td></tr>';
      }
    }
    ?>
  </table>

  <table class="menu">
    <?php
    // Common URL parameters
    $currentUrl = 'api.php?' . NVLL_Session::getUrlGetSession() . '&service=aff_mail';

    echo '<tr>';
    // Show/hide header link
    if ($conf->use_verbose && $original === '0') {
      $currentParams = [
        'mail' => $content['msgnum'],
        'as_html' => $as_html,
        'original' => $original,
        'display_images' => $display_images
      ];

      if ($verbose == '1') {
        $params = array_merge($currentParams, ['verbose' => '0']);
        $headerLink = "<a href=\"$currentUrl&" . NVLL_Request::Params($params) . "\">$html_remove_header</a>";
        echo "<td class=\"mailSwitchHeaders dontPrint\" style=\"display:flex;\">$headerLink";
      } else {
        $params = array_merge($currentParams, ['verbose' => '1']);
        $headerLink = "<a href=\"$currentUrl&" . NVLL_Request::Params($params) . "\">$html_view_header</a>";
        echo "<td class=\"mailSwitchHeaders dontPrint\">$headerLink";
      }

      // View as HTML/Plain text link
      if ($content['body_mime'] == 'text/html') {
        if ($as_html == '1' || isset($_REQUEST['charset'])) {
          $params = array_merge($currentParams, ['verbose' => $verbose, 'as_html' => '0']);
          echo "&nbsp;|&nbsp;<a href=\"$currentUrl&" . NVLL_Request::Params($params) . "\">$html_view_as_plain</a>";
        } else {
          $params = array_merge($currentParams, ['verbose' => $verbose, 'as_html' => '1']);
          echo "&nbsp;|&nbsp;<a href=\"$currentUrl&" . NVLL_Request::Params($params) . "\">$html_view_as_html</a>";
        }
      }

      echo "</td>";
    } else {
      echo '<td>&nbsp;</td>';
    }

    // Next/prev message links
    $currentParams = [
      'verbose' => $verbose,
      'as_html' => $as_html,
      'original' => $original,
      'display_images' => $display_images
    ];

    if ($content['prev'] !== '' && $content['prev'] !== 0) {
      $params = array_merge($currentParams, ['mail' => $content['prev']]);
      $prevLink = "<a href=\"$currentUrl&" . NVLL_Request::Params($params) . "\" title=\"$title_prev_msg\" rel=\"prev\">&laquo; $alt_prev</a>";
    } else {
      $prevLink = '';
    }

    if ($content['next'] !== '' && $content['next'] !== 0) {
      $params = array_merge($currentParams, ['mail' => $content['next']]);
      $nextLink = "<a href=\"$currentUrl&" . NVLL_Request::Params($params) . "\" title=\"$title_next_msg\" rel=\"next\">$alt_next &raquo;</a>";
    } else {
      $nextLink = '';
    }

    $separator = ($prevLink && $nextLink) ? '&nbsp;' : '';

    echo "<td class=\"right dontPrint\">$prevLink$separator$nextLink</td>";
    echo '</tr>';
    ?>
  </table>
</div>
<?php if (($has_images || $rfc822_hasImages) && $display_images != 1) {
  $params = [
    'mail' => $content['msgnum'],
    'verbose' => $verbose,
    'as_html' => '1',
    'original' => $original,
    'display_images' => '1'
  ];
  echo '<div class="nopic">';
  echo $html_images_warning;
  echo '<br/>';
  echo '<a href="' . $currentUrl . '&' . NVLL_Request::Params($params) . '">' . $html_images_display . '</a>';
  echo '</div>';
}
if ($content['spam']) echo '<div class="spamWarning">' . $html_spam_warning . '</div>'; ?>
<div class="mailData">
  <?php if ($original === '0') {
    echo '<div class="mail">' . $content['body'] . '</div>';
  } else {
    echo '<div class="mail"><pre style="margin:0;white-space:pre-wrap;white-space:-moz-pre-wrap;white-space:-o-pre-wrap;word-wrap:break-word;">' .
      htmlspecialchars(trim($content['original']), ENT_COMPAT | ENT_SUBSTITUTE) .
      '</pre></div>';
  } ?>
</div>