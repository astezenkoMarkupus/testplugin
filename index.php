<?php

/**
 * Plugin Name: Test Plugin
 * Description: Test task.
 * Version: 0.1
 * Author: Andrei Stezenko
 * Author URI: https://github.com/astezenkoMarkupus/
 */

use TestPlugin\FileLogger;

require 'vendor/autoload.php';

class TestPlugin {
	const PAGE_TEMPLATES_PATH = WP_PLUGIN_DIR . '/testplugin/page-templates';

	public function __construct() {
		register_activation_hook( __FILE__, [ $this, 'testplugin_activation' ] );
		register_deactivation_hook( __FILE__, [ $this, 'testplugin_deactivation' ] );

		add_action( 'admin_menu', [$this, 'add_admin_page'] );
	}

	/**
	 * Plugin activated.
	 *
	 * @return void
	 */
	public function testplugin_activation(): void {
		$logger = new FileLogger();
		$logger->debug( 'Plugin activated!' );
	}

	/**
	 * Plugin deactivated.
	 *
	 * @return void
	 */
	public function testplugin_deactivation(): void {
		$logger = new FileLogger();
		$logger->debug( 'Plugin deactivated!' );
	}

	/**
	 * Add the plugin page to the WP Admin menu.
	 *
	 * @return void
	 */
	public function add_admin_page(): void
	{
		add_menu_page(
			'TestPlugin Settings',
			'TestPlugin',
			'manage_options',
			'testplugin',
			[$this, 'render_template']
		);
	}

	/**
	 * Show Admin page layout.
	 *
	 * @return void
	 */
	public function render_template(): void
	{
		$template_path = self::PAGE_TEMPLATES_PATH . '/page.php';

		if( ! is_readable( $template_path ) ) return;

		include $template_path;
	}
}

new TestPlugin();
