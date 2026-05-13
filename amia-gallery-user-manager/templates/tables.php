<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<div class="agum-table-responsive">
<table class="agum-table">
	<thead><tr><th><input type="checkbox" class="agum-select-all"></th><th>Photo</th><th>Name</th><th>Student ID</th><th>Admission</th><th>Class</th><th>Role</th><th>Phone</th><th>Telegram</th><th>Last Login</th><th>Actions</th></tr></thead>
	<tbody>
	<?php if ( empty( $users ) ) : ?><tr><td colspan="11" class="agum-empty">No users found.</td></tr><?php endif; ?>
	<?php foreach ( (array) $users as $user ) : ?>
	<tr data-user='<?php echo esc_attr( wp_json_encode( $user ) ); ?>'>
		<td><input type="checkbox" class="agum-row-check" value="<?php echo esc_attr( $user->id ); ?>"></td>
		<td><?php if ( $user->image_path ) : ?><img class="agum-user-photo" src="<?php echo esc_url( $user->image_path ); ?>" alt=""><?php else : ?><span class="agum-photo-empty">👤</span><?php endif; ?></td>
		<td><strong><?php echo esc_html( $user->name ); ?></strong><small><?php echo esc_html( $user->username ); ?></small></td>
		<td><?php echo esc_html( $user->student_id ); ?></td><td><?php echo esc_html( $user->admission_no ); ?></td><td><?php echo esc_html( $user->class ); ?></td><td><span class="agum-pill"><?php echo esc_html( $user->role ); ?></span></td><td><?php echo esc_html( $user->phone_number ); ?></td><td><?php echo esc_html( $user->telegram_username ); ?></td><td><?php echo esc_html( $user->last_login ? $user->last_login : '—' ); ?></td>
		<td><button class="agum-icon agum-edit-user">✎</button><button class="agum-icon agum-otp" data-id="<?php echo esc_attr( $user->id ); ?>">OTP</button></td>
	</tr>
	<?php endforeach; ?>
	</tbody>
</table>
</div>
