<?php

class BS_Day_Admin
{

	public function __construct()
	{
		// Post Type and Fields
		if ( ! class_exists('CMB_Meta_Box'))
			require_once( plugin_dir_path( __FILE__ ) . '/Custom-Meta-Boxes/custom-meta-boxes.php' );

        add_action( 'init', array( $this, 'post_type_setup' ) );
        add_filter( 'cmb_meta_boxes', array( $this, 'field_setup' ) );
        add_action( 'p2p_init', array( $this, 'relationship_setup' ) );

	}

	public function post_type_setup()
	{
		$labels = array(
		    'name'               => 'Day',
		    'singular_name'      => 'Day',
		    'add_new'            => 'Add New',
		    'add_new_item'       => 'Add New Day',
		    'edit_item'          => 'Edit Day',
		    'new_item'           => 'New Day',
		    'all_items'          => 'All Days',
		    'view_item'          => 'View Day',
		    'search_items'       => 'Search Days',
		    'not_found'          => 'No day found',
		    'not_found_in_trash' => 'No days found in Trash',
		    'parent_item_colon'  => '',
		    'menu_name'          => 'Day'
		);

		$args = array(
		    'labels'             => $labels,
		    'public'             => true,
		    'publicly_queryable' => true,
		    'show_ui'            => false,
		    'show_in_menu'       => false,
		    'query_var'          => true,
		    'rewrite'            => array( 'slug' => 'day' ),
		    'capability_type'    => 'post',
		    'has_archive'        => false,
		    'hierarchical'       => true,
		    'menu_position'      => null,
            //'show_in_menu'       =>'edit.php?post_type=accommodation',
		    'supports'           => array( 'title', 'editor', 'thumbnail' )
		);

		register_post_type( 'day', $args );
	}

    function relationship_setup()
    {
        //include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        //if ( is_plugin_active('posts-to-posts/posts-to-posts.php') ) {
            p2p_register_connection_type( array(
                'name' => 'day_to_activity',
                'from' => 'day',
                'to' => 'activity',
                'cardinality' => 'one-to-many',
                'can_create_post'=> false,
                'admin_dropdown' => false,
            ) );

    }

    function field_setup( array $meta_boxes )
	{
		$prefix = 'bs_day_';
		$fields = array(
                array(
                'id' => $prefix . 'day',
                'name' => 'Day:',
                'type' => 'text',
                'default'=>'0'
                ),
                array(
                'id' => $prefix . 'room_basis',
                'name' => 'Room basis:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'drinks_basis',
                'name' => 'Drinks basis:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'consultant_notes',
                'name' => 'Consultant notes:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'included',
                'name' => 'Included:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'excluded',
                'name' => 'Excluded:',
                'type' => 'text'
                )
		);

		$meta_boxes[] = array(
			'title' => 'Day Details',
			'pages' => 'day',
			'fields' => $fields
		);

		return $meta_boxes;
    }
}

$BS_Day_Admin = new BS_Day_Admin();