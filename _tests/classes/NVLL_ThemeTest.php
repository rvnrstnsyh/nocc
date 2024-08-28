<?php

/**
 * Test cases for NVLL_Theme.
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

require_once dirname(__FILE__) . '/../../classes/NVLL_Theme.php';

/**
 * Test class for NVLL_Theme.
 * TODO Fix this.
 */
class NVLL_ThemeTest extends PHPUnit\Framework\TestCase
{
    /**
     * @var NVLL_Theme
     */
    protected $theme1;

    /**
     * @var NVLL_Theme
     */
    protected $theme2;

    /**
     * @var NVLL_Theme
     */
    protected $theme3;

    /**
     * @var NVLL_Theme
     */
    protected $theme4;

    /**
     * @var NVLL_Theme
     */
    protected $theme5;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->theme1 = new NVLL_Theme('test1');
        $this->theme2 = new NVLL_Theme('notexists');
        $this->theme3 = new NVLL_Theme('');
        $this->theme4 = new NVLL_Theme('../../../../../../../etc/passwd%00');
        $this->theme5 = new NVLL_Theme('<script>alert(document.cookie)</script>');
    }

    /**
     * Test case for getName().
     */
    public function testGetName()
    {
        $this->assertEquals('test1', $this->theme1->getName(), 'test1');
        $this->assertEquals('notexists', $this->theme2->getName(), 'notexists');
        $this->assertEquals('', $this->theme3->getName());
        $this->assertEquals('etcpasswd%00', $this->theme4->getName(), '../../../../../../../etc/passwd%00');
        $this->assertEquals('alert(document.cookie)', $this->theme5->getName(), '<script>alert(document.cookie)</script>');
    }

    /**
     * Test case for getPath().
     */
    public function testGetPath()
    {
        $this->assertEquals('themes/test1', $this->theme1->getPath(), 'test1');
        $this->assertEquals('', $this->theme2->getPath(), 'notexists');
        $this->assertEquals('', $this->theme3->getPath());
        $this->assertEquals('', $this->theme4->getPath(), '../../../../../../../etc/passwd%00');
        $this->assertEquals('', $this->theme5->getPath(), '<script>alert(document.cookie)</script>');
    }

    /**
     * Test case for getRealPath().
     */
    public function testGetRealPath()
    {
        $this->assertNotEquals('', $this->theme1->getRealPath(), 'test1');
        $this->assertEquals('', $this->theme2->getRealPath(), 'notexists');
        $this->assertEquals('', $this->theme3->getRealPath());
        $this->assertEquals('', $this->theme4->getRealPath(), '../../../../../../../etc/passwd%00');
        $this->assertEquals('', $this->theme5->getRealPath(), '<script>alert(document.cookie)</script>');
    }

    /**
     * Test case for exists().
     */
    public function testExists()
    {
        $this->assertTrue($this->theme1->exists(), 'test1');
        $this->assertFalse($this->theme2->exists(), 'notexists');
        $this->assertFalse($this->theme3->exists());
        $this->assertFalse($this->theme4->exists(), '../../../../../../../etc/passwd%00');
        $this->assertFalse($this->theme5->exists(), '<script>alert(document.cookie)</script>');
    }

    /**
     * Test case for getStylesheet().
     */
    public function testGetStylesheet()
    {
        $this->assertEquals('themes/test1/style.css', $this->theme1->getStylesheet(), 'test1');
        $this->assertEquals('', $this->theme2->getStylesheet(), 'notexists');
        $this->assertEquals('', $this->theme3->getStylesheet());
        $this->assertEquals('', $this->theme4->getStylesheet(), '../../../../../../../etc/passwd%00');
        $this->assertEquals('', $this->theme5->getStylesheet(), '<script>alert(document.cookie)</script>');
    }

    /**
     * Test case for getPrintStylesheet().
     */
    public function testGetPrintStylesheet()
    {
        $this->assertEquals('themes/test1/print.css', $this->theme1->getPrintStylesheet(), 'test1');
        $this->assertEquals('', $this->theme2->getPrintStylesheet(), 'notexists');
        $this->assertEquals('', $this->theme3->getPrintStylesheet());
        $this->assertEquals('', $this->theme4->getPrintStylesheet(), '../../../../../../../etc/passwd%00');
        $this->assertEquals('', $this->theme5->getPrintStylesheet(), '<script>alert(document.cookie)</script>');
    }

    /**
     * Test case for getFavicon().
     */
    public function testGetFavicon()
    {
        $this->assertEquals('favicon.ico', $this->theme1->getFavicon(), 'test1');
        $this->assertEquals('favicon.ico', $this->theme2->getFavicon(), 'notexists');
        $this->assertEquals('favicon.ico', $this->theme3->getFavicon());
        $this->assertEquals('favicon.ico', $this->theme4->getFavicon(), '../../../../../../../etc/passwd%00');
        $this->assertEquals('favicon.ico', $this->theme5->getFavicon(), '<script>alert(document.cookie)</script>');
    }

    /**
     * Test case for getCustomHeader().
     */
    public function testGetCustomHeader()
    {
        $this->assertNotEquals('', $this->theme1->getCustomHeader(), 'test1');
        $this->assertEquals('', $this->theme2->getCustomHeader(), 'notexists');
        $this->assertEquals('', $this->theme3->getCustomHeader());
        $this->assertEquals('', $this->theme4->getCustomHeader(), '../../../../../../../etc/passwd%00');
        $this->assertEquals('', $this->theme5->getCustomHeader(), '<script>alert(document.cookie)</script>');
    }

    /**
     * Test case for getCustomFooter().
     */
    public function testGetCustomFooter()
    {
        $this->assertNotEquals('', $this->theme1->getCustomFooter(), 'test1');
        $this->assertEquals('', $this->theme2->getCustomFooter(), 'notexists');
        $this->assertEquals('', $this->theme3->getCustomFooter());
        $this->assertEquals('', $this->theme4->getCustomFooter(), '../../../../../../../etc/passwd%00');
        $this->assertEquals('', $this->theme5->getCustomFooter(), '<script>alert(document.cookie)</script>');
    }

    /**
     * @todo Implement testReplaceTextSmilies().
     */
    public function testReplaceTextSmilies()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
