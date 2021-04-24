<?php
/**
 * For the chat to work on your domain you also got to set the
 * value for 'ajaxURL' in assets/mychat.js:28.
 */

/** The name of the database*/
define( 'DB_NAME', '' );

/** MySQL database username */
define( 'DB_USER', '' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', '' );

/** Video embed code **/
define('TWITCH_CHANNEL', '');

/** Polling intervall in seconds */
define('POLL_INTERVAL', 3);

/** Number of polling cycles till a user is marked as offline */
define('OFFLINE_CYCLES', 3);

/** Limit number of old messages loaded on inital chat display */
define('HISTORY_COUNT', 3);
