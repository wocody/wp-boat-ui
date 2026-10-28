<?php
/**
 * Escaping and markup helpers.
 *
 * @package Wocody\BoatUI
 */

namespace Wocody\BoatUI;

/** Escaping and markup helpers that also work outside WordPress (tests, previews). */
final class Html {

	/**
	 * Unique id counter.
	 *
	 * @var int
	 */
	private static $counter = 0;

	/**
	 * Escape text for HTML.
	 *
	 * @param mixed $text Text.
	 * @return string
	 */
	public static function esc( $text ) {
		$text = (string) $text;
		return function_exists( 'esc_html' ) ? esc_html( $text ) : htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}

	/**
	 * Escape an attribute value.
	 *
	 * @param mixed $text Value.
	 * @return string
	 */
	public static function esc_attr( $text ) {
		$text = (string) $text;
		return function_exists( 'esc_attr' ) ? esc_attr( $text ) : htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}

	/**
	 * Escape a URL.
	 *
	 * @param mixed $url URL.
	 * @return string
	 */
	public static function esc_url( $url ) {
		$url = (string) $url;
		if ( function_exists( 'esc_url' ) ) {
			return esc_url( $url );
		}
		if ( preg_match( '/^\s*(javascript|data|vbscript):/i', $url ) ) {
			return '';
		}
		return htmlspecialchars( $url, ENT_QUOTES, 'UTF-8' );
	}

	/**
	 * Merge class lists. Accepts strings, lists and `class => bool` maps.
	 *
	 * @param mixed ...$lists Class lists.
	 * @return string
	 */
	public static function classes( ...$lists ) {
		$classes = array();
		foreach ( $lists as $list ) {
			if ( is_string( $list ) ) {
				$list = preg_split( '/\s+/', $list, -1, PREG_SPLIT_NO_EMPTY );
			}
			if ( ! is_array( $list ) ) {
				continue;
			}
			foreach ( $list as $key => $value ) {
				if ( is_int( $key ) ) {
					if ( is_string( $value ) && '' !== trim( $value ) ) {
						foreach ( preg_split( '/\s+/', $value, -1, PREG_SPLIT_NO_EMPTY ) as $name ) {
							$classes[ $name ] = true;
						}
					}
				} elseif ( $value ) {
					$classes[ $key ] = true;
				}
			}
		}
		return implode( ' ', array_keys( $classes ) );
	}

	/**
	 * Build an attribute string with a leading space.
	 *
	 * Null and false values are skipped, true renders a bare attribute and
	 * arrays become space separated (class) or JSON (data-*) values.
	 *
	 * @param array<string, mixed> $attrs Attributes.
	 * @return string
	 */
	public static function attrs( array $attrs ) {
		$out = '';
		foreach ( $attrs as $name => $value ) {
			if ( null === $value || false === $value || ! preg_match( '/^[a-zA-Z_:][a-zA-Z0-9_:.\-]*$/D', (string) $name ) ) {
				continue;
			}
			if ( 'class' === $name ) {
				$value = self::classes( $value );
				if ( '' === $value ) {
					continue;
				}
			}
			if ( true === $value ) {
				$out .= ' ' . $name;
				continue;
			}
			if ( is_array( $value ) ) {
				$value = 0 === strpos( $name, 'data-' ) ? self::json( $value ) : implode( ' ', $value );
			}
			if ( in_array( $name, array( 'href', 'src', 'action', 'formaction' ), true ) ) {
				$value = self::esc_url( $value );
			} else {
				$value = self::esc_attr( $value );
			}
			$out .= ' ' . $name . '="' . $value . '"';
		}
		return $out;
	}

	/**
	 * Render trusted content: a callable that echoes or returns markup, a string or a list of them.
	 *
	 * @param mixed $content Content.
	 * @return string
	 */
	public static function render( $content ) {
		if ( null === $content || false === $content ) {
			return '';
		}
		if ( is_array( $content ) && ! is_callable( $content ) ) {
			return implode( '', array_map( array( __CLASS__, 'render' ), $content ) );
		}
		if ( is_callable( $content ) && ! is_string( $content ) ) {
			ob_start();
			$returned = call_user_func( $content );
			$echoed   = (string) ob_get_clean();
			return $echoed . ( is_string( $returned ) ? $returned : '' );
		}
		return (string) $content;
	}

	/**
	 * Generate a unique element id.
	 *
	 * @param string $prefix Prefix.
	 * @return string
	 */
	public static function id( $prefix = 'bui' ) {
		++self::$counter;
		return $prefix . '-' . self::$counter;
	}

	/**
	 * Build an element.
	 *
	 * @param string               $tag     Tag name.
	 * @param array<string, mixed> $attrs   Attributes.
	 * @param string|null          $content Trusted inner HTML, or null for a void element.
	 * @return string
	 */
	public static function tag( $tag, array $attrs = array(), $content = '' ) {
		$open = '<' . $tag . self::attrs( $attrs ) . '>';
		return null === $content ? $open : $open . $content . '</' . $tag . '>';
	}

	/**
	 * JSON encode, with the WordPress encoder when available.
	 *
	 * @param mixed $value Value.
	 * @return string
	 */
	public static function json( $value ) {
		$json = function_exists( 'wp_json_encode' ) ? wp_json_encode( $value ) : json_encode( $value ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode -- Fallback outside WordPress.
		return false === $json ? '' : $json;
	}
}
