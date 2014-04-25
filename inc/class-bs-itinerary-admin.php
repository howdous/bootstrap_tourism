<?php

class BS_Itinerary_Admin
{

	public function __construct()
	{
		// Post Type and Fields
		if ( ! class_exists('CMB_Meta_Box'))
			require_once( plugin_dir_path( __FILE__ ) . '/Custom-Meta-Boxes/custom-meta-boxes.php' );

        add_action( 'init', array( $this, 'post_type_setup' ) );
	    add_filter( 'cmb_meta_boxes', array( $this, 'field_setup' ) );
        add_action( 'p2p_init', array( $this, 'relationship_setup' ) );
        add_action( 'add_meta_boxes_itinerary', array( $this,'add_tour_parent') );
	}

    /* Creates the meta box. */
    function add_tour_parent( $post ) {

        add_meta_box(
            'tour_parent',
            'Tour',
            array($this,'tour_parent_meta_box'),
            $post->post_type,
            'side',
            'high'
        );
    }

    /* Displays the meta box. */
    function tour_parent_meta_box( $post )
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
                    'post_type'   => 'tour',
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

	public function post_type_setup()
	{
		$labels = array(
		    'name'               => 'Itineraries',
		    'singular_name'      => 'Itinerary',
		    'add_new'            => 'Add New',
		    'add_new_item'       => 'Add New Itinerary',
		    'edit_item'          => 'Edit Itinerary',
		    'new_item'           => 'New Itinerary',
		    'all_items'          => 'All Itineraries',
		    'view_item'          => 'View Itinerary',
		    'search_items'       => 'Search Itineraries',
		    'not_found'          => 'No itinerary found',
		    'not_found_in_trash' => 'No itneraries found in Trash',
		    'parent_item_colon'  => '',
		    'menu_name'          => 'Itinerary'
		);

		$args = array(
		    'labels'             => $labels,
		    'public'             => true,
		    'publicly_queryable' => true,
		    'show_ui'            => true,
		    //'show_in_menu'       => true,
		    'query_var'          => true,
		    'rewrite'            => array( 'slug' => 'itinerary' ),
		    'capability_type'    => 'post',
		    'has_archive'        => false,
		    'hierarchical'       => false,
		    //'menu_position'      => '5.3',
            'show_in_menu'       =>'edit.php?post_type=tour',
		    'supports'           => array( 'title', 'editor', 'thumbnail' )
		);

		register_post_type( 'itinerary', $args );
	}

    function field_setup( array $meta_boxes )
	{
		$prefix = 'bs_itinerary_';
		$fields = array(
                array(
                'id' => $prefix . 'leg_id',
                'name' => 'Leg ID:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'sequence',
                'name' => 'Sequence:',
                'type' => 'text',
                'default'=>'0'
                ),
                array(
                'id' => $prefix . 'content_entity_id',
                'name' => 'Content ID:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'nights',
                'name' => 'Nights:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'booking_reference',
                'name' => 'Booking Reference:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'rooms_single',
                'name' => 'Rooms Single:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'rooms_double',
                'name' => 'Rooms Double:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'rooms_twin',
                'name' => 'Rooms Twin:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'rooms_triple',
                'name' => 'Rooms Triple:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'rooms_family',
                'name' => 'Rooms Family:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'room_type_single',
                'name' => 'Room Type Single:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'room_type_double',
                'name' => 'Room Type Double:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'room_type_twin',
                'name' => 'Room Type Twin:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'room_type_triple',
                'name' => 'Room Type Triple:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'room_type_family',
                'name' => 'Room Type Family:',
                'type' => 'text'
                ),
                array(
                'id' => $prefix . 'type',
                'name' => 'Type:',
                'type' => 'text'
                ),


		);

		$meta_boxes[] = array(
			'title' => 'Itinerary Details',
			'pages' => 'itinerary',
			'fields' => $fields
		);

		return $meta_boxes;
    }

    function relationship_setup()
    {
        //include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        //if ( is_plugin_active('posts-to-posts/posts-to-posts.php') ) {
            p2p_register_connection_type( array(
                'name' => 'itinerary_to_day',
                'from' => 'itinerary',
                'to' => 'day',
                'cardinality' => 'one-to-many',
                'can_create_post'=> false,
                'admin_dropdown' => false,
            ) );

            p2p_register_connection_type( array(
                'name' => 'itinerary_to_accommodation',
                'from' => 'itinerary',
                'to' => 'accommodation',
                'can_create_post'=> false,
                'admin_dropdown' => false,
                //'cardinality' => 'many-to-many',
                //'admin_box' => array('show'=>false),
            ) );
/*
            p2p_register_connection_type( array(
                'name' => 'tour_to_itinerary',
                'from' => 'tour',
                'to' => 'itinerary',
                'cardinality' => 'one-to-many',
                'can_create_post'=> false,
                'admin_dropdown' => false,
                //'admin_box' => array('show'=>false),
            ) );

            */
        //}
    }

    function register_tour_meta_box()
    {
        add_meta_box( 'tour-box', 'Tour', array($this,'render_tour_meta_box'), 'itinerary','side','high' );
    }

    function render_tour_meta_box( $post )
    {
        $connected = p2p_type( 'tour_to_itinerary' )->get_connected( $post );
    ?>
        <ul>
        <?php while ( $connected->have_posts() ) : $connected->the_post(); ?>
            <li><a href="post.php?post=<?php the_ID(); ?>&action=edit"><?php the_title(); ?></a></li>
        <?php endwhile; ?>
        </ul>
        <?php
    }
}

$BS_Itinerary_Admin = new BS_Itinerary_Admin();