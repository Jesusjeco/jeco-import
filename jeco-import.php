<?php
/*
Plugin Name: Jeco import
Description: Connect with an external WordPress and pull the posts into yours.
Version: 1.0
Author: Jesus Carrero
Author URI: jesusjeco.com
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('JECO_IMPORT')) {

    /**
     * Class JECO_IMPORT
     *
     * This class handles the functionality of the Jeco import plugin.
     */
    class JECO_IMPORT
    {
        /**
         * Plugin version number.
         *
         * @var string
         */
        public $version = '1.0.0';

        /**
         * API URL from the external WordPress.
         *
         * @var string
         */
        private $api_url = 'https://blog.jesusjeco.com/wp-json/wp/v2/posts';

        /**
         * JECO_IMPORT constructor.
         *
         * Initialize hooks and constants.
         */
        public function __construct()
        {
            // Define constants for the plugin
            $this->define('JECO_IMPORT_VERSION', $this->version);
            $this->define('JECO_IMPORT_ROOT_URL', plugin_dir_url(__FILE__));
            $this->define('JECO_IMPORT_ROOT_PATH', plugin_dir_path(__FILE__));
            $this->define('JECO_IMPORT_INC_PATH', plugin_dir_path(__FILE__) . "inc/");

            $this->include_files();
            $this->init_hooks();
        }

        /**
         * Include necessary files for the plugin.
         *
         * @return void
         */
        public function include_files()
        {
            // Files to be included
        }

        /**
         * Initialize hooks for the plugin.
         *
         * @return void
         */
        public function init_hooks()
        {
            register_activation_hook(__FILE__, [$this, 'JECO_IMPORT_activation']);
            register_deactivation_hook(__FILE__, [$this, 'JECO_IMPORT_deactivation']);
            
            // Add admin menu
            add_action('admin_menu', [$this, 'admin_menu']);
        }

        /**
         * Activation function.
         *
         * @return void
         */
        public function JECO_IMPORT_activation()
        {
            // Any activation logic
        }

        /**
         * Deactivation function.
         *
         * @return void
         */
        public function JECO_IMPORT_deactivation() {}

        /**
         * Admin menu for running the import manually.
         */
        public function admin_menu()
        {
            add_menu_page(
                'Jeco Import', 
                'Jeco Import', 
                'manage_options', 
                'jeco-import', 
                [$this, 'import_posts_page'], 
                'dashicons-download', 
                25
            );
        }

        /**
         * Page for triggering the import.
         */
        public function import_posts_page()
        {
            require_once plugin_dir_path(__FILE__) . 'views/main.php';
        }

        /**
         * Import posts from the external API.
         *
         * @return void
         */
        public function import_posts()
        {
            // Fetch posts from the external API
            $response = wp_remote_get($this->api_url);
            if (is_wp_error($response)) {
                wp_die('Failed to retrieve posts from the external API.');
            }

            $posts = json_decode(wp_remote_retrieve_body($response), true);

            if (!empty($posts)) {
                foreach ($posts as $post_data) {
                    // Check if post already exists by external post_id
                    $existing_post = get_posts([
                        'meta_key' => 'external_post_id',
                        'meta_value' => $post_data['id'],
                        'post_type' => 'post',
                        'post_status' => 'any',
                        'numberposts' => 1,
                    ]);

                    if (empty($existing_post)) {
                        // Create new post
                        $post_id = wp_insert_post([
                            'post_title'   => $post_data['title']['rendered'],
                            'post_content' => $post_data['content']['rendered'],
                            'post_status'  => 'publish',
                            'post_type'    => 'post',
                            
                        ]);

                        // Store external post_id as post meta
                        if ($post_id) {
                            update_post_meta($post_id, 'external_post_id', $post_data['id']);
                        }
                    }
                }
                echo '<div class="notice notice-success"><p>Posts imported successfully!</p></div>';
            } else {
                echo '<div class="notice notice-warning"><p>No posts found to import.</p></div>';
            }
        }

        /**
         * Defines a constant if it is not already defined.
         *
         * @param string $name  The constant name.
         * @param mixed  $value The constant value.
         *
         * @return void
         */
        public function define($name, $value = true)
        {
            if (!defined($name)) {
                define($name, $value);
            }
        }
    }

    /**
     * Returns the one true instance of the JECO_IMPORT class.
     *
     * @return JECO_IMPORT
     */
    function JECO_IMPORT()
    {
        global $JECO_IMPORT;

        if (!isset($JECO_IMPORT)) {
            $JECO_IMPORT = new JECO_IMPORT();
        }

        return $JECO_IMPORT;
    }

    // Instantiate the plugin
    JECO_IMPORT();
}
