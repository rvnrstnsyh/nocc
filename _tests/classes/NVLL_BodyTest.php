<?php

/**
 * Test cases for NVLL_Body.
 *
 * Copyright 2010-2011 Tim Gerundt <tim@gerundt.de>
 * Copyright 2024 Rivane Rasetiansyah <re@nvll.me>
 *
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

require_once dirname(__FILE__) . '/../../classes/NVLL_Body.php';
require_once dirname(__FILE__) . '/../../classes/NVLL_Session.php';

/**
 * Test class for NVLL_Body.
 */
class NVLL_BodyTest extends PHPUnit\Framework\TestCase
{
        /**
         * Test case for prepareHtmlLinks().
         */
        public function testPrepareHtmlLinks()
        {
                $actual =
                        'This is a test mail with URLs:
                        * <a href="http://nvll.sf.net/">NVLL</a>
                        * <A HREF="http://nvll.sf.net/?lang=de">NVLL German</A>
                        * <a href="http://nvll.sourceforge.net/docs/changelog.php">NVLL ChangeLog</a>
                        * <a href="mailto:nvll-discuss@lists.sourceforge.net">Mailing list</a>
                        * <A HREF="MAILTO:nvll-discuss@lists.sourceforge.net">Mailing list</A>';

                $expected =
                        'This is a test mail with URLs:
                        * <a href="http://nvll.sf.net/" target="_blank">NVLL</a>
                        * <A href="http://nvll.sf.net/?lang=de" target="_blank">NVLL German</A>
                        * <a href="http://nvll.sourceforge.net/docs/changelog.php" target="_blank">NVLL ChangeLog</a>
                        * <a href="action.php?_nvkey=PHPSESSID&amp;action=write&amp;mail_to=nvll-discuss@lists.sourceforge.net">Mailing list</a>
                        * <A href="action.php?_nvkey=PHPSESSID&amp;action=write&amp;mail_to=nvll-discuss@lists.sourceforge.net">Mailing list</A>';

                $this->assertEquals($expected, NVLL_Body::prepareHtmlLinks($actual, 'http://localhost/retrmail/'));
        }

        /**
         * Test case for prepareTextLinks().
         */
        public function testPrepareTextLinks()
        {
                $actual =
                        'This is a test mail with URLs:
                        * http://nvll.sf.net/
                        * http://nvll.sf.net/?lang=de
                        * http://nvll.sourceforge.net/docs/changelog.php
                        * http://localhost/test1.php#anchor
                        * http://localhost/test2.php?para1=abc&para2=def
                        * http://localhost/trac/ticket/123#comment:4
                        * &quot;http://nvll.sf.net/&quot;
                        * &lt;http://nvll.sf.net/&gt;
                        * &lt;&lt;http://nvll.sf.net/&gt;&gt;
                        * [http://nvll.sf.net/]
                        * nvll-discuss@lists.sourceforge.net
                        * &lt;nvll-discuss@lists.sourceforge.net&gt;';

                $expected =
                        'This is a test mail with URLs:
                        * <a href="http://nvll.sf.net/" target="_blank">http://nvll.sf.net/</a>
                        * <a href="http://nvll.sf.net/?lang=de" target="_blank">http://nvll.sf.net/?lang=de</a>
                        * <a href="http://nvll.sourceforge.net/docs/changelog.php" target="_blank">http://nvll.sourceforge.net/docs/changelog.php</a>
                        * <a href="http://localhost/test1.php#anchor" target="_blank">http://localhost/test1.php#anchor</a>
                        * <a href="http://localhost/test2.php?para1=abc&para2=def" target="_blank">http://localhost/test2.php?para1=abc&para2=def</a>
                        * <a href="http://localhost/trac/ticket/123#comment:4" target="_blank">http://localhost/trac/ticket/123#comment:4</a>
                        * &quot;<a href="http://nvll.sf.net/" target="_blank">http://nvll.sf.net/</a>&quot;
                        * &lt;<a href="http://nvll.sf.net/" target="_blank">http://nvll.sf.net/</a>&gt;
                        * &lt;&lt;<a href="http://nvll.sf.net/" target="_blank">http://nvll.sf.net/</a>&gt;&gt;
                        * [<a href="http://nvll.sf.net/" target="_blank">http://nvll.sf.net/</a>]
                        * <a href="action.php?_nvkey=PHPSESSID&amp;action=write&amp;mail_to=nvll-discuss@lists.sourceforge.net">nvll-discuss@lists.sourceforge.net</a>
                        * &lt;<a href="action.php?_nvkey=PHPSESSID&amp;action=write&amp;mail_to=nvll-discuss@lists.sourceforge.net">nvll-discuss@lists.sourceforge.net</a>&gt;';

                $this->assertEquals($expected, NVLL_Body::prepareTextLinks($actual, 'http://localhost/retrmail/'));
        }

        /**
         * Test case for addColoredQuotes().
         */
        public function testAddColoredQuotes()
        {
                $actual = '&gt; &gt; &gt; This is level 3' .
                        "\r\n" . '&gt;&gt;&gt; ...' .
                        "\r\n" . '&gt; &gt; This is level 2' .
                        "\r\n" . '&gt;&gt; ...' .
                        "\r\n" . '&gt; This is level 1' .
                        "\r\n" . '&gt; ...' .
                        "\r\n" . 'And this is level 0' .
                        "\r\n" . '...';

                $expected = '<span class="quoteLevel3">&gt; &gt; &gt; This is level 3</span>' .
                        "\r\n" . '<span class="quoteLevel3">&gt;&gt;&gt; ...</span>' .
                        "\r\n" . '<span class="quoteLevel2">&gt; &gt; This is level 2</span>' .
                        "\r\n" . '<span class="quoteLevel2">&gt;&gt; ...</span>' .
                        "\r\n" . '<span class="quoteLevel1">&gt; This is level 1</span>' .
                        "\r\n" . '<span class="quoteLevel1">&gt; ...</span>' .
                        "\r\n" . 'And this is level 0' .
                        "\r\n" . '...';

                $this->assertEquals($expected, NVLL_Body::addColoredQuotes($actual));
        }




        /**
         * Test case for addStructuredText().
         */
        public function testAddStructuredText()
        {
                $actual = 'This *is* /just/ a _test_ |from| 10^6 and +/-0!';
                $expected = 'This <strong>*is*</strong> <em>/just/</em> a <span style="text-decoration:underline">_test_</span> <code>|from|</code> 10<sup>6</sup> and &plusmn;0!';

                $this->assertEquals($expected, NVLL_Body::addStructuredText($actual));
        }
}
