<?php
/*
Plugin Name: Training Calendar for WooCommerce
Description: Training Calendar for WooCommerce is a powerful scheduling and event management plugin that allows administrators to create, manage, and display training sessions linked with WooCommerce products. It supports manual and recurring events, custom date ranges, multiple time slots (start & end), and location-based sessions. The plugin provides an interactive calendar (month, week, and day views) and allows users to browse sessions and directly purchase subscriptions or bookings. It also includes a shortcode to display today's events in a responsive card layout across any page. Ideal for academies, sports clubs, coaching centers, and subscription-based training platforms.
Version: 1.0.0
Author: <a href="https://mrhammad.com">Muhammad Hamad</a> at <a href="https://innovateksol.com">Innovatek Solutions</a>
Author URI: https://mrhammad.com
Plugin URI: https://github.com/Hamad-mirza/
*/


if (!defined('ABSPATH')) exit;

define('TC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TC_PLUGIN_PATH', plugin_dir_path(__FILE__));

require_once TC_PLUGIN_PATH . 'includes/class-cpt.php';
require_once TC_PLUGIN_PATH . 'includes/class-calendar.php';

new TC_CPT();
new TC_Calendar();