<?php
/**
 * Test_WP_Create_User class
 */
class Test_WP_Create_User extends WP_REST_Controller {
    /**
     * Initialize
     */
    public function __construct() {
        $this->namespace = 'test/v1';
        $this->rest_base = 'users';
    }

    /**
     * Register routes
     *
     * @return void
     */
    public function register_routes() {
        register_rest_route($this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'create_user'],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'manage_options' );
                },
            ],
        ]);
    }

    /**
     * Create user
     *
     * @param object $request
     * @return void
     */
    public function create_user($request) {
        $first_name = isset( $request['first_name'] ) ? sanitize_text_field( $request['first_name'] ) : '';
        $last_name  = isset( $request['last_name'] ) ? sanitize_text_field( $request['last_name'] ) : '';
        $email      = isset( $request['email'] ) ? sanitize_text_field( $request['email'] ) : '';
        $phone      = isset( $request['phone'] ) ? sanitize_text_field( $request['phone'] ) : '';
        $address    = isset( $request['address'] ) ? sanitize_text_field( $request['address'] ) : '';
        $city       = isset( $request['city'] ) ? sanitize_text_field( $request['city'] ) : '';
        $zip        = isset( $request['zip'] ) ? sanitize_text_field( $request['zip'] ) : '';

        $user_meta = [
            'first_name'    =>  $first_name,
            'last_name'     =>  $last_name,
            'phone'         =>  $phone,
            'address'       =>  $address,
            'city'          =>  $city,
            'zip'           =>  $zip,
        ];

        // Check email is empty
        if ( ! $email ) {
            return new WP_Error( 'test_user_empty_email', __( 'Email field can\'t be empty', 'test-wp-user' ) );
        }

        // Create user
        $created_user =  wp_create_user( $email, wp_generate_password( 8 ), $email );

        // Check error unable to create user
        if ( is_wp_error( $created_user ) ) {
            return $created_user;
        }

        // Add custom subscriber role for the newly created user
        $new_user = new WP_User( $created_user );
        $new_user->remove_role( 'subscriber' );
        $new_user->add_role( 'test_subscriber_role' );

        // Update user meta
        foreach( $user_meta as $meta_key => $meta_value ) {
            update_user_meta( $created_user, $meta_key, $meta_value );
        }

        $response = rest_ensure_response( $this->prepare_response( $created_user ) );

        return $response;
    }

    /**
     * Prepare response
     *
     * @param integer $user_id
     * @return array
     */
    public function prepare_response( $user_id ) {
        $user = get_userdata( $user_id );

        return [
            'ID'            =>  $user_id,
            'first_name'    =>  get_user_meta( $user_id, 'first_name', true ),
            'last_name'     =>  get_user_meta( $user_id, 'last_name', true ),
            'email'         =>  $user->user_email,
            'phone'         =>  get_user_meta( $user_id, 'phone', true ),
            'address'       =>  get_user_meta( $user_id, 'address', true ),
            'city'          =>  get_user_meta( $user_id, 'city', true ),
            'zip'           =>  get_user_meta( $user_id, 'zip', true )
        ];
    }
}