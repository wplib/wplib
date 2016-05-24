<?php

/**
 * Class WPLib_Html_Element
 * 
 * WORK IN PROGRESS - DO NOT USE YET
 * 
 */
class WPLib_Html_Element {

    /**
     * @var array
     */
    private static $_tag_attribute_names = array();

    /**
     * @var string[]
     */
    static private $_void_elements;

    /**
     * @var string
     */
    var $tag_name;

    /**
     * @var string
     */
    var $element_value;

    /**
     * @var string[]
     */
    private $_attribute_pairs;

    /**
     * @var bool
     */
    private $_attributes_parsed;

    /**
     * @param string $tag_name
     * @param array $attribute_pairs
     * @param null|callable|string $value
     */
    function __construct( $tag_name, $attribute_pairs = array(), $value = null ) {

        $this->reset_element( $tag_name, $attributes, $value );

    }

    /**
     * @param string $tag_name
     * @param array $attribute_pairs
     * @param null|callable|string $value
     */
    function reset_element( $tag_name, $attribute_pairs = array(), $value = null ) {

        $this->tag_name = $tag_name;

        foreach ( wp_parse_args( $attribute_pairs ) as $name => $value ) {

            if ( preg_match( '#^html_(.*?)$#', $name, $match ) ) {

                $attribute_pairs[ $match[1] ] = $value;

            }

        }

        $this->element_value = $value;

        $this->_attributes_parsed = false;

    }

    /**
     * @return bool
     */
    function is_void_element() {

        static $void_elements = false;

        if ( ! $void_elements ) {

            $void_elements = implode( '|', self::void_html_tags() );

        }

        return preg_match( "#^({$void_elements})$#i", $this->tag_name )
            ? true
            : false;
    }

    /**
     * @return array
     */
    function element_html() {

        $html = "<{$this->tag_name} " . $this->attributes_html() . '>';

        if ( ! $this->is_void_element() ) {

            $value = is_callable( $this->element_value )
                ? call_user_func( $this->element_value, $this )
                : $this->element_value;

            $html .= "{$value}</{$this->tag_name}>";

        }

        return $html;
    }

    /**
     * @return string
     */
    function attributes_html() {

        $html = '';

        foreach( $this->attribute_pairs() as $name => $value ) {

            if ( ! is_null( $value ) ) {

                $html .= " {$name}=\"{$value}\"";

            }

        }

        return $html;

    }

    /**
     * @return string[]
     */
    function attribute_pairs() {

        if ( ! $this->_attributes_parsed ) {

            $attribute_pairs = array();

            foreach(self::get_html_tag_attribute_names( $this->tag_name ) as $name ) {

                if ( ! in_array( $name, $attribute_names ) ) {

                    if ( ! preg_match( '#^data-#', $name ) ) {

                        /*
                         * Unknown attribute.  Ignore it.
                         */
                        continue;

                    }


                }

                $attribute_pairs[ $name ] = $value;

            }

            $this->_attribute_pairs = $attribute_pairs;

            $this->_attributes_parsed = true;

        }

        return $this->_attribute_pairs;

    }

    /**
     * @param string $attribute_name
     * @return string|null
     */
    function get_attribute_value( $attribute_name ) {

        $attributes = $this->attribute_pairs();

        return ! empty( $attributes[$attribute_name] )
            ? $attributes[$attribute_name]
            : null;

    }

    /**
     * Sanitizes an attribute name for based on the current tag name
     *
     * @param string $attribute_name
     * @return string|null
     */
    function sanitize_attribute_name( $attribute_name ) {

        $attribute_name = strtolower( $attribute_name );

        $attribute_names = self::get_html_tag_attribute_names( $this->tag_name );

        if ( ! in_array( $attribute_name, $attribute_names ) ) {

            if ( ! preg_match( '#^data-', $attribute_name ) ) {

                $attribute_name = null;

            } else {

                $attribute_name = self::sanitize_html_attribute_name( $attribute_name );

            }

        }

        return $attribute_name;

    }

    /**
     * @param string $attribute_name
     * @return string|null
     */
    function get_attribute_html( $attribute_name ) {

        $attribute_name = $this->sanitize_attribute_name( $attribute_name );

        $value = esc_attr( $this->get_attribute_value( $attribute_name ) );

        return $value
            ? " {$attribute_name}=\"{$value}\""
            : false;

    }

    /**
     * Clean and sanitize any attribute name to strip out any invalid characters
     *
     * @param string $attribute_name
     * @return string
     *
     * @see https://html.spec.whatwg.org/multipage/syntax.html#attributes-2
     * @see https://html.spec.whatwg.org/multipage/infrastructure.html#space-character
     * @see http://stackoverflow.com/a/1497928/102699
     * @see http://magp.ie/2011/01/06/remove-non-utf8-characters-from-string-with-php/
     *
     */
    static function sanitize_html_attribute_name( $attribute_name ) {

        /*
         * First ensure everything is in UTF-8 format
         */
        $attribute_name = mb_convert_encoding( $attribute_name, 'UTF-8', 'UTF-8' );

        /*
         * Remove overly long 2 byte sequences, as well as characters above U+10000
         */
        $attribute_name = preg_replace(
            '#[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]'.
            '|[\x00-\x7F][\x80-\xBF]+'.
            '|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*'.
            '|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})'.
            '|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})#S',
            '',
            $attribute_name
        );

        /*
         * Remove overly long 3 byte sequences and UTF-16 surrogates
         */
        $attribute_name = preg_replace(
            '#\xE0[\x80-\x9F][\x80-\xBF]|\xED[\xA0-\xBF][\x80-\xBF]#S',
            '',
            $attribute_name
        );

        /*
         * Remove invalid attribute characters
         */
        $attribute_name = preg_replace( '#[\'">/= \n\r\t\x00-\x1F\x7F\]#', '', $attribute_name );

        return $attribute_name;

    }

    /**
     * Returns list of void HTML elements
     *
     * @return array
     */
    static function void_html_tags() {

        if ( ! isset( self::$_void_elements ) ) {

            /**
             * Filter the list of void HTML elements to allow for future changes to HTML
             *
             * @since 0.13.0
             *
             * @param string[]  $void_elements The array of void HTML elements names
             */
            self::$_void_elements = apply_filters( 'wplib_void_html_elements', array('area',
                'base',
                'br',
                'col',
                'command',
                'embed',
                'hr',
                'img',
                'input',
                'keygen',
                'link',
                'meta',
                'param',
                'source',
                'track',
                'wbr'
            ) );
        }

        return self::$_void_elements;

    }

    /**
     * @param string $tag_name
     * @param string[] $attribute_pairs
     * @param mixed $value
     * @param bool $reuse
     * @return WPLib_Html_Element
     */
    static function get_html_tag_html($tag_name, $attribute_pairs, $value, $reuse = false ) {

        return self::dispense_html_element( $tag_name, $attribute_pairs, $value, $reuse )->element_html();

    }

    /**
     * @param string $tag_name
     * @param string[] $attribute_pairs
     * @param mixed $value
     * @param bool $reuse
     * @return WPLib_Html_Element
     */
    static function dispense_html_element( $tag_name, $attribute_pairs = array(), $value = null, $reuse = false ) {

        if ( ! $reuse ) {

            $element = new WPLib_Html_Element( $tag_name, $attribute_pairs, $value );

        } else {

            /**
             * @var WPLib_Html_Element $reusable_element
             */
            static $reusable_element = false;

            if ( ! $reusable_element ) {

                $reusable_element = new WPLib_Html_Element( $tag_name, $attribute_pairs, $value );

            } else {

                $reusable_element->reset_element( $tag_name, $attribute_pairs, $value );

            }

            $element = $reusable_element;

        }

        return $element;

    }

    /**
     * This returns the attribute names for an HTML element.
     *
     * @param string $tag_name
     * @return string[]
     */
    static function get_html_tag_attribute_names($tag_name ) {

        if ( ! isset( self::$_tag_attribute_names[ $tag_name ] ) ) {

            /**
             * @see http://www.w3.org/TR/html5/dom.html#global-attribute_names
             */
            $attribute_names = array(
                'accesskey', 'class', 'contenteditable', 'dir', 'draggable', 'dropzone',
                'hidden', 'id', 'lang', 'spellcheck', 'style', 'tabindex', 'title', 'translate'
            );

            switch ( $tag_name ) {

                case 'input':
                    $more_attributes = array(
                        'accept', 'alt', 'autocomplete', 'autofocus', 'autosave', 'checked', 'dirname', 'disabled',
                        'form', 'formaction', 'formenctype', 'formmethod', 'formnovalidate', 'formtarget',
                        'height', 'inputmode', 'list', 'max', 'maxlength', 'min', 'minlength', 'multiple',
                        'name', 'pattern', 'placeholder', 'readonly', 'required', 'selectionDirection',
                        'size', 'src', 'step', 'type', 'value', 'width'
                    );
                    break;

                case 'textarea':
                    $more_attributes = array( 'cols', 'name', 'rows', 'tabindex', 'wrap' );
                    break;

                case 'label':
                    $more_attributes = array( 'for', 'form' );
                    break;

                case 'ul':
                    $more_attributes = array( 'compact', 'type' );
                    break;

                case 'ol':
                    $more_attributes = array( 'compact', 'reversed', 'start', 'type' );
                    break;

                case 'li':
                    $more_attributes = array( 'type', 'value' );
                    break;

                case 'a':
                    $more_attributes = array( 'charset', 'coords', 'download', 'href', 'hreflang', 'media', 'rel', 'target', 'type' );
                    break;

                case 'section':
                case 'div':
                case 'span':
                default:
                    $more_attributes = false;
                    break;
            }

            if ( $more_attributes ) {

                $attribute_names = array_merge( $attribute_names, $more_attributes );

            }

            /**
             * Filter the list of void HTML elements to allow for future changes to HTML
             *
             * @since 0.13.0
             *
             * @param string[] $attribute_names The array of void HTML tag attribute names.
             * @param string   $tag_name Name of HTML for which these attribute names are associated.
             */
            $attribute_names = apply_filters( 'wplib_html_tag_attribute_names', $attribute_names, $tag_name );


            self::$_tag_attribute_names[ $tag_name ] = $attribute_names;

        }

        return self::$_tag_attribute_names[ $tag_name ];

    }

}


