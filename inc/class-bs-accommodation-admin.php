<?php

class BS_Accommodation_Admin {

	public function __construct()
	{

		// Post Type and Fields
		if ( ! class_exists('CMB_Meta_Box'))
        {
			require_once( plugin_dir_path( __FILE__ ) . '/Custom-Meta-Boxes/custom-meta-boxes.php' );
        }

	    add_action( 'init', array( $this, 'post_type_setup' ) );
	    add_filter( 'cmb_meta_boxes', array( $this, 'field_setup' ) );

        //add_action( 'admin_menu', array($this,'register_custom_accommodation_list') );

        add_action( 'add_meta_boxes_accommodation', array( $this,'add_room_child') );
        add_action( 'add_meta_boxes_accommodation', array( $this,'add_custom_location') );


	    add_action( 'init', array( $this, 'taxonomy_setup' ), 9 );
        //add_action( 'init', array( $this, 'relationship_setup' ) );
        //add_action( 'add_meta_boxes', array( $this, 'register_accomtour_meta_box' ) );
        //add_action( 'add_meta_boxes', array( $this, 'register_itinerary_meta_box' ) );
	}

    function relationship_setup()
    {
            p2p_register_connection_type( array(
                'name' => 'itinerary_to_accommodation',
                'from' => 'itinerary',
                'to' => 'accommodation',
                'cardinality' => 'many-to-one',
                //'admin_box' => array('show'=>false),
            ) );
    }

    function register_accomtour_meta_box()
    {
        add_meta_box( 'accomtour-box', 'Tour', array($this,'render_accomtour_meta_box'), 'accommodation','side','high' );
    }

    function render_accomtour_meta_box( $post )
    {
        $connected = p2p_type( 'itinerary_to_accommodation' )->get_connected( $post );
    ?>
        <ul>
        <?php while ( $connected->have_posts() ) : $connected->the_post(); ?>
            <li><a href="post.php?post=<?php the_ID(); ?>&action=edit"><?php the_title(); ?></a></li>
        <?php endwhile; ?>
        </ul>
        <?php
    }

    function register_itinerary_meta_box()
    {
        add_meta_box( 'tour-box2', 'Tour', array($this,'render_tour_meta_box'), 'accommodation','side','high' );
    }

    function render_itinerary_meta_box( $post )
    {
        $connected = p2p_type( 'itinerary_to_accommodation' )->get_connected( $post );
    ?>
        <ul>
        <?php while ($connected->have_posts() ) : $connected->the_post(); ?>
            <li>--<a href="post.php?post=<?php echo($connected['post']->ID); ?>&action=edit"><?php var_dump($connected['post']);echo($connected['post']->post_title); ?></a></li>
        <?php endwhile; ?>
        </ul>
        <?php
    }

    //Hook to add child room meta box
    function add_room_child( $post ) {
        add_meta_box(
            'room_child',
            'Rooms / Units',
            array($this,'room_child_meta_box'),
            $post->post_type,
            'side',
            'high'
        );
    }

    // Displays the meta box
    function room_child_meta_box( $post ) {

        $post_id=get_the_ID();

        global $wpdb;
        $sql='select ID,post_title from '.$wpdb->posts.'
        where post_parent = "'.$post_id.'" and post_type="room"
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

    function add_custom_location( $post ) {

        add_meta_box(
            'custom_location',
            'Location',
            array($this,'custom_location_meta_box'),
            $post->post_type,
            'side',
            'high'
        );
    }

    function custom_location_meta_box($post)
    {
        $location = $this->get_the_term_list($post->ID, 'location', '<br>',FALSE);
        if($location)
        {
            echo($location);
        }
        else{
            echo('Nothing Found');
        }
    }

    function register_custom_accommodation_list()
    {
        add_menu_page( 'Accommodation', 'Accommodation', 'manage_options', 'accom', array($this,'custom_accommodation_page'), 'dashicons-admin-post', '2.2' );
    }

    function custom_accommodation_page()
    {
        $sel_state='';
        $all=false;

        if (isset($_GET['alpha']))
        {
            if($_GET['alpha']=='all')
            {
                $all=true;
            }
            else
            {
                $sel_state = ' AND parent.post_title like "'.$_GET['alpha'].'%"';//get_state_by_id($_GET['opt']);

            }
            echo '<a href="admin.php?page=accom ">#</a> ';
            $has=true;

        }
        else
        {
            echo ' <b>#</b> ';
            $sel_state = ' AND parent.post_title NOT REGEXP "^[[:alpha:]]"';
            $has=false;
        }

        $range=range('a', 'z');
        $range[]='all';

        foreach ($range as $char)
        {
            if($has && $_GET['alpha']==$char)
            {
                echo'| <b>'.$char.'</b>';
            }
            else
            {
                echo '| <a href="admin.php?page=accom&alpha=' . $char . '">' . $char . '</a> ';
            }
        }

        global $wpdb;

        if($all)
        {
            $sql='select
                    parent.ID,
                    parent.post_title as accom
                    from wp_posts parent
                    where parent.post_type="accommodation"
                    order by accom
                    ';
        }
        else
        {

            $sql='select
                    parent.ID,
                    parent.post_title as accom,
                    GROUP_CONCAT(DISTINCT child.ID,"<<>>",child.post_title ORDER BY child.post_title SEPARATOR "::::") as rooms
                  from wp_posts parent
                    left join wp_posts child on (parent.ID=child.post_parent and parent.post_parent = 0)
                  Where parent.post_type="accommodation" and child.post_type="room" '.$sel_state.'
                  GROUP BY
                    parent.ID,
                    parent.post_title
                  union
                  select parent.ID,parent.post_title as accom,NULL
                  from wp_posts parent
                  where parent.ID not in (select post_parent from wp_posts where post_type="room")
                  and parent.post_type="accommodation" '.$sel_state.'
                  order by accom';
        }
        //$sql="SELECT ID,post_title FROM $wpdb->posts where post_type='accommodation' order by post_title asc";
        $posts = $wpdb->get_results($sql);
        $i = 0;

        echo '<table width="100%"><tr>';
        foreach ($posts as $post)
        {
            echo '<td valign="top"><b><a href="post.php?post='.$post->ID.'&action=edit">'.$post->accom.'</a></b>';



            if($post->rooms)
            {
                $rooms=explode("::::",$post->rooms);
                foreach($rooms as $k=>$v)
                {
                    $details=explode("<<>>",$v);
                    if(empty($details[1]))
                    {
                        $details[1]='aaarghh';//why an empty name???
                    }
                    echo('<br>&nbsp;&nbsp;&nbsp;<a href="post.php?post='.$details[0].'&action=edit">'.$details[1].'</a>');
                }
            }

            echo('</td>');

            if ($i++ == 2)
            {
                echo '</tr><tr>';
                $i=0;
            }
        }
        echo '</tr></table>';
    }

	public function post_type_setup()
	{
		$labels = array(
		    'name'               => 'Accommodation',
		    'singular_name'      => 'Accommodation',
		    'add_new'            => 'Add New',
		    'add_new_item'       => 'Add New Accommodation',
		    'edit_item'          => 'Edit Accommodation',
		    'new_item'           => 'New Accommodation',
		    'all_items'          => 'All Accommodation',
		    'view_item'          => 'View Accommodation',
		    'search_items'       => 'Search Accommodation',
		    'not_found'          => 'No accommodation found',
		    'not_found_in_trash' => 'No accommodation found in Trash',
		    'parent_item_colon'  => '',
		    'menu_name'          => 'Accommodation'
		);

		$args = array(
            'menu_icon'          =>'dashicons-post-status',
		    'labels'             => $labels,
		    'public'             => true,
		    'publicly_queryable' => true,
		    'show_ui'            => true,
		    'show_in_menu'       => true,
		    'query_var'          => true,
		    'rewrite'            => array( 'slug' => 'accommodation' ),
		    'capability_type'    => 'post',
		    'has_archive'        => true,
		    'hierarchical'       => false,
		    //'menu_position'      => '5.21',
		    'supports'           => array( 'title', 'editor', 'custom-fields' )
		);

		register_post_type( 'accommodation', $args );
	}

	function field_setup( array $meta_boxes )
	{

		$prefix = 'bs_accommodation_';

        $contact_details_fields = array(
                array(
				'id' => $prefix . 'contact_email',
				'name' => 'Email:',
				'type' => 'text'
				),
                array(
				'id' => $prefix . 'contact_telephone',
				'name' => 'Telephone:',
				'type' => 'text'
				),
                array(
				'id' => $prefix . 'contact_frontdesk_telephone',
				'name' => 'Front Desk URL',
				'type' => 'text'
                ),
                array(
				'id' => $prefix . 'contact_address',
				'name' => 'Address:',
				'type' => 'textarea'
				),
                array(
				'id' => $prefix . 'contact_skype',
				'name' => 'Skype:',
				'type' => 'text'
				),
                array(
				'id' => $prefix . 'contact_twitter',
				'name' => 'Twitter:',
				'type' => 'text'
				),
                array(
				'id' => $prefix . 'contact_facebook',
				'name' => 'Facebook:',
				'type' => 'text'
                ),
                array(
				'id' => $prefix . 'contact_web_url',
				'name' => 'Web URL:',
				'type' => 'text'),
                array(
				'id' => $prefix . 'contact_bookings_url',
				'name' => 'Booking URL:',
				'type' => 'text'
                ),
                array(
				'id' => $prefix . 'contact_mobilebookings_url',
				'name' => 'Mobile Booking URL:',
				'type' => 'text'
                ),

        );

        $location_details_fields = array(
                array(
				'id' => $prefix . 'map_object_id',
				'name' => 'WETU Map Object ID:',
				'type' => 'text'
				),
                array(
				'id' => $prefix . 'latitude',
				'name' => 'Latitude:',
				'type' => 'text'
				),
                array(
				'id' => $prefix . 'longitude',
				'name' => 'Longitude:',
				'type' => 'text'
				),
                array(
				'id' => $prefix . 'driving_latitude',
				'name' => 'Driving Latitude:',
				'type' => 'text'
				),
                array(
				'id' => $prefix . 'driving_longitude',
				'name' => 'Driving Longitude:',
				'type' => 'text'
				),
        );

        $wetu_details_fields = array(
                array(
				'id' => $prefix . 'last_wetu_update',
				'name' => 'Last Update:',
				'type' => 'date'
				),
                array(
				'id' => $prefix . 'affiliate_name',
				'name' => 'Affiliate Name:',
				'type' => 'text'
				),
                array(
				'id' => $prefix . 'affiliate_code',
				'name' => 'Affiliate Code:',
				'type' => 'text'
				),
                array(
				'id' => $prefix . 'status',
				'name' => 'Status:',
				'type' => 'text'
				),
                array(
				'id' => $prefix . 'category',
				'name' => 'Category:',
				'type' => 'text'
				),
                array(
				'id' => $prefix . 'type',
				'name' => 'Type:',
				'type' => 'text'
				),

        );

        $fields = array(
            array(
				'id' => $prefix . 'extended_description',
				'name' => 'Extended Description:',
				'type' => 'textarea'
				),
                array(
				'id' => $prefix . 'teaser_description',
				'name' => 'Teaser:',
				'type' => 'text'
				),
            array(
				'id' => $prefix . 'star_authority',
				'name' => 'Star Authority:',
				'type' => 'text'
				),
            array(
				'id' => $prefix . 'stars',
				'name' => 'Stars:',
				'type' => 'text'
				),
            array(
				'id' => $prefix . 'check_in_time',
				'name' => 'Check In Time:',
				'type' => 'time'
				),
            array(
				'id' => $prefix . 'check_out_time',
				'name' => 'Check Out Time:',
				'type' => 'time'
				),
            array(
				'id' => $prefix . 'rooms_number',
				'name' => 'Number of Rooms:',
				'type' => 'text'
				),
			array(
				'id' => $prefix . 'featured',
				'name' => 'Featured:',
				'type'    => 'radio',
				'options' => array(
				    '1' => 'Yes',
				    '0' => 'No'
				),
				'default' => '0'
				),

			array(
				'id' => $prefix . 'banner_image',
				'name' => 'Banner Image:',
				'type' => 'image'
				),


			array(
				'id' => $prefix . 'activities',
				'name' => 'Activities:',
				'type' => 'group',
				'repeatable' => true,
				'fields' => array(
                    array(
                        'id' => 'id',
                        'name' => 'Id:',
                        'type' => 'text'
                        ),
                    array(
                        'id' => 'name',
                        'name' => 'Name:',
                        'type' => 'text'
                        ),
                    array(
                        'id' => 'description',
                        'name' => 'Description:',
                        'type' => 'textarea'
                        ),
					)
				),

			array(
				'id' => $prefix . 'specials',
				'name' => 'Specials:',
				'type' => 'group',
				'repeatable' => true,
				'fields' => array(
						array(
							'id' => 'name',
							'name' => 'Name:',
							'type' => 'text'
							),
						array(
							'id' => 'type',
							'name' => 'Type:',
							'type' => 'text'
							),
						array(
							'id' => 'description',
							'name' => 'Description:',
							'type' => 'textarea'
							),
                        array(
							'id' => 'conditions',
							'name' => 'Conditions:',
							'type' => 'textarea'
							),
						array(
							'id' => 'start',
							'name' => 'Start Date:',
							'type' => 'date'
							),
						array(
							'id' => 'end',
							'name' => 'End Date:',
							'type' => 'date'
							),
					)
				),
		);

        $youtube_details = array(
            array(
				'id' => $prefix . 'youtube',
				'name' => 'Youtube Videos:',
				'type' => 'group',
				'repeatable' => true,
				'fields' => array(
                    array(
                        'id' => 'label',
                        'name' => 'Label',
                        'type' => 'text'
                    ),
                    array(
                        'id' => 'description',
                        'name' => 'Description:',
                        'type' => 'text'
                    ),
                    array(
                        'id' => 'url',
                        'name' => 'URL:',
                        'type' => 'text'
                    ),
                )
            ),
        );

        $image_details= array(
			array(
				'id' => $prefix . 'images',
				'name' => 'Image:',
				'type' => 'image',
				'repeatable' => true,
			),
        );

        $document_details= array(
			array(
				'id' => $prefix . 'documents',
				'name' => 'Document:',
				'type' => 'file',
				'repeatable' => true,
			),
        );

        $meta_boxes[] = array(
			'title' => 'Wetu Details',
			'pages' => 'accommodation',
			'fields' => $wetu_details_fields
		);

		$meta_boxes[] = array(
			'title' => 'Accommodation Details',
			'pages' => 'accommodation',
			'fields' => $fields
		);

        $meta_boxes[] = array(
			'title' => 'Contact Details',
			'pages' => 'accommodation',
			'fields' => $contact_details_fields
		);

        $meta_boxes[] = array(
			'title' => 'Location Details',
			'pages' => 'accommodation',
			'fields' => $location_details_fields
		);

        $meta_boxes[] = array(
			'title' => 'Images',
			'pages' => 'accommodation',
			'fields' => $image_details
		);

        $meta_boxes[] = array(
			'title' => 'Documents',
			'pages' => 'accommodation',
			'fields' => $document_details
		);

        $meta_boxes[] = array(
			'title' => 'Youtube Videos',
			'pages' => 'accommodation',
			'fields' => $youtube_details
		);

		return $meta_boxes;
	}


    public function taxonomy_setup()
	{

        $labels = array(
			'name'              => _x( 'Locations', 'taxonomy general name' ),
			'singular_name'     => _x( 'Location', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Locations' ),
			'all_items'         => __( 'All Locations' ),
			'parent_item'       => __( 'Parent Location' ),
			'parent_item_colon' => __( 'Parent Location:' ),
			'edit_item'         => __( 'Edit Location' ),
			'update_item'       => __( 'Update Location' ),
			'add_new_item'      => __( 'Add New Location' ),
			'new_item_name'     => __( 'New Location Name' ),
			'menu_name'         => __( 'Locations' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => false,
			'show_admin_column' => false,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'location' ),
		);

		register_taxonomy( 'location', array('accommodation','destination'), $args );

		$labels = array(
			'name'              => _x( 'Travel Styles', 'taxonomy general name' ),
			'singular_name'     => _x( 'Travel Style', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Travel Styles' ),
			'all_items'         => __( 'All Travel Styles' ),
			'parent_item'       => __( null ),
			'parent_item_colon' => __( null ),
			'edit_item'         => __( 'Edit Travel Style' ),
			'update_item'       => __( 'Update Travel Style' ),
			'add_new_item'      => __( 'Add New Travel Style' ),
			'new_item_name'     => __( 'New Travel Style Name' ),
			'menu_name'         => __( 'Travel Styles' ),
		);

		$args = array(
			'hierarchical'      => false,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => false,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'travel-style' ),
		);

		register_taxonomy( 'travel-style', array( 'accommodation','tour'), $args );

        $labels = array(
			'name'              => _x( 'Special Interest', 'taxonomy general name' ),
			'singular_name'     => _x( 'Special Interest', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Special Interests' ),
			'all_items'         => __( 'All Special Interests' ),
			'parent_item'       => __( 'Parent Special Interest' ),
			'parent_item_colon' => __( 'Parent Special Interest:' ),
			'edit_item'         => __( 'Edit Special Interest' ),
			'update_item'       => __( 'Update Special Interest' ),
			'add_new_item'      => __( 'Add New Special Interest' ),
			'new_item_name'     => __( 'New Special Interest Name' ),
			'menu_name'         => __( 'Special Interests' ),
		);

		$args = array(
			'hierarchical'      => false,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => false,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'special-interest' ),
		);

		register_taxonomy( 'special-interest', array('accommodation'), $args );

        $labels = array(
			'name'              => _x( 'Spoken Languages', 'taxonomy general name' ),
			'singular_name'     => _x( 'Spoken Language', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Spoken Languages' ),
			'all_items'         => __( 'All Spoken Languages' ),
			'parent_item'       => __( 'Parent Spoken Language' ),
			'parent_item_colon' => __( 'Parent Spoken Language:' ),
			'edit_item'         => __( 'Edit Spoken Language' ),
			'update_item'       => __( 'Update Spoken Language' ),
			'add_new_item'      => __( 'Add New Spoken Language' ),
			'new_item_name'     => __( 'New Spoken Language Name' ),
			'menu_name'         => __( 'Spoken Languages' ),
		);

		$args = array(
			'hierarchical'      => false,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => false,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'spoken-language' ),
		);

		register_taxonomy( 'spoken-language', array( 'accommodation'), $args );

		$labels = array(
			'name'              => _x( 'On Site Activities', 'taxonomy general name' ),
			'singular_name'     => _x( 'On Site Activity', 'taxonomy singular name' ),
			'search_items'      => __( 'Search On Site Activities' ),
			'all_items'         => __( 'All On Site Activities' ),
			'parent_item'       => __( null),
			'parent_item_colon' => __( null ),
			'edit_item'         => __( 'Edit On Site Activity' ),
			'update_item'       => __( 'Update On Site Activity' ),
			'add_new_item'      => __( 'Add New On Site Activity' ),
			'new_item_name'     => __( 'New On Site Activity Name' ),
			'menu_name'         => __( 'On Site Activities' ),
		);

		$args = array(
			'hierarchical'          => false,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => false,
			'update_count_callback' => '_update_post_term_count',
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'onsite-activity' ),
		);

		register_taxonomy( 'onsite-activity', array( 'accommodation' ), $args );

        $labels = array(
			'name'              => _x( 'Off Site Activities', 'taxonomy general name' ),
			'singular_name'     => _x( 'Off Site Activity', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Off Site Activities' ),
			'all_items'         => __( 'All Off Site Activities' ),
			'parent_item'       => __( null),
			'parent_item_colon' => __( null ),
			'edit_item'         => __( 'Edit Off Site Activity' ),
			'update_item'       => __( 'Update Off Site Activity' ),
			'add_new_item'      => __( 'Add New Off Site Activity' ),
			'new_item_name'     => __( 'New Off Site Activity Name' ),
			'menu_name'         => __( 'Off Site Activities' ),
		);

		$args = array(
			'hierarchical'          => false,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => false,
			'update_count_callback' => '_update_post_term_count',
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'offsite-activity' ),
		);

		register_taxonomy( 'offsite-activity', array(  'accommodation' ), $args );

		$labels = array(
			'name'                       => _x( 'Services', 'taxonomy general name' ),
			'singular_name'              => _x( 'Service', 'taxonomy singular name' ),
			'search_items'               => __( 'Search Services' ),
			'popular_items'              => __( 'Popular Services' ),
			'all_items'                  => __( 'All Services' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Service' ),
			'update_item'                => __( 'Update Service' ),
			'add_new_item'               => __( 'Add New Service' ),
			'new_item_name'              => __( 'New Service Name' ),
			'separate_items_with_commas' => __( 'Separate services with commas' ),
			'add_or_remove_items'        => __( 'Add or remove services' ),
			'choose_from_most_used'      => __( 'Choose from the most used services' ),
			'not_found'                  => __( 'No services found.' ),
			'menu_name'                  => __( 'Services' ),
		);

		$args = array(
			'hierarchical'          => false,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => false,
			'update_count_callback' => '_update_post_term_count',
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'service' ),
		);

		register_taxonomy( 'service', array( 'accommodation' ), $args );

		$labels = array(
			'name'                       => _x( 'Property Facilities', 'taxonomy general name' ),
			'singular_name'              => _x( 'Property Facility', 'taxonomy singular name' ),
			'search_items'               => __( 'Search Property Facilities' ),
			'popular_items'              => __( 'Popular Property Facilities' ),
			'all_items'                  => __( 'All Property Facilities' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Property Facility' ),
			'update_item'                => __( 'Update Property Facility' ),
			'add_new_item'               => __( 'Add New Property Facility' ),
			'new_item_name'              => __( 'New Property Facility Name' ),
			'separate_items_with_commas' => __( 'Separate property facilities with commas' ),
			'add_or_remove_items'        => __( 'Add or remove property facilities' ),
			'choose_from_most_used'      => __( 'Choose from the most used property facilities' ),
			'not_found'                  => __( 'No property facilities found.' ),
			'menu_name'                  => __( 'Property Facilities' ),
		);

		$args = array(
			'hierarchical'          => false,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => false,
			'update_count_callback' => '_update_post_term_count',
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'property-facility' ),
		);

		register_taxonomy( 'property-facility', array(  'accommodation' ), $args );

        		$labels = array(
			'name'                       => _x( 'Room Facilities', 'taxonomy general name' ),
			'singular_name'              => _x( 'Room Facility', 'taxonomy singular name' ),
			'search_items'               => __( 'Search Room Facilities' ),
			'popular_items'              => __( 'Popular Room Facilities' ),
			'all_items'                  => __( 'All Room Facilities' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Room Facility' ),
			'update_item'                => __( 'Update Room Facility' ),
			'add_new_item'               => __( 'Add New Room Facility' ),
			'new_item_name'              => __( 'New Room Facility Name' ),
			'separate_items_with_commas' => __( 'Separate room facilities with commas' ),
			'add_or_remove_items'        => __( 'Add or remove room facilities' ),
			'choose_from_most_used'      => __( 'Choose from the most used room facilities' ),
			'not_found'                  => __( 'No room facilities found.' ),
			'menu_name'                  => __( 'Room Facilities' ),
		);

		$args = array(
			'hierarchical'          => false,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => false,
			'update_count_callback' => '_update_post_term_count',
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'room-facility' ),
		);

		register_taxonomy( 'room-facility', array(  'accommodation' ), $args );

        $labels = array(
			'name'              => _x( 'Tour Categories', 'taxonomy general name' ),
			'singular_name'     => _x( 'Tour Category', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Tour Categories' ),
			'all_items'         => __( 'All Tour Categories' ),
			'parent_item'       => __( 'Parent Tour Category' ),
			'parent_item_colon' => __( 'Parent Tour Category:' ),
			'edit_item'         => __( 'Edit Tour Category' ),
			'update_item'       => __( 'Update Tour Category' ),
			'add_new_item'      => __( 'Add New Tour Category' ),
			'new_item_name'     => __( 'New Tour Category Name' ),
			'menu_name'         => __( 'Tour Categories' ),
		);

		$args = array(
			'hierarchical'      => false,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => false,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'tour-category' ),
		);

		register_taxonomy( 'tour-category', array( 'tour' ), $args );
	}

    public function get_the_term_list($post_id, $taxonomy, $term_divider = '/', $reverse = false) {
        $object_terms = wp_get_object_terms($post_id, $taxonomy);
        $parents_assembled_array = array();
        //***
        if (!empty($object_terms)) {
            foreach ($object_terms as $term) {
                $parents_assembled_array[$term->parent][] = $term;
            }
        }
        //***
        $sorting_array = $this->sort_taxonomies_by_parents($parents_assembled_array);
        $term_list = $this->get_the_term_list_links($taxonomy, $sorting_array);
        if ($reverse) {
            $term_list = array_reverse($term_list);
        }
        $result = implode($term_divider, $term_list);

        return $result;
    }

    private function sort_taxonomies_by_parents($data, $parent_id = 0) {
        if (isset($data[$parent_id])) {
            if (!empty($data[$parent_id])) {
                foreach ($data[$parent_id] as $key => $taxonomy_object) {
                    if (isset($data[$taxonomy_object->term_id])) {
                        $data[$parent_id][$key]->childs = $this->sort_taxonomies_by_parents($data, $taxonomy_object->term_id);
                    }
                }

                return $data[$parent_id];
            }
        }

        return array();
    }

    //only for taxonomies. returns array of term links
    private function get_the_term_list_links($taxonomy, $data, $result = array()) {
        if (!empty($data)) {
            foreach ($data as $term) {
                $result[] = '<a rel="tag" href="' . get_term_link($term->slug, $taxonomy) . '">' . $term->name . '</a>';
                if (!empty($term->childs)) {
                    //***
                    $res = $this->get_the_term_list_links($taxonomy, $term->childs, array());
                    if (!empty($res)) {
                        //***
                        foreach ($res as $val) {
                            if (!is_array($val)) {
                                $result[] = $val;
                            }
                        }
                        //***
                    }
                    //***
                }
            }
        }

        return $result;
    }
}

$BS_Accommodation_Admin = new BS_Accommodation_Admin();