<?php
namespace Tests\WPLib\UnitTests {

    use Tests\WPLib\WPLib_Test_Case;
    use \WPLib_View_Base;

    require_once WPLIB_SRC_DIR . '/includes/class-view-base.php';

    /**
     * Class Model_Base
     * @package Tests\WPLib\UnitTests
     */
    class View_Base extends WPLib_View_Base {

        /**
         *
         */
        const FOO = 'bar';

        /**
         * View_Base constructor.
         * @param array $args
         */
        function __construct($args = array()) {

            $this->owner = 'foo';

            parent::__construct($args);

        }

        /**
         * @return string
         */
        function foobar() {

            return 'foobaz';

        }
        
        /**
         * @param $val
         */
        function set_foo($val) {

            $this->foo = $val;

        }

    }

    /**
     * Class Test_View_Base
     * @package            Tests\WPLib\UnitTests
     * @group              Base
     * @coversDefaultClass WPLib_View_Base
     */
    class Test_View_Base extends WPLib_Test_Case {

        /**
         * @var View_Base
         */
        protected $_sut;

        /**
         *
         */
        function setUp(): void {

            $this->_sut = new View_Base();

            parent::setUp();

        }

        /**
         * @covers ::item
         */
        function testItem() {

            $this->assertEquals('foo', $this->_sut->item());

        }

        /**
         * @covers  ::setItem
         * @depends testItem
         */
        function testSetItem() {

            $this->_sut->set_item('bar');
            $this->assertEquals('bar', $this->_sut->item());

        }

        /**
         * @covers ::get_constant
         */
        function testGetConstant() {

            $this->assertEquals('bar', $this->_sut->get_constant('FOO'));

        }

        /**
         * @covers ::__get
         */
        function testGet() {

            $this->assertEquals('foobaz', $this->_sut->foobar);

        }

        /**
         * @covers ::__set
         */
        function testSet() {

            $this->_sut->foo = 'asdf';
            $this->assertEquals('asdf', $this->getReflectionPropertyValue($this->_sut, 'foo'));

        }

        /**
         * @covers ::__call
         */
        function testCall() {

            $this->markTestIncomplete();

        }

    }

}
