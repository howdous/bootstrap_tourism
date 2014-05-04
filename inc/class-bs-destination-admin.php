<?php

class BS_Destination_Admin {

	public function __construct()
	{

		// Post Type and Fields
		if ( ! class_exists('CMB_Meta_Box'))
        {
			require_once( plugin_dir_path( __FILE__ ) . '/Custom-Meta-Boxes/custom-meta-boxes.php' );
        }

	    add_action( 'init', array( $this, 'post_type_setup' ) );
	    add_filter( 'cmb_meta_boxes', array( $this, 'field_setup' ) );
        add_action( 'add_meta_boxes_destination', array( $this,'add_custom_location') );

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


	public function post_type_setup()
	{
		$labels = array(
		    'name'               => 'Destination',
		    'singular_name'      => 'Destination',
		    'add_new'            => 'Add New',
		    'add_new_item'       => 'Add New Destination',
		    'edit_item'          => 'Edit Destination',
		    'new_item'           => 'New Destination',
		    'all_items'          => 'All Destination',
		    'view_item'          => 'View Destination',
		    'search_items'       => 'Search Destination',
		    'not_found'          => 'No destination found',
		    'not_found_in_trash' => 'No destination found in Trash',
		    'parent_item_colon'  => '',
		    'menu_name'          => 'Destination'
		);

		$args = array(
            'menu_icon'          =>'dashicons-location',
		    'labels'             => $labels,
		    'public'             => true,
		    'publicly_queryable' => true,
		    'show_ui'            => true,
		    'show_in_menu'       => true,
		    'query_var'          => true,
		    'rewrite'            => array( 'slug' => 'destination' ),
		    'capability_type'    => 'post',
		    'has_archive'        => true,
		    'hierarchical'       => true,
		    //'menu_position'      => '5.21',
		    'supports'           => array( 'title', 'editor',)
		);

		register_post_type( 'destination', $args );
	}

	function field_setup( array $meta_boxes )
	{

		$prefix = 'bs_destination_';

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
				'id' => $prefix . 'status',
				'name' => 'Status:',
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
			'pages' => 'destination',
			'fields' => $wetu_details_fields
		);

		$meta_boxes[] = array(
			'title' => 'Destination Details',
			'pages' => 'destination',
			'fields' => $fields
		);


        $meta_boxes[] = array(
			'title' => 'Location Details',
			'pages' => 'destination',
			'fields' => $location_details_fields
		);

        $meta_boxes[] = array(
			'title' => 'Images',
			'pages' => 'destination',
			'fields' => $image_details
		);

        $meta_boxes[] = array(
			'title' => 'Documents',
			'pages' => 'destination',
			'fields' => $document_details
		);

        $meta_boxes[] = array(
			'title' => 'Youtube Videos',
			'pages' => 'destination',
			'fields' => $youtube_details
		);

		return $meta_boxes;
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

$BS_Destination_Admin = new BS_Destination_Admin();