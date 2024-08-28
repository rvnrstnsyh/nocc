<?php

/**
 * Test cases for NVLL_MailAddress.
 *
 * Copyright 2009-2011 Tim Gerundt <tim@gerundt.de>
 * Copyright 2024 Rivane Rasetiansyah <re@nvll.me>
 *
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

require_once dirname(__FILE__) . '/../../classes/NVLL_MailAddress.php';

/**
 * Test class for NVLL_MailAddress.
 */
class NVLL_MailAddressTest extends PHPUnit\Framework\TestCase
{
    /**
     * @var NVLL_MailAddress
     */
    protected $mailAddress1;

    /**
     * @var NVLL_MailAddress
     */
    protected $mailAddress2;

    /**
     * @var NVLL_MailAddress
     */
    protected $mailAddress3;

    /**
     * @var NVLL_MailAddress
     */
    protected $mailAddress4;

    /**
     * @var NVLL_MailAddress
     */
    protected $mailAddress5;

    /**
     * @var NVLL_MailAddress
     */
    protected $mailAddress6;

    /**
     * @var NVLL_MailAddress
     */
    protected $mailAddress7;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->mailAddress1 = new NVLL_MailAddress('');
        $this->mailAddress2 = new NVLL_MailAddress('foo@bar.org');
        $this->mailAddress3 = new NVLL_MailAddress('Foo Bar <foo@bar.org>');
        $this->mailAddress4 = new NVLL_MailAddress('"Foo Bar" <foo@bar.org>');
        $this->mailAddress5 = new NVLL_MailAddress('bug');
        $this->mailAddress6 = new NVLL_MailAddress('foo@bar.org', 'Foobar');
        $this->mailAddress7 = new NVLL_MailAddress('"foo <test> bar" <foo@bar.org>');
    }

    /**
     * Test case for getName().
     */
    public function testGetName()
    {
        $this->assertEquals('', $this->mailAddress1->getName());
        $this->assertEquals('', $this->mailAddress2->getName(), 'foo@bar.org');
        $this->assertEquals('Foo Bar', $this->mailAddress3->getName(), 'Foo Bar <foo@bar.org>');
        $this->assertEquals('Foo Bar', $this->mailAddress4->getName(), '"Foo Bar" <foo@bar.org>');
        $this->assertEquals('', $this->mailAddress5->getName(), 'bug');
        $this->assertEquals('Foobar', $this->mailAddress6->getName(), 'foo@bar.org, Foobar');
        $this->assertEquals('foo <test> bar', $this->mailAddress7->getName(), '"foo <test> bar" <foo@bar.org>');
    }

    /**
     * Test case for hasName().
     */
    public function testHasName()
    {
        $this->assertFalse($this->mailAddress1->hasName());
        $this->assertFalse($this->mailAddress2->hasName(), 'foo@bar.org');
        $this->assertTrue($this->mailAddress3->hasName(), 'Foo Bar <foo@bar.org>');
        $this->assertTrue($this->mailAddress4->hasName(), '"Foo Bar" <foo@bar.org>');
        $this->assertFalse($this->mailAddress5->hasName(), 'bug');
        $this->assertTrue($this->mailAddress6->hasName(), 'foo@bar.org, Foobar');
        $this->assertTrue($this->mailAddress7->hasName(), '"foo <test> bar" <foo@bar.org>');
    }

    /**
     * Test case for setName().
     */
    public function testSetName()
    {
        $mailAddress = new NVLL_MailAddress('"Foo Bar" <foo@bar.org>');

        $this->assertEquals('Foo Bar', $mailAddress->getName(), '"Foo Bar" <foo@bar.org>');
        $mailAddress->setName('Bar Foo');
        $this->assertEquals('Bar Foo', $mailAddress->getName(), '"Bar Foo" <foo@bar.org>');
        $mailAddress->setName(false);
        $this->assertEquals('Bar Foo', $mailAddress->getName(), '"Bar Foo" <foo@bar.org>');
    }

    /**
     * Test case for getAddress().
     */
    public function testGetAddress()
    {
        $this->assertEquals('', $this->mailAddress1->getAddress());
        $this->assertEquals('foo@bar.org', $this->mailAddress2->getAddress(), 'foo@bar.org');
        $this->assertEquals('foo@bar.org', $this->mailAddress3->getAddress(), 'Foo Bar <foo@bar.org>');
        $this->assertEquals('foo@bar.org', $this->mailAddress4->getAddress(), '"Foo Bar" <foo@bar.org>');
        //$this->assertEquals('', $this->mailAddress5->getAddress(), 'bug');
        $this->assertEquals('foo@bar.org', $this->mailAddress6->getAddress(), 'foo@bar.org, Foobar');
        $this->assertEquals('foo@bar.org', $this->mailAddress7->getAddress(), '"foo <test> bar" <foo@bar.org>');
    }

    /**
     * Test case for hasAddress().
     */
    public function testHasAddress()
    {
        $this->assertFalse($this->mailAddress1->hasAddress());
        $this->assertTrue($this->mailAddress2->hasAddress(), 'foo@bar.org');
        $this->assertTrue($this->mailAddress3->hasAddress(), 'Foo Bar <foo@bar.org>');
        $this->assertTrue($this->mailAddress4->hasAddress(), '"Foo Bar" <foo@bar.org>');
        //$this->assertTrue($this->mailAddress5->hasAddress(), 'bug');
        $this->assertTrue($this->mailAddress6->hasAddress(), 'foo@bar.org, Foobar');
        $this->assertTrue($this->mailAddress7->hasAddress(), '"foo <test> bar" <foo@bar.org>');
    }

    /**
     * Test case for setAddress().
     */
    public function testSetAddress()
    {
        $mailAddress = new NVLL_MailAddress('"Foo Bar" <foo@bar.org>');

        $this->assertEquals('foo@bar.org', $mailAddress->getAddress(), '"Foo Bar" <foo@bar.org>');
        $mailAddress->setAddress('bar@foo.com');
        $this->assertEquals('bar@foo.com', $mailAddress->getAddress(), '"Foo Bar" <bar@foo.com>');
        $mailAddress->setAddress(false);
        $this->assertEquals('bar@foo.com', $mailAddress->getAddress(), '"Foo Bar" <bar@foo.com>');
    }

    /**
     * Test case for __toString().
     */
    public function testToString()
    {
        $this->assertEquals('', (string)$this->mailAddress1);
        $this->assertEquals('foo@bar.org', (string)$this->mailAddress2, 'foo@bar.org');
        $this->assertEquals('"Foo Bar" <foo@bar.org>', (string)$this->mailAddress3, 'Foo Bar <foo@bar.org>');
        $this->assertEquals('"Foo Bar" <foo@bar.org>', (string)$this->mailAddress4, '"Foo Bar" <foo@bar.org>');
        //$this->assertEquals('', (string)$this->mailAddress5, 'bug');
        $this->assertEquals('Foobar <foo@bar.org>', (string)$this->mailAddress6, 'foo@bar.org, Foobar');
        $this->assertEquals('"foo <test> bar" <foo@bar.org>', (string)$this->mailAddress7, '"foo <test> bar" <foo@bar.org>');
    }

    /**
     * Test case for isValidAddress().
     */
    public function testIsValidAddress()
    {
        $this->assertFalse(NVLL_MailAddress::isValidAddress(''));
        $this->assertFalse(NVLL_MailAddress::isValidAddress('bug'), 'bug');
        $this->assertTrue(NVLL_MailAddress::isValidAddress('foo@bar.org'), 'foo@bar.org');
        $this->assertTrue(NVLL_MailAddress::isValidAddress('foo.foo@bar.bar.org'), 'foo.foo@bar.bar.org');
        $this->assertTrue(NVLL_MailAddress::isValidAddress('foo-foo@bar-bar.org'), 'foo-foo@bar-bar.org');
        $this->assertTrue(NVLL_MailAddress::isValidAddress('foo_foo@bar.org'), 'foo_foo@bar.org');
        $this->assertFalse(NVLL_MailAddress::isValidAddress('foo@bar'), 'foo@bar');
        $this->assertFalse(NVLL_MailAddress::isValidAddress('bar.org'), 'bar.org');
        $this->assertFalse(NVLL_MailAddress::isValidAddress('foo @ bar.org'), 'foo @ bar.org');
    }

    /**
     * Test case for compareAddress().
     */
    public function testCompareAddress()
    {
        $this->assertEquals(-1, NVLL_MailAddress::compareAddress('', ''));
        $this->assertEquals(-1, NVLL_MailAddress::compareAddress(null, null), 'null, null');
        $this->assertEquals(0, NVLL_MailAddress::compareAddress('foo@bar.org', 'bar@foo.org'), 'foo@bar.org, bar@foo.org');
        $this->assertEquals(0, NVLL_MailAddress::compareAddress('Foo <foo@bar.org>', 'Bar <bar@foo.org>'), 'Foo <foo@bar.org>, Bar <bar@foo.org>');
        $this->assertEquals(0, NVLL_MailAddress::compareAddress('foo@bar.org', 'BAR@FOO.ORG'), 'foo@bar.org, BAR@FOO.ORG');
        $this->assertEquals(0, NVLL_MailAddress::compareAddress('Foo <foo@bar.org>', 'BAR <BAR@FOO.ORG>'), 'Foo <foo@bar.org>, BAR <BAR@FOO.ORG>');
        $this->assertEquals(1, NVLL_MailAddress::compareAddress('foo@bar.org', 'foo@bar.org'), 'foo@bar.org, foo@bar.org');
        $this->assertEquals(1, NVLL_MailAddress::compareAddress('Foo <foo@bar.org>', 'Foo <foo@bar.org>'), 'Foo <foo@bar.org>, Foo <foo@bar.org>');
        $this->assertEquals(1, NVLL_MailAddress::compareAddress('foo@bar.org', 'FOO@BAR.ORG'), 'foo@bar.org, FOO@BAR.ORG');
        $this->assertEquals(1, NVLL_MailAddress::compareAddress('Foo <foo@bar.org>', 'FOO <FOO@BAR.ORG>'), 'Foo <foo@bar.org>, FOO <FOO@BAR.ORG>');
    }

    /**
     * Test case for chopAddress().
     */
    public function testChopAddress()
    {
        $this->assertEquals('', NVLL_MailAddress::chopAddress(''));
        $this->assertEquals('bug', NVLL_MailAddress::chopAddress('bug'), 'bug');
        $this->assertEquals('foo@bar.org', NVLL_MailAddress::chopAddress('foo@bar.org'), 'foo@bar.org');
        $this->assertEquals('Foo Bar', NVLL_MailAddress::chopAddress('Foo Bar <foo@bar.org>'), 'Foo Bar <foo@bar.org>');
        $this->assertEquals('"Foo Bar"', NVLL_MailAddress::chopAddress('"Foo Bar" <foo@bar.org>'), '"Foo Bar" <foo@bar.org>');
        $this->assertEquals('"foo >> bar"', NVLL_MailAddress::chopAddress('"foo >> bar" <foo@bar.org>'), '"foo >> bar" <foo@bar.org>');
        $this->assertEquals('"foo <> bar"', NVLL_MailAddress::chopAddress('"foo <> bar" <foo@bar.org>'), '"foo <> bar" <foo@bar.org>');
        $this->assertEquals('"foo << bar"', NVLL_MailAddress::chopAddress('"foo << bar" <foo@bar.org>'), '"foo << bar" <foo@bar.org>');
    }
}
