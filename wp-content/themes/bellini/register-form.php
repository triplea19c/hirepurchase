<?php
/*
If you would like to edit this file, copy it to your current theme's directory and edit it there.
Theme My Login will always look in your theme's directory first, before using this default template.
*/
?>
<div class="tml tml-register" id="theme-my-login<?php $template->the_instance(); ?>">
	<?php $template->the_action_template_message( 'register' ); ?>
	<?php $template->the_errors(); ?>
	<form name="registerform" id="registerform<?php $template->the_instance(); ?>" action="<?php $template->the_action_url( 'register', 'login_post' ); ?>" method="post">
		<?php if ( 'email' != $theme_my_login->get_option( 'login_type' ) ) : ?>
		<p class="tml-user-login-wrap">
			<label for="user_login<?php $template->the_instance(); ?>"><?php _e( 'Username', 'theme-my-login' ); ?></label>
			<input type="text" name="user_login" id="user_login<?php $template->the_instance(); ?>" class="input" value="<?php $template->the_posted_value( 'user_login' ); ?>" size="20" />
		</p>

		<?php endif; ?>

		<?php do_action( 'register_form' ); ?>

		<p class="tml-user-email-wrap">
			<label for="user_email<?php $template->the_instance(); ?>"><?php _e( 'E-mail', 'theme-my-login' ); ?></label>
			<input type="text" name="user_email" id="user_email<?php $template->the_instance(); ?>" class="input" value="<?php $template->the_posted_value( 'user_email' ); ?>" size="20" />
		</p>

		<p>
			<label for="first_name<?php $template->the_instance(); ?>"><?php _e( 'First name', 'theme-my-login' ) ?></label>
			<input type="text" name="first_name" id="first_name<?php $template->the_instance(); ?>" class="input" value="<?php $template->the_posted_value( 'first_name' ); ?>" size="20" tabindex="20" />
		</p>

		<p>
			<label for="last_name<?php $template->the_instance(); ?>"><?php _e( 'Last name', 'theme-my-login' ) ?></label>
			<input type="text" name="last_name" id="last_name<?php $template->the_instance(); ?>" class="input" value="<?php $template->the_posted_value( 'last_name' ); ?>" size="20" tabindex="20" />
		</p>

		<p>
			<label for="work_place<?php $template->the_instance(); ?>"><?php _e( 'Workplace', 'theme-my-login' ) ?></label>
			<input type="text" name="work_place" id="work_place<?php $template->the_instance(); ?>" class="input" value="<?php $template->the_posted_value( 'work_place' ); ?>" size="20" tabindex="20" />
		</p>

		<p>
			<label for="position<?php $template->the_instance(); ?>"><?php _e( 'Position', 'theme-my-login' ) ?></label>
			<input type="text" name="position" id="position<?php $template->the_instance(); ?>" class="input" value="<?php $template->the_posted_value( 'position' ); ?>" size="20" tabindex="20" />
		</p>

		<p>
			<label for="referee<?php $template->the_instance(); ?>"><?php _e( 'Referee', 'theme-my-login' ) ?></label>
			<input type="text" name="referee" id="referee<?php $template->the_instance(); ?>" class="input" value="<?php $template->the_posted_value( 'referee' ); ?>" size="20" tabindex="20" />
		</p>

		<p class="tml-registration-confirmation" id="reg_passmail<?php $template->the_instance(); ?>"><?php echo apply_filters( 'tml_register_passmail_template_message', __( 'Registration confirmation will be e-mailed to you.', 'theme-my-login' ) ); ?></p>

		<p class="tml-submit-wrap">
			<input type="submit" name="wp-submit" id="wp-submit<?php $template->the_instance(); ?>" value="<?php esc_attr_e( 'Register', 'theme-my-login' ); ?>" />
			<input type="hidden" name="redirect_to" value="<?php $template->the_redirect_url( 'register' ); ?>" />
			<input type="hidden" name="instance" value="<?php $template->the_instance(); ?>" />
			<input type="hidden" name="action" value="register" />
		</p>
	</form>
	<?php $template->the_action_links( array( 'register' => false ) ); ?>
</div>
