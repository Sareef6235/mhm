<?php
/**
 * Shared helper functions.
 *
 * @package AMIA_Gallery_User_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default plugin settings.
 *
 * @return array
 */
function agum_default_settings() {
	return array(
		'items_per_page'     => 20,
		'otp_expiry_minutes' => 10,
		'enable_dark_mode'   => 1,
		'delete_on_uninstall'=> 0,
		'upload_limit_mb'    => 5,
		'image_limit_kb'     => 300,
		'columns'            => agum_default_columns(),
	);
}


/**
 * Default configurable columns.
 *
 * @return array
 */
function agum_default_columns() {
	$keys = array( 'student_id', 'admission_no', 'name', 'class', 'dob', 'role', 'username', 'email', 'password', 'phone_number', 'image_path', 'profile_photo', 'approval_status', 'notes', 'remarks' );
	$columns = array();
	foreach ( $keys as $index => $key ) {
		$columns[] = array( 'key' => $key, 'label' => ucwords( str_replace( '_', ' ', $key ) ), 'type' => agum_default_column_type( $key ), 'enabled' => 1, 'required' => in_array( $key, array( 'student_id', 'admission_no', 'name', 'class', 'dob', 'role', 'username', 'email', 'password' ), true ) ? 1 : 0, 'form' => 1, 'edit' => 1, 'csv' => 1, 'bulk' => 1, 'bulk_upload' => 1, 'image_field' => in_array( $key, array( 'image_path', 'profile_photo' ), true ) ? 1 : 0, 'searchable' => in_array( $key, array( 'student_id', 'admission_no', 'name', 'username', 'email', 'phone_number' ), true ) ? 1 : 0, 'filterable' => in_array( $key, array( 'role', 'class', 'approval_status' ), true ) ? 1 : 0, 'export' => 1, 'placeholder' => '', 'default_value' => '', 'order' => $index );
	}
	return $columns;
}


/**
 * Default field type for a column key.
 *
 * @param string $key Column key.
 * @return string
 */
function agum_default_column_type( $key ) {
	if ( false !== strpos( $key, 'email' ) ) { return 'email'; }
	if ( false !== strpos( $key, 'password' ) ) { return 'password'; }
	if ( false !== strpos( $key, 'date' ) || 'dob' === $key ) { return 'date'; }
	if ( false !== strpos( $key, 'image' ) || false !== strpos( $key, 'photo' ) ) { return 'image'; }
	if ( in_array( $key, array( 'role', 'approval_status' ), true ) ) { return 'select'; }
	if ( in_array( $key, array( 'notes', 'remarks' ), true ) ) { return 'textarea'; }
	return 'text';
}

/**
 * Return enabled columns, sorted by order.
 *
 * @return array
 */
function agum_get_columns() {
	$settings = agum_get_settings();
	return agum_sanitize_columns( isset( $settings['columns'] ) ? $settings['columns'] : array() );
}

/**
 * Get one column definition by key.
 *
 * @param string $key Column key.
 * @return array|null
 */
function agum_get_column( $key ) {
	foreach ( agum_get_columns() as $column ) {
		if ( $column['key'] === $key ) { return $column; }
	}
	return null;
}

/**
 * Built-in columns handled by AGUM core schema.
 *
 * @return array
 */
function agum_core_column_keys() {
	return array( 'id', 'wp_user_id', 'student_id', 'admission_no', 'name', 'class', 'dob', 'role', 'username', 'email', 'password', 'phone_number', 'image_path', 'profile_photo', 'otp_code', 'last_login', 'last_seen', 'approval_status', 'notes', 'remarks', 'created_at', 'updated_at' );
}

/**
 * Sanitize configurable column settings.
 *
 * @param array $columns Raw columns.
 * @return array
 */
function agum_sanitize_columns( $columns ) {
	$clean = array();
	foreach ( (array) $columns as $index => $column ) {
		$key = isset( $column['key'] ) ? sanitize_key( $column['key'] ) : '';
		$key = preg_replace( '/[^a-z0-9_]/', '_', $key );
		$key = preg_replace( '/_+/', '_', $key );
		if ( $key && ! preg_match( '/^[a-z_]/', $key ) ) {
			$key = 'field_' . $key;
		}
		if ( ! $key ) {
			continue;
		}
		$type = isset( $column['type'] ) ? sanitize_key( $column['type'] ) : agum_default_column_type( $key );
		$type = in_array( $type, array( 'text', 'number', 'email', 'password', 'select', 'date', 'image', 'file', 'textarea', 'toggle' ), true ) ? $type : 'text';
		$clean[] = array(
			'key'         => $key,
			'label'       => isset( $column['label'] ) ? sanitize_text_field( $column['label'] ) : ucwords( str_replace( '_', ' ', $key ) ),
			'type'        => $type,
			'enabled'     => ! empty( $column['enabled'] ) ? 1 : 0,
			'required'    => ! empty( $column['required'] ) ? 1 : 0,
			'form'        => isset( $column['form'] ) ? ( ! empty( $column['form'] ) ? 1 : 0 ) : 1,
			'edit'        => isset( $column['edit'] ) ? ( ! empty( $column['edit'] ) ? 1 : 0 ) : 1,
			'csv'         => isset( $column['csv'] ) ? ( ! empty( $column['csv'] ) ? 1 : 0 ) : ( isset( $column['bulk'] ) ? ( ! empty( $column['bulk'] ) ? 1 : 0 ) : 1 ),
			'bulk'        => ! empty( $column['bulk'] ) ? 1 : 0,
			'bulk_upload' => isset( $column['bulk_upload'] ) ? ( ! empty( $column['bulk_upload'] ) ? 1 : 0 ) : ( ! empty( $column['bulk'] ) ? 1 : 0 ),
			'image_field' => isset( $column['image_field'] ) ? ( ! empty( $column['image_field'] ) ? 1 : 0 ) : ( in_array( $key, array( 'image_path', 'profile_photo' ), true ) ? 1 : 0 ),
			'searchable'  => ! empty( $column['searchable'] ) ? 1 : 0,
			'filterable'  => ! empty( $column['filterable'] ) ? 1 : 0,
			'export'      => isset( $column['export'] ) ? ( ! empty( $column['export'] ) ? 1 : 0 ) : 1,
			'options'       => isset( $column['options'] ) ? sanitize_text_field( $column['options'] ) : '',
			'placeholder'   => isset( $column['placeholder'] ) ? sanitize_text_field( $column['placeholder'] ) : '',
			'default_value' => isset( $column['default_value'] ) ? sanitize_text_field( $column['default_value'] ) : '',
			'order'         => isset( $column['order'] ) ? absint( $column['order'] ) : $index,
		);
	}
	usort( $clean, static function ( $a, $b ) { return $a['order'] <=> $b['order']; } );
	return $clean ? $clean : agum_default_columns();
}


/**
 * Return columns enabled for a specific dynamic surface.
 *
 * @param string $context Surface key: form, edit, csv, bulk_upload, image_field, searchable, filterable, export.
 * @return array
 */
function agum_get_columns_for_context( $context ) {
	$context = sanitize_key( $context );
	return array_values( array_filter( agum_get_columns(), static function ( $column ) use ( $context ) {
		return ! empty( $column['enabled'] ) && ( ! array_key_exists( $context, $column ) || ! empty( $column[ $context ] ) );
	} ) );
}

/**
 * Render a dynamic user field based on settings.
 *
 * @param array  $column Column config.
 * @param string $context form/edit context.
 * @return void
 */
function agum_render_dynamic_field( $column, $context = 'form' ) {
	$key = sanitize_key( $column['key'] );
	$required = ! empty( $column['required'] ) ? 'required' : '';
	$type = isset( $column['type'] ) ? sanitize_key( $column['type'] ) : 'text';
	$placeholder = isset( $column['placeholder'] ) ? $column['placeholder'] : '';
	$default_value = isset( $column['default_value'] ) ? $column['default_value'] : '';
	$options = array_filter( array_map( 'trim', explode( ',', isset( $column['options'] ) ? $column['options'] : '' ) ) );
	?>
	<label class="agum-field agum-field-<?php echo esc_attr( $key ); ?>" data-field="<?php echo esc_attr( $key ); ?>" data-agum-form="<?php echo esc_attr( ! empty( $column['form'] ) ? 1 : 0 ); ?>" data-agum-edit="<?php echo esc_attr( ! empty( $column['edit'] ) ? 1 : 0 ); ?>">
		<span><?php echo esc_html( $column['label'] ); ?><?php echo $required ? ' *' : ''; ?></span>
		<?php if ( 'role' === $key ) : ?>
			<select name="role" <?php echo esc_attr( $required ); ?>><option value="student" <?php selected( $default_value, 'student' ); ?>>Student → Subscriber</option><option value="ustad" <?php selected( $default_value, 'ustad' ); ?>>Ustad → Editor</option><option value="admin" <?php selected( $default_value, 'admin' ); ?>>Admin → Administrator</option><option value="superadmin" <?php selected( $default_value, 'superadmin' ); ?>>Superadmin → Administrator</option><option value="staff" <?php selected( $default_value, 'staff' ); ?>>Staff → Subscriber</option></select>
		<?php elseif ( 'approval_status' === $key ) : ?>
			<select name="approval_status" <?php echo esc_attr( $required ); ?>><option value="approved" <?php selected( $default_value, 'approved' ); ?>>Approved</option><option value="pending" <?php selected( $default_value, 'pending' ); ?>>Pending</option><option value="rejected" <?php selected( $default_value, 'rejected' ); ?>>Rejected</option></select>
		<?php elseif ( 'select' === $type && $options ) : ?>
			<select name="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $required ); ?>><?php foreach ( $options as $option ) : ?><option value="<?php echo esc_attr( $option ); ?>" <?php selected( $default_value, $option ); ?>><?php echo esc_html( $option ); ?></option><?php endforeach; ?></select>
		<?php elseif ( 'textarea' === $type ) : ?>
			<textarea name="<?php echo esc_attr( $key ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>" <?php echo esc_attr( $required ); ?>><?php echo esc_textarea( $default_value ); ?></textarea>
		<?php elseif ( 'toggle' === $type ) : ?>
			<input name="<?php echo esc_attr( $key ); ?>" type="checkbox" value="1" <?php checked( $default_value ); ?>>
		<?php else : ?>
			<input name="<?php echo esc_attr( $key ); ?>" type="<?php echo esc_attr( in_array( $type, array( 'email', 'password', 'number', 'date' ), true ) ? $type : 'text' ); ?>" value="<?php echo 'password' === $key ? '' : esc_attr( $default_value ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>" <?php echo 'password' === $key ? 'minlength="8"' : ''; ?> <?php echo esc_attr( $required ); ?>>
		<?php endif; ?>
	</label>
	<?php
}

/**
 * Get merged settings.
 *
 * @return array
 */
function agum_get_settings() {
	$settings = get_option( 'agum_settings', array() );
	return wp_parse_args( is_array( $settings ) ? $settings : array(), agum_default_settings() );
}

/**
 * Include a template file.
 *
 * @param string $template Template name.
 * @param array  $args Arguments.
 * @return void
 */
function agum_template( $template, $args = array() ) {
	$file = AGUM_PATH . 'templates/' . sanitize_file_name( $template ) . '.php';
	if ( ! file_exists( $file ) ) {
		return;
	}

	extract( $args, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
	include $file;
}

/**
 * Normalize a username to the canonical media basename.
 *
 * @param string $name User name.
 * @return string
 */
function agum_normalize_image_basename( $name ) {
	$name = sanitize_text_field( $name );
	$name = strtoupper( str_replace( array( ' ', '-' ), '_', $name ) );
	$name = preg_replace( '/[^A-Z0-9_]/', '', $name );
	return trim( $name, '_' );
}

/**
 * Validate phone number with optional plus country code.
 *
 * @param string $phone Phone number.
 * @return bool
 */
function agum_is_valid_phone( $phone ) {
	$phone = trim( (string) $phone );
	return '' === $phone || (bool) preg_match( '/^\+?[0-9]{7,15}$/', $phone );
}

/**
 * Get plugin upload base directory and URL.
 *
 * @return array
 */
function agum_upload_info() {
	$uploads = wp_upload_dir();
	return array(
		'base_dir' => trailingslashit( $uploads['basedir'] ) . 'amia-gallery-user-manager',
		'base_url' => trailingslashit( $uploads['baseurl'] ) . 'amia-gallery-user-manager',
		'image_dir'=> trailingslashit( $uploads['basedir'] ) . 'amia-gallery-user-manager/user-images',
		'image_url'=> trailingslashit( $uploads['baseurl'] ) . 'amia-gallery-user-manager/user-images',
	);
}

/**
 * Build admin page URL.
 *
 * @param string $page Page slug.
 * @param array  $args Query args.
 * @return string
 */
function agum_admin_url( $page, $args = array() ) {
	return esc_url( add_query_arg( array_merge( array( 'page' => $page ), $args ), admin_url( 'admin.php' ) ) );
}


/**
 * Map plugin roles to native WordPress roles.
 *
 * @param string $role Plugin role.
 * @return string
 */
function agum_map_role_to_wp_role( $role ) {
	$map = array(
		'student'    => 'subscriber',
		'ustad'      => 'editor',
		'admin'      => 'administrator',
		'superadmin' => 'administrator',
		'staff'      => 'subscriber',
	);

	$role = sanitize_key( $role );
	return isset( $map[ $role ] ) ? $map[ $role ] : 'subscriber';
}

/**
 * Create a deterministic email when CSV/form data does not include one.
 *
 * @param string $username Username.
 * @return string
 */
function agum_generate_placeholder_email( $username ) {
	$username = sanitize_user( $username, true );
	if ( ! $username ) {
		$username = 'agum_user_' . wp_generate_password( 8, false, false );
	}

	return strtolower( $username ) . '@amia-gallery.local';
}

/**
 * Resolve a unique placeholder email without colliding with existing WordPress users.
 *
 * @param string $username Username.
 * @return string
 */
function agum_unique_placeholder_email( $username ) {
	$base  = agum_generate_placeholder_email( $username );
	$email = $base;
	$index = 2;

	while ( get_user_by( 'email', $email ) ) {
		$email = preg_replace( '/@/', '+' . $index . '@', $base, 1 );
		++$index;
	}

	return $email;
}


/**
 * Persist settings from a submitted request and keep schema/validation in sync.
 *
 * @param array $request Raw request data.
 * @return array Updated settings.
 */
function agum_save_settings_from_request( $request ) {
	$current = agum_get_settings();
	$previous_columns = isset( $current['columns'] ) ? $current['columns'] : array();
	$settings = $current;
	$settings['items_per_page'] = isset( $request['items_per_page'] ) ? max( 1, absint( $request['items_per_page'] ) ) : $current['items_per_page'];
	$settings['otp_expiry_minutes'] = isset( $request['otp_expiry_minutes'] ) ? max( 1, absint( $request['otp_expiry_minutes'] ) ) : $current['otp_expiry_minutes'];
	$settings['enable_dark_mode'] = ! empty( $request['enable_dark_mode'] ) ? 1 : 0;
	$settings['delete_on_uninstall'] = ! empty( $request['delete_on_uninstall'] ) ? 1 : 0;
	$settings['columns'] = isset( $request['columns'] ) ? agum_sanitize_columns( wp_unslash( $request['columns'] ) ) : $current['columns'];

	update_option( 'agum_settings', $settings );
	wp_cache_delete( 'agum_settings', 'agum' );
	AGUM_DB::sync_dynamic_columns( $previous_columns );
	wp_cache_delete( 'agum_user_columns', 'agum' );
	wp_cache_delete( 'agum_required_fields', 'agum' );

	return $settings;
}

/**
 * One-time migration for installs where phone_number was made required by old defaults.
 *
 * @return void
 */
function agum_migrate_phone_required_setting() {
	if ( get_option( 'agum_phone_required_migrated' ) ) {
		return;
	}
	$settings = agum_get_settings();
	foreach ( $settings['columns'] as &$column ) {
		if ( isset( $column['key'] ) && 'phone_number' === sanitize_key( $column['key'] ) ) {
			$column['required'] = 0;
		}
	}
	unset( $column );
	update_option( 'agum_settings', $settings );
	update_option( 'agum_phone_required_migrated', current_time( 'mysql' ) );
	wp_cache_delete( 'agum_settings', 'agum' );
	wp_cache_delete( 'agum_required_fields', 'agum' );
}
