<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="agum-app" data-theme="dark">
	<aside class="agum-sidebar">
		<div class="agum-brand"><span class="agum-logo">A</span><div><strong>AMIA Gallery</strong><small>Settings</small></div></div>
		<nav>
			<a href="<?php echo agum_admin_url( 'agum-dashboard' ); ?>">🏠 Dashboard</a>
			<a href="<?php echo agum_admin_url( 'agum-users' ); ?>">👥 Users</a>
			<a href="<?php echo agum_admin_url( 'agum-settings' ); ?>">⚙ Settings</a>
			<a href="<?php echo agum_admin_url( 'agum-logs' ); ?>">🔒 Security Logs</a>
		</nav>
	</aside>
	<main class="agum-main">
		<header class="agum-topbar">
			<button class="agum-menu-toggle" type="button">☰</button>
			<div><h1>Plugin Settings</h1><p>Control pagination, OTP expiry, dark mode, WordPress user sync and uninstall behavior.</p></div>
		</header>
		<section class="agum-card">
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="agum-settings-form">
				<input type="hidden" name="action" value="agum_save_settings">
				<?php wp_nonce_field( AGUM_Security::NONCE_ACTION, AGUM_Security::NONCE_NAME ); ?>
				<label>Items per page<input type="number" min="5" max="200" name="items_per_page" value="<?php echo esc_attr( $settings['items_per_page'] ); ?>"></label>
				<label>OTP expiry minutes<input type="number" min="1" max="60" name="otp_expiry_minutes" value="<?php echo esc_attr( $settings['otp_expiry_minutes'] ); ?>"></label>
				<label class="agum-check"><input type="checkbox" name="enable_dark_mode" <?php checked( $settings['enable_dark_mode'] ); ?>> Enable dark mode</label>
				<label class="agum-check"><input type="checkbox" name="delete_on_uninstall" <?php checked( $settings['delete_on_uninstall'] ); ?>> Delete data on uninstall</label>
				<button class="agum-btn" type="submit">Save Settings</button>
			</form>
		</section>

		<section class="agum-card">
			<div class="agum-section-head"><h2>Role Management</h2><span class="agum-pill">Permissions & Access</span></div>
			<div class="agum-role-grid">
				<?php foreach ( array( 'student' => 'subscriber', 'ustad' => 'editor', 'admin' => 'administrator', 'superadmin' => 'administrator' ) as $agum_role => $wp_role ) : ?>
					<div class="agum-role-card agum-role-<?php echo esc_attr( $agum_role ); ?>"><strong><?php echo esc_html( ucfirst( $agum_role ) ); ?></strong><span>WordPress role: <?php echo esc_html( $wp_role ); ?></span><p><?php echo 'student' === $agum_role ? esc_html__( 'Limited dashboard access and profile visibility.', 'amia-gallery-user-manager' ) : esc_html__( 'Managed role permissions for dashboard and profile operations.', 'amia-gallery-user-manager' ); ?></p></div>
				<?php endforeach; ?>
			</div>
		</section>
		<section class="agum-card">
			<div class="agum-section-head"><h2>Column Manager</h2><button class="agum-btn agum-btn-ghost agum-add-column" type="button">+ Add Column</button></div>
			<p>Drag rows to reorder columns, rename labels, enable/disable columns, or remove optional columns.</p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="agum-column-form"><input type="hidden" name="action" value="agum_save_settings"><?php wp_nonce_field( AGUM_Security::NONCE_ACTION, AGUM_Security::NONCE_NAME ); ?><input type="hidden" name="items_per_page" value="<?php echo esc_attr( $settings['items_per_page'] ); ?>"><input type="hidden" name="otp_expiry_minutes" value="<?php echo esc_attr( $settings['otp_expiry_minutes'] ); ?>"><?php if ( $settings['enable_dark_mode'] ) : ?><input type="hidden" name="enable_dark_mode" value="1"><?php endif; ?><?php if ( $settings['delete_on_uninstall'] ) : ?><input type="hidden" name="delete_on_uninstall" value="1"><?php endif; ?><div class="agum-column-manager" id="agum-column-manager">
			<?php foreach ( agum_sanitize_columns( $settings['columns'] ) as $index => $column ) : ?>
				<div class="agum-column-row" draggable="true">
					<span class="agum-drag">↕</span><input name="columns[<?php echo esc_attr( $index ); ?>][order]" value="<?php echo esc_attr( $index ); ?>" type="hidden" class="agum-column-order"><input name="columns[<?php echo esc_attr( $index ); ?>][key]" value="<?php echo esc_attr( $column['key'] ); ?>" placeholder="column_key"><input name="columns[<?php echo esc_attr( $index ); ?>][label]" value="<?php echo esc_attr( $column['label'] ); ?>" placeholder="Column label"><label class="agum-check"><input type="checkbox" name="columns[<?php echo esc_attr( $index ); ?>][enabled]" value="1" <?php checked( $column['enabled'] ); ?>> Enabled</label><button class="agum-icon agum-remove-column" type="button">×</button>
				</div>
			<?php endforeach; ?>
			</div>
		</section>
		<section class="agum-card">
			<div class="agum-section-head">
				<div><h2>Sync AGUM Users to WordPress Users</h2><p>Create or link native WordPress users for legacy rows in the AGUM profile table.</p></div>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="agum_sync_wp_users">
					<?php wp_nonce_field( AGUM_Security::NONCE_ACTION, AGUM_Security::NONCE_NAME ); ?>
					<button class="agum-btn" type="submit">Sync AGUM Users to WordPress Users</button>
				</form>
			</div>
			<?php if ( ! empty( $sync_report ) && is_array( $sync_report ) ) : ?>
				<div class="agum-grid">
					<div class="agum-stat"><span>Created</span><strong><?php echo esc_html( $sync_report['created'] ); ?></strong></div>
					<div class="agum-stat"><span>Linked</span><strong><?php echo esc_html( $sync_report['linked'] ); ?></strong></div>
					<div class="agum-stat"><span>Skipped</span><strong><?php echo esc_html( $sync_report['skipped'] ); ?></strong></div>
				</div>
				<ul class="agum-errors">
					<?php foreach ( $sync_report['errors'] as $error ) : ?>
						<li><?php echo esc_html( $error ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</section>
	</main>
</div>
