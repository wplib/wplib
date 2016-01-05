<?php

/**
 * Class WPLib_Args
 *
 */
class WPLib_Args
	implements IteratorAggregate, ArrayAccess, Serializable, Countable {

	/**
	 * @var array
	 */
	private $_args = array();

	/**
	 * @param array $args
	 */
	function __construct( $args ) {

		$this->_args = $args;

	}

	/**
	 * @return ArrayIterator
	 */
	function getIterator() {

		return new ArrayIterator( $this->_args );

	}

	/**
	 * @param mixed $offset
	 *
	 * @return bool
	 */
	function offsetExists( $offset ) {

		return isset( $this->_args[ $offset ] );

	}

	/**
	 * @param mixed $offset
	 *
	 * @return null
	 */
	function offsetGet( $offset ) {

		return $this->offsetExists( $offset ) ? $this->_args[ $offset ] : null;

	}

	/**
	 * @param mixed $offset
	 * @param mixed $value
	 */
	function offsetSet( $offset, $value ) {

		if ( is_null( $offset ) ) {

			$this->_args[] = $value;

		} else {

			$this->_args[ $offset ] = $value;

		}

	}

	/**
	 * @param mixed $offset
	 */
	function offsetUnset( $offset ) {

		if ( $this->offsetExists( $offset ) ) {

            unset( $this->_args[ $offset ] );

	    }

	}

	/**
	 * @return string
	 */
	function serialize() {

		return serialize( $this->_args );

	}

	/**
	 * @param string $serialized
	 */
	function unserialize( $serialized ) {

		$this->_args = unserialize( $serialized );

	}

	/**
	 * @return int
	 */
	function count() {

		return count( $this->_args );

	}

	/**
	 * @return array
	 */
	function args() {

		return $this->_args;

	}

	/**
	 */
	function clear_args() {

		$this->_args = array();

	}

	/**
	 *
	 * @param array $args
	 *
	 */
	function set_args( $args ) {

		$this->_args = $args;

	}

	/**
	 * @param $default
	 */
	function merge_default( $default ) {

		$this->_args = array_merge( $defaults, $this->_args );

	}

	/**
	 * @param string $property_name
	 *
	 * @return bool
	 */
	function __isset( $property_name ) {

		return isset( $this->_args[ $property_name ] );

	}

	/**
	 * @param string $property_name
	 *
	 * @return mixed|null
	 */
	function __get( $property_name ) {

		return isset( $this->_args[ $property_name ] )
			? $this->_args[ $property_name ]
			: null;

	}

}
