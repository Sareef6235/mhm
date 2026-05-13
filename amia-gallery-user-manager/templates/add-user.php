<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<div class="agum-form-grid">
	<label>Student ID<input name="student_id" type="text" required></label>
	<label>Admission No<input name="admission_no" type="text" required></label>
	<label>Name<input name="name" type="text" required></label>
	<label>Class<input name="class" type="text" required></label>
	<label>DOB<input name="dob" type="date" required></label>
	<label>Role<select name="role" required><option value="student">Student → Subscriber</option><option value="ustad">Ustad → Editor</option><option value="admin">Admin → Administrator</option><option value="superadmin">Superadmin → Administrator</option><option value="staff">Staff → Subscriber</option></select></label>
	<label>Username<input name="username" type="text" required></label>
	<label>Email<input name="email" type="email" required></label>
	<label>Password<input name="password" type="password" minlength="8" required></label>
	<label>Phone Number<input name="phone_number" type="tel" pattern="\+?[0-9]{7,15}" required></label>
	<label>Telegram Username<input name="telegram_username" type="text"></label>
	<label>Telegram ID<input name="telegram_id" type="text"></label>
	<label>Profile Photo<input name="profile_photo_file" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"></label>
	<label>Image Path<input name="image_path" type="url" readonly placeholder="Auto-filled after image validation"></label>
	<label>Profile Photo URL<input name="profile_photo" type="url" readonly placeholder="Auto-filled after image validation"></label>
</div>
<div class="agum-preview"><img class="agum-profile-preview" src="<?php echo esc_url( AGUM_Upload::fallback_image_url() ); ?>" alt="Profile preview"></div>
