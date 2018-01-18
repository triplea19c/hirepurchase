<?php

function tml_registration_errors( $errors ) {
	if ( empty( $_POST['first_name'] ) )
		$errors->add( 'empty_first_name', '<strong>ERROR</strong>: Please enter your first name.' );
	if ( empty( $_POST['last_name'] ) )
		$errors->add( 'empty_last_name', '<strong>ERROR</strong>: Please enter your last name.' );
	if ( empty( $_POST['work_place'] ) )
		$errors->add( 'empty_work_place', '<strong>ERROR</strong>: Please enter your workplace.' );
	if ( empty( $_POST['position'] ) )
		$errors->add( 'empty_position', '<strong>ERROR</strong>: Please enter your position.' );
	if ( empty( $_POST['referee'] ) )
		$errors->add( 'empty_referee', '<strong>ERROR</strong>: Please enter your referee.' );
	return $errors;
}
add_filter( 'registration_errors', 'tml_registration_errors' );

function tml_user_register( $user_id ) {
	if ( !empty( $_POST['first_name'] ) )
		update_user_meta( $user_id, 'first_name', $_POST['first_name'] );
	if ( !empty( $_POST['last_name'] ) )
		update_user_meta( $user_id, 'last_name', $_POST['last_name'] );
	if ( !empty( $_POST['work_place'] ) )
		update_user_meta( $user_id, 'work_place', $_POST['work_place'] );
	if ( !empty( $_POST['position'] ) )
		update_user_meta( $user_id, 'position', $_POST['position'] );
	if ( !empty( $_POST['referee'] ) )
                update_user_meta( $user_id, 'referee', $_POST['referee'] );
}
add_action( 'user_register', 'tml_user_register' );

?>
