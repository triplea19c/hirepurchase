<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wordpress');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'root');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/** Language set to english (american) **/
define( 'WPLANG', 'en_US' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '=6%TQt#ca1{UY.@db4@cm2_ Q#7HAi{S7LM}3-k/XMFo)>tlEG3aKtEaf9`58+Y,');
define('SECURE_AUTH_KEY',  '{(y^P3nt(KA{-%$!gza!l0:*2,8^<FU2Bt1-?!T,E-T3I$}d7CVV8&>fQs$,.w>y');
define('LOGGED_IN_KEY',    '&<Jh/FGL>5`2R0EB_&5E2KVx-=KL2R3FIoZ=n.aY#Ta^=.$|n5bn~b2WE1NwZb@6');
define('NONCE_KEY',        '$9rxL)yQ1CePSb,l^$Jy5nGhUo$U4L2Jw-qx4@1_aNq>yaT^U%EBCKXk9,l,}|$[');
define('AUTH_SALT',        '*hxgfN *5GVDmc?L%4D<J{:%bJTDd80=E.V do$r*%s`C%+g7-MhOcP#!J,2VjR^');
define('SECURE_AUTH_SALT', 'Ac<{X^r[xLt[:}EiwzN8.LW;1oqbufu1*^FT%-X~2~FfiC~yAnjc|(SK)!wVza*O');
define('LOGGED_IN_SALT',   ',EoQ2*K HaS3?50 nZ P^P _ep]iL:19/[{bI7~Im#!{Rw](gKL:Xcc|vr_-%:DY');
define('NONCE_SALT',       '=%&Pf]oPI5Nsf)<Us#0i.~31`;w[rnRh7i9;g:r5?YY.G~ZR~NvFh=u0. 7fXMgm');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

define('WP_MEMORY_LIMIT', '256M');

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

/** To prevent plugins and themes from outputting notices entirely, ensure debug mode is off in your wp-config file **/
define('WP_DEBUG', false);
error_reporting(0);
@ini_set('display_errors', 0);

?>