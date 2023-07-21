<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress_test' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         ',eL*vL:>DW([:F2Yv[@qd{IO|Uus4V3%Ui%nA=Q@6f-a;_Fs.mJF_$*^5;8T9eoX' );
define( 'SECURE_AUTH_KEY',  'b%v-h7{faK)FhpY<I:PwJstn-!L[2`V3_~88q;2Qt4iriXQO)nES/*5lx=Emdc$I' );
define( 'LOGGED_IN_KEY',    '&ny!i[dhSEuS,6D3D&c$4=aHqkX+_hQ,z)eZ2xU7goh]>Xi^449Q!Mw61+s7!a}y' );
define( 'NONCE_KEY',        'JN5>`4xiQ(UqaZ:dm(`E.MYa 5-o3O?bxs&R6e_BWU<T=&;p-k_B<l6z ew&m%90' );
define( 'AUTH_SALT',        'mY4NS8sQ}&C*.!h7Se>EP]cpAUsev&?Y~3%BsrW,23EkK.8v4)!D(*s3m]~%P&OT' );
define( 'SECURE_AUTH_SALT', 'AX<D<{xFbb<u )Ov<^W,ezz0j~&zur!MS}L[8kMUL}f>~Nb:x+{555H[@u,3M83Q' );
define( 'LOGGED_IN_SALT',   'uGI+bj^~&2mhkog--$qv:lcZ/?X2o/T*S l|7x|;t]YPp`}rQ]^%E[Ee6Nh}bg&o' );
define( 'NONCE_SALT',       'fiLwu)RA}4 D;$}GhJKJh/UG,A`OC~;~p2Mul8WrAOY#f6.=){{evP0EGSnQ}b-i' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
