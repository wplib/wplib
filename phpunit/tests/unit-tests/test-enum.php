<?php
namespace Tests\WPLib\UnitTests {

    use Tests\WPLib\WPLib_Test_Case;
    use \WPLib_Enum;
    use \ReflectionClass;

    class Enum_Test extends WPLib_Enum {

        const SLUG = 'test_slug';

        const __default = 2;

        const FOO = 1;
        const BAR = 2;

    }

    /**
     * Class Test_Enum
     * @package Tests\WPLib\UnitTests
     * @coversDefaultClass WPLib_Enum
     */
    class Test_Enum extends WPLib_Test_Case {

        /**
         * @var Enum_Test
         */
        private $_sut;

        /**
         * @var string
         */
        private $_sut_class;

        /**
         * Set up the system under test
         */
        function setUp(): void {

            $this->_sut         = new Enum_Test();
            $this->_sut_class   = get_class($this->_sut);

        }

        /**
         * @covers ::get_slug
         */
        function testGetSlug() {

            $this->assertEquals('test_slug', Enum_Test::get_slug($this->_sut));
            $this->assertEquals('test_slug', Enum_Test::get_slug($this->_sut_class));

        }

        /**
         * @covers ::get_enum
         */
        function testGetEnum() {

            $this->assertEquals($this->_sut, Enum_Test::get_enum('test_slug'));
            $this->assertNull( Enum_Test::get_enum('bar'));

        }

        /**
         * @covers ::get_enum_class
         */
        function testGetEnumClass() {

            $this->assertEquals(get_class($this->_sut), Enum_Test::get_enum_class('test_slug'));

        }

        /**
         * @covers ::get_enum_classes
         */
        function testGetEnumClasses() {

            $this->assertArrayHasKey('test_slug', Enum_Test::get_enum_classes());

        }

        /**
         * @covers ::get_value
         */
        function testGetValue() {

            $this->assertEquals(2,$this->_sut->get_value());

        }

        /**
         * @covers ::set_value
         * @uses   is_valid
         */
        function testSetValue() {

            $this->markTestIncomplete();
            $this->_sut->set_value('BAR');
            $this->assertEquals(2,$this->_sut->get_value());

        }

        /**
         * @covers :: set_enum
         * @uses    set_value
         * @depends testGetValue
         * @depends testSetValue
         */
        function testSetEnum() {

            $class = $this->_sut_class;
            $class::set_enum(get_class($this->_sut), 1);
            $this->assertEquals(1,$this->_sut->get_value());

        }

        /**
         * @covers ::get_enum_value
         */
        function testGetEnumValue() {

            $this->assertEquals(2,$this->_sut->get_enum_value('BAR'));

        }

        /**
         * @covers ::get_enum_values
         */
        function testGetEnumValues() {

            $reflection = new ReflectionClass($this->_sut_class);
            $class      = $this->_sut_class;
            $expected   = $reflection->getConstants();

            foreach($class::get_enum_values() as $key => $value) {

                if(! $key == '__default') {

                    $this->assertArrayHasKey($key, $expected);
                    $this->assertEquals($value, $expected[$key]);

                }

            }

        }

        /**
         * @covers ::has_enum_const
         */
        function testHasEnumConst() {

            $this->assertTrue($this->_sut->has_enum_const('FOO'));

        }

        /**
         * @covers ::get_enum_const
         */
        function testGetEnumConst() {

            $this->markTestIncomplete();
            $this->assertEquals('FOO',$this->_sut->get_enum_const(1));

        }

        /**
         * @covers ::get_enum_consts
         */
        function testGetEnumConsts() {

            $reflection = new ReflectionClass($this->_sut_class);
            $expected   = $reflection->getConstants();
            $class      = $this->_sut_class;
            $values     = $class::get_enum_consts($this->_sut);

            foreach($values as $value => $key) {

                $this->assertArrayHasKey($key, $expected);
                $this->assertEquals($value, $expected[$key]);

            }

        }

        /**
         * @covers ::has_enum_value
         */
        function testHasEnumValue() {

            $class = $this->_sut_class;
            $this->assertTrue($class::has_enum_value(2));
        }

        /**
         * @covers ::is_valid
         * @uses   has_enum_value
         */
        function testIsValid() {

            $class = $this->_sut_class;
            $this->assertTrue($class::is_valid(2));
        }

        /**
         * @covers ::__toString
         */
        function testToString() {

            $this->assertInternalType('string', $this->_sut->__toString(2));

        }

    }

}
