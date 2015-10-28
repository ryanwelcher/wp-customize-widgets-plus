<?php

namespace CustomizeWidgetsPlus;

class Test_Customize_Snapshot_Manager extends Base_Test_Case {

	/**
	 * A valid UUID.
	 * @type string
	 */
	const UUID = '65aee1ff-af47-47df-9e14-9c69b3017cd3';

	/**
	 * @var \WP_Customize_Manager
	 */
	protected $customize;

	/**
	 * @var Customize_Snapshot_Manager
	 */
	protected $manager;

	/**
	 * @var int
	 */
	protected $user_id;

	function setUp() {
		parent::setUp();
		require_once( ABSPATH . WPINC . '/class-wp-customize-manager.php' );
		$GLOBALS['wp_customize'] = new \WP_Customize_Manager();
		$this->wp_customize = $GLOBALS['wp_customize'];

		$this->wp_customize->add_setting( 'foo', array( 'default' => 'foo_default' ) );
		$this->wp_customize->add_setting( 'bar', array( 'default' => 'bar_default' ) );

		$this->manager = new Customize_Snapshot_Manager( $this->plugin );
		$this->user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );

		remove_action( 'after_setup_theme', 'twentyfifteen_setup' );
	}

	function tearDown() {
		$this->wp_customize = null;
		$this->manager = null;
		unset( $GLOBALS['wp_customize'] );
		unset( $GLOBALS['wp_scripts'] );
		unset( $_REQUEST['customize_snapshot_uuid'] );
		unset( $_REQUEST['scope'] );
		parent::tearDown();
	}

	function do_customize_on() {
		$_REQUEST['wp_customize'] = 'on';
	}

	function do_customize_boot_actions( $on = false ) {
		$_SERVER['REQUEST_METHOD'] = 'POST';
		do_action( 'setup_theme' );
		$_REQUEST['nonce'] = wp_create_nonce( 'preview-customize_' . $this->wp_customize->theme()->get_stylesheet() );
		do_action( 'after_setup_theme' );
		do_action( 'init' );
		do_action( 'wp_loaded' );
		do_action( 'wp', $GLOBALS['wp'] );
		if ( $on ) {
			$this->do_customize_on();
		}
	}

	/**
	 * @see Customize_Snapshot_Manager::__construct()
	 */
	function test_construct_without_customize() {
		$this->assertInstanceOf( 'CustomizeWidgetsPlus\Customize_Snapshot_Manager', $this->manager );
		$this->assertNull( $this->manager->plugin );
	}

	/**
	 * @see Customize_Snapshot_Manager::__construct()
	 */
	function test_construct_with_customize() {
		wp_set_current_user( $this->user_id );
		$this->do_customize_boot_actions( true );
		$this->assertTrue( is_customize_preview() );
		$manager = new Customize_Snapshot_Manager( $this->plugin );
		$this->assertInstanceOf( 'CustomizeWidgetsPlus\Plugin', $manager->plugin );
		$this->assertInstanceOf( 'CustomizeWidgetsPlus\Customize_Snapshot', $manager->snapshot() );
		$this->assertEquals( 0, has_action( 'init', array( $manager, 'create_post_type' ) ) );
		$this->assertEquals( 10, has_action( 'customize_controls_enqueue_scripts', array( $manager, 'enqueue_scripts' ) ) );
		$this->assertEquals( 10, has_action( 'wp_ajax_customize_update_snapshot', array( $manager, 'update_snapshot' ) ) );
	}

	/**
	 * @see Customize_Snapshot_Manager::__construct()
	 */
	function test_construct_with_customize_bootstrapped() {
		wp_set_current_user( $this->user_id );
		$this->do_customize_boot_actions( true );
		unset( $GLOBALS['wp_customize'] );
		$_GET['customize_snapshot_uuid'] = self::UUID;
		$_GET['scope'] = 'full';
		$manager = new Customize_Snapshot_Manager( $this->plugin );
		$this->assertInstanceOf( 'WP_Customize_Manager', $GLOBALS['wp_customize'] );
	}

	/**
	 * @see Customize_Snapshot_Manager::store_post_data()
	 */
	function test_store_post_data() {
		$this->markTestIncomplete( 'This test has not been implemented.' );
	}

	/**
	 * @see Customize_Snapshot_Manager::create_post_type()
	 */
	function test_create_post_type() {
		$pobj = get_post_type_object( Customize_Snapshot_Manager::POST_TYPE );
		$this->assertInstanceOf( 'stdClass', $pobj );
		$this->assertEquals( Customize_Snapshot_Manager::POST_TYPE, $pobj->name );

		// Test some defaults
		$this->assertFalse( is_post_type_hierarchical( Customize_Snapshot_Manager::POST_TYPE ) );
		$this->assertEquals( array(), get_object_taxonomies( Customize_Snapshot_Manager::POST_TYPE ) );
	}

	/**
	 * @see Customize_Snapshot_Manager::enqueue_scripts()
	 */
	function test_register_scripts() {
		$this->plugin->register_scripts( wp_scripts() );
		$this->plugin->register_styles( wp_styles() );
		$this->manager->enqueue_scripts();
		$this->assertTrue( wp_script_is( 'customize-widgets-plus-customize-snapshot', 'enqueued' ) );
		$this->assertTrue( wp_style_is( 'customize-widgets-plus-customize-snapshot', 'enqueued' ) );
	}

	/**
	 * @see Customize_Snapshot_Manager::snapshot()
	 */
	function test_snapshot() {
		wp_set_current_user( $this->user_id );
		$this->do_customize_boot_actions( true );
		$manager = new Customize_Snapshot_Manager( $this->plugin );
		$this->assertInstanceOf( 'CustomizeWidgetsPlus\Customize_Snapshot', $manager->snapshot() );
	}

	/**
	 * @see Customize_Snapshot_Manager::preview()
	 */
	function test_preview() {
		$this->markTestIncomplete( 'This test has not been implemented.' );
	}

}
