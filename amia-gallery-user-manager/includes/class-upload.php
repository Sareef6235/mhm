<?php
/**
 * Upload service.
 *
 * @package AMIA_Gallery_User_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AGUM_Upload {
	const MAX_SIZE = 5242880;

	public static function ensure_upload_directories() {
		$info = agum_upload_info();
		wp_mkdir_p( $info['image_dir'] );
		if ( ! file_exists( trailingslashit( $info['base_dir'] ) . 'index.php' ) ) {
			file_put_contents( trailingslashit( $info['base_dir'] ) . 'index.php', '<?php // Silence is golden.' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		}
		if ( ! file_exists( trailingslashit( $info['image_dir'] ) . 'index.php' ) ) {
			file_put_contents( trailingslashit( $info['image_dir'] ) . 'index.php', '<?php // Silence is golden.' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		}
	}

	public static function handle_user_image( $file, $user_id ) {
		$user = AGUM_Users::get( $user_id );
		if ( ! $user ) {
			return new WP_Error( 'missing_user', __( 'User not found for image mapping.', 'amia-gallery-user-manager' ) );
		}
		return self::handle_named_image( $file, $user->name, $user_id );
	}

	public static function handle_named_image( $file, $name, $user_id = 0 ) {
		global $wpdb;
		self::ensure_upload_directories();

		if ( empty( $file['tmp_name'] ) || ! is_uploaded_file( $file['tmp_name'] ) ) {
			return new WP_Error( 'invalid_upload', __( 'No valid image was uploaded.', 'amia-gallery-user-manager' ) );
		}
		if ( (int) $file['size'] > self::MAX_SIZE ) {
			return new WP_Error( 'file_too_large', __( 'Image must be 5MB or smaller.', 'amia-gallery-user-manager' ) );
		}

		$check = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], array(
			'jpg|jpeg' => 'image/jpeg',
			'png'      => 'image/png',
			'webp'     => 'image/webp',
		) );

		if ( empty( $check['ext'] ) || empty( $check['type'] ) ) {
			return new WP_Error( 'invalid_type', __( 'Only JPG, JPEG, PNG, and WEBP images are allowed.', 'amia-gallery-user-manager' ) );
		}

		$basename = agum_normalize_image_basename( $name );
		if ( ! $basename ) {
			return new WP_Error( 'invalid_name', __( 'User name cannot be converted to a safe image filename.', 'amia-gallery-user-manager' ) );
		}

		$info = agum_upload_info();
		$filename = $basename . '.' . strtolower( $check['ext'] );
		$target = trailingslashit( $info['image_dir'] ) . $filename;

		if ( ! @move_uploaded_file( $file['tmp_name'], $target ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			return new WP_Error( 'move_failed', __( 'Could not save uploaded image.', 'amia-gallery-user-manager' ) );
		}

		@chmod( $target, 0644 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		$url = trailingslashit( $info['image_url'] ) . $filename;

		if ( $user_id ) {
			$wpdb->update( AGUM_DB::users_table(), array( 'image_path' => esc_url_raw( $url ), 'updated_at' => current_time( 'mysql' ) ), array( 'id' => absint( $user_id ) ), array( '%s', '%s' ), array( '%d' ) );
			AGUM_Logger::log( 'image_uploaded', sprintf( 'Mapped image %s', $filename ), $user_id );
		}

		return array( 'url' => esc_url_raw( $url ), 'filename' => $filename );
	}

	public static function auto_assign_existing_image( $user_id, $name ) {
		global $wpdb;
		$info = agum_upload_info();
		$base = agum_normalize_image_basename( $name );
		foreach ( array( 'jpg', 'jpeg', 'png', 'webp' ) as $ext ) {
			$file = trailingslashit( $info['image_dir'] ) . $base . '.' . $ext;
			if ( file_exists( $file ) ) {
				$url = trailingslashit( $info['image_url'] ) . basename( $file );
				$wpdb->update( AGUM_DB::users_table(), array( 'image_path' => esc_url_raw( $url ) ), array( 'id' => absint( $user_id ) ), array( '%s' ), array( '%d' ) );
				return $url;
			}
		}
		return '';
	}
}
