<?php if ( ! defined( 'ABSPATH' ) ) { exit; } $columns = agum_get_columns(); ?>
<div class="agum-form-grid">
<?php foreach ( $columns as $column ) : if ( empty( $column['enabled'] ) ) { continue; } $key = $column['key']; if ( in_array( $key, array( 'image_path', 'profile_photo' ), true ) ) { continue; } $required = ! empty( $column['required'] ) ? 'required' : ''; ?>
	<label><?php echo esc_html( $column['label'] ); ?>
	<?php if ( 'role' === $key ) : ?><select name="role" <?php echo esc_attr( $required ); ?>><option value="student">Student → Subscriber</option><option value="ustad">Ustad → Editor</option><option value="admin">Admin → Administrator</option><option value="superadmin">Superadmin → Administrator</option><option value="staff">Staff → Subscriber</option></select>
	<?php elseif ( 'approval_status' === $key ) : ?><select name="approval_status" <?php echo esc_attr( $required ); ?>><option value="approved">Approved</option><option value="pending">Pending</option><option value="rejected">Rejected</option></select>
	<?php elseif ( 'textarea' === $column['type'] ) : ?><textarea name="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $required ); ?>></textarea>
	<?php elseif ( 'toggle' === $column['type'] ) : ?><input name="<?php echo esc_attr( $key ); ?>" type="checkbox" value="1">
	<?php else : ?><input name="<?php echo esc_attr( $key ); ?>" type="<?php echo esc_attr( in_array( $column['type'], array( 'email', 'password', 'number', 'date' ), true ) ? $column['type'] : 'text' ); ?>" <?php echo 'password' === $key ? 'minlength="8"' : ''; ?> <?php echo esc_attr( $required ); ?>><?php endif; ?></label>
<?php endforeach; ?>
	<label>Profile Photo<input name="profile_photo_file" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"></label>
	<label>Image Path<input name="image_path" type="url" readonly placeholder="Auto-filled after image upload"></label>
	<label>Profile Photo URL<input name="profile_photo" type="url" readonly placeholder="Auto-filled after image upload"></label>
</div><div class="agum-preview"><img class="agum-profile-preview" src="<?php echo esc_url( AGUM_Upload::fallback_image_url() ); ?>" alt="Profile preview"></div>
