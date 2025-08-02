<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'Wordpress' );

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
define( 'AUTH_KEY',         'a{`:,vZvtw3f{?(AE~=qci/P-@dL^])^.}eLycRqw4ud^BZ7r*BmC4,QAzP&CVn`' );
define( 'SECURE_AUTH_KEY',  '7h`QEjU)QFh:F}a8[1xEOd`+<bRm}8N7?3J.kn)o8}]+>DpK5~k)d2+9Nojz7L h' );
define( 'LOGGED_IN_KEY',    '/]hX6lMyD76(kYeb-7B =-`ejJ{?#uFNI~$|;7;S?g~Bm%DUBL+3{{^;o>}/GMF;' );
define( 'NONCE_KEY',        'Ir.Oge6d>AB`BRLTOQ/~$p|s3fjmid*gkLPpBnJcyWcRy;BpBzsG,m,fc=rD>(P+' );
define( 'AUTH_SALT',        '6/dKIY^Ck($i1 {3?$(>e>7C&ILD9~I>~2fK]VBmm|$=q#8K!q*zbom;wKxC-`Y#' );
define( 'SECURE_AUTH_SALT', 'r;k`)P4yM<Gif/Gv$gO1UJD<_r7uB2|[:MQ/A38ycSXG~lVRQIy?6A/%E+jyemDY' );
define( 'LOGGED_IN_SALT',   'qb06c>7R5iE,%JS(?Nr4cW3OOBPeW1ceYGtgq$yKDAY&1x~`LY^++D@VO{Q{A80V' );
define( 'NONCE_SALT',       '1ScLw N&nXg7SEb?vu4i$9<ZU/y+kOquj/DO!Hm7G<:m(3Z?j-I&t]j?bTbUFW4f' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
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
