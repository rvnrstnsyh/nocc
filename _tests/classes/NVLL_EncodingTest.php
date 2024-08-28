<?php

/**
 * Test cases for NVLL_Encoding.
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

require_once dirname(__FILE__) . '/../../classes/NVLL_Encoding.php';

/**
 * Test class for NVLL_Encoding.
 */
class NVLL_EncodingTest extends PHPUnit\Framework\TestCase
{
    /**
     * @var NVLL_Encoding
     */
    protected $encodingNull;

    /**
     * @var NVLL_Encoding
     */
    protected $encodingBug;

    /**
     * @var NVLL_Encoding
     */
    protected $encoding0;

    /**
     * @var NVLL_Encoding
     */
    protected $encoding1;

    /**
     * @var NVLL_Encoding
     */
    protected $encoding2;

    /**
     * @var NVLL_Encoding
     */
    protected $encoding3;

    /**
     * @var NVLL_Encoding
     */
    protected $encoding4;

    /**
     * @var NVLL_Encoding
     */
    protected $encoding5;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->encodingNull = new NVLL_Encoding(null);
        $this->encodingBug = new NVLL_Encoding('bug');
        $this->encoding0 = new NVLL_Encoding(0);
        $this->encoding1 = new NVLL_Encoding(1);
        $this->encoding2 = new NVLL_Encoding(2);
        $this->encoding3 = new NVLL_Encoding(3);
        $this->encoding4 = new NVLL_Encoding(4);
        $this->encoding5 = new NVLL_Encoding(5);
    }

    /**
     * Test case for __toString().
     */
    public function test__toString()
    {
        $this->assertEquals('', $this->encodingNull->__toString(), 'null');
        $this->assertEquals('', $this->encodingBug->__toString(), 'bug');
        $this->assertEquals('7BIT', $this->encoding0->__toString(), '0');
        $this->assertEquals('8BIT', $this->encoding1->__toString(), '1');
        $this->assertEquals('BINARY', $this->encoding2->__toString(), '2');
        $this->assertEquals('BASE64', $this->encoding3->__toString(), '3');
        $this->assertEquals('QUOTED-PRINTABLE', $this->encoding4->__toString(), '4');
        $this->assertEquals('OTHER', $this->encoding5->__toString(), '5');
    }
}
