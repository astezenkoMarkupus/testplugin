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
	public function __construct() {
		register_activation_hook( __FILE__, [ $this, 'testplugin_activation' ] );
		register_deactivation_hook( __FILE__, [ $this, 'testplugin_deactivation' ] );
	}

	public function testplugin_activation(): void {
		$logger = new FileLogger();
		$logger->debug( 'Plugin activated!' );
	}

	public function testplugin_deactivation(): void {
		$logger = new FileLogger();
		$logger->debug( 'Plugin deactivated!' );
	}
}

new TestPlugin();