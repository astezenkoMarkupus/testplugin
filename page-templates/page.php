<div id="testplugin-admin" class="wrap">
	<div id="icon-tools" class="icon32"><br></div>

	<h2 class="testplugin-title"><?php _e( 'TestPlugin Settings' ) ?></h2>

	<?php
	if( ! empty( $_GET['updated'] ) ){
		?>
		<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
			<p>
				<strong><?php _e('Settings saved.') ?></strong>
			</p>
			<button type="button" class="notice-dismiss">
				<span class="screen-reader-text">Dismiss this notice.</span>
			</button>
		</div>
		<?php
	}
	?>

	<form class="testplugin-form">
		<fieldset>
			<button><?php _e( 'Get ToDos', 'testplugin' ) ?></button>
		</fieldset>
	</form>
</div>

