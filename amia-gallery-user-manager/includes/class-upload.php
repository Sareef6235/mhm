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
	const OPTIMIZED_MAX_SIZE = 307200;

	public static function ensure_upload_directories() {
		$info = agum_upload_info();
		wp_mkdir_p( $info['image_dir'] );
		foreach ( array( $info['base_dir'], $info['image_dir'] ) as $dir ) {
			if ( ! file_exists( trailingslashit( $dir ) . 'index.php' ) ) {
				file_put_contents( trailingslashit( $dir ) . 'index.php', '<?php // Silence is golden.' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			}
		}
	}

	/**
	 * Default fallback profile image URL.
	 *
	 * @return string
	 */
	public static function fallback_image_url() {
		return AGUM_URL . 'assets/images/default-profile.svg';
	}

	/**
	 * Validate a stored image URL/reference.
	 *
	 * @param string $reference Image URL or filename.
	 * @return true|WP_Error
	 */
	public static function validate_image_reference( $reference ) {
		$reference = trim( (string) $reference );
		if ( '' === $reference ) {
			return new WP_Error( 'missing_image', __( 'Profile image is required.', 'amia-gallery-user-manager' ) );
		}

		$path = wp_parse_url( $reference, PHP_URL_PATH );
		$ext  = strtolower( pathinfo( $path ? $path : $reference, PATHINFO_EXTENSION ) );
		if ( ! in_array( $ext, array( 'jpg', 'jpeg', 'png', 'webp' ), true ) ) {
			if ( 'svg' !== $ext || esc_url_raw( $reference ) !== self::fallback_image_url() ) {
				return new WP_Error( 'invalid_image_reference', __( 'Image path/profile photo must reference JPG, JPEG, PNG, or WEBP.', 'amia-gallery-user-manager' ) );
			}
		}

		return true;
	}

	/**
	 * Validate uploaded image only.
	 *
	 * @param array $file Upload array.
	 * @return array|WP_Error
	 */
	public static function validate_upload_file( $file ) {
		if ( empty( $file['tmp_name'] ) || ! is_uploaded_file( $file['tmp_name'] ) ) {
			return new WP_Error( 'invalid_upload', __( 'A valid profile photo is required.', 'amia-gallery-user-manager' ) );
		}
		if ( ! empty( $file['error'] ) ) {
			return new WP_Error( 'upload_error', __( 'The profile photo upload failed.', 'amia-gallery-user-manager' ) );
		}
		if ( (int) $file['size'] > self::MAX_SIZE ) {
			return new WP_Error( 'file_too_large', __( 'Image must be 5MB or smaller.', 'amia-gallery-user-manager' ) );
		}

		$check = wp_check_filetype_and_ext(
			$file['tmp_name'],
			$file['name'],
			array(
				'jpg|jpeg' => 'image/jpeg',
				'png'      => 'image/png',
				'webp'     => 'image/webp',
			)
		);

		if ( empty( $check['ext'] ) || empty( $check['type'] ) ) {
			return new WP_Error( 'invalid_type', __( 'Only JPG, JPEG, PNG, and WEBP profile photos are allowed.', 'amia-gallery-user-manager' ) );
		}

		return $check;
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

		$check = self::validate_upload_file( $file );
		if ( is_wp_error( $check ) ) {
			return $check;
		}

		$basename = agum_normalize_image_basename( $name );
		if ( ! $basename ) {
			return new WP_Error( 'invalid_name', __( 'User name cannot be converted to a safe image filename.', 'amia-gallery-user-manager' ) );
		}

		$info = agum_upload_info();
		$filename = $basename . '.' . strtolower( $check['ext'] );
		$target = trailingslashit( $info['image_dir'] ) . $filename;

		if ( file_exists( $target ) && ! self::is_same_user_image( $target, $user_id ) ) {
			return new WP_Error( 'duplicate_image_name', __( 'A profile photo with this database user name already exists.', 'amia-gallery-user-manager' ) );
		}

		if ( ! @move_uploaded_file( $file['tmp_name'], $target ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			return new WP_Error( 'move_failed', __( 'Could not save uploaded image.', 'amia-gallery-user-manager' ) );
		}

		@chmod( $target, 0644 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		$optimized = self::optimize_image( $target, $check['type'] );
		if ( is_wp_error( $optimized ) ) {
			return $optimized;
		}
		self::generate_thumbnail( $target );
		AGUM_Background::enqueue( 'image_optimized', array( 'file' => $filename, 'path' => $target ) );
		$url = trailingslashit( $info['image_url'] ) . $filename;

		if ( $user_id ) {
			self::sync_profile_image( $user_id, $url );
			AGUM_Logger::log( 'image_uploaded', sprintf( 'Mapped image %s', $filename ), $user_id );
		}

		return array( 'url' => esc_url_raw( $url ), 'filename' => $filename );
	}

	/**
	 * Determine whether a target image already belongs to this AGUM profile.
	 *
	 * @param string $target Target filesystem path.
	 * @param int    $user_id AGUM profile ID.
	 * @return bool
	 */
	private static function is_same_user_image( $target, $user_id ) {
		if ( ! $user_id ) {
			return false;
		}
		$profile = AGUM_Users::get( $user_id );
		if ( ! $profile || empty( $profile->image_path ) ) {
			return false;
		}
		return basename( $target ) === basename( wp_parse_url( $profile->image_path, PHP_URL_PATH ) );
	}

	/**
	 * Sync image URL into AGUM row and WordPress user meta.
	 *
	 * @param int    $user_id AGUM ID.
	 * @param string $url Image URL.
	 * @return void
	 */
	public static function sync_profile_image( $user_id, $url ) {
		global $wpdb;
		$url = esc_url_raw( $url );
		$data = array( 'updated_at' => current_time( 'mysql' ) );
		$formats = array( '%s' );
		foreach ( array( 'image_path', 'profile_photo' ) as $column ) {
			if ( in_array( $column, AGUM_DB::user_columns(), true ) ) {
				$data[ $column ] = $url;
				$formats[] = '%s';
			}
		}
		$wpdb->update( AGUM_DB::users_table(), $data, array( 'id' => absint( $user_id ) ), $formats, array( '%d' ) );
		$profile = AGUM_Users::get( $user_id );
		if ( $profile && ! empty( $profile->wp_user_id ) ) {
			update_user_meta( absint( $profile->wp_user_id ), 'agum_profile_image', $url );
			update_user_meta( absint( $profile->wp_user_id ), 'agum_profile_photo', $url );
			update_user_meta( absint( $profile->wp_user_id ), 'wp_user_avatar', $url );
		}
	}


	/**
	 * Optimize uploaded images to a responsive profile size and target 300KB.
	 *
	 * @param string $path File path.
	 * @param string $mime MIME type.
	 * @return true|WP_Error
	 */
	public static function optimize_image( $path, $mime = '' ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$editor = wp_get_image_editor( $path );
		if ( is_wp_error( $editor ) ) {
			return $editor;
		}

		$editor->resize( 900, 900, false );
		$quality = 86;
		$result = true;
		do {
			if ( method_exists( $editor, 'set_quality' ) ) {
				$editor->set_quality( $quality );
			}
			$result = $editor->save( $path, $mime );
			clearstatcache( true, $path );
			$quality -= 8;
		} while ( ! is_wp_error( $result ) && file_exists( $path ) && filesize( $path ) > self::OPTIMIZED_MAX_SIZE && $quality >= 42 );

		return is_wp_error( $result ) ? $result : true;
	}

	/**
	 * Generate optimized thumbnail next to the profile image.
	 *
	 * @param string $path File path.
	 * @return string|WP_Error
	 */
	public static function generate_thumbnail( $path ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$editor = wp_get_image_editor( $path );
		if ( is_wp_error( $editor ) ) {
			return $editor;
		}
		$editor->resize( 160, 160, true );
		$thumb = trailingslashit( dirname( $path ) ) . pathinfo( $path, PATHINFO_FILENAME ) . '-thumb.' . pathinfo( $path, PATHINFO_EXTENSION );
		$saved = $editor->save( $thumb );
		return is_wp_error( $saved ) ? $saved : $thumb;
	}

	/**
	 * Bulk upload and auto-match images to AGUM users by username/name.
	 *
	 * @param array $files Multiple file input array.
	 * @return array
	 */
	public static function bulk_upload_images( $files ) {
		$report = array( 'mapped' => 0, 'skipped' => 0, 'errors' => array(), 'items' => array() );
		if ( empty( $files['name'] ) || ! is_array( $files['name'] ) ) {
			$report['errors'][] = __( 'No images were selected.', 'amia-gallery-user-manager' );
			return $report;
		}

		foreach ( $files['name'] as $index => $name ) {
			$file = array(
				'name'     => $files['name'][ $index ],
				'type'     => $files['type'][ $index ],
				'tmp_name' => $files['tmp_name'][ $index ],
				'error'    => $files['error'][ $index ],
				'size'     => $files['size'][ $index ],
			);
			$user = AGUM_Users::find_for_image_upload( $file['name'] );
			if ( ! $user ) {
				++$report['skipped'];
				$report['errors'][] = sprintf( __( '%s skipped: no matching username or name.', 'amia-gallery-user-manager' ), sanitize_file_name( $file['name'] ) );
				continue;
			}
			$result = self::handle_named_image( $file, $user->username ? $user->username : $user->name, $user->id );
			if ( is_wp_error( $result ) ) {
				++$report['skipped'];
				$report['errors'][] = sprintf( '%s: %s', sanitize_file_name( $file['name'] ), $result->get_error_message() );
				continue;
			}
			++$report['mapped'];
			$report['items'][] = array( 'user' => $user->username, 'filename' => $result['filename'], 'url' => $result['url'] );
		}
		AGUM_Logger::log( 'bulk_image_upload', sprintf( 'Bulk image upload mapped %d images and skipped %d.', $report['mapped'], $report['skipped'] ), null, $report );
		return $report;
	}

	public static function auto_assign_existing_image( $user_id, $name ) {
		$info = agum_upload_info();
		$base = agum_normalize_image_basename( $name );
		foreach ( array( 'jpg', 'jpeg', 'png', 'webp' ) as $ext ) {
			$file = trailingslashit( $info['image_dir'] ) . $base . '.' . $ext;
			if ( file_exists( $file ) ) {
				$url = trailingslashit( $info['image_url'] ) . basename( $file );
				self::sync_profile_image( $user_id, $url );
				return $url;
			}
		}
		return '';
	}
}
