<?php
namespace Tests\WPLib\UnitTests {

    use Tests\WPLib\WPLib_Test_Case;
    use \WPLib_Item_Base;

    require_once WPLIB_SRC_DIR . '/includes/class-item-base.php';

    /**
     * Class Item
     * @package Tests\WPLib\UnitTests
     */
    class Item extends WPLib_Item_Base {

    }

    /**
     * Class Item_Model
     * @package Tests\WPLib\UnitTests
     */
    class Item_Model {

        /**
         * @var string
         */
        var $foo = 'bar';

        /**
         * @return string
         */
        function bar() {

            return 'foo';

        }

    }

    /**
     * Class Item_View
     * @package Tests\WPLib\UnitTests
     */
    class Item_View {


        static function make_new( $args = array() ) {

            return new Item_View;

        }

        function the_baz() {

            return 'foobar';

        }

    }

    /**
     * Class Test_Item_Base
     * @package Tests\WPLib\UnitTests
     * @coversDefaultClass WPLib_Item_Base
     */
    class Test_Item_Base extends WPLib_Test_Case {

        /**
         * @var Item
         */
        protected $_sut;

        /**
         *
         */
        function setUp(): void {

            $this->_sut = new Item();

        }

        /**
         * @covers ::__construct
         * @uses   _get_property_class
         * @uses   \Tests\WPLib\UnitTests\Item_View::make_new
         */
        function testConstructorNoParams() {

            $item = new Item();

            $this->assertAttributeInstanceOf( '\Tests\WPLib\UnitTests\Item_Model', 'model', $item, 'Model is not properly set.' );
            $this->assertAttributeInstanceOf( '\Tests\WPLib\UnitTests\Item_View', 'view', $item, 'View is not properly set.' );

            unset( $item );

        }

        /**
         * @covers ::__construct
         */
        function testConstructorWithObjects() {

            $model = new \StdClass;
            $view  = new \StdClass;

            $item = new Item( array(
                'model' => $model,
                'view'  => $view,
            ) );

            $this->assertEquals( $model, $this->getReflectionPropertyValue( $item, 'model' ), 'Model is not properly set.' );
            $this->assertEquals( $view, $this->getReflectionPropertyValue( $item, 'view' ), 'View is not properly set.' );

            unset( $item );

        }

        /**
         * @covers ::__construct
         */
        function testConstructorWithStrings() {

            $item = new Item( array(
                'model' => '\Tests\WPLib\UnitTests\Item_Model',
                'view'  => '\Tests\WPLib\UnitTests\Item_View',
            ) );

            $this->assertAttributeInstanceOf( '\Tests\WPLib\UnitTests\Item_Model', 'model', $item, 'Model is not properly set.' );
            $this->assertAttributeInstanceOf( '\Tests\WPLib\UnitTests\Item_View', 'view', $item, 'View is not properly set.' );

            unset( $item );

        }

        /**
         * @covers ::__isset
         */
        function testIsset() {

            $this->assertTrue( $this->_sut->__isset( 'foo' ) );
            $this->assertFalse( $this->_sut->__isset( 'bar' ) );

        }

        /**
         * @covers ::__get
         */
        function testGet() {

            $this->assertEquals( 'bar', $this->_sut->foo );

        }

        /**
         * @covers :: __set
         * @depends testGet
         */
        function testSet() {

            $this->_sut->foo = 'baz';
            $this->assertEquals( 'baz', $this->_sut->foo );

        }

        /**
         * @covers ::__call
         */
        function testCallForModel() {

            $this->assertEquals( 'foo', $this->_sut->bar() );

        }

        /**
         * @covers ::__call
         */
        function testCallForView() {

            $this->assertEquals( 'foobar', $this->_sut->the_baz() );

        }

        /**
         * @covers ::__call
         */
        function testCallForError() {

            $this->setExpectedException( 'PHPUnit_Framework_Exception' );
            $this->_sut->foobar();

        }

    }

}
