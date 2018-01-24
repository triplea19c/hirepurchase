<?php
//
// Recommended way to include parent theme styles.
//  (Please see http://codex.wordpress.org/Child_Themes#How_to_Create_a_Child_Theme)
//
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );
function theme_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array('parent-style')
    );
}

function prefix_add_discount_line( $cart ) {

  $discount = $cart->subtotal * 0.5;

  $cart->add_fee( __( 'Down Payment', 'yourtext-domain' ) , -$discount );

}
add_action( 'woocommerce_cart_calculate_fees', 'prefix_add_discount_line' ); ?>
