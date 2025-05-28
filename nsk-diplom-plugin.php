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
 * Requires at least: 5.0
 * Tested up to: 6.8
 * Requires PHP: 7.2
 * Network: false
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('NSK_DIPLOM_VERSION', '1.1.0');
define('NSK_DIPLOM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('NSK_DIPLOM_PLUGIN_PATH', plugin_dir_path(__FILE__));

/**
 * Main NSK Diplom Plugin Class
 */
class NSK_Diplom_Plugin {    public function __construct() {
        // Check PHP version compatibility first
        if (version_compare(PHP_VERSION, '7.2', '<')) {
            add_action('admin_notices', array($this, 'php_version_notice'));
            return;
        }
        
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Check WordPress version compatibility
        add_action('admin_notices', array($this, 'check_wordpress_version'));
    }
      /**
     * Initialize the plugin
     */
    public function init() {
        $this->register_post_type();
        $this->add_meta_boxes();
        $this->handle_form_submission();
        $this->add_shortcodes();
        $this->create_rewrite_rules();
        
        // Flush rewrite rules if needed
        if (get_option('nsk_diplom_flush_rewrite_rules')) {
            flush_rewrite_rules();
            delete_option('nsk_diplom_flush_rewrite_rules');
        }
    }
      /**
     * Check WordPress version compatibility
     */
    public function check_wordpress_version() {
        global $wp_version;
        
        if (version_compare($wp_version, '5.0', '<')) {
            echo '<div class="notice notice-error"><p>';
            echo '<strong>NSK Diplom Plugin:</strong> Krever WordPress 5.0 eller nyere. ';
            echo 'Du bruker versjon ' . $wp_version . '. Vennligst oppdater WordPress.';
            echo '</p></div>';
        }
    }
    
    /**
     * PHP version compatibility notice
     */
    public function php_version_notice() {
        echo '<div class="notice notice-error"><p>';
        echo '<strong>NSK Diplom Plugin:</strong> Krever PHP 7.2 eller nyere. ';
        echo 'Du bruker versjon ' . PHP_VERSION . '. Vennligst oppdater PHP eller kontakt din webhost.';
        echo '</p></div>';
    }
    
    /**
     * Register the Diplom custom post type
     */
    public function register_post_type() {
        $labels = array(
            'name'                  => _x('Diplomer', 'Post type general name', 'nsk-diplom'),
            'singular_name'         => _x('Diplom', 'Post type singular name', 'nsk-diplom'),
            'menu_name'             => _x('Diplomer', 'Admin Menu text', 'nsk-diplom'),
            'name_admin_bar'        => _x('Diplom', 'Add New on Toolbar', 'nsk-diplom'),
            'add_new'               => __('Legg til ny', 'nsk-diplom'),
            'add_new_item'          => __('Legg til nytt diplom', 'nsk-diplom'),
            'new_item'              => __('Nytt diplom', 'nsk-diplom'),
            'edit_item'             => __('Rediger diplom', 'nsk-diplom'),
            'view_item'             => __('Vis diplom', 'nsk-diplom'),
            'all_items'             => __('Alle diplomer', 'nsk-diplom'),
            'search_items'          => __('Søk diplomer', 'nsk-diplom'),
            'parent_item_colon'     => __('Overordnet diplom:', 'nsk-diplom'),
            'not_found'             => __('Ingen diplomer funnet.', 'nsk-diplom'),
            'not_found_in_trash'    => __('Ingen diplomer funnet i papirkurv.', 'nsk-diplom'),
        );        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'diplom'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'menu_icon'          => 'dashicons-awards',
            'supports'           => array('title', 'editor', 'thumbnail', 'custom-fields'),
            'show_in_rest'       => true,
            'rest_base'          => 'diplomer',
            'rest_controller_class' => 'WP_REST_Posts_Controller',
            'delete_with_user'   => false,
            'template'           => array(),
            'template_lock'      => false,
        );

        register_post_type('nsk_diplom', $args);
    }
    
    /**
     * Add meta boxes for custom fields
     */
    public function add_meta_boxes() {
        add_action('add_meta_boxes', array($this, 'add_diplom_meta_boxes'));
        add_action('save_post', array($this, 'save_diplom_meta_data'));
    }
    
    /**
     * Add meta boxes to the post edit screen
     */
    public function add_diplom_meta_boxes() {
        add_meta_box(
            'nsk-diplom-details',
            'Diplom Detaljer',
            array($this, 'diplom_meta_box_callback'),
            'nsk_diplom'
        );
    }
    
    /**
     * Meta box callback function
     */
    public function diplom_meta_box_callback($post) {
        wp_nonce_field('nsk_diplom_meta_box', 'nsk_diplom_meta_box_nonce');
        
        $tildeling_dato = get_post_meta($post->ID, '_nsk_tildeling_dato', true);
        $diplom_bilde = get_post_meta($post->ID, '_nsk_diplom_bilde', true);
        $lag_bilde = get_post_meta($post->ID, '_nsk_lag_bilde', true);
        $forklarende_tekst = get_post_meta($post->ID, '_nsk_forklarende_tekst', true);
        
        echo '<table class="form-table">';
        
        echo '<tr>';
        echo '<th><label for="nsk_tildeling_dato">Dato for tildeling/arrangement:</label></th>';
        echo '<td><input type="date" id="nsk_tildeling_dato" name="nsk_tildeling_dato" value="' . esc_attr($tildeling_dato) . '" /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th><label for="nsk_diplom_bilde">Bilde av diplom:</label></th>';
        echo '<td>';
        echo '<input type="hidden" id="nsk_diplom_bilde" name="nsk_diplom_bilde" value="' . esc_attr($diplom_bilde) . '" />';
        echo '<input type="button" class="button" id="upload_diplom_bilde" value="Velg bilde" />';
        echo '<div id="diplom_bilde_preview">';
        if ($diplom_bilde) {
            echo '<img src="' . wp_get_attachment_url($diplom_bilde) . '" style="max-width: 200px; height: auto;" />';
        }
        echo '</div>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th><label for="nsk_lag_bilde">Bilde av stafettlag/arrangørgjeng:</label></th>';
        echo '<td>';
        echo '<input type="hidden" id="nsk_lag_bilde" name="nsk_lag_bilde" value="' . esc_attr($lag_bilde) . '" />';
        echo '<input type="button" class="button" id="upload_lag_bilde" value="Velg bilde" />';
        echo '<div id="lag_bilde_preview">';
        if ($lag_bilde) {
            echo '<img src="' . wp_get_attachment_url($lag_bilde) . '" style="max-width: 200px; height: auto;" />';
        }
        echo '</div>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th><label for="nsk_forklarende_tekst">Forklarende tekst:</label></th>';
        echo '<td><textarea id="nsk_forklarende_tekst" name="nsk_forklarende_tekst" rows="5" cols="50">' . esc_textarea($forklarende_tekst) . '</textarea></td>';
        echo '</tr>';
        
        echo '</table>';
    }
    
    /**
     * Save meta data
     */
    public function save_diplom_meta_data($post_id) {
        if (!isset($_POST['nsk_diplom_meta_box_nonce'])) {
            return;
        }
        
        if (!wp_verify_nonce($_POST['nsk_diplom_meta_box_nonce'], 'nsk_diplom_meta_box')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (isset($_POST['post_type']) && 'nsk_diplom' == $_POST['post_type']) {
            if (!current_user_can('edit_page', $post_id)) {
                return;
            }
        } else {
            if (!current_user_can('edit_post', $post_id)) {
                return;
            }
        }
        
        if (isset($_POST['nsk_tildeling_dato'])) {
            update_post_meta($post_id, '_nsk_tildeling_dato', sanitize_text_field($_POST['nsk_tildeling_dato']));
        }
        
        if (isset($_POST['nsk_diplom_bilde'])) {
            update_post_meta($post_id, '_nsk_diplom_bilde', sanitize_text_field($_POST['nsk_diplom_bilde']));
        }
        
        if (isset($_POST['nsk_lag_bilde'])) {
            update_post_meta($post_id, '_nsk_lag_bilde', sanitize_text_field($_POST['nsk_lag_bilde']));
        }
        
        if (isset($_POST['nsk_forklarende_tekst'])) {
            update_post_meta($post_id, '_nsk_forklarende_tekst', sanitize_textarea_field($_POST['nsk_forklarende_tekst']));
        }
    }
    
    /**
     * Handle form submission from frontend
     */
    public function handle_form_submission() {
        if (isset($_POST['nsk_diplom_submit']) && wp_verify_nonce($_POST['nsk_diplom_nonce'], 'nsk_diplom_form')) {
            if (!is_user_logged_in()) {
                wp_die('Du må være logget inn for å legge til diplomer.');
            }
            
            $title = sanitize_text_field($_POST['diplom_title']);
            $dato = sanitize_text_field($_POST['tildeling_dato']);
            $forklarende_tekst = sanitize_textarea_field($_POST['forklarende_tekst']);
            
            $post_data = array(
                'post_title'    => $title,
                'post_content'  => '',
                'post_status'   => 'publish',
                'post_type'     => 'nsk_diplom',
                'post_author'   => get_current_user_id(),
            );
            
            $post_id = wp_insert_post($post_data);
            
            if ($post_id) {
                update_post_meta($post_id, '_nsk_tildeling_dato', $dato);
                update_post_meta($post_id, '_nsk_forklarende_tekst', $forklarende_tekst);
                
                // Handle file uploads
                if (!empty($_FILES['diplom_bilde']['name'])) {
                    $diplom_bilde_id = $this->handle_file_upload($_FILES['diplom_bilde'], $post_id);
                    if ($diplom_bilde_id) {
                        update_post_meta($post_id, '_nsk_diplom_bilde', $diplom_bilde_id);
                        set_post_thumbnail($post_id, $diplom_bilde_id);
                    }
                }
                
                if (!empty($_FILES['lag_bilde']['name'])) {
                    $lag_bilde_id = $this->handle_file_upload($_FILES['lag_bilde'], $post_id);
                    if ($lag_bilde_id) {
                        update_post_meta($post_id, '_nsk_lag_bilde', $lag_bilde_id);
                    }
                }
                
                wp_redirect(home_url('/diplomer/') . '?success=1');
                exit;
            }
        }
    }
    
    /**
     * Handle file upload
     */
    private function handle_file_upload($file, $post_id) {
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        
        $upload_overrides = array('test_form' => false);
        $movefile = wp_handle_upload($file, $upload_overrides);
        
        if ($movefile && !isset($movefile['error'])) {
            $attachment = array(
                'post_mime_type' => $movefile['type'],
                'post_title'     => preg_replace('/\.[^.]+$/', '', basename($movefile['file'])),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );
            
            $attach_id = wp_insert_attachment($attachment, $movefile['file'], $post_id);
            
            if (!function_exists('wp_generate_attachment_metadata')) {
                require_once(ABSPATH . 'wp-admin/includes/image.php');
            }
            
            $attach_data = wp_generate_attachment_metadata($attach_id, $movefile['file']);
            wp_update_attachment_metadata($attach_id, $attach_data);
            
            return $attach_id;
        }
        
        return false;
    }
    
    /**
     * Add shortcodes
     */
    public function add_shortcodes() {
        add_shortcode('nsk_diplomer', array($this, 'diplomer_shortcode'));
        add_shortcode('nsk_diplom_form', array($this, 'diplom_form_shortcode'));
    }
    
    /**
     * Shortcode to display diplomas
     */
    public function diplomer_shortcode($atts) {
        $atts = shortcode_atts(array(
            'per_page' => 10,
        ), $atts);
        
        $args = array(
            'post_type' => 'nsk_diplom',
            'posts_per_page' => $atts['per_page'],
            'post_status' => 'publish',
            'meta_key' => '_nsk_tildeling_dato',
            'orderby' => 'meta_value',
            'order' => 'DESC',
        );
        
        $diplomer = new WP_Query($args);
        
        ob_start();
        
        if (is_user_logged_in()) {
            echo '<div class="nsk-diplom-header">';
            echo '<a href="' . home_url('/legg-til-diplom/') . '" class="button nsk-add-diplom-btn">Legg til nytt diplom</a>';
            echo '</div>';
        }
        
        if ($diplomer->have_posts()) {
            echo '<div class="nsk-diplomer-grid">';
            
            while ($diplomer->have_posts()) {
                $diplomer->the_post();
                $this->display_diplom_card(get_the_ID());
            }
            
            echo '</div>';
        } else {
            echo '<p>Ingen diplomer funnet.</p>';
        }
        
        if (is_user_logged_in()) {
            echo '<div class="nsk-diplom-footer">';
            echo '<a href="' . home_url('/legg-til-diplom/') . '" class="button nsk-add-diplom-btn">Legg til nytt diplom</a>';
            echo '</div>';
        }
        
        wp_reset_postdata();
        
        return ob_get_clean();
    }
    
    /**
     * Display a single diploma card
     */
    private function display_diplom_card($post_id) {
        $tildeling_dato = get_post_meta($post_id, '_nsk_tildeling_dato', true);
        $diplom_bilde = get_post_meta($post_id, '_nsk_diplom_bilde', true);
        $lag_bilde = get_post_meta($post_id, '_nsk_lag_bilde', true);
        $forklarende_tekst = get_post_meta($post_id, '_nsk_forklarende_tekst', true);
        
        echo '<div class="nsk-diplom-card">';
        echo '<h3>' . get_the_title($post_id) . '</h3>';
        
        if ($tildeling_dato) {
            $formatted_date = date('d.m.Y', strtotime($tildeling_dato));
            echo '<p class="nsk-diplom-date"><strong>Dato:</strong> ' . $formatted_date . '</p>';
        }
        
        if ($diplom_bilde) {
            echo '<div class="nsk-diplom-image">';
            echo '<img src="' . wp_get_attachment_url($diplom_bilde) . '" alt="Diplom" />';
            echo '</div>';
        }
        
        if ($lag_bilde) {
            echo '<div class="nsk-lag-image">';
            echo '<img src="' . wp_get_attachment_url($lag_bilde) . '" alt="Stafettlag/Arrangørgjeng" />';
            echo '</div>';
        }
        
        if ($forklarende_tekst) {
            echo '<div class="nsk-diplom-description">';
            echo '<p>' . nl2br(esc_html($forklarende_tekst)) . '</p>';
            echo '</div>';
        }
        
        echo '</div>';
    }
    
    /**
     * Shortcode for the diploma submission form
     */
    public function diplom_form_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '<p>Du må være <a href="' . wp_login_url(get_permalink()) . '">logget inn</a> for å legge til diplomer.</p>';
        }
        
        ob_start();
        
        if (isset($_GET['success'])) {
            echo '<div class="nsk-success-message">Diplom lagt til!</div>';
        }
        
        ?>
        <form method="post" enctype="multipart/form-data" class="nsk-diplom-form">
            <?php wp_nonce_field('nsk_diplom_form', 'nsk_diplom_nonce'); ?>
            
            <div class="form-group">
                <label for="diplom_title">Tittel på diplom/pris:</label>
                <input type="text" id="diplom_title" name="diplom_title" required />
            </div>
            
            <div class="form-group">
                <label for="tildeling_dato">Dato for tildeling/arrangement:</label>
                <input type="date" id="tildeling_dato" name="tildeling_dato" required />
            </div>
            
            <div class="form-group">
                <label for="diplom_bilde">Last opp bilde av skannet diplom:</label>
                <input type="file" id="diplom_bilde" name="diplom_bilde" accept="image/*" />
            </div>
            
            <div class="form-group">
                <label for="lag_bilde">Last opp bilde av stafettlag/arrangørgjeng:</label>
                <input type="file" id="lag_bilde" name="lag_bilde" accept="image/*" />
            </div>
            
            <div class="form-group">
                <label for="forklarende_tekst">Forklarende tekst om prisen/arrangementet:</label>
                <textarea id="forklarende_tekst" name="forklarende_tekst" rows="5"></textarea>
            </div>
            
            <div class="form-group">
                <input type="submit" name="nsk_diplom_submit" value="Legg til diplom" class="button button-primary" />
                <a href="<?php echo home_url('/diplomer/'); ?>" class="button">Avbryt</a>
            </div>
        </form>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Create rewrite rules for custom URLs
     */
    public function create_rewrite_rules() {
        add_rewrite_rule('^diplomer/?$', 'index.php?diplomer_page=1', 'top');
        add_rewrite_rule('^legg-til-diplom/?$', 'index.php?diplom_form_page=1', 'top');
        
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_action('template_redirect', array($this, 'template_redirect'));
    }
    
    /**
     * Add query vars
     */
    public function add_query_vars($vars) {
        $vars[] = 'diplomer_page';
        $vars[] = 'diplom_form_page';
        return $vars;
    }
    
    /**
     * Handle template redirect
     */
    public function template_redirect() {
        if (get_query_var('diplomer_page')) {
            $this->load_diplomer_template();
        }
        
        if (get_query_var('diplom_form_page')) {
            $this->load_diplom_form_template();
        }
    }
    
    /**
     * Load diplomer template
     */
    private function load_diplomer_template() {
        get_header();
        echo '<div class="container">';
        echo '<h1>NSK Diplomer</h1>';
        echo do_shortcode('[nsk_diplomer]');
        echo '</div>';
        get_footer();
        exit;
    }
    
    /**
     * Load diplom form template
     */
    private function load_diplom_form_template() {
        get_header();
        echo '<div class="container">';
        echo '<h1>Legg til nytt diplom</h1>';
        echo do_shortcode('[nsk_diplom_form]');
        echo '</div>';
        get_footer();
        exit;
    }
      /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        wp_enqueue_style('nsk-diplom-style', NSK_DIPLOM_PLUGIN_URL . 'assets/style.css', array(), NSK_DIPLOM_VERSION);
        wp_enqueue_script('nsk-diplom-script', NSK_DIPLOM_PLUGIN_URL . 'assets/script.js', array('jquery'), NSK_DIPLOM_VERSION, true);
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts($hook) {
        global $post_type;
        
        // Only load on our post type edit screens
        if ($post_type === 'nsk_diplom' || $hook === 'post-new.php' || $hook === 'post.php') {
            wp_enqueue_media();
            wp_enqueue_script('nsk-diplom-admin', NSK_DIPLOM_PLUGIN_URL . 'assets/admin.js', array('jquery'), NSK_DIPLOM_VERSION, true);
        }
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        $this->register_post_type();
        add_option('nsk_diplom_flush_rewrite_rules', true);
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
}

// Initialize the plugin
new NSK_Diplom_Plugin();
