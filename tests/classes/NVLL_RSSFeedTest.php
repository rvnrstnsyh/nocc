<?php

/**
 * Test cases for NVLL_RSSFeed.
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

require_once dirname(__FILE__) . '/../../classes/NVLL_RSSfeed.php';

/**
 * Test class for NVLL_RSSFeed.
 */
class NVLL_RSSFeedTest extends PHPUnit\Framework\TestCase
{
    /**
     * @var NVLL_RSSFeed
     */
    protected $rssFeed1;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->rssFeed1 = new NVLL_RSSFeed;
        $this->rssFeed1->setTitle('Title');
        $this->rssFeed1->setDescription('Description');
        $this->rssFeed1->setLink('http://nvll.sf.net/');
    }

    /**
     * Test case for getTitle().
     */
    public function testGetTitle()
    {
        $this->assertEquals('Title', $this->rssFeed1->getTitle());
    }

    /**
     * @todo Implement testSetTitle().
     */
    public function testSetTitle()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Test case for getDescription().
     */
    public function testGetDescription()
    {
        $this->assertEquals('Description', $this->rssFeed1->getDescription());
    }

    /**
     * @todo Implement testSetDescription().
     */
    public function testSetDescription()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Test case for getLink().
     */
    public function testGetLink()
    {
        $this->assertEquals('http://nvll.sf.net/', $this->rssFeed1->getLink());
    }

    /**
     * @todo Implement testSetLink().
     */
    public function testSetLink()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testAddItem().
     */
    public function testAddItem()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testSendToBrowser().
     */
    public function testSendToBrowser()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Test case for getIso8601Date().
     */
    public function testGetIso8601Date()
    {
        $timestamp = mktime(22, 26, 11, 2, 19, 2010);
        $tzd = substr(date('O', $timestamp), 0, 3) . ':' . substr(date('O', $timestamp), -2);

        $this->assertStringStartsWith('2010-02-19T22:26:11', NVLL_RSSFeed::getIso8601Date($timestamp), 'starts with');
        $this->assertStringEndsWith($tzd, NVLL_RSSFeed::getIso8601Date($timestamp), 'ends with');
        $this->assertEquals(25, strlen(NVLL_RSSFeed::getIso8601Date($timestamp)), 'length');
    }
}
