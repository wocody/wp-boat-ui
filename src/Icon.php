<?php
/**
 * Heroicons renderer.
 *
 * @package Wocody\BoatUI
 */

namespace Wocody\BoatUI;

/** Inline SVG icons from Heroicons v1: `outline` (24px stroke) and `solid` (20px fill). */
final class Icon {

	/**
	 * Icon map, loaded on first use.
	 *
	 * @var array<string, array<string, string>>|null
	 */
	private static $icons = null;

	/**
	 * Render an icon. Unknown names render nothing.
	 *
	 * @param string               $name Icon name, e.g. `cog` or `solid:check`.
	 * @param array<string, mixed> $args `class`, `set` (outline|solid), `label` (accessible name) and extra `attrs`.
	 * @return string
	 */
	public static function render( $name, array $args = array() ) {
		$set = isset( $args['set'] ) ? $args['set'] : 'outline';
		if ( false !== strpos( $name, ':' ) ) {
			list( $set, $name ) = explode( ':', $name, 2 );
		}
		$icons = self::icons();
		if ( ! isset( $icons[ $set ][ $name ] ) ) {
			return '';
		}
		$attrs = 'solid' === $set
			? array(
				'viewBox' => '0 0 20 20',
				'fill'    => 'currentColor',
			)
			: array(
				'fill'         => 'none',
				'viewBox'      => '0 0 24 24',
				'stroke-width' => '2',
				'stroke'       => 'currentColor',
			);
		$attrs = array( 'xmlns' => 'http://www.w3.org/2000/svg' ) + $attrs;
		if ( ! empty( $args['label'] ) ) {
			$attrs['role']       = 'img';
			$attrs['aria-label'] = $args['label'];
		} else {
			$attrs['aria-hidden'] = 'true';
			$attrs['focusable']   = 'false';
		}
		$attrs['class'] = isset( $args['class'] ) ? $args['class'] : null;
		if ( isset( $args['attrs'] ) && is_array( $args['attrs'] ) ) {
			$attrs = array_merge( $attrs, $args['attrs'] );
		}
		return '<svg' . Html::attrs( $attrs ) . '>' . $icons[ $set ][ $name ] . '</svg>';
	}

	/**
	 * Whether an icon exists.
	 *
	 * @param string $name Icon name, optionally prefixed with the set.
	 * @return bool
	 */
	public static function exists( $name ) {
		$set = 'outline';
		if ( false !== strpos( $name, ':' ) ) {
			list( $set, $name ) = explode( ':', $name, 2 );
		}
		$icons = self::icons();
		return isset( $icons[ $set ][ $name ] );
	}

	/**
	 * Available icon names per set.
	 *
	 * @return array<string, string[]>
	 */
	public static function names() {
		return array_map( 'array_keys', self::icons() );
	}

	/**
	 * Load the icon map.
	 *
	 * @return array<string, array<string, string>>
	 */
	private static function icons() {
		if ( null === self::$icons ) {
			self::$icons = require __DIR__ . '/icons.php';
		}
		return self::$icons;
	}
}
