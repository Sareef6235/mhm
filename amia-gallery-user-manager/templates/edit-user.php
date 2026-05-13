<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<div id="agum-user-modal" class="agum-modal" aria-hidden="true">
	<div class="agum-modal-panel">
		<button class="agum-modal-close" type="button">×</button>
		<h2>User Profile</h2>
		<form class="agum-user-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
			<input type="hidden" name="action" value="agum_save_user"><input type="hidden" name="id" value=""><?php wp_nonce_field( AGUM_Security::NONCE_ACTION, AGUM_Security::NONCE_NAME ); ?>
			<?php agum_template( 'add-user' ); ?>
			<div class="agum-modal-actions"><button class="agum-btn" type="submit">Save User</button><button class="agum-btn agum-btn-ghost agum-modal-close" type="button">Cancel</button></div>
		</form>
	</div>
</div>
