<?php

class BS_Tour_Admin
{

	public function __construct()
	{
		// Post Type and Fields
		if ( ! class_exists('CMB_Meta_Box'))
			require_once( plugin_dir_path( __FILE__ ) . '/Custom-Meta-Boxes/custom-meta-boxes.php' );

        add_action( 'init', array( $this, 'post_type_setup' ) );
	    add_filter( 'cmb_meta_boxes', array( $this, 'field_setup' ) );
        //add_action( 'init', array( $this, 'taxonomy_setup' ), 9 );
        add_action( 'add_meta_boxes_tour', array( $this,'add_itinerary_child') );

        //add_action( 'admin_menu', array($this,'register_custom_tour_list') );
	}

	public function post_type_setup()
	{
		$labels = array(
		    'name'               => 'Tour',
		    'singular_name'      => 'Tour',
		    'add_new'            => 'Add New',
		    'add_new_item'       => 'Add New Tour',
		    'edit_item'          => 'Edit Tour',
		    'new_item'           => 'New Tour',
		    'all_items'          => 'All Tours',
		    'view_item'          => 'View Tour',
		    'search_items'       => 'Search Tours',
		    'not_found'          => 'No tour found',
		    'not_found_in_trash' => 'No tours found in Trash',
		    'parent_item_colon'  => '',
		    'menu_name'          => 'Tour'
		);

		$args = array(
            'menu_icon'          =>'dashicons-admin-site',
		    'labels'             => $labels,
		    'public'             => true,
		    'publicly_queryable' => true,
		    'show_ui'            => true,
		    'show_in_menu'       => true,
            //'show_in_menu'       =>'edit.php?post_type=tour',
		    'query_var'          => true,
		    'rewrite'            => array( 'slug' => 'tour' ),
		    'capability_type'    => 'post',
		    'has_archive'        => true,
		    'hierarchical'       => false,
		    'menu_position'      => null,
		    //'supports'           => array( 'title', 'editor', 'thumbnail' ),
            'supports'           => array( 'title', 'editor')
		);

		register_post_type( 'tour', $args );
	}

    function field_setup( array $meta_boxes )
	{
		$prefix = 'bs_tour_';
		$fields = array(
                array(
                'id' => $prefix . 'type',
                'name' => 'Type:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'identifier',
                'name' => 'Identifier:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'identifier_key',
                'name' => 'Identifier key:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'days',
                'name' => 'Days:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'reference_number',
                'name' => 'Reference number:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'client_name',
                'name' => 'Client name:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'last_modified',
                'name' => 'Last modified:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'access_count',
                'name' => 'Access count:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'is_disabled',
                'name' => 'Is disabled:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'booking_status',
                'name' => 'Booking status:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'itinerary_id',
                'name' => 'Itinerary id:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'operator_id',
                'name' => 'Operator id:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'operator_user_id',
                'name' => 'Operator user id:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'operator_identity_id',
                'name' => 'Operator identity id:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'operator_theme_id',
                'name' => 'Operator theme id:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'bound_lat1',
                'name' => 'Bound lat1:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'bound_lat2',
                'name' => 'Bound lat2:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'bound_lng1',
                'name' => 'Bound lng1:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'bound_lng2',
                'name' => 'Bound lng2:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'hide_route_information',
                'name' => 'Hide route information:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'hide_guide',
                'name' => 'Hide guide:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'show_itinerary_only',
                'name' => 'Show itinerary only:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'price',
                'name' => 'Price:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'currency',
                'name' => 'Currency:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'price_includes',
                'name' => 'Price includes:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'price_excludes',
                'name' => 'Price excludes:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'enquiry_prompt_delay',
                'name' => 'Enquiry prompt delay:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'travellers_adult',
                'name' => 'Travellers adult:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'travellers_children',
                'name' => 'Travellers children:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'rooms_single',
                'name' => 'Rooms single:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'rooms_double',
                'name' => 'Rooms double:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'rooms_twin',
                'name' => 'Rooms twin:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'rooms_triple',
                'name' => 'Rooms triple:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'rooms_family',
                'name' => 'Rooms family:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'start_travel_days',
                'name' => 'Start travel days:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'end_travel_days',
                'name' => 'End travel days:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'hide_enquiry',
                'name' => 'Hide enquiry:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'departures',
                'name' => 'Departures:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'language',
                'name' => 'Language:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'notification_frequency',
                'name' => 'Notification frequency:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'notification_email',
                'name' => 'Notification email:',
                'type' => 'text'
                ),
		);

        $contact_fields=array(
            array(
				'id' => $prefix.'contacts',
				'name' => 'Contacts',
				'type' => 'group',
				'repeatable' => true,
				'fields' => array(
                    array(
                    'id' => 'company',
                    'name' => 'Company:',
                    'type' => 'text'
                    ),
                    array(
                    'id' => 'telephone',
                    'name' => 'Telephone:',
                    'type' => 'text'
                    ),
                    array(
                    'id' => 'contact_person',
                    'name' => 'Contact person:',
                    'type' => 'text'
                    ),
                    array(
                    'id' => 'email',
                    'name' => 'Email:',
                    'type' => 'text'
                    ),
                )
            ),
        );

        $document_fields=array(
            array(
				'id' => $prefix . 'documents',
				'name' => 'Documents',
				'type' => 'group',
				'repeatable' => true,
				'fields' => array(
                    array(
                    'id' => 'itinerary_id',
                    'name' => 'Itinerary id:',
                    'type' => 'text'
                    ),
                    array(
                    'id' => 'itinerary_ducument_id',
                    'name' => 'Itinerary ducument id:',
                    'type' => 'text'
                    ),
                    array(
                    'id' => 'name',
                    'name' => 'Name:',
                    'type' => 'text'
                    ),
                    array(
                    'id' => 'hidden',
                    'name' => 'Hidden:',
                    'type' => 'text'
                    ),
                    array(
                    'id' => 'element',
                    'name' => 'Element:',
                    'type' => 'text'
                    ),
                )
            ),
        );

        $route_fields=array(
            array(
				'id' => $prefix . 'routes',
				'name' => 'Routes:',
				'type' => 'group',
				'repeatable' => true,
				'fields' => array(

                    array(
                    'id' => 'mode',
                    'name' => 'Mode:',
                    'type' => 'text'
                    ),
                    array(
                    'id' => 'start_leg_id',
                    'name' => 'Start leg id:',
                    'type' => 'text',
                    'default'=>'0'
                    ),
                    array(
                    'id' => 'end_leg_id',
                    'name' => 'End leg id:',
                    'type' => 'text'
                    ),
                    array(
                    'id' => 'start_label',
                    'name' => 'Start label:',
                    'type' => 'text'
                    ),
                    array(
                    'id' => 'start_content_entity_id',
                    'name' => 'Start content entity id:',
                    'type' => 'text'
                    ),
                    array(
                    'id' => 'end_label',
                    'name' => 'End label:',
                    'type' => 'text'
                    ),
                    array(
                    'id' => 'end_content_entity_id',
                    'name' => 'End content entity id:',
                    'type' => 'text'
                    ),
                    array(
                    'id' => 'sequence',
                    'name' => 'Sequence:',
                    'type' => 'text'
                    ),
                    array(
                    'id' => 'agency',
                    'name' => 'Agency:',
                    'type' => 'text'
                    ),
                    array(
                    'id' => 'vehicle',
                    'name' => 'Vehicle:',
                    'type' => 'text'
                    ),
                    array(
                    'id' => 'reference_codes',
                    'name' => 'Reference codes:',
                    'type' => 'text'
                    ),
                    array(
                    'id' => 'notes',
                    'name' => 'Notes:',
                    'type' => 'text'
                    ),
                    array(
                    'id' => 'start_time',
                    'name' => 'Start time:',
                    'type' => 'text'
                    ),
                    array(
                    'id' => 'end_time',
                    'name' => 'End time:',
                    'type' => 'text'
                    ),
                    array(
                    'id' => 'points',
                    'name' => 'Points:',
                    'type' => 'text'
                    ),
                    array(
                    'id' => 'via_points',
                    'name' => 'Via points:',
                    'type' => 'text'
                    ),
                    array(
                    'id' => 'custom_directions',
                    'name' => 'Custom directions:',
                    'type' => 'text'
                    ),
                    array(
                    'id' => 'directions',
                    'name' => 'Directions:',
                    'type' => 'text'
                    ),
                    array(
                    'id' => 'included',
                    'name' => 'Included:',
                    'type' => 'text'
                    ),
                    array(
                    'id' => 'excluded',
                    'name' => 'Excluded:',
                    'type' => 'text'
                    ),
                    array(
                    'id' => 'contact_numbers',
                    'name' => 'Contact numbers:',
                    'type' => 'text'
                    ),
                    array(
                    'id' => 'start_address',
                    'name' => 'Start address:',
                    'type' => 'text'
                    ),
                    array(
                    'id' => 'end_address',
                    'name' => 'End address:',
                    'type' => 'text'
                    ),
                    array(
                    'id' => 'start_terminal',
                    'name' => 'Start terminal:',
                    'type' => 'text'
                    ),
                    array(
                    'id' => 'end_terminal',
                    'name' => 'End terminal:',
                    'type' => 'text'
                    ),
                    array(
                    'id' => 'ticket_class',
                    'name' => 'Ticket class:',
                    'type' => 'text'
                    ),
                    array(
                    'id' => 'check_in_time',
                    'name' => 'Check in time:',
                    'type' => 'text'
                    ),
                    array(
                    'id' => 'type',
                    'name' => 'Type:',
                    'type' => 'text'
                    ),
				)
			),
        );
		$meta_boxes[] = array(
			'title' => 'Tour Details',
			'pages' => 'tour',
			'fields' => $fields
		);

        $meta_boxes[] = array(
			'title' => 'Contacts',
			'pages' => 'tour',
			'fields' => $contact_fields
		);

        $meta_boxes[] = array(
			'title' => 'Documents',
			'pages' => 'tour',
			'fields' => $document_fields
		);

        $meta_boxes[] = array(
			'title' => 'Routes',
			'pages' => 'tour',
			'fields' => $route_fields
		);

		return $meta_boxes;
    }

    public function taxonomy_setup()
	{

    }

    function register_custom_tour_list()
    {
        //add_menu_page( 'Tour', 'Tour', 'manage_options', 'tour', array(), 'dashicons-admin-post', '2.1' );
        add_menu_page( 'Tour', 'Tour', 'manage_options', 'tour', array($this,'custom_tour_page'), 'dashicons-admin-post', '2.1' );
    }

    function custom_tour_page()
    {
        return false;
    }

    function add_itinerary_child( $post ) {
        add_meta_box(
            'itinerary_child',
            'Itinerary',
            array($this,'itinerary_child_meta_box'),
            $post->post_type,
            'side',
            'high'
        );
    }

    // Displays the meta box
    function itinerary_child_meta_box( $post ) {

        $post_id=get_the_ID();

        global $wpdb;
        $sql='select ID,post_title from '.$wpdb->posts.'
        where post_parent = "'.$post_id.'" and post_type="itinerary"
        order by post_title
        ';

        $children = $wpdb->get_results( $sql );
        if(!empty($children))
        {
            foreach($children as $child)
            {
                echo('<a href="post.php?post='.$child->ID.'&action=edit">'.$child->post_title.'</a><br>');
            }
        }
        else{
            echo('Nothing found');
        }
    }

}

$BS_Tour_Admin = new BS_Tour_Admin();