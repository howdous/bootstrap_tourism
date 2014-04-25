<?php

class BS_Room_Admin {

	public function __construct()
	{
		// Post Type and Fields
		if ( ! class_exists('CMB_Meta_Box'))
			require_once( plugin_dir_path( __FILE__ ) . '/Custom-Meta-Boxes/custom-meta-boxes.php' );

        add_action( 'add_meta_boxes_room', array( $this,'add_accom_parent') );

        add_action( 'init', array( $this, 'post_type_setup' ) );

	    add_filter( 'cmb_meta_boxes', array( $this, 'field_setup' ) );

	    // Admin Columns
	    add_filter( 'manage_edit-room_columns', array( $this, 'register_columns' ), 10, 1 );
	    add_action( 'manage_room_posts_custom_column', array( $this, 'display_columns' ), 10, 1 );
	    add_filter( 'manage_edit-room_sortable_columns', array( $this, 'register_sortable' ) );
	    add_filter( 'request', array( $this, 'sort_columns' ) );

	    add_action( 'init', array( $this, 'taxonomy_setup' ), 9 );
        //add_action( 'p2p_init',array($this, 'my_connection_types') );

        add_action( 'admin_menu', array($this,'register_custom_room_list') );

	}


    /*
    function my_connection_types() {
        p2p_register_connection_type( array(
            'name' => 'accom-to-room',
            'from' => 'accommodation',
            'to' => 'room',
            'cardinality' => 'one-to-many',
            'admin_box' => array(
                'show' => 'any',
                'context' => 'advanced'
              )
        ) );
    }
*/
    /* Hook meta box to just the 'place' post type. */

    /* Creates the meta box. */
    function add_accom_parent( $post ) {

        add_meta_box(
            'accom_parent',
            'Accomodation',
            array($this,'accom_parent_meta_box'),
            $post->post_type,
            'side',
            'high'
        );
    }

    /* Displays the meta box. */
    function accom_parent_meta_box( $post )
    {
        $post_id=get_the_ID();
        $parent_id=get_post_ancestors( $post_id );
        $parent = get_page( $parent_id[0] );
        if ( !empty( $parent ) ) {

            echo('<a href="post.php?post='.$parent->ID.'&action=edit">'.$parent->post_title.'</a>');
        }
        /*
        $parents = get_posts(
                array(
                    'post_type'   => 'accommodation',
                    'orderby'     => 'title',
                    'order'       => 'ASC',
                    'numberposts' => -1,
                    'post_status' => 'any'
                )
            );

        if ( !empty( $parents ) )
        {
            echo '<select name="parent_id" class="widefat">'; // !Important! Don't change the 'parent_id' name attribute.
            foreach ( $parents as $parent ) {
                printf( '<option value="%s"%s>%s</option>', esc_attr( $parent->ID ), selected( $parent->ID, $post->post_parent, false ), esc_html( $parent->post_title ) );
            }
            echo '</select>';
        }
        */
    }

    function register_custom_room_list()
    {
        //add_menu_page( 'Room', 'Room', 'manage_options', 'rooms', array($this,'custom_room_page'), '', 27 );
    }

    function custom_room_page()
    {
        global $wpdb;
        $sql="SELECT ID,post_title FROM $wpdb->posts where post_type='room' order by post_title asc";
        $posts = $wpdb->get_results($sql);
        $i = 0;
        echo '<table width="100%"><tr>';
        foreach ($posts as $post)
        {
            echo '<td><a href="post.php?post='.$post->ID.'&action=edit">'.$post->post_title.'</a></td>';
            if ($i++ == 3)
            {
                echo '</tr><tr>';
                $i=0;
            }
        }
        echo '</tr></table>';
    }

    public function taxonomy_setup()
	{
		$labels = array(
			'name'              => _x( 'Amenities', 'taxonomy general name' ),
			'singular_name'     => _x( 'Amenity', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Amenities' ),
			'all_items'         => __( 'All Amenities' ),
			'parent_item'       => __( 'Parent Amenity' ),
			'parent_item_colon' => __( 'Parent Amenity:' ),
			'edit_item'         => __( 'Edit Amenity' ),
			'update_item'       => __( 'Update Amenity' ),
			'add_new_item'      => __( 'Add New Amenity' ),
			'new_item_name'     => __( 'New Amenity Name' ),
			'menu_name'         => __( 'Amenities' ),
		);

		$args = array(
			'hierarchical'      => false,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'amenity' ),
		);

		register_taxonomy( 'amenity', array( 'room' ), $args );


    }


	public function post_type_setup()
	{
		$labels = array(
		    'name'               => 'Room',
		    'singular_name'      => 'Room',
		    'add_new'            => 'Add New',
		    'add_new_item'       => 'Add New Room',
		    'edit_item'          => 'Edit Room',
		    'new_item'           => 'New Room',
		    'all_items'          => 'All Rooms',
		    'view_item'          => 'View Room',
		    'search_items'       => 'Search Rooms',
		    'not_found'          => 'No room found',
		    'not_found_in_trash' => 'No rooms found in Trash',
		    'parent_item_colon'  => '',
		    'menu_name'          => 'Room'
		);

		$args = array(
		    'labels'             => $labels,
		    'public'             => true,
		    'publicly_queryable' => true,
		    'show_ui'            => true,
		    'show_in_menu'       => true,
		    'query_var'          => true,
		    'rewrite'            => array( 'slug' => 'room' ),
		    'capability_type'    => 'post',
		    'has_archive'        => false,
		    'hierarchical'       => true,
		    'menu_position'      => null,
            'show_in_menu'       =>'edit.php?post_type=accommodation',
		    'supports'           => array( 'title', 'editor', 'thumbnail')
		);

		register_post_type( 'room', $args );
	}

	function field_setup( array $meta_boxes )
	{

		$prefix = 'bs_room_';
		$fields = array(
			array(
				'id' => $prefix . 'rates',
				'name' => 'Room Rates:',
				'type' => 'group',
				'repeatable' => true,
				'fields' => array(
                        array(
                            'id'      => 'type',
                            'name'    => 'Rate Type',
                            'type'    => 'text',
                            ),
						array(
							'id' => 'rate',
							'name' => 'Rate:',
							'type' => 'text_small'
							),
                       array(
                            'id'      => 'currency',
                            'name'    => 'Currency',
                            'type'    => 'text',
                            ),
						array(
							'id' => 'start',
							'name' => 'Date Start:',
							'type' => 'date'
							),
		 				array(
							'id' => 'end',
							'name' => 'Date End:',
							'type' => 'date'
							),
                        array(
							'id' => 'conditions',
							'name' => 'Conditions:',
							'type' => 'textarea'
							),
                        array(
							'id' => 'included',
							'name' => 'Included:',
							'type' => 'textarea'
							),
					)
				),
			array(
				'id' => $prefix . 'images',
				'name' => 'Image:',
				'type' => 'image',
				'repeatable' => true,
			),

			array(
				'id' => $prefix . 'wetu_room_id',
				'name' => 'Wetu ID:',
				'type' => 'text',
				),
            	array(
				'id' => $prefix . 'affiliate_code',
				'name' => 'Affiliate Code:',
				'type' => 'text'
				),
                array(
                  'id'      => $prefix.'type',
                  'name'    => 'Room Type',
                  'type'    => 'text',
                  ),

		);

		$meta_boxes[] = array(
			'title' => 'Room Details',
			'pages' => 'room',
			'fields' => $fields
		);

		return $meta_boxes;

	}

	public function register_columns( $columns )
	{
        /*
	 	$column_thumbnail = array( 'thumbnail' => 'Thumbnail' );
	 	$column_price = array( 'price' => 'Price' );
	 	$column_featured = array( 'featured' => 'Featured' );
		$columns = array_slice( $columns, 0, 1, true ) + $column_thumbnail + array_slice( $columns, 1, NULL, true );
		$columns = array_slice( $columns, 0, 3, true ) + $column_price + array_slice( $columns, 3, NULL, true );
		$columns = array_slice( $columns, 0, 8, true ) + $column_featured + array_slice( $columns, 8, NULL, true );
		*/
		return $columns;

	}

	public function display_columns( $column )
	{
        /*
		global $post;
		switch ( $column ) {
			case 'thumbnail':
				echo get_the_post_thumbnail( $post->ID, array( 100,100 ) );
				break;
			case 'price':
				echo get_post_meta( get_the_ID(), 'bs_room_price', true );
				break;
			case 'featured':
				if ( get_post_meta( get_the_ID(), 'bs_room_featured', true ) == 1 ) {
					echo '&#10004;';
				} else {
					echo '&#10008;';
				}
				break;
		}
		*/
	}

	public function register_sortable( $columns )
	{
        /*
		$columns['featured'] = 'featured';
		$columns['taxonomy-travel_style'] = 'taxonomy-travel_style';
		$columns['taxonomy-activity'] = 'taxonomy-activity';
		$columns['taxonomy-service'] = 'taxonomy-service';
		$columns['taxonomy-facility'] = 'taxonomy-facility';
		*/
	    return $columns;
	}

	public function sort_columns( $vars )
	{
		if ( isset( $vars['orderby'] ) && 'featured' == $vars['orderby'] ) {
		    $vars = array_merge( $vars, array(
		        'meta_key' => 'bs_room_featured',
		        'orderby' => 'meta_value_num'
		    ) );
		}

		return $vars;
	}

}

$BS_Room_Admin = new BS_Room_Admin();