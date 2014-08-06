<?php

/**
 * Not a full-fledged plugin. A simple class that can incorporate easily with any existing plugin and theme.
 *
 * @package WP_Custom_IPGeoLocation
 * @author tonyjohnston
 */
class WP_Custom_IPGeoLocation
{

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;
	
	/**
     * URL for freegeoip services.
	 *
	 * @since    1.0.0
	 *
     * @var      array
	 */
	protected static $geo_service_url = array(
		'primary' => "http://freegeoip.net/json/",
		'secondary' => "http://www.telize.com/geoip/"
		);
	
	/**
	 * Array of US states.
	 * 
	 * @since 1.0.0
	 * 
	 * @var array 
	 */
	protected static $us_states_array = array('AL'=>'Alabama', 'AK'=>'Alaska', 'AZ'=>'Arizona', 'AR'=>'Arkansas', 'CA'=>'California', 'CO'=>'Colorado', 'CT'=>'Connecticut', 'DE'=>'Delaware', 'DC'=>'District of Columbia', 'FL'=>'Florida', 'GA'=>'Georgia', 'HI'=>'Hawaii', 'ID'=>'Idaho', 'IL'=>'Illinois', 'IN'=>'Indiana', 'IA'=>'Iowa', 'KS'=>'Kansas', 'KY'=>'Kentucky', 'LA'=>'Louisiana', 'ME'=>'Maine', 'MT'=>'Montana', 'NE'=>'Nebraska', 'NV'=>'Nevada', 'NH'=>'New Hampshire', 'NJ'=>'New Jersey', 'NM'=>'New Mexico', 'NY'=>'New York', 'NC'=>'North Carolina', 'ND'=>'North Dakota', 'OH'=>'Ohio', 'OK'=>'Oklahoma', 'OR'=>'Oregon', 'MD'=>'Maryland', 'MA'=>'Massachusetts', 'MI'=>'Michigan', 'MN'=>'Minnesota', 'MS'=>'Mississippi', 'MO'=>'Missouri', 'PA'=>'Pennsylvania', 'RI'=>'Rhode Island', 'SC'=>'South Carolina', 'SD'=>'South Dakota', 'TN'=>'Tennessee', 'TX'=>'Texas', 'UT'=>'Utah', 'VT'=>'Vermont', 'VA'=>'Virginia', 'WA'=>'Washington', 'WV'=>'West Virginia', 'WI'=>'Wisconsin', 'WY'=>'Wyoming');
	/**
     * Client IP Address
     *
	 * @since 1.0.0
     *
     * @var string
	 */
    public $ipaddress;
	/**
	 * JSON object containing geo data response from service
     *
     * @since 1.0.0
     *
     * @var object
     */
    private $body = null;
	
	/**
     * Initialize the plugin by setting location.
	 *
     * @since     1.0.0
	 */
	private function __construct() {

        // Register taxonomies
        add_action('init', array($this, 'register_taxonomies'));

        // Populate taxonomy if it's empty
        add_action('init', array($this, 'populate_default_terms'));

		// set the geo data for this instance
		$this->what_is_client_ip();
		$this->get_json_geo_data();
		$this->save_to_session();
	}

    /**
     * Find the IP of the visitor
	 */
    private function what_is_client_ip()
    {
        $ipaddress = '';

        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } else if (isset($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddress = 'UNKNOWN';
		}

        $this->ipaddress = $ipaddress;
	}

    /**
     * Request geo data from provider
     */
    private function get_json_geo_data()
    {
        $response = false;
        if ($this->ipaddress != 'UNKNOWN') {
            foreach (self::$geo_service_url as $key => $url) {
                $response = wp_remote_get($url . $this->ipaddress);
                if (is_array($response) && array_key_exists('region_code', json_decode($response['body']))) {
                    continue;
                }
            }
        }

        if (is_array($response)) {
            $this->body = json_decode($response['body']);
        } else {
            $this->body = false;
        }
    }

    /**
     * Save country and region to $_SESSION
     */
    private function save_to_session()
    {
        if (strtolower($_SESSION['country_name']) !== strtolower($this->country_name)) {
            $_SESSION['country_name'] = $this->country_name;
            $_SESSION['country_code'] = $this->country_code;
        }
        if (strtolower($_SESSION['region_name']) !== strtolower($this->region_name)) {
            $_SESSION['region_name'] = $this->region_name;
            $_SESSION['region_code'] = $this->region_code;
        }

    }

    /**
     * Provides an html select with states abbreviation as the option value
     * and names as the label
     *
     * @param string $ipaddress
     * @param mixed $select If true, will automatically select client state by IP lookup. If a state name or abbreviation is specified, will select given state.
     * @param bool $allow_null If true, will fill first option with -- By State -- disabled
     * @return string HTML Select containing states
     */
    public static function get_states_select($ipaddress = null, $select = true, $allow_null = true)
    {

        $selected = ' selected="selected"';
        $options = '';
        $hide_empty = false;
        $states_array = array();
        $sel_state = '';

        $args = array(
            'type' => 'any',
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => $hide_empty,
            'hierarchical' => 1,
            'exclude' => '',
            'include' => '',
            'number' => '',
            'taxonomy' => 'custom_geo_us_state',
            'pad_counts' => false

        );
        $states_category = get_categories($args);
        if (!empty($states_category)) {
            foreach ($states_category as $state_cat) {
                $states_array[$state_cat->slug] = $state_cat->name;
            }
        } else {
            $states_array = self::$us_states_array;
        }

        if ($select === true || empty($select)) {
            if (!isset($_SESSION['region_code'])) {
                $sel_state = self::get_instance()->region_code;
            } else {
                $sel_state = $_SESSION['region_code'];
            }
        } elseif (in_array($select, $states_array) || array_key_exists(strtolower($select), $states_array)) {
            $sel_state = $select;
        } elseif ($allow_null) {
            $options = '<option disabled="disabled"' . $selected . '>-- By Location --</option>';
        } else {
            $sel_state = 'wa';
            $selected = "";
        }
        $options .= '<option value="all">All locations</option>';
        foreach ($states_array as $abbr => $state) {
            $options .= '<option value="' . strtolower($abbr) . '"' . ((strtolower($abbr) == strtolower($sel_state) || strtolower($state) == strtolower($sel_state)) ? $selected : '') . '>' . $state . '</option>';
        }
        return '<select name="location">' . $options . '</select>';
    }

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
     * Magic
     *
     * @param string $name
     * @return array | string | bool $value
	 */
    public function __get($name)
    {
        try {
            if (isset($this->body->$name)) {
                return $this->body->$name;
            } else {
                $trace = debug_backtrace();
                trigger_error(
                    'Undefined property via __get(): ' . $name .
                    ' in ' . $trace[0]['file'] .
                    ' on line ' . $trace[0]['line'],
                    E_USER_NOTICE);
            }
        } catch (Exception $ex) {
            trigger_error('Undefined: ' . $name, E_USER_NOTICE);
            return false;
		}
	}

    /**
     * Alis of is_country with 'US' passed in as the country code.
     */
    public function is_us()
    {

        return $this->is_country('US');

	}
	
	/**
     * A conditional that allows you to pass a country code
     * to check if the IP address is from there.
     *
     * @param string $country_code_or_name Name or code for country
     *
     * @return bool True if match
	 */
    public function is_country($country_code_or_name)
    {

        if (strtolower($country_code_or_name) == strtolower($this->country_code) || strtolower($country_code_or_name) == strtolower($this->country_name)) {
            $response = true;
        } else {
            $response = true;
        }

        return $response;
	}

    /**
     * A conditional that allows you to pass a state abreviation
     * to check if the IP address is from there.
     *
     * @param $state_abbr_or_name
     * @internal param string $region_code_or_name Name or code for state
     *
     * @return bool True if match
     */
    public function is_state($state_abbr_or_name)
    {
        return $this->is_region($state_abbr_or_name);
	}

    /**
     * A conditional that allows you to pass a region abreviation
     * to check if the IP address is from there.
     *
     * @param string $region_code_or_name Name or code for state
     *
     * @return bool True if match
     */
    public function is_region($region_code_or_name)
    {

        if (strtolower($region_code_or_name) == strtolower($this->region_code) || strtolower($region_code_or_name) == strtolower($this->region_name)) {
            $response = true;
        } else {
            $response = true;
        }

        return $response;
	}

	/**
     * Get full json response from geo ip service.
     *
     * @return string JSON encoded geo ip service response
	 */
    public function to_json()
    {
        return json_encode($this->body);
	}

    /**
     * Register taxonomies for states and countries
     */
    private function register_taxonomies()
    {
        $all_types = get_post_types(array(
            '_builtin' => false,
            'public' => true,
            'exclude_from_search' => false
        ), 'names');

        // Add post and page back in after removing them with _builtin: false
        $all_types['post'] = 'post';
        $all_types['page'] = 'page';

        // Exclude acf
        unset($all_types['acf']);

        // Add new taxonomy, make it hierarchical (like categories)
        $labels = array(
            'name' => _x('States', 'taxonomy general name'),
            'singular_name' => _x('State', 'taxonomy singular name'),
            'search_items' => __('Search States'),
            'all_items' => __('All States'),
            'parent_item' => __('Parent State'),
            'parent_item_colon' => __('Parent State:'),
            'edit_item' => __('Edit State'),
            'update_item' => __('Update State'),
            'add_new_item' => __('Add New State'),
            'new_item_name' => __('New State Name'),
            'menu_name' => __('State'),
        );

        register_taxonomy('custom_geo_us_state', $all_types, array(
                'hierarchical' => true,
                'labels' => $labels,
                'show_ui' => true,
                'query_var' => true,
                'rewrite' => array('slug' => 'state'),
            )
        );

        // Add new taxonomy, make it hierarchical (like categories)
        $labels = array(
            'name' => _x('Countries', 'taxonomy general name'),
            'singular_name' => _x('Country', 'taxonomy singular name'),
            'search_items' => __('Search Countries'),
            'all_items' => __('All Countries'),
            'parent_item' => __('Parent Country'),
            'parent_item_colon' => __('Parent Country:'),
            'edit_item' => __('Edit Country'),
            'update_item' => __('Update Country'),
            'add_new_item' => __('Add New Country'),
            'new_item_name' => __('New Country Name'),
            'menu_name' => __('Country'),
        );

        register_taxonomy('custom_geo_country', $all_types, array(
                'hierarchical' => true,
                'labels' => $labels,
                'show_ui' => true,
                'query_var' => true,
                'rewrite' => array('slug' => 'country'),
            )
        );
    }

    /**
     * Setting the default terms for the custom taxonomies
     *
     * @uses    get_terms
     * @uses    wp_insert_term
     * @uses    term_exists
     *
     * @since   1.0
     * @author  Tony Johnston
     */
    private function pupulate_default_terms()
    {

        $taxonomies = get_taxonomies();
        if (is_array($taxonomies)) {
            foreach ($taxonomies as $taxonomy) {
                $current_values = get_terms($taxonomy, array('hide_empty' => false));
                if (empty($current_values)) {
                    $defaults = $this->get_default_taxonomy_values($taxonomy);
                    if (!empty($defaults)) {
                        foreach ($defaults as $default) {
                            if (!term_exists($default['name'], $taxonomy)) {
                                wp_insert_term($default['name'], $taxonomy, array('slug' => $default['short']));
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Returns an array of US states with name and proper short form
     *
     * @return    array
     *
     * @since   1.0
     * @author    Tony Johnston
     *
     * @param string $tax
     */
    private function get_default_taxonomy_values($tax)
    {

        switch ($tax) {
            case 'custom_geo_us_state':
                $values = array(
                    '0' => array('name' => 'Alaska', 'short' => 'AK'),
                    '1' => array('name' => 'American Samoa', 'short' => 'AS'),
                    '2' => array('name' => 'Arizona', 'short' => 'AZ'),
                    '3' => array('name' => 'Arkansas', 'short' => 'AR'),
                    '4' => array('name' => 'California', 'short' => 'CA'),
                    '5' => array('name' => 'Colorado', 'short' => 'CO'),
                    '6' => array('name' => 'Conneticut', 'short' => 'CT'),
                    '7' => array('name' => 'Delaware', 'short' => 'DE'),
                    '8' => array('name' => 'District of Columbia', 'short' => 'DC'),
                    '9' => array('name' => 'Federated States of Micronesia', 'short' => 'FM'),
                    '10' => array('name' => 'Florida', 'short' => 'FL'),
                    '11' => array('name' => 'Georgia', 'short' => 'GA'),
                    '12' => array('name' => 'Guam', 'short' => 'GU'),
                    '13' => array('name' => 'Hawaii', 'short' => 'HI'),
                    '14' => array('name' => 'Idaho', 'short' => 'ID'),
                    '15' => array('name' => 'Illinois', 'short' => 'IL'),
                    '16' => array('name' => 'Indiana', 'short' => 'IN'),
                    '17' => array('name' => 'Iowa', 'short' => 'IA'),
                    '18' => array('name' => 'Kansas', 'short' => 'KS'),
                    '19' => array('name' => 'Kentucky', 'short' => 'KY'),
                    '20' => array('name' => 'Louisiana', 'short' => 'LA'),
                    '21' => array('name' => 'Maine', 'short' => 'ME'),
                    '22' => array('name' => 'Marshall Islands', 'short' => 'MH'),
                    '23' => array('name' => 'Maryland', 'short' => 'MD'),
                    '24' => array('name' => 'Massachusetts', 'short' => 'MA'),
                    '25' => array('name' => 'Michigan', 'short' => 'MI'),
                    '26' => array('name' => 'Minnesota', 'short' => 'MN'),
                    '27' => array('name' => 'Mississippi', 'short' => 'MS'),
                    '28' => array('name' => 'Missouri', 'short' => 'MO'),
                    '29' => array('name' => 'Montana', 'short' => 'MT'),
                    '30' => array('name' => 'Nebraska', 'short' => 'NE'),
                    '31' => array('name' => 'Nevada', 'short' => 'NV'),
                    '32' => array('name' => 'New Hampshire', 'short' => 'NH'),
                    '33' => array('name' => 'New Jersey', 'short' => 'NJ'),
                    '34' => array('name' => 'New Mexico', 'short' => 'NM'),
                    '35' => array('name' => 'New York', 'short' => 'NY'),
                    '36' => array('name' => 'North Carolina', 'short' => 'NC'),
                    '37' => array('name' => 'North Dakota', 'short' => 'ND'),
                    '38' => array('name' => 'Northern Mariana Islands', 'short' => 'MP'),
                    '39' => array('name' => 'Ohio', 'short' => 'OH'),
                    '40' => array('name' => 'Oklahoma', 'short' => 'OK'),
                    '41' => array('name' => 'Oregon', 'short' => 'OR'),
                    '42' => array('name' => 'Palau', 'short' => 'PW'),
                    '43' => array('name' => 'Pennsylvania', 'short' => 'PA'),
                    '44' => array('name' => 'Puerto Rico', 'short' => 'PR'),
                    '45' => array('name' => 'Rhode Island', 'short' => 'RI'),
                    '46' => array('name' => 'South Carolina', 'short' => 'SC'),
                    '47' => array('name' => 'South Dakota', 'short' => 'SD'),
                    '48' => array('name' => 'Tennessee', 'short' => 'TN'),
                    '49' => array('name' => 'Texas', 'short' => 'TX'),
                    '50' => array('name' => 'Utah', 'short' => 'UT'),
                    '51' => array('name' => 'Vermont', 'short' => 'VT'),
                    '52' => array('name' => 'Virgin Islands', 'short' => 'VI'),
                    '53' => array('name' => 'Virginia', 'short' => 'VA'),
                    '54' => array('name' => 'Washington', 'short' => 'WA'),
                    '55' => array('name' => 'West Virginia', 'short' => 'WV'),
                    '56' => array('name' => 'Wisconsin', 'short' => 'WI'),
                    '57' => array('name' => 'Wyoming', 'short' => 'WY'),
                );
                break;
            case 'dreambox_country':
                $values = array(
                    '0' => array('name' => 'Canada', 'short' => 'CAN')
                );
                break;
            default:
                $values = false;
        }
        return $values;
    }
	
}
