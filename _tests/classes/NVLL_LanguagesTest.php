<?php

/**
 * Test cases for NVLL_Languages.
 *
 * Copyright 2009-2011 Tim Gerundt <tim@gerundt.de>
 * Copyright 2024 Rivane Rasetiansyah <re@nvll.me>
 *
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

require_once dirname(__FILE__) . '/../../classes/NVLL_Languages.php';

/**
 * Test class for NVLL_Languages.
 */
class NVLL_LanguagesTest extends PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    protected $rootPath;

    /**
     * @var NVLL_Languages
     */
    protected $languages1;

    /**
     * @var NVLL_Languages
     */
    protected $languages2;

    /**
     * @var NVLL_Languages
     */
    protected $languages3;

    /**
     * @var NVLL_Languages
     */
    protected $languages4;

    /**
     * @var NVLL_Languages
     */
    protected $languages5;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->rootPath = dirname(__FILE__) . '/../';

        $this->languages1 = new NVLL_Languages('');
        $this->languages2 = new NVLL_Languages($this->rootPath . './languages', 'de', ['de']);
        $this->languages3 = new NVLL_Languages($this->rootPath . './languages/', 'DE', ['de', 'en', 'fr']);
        $this->languages4 = new NVLL_Languages(array('bug'));
        $this->languages5 = new NVLL_Languages($this->rootPath . './languages/', array('bug'));

        // Add this line to create a test language file
        file_put_contents($this->rootPath . './languages/se.php', '<?php return array();');
    }

    protected function tearDown(): void
    {
        // Remove the test language file
        @unlink($this->rootPath . './languages/se.php');
    }

    /**
     * Test case for count().
     */
    public function testCount()
    {
        $this->assertEquals(0, $this->languages1->count());
        $this->assertEquals(2, $this->languages2->count(), './languages, de');
        $this->assertEquals(3, $this->languages3->count(), './languages/, DE');
        $this->assertEquals(0, $this->languages4->count(), 'array(bug)');
        $this->assertEquals(3, $this->languages5->count(), './languages/, array(bug)');
    }

    /**
     * Test case for exists().
     */
    public function testExists()
    {
        $languages = new NVLL_Languages($this->rootPath . './languages');

        $this->assertFalse(@$languages->exists(''), 'exists()');
        $this->assertFalse($languages->exists(array('bug')), 'exists(array("bug"))');
        $this->assertFalse($languages->exists(''), 'exists("")');
        $this->assertFalse($languages->exists('notexists'), 'exists("notexists")');
        $this->assertTrue($languages->exists('de'), 'exists("de")');
        $this->assertTrue($languages->exists('DE'), 'exists("DE")');
    }

    /**
     * @todo Implement testDetectFromBrowser().
     */
    public function testDetectFromBrowser()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * Test case for getDefaultLangId().
     */
    public function testGetDefaultLangId()
    {
        $this->assertEquals('en', $this->languages1->getDefaultLangId());
        $this->assertEquals('de', $this->languages2->getDefaultLangId(), './languages, de');
        $this->assertEquals('de', $this->languages3->getDefaultLangId(), './languages/, DE');
        $this->assertEquals('en', $this->languages4->getDefaultLangId(), 'array(bug)');
        $this->assertEquals('en', $this->languages5->getDefaultLangId(), './languages/, array(bug)');
    }

    /**
     * Test case for setDefaultLangId().
     */
    public function testSetDefaultLangId()
    {
        $languages = new NVLL_Languages($this->rootPath . './languages');

        $this->assertFalse(@$languages->setDefaultLangId(''), 'setDefaultLangId()');
        $this->assertEquals('en', $languages->getDefaultLangId(), 'getDefaultLangId()');
        $this->assertFalse($languages->setDefaultLangId(array('bug')), 'setDefaultLangId(array("bug"))');
        $this->assertEquals('en', $languages->getDefaultLangId(), 'getDefaultLangId()');
        $this->assertFalse($languages->setDefaultLangId(''), 'setDefaultLangId("")');
        $this->assertEquals('en', $languages->getDefaultLangId(), 'getDefaultLangId()');
        $this->assertFalse($languages->setDefaultLangId('notexists'), 'setDefaultLangId("notexists")');
        $this->assertEquals('en', $languages->getDefaultLangId(), 'getDefaultLangId()');
        $this->assertFalse($languages->setDefaultLangId('../../../../../../../etc/passwd%00'), 'setDefaultLangId("passwd")');
        $this->assertEquals('en', $languages->getDefaultLangId(), 'getDefaultLangId()');
        $this->assertFalse($languages->setDefaultLangId('<script>alert(document.cookie)</script>'), 'setDefaultLangId("alert()")');
        $this->assertEquals('en', $languages->getDefaultLangId(), 'getDefaultLangId()');
        $this->assertTrue($languages->setDefaultLangId('se'), 'setDefaultLangId("se")');
        $this->assertEquals('se', $languages->getDefaultLangId(), 'getDefaultLangId()');
        $this->assertTrue($languages->setDefaultLangId('DE'), 'setDefaultLangId("DE")');
        $this->assertEquals('de', $languages->getDefaultLangId(), 'getDefaultLangId()');
    }

    /**
     * Test case for getSelectedLangId().
     */
    public function testGetSelectedLangId()
    {
        $this->assertEquals('en', $this->languages1->getSelectedLangId());
        $this->assertEquals('de', $this->languages2->getSelectedLangId(), './languages, de');
        $this->assertEquals('de', $this->languages3->getSelectedLangId(), './languages/, DE');
        $this->assertEquals('en', $this->languages4->getSelectedLangId(), 'array(bug)');
        $this->assertEquals('en', $this->languages5->getSelectedLangId(), './languages/, array(bug)');
    }

    /**
     * Test case for setSelectedLangId().
     */
    public function testSetSelectedLangId()
    {
        $languages = new NVLL_Languages($this->rootPath . './languages');

        $this->assertFalse(@$languages->setSelectedLangId(''), 'setSelectedLangId()');
        $this->assertEquals('en', $languages->getSelectedLangId(), 'getSelectedLangId()');
        $this->assertFalse($languages->setSelectedLangId(array('bug')), 'setSelectedLangId(array("bug"))');
        $this->assertEquals('en', $languages->getSelectedLangId(), 'getSelectedLangId()');
        $this->assertFalse($languages->setSelectedLangId(''), 'setSelectedLangId("")');
        $this->assertEquals('en', $languages->getSelectedLangId(), 'getSelectedLangId()');
        $this->assertFalse($languages->setSelectedLangId('../../../../../../../etc/passwd%00'), 'setSelectedLangId("passwd")');
        $this->assertEquals('en', $languages->getSelectedLangId(), 'getSelectedLangId()');
        $this->assertFalse($languages->setSelectedLangId('<script>alert(document.cookie)</script>'), 'setSelectedLangId("alert()")');
        $this->assertEquals('en', $languages->getSelectedLangId(), 'getSelectedLangId()');
        $this->assertFalse($languages->setSelectedLangId('notexists'), 'setSelectedLangId("notexists")');
        $this->assertEquals('en', $languages->getSelectedLangId(), 'getSelectedLangId()');
        $this->assertTrue($languages->setSelectedLangId('se'), 'setSelectedLangId("se")');
        $this->assertEquals('se', $languages->getSelectedLangId(), 'getSelectedLangId()');
        $this->assertTrue($languages->setSelectedLangId('DE'), 'setSelectedLangId("DE")');
        $this->assertEquals('de', $languages->getSelectedLangId(), 'getSelectedLangId()');
    }

    /**
     * Test case for parseAcceptLanguageHeader().
     */
    public function testParseAcceptLanguageHeader()
    {
        $this->assertEquals(0, count(@NVLL_Languages::parseAcceptLanguageHeader('')), 'parseHttpAcceptLanguage()');
        $this->assertEquals(0, count(NVLL_Languages::parseAcceptLanguageHeader(array('bug'))), 'parseHttpAcceptLanguage(array("bug"))');
        $this->assertEquals(0, count(NVLL_Languages::parseAcceptLanguageHeader('')), 'parseHttpAcceptLanguage("")');
        $this->assertEquals(1, count(NVLL_Languages::parseAcceptLanguageHeader('de')), 'parseHttpAcceptLanguage("de")');
        $this->assertEquals(1, count(NVLL_Languages::parseAcceptLanguageHeader('de-de')), 'parseHttpAcceptLanguage("de-de")');
        $this->assertEquals(2, count(NVLL_Languages::parseAcceptLanguageHeader('de,de-de')), 'parseHttpAcceptLanguage("de,de-de")');
        $this->assertEquals(1, count(NVLL_Languages::parseAcceptLanguageHeader('de-de;q=0.5')), 'parseHttpAcceptLanguage("de-de;q=0.5")');
        $this->assertEquals(4, count(NVLL_Languages::parseAcceptLanguageHeader('de-de,de;q=0.8,en-us;q=0.5,en;q=0.3')), 'parseHttpAcceptLanguage("de-de,de;q=0.8,en-us;q=0.5,en;q=0.3")');
        $this->assertEquals(1, count(NVLL_Languages::parseAcceptLanguageHeader('De-De ; Q = 0.5')), 'parseHttpAcceptLanguage("de-de;q=0.5")');
        $this->assertEquals(4, count(NVLL_Languages::parseAcceptLanguageHeader('   de-de , de; q=0.8, en-us;q=0.5, en;q=0.3')), 'parseHttpAcceptLanguage("   de-de , de; q=0.8, en-us;q=0.5, en;q=0.3")');
        $this->assertEquals(0, count(NVLL_Languages::parseAcceptLanguageHeader(',,,;;;')), 'parseHttpAcceptLanguage(",,,;;;")');
    }
}
