<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$columns = array_values( array_filter( agum_get_columns(), static function ( $column ) { return ! empty( $column['enabled'] ) && ( ! empty( $column['form'] ) || ! empty( $column['edit'] ) ); } ) );
?>
<div class="agum-form-grid">
<?php
foreach ( $columns as $column ) :
	$key = $column['key'];
	if ( in_array( $key, array( 'image_path', 'profile_photo' ), true ) ) {
		continue;
	}
	agum_render_dynamic_field( $column, 'form' );
endforeach;
?>
	<label class="agum-field agum-field-profile-photo"><span><?php esc_html_e( 'Profile Photo', 'amia-gallery-user-manager' ); ?></span><input name="profile_photo_file" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"></label>
	<?php foreach ( agum_get_columns_for_context( 'image_field' ) as $image_column ) : ?>
		<label class="agum-field agum-field-<?php echo esc_attr( $image_column['key'] ); ?>"><span><?php echo esc_html( $image_column['label'] ); ?></span><input name="<?php echo esc_attr( $image_column['key'] ); ?>" type="url" readonly placeholder="<?php esc_attr_e( 'Auto-filled after image upload', 'amia-gallery-user-manager' ); ?>"></label>
	<?php endforeach; ?>
</div>
<div class="agum-preview"><img class="agum-profile-preview" src="<?php echo esc_url( AGUM_Upload::fallback_image_url() ); ?>" alt="<?php esc_attr_e( 'Profile preview', 'amia-gallery-user-manager' ); ?>"></div>
