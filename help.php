<?php

/**
 * Help
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

require_once dirname(__FILE__) .  '/common.php';

$lang = $_SESSION['nvll_lang'];
$theme = new NVLL_Theme($_SESSION['nvll_theme']);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $lang ?>" lang="<?php echo $lang ?>">

<head>
  <title>Non-Violable Liberty Layers | Webmail | <?php echo $html_help ?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <link href="<?php echo $theme->getStylesheet(); ?>" rel="stylesheet" type="text/css" />
  <link href="<?php echo $theme->getPrintStylesheet(); ?>" rel="stylesheet" media="print" type="text/css" />
  <link href="<?php echo $theme->getFavicon(); ?>" rel="shortcut icon" type="image/x-icon" />
  <script src="scripts/index.js" type="text/javascript"></script>
</head>

<body dir="<?php echo $lang_dir; ?>">

</body>

</html>