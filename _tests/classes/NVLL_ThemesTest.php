<?php

/**
 * Test cases for NVLL_Themes.
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

require_once dirname(__FILE__) . '/../../classes/NVLL_Themes.php';

/**
 * Test class for NVLL_Themes.
 */
class NVLL_ThemesTest extends PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    protected $rootPath;

    /**
     * @var NVLL_Themes
     */
    protected $themes1;

    /**
     * @var NVLL_Themes
     */
    protected $themes2;

    /**
     * @var NVLL_Themes
     */
    protected $themes3;

    /**
     * @var NVLL_Themes
     */
    protected $themes4;

    /**
     * @var NVLL_Themes
     */
    protected $themes5;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->rootPath = dirname(__FILE__) . '/../';

        $this->themes1 = new NVLL_Themes('');
        $this->themes2 = new NVLL_Themes($this->rootPath . './themes', 'test1');
        $this->themes3 = new NVLL_Themes($this->rootPath . './themes/', 'TEST1');
        $this->themes4 = new NVLL_Themes(array('bug'));
        $this->themes5 = new NVLL_Themes($this->rootPath . './themes/', array('bug'));
    }

    /**
     * Test case for count().
     */
    public function testCount()
    {
        $this->assertEquals(0, $this->themes1->count());
        $this->assertEquals(2, $this->themes2->count(), './themes, test1');
        $this->assertEquals(2, $this->themes3->count(), './themes/ TEST1');
        $this->assertEquals(0, $this->themes4->count(), 'array(bug)');
        $this->assertEquals(2, $this->themes5->count(), './themes/, array(bug)');
    }

    /**
     * Test case for exists().
     */
    public function testExists()
    {
        $themes = new NVLL_Themes($this->rootPath . './themes');

        $this->assertFalse(@$themes->exists(''), 'exists()');
        $this->assertFalse($themes->exists(array('bug')), 'exists(array("bug"))');
        $this->assertFalse($themes->exists(''), 'exists("")');
        $this->assertFalse($themes->exists('notexists'), 'exists("notexists")');
        $this->assertTrue($themes->exists('test1'), 'exists("test1")');
        $this->assertTrue($themes->exists('TEST1'), 'exists("TEST1")');
    }

    /**
     * Test case for getThemeNames().
     */
    public function testGetThemeNames()
    {
        $themes = new NVLL_Themes($this->rootPath . './themes');
        $themeNames = $themes->getThemeNames();

        $this->assertEquals(2, count($themeNames), 'count($themeNames)');
        $this->assertEquals('test1', $themeNames[0], '$themeNames[0]');
        $this->assertEquals('test2', $themeNames[1], '$themeNames[1]');
    }

    /**
     * Test case for getDefaultThemeName().
     */
    public function testGetDefaultThemeName()
    {
        $this->assertEquals('default', $this->themes1->getDefaultThemeName());
        $this->assertEquals('test1', $this->themes2->getDefaultThemeName(), './themes, test1');
        $this->assertEquals('test1', $this->themes3->getDefaultThemeName(), './themes/ TEST1');
        $this->assertEquals('default', $this->themes4->getDefaultThemeName(), 'array(bug)');
        $this->assertEquals('default', $this->themes5->getDefaultThemeName(), './themes/, array(bug)');
    }

    /**
     * Test case for setDefaultThemeName().
     */
    public function testSetDefaultThemeName()
    {
        $themes = new NVLL_Themes($this->rootPath . './themes');

        $this->assertFalse(@$themes->setDefaultThemeName(''), 'setDefaultThemeName()');
        $this->assertEquals('default', $themes->getDefaultThemeName(), 'getDefaultThemeName()');
        $this->assertFalse($themes->setDefaultThemeName(array('bug')), 'setDefaultThemeName(array("bug"))');
        $this->assertEquals('default', $themes->getDefaultThemeName(), 'getDefaultThemeName()');
        $this->assertFalse($themes->setDefaultThemeName(''), 'setDefaultThemeName("")');
        $this->assertEquals('default', $themes->getDefaultThemeName(), 'getDefaultThemeName()');
        $this->assertFalse($themes->setDefaultThemeName('notexists'), 'setDefaultThemeName("notexists")');
        $this->assertEquals('default', $themes->getDefaultThemeName(), 'getDefaultThemeName()');
        $this->assertFalse($themes->setDefaultThemeName('../../../../../../../etc/passwd%00'), 'setDefaultThemeName("passwd")');
        $this->assertEquals('default', $themes->getDefaultThemeName(), 'getDefaultThemeName()');
        $this->assertFalse($themes->setDefaultThemeName('<script>alert(document.cookie)</script>'), 'setDefaultThemeName("alert()")');
        $this->assertEquals('default', $themes->getDefaultThemeName(), 'getDefaultThemeName()');
        $this->assertTrue($themes->setDefaultThemeName('test1'), 'setDefaultThemeName("test1")');
        $this->assertEquals('test1', $themes->getDefaultThemeName(), 'getDefaultThemeName()');
        $this->assertTrue($themes->setDefaultThemeName('TEST2'), 'setDefaultThemeName("TEST2")');
        $this->assertEquals('test2', $themes->getDefaultThemeName(), 'getDefaultThemeName()');
    }

    /**
     * Test case for getSelectedThemeName().
     */
    public function testGetSelectedThemeName()
    {
        $this->assertEquals('default', $this->themes1->getSelectedThemeName());
        $this->assertEquals('test1', $this->themes2->getSelectedThemeName(), './themes, test1');
        $this->assertEquals('test1', $this->themes3->getSelectedThemeName(), './themes/ TEST1');
        $this->assertEquals('default', $this->themes4->getSelectedThemeName(), 'array(bug)');
        $this->assertEquals('default', $this->themes5->getSelectedThemeName(), './themes/, array(bug)');
    }

    /**
     * Test case for setSelectedThemeName().
     */
    public function testSetSelectedThemeName()
    {
        $themes = new NVLL_Themes($this->rootPath . './themes');

        $this->assertFalse(@$themes->setSelectedThemeName(''), 'setSelectedThemeName()');
        $this->assertEquals('default', $themes->getSelectedThemeName(), 'getSelectedThemeName()');
        $this->assertFalse($themes->setSelectedThemeName(array('bug')), 'setSelectedThemeName(array("bug"))');
        $this->assertEquals('default', $themes->getSelectedThemeName(), 'getSelectedThemeName()');
        $this->assertFalse($themes->setSelectedThemeName(''), 'setSelectedThemeName("")');
        $this->assertEquals('default', $themes->getSelectedThemeName(), 'getSelectedThemeName()');
        $this->assertFalse($themes->setSelectedThemeName('../../../../../../../etc/passwd%00'), 'setSelectedThemeName("passwd")');
        $this->assertEquals('default', $themes->getSelectedThemeName(), 'getSelectedThemeName()');
        $this->assertFalse($themes->setSelectedThemeName('<script>alert(document.cookie)</script>'), 'setSelectedThemeName("alert()")');
        $this->assertEquals('default', $themes->getSelectedThemeName(), 'getSelectedThemeName()');
        $this->assertFalse($themes->setSelectedThemeName('notexists'), 'setSelectedThemeName("notexists")');
        $this->assertEquals('default', $themes->getSelectedThemeName(), 'getSelectedThemeName()');
        $this->assertTrue($themes->setSelectedThemeName('test1'), 'setSelectedThemeName("test1")');
        $this->assertEquals('test1', $themes->getSelectedThemeName(), 'getSelectedThemeName()');
        $this->assertTrue($themes->setSelectedThemeName('TEST2'), 'setSelectedThemeName("TEST2")');
        $this->assertEquals('test2', $themes->getSelectedThemeName(), 'getSelectedThemeName()');
    }
}
