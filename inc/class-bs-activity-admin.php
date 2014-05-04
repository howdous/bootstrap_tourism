<?php

class BS_Activity_Admin
{

	public function __construct()
	{
		// Post Type and Fields
		if ( ! class_exists('CMB_Meta_Box'))
			require_once( plugin_dir_path( __FILE__ ) . '/Custom-Meta-Boxes/custom-meta-boxes.php' );

        add_action( 'init', array( $this, 'post_type_setup' ) );

        add_filter( 'cmb_meta_boxes', array( $this, 'field_setup' ) );

	}

	public function post_type_setup()
	{
		$labels = array(
		    'name'               => 'Activity',
		    'singular_name'      => 'Activity',
		    'add_new'            => 'Add New',
		    'add_new_item'       => 'Add New Activity',
		    'edit_item'          => 'Edit Activity',
		    'new_item'           => 'New Activity',
		    'all_items'          => 'All Activities',
		    'view_item'          => 'View Activity',
		    'search_items'       => 'Search Activities',
		    'not_found'          => 'No activity found',
		    'not_found_in_trash' => 'No activityies found in Trash',
		    'parent_item_colon'  => '',
		    'menu_name'          => 'Activity'
		);

		$args = array(
            'menu_icon'          =>'dashicons-universal-access-alt',
		    'labels'             => $labels,
		    'public'             => true,
		    'publicly_queryable' => true,
		    'show_ui'            => true,
		    'show_in_menu'       => true,
		    'query_var'          => true,
		    'rewrite'            => array( 'slug' => 'activity' ),
		    'capability_type'    => 'post',
		    'has_archive'        => true,
		    'hierarchical'       => false,
		    'menu_position'      => null,
            //'show_in_menu'       =>'edit.php?post_type=accommodation',
		    'supports'           => array( 'title', 'editor', 'thumbnail' )
		);

		register_post_type( 'activity', $args );
	}

    function field_setup( array $meta_boxes )
	{
		$prefix = 'bs_activity_';
		$fields = array(
            array(
            'id' => $prefix . 'duration',
            'name' => 'Duration',
            'type' => 'text'
            ),
            array(
            'id' => $prefix . 'start_time',
            'name' => 'Start Time',
            'type' => 'text'
            ),
            array(
            'id' => $prefix . 'end_time',
            'name' => 'End Time',
            'type' => 'text'
            ),
            array(
            'id' => $prefix . 'time_slot',
            'name' => 'Time Slot',
            'type' => 'text'
            ),
            array(
            'id' => $prefix . 'sequence',
            'name' => 'Sequence',
            'type' => 'text'
            ),
            array(
            'id' => $prefix . 'content_entity_id',
            'name' => 'Content ID',
            'type' => 'text'
            ),
            array(
            'id' => $prefix . 'name',
            'name' => 'Name',
            'type' => 'text'
            ),
            array(
            'id' => $prefix . 'is_highlight',
            'name' => 'Is Highlight',
            'type' => 'text'
            ),
            array(
            'id' => $prefix . 'type',
            'name' => 'Type',
            'type' => 'text'
            ),
            array(
            'id' => $prefix . 'reference',
            'name' => 'Reference',
            'type' => 'text'
            ),
            array(
            'id' => $prefix . 'prevent_voucher',
            'name' => 'Prevent Voucher',
            'type' => 'text'
            ),

		);

		$meta_boxes[] = array(
			'title' => 'Activity Details',
			'pages' => 'activity',
			'fields' => $fields
		);

		return $meta_boxes;
    }
}

$BS_Activity_Admin = new BS_Activity_Admin();