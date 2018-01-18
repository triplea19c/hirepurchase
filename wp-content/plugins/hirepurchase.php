<?php
/*
Plugin name: redirect on post
Desciption: 

http://stackoverflow.com/questions/13686245/how-to-create-a-custom-url-based-on-dropdown-in-wordpress-form-submission
I-changed-dropdown-to-field-input
*/ 
function redirect_on_submit() {
  // check if the post is set
  if (isset($_POST['amount']) && ! empty ($_POST['amount'])) 

{
    header( "Location: http://localhost/index.php/hirepurchase/&amount=" . $_POST['amount'] );
  }
}
add_action('init', redirect_on_submit);
?>