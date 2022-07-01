<?php

namespace Tests\WPLib {

    use \WP_UnitTestCase;

    class WPLib_Test_Case extends WP_UnitTestCase {

        public function getReflectionPropertyValue( $class, $property ) {

            $reflection = new \ReflectionProperty( $class, $property );
            $reflection->setAccessible( true );
            return $reflection->getValue( $class );

        }

        public function setReflectionPropertyValue( $class, $property, $value ) {

            $reflection = new \ReflectionProperty( $class, $property );
            $reflection->setAccessible( true );
            return $reflection->setValue( $class, $value );

        }

        public function reflectionMethodInvoke( $class, $method ) {

            $reflection = new \ReflectionMethod( $class, $method );
            $reflection->setAccessible( true );
            return $reflection->invoke( $class );

        }

        public function reflectionMethodInvokeArgs( $class, $method, $args ) {

            $reflection = new \ReflectionMethod( $class, $method );
            $reflection->setAccessible( true );
            return $reflection->invoke( $class, $args );

        }

    }

}
