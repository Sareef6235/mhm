<?php
/* Template Name: User Profile */
get_header();
?>
<section class="amia-section"><div class="amia-container amia-card"><h1><?php esc_html_e( 'User Profile', 'amia-gallery-premium' ); ?></h1>
<?php if ( is_user_logged_in() ) : $u = wp_get_current_user(); $profile = amia_premium_agum_profile_for_wp_user( $u->ID ); $columns = amia_premium_agum_columns( 'export' ); ?>
	<img src="<?php echo esc_url( amia_premium_user_image( $u->ID ) ); ?>" width="140" height="140" alt="">
	<h2><?php echo esc_html( $u->display_name ); ?></h2>
	<div class="amia-profile-fields">
		<?php foreach ( $columns as $column ) : $key = $column['key']; if ( in_array( $key, array( 'password', 'image_path', 'profile_photo' ), true ) ) { continue; } ?>
			<p><strong><?php echo esc_html( $column['label'] ); ?>:</strong> <?php echo esc_html( $profile && isset( $profile->{$key} ) ? $profile->{$key} : ( 'email' === $key ? $u->user_email : '' ) ); ?></p>
		<?php endforeach; ?>
	</div>
	<a class="amia-btn" href="<?php echo esc_url( get_edit_profile_url() ); ?>"><?php esc_html_e( 'Edit WordPress Profile', 'amia-gallery-premium' ); ?></a>
<?php else : ?>
	<a class="amia-btn" href="<?php echo esc_url( wp_login_url() ); ?>"><?php esc_html_e( 'Login', 'amia-gallery-premium' ); ?></a>
<?php endif; ?></div></section><?php get_footer(); ?>
