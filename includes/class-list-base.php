<?php

/**
 * Class WPLib_List_Base
 *
 * @future https://github.com/wplib/wplib/issues/4
 * @see https://github.com/wplib/wplib/commit/8dc27c368e84f7ba6e1448753e1b1f082a60ac6d#commitcomment-11026937
 */
abstract class WPLib_List_Base
	extends WPLib_Base
	implements IteratorAggregate, ArrayAccess, Serializable, Countable {

	/**
	 * @var array
	 */
	private $_elements = array();

	/**
	 * @var string
	 */
	protected $_index_by = false;

	/**
	 * @param array $elements
	 * @param array $args
	 */
	function __construct( $elements = array(), $args = array() ) {

		$this->_elements = $elements;

		parent::__construct( $args );

		if ( $this->_index_by ) {

			$this->_reindex_elements();

		}

	}

	/**
	 * @return ArrayIterator
	 */
	function getIterator() {

		return new ArrayIterator( $this->_elements );

	}

	/**
	 * @param mixed $offset
	 *
	 * @return bool
	 */
	function offsetExists( $offset ) {

		return isset( $this->_elements[ $offset ] );

	}

	/**
	 * @param mixed $offset
	 *
	 * @return null
	 */
	function offsetGet( $offset ) {

		return $this->offsetExists( $offset ) ? $this->_elements[ $offset ] : null;

	}

	/**
	 * @param mixed $offset
	 * @param mixed $value
	 */
	function offsetSet( $offset, $value ) {

		if ( is_null( $offset ) ) {

			$this->_elements[] = $value;

		} else {

			$this->_elements[ $offset ] = $value;

		}

	}

	/**
	 * @param mixed $offset
	 */
	function offsetUnset( $offset ) {

		if ( $this->offsetExists( $offset ) ) {

            unset( $this->_elements[ $offset ] );

	    }

	}

	/**
	 * @return string
	 */
	function serialize() {

		return serialize( $this->_elements );

	}

	/**
	 * @param string $serialized
	 */
	function unserialize( $serialized ) {

		$this->_elements = unserialize( $serialized );

	}

	/**
	 * @return int
	 */
	function count() {

		return count( $this->_elements );

	}

	/**
	 *
	 */
	private function _reindex_elements() {

		if ( $this->_index_by ) {

			$elements = array();

			foreach ( $this->_elements as $element ) {

				if ( ! ( $index = $this->get_element_index( $element ) ) ) {
					/*
					 * All elements must have an index to be reindexed.
					 * If not, bail out.
					 */
					break;

				}

				$elements[ $index ] = $element;

			}

			/*
			 * Only use the new index if all elements had an index
			 */
			if ( count( $this->_elements ) === count( $elements ) ) {

				$this->_elements = $elements;

			}

		}

	}

	/**
	 * Get index value for an object stored in a list.
	 *
	 * Either a property of a method can generate an index value, or a subclass can generate a different one.
	 * Returns false it $this->_index_by not set.
	 *
	 * @param object $element
	 *
	 * @return bool|mixed
	 */
	function get_element_index( $element ) {

		if ( ! $this->_index_by ) {

			$index = false;

		} else if ( property_exists( $element, $index_by = $this->_index_by ) ) {

			$index = $element->$index_by;

		} else if ( method_exists( $element, $index_by ) ) {

			$index = call_user_func( array( $element, $index_by ) );

		} else {

			$index = false;

		}

		return $index;

	}

	/**
	 * @return array
	 */
	function elements() {

		return $this->_elements;

	}

	/**
	 */
	function clear_elements() {

		$this->_elements = array();

	}

	/**
	 *
	 * @param array $elements
	 *
	 */
	function set_elements( $elements ) {

		$this->_elements = $elements;

	}

	/**
	 * @param string $template
	 * @param array $args
	 */
	function the_template( $template, $args = array() ) {

		echo $this->get_template_html( $template, $args );

	}

	/**
	 * @param string $template
	 * @param array $args
	 * @return string
	 */
	function get_template_html( $template, $args = array() ) {

		$cache_key = "wplib_template[{$template}][" . md5( serialize( $args ) ) . ']';

		if ( ! ( $output = WPLib::cache_get( $cache_key ) ) ) {

			ob_start();

			$index = 0;

			foreach ( $this->elements() as $element ) {

				/**
				 * @var WPLib_Item_Base $element
				 *
				 * @future Create a interface that would indicate a class has a 'the_template' method.
				 *
				 */
				$args[ 'index' ] = $index++;
				$element->the_template( $template, $args );

			}

			WPLib::cache_set( $cache_key, $output = ob_get_clean() );

		}
		return $output;

	}
}
