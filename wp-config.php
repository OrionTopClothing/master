<?php
//Begin Really Simple SSL session cookie settings
@ini_set('session.cookie_httponly', true);
@ini_set('session.cookie_secure', true);
@ini_set('session.use_only_cookies', true);
//END Really Simple SSL

define('WP_CACHE', false); // Added by WP Rocket
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
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'unitedar_orion' );

/** MySQL database username */
define( 'DB_USER', 'unitedar_orion' );

/** MySQL database password */
define( 'DB_PASSWORD', '2m$CV}0TWHL+' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'F[K~Dray-apvh{d*C;[T.$9AVmq{My[<K3gJ s8UKRW91t@.0_${Jf1g&G(FmUhg' );
define( 'SECURE_AUTH_KEY',  '@ ~EhF[+vg9+v,!*d>2ZLrnDpz}1 RF;a>QN%[`Fn(b3F82bZepm&@azw%16jcC}' );
define( 'LOGGED_IN_KEY',    'y8~Q0F`lU,bv>^r<2S(t,:7;9$69Uu.7^u/myq2H*#$/3P8mIc>W@6j`4yGIEsmA' );
define( 'NONCE_KEY',        'nKOL@7Pa?y5]_Zl|QFW3(Yfb) I[9V0 pN:qa#SJP4qUvb)ujF}3qC<_1|G7<i_7' );
define( 'AUTH_SALT',        'Rg-AiWqN&0 **C%56GAsHV[OnX@=8y}M71]@YDWwd>@G}u,jbzK>KCHb#%A|}(jY' );
define( 'SECURE_AUTH_SALT', 'H1_8.jb+7s{5iWp7$puRu~5luNd4o&s@6z#i(@!|n||zD9#^)d9Sexk63ht<nh1$' );
define( 'LOGGED_IN_SALT',   '3tDGjdai29go0n)4ox:9TL;;)iPzj/)hDO9i$Y9f6{Daxy0(oOJ![.oW3q A)0W6' );
define( 'NONCE_SALT',       '0H<,aI9VFg1];E7AIlMtsHqw1 %lMX1nG5n#b{LHusB,i(K-I]{Ix^TM@2o3,%0E' );

/**#@-*/

/**
 * WordPress Database Table prefix.
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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
