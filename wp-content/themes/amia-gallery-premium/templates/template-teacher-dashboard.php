<?php
/* Template Name: Teacher Dashboard */
get_header();
get_template_part( 'partials/dashboard-shell' );
$users = amia_premium_agum_users( array( 'role' => 'student', 'per_page' => 20 ) );
$columns = array_values( array_filter( amia_premium_agum_columns( 'export' ), static function ( $column ) {
	return ! in_array( $column['key'], array( 'password', 'image_path', 'profile_photo' ), true );
} ) );
?>
<h1><?php esc_html_e( 'Teacher Dashboard', 'amia-gallery-premium' ); ?></h1>
<div class="amia-table-wrap amia-card">
	<table class="amia-table">
		<thead><tr><?php foreach ( $columns as $column ) : ?><th><?php echo esc_html( $column['label'] ); ?></th><?php endforeach; ?></tr></thead>
		<tbody><?php foreach ( $users['items'] as $user ) : ?><tr><?php foreach ( $columns as $column ) : $key = $column['key']; ?><td><?php echo esc_html( isset( $user->{$key} ) ? $user->{$key} : '' ); ?></td><?php endforeach; ?></tr><?php endforeach; ?></tbody>
	</table>
</div>
<?php get_template_part( 'partials/dashboard-end' ); get_footer(); ?>
