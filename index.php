<?php
/**
 * Plugin Name: NSK Diplom Plugin
 * Plugin URI: https://nsk.no
 * Description: Plugin for å administrere og vise NSK-diplomer og priser. Lar medlemmer legge til diplomer via webskjema og viser dem på en offentlig side.
 * Version: 1.1.0
 * Author: NSK
 * Author URI: https://nsk.no
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: nsk-diplom
 * Domain Path: /languages
 * Requires at least: 5.0 * Tested up to: 6.8
 * Requires PHP: 7.2
 * Network: false
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include the main plugin file
require_once plugin_dir_path(__FILE__) . 'nsk-diplom-plugin.php';
