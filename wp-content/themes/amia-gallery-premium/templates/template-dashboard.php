<?php
/* Template Name: AMIA Dashboard */
get_header();
get_template_part( 'partials/dashboard-shell' );
$analytics = amia_premium_dashboard_analytics();
$stats = $analytics['stats'];
?>
<section class="amia-dashboard-hero amia-glass amia-live-dashboard">
	<div><span class="amia-kicker"><?php esc_html_e( 'Enterprise SaaS Dashboard', 'amia-gallery-premium' ); ?></span><h1><?php esc_html_e( 'AMIA Command Center', 'amia-gallery-premium' ); ?></h1><p class="amia-muted"><?php esc_html_e( 'Live user growth, uploads, logins, activity, roles, device intelligence and smart notifications.', 'amia-gallery-premium' ); ?></p></div>
	<div class="amia-hero-actions"><button type="button" class="amia-btn amia-global-search-open"><?php esc_html_e( 'Smart Search', 'amia-gallery-premium' ); ?></button><button type="button" class="amia-btn amia-btn-ghost amia-open-notifications"><?php esc_html_e( 'Notifications', 'amia-gallery-premium' ); ?></button></div>
</section>
<section class="amia-live-dashboard amia-bento-grid">
	<article class="amia-bento-card amia-gradient-card"><span><?php esc_html_e( 'Total Users', 'amia-gallery-premium' ); ?></span><strong data-stat="total" data-count="<?php echo esc_attr( $stats['total'] ); ?>">0</strong><small><?php esc_html_e( 'Profile registry', 'amia-gallery-premium' ); ?></small></article>
	<article class="amia-bento-card"><span><?php esc_html_e( 'Students', 'amia-gallery-premium' ); ?></span><strong data-stat="students" data-count="<?php echo esc_attr( $stats['students'] ); ?>">0</strong><div class="amia-growth-chart"></div></article>
	<article class="amia-bento-card"><span><?php esc_html_e( 'Ustads', 'amia-gallery-premium' ); ?></span><strong data-stat="ustads" data-count="<?php echo esc_attr( $stats['ustads'] ); ?>">0</strong><div class="amia-role-chart"></div></article>
	<article class="amia-bento-card"><span><?php esc_html_e( 'Images', 'amia-gallery-premium' ); ?></span><strong data-stat="images" data-count="<?php echo esc_attr( $stats['images'] ); ?>">0</strong><div class="amia-upload-chart"></div></article>
	<article class="amia-bento-card amia-wide"><h2><?php esc_html_e( 'Login Analytics', 'amia-gallery-premium' ); ?></h2><div class="amia-login-chart"></div></article>
	<article class="amia-bento-card amia-wide"><h2><?php esc_html_e( 'Activity Analytics', 'amia-gallery-premium' ); ?></h2><div class="amia-activity-chart"></div></article>
	<article class="amia-bento-card"><h2><?php esc_html_e( 'Device Analytics', 'amia-gallery-premium' ); ?></h2><div class="amia-device-chart"></div></article>
	<article class="amia-bento-card"><h2><?php esc_html_e( 'Browser Analytics', 'amia-gallery-premium' ); ?></h2><div class="amia-browser-chart"></div></article>
	<article class="amia-bento-card amia-wide"><h2><?php esc_html_e( 'Recent Users', 'amia-gallery-premium' ); ?></h2><div class="amia-recent-users"></div></article>
	<article class="amia-bento-card"><h2><?php esc_html_e( 'Quick Actions', 'amia-gallery-premium' ); ?></h2><div class="amia-quick-grid"><a class="amia-btn" href="<?php echo esc_url( home_url( '/csv-upload/' ) ); ?>"><?php esc_html_e( 'CSV Upload', 'amia-gallery-premium' ); ?></a><a class="amia-btn amia-btn-ghost" href="<?php echo esc_url( home_url( '/bulk-image-upload/' ) ); ?>"><?php esc_html_e( 'Bulk Images', 'amia-gallery-premium' ); ?></a><a class="amia-btn amia-btn-ghost" href="<?php echo esc_url( home_url( '/reports/' ) ); ?>"><?php esc_html_e( 'Reports', 'amia-gallery-premium' ); ?></a></div></article>
</section>
<section class="amia-card amia-view-only-feed"><div class="amia-section-head"><h2><?php esc_html_e( 'Recent Activity', 'amia-gallery-premium' ); ?></h2><span class="amia-pill"><?php esc_html_e( 'View only', 'amia-gallery-premium' ); ?></span></div><?php get_template_part( 'partials/activity-list' ); ?></section>
<?php get_template_part( 'partials/dashboard-end' ); get_footer(); ?>
