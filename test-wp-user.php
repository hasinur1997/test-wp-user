<?php 
/**
* Plugin Name:       Test WP User
* Plugin URI:        https://example.com/plugins/the-basics/
* Description:       A test plugin for creating a user by using custom endpoint.
* Version:           1.0
* Requires at least: 5.2
* Requires PHP:      7.2
* Author:            Hasinur Rahman
* Author URI:        https://author.example.com/
* License:           GPL v2 or later
* License URI:       https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain:       test-wp-user
* Domain Path:       /languages
*/

/**
 * Test_WP_User Class
 */
class Test_WP_User {
    /**
     * Initialize
     */
    public function __construct() {
        $this->includes();
        $this->init_hooks(); 
    }

    /**
     * Instantiate the class
     *
     * @return object
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Includes all required files
     *
     * @return void
     */
    public function includes() {
        require_once dirname( __FILE__ ) . '/includes/api/class-test-create-user.php';
    }

    /**
     * Intialize all required hooks
     *
     * @return void
     */
    public function init_hooks() {
        // Add custom role on plugin activation
        register_activation_hook( __FILE__, [ $this, 'create_custom_role' ] );

        // Register route
        add_action( 'rest_api_init', [ $this, 'register_controllers' ] );
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function register_controllers() {
        $user_controller = new Test_WP_Create_User();

        $user_controller->register_routes();
    }

    /**
     * Create custom role equivalent to subscriber
     *
     * @return void
     */
    public function create_custom_role() {
        add_role( 
            'test_subscriber_role', 
            __( 'Custom Subscriber', 'test-wp-user' ), 
            array( 'read' => true, 'level_0' => true ) 
        );
    }
}

// Kick Off the plugin
Test_WP_User::init();