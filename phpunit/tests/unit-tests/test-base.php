<?php
namespace Tests\WPLib\UnitTests {

	use Tests\WPLib\WPLib_Test_Case;
	use \WPLib_Base;

	require_once WPLIB_SRC_DIR . '/includes/class-base.php';

	/**
	 * Class Test_Base
	 * @package Tests\WPLib\UnitTests
	 */
	class Test_Base extends WPLib_Base{

		const FOO_CONSTANT = 'foo';

		var $foo;
		var $_bar;

		function foo() {

			return $this->foo;

		}

		function set_foo( $value ) {

			$this->foo = $value;

		}

	}

	/**
	 * Class Test_WPLib_Base
	 * @package            Tests\WPLib\UnitTests
	 * @coversDefaultClass WPLib_Base
	 */
	class Test_WPLib_Base extends WPLIB_Test_Case {

		/**
		 * @var Test_Base
		 */
		private $_sut;

		function setUp(): void {

			$this->_sut = new Test_Base( array( 'foo' => 'foobar', 'bar' => 'barbaz', 'baz' => 'foobaz' ) );

		}

		/**
		 * @covers ::set_state
		 */
		function testSetState() {

			$args = array(
				'foo' => '1',
				'bar' => '2',
				'baz' => '3'
			);

			$this->_sut->set_state( $args );

			$this->assertEquals( '1', $this->getReflectionPropertyValue( $this->_sut, 'foo' ) );
			$this->assertEquals( '2', $this->getReflectionPropertyValue( $this->_sut, '_bar' ) );
			$this->assertEquals( '3', $this->getReflectionPropertyValue( $this->_sut, 'extra_args' )['baz'] );

		}

		/**
		 * @covers ::__construct
		 * @uses   WPLib_Base::set_state
		 * @depends  testSetState
		 */
		function testConstructor() {

			$this->assertEquals( 'foobar', $this->getReflectionPropertyValue( $this->_sut, 'foo' ) );
			$this->assertEquals( 'barbaz', $this->getReflectionPropertyValue( $this->_sut, '_bar' ) );
			$this->assertEquals( 'foobaz', $this->getReflectionPropertyValue( $this->_sut, 'extra_args' )['baz'] );

			unset( $base );

		}

		/**
		 * @covers ::get_constant
		 */
		function testGetConstant() {

			$this->assertEquals( 'foo', $this->_sut->get_constant( 'FOO_CONSTANT' ) );
			$this->assertNull( $this->_sut->get_constant( 'BAR_CONSTANT' ) );

		}

		/**
		 * @covers ::add_class_action
		 */
		function testAddClassAction() {

			$this->markTestIncomplete( 'has_action is not working as expected.');

			Test_Base::add_class_action( 'pre_get_posts' );
			$this->assertEquals( 10, has_action( 'pre_get_posts', array( 'Test_Base', '_pre_get_posts' ) ) );

		}

		/**
		 * @covers ::add_clss_action
		 */
		function testAddClassActionSpecifyPriority() {

			$this->markTestIncomplete( 'has_action is not working as expected.');
			$this->_sut->add_class_action( 'pre_get_posts', 22 );
			$this->assertEquals( 22, has_action( 'pre_get_posts' ) );

		}

		/**
		 * @covers ::__isset
		 * @depends testConstructor
		 */
		function testIsset() {

			$this->assertTrue( $this->_sut->__isset( 'foo' ) );

		}

		/**
		 * @covers ::__get
		 * @depends testConstructor
		 */
		function testGet() {

			$this->assertEquals( 'foobar', $this->_sut->foo );
			$this->markTestIncomplete();

		}

		/**
		 * @covers ::__set
		 */
		function testSet() {

			$this->_sut->foo = 'asdf';
			$this->assertEquals( 'asdf', $this->_sut->foo );
		}

		/**
		 * @covers ::__call
		 */
		function testCall() {

			$this->markTestIncomplete( 'Not yet implemented' );

		}

	}

}
