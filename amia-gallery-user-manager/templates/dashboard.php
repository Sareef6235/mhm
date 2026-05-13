<?php
/** Dashboard shell. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
$view = isset( $view ) ? $view : 'dashboard';
$stats = isset( $stats ) ? $stats : AGUM_Users::stats();
?>
<div class="agum-app" data-theme="dark">
	<aside class="agum-sidebar">
		<div class="agum-brand"><span class="agum-logo">A</span><div><strong>AMIA Gallery</strong><small>User Manager Pro</small></div></div>
		<nav>
			<a href="<?php echo agum_admin_url( 'agum-dashboard' ); ?>">🏠 Dashboard</a>
			<a href="<?php echo agum_admin_url( 'agum-users' ); ?>">👥 Users</a>
			<a href="<?php echo agum_admin_url( 'agum-ustads' ); ?>">🧑‍🏫 Ustads</a>
			<a href="<?php echo agum_admin_url( 'agum-csv' ); ?>">📤 CSV Upload</a>
			<a href="<?php echo agum_admin_url( 'agum-media' ); ?>">🖼 Media Upload</a>
			<a href="<?php echo agum_admin_url( 'agum-reports' ); ?>">📊 Reports</a>
			<a href="<?php echo agum_admin_url( 'agum-settings' ); ?>">⚙ Settings</a>
			<a href="<?php echo agum_admin_url( 'agum-logs' ); ?>">🔒 Security Logs</a>
		</nav>
	</aside>
	<main class="agum-main">
		<header class="agum-topbar">
			<button class="agum-menu-toggle" type="button">☰</button>
			<div><h1><?php esc_html_e( 'AMIA Gallery User Manager Pro', 'amia-gallery-user-manager' ); ?></h1><p><?php esc_html_e( 'Secure, fast and premium user operations.', 'amia-gallery-user-manager' ); ?></p></div>
			<div class="agum-top-actions"><input class="agum-live-search" type="search" placeholder="Search users, email, phone, admission no"><span class="agum-bell">🔔</span><span class="agum-avatar"><?php echo esc_html( strtoupper( substr( wp_get_current_user()->display_name, 0, 1 ) ) ); ?></span></div>
		</header>

		<?php $agum_error = get_transient( 'agum_form_error_' . get_current_user_id() ); if ( $agum_error ) : delete_transient( 'agum_form_error_' . get_current_user_id() ); ?>
		<section class="agum-card agum-validation-error"><strong><?php esc_html_e( 'Validation error:', 'amia-gallery-user-manager' ); ?></strong> <?php echo esc_html( $agum_error ); ?></section>
		<?php endif; ?>

		<?php if ( 'dashboard' === $view || 'reports' === $view ) : ?>
		<section class="agum-grid agum-stats">
			<div class="agum-card agum-stat"><span>Total Users</span><strong><?php echo esc_html( $stats['total'] ); ?></strong><em>All managed profiles</em></div>
			<div class="agum-card agum-stat"><span>Students</span><strong><?php echo esc_html( $stats['students'] ); ?></strong><em>Active student records</em></div>
			<div class="agum-card agum-stat"><span>Ustads</span><strong><?php echo esc_html( $stats['ustads'] ); ?></strong><em>Teaching staff</em></div>
			<div class="agum-card agum-stat"><span>Mapped Images</span><strong><?php echo esc_html( $stats['images'] ); ?></strong><em>Automatic photo links</em></div>
		</section>

		<section class="agum-card"><div class="agum-section-head"><h2>Role Overview</h2><span class="agum-pill">Role filters & permissions</span></div><div class="agum-role-grid"><div class="agum-role-card agum-role-student"><strong>Students</strong><span><?php echo esc_html( $stats['students'] ); ?> users</span><p>Subscriber access</p></div><div class="agum-role-card agum-role-ustad"><strong>Ustads</strong><span><?php echo esc_html( $stats['ustads'] ); ?> users</span><p>Editor access</p></div><div class="agum-role-card agum-role-admin"><strong>Admins</strong><span>Administrator</span><p>Management access</p></div><div class="agum-role-card agum-role-superadmin"><strong>Superadmins</strong><span>Administrator</span><p>Full dashboard access</p></div></div></section>
		<section class="agum-card agum-welcome"><h2>Welcome back</h2><p>Run imports, upload mapped photos, generate OTPs, and monitor security logs from a single modern dashboard.</p><div class="agum-actions"><a class="agum-btn" href="<?php echo agum_admin_url( 'agum-users' ); ?>">Manage Users</a><a class="agum-btn agum-btn-ghost" href="<?php echo agum_admin_url( 'agum-csv' ); ?>">Import CSV</a></div></section>
		<?php endif; ?>

		<?php if ( 'users' === $view ) : ?>
		<section class="agum-card">
			<div class="agum-section-head"><h2><?php echo isset( $role ) && 'ustad' === $role ? esc_html__( 'Ustads', 'amia-gallery-user-manager' ) : esc_html__( 'Users', 'amia-gallery-user-manager' ); ?></h2><button class="agum-btn agum-open-modal" data-target="#agum-user-modal">+ Add User</button></div>
			<div class="agum-filters"><input id="agum-search" type="search" placeholder="Search by name, username, email, phone"><select id="agum-role-filter"><option value="">All roles</option><option value="student">Student</option><option value="ustad">Ustad</option><option value="staff">Staff</option><option value="admin">Admin</option></select><a class="agum-btn agum-btn-ghost" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=agum_export_csv' ), AGUM_Security::NONCE_ACTION, AGUM_Security::NONCE_NAME ) ); ?>">Export CSV</a><button class="agum-btn agum-danger agum-bulk-delete">Bulk Delete</button></div>
			<div id="agum-table-wrap"><?php agum_template( 'tables', array( 'users' => $query['items'] ) ); ?></div>
		</section>
		<?php agum_template( 'edit-user' ); endif; ?>

		<?php if ( 'logs' === $view || 'dashboard' === $view || 'reports' === $view ) : ?>
		<section class="agum-card"><div class="agum-section-head"><h2>Recent Activity</h2><div class="agum-actions"><span class="agum-pill">Live logs</span><button type="button" class="agum-btn agum-danger agum-clear-activity">Clear Recent Activity</button></div></div><div class="agum-timeline">
		<?php foreach ( (array) $logs as $log ) : ?><div><strong><?php echo esc_html( $log->action ); ?></strong><p><?php echo esc_html( $log->message ); ?></p><time><?php echo esc_html( $log->created_at ); ?></time></div><?php endforeach; ?>
		</div></section>
		<?php endif; ?>
	</main>
</div>
<div class="agum-toast" aria-live="polite"></div>
