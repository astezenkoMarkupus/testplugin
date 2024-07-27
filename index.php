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
	private $logger;
	private $db;

	const PLUGIN_VERSION      = '0.0.1';
	const PLUGIN_DB_VERSION   = '0.0.1';
	const PAGE_TEMPLATES_PATH = WP_PLUGIN_DIR . '/testplugin/page-templates';

	public function __construct() {
		global $wpdb;
		$this->logger = new FileLogger();
		$this->db     = $wpdb;

		register_activation_hook( __FILE__, [ $this, 'testplugin_activation' ] );
		register_deactivation_hook( __FILE__, [ $this, 'testplugin_deactivation' ] );

		add_action( 'admin_menu', [ $this, 'add_admin_page' ] );

		add_action( 'admin_enqueue_scripts', [$this, 'enqueue_scripts'] );
		add_action( 'wp_ajax_testplugin_ajax_load_todos', [ $this, 'testplugin_ajax_load_todos' ] );
		add_action( 'wp_ajax_testplugin_ajax_search_todos', [ $this, 'testplugin_ajax_search_todos' ] );
	}

	public function enqueue_scripts(): void {
		wp_enqueue_style( 'testplugin-admin', plugins_url( 'styles/admin.css', __FILE__ ), [], self::PLUGIN_VERSION );
		wp_enqueue_script( 'testplugin-admin', plugins_url( 'scripts/admin.js', __FILE__ ), [], self::PLUGIN_VERSION, true );
		wp_localize_script( 'testplugin-admin', 'ajaxData', [ 'ajaxUrl' => admin_url( 'admin-ajax.php' ) ] );
	}

	/**
	 * Plugin activated.
	 *
	 * @return void
	 */
	public function testplugin_activation(): void {
		$this->logger->debug( 'Plugin activated!' );

		$db_table_name   = "{$this->db->prefix}testplugin_todos";
		$charset_collate = $this->db->get_charset_collate();

		if ( ( $this->db->get_var( "show tables like '$db_table_name'" ) != $db_table_name ) ||
		     ( get_option( sanitize_title( 'testplugin_db_version' ) !== self::PLUGIN_DB_VERSION ) ) ) {
			$sql = "CREATE TABLE $db_table_name (
	            id int(10) unsigned NOT NULL AUTO_INCREMENT,
	            user_id int(10) NOT NULL,
	            todo_id int(10) NOT NULL,
	            title varchar(256) NOT NULL,
	            completed tinyint(1) NOT NULL DEFAULT '0',
	            PRIMARY KEY (id)
	        ) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
			add_option( 'testplugin_db_version', self::PLUGIN_DB_VERSION );
			$this->logger->info( 'DB table created!' );
		}
	}

	/**
	 * Plugin deactivated.
	 *
	 * @return void
	 */
	public function testplugin_deactivation(): void {
		$this->logger->debug( 'Plugin deactivated!' );
	}

	/**
	 * Add the plugin page to the WP Admin menu.
	 *
	 * @return void
	 */
	public function add_admin_page(): void {
		add_menu_page( 'TestPlugin Settings', 'TestPlugin', 'manage_options', 'testplugin', [
			$this,
			'render_template'
		] );
	}

	/**
	 * Show Admin page layout.
	 *
	 * @return void
	 */
	public function render_template(): void {
		$template_path = self::PAGE_TEMPLATES_PATH . '/page.php';

		if ( ! is_readable( $template_path ) ) {
			return;
		}

		include $template_path;
	}

	public function testplugin_ajax_load_todos(): void {
		$response = [];

		try{
			$response = wp_remote_get( 'https://jsonplaceholder.typicode.com/todos' );
		}catch( Exception $e ){
			$this->logger->error( 'Error while fetching todos!', [ 'error' => $e ] );
			wp_send_json_error( [ 'msg' => $e->getMessage() ] );
		}

		$todos = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $todos ) ) {
			wp_send_json_error( [ 'msg' => __( 'Todos are empty', 'testplugin' ) ] );
		}

		/**
		 * Update existing or insert new rows.
		 */
		foreach ( $todos as $todo ) {
			if( $this->check_field_exists( $todo['id'] ) ){
				$update = $this->db->update( "{$this->db->prefix}testplugin_todos",
					[ 'user_id' => $todo['userId'], 'todo_id' => $todo['id'], 'title' => $todo['title'], 'completed' => $todo['completed'] ],
					[ 'todo_id' => $todo['id'], 'title' => $todo['title'] ],
					[ '%d', '%d', '%s', '%d' ],
					[ '%d', '%s' ]
				);

				if( $update === FALSE ) {
					$this->logger->error( 'Error when trying to update ToDo!', [
						'user_id'   => $todo['userId'],
						'todo_id'   => $todo['id'],
						'title'     => $todo['title'],
						'completed' => $todo['completed'],
					] );
				}
			}else{
				$insert = $this->db->insert( "{$this->db->prefix}testplugin_todos",
					[ 'user_id' => $todo['userId'], 'todo_id' => $todo['id'], 'title' => $todo['title'], 'completed' => $todo['completed'] ],
					[ '%d', '%d', '%s', '%d' ]
				);

				if( $insert === FALSE ) {
					$this->logger->error( 'Error when trying to insert new ToDo!', [
						'user_id'   => $todo['userId'],
						'todo_id'   => $todo['id'],
						'title'     => $todo['title'],
						'completed' => $todo['completed'],
					] );
				}
			}
		}

		wp_send_json_success( [ 'todos' => $this->getTodos() ] );
	}

	public function testplugin_ajax_search_todos(): void {
		$title = $_POST['title'] ?? '';

		if ( ! $title ) {
			wp_send_json_error( [ 'msg' => __( 'Incorrect data!', 'testplugin' ) ] );
		}

		$todos = $this->db->get_results(  "SELECT * FROM {$this->db->prefix}testplugin_todos WHERE title LIKE '%$title%'" );

		if ( empty( $todos ) ) {
			wp_send_json_error( [ 'msg' => __( 'No results found!', 'testplugin' ) ] );
		}

		wp_send_json_success( [ 'todos' => $this->getTodos( $todos ) ] );
	}

	private function check_field_exists( $todo_id ): bool
	{
		return ( bool ) $this->db->get_var(
			$this->db->prepare(
				"SELECT COUNT(id)
				FROM {$this->db->prefix}testplugin_todos
				WHERE todo_id = $todo_id"
			)
		);
	}

	private function getTodos( array $ready_data = [] ): string {
		if( ! empty( $ready_data ) ) {
			$todos = $ready_data;
		}else{
			$todos = $this->db->get_results(  "SELECT * FROM {$this->db->prefix}testplugin_todos" );
		}

		$res = '';

		foreach ( $todos as $todo ) {
			$res .= '<div class="testplugin-todos-item">';
			$res .= '<div>' . $todo->id . '</div>';
			$res .= '<div>' . $todo->user_id . '</div>';
			$res .= '<div>' . $todo->todo_id . '</div>';
			$res .= '<div>' . $todo->title . '</div>';
			$res .= '<div>' . ( $todo->completed ? 'Yes' : 'No' ) . '</div>';
			$res .= '</div>';
		}

		return $res;
	}
}

new TestPlugin();
