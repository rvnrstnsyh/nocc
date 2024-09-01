<?php

/**
 * Test cases for NVLL_RssFeed_Item.
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */


require_once dirname(__FILE__) . '/../../classes/NVLL_RSSFeed.php';

/**
 * Test class for NVLL_RSSFeed_Item.
 */
class NVLL_RSSFeed_ItemTest extends PHPUnit\Framework\TestCase
{
    /**
     * @var NVLL_RSSFeed_Item
     */
    protected $rssFeedItem1;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->rssFeedItem1 = new NVLL_RSSFeed_Item;
        $this->rssFeedItem1->setTitle('Title');
        $this->rssFeedItem1->setDescription('Description');
        $this->rssFeedItem1->setContent('...');
        $this->rssFeedItem1->setLink('http://nvll.sf.net/');
        $this->rssFeedItem1->setCreator('Tim Gerundt');
    }

    /**
     * Test case for getTitle().
     */
    public function testGetTitle()
    {
        $this->assertEquals('Title', $this->rssFeedItem1->getTitle());
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
        $this->assertEquals('Description', $this->rssFeedItem1->getDescription());
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
     *Test case for getContent().
     */
    public function testGetContent()
    {
        $this->assertEquals('...', $this->rssFeedItem1->getContent());
    }

    /**
     * @todo Implement testSetContent().
     */
    public function testSetContent()
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
        $this->assertEquals('http://nvll.sf.net/', $this->rssFeedItem1->getLink());
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
     * Test case for getCreator().
     */
    public function testGetCreator()
    {
        $this->assertEquals('Tim Gerundt', $this->rssFeedItem1->getCreator());
    }

    /**
     * @todo Implement testSetCreator().
     */
    public function testSetCreator()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
