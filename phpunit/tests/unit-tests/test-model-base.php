<?php
namespace Tests\WPLib\UnitTests {

    use Tests\WPLib\WPLib_Test_Case;
    use \WPLib_Model_Base;

    require_once WPLIB_TESTS_WORKING_DIR . '/includes/class-model-base.php';

    /**
     * Class Model_Base
     * @package Tests\WPLib\UnitTests
     */
    class Model_Base extends WPLib_Model_Base {

        const FOO = 'bar';

    }

    /**
     * Class Test_Model_Base
     * @package             Tests\WPLib\UnitTests
     * @coversDefaultClass  WPLib_Model_Base
     */
    class Test_Model_Base extends WPLib_Test_Case {

        /**
         * @var Model_Base
         */
        protected $_sut;

        /**
         *
         */
        function setUp() {

            $this->_sut = new Model_Base();

            parent::setUp();

        }

        /**
         * @covers ::get_constant
         */
        function testGetConstant() {

            $this->assertEquals( 'bar', $this->_sut->get_constant( 'FOO' ) );

        }

    }

}
