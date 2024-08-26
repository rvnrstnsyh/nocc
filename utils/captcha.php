<?php

function get_db_instance(): PDO
{
  global $conf;
  static $db = null;

  if ($db !== null) return $db;

  try {
    $db = new PDO(
      'mysql:host=' . $conf->database_hostname . ';dbname=' . $conf->database_name,
      $conf->database_user,
      $conf->database_password,
      [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_PERSISTENT => $conf->database_persistent
      ]
    );
  } catch (PDOException $error) {
    http_response_code(500);
    die(_('No Connection to MySQL database'));
  }

  return $db;
}

function send_captcha(): void
{
  global $conf;

  if (!$conf->use_captcha || $conf->captcha_difficulty === 0 || !extension_loaded('gd')) return;

  $db = get_db_instance();
  $captchachars = $conf->captcha_characters ? $conf->captcha_characters : 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
  $length = strlen($captchachars) - 1;
  $code = '';

  for ($i = 0; $i < 5; ++$i) $code .= $captchachars[mt_rand(0, $length)];

  $randid = mt_rand();
  $time = time();
  $db->prepare('INSERT INTO captchas (id, time, code) VALUES (?, ?, ?);')->execute([$randid, $time, $code]);

  echo '<div class="row" style="display: flex;"><div class="col"><td style="display: flex;margin-top:5px;">';

  $width = $conf->captcha_difficulty === 3 ? 150 : 55;
  $height = $conf->captcha_difficulty === 3 ? 200 : 22;
  $im = imagecreatetruecolor($width, $height);
  $bg = imagecolorallocate($im, 0, 0, 128);
  $fg = imagecolorallocate($im, 255, 255, 255);

  imagefill($im, 0, 0, $bg);

  switch ($conf->captcha_difficulty) {
    case 1:
      imagestring($im, 5, 5, 5, $code, $fg);
      break;
    case 2:
      imagestring($im, 5, 5, 5, $code, $fg);
      $line = $fg;
      for ($i = 0; $i < 2; ++$i) {
        imageline($im, 0, mt_rand(0, 24), 55, mt_rand(0, 24), $line);
      }
      for ($i = 0; $i < 100; ++$i) {
        imagesetpixel($im, mt_rand(0, 55), mt_rand(0, 24), $fg);
      }
      break;
    case 3:
      $chars = [];
      for ($i = 0; $i < 10; ++$i) {
        do {
          $x = mt_rand(10, 140);
          $y = mt_rand(10, 180);
        } while (array_reduce($chars, fn($carry, $char) => $carry || (abs($char['x'] - $x) < 25 && abs($char['y'] - $y) < 25), false));

        $chars[] = ['x' => $x, 'y' => $y];
        imagechar($im, 5, $x, $y, $i < 5 ? $captchachars[mt_rand(0, $length)] : $code[$i - 5], $fg);
      }

      $follow = imagecolorallocate($im, 200, 0, 0);
      imagearc($im, $chars[5]['x'] + 4, $chars[5]['y'] + 8, 16, 16, 0, 360, $follow);

      for ($i = 5; $i < 9; ++$i) {
        imageline($im, $chars[$i]['x'] + 4, $chars[$i]['y'] + 8, $chars[$i + 1]['x'] + 4, $chars[$i + 1]['y'] + 8, $follow);
      }
      for ($i = 0; $i < 5; ++$i) {
        imageline($im, 0, mt_rand(0, 200), 150, mt_rand(0, 200), $fg);
      }
      for ($i = 0; $i < 1000; ++$i) {
        imagesetpixel($im, mt_rand(0, 150), mt_rand(0, 200), $fg);
      }
      break;
    default:
      imagestring($im, 5, 5, 5, $code, $fg);
      $line = $fg;
      for ($i = 0; $i < 2; ++$i) {
        imageline($im, 0, mt_rand(0, 24), 55, mt_rand(0, 24), $line);
      }
      for ($i = 0; $i < 100; ++$i) {
        imagesetpixel($im, mt_rand(0, 55), mt_rand(0, 24), $fg);
      }
      break;
  }

  ob_start();
  imagegif($im);
  imagedestroy($im);
  echo '<img alt="" width="' . $width . '" height="' . $height . '" src="data:image/gif;base64,' . base64_encode(ob_get_clean()) . '">';
  echo '</div><div class="col"><input type="hidden" name="challenge" value="' . $randid . '"><input type="text" name="captcha" placeholder="CAPTCHA" size="6" autocomplete="off" style="height: 16px;" required></div></div>';
}

function verify_captcha(string $challenge, string $captcha_code): bool
{
  global $conf;

  if (!$conf->use_captcha || $conf->captcha_difficulty === 0) return true;
  if (empty($challenge)) return false;

  $db = get_db_instance();
  $stmt = $db->prepare('SELECT code FROM captchas WHERE id = ?');
  $stmt->execute([$challenge]);
  $result = $stmt->fetch(PDO::FETCH_COLUMN);

  if (!$result) return false;

  $code = $result;

  // Delete the used captcha and expired ones
  $db->prepare('DELETE FROM captchas WHERE id = ? OR time < ?')->execute([$challenge, time() - 600]);

  if ($captcha_code === $code) return true;
  // Special case for difficulty level 3
  if ($conf->captcha_difficulty === 3 && strrev($captcha_code) === $code) return true;

  return false;
}
