<?php
/**
 * Dependency-free smoke tests: php tests/run.php
 *
 * @package Wocody\BoatUI
 */

use Wocody\BoatUI\Html;
use Wocody\BoatUI\Page;
use Wocody\BoatUI\UI;
use Wocody\BoatUI\Demo;

require dirname( __DIR__ ) . '/bootstrap.php';

$failures = 0;
$checks   = 0;
$check    = static function ( $label, $condition ) use ( &$failures, &$checks ) {
	++$checks;
	if ( ! $condition ) {
		++$failures;
		fwrite( STDERR, "FAIL: {$label}\n" );
	}
};

// Version election: the newest registered copy wins.
Wocody_Boat_UI_Loader::register( '0.9.0', '/nonexistent' );
$check( 'newest version elected', '1.0.0' === UI::version() );
$check( 'root is this copy', realpath( dirname( __DIR__ ) ) === realpath( UI::root() ) );

// Escaping.
$button = UI::button( '<b>x</b>', array( 'attrs' => array( 'data-x' => '"><script>' ) ) );
$check( 'button label escaped', false === strpos( $button, '<b>' ) );
$check( 'button attribute escaped', false === strpos( $button, '"><script>' ) );
$check( 'javascript: urls dropped', false === strpos( UI::link( 'a', 'javascript:alert(1)' ), 'javascript:' ) );
$check( 'button defaults to type=button', false !== strpos( UI::button( 'Go' ), 'type="button"' ) );
$check( 'button href renders link', 0 === strpos( UI::button( 'Go', array( 'href' => '/x' ) ), '<a ' ) );
$check( 'classes merge', 'a b c' === Html::classes( 'a b', array( 'c' => true, 'd' => false ), array( 'a' ) ) );

// Fields.
$field = UI::text_field(
	array(
		'id'          => 'name',
		'label'       => 'Name',
		'description' => 'Help',
		'validation'  => array(
			'variant' => 'error',
			'message' => 'Bad',
		),
	)
);
$check( 'field label associated', false !== strpos( $field, 'for="name"' ) );
$check( 'field described by validation and description', false !== strpos( $field, 'aria-describedby="name__validation name__description"' ) );
$check( 'field invalid', false !== strpos( $field, 'aria-invalid="true"' ) );

$toggle = UI::toggle_field(
	array(
		'id'      => 't',
		'name'    => 'opt',
		'label'   => 'Option',
		'checked' => true,
	)
);
$check( 'toggle is a native switch checkbox', false !== strpos( $toggle, 'type="checkbox" role="switch"' ) );
$check( 'toggle checked', false !== strpos( $toggle, ' checked' ) );
$check( 'toggle labelled', false !== strpos( $toggle, 'aria-labelledby="t__label"' ) );

$select = UI::select_field(
	array(
		'options' => array(
			'a' => 'A',
			'b' => 'B',
		),
		'value'   => 'b',
	)
);
$check( 'select marks value', false !== strpos( $select, '<option value="b" selected>' ) );

$group = UI::checkbox_group(
	array(
		'name'    => 'types',
		'options' => array( 'post' => 'Posts' ),
		'value'   => array( 'post' ),
	)
);
$check( 'checkbox group uses array names', false !== strpos( $group, 'name="types[]"' ) );

$table = UI::table(
	array(
		'columns' => array(
			'a' => 'A',
			'b' => 'B',
		),
		'rows'    => array(),
		'empty'   => 'Nothing',
	)
);
$check( 'empty table spans columns', false !== strpos( $table, 'colspan="2"' ) );

// Callable content is captured.
$paper = UI::paper(
	array(
		'title'   => 'T',
		'content' => static function () {
			echo '<p>echoed</p>';
		},
	)
);
$check( 'callable content rendered', false !== strpos( $paper, '<p>echoed</p>' ) );

// Full showcase page.
$page = Page::html( Demo::config() );
$check( 'page root', false !== strpos( $page, 'class="bui-root"' ) );
$check( 'router enabled', false !== strpos( $page, 'data-bui-router' ) );
$check( 'search dialog', false !== strpos( $page, 'id="bui-search"' ) );
$check( 'mobile navigation', false !== strpos( $page, 'id="bui-mobile-navigation"' ) );
$check( 'wp header end for notices', false !== strpos( $page, 'wp-header-end' ) );
$check( 'aside rendered', false !== strpos( $page, 'bui-layout__aside' ) );
libxml_use_internal_errors( true );
$dom = new DOMDocument();
$dom->loadHTML( '<!doctype html><html><body>' . $page . '</body></html>' );
$ids = array();
foreach ( ( new DOMXPath( $dom ) )->query( '//*[@id]' ) as $node ) {
	$ids[] = $node->getAttribute( 'id' );
}
$duplicates = array_diff_assoc( $ids, array_unique( $ids ) );
$check( 'no duplicate ids (' . implode( ',', $duplicates ) . ')', ! $duplicates );

echo "{$checks} checks, {$failures} failures\n";
exit( $failures ? 1 : 0 );
