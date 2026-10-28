<?php
/**
 * Admin screen layout.
 *
 * @package Wocody\BoatUI
 */

namespace Wocody\BoatUI;

/** Admin screen layout: brand, sidebar navigation, main column and optional aside. */
final class Page {

	/**
	 * Echo a full admin screen.
	 *
	 * @param array<string, mixed> $args See Page::html().
	 * @return void
	 */
	public static function render( array $args ) {
		echo self::html( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by the component renderers.
	}

	/**
	 * Build a full admin screen.
	 *
	 * Arguments: `id` (root id); `brand` (`name` and `product` wordmark parts, or `logo` as image URL or trusted SVG, plus `href`
	 * and `label`); `navigation` (see Page::navigation()); `search` (quick search over the navigation with ⌘K / Ctrl+K, default
	 * true); `router` (show one `[data-bui-view]` at a time from the URL hash, default false); `mobile_title`; `before` and
	 * `content` (trusted HTML for the main column, usually UI::paper() blocks); `aside` (trusted HTML or list for the right
	 * column); `class` and `attrs` for the root element.
	 *
	 * @param array<string, mixed> $args Screen arguments.
	 * @return string
	 */
	public static function html( array $args ) {
		$navigation = isset( $args['navigation'] ) ? (array) $args['navigation'] : array();
		$search     = ! isset( $args['search'] ) || $args['search'];
		$has_aside  = ! empty( $args['aside'] );
		$attrs      = isset( $args['attrs'] ) && is_array( $args['attrs'] ) ? $args['attrs'] : array();

		$attrs['id']              = isset( $args['id'] ) ? $args['id'] : null;
		$attrs['class']           = Html::classes( 'bui-root', isset( $args['class'] ) ? $args['class'] : '' );
		$attrs['data-bui-router'] = ! empty( $args['router'] );

		$sidebar = self::sidebar( $args, $navigation, $search );

		$main = Html::render( isset( $args['before'] ) ? $args['before'] : '' ) . Html::render( isset( $args['content'] ) ? $args['content'] : '' );
		$body = '<div class="bui-layout__main">' . $main . '</div>';
		if ( $has_aside ) {
			$aside = is_array( $args['aside'] ) && ! is_callable( $args['aside'] ) ? $args['aside'] : array( $args['aside'] );
			$body .= '<aside class="bui-layout__aside"><div class="bui-layout__aside-grid">' . Html::render( $aside ) . '</div></aside>';
		}

		$mobile = '';
		if ( $navigation ) {
			$mobile = '<div class="bui-mobile-navigation__top"><button type="button" class="bui-mobile-navigation__toggle" data-bui-modal-open="bui-mobile-navigation" aria-controls="bui-mobile-navigation"><span class="bui-sr-only">'
				. Html::esc( Strings::get( 'openNavigation' ) ) . '</span>' . Icon::render( 'menu-alt-1' ) . '</button>'
				. ( ! empty( $args['mobile_title'] ) ? '<span class="bui-mobile-navigation__title">' . Html::esc( $args['mobile_title'] ) . '</span>' : '' ) . '</div>'
				. '<dialog id="bui-mobile-navigation" class="bui-mobile-navigation__dialog" aria-label="' . Html::esc_attr( Strings::get( 'mainNavigation' ) ) . '"><div class="bui-mobile-navigation__panel">'
				. '<button type="button" class="bui-mobile-navigation__close" data-bui-modal-close><span class="bui-sr-only">' . Html::esc( Strings::get( 'closeNavigation' ) ) . '</span>' . Icon::render( 'x' ) . '</button>'
				. '<div class="bui-mobile-navigation__body">' . self::sidebar_content( $args, $navigation, false, 'mobile' ) . '</div></div></dialog>';
		}

		$overlays = '<div class="bui-notifications bui-notifications--bottom-left" aria-live="polite" aria-label="' . Html::esc_attr( Strings::get( 'notifications' ) ) . '" data-bui-notifications></div>';
		if ( $search && $navigation ) {
			$overlays .= self::search_modal();
		}

		$root = Html::tag(
			'div',
			$attrs,
			$mobile
			. '<div class="' . Html::esc_attr( Html::classes( 'bui-layout', $has_aside ? 'bui-layout--has-aside' : '' ) ) . '">'
			. ( $navigation || ! empty( $args['brand'] ) ? '<aside class="bui-layout__sidebar">' . $sidebar . '</aside>' : '' )
			. '<div class="bui-layout__body">' . $body . '</div></div>'
			. $overlays
		);

		// WordPress moves admin notices after .wp-header-end; keep them outside the scoped reset.
		return '<div class="bui-wp-notices"><hr class="wp-header-end"></div>' . $root;
	}

	/**
	 * Desktop sidebar.
	 *
	 * @param array<string, mixed> $args       Page arguments.
	 * @param array<int, mixed>    $navigation Navigation.
	 * @param bool                 $search     Show search.
	 * @return string
	 */
	private static function sidebar( array $args, array $navigation, $search ) {
		return self::sidebar_content( $args, $navigation, $search, 'desktop' );
	}

	/**
	 * Sidebar header and navigation.
	 *
	 * @param array<string, mixed> $args       Page arguments.
	 * @param array<int, mixed>    $navigation Navigation.
	 * @param bool                 $search     Show search.
	 * @param string               $context    desktop|mobile.
	 * @return string
	 */
	private static function sidebar_content( array $args, array $navigation, $search, $context ) {
		$header = ! empty( $args['brand'] ) ? self::brand( $args['brand'] ) : '';
		if ( $search && $navigation ) {
			$header .= '<button type="button" class="bui-search-button" data-bui-search-open>' . Icon::render( 'solid:search', array( 'class' => 'bui-search-button__icon' ) )
				. '<span class="bui-search-button__label">' . Html::esc( Strings::get( 'search' ) ) . '</span><kbd class="bui-search-button__kbd" data-bui-search-shortcut>Ctrl K</kbd></button>';
		}
		$out = '' !== $header ? '<header class="bui-sidebar-navigation__header">' . $header . '</header>' : '';
		if ( $navigation ) {
			$out .= self::navigation( $navigation, $context );
		}
		return '<nav class="bui-sidebar-navigation" aria-label="' . Html::esc_attr( Strings::get( 'mainNavigation' ) ) . '">' . $out . '</nav>';
	}

	/**
	 * Brand wordmark or logo.
	 *
	 * @param array<string, mixed> $brand Brand arguments.
	 * @return string
	 */
	public static function brand( array $brand ) {
		$label = isset( $brand['label'] ) ? $brand['label'] : trim( ( isset( $brand['name'] ) ? $brand['name'] : '' ) . ' ' . ( isset( $brand['product'] ) ? $brand['product'] : '' ) );
		if ( ! empty( $brand['logo'] ) ) {
			$logo  = (string) $brand['logo'];
			$inner = 0 === strpos( ltrim( $logo ), '<svg' )
				? $logo
				: '<img class="bui-brand__logo" src="' . Html::esc_url( $logo ) . '" alt="">';
		} else {
			$inner = '<span class="bui-brand__name">' . Html::esc( isset( $brand['name'] ) ? $brand['name'] : '' ) . '</span>'
				. ( ! empty( $brand['product'] ) ? '<span class="bui-brand__product">' . Html::esc( $brand['product'] ) . '</span><span class="bui-brand__slash" aria-hidden="true">/</span>' : '' );
		}
		return Html::tag(
			'a',
			array(
				'class'      => 'bui-brand',
				'href'       => isset( $brand['href'] ) ? $brand['href'] : '#',
				'aria-label' => $label,
			),
			$inner
		);
	}

	/**
	 * Sidebar navigation.
	 *
	 * Each entry is either a link (`label`, `href`, `icon`, `id`, `active`, `badge`, `view`, `attrs`)
	 * or a collapsible group (`label`, `icon`, `items` (links), `open` (default true), `limit` (items shown before "Show more")).
	 * `view` links a `[data-bui-view]` element when the page router is on.
	 *
	 * @param array<int, mixed> $entries Navigation entries.
	 * @param string            $context desktop|mobile, used to keep ids unique.
	 * @return string
	 */
	public static function navigation( array $entries, $context = 'desktop' ) {
		$groups = '';
		$loose  = '';
		foreach ( $entries as $index => $entry ) {
			if ( empty( $entry['items'] ) ) {
				$loose .= self::nav_item( $entry, true );
				continue;
			}
			if ( '' !== $loose ) {
				$groups .= '<ul role="list" class="bui-sidebar-navigation__list">' . $loose . '</ul>';
				$loose   = '';
			}
			$active = false;
			foreach ( $entry['items'] as $item ) {
				$active = $active || ! empty( $item['active'] );
			}
			$open    = ! isset( $entry['open'] ) || $entry['open'] || $active;
			$limit   = isset( $entry['limit'] ) ? (int) $entry['limit'] : 0;
			$items   = '';
			$extra   = '';
			$more_id = 'bui-nav-more-' . $context . '-' . $index;
			foreach ( array_values( $entry['items'] ) as $position => $item ) {
				if ( $limit > 0 && $position >= $limit && empty( $item['active'] ) ) {
					$extra .= self::nav_item( $item, false );
				} else {
					$items .= self::nav_item( $item, false );
				}
			}
			if ( '' !== $extra ) {
				$items .= '<li class="bui-sidebar-navigation__more" id="' . Html::esc_attr( $more_id ) . '" hidden><ul role="list" class="bui-sidebar-navigation__list">' . $extra . '</ul></li>'
					. '<li class="bui-sidebar-navigation__limiter"><hr><button type="button" class="bui-sidebar-navigation__limiter-toggle" aria-expanded="false" aria-controls="' . Html::esc_attr( $more_id ) . '" data-bui-limiter>'
					. '<span data-bui-limiter-label>' . Html::esc( Strings::get( 'showMore' ) ) . '</span>' . Icon::render( 'chevron-down' ) . '</button><hr></li>';
			}
			$summary = '<summary class="bui-sidebar-navigation__collapsible-button">'
				. ( ! empty( $entry['icon'] ) ? Icon::render( $entry['icon'], array( 'class' => 'bui-sidebar-navigation__icon' ) ) : '' )
				. Html::esc( $entry['label'] ) . Icon::render( 'chevron-up', array( 'class' => 'bui-sidebar-navigation__chevron' ) ) . '</summary>';
			$groups .= Html::tag(
				'details',
				array(
					'class' => 'bui-sidebar-navigation__collapsible',
					'open'  => $open,
				),
				$summary . '<ul role="list" class="bui-sidebar-navigation__list bui-sidebar-navigation__list--indented">' . $items . '</ul>'
			);
		}
		if ( '' !== $loose ) {
			$groups .= '<ul role="list" class="bui-sidebar-navigation__list">' . $loose . '</ul>';
		}
		return '<div class="bui-sidebar-navigation__groups">' . $groups . '</div>';
	}

	/**
	 * One navigation link.
	 *
	 * @param array<string, mixed> $item     Item.
	 * @param bool                 $top_level Whether it is outside a group (icons are shown).
	 * @return string
	 */
	private static function nav_item( array $item, $top_level ) {
		$active = ! empty( $item['active'] );
		$inner  = $top_level && ! empty( $item['icon'] ) ? Icon::render( $item['icon'], array( 'class' => 'bui-sidebar-navigation__icon' ) ) : '';
		$inner .= '<span>' . Html::esc( $item['label'] ) . '</span>';
		if ( isset( $item['badge'] ) && '' !== $item['badge'] ) {
			$badge  = is_array( $item['badge'] ) ? $item['badge'] : array( $item['badge'], 'plain' );
			$inner .= UI::badge(
				$badge[0],
				isset( $badge[1] ) ? $badge[1] : 'plain',
				array(
					'size'  => 'small',
					'class' => 'bui-sidebar-navigation__badge',
				)
			);
		}
		$link = Html::tag(
			'a',
			( isset( $item['attrs'] ) && is_array( $item['attrs'] ) ? $item['attrs'] : array() ) + array(
				'class'         => array(
					'bui-sidebar-navigation__link',
					$active ? 'bui-sidebar-navigation__item--active' : '',
				),
				'href'          => isset( $item['href'] ) ? $item['href'] : ( isset( $item['view'] ) ? '#' . $item['view'] : '#' ),
				'aria-current'  => $active ? 'page' : null,
				'data-bui-nav'  => isset( $item['view'] ) ? $item['view'] : null,
				'data-bui-item' => isset( $item['id'] ) ? $item['id'] : null,
			),
			$inner
		);
		return '<li class="bui-sidebar-navigation__item">' . $link . '</li>';
	}

	/**
	 * Quick search dialog.
	 *
	 * @return string
	 */
	private static function search_modal() {
		return '<dialog id="bui-search" class="bui-modal bui-modal--top-center bui-search-modal" aria-label="' . Html::esc_attr( Strings::get( 'searchLabel' ) ) . '"><div class="bui-modal__layout"><div class="bui-modal__panel">'
			. '<div class="bui-search-modal__field">' . Icon::render( 'solid:search' )
			. '<input type="search" class="bui-search-modal__input" role="combobox" aria-expanded="true" aria-controls="bui-search-results" aria-autocomplete="list" autocomplete="off" placeholder="' . Html::esc_attr( Strings::get( 'searchPlaceholder' ) ) . '" aria-label="' . Html::esc_attr( Strings::get( 'searchLabel' ) ) . '" data-bui-search-input></div>'
			. '<div id="bui-search-results" class="bui-search-modal__results" role="listbox" data-bui-search-results></div>'
			. '</div></div></dialog>';
	}
}
