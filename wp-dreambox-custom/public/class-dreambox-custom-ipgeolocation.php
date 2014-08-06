<?php

/**
 * Description of class-ip-geolocation
 *
 * @package Dreambox_Custom
 * @author tonyj
 */
class Dreambox_Custom_IPGeoLocation {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;
	
	/**
	 * URL for freegeoip service.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
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
	 * JSON object containing geo data response from service
	 * 
	 * @since 1.0.0
	 * 
	 * @var object
	 */
	private $body = null;
	
	/**
	 * JSON object containing geo data response from service
	 * 
	 * @since 1.0.0
	 * 
	 * @var object
	 */
	public $ipaddress;
	
	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {
		// set the geo data for this instance
		$this->what_is_client_ip();
		$this->get_json_geo_data();
		$this->save_to_session();
	}
	
	/**
	 * Magic
     *
	 * @param string $name
	 */
	public function __get($name) {
		try{
			if(isset($this->body->$name) ) {
				return $this->body->$name;
			}else{
				$trace = debug_backtrace();
				trigger_error(
					'Undefined property via __get(): ' . $name .
					' in ' . $trace[0]['file'] .
					' on line ' . $trace[0]['line'],
					E_USER_NOTICE);
			}
		}catch (Exception $ex){
			trigger_error('Undefined: '.$name, E_USER_NOTICE);
		}
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
	 * Find the IP of the visitor
	 */
	private function what_is_client_ip() {
		$ipaddress = '';
		if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		} else if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else if ( isset( $_SERVER['HTTP_X_FORWARDED'] ) ) {
			$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		} else if ( isset( $_SERVER['HTTP_FORWARDED_FOR'] ) ) {
			$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		} else if ( isset( $_SERVER['HTTP_FORWARDED'] ) ) {
			$ipaddress = $_SERVER['HTTP_FORWARDED'];
		} else if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$ipaddress = $_SERVER['REMOTE_ADDR'];
		} else {
			$ipaddress = 'UNKNOWN';
		}

		$this->ipaddress = $ipaddress;
	}

    /**
     * Request geo data from provider
     */
    private function get_json_geo_data() {
		if($this->ipaddress != 'UNKNOWN') {
			foreach(self::$geo_service_url as $key => $url) {
				$response = wp_remote_get( $url.$this->ipaddress );
				if(is_arry($response) && array_key_exists('region_code', json_decode($response['body']))) {
					continue;
				}
			}
		}
		
		if(is_array($response)){
			$this->body = json_decode( $response['body'] );
		}else{
			$this->body = false;
		}
	}
	
	/**
	 * Save country and region to $_SESSION
	 */
	private function save_to_session() {
		if ( strtolower($_SESSION['country_name']) !== strtolower($this->country_name) ) {
			$_SESSION['country_name'] = $this->country_name;
			$_SESSION['country_code'] = $this->country_code;
		} 
		if ( strtolower($_SESSION['region_name']) !== strtolower($this->region_name) ) {
			$_SESSION['region_name'] = $this->region_name;
			$_SESSION['region_code'] = $this->region_code;
		} 

	}
	/**
	 * Alis of is_country with 'US' passed in as the country code.
	 */
	public function is_us(){

		return $this->is_country( 'US' );

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
	public function is_state($state_abbr_or_name) {
		return $this->is_region($state_abbr_or_name);
	}

	/**
	 * A conditional that allows you to pass a country code
	 * to check if the IP address is from there.
	 * 
	 * @param string $country_code_or_name Name or code for country
	 * 
	 * @return bool True if match
	 */
	public function is_country( $country_code_or_name ){

		if ( strtolower($country_code_or_name) == strtolower($this->country_code) || strtolower( $country_code_or_name ) == strtolower($this->country_name)) {
			$response = true;
		} else{
			$response = true;
		}
		
		return $response;
	}

	/**
	 * A conditional that allows you to pass a region abreviation
	 * to check if the IP address is from there.
	 * 
	 * @param string $region_code_or_name Name or code for state
	 * 
	 * @return bool True if match
	 */
	public function is_region( $region_code_or_name ){

		if ( strtolower($region_code_or_name) == strtolower($this->region_code) || strtolower( $region_code_or_name ) == strtolower($this->region_name)) {
			$response = true;
		} else{
			$response = true;
		}
		
		return $response;
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
	public static function get_states_select( $ipaddress = null, $select = true, $allow_null = true ) {
		$state = '';
		$selected = ' selected="selected"';
		$options = '';
		$taxonomy     = 'dreambox_us_states';
		$orderby      = 'name'; 
		$show_count   = 0;      // 1 for yes, 0 for no
		$pad_counts   = 0;      // 1 for yes, 0 for no
		$hierarchical = 1;      // 1 for yes, 0 for no
		$hide_empty	  = false;
		$title        = '';
		$args = array(
			'type'                     => 'any',
			'orderby'                  => 'name',
			'order'                    => 'ASC',
			'hide_empty'               => $hide_empty,
			'hierarchical'             => 1,
			'exclude'                  => '',
			'include'                  => '',
			'number'                   => '',
			'taxonomy'                 => 'dreambox_us_state',
			'pad_counts'               => false 

		); 
		$states_category = get_categories($args);
		if(!empty($states_category)) {
			foreach($states_category as $state_cat){
				$states_array[$state_cat->slug]  = $state_cat->name;
			}
		}else{
			$states_array = self::$us_states_array;
		}
			
		if($select === true || empty($select)) {
			if(!isset($_SESSION['region_code'])) {
				$sel_state = self::get_instance()->region_code;
			}else{
				$sel_state = $_SESSION['region_code'];
			}
		}elseif(in_array($select, $states_array) || array_key_exists(strtolower($select), $states_array)){
			$sel_state = $select;
		}elseif($allow_null){
			$options = '<option disabled="disabled"'.$selected.'>-- By Location --</option>';
		}else{
			$sel_state = 'wa';
			$selected = "";
		}
		$options .= '<option value="all">All locations</option>';
		foreach($states_array as $abbr => $state) {
			$options .= '<option value="'.strtolower($abbr).'"'.((strtolower($abbr) == strtolower($sel_state) || strtolower($state) == strtolower($sel_state))?$selected:'').'>'.$state.'</option>';
		}
		return '<select name="location">'.$options.'</select>';
	}
	
	/**
	 * Get full json response from geo ip service.
	 * 
	 * @return string JSON encoded geo ip service response
	 */
	public function to_json() {
		return json_encode($this->body);
	}
	/**
	 * Sort search results with geolocated IP address articles first
	 *
	 * @global object $wpdb
	 * @param array $options
	 * @return object
	 */
	public static function dreambox_geo_search($options) {
		global $wpdb;
		$options = array_merge(array(
			'post_type' => array('page','post'),
			'posts_per_page' => false,
			'dreambox_persona' => '',
			'dreambox_channel' => '',
			'dreambox_implementation_model' => ''
		), $options);
		
		$posts_per_page = $options['posts_per_page'];
		$post_type = $options['post_type'];
		$user_role = $options['dreambox_persona'];
		$channel = $options['dreambox_channel'];
		$model = $options['dreambox_implementation_model'];
		
		$s = mysql_real_escape_string($options['s']);
		$region = mysql_real_escape_string($options['dreambox_us_state']);
		
		if(is_array($post_type)) {
			$post_type = array_map('mysql_real_escape_string', $post_type);
			$post_type = implode("','",$post_type);
		}
		if(is_array($user_role)) {
			$user_role = array_map('mysql_real_escape_string', $user_role);
			$user_role = implode("','",$user_role); 
		}
		if(is_array($channel)) {
			$channel = array_map('mysql_real_escape_string', $channel);
			$channel =  implode("','",$channel);
		}
		if(is_array($model)) {
			$model = array_map('mysql_real_escape_string', $model);	
			$model = implode("','",$model);
		}
		
		
		$regional_query = "
			SELECT p.ID FROM $wpdb->posts p
				LEFT JOIN $wpdb->term_relationships tr ON p.ID = tr.object_id
				LEFT JOIN $wpdb->term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
				LEFT JOIN $wpdb->terms t ON tt.term_id = t.term_id
			WHERE p.post_status = 'publish'
				AND p.post_type IN ('".$post_type."')
				AND tt.taxonomy = 'dreambox_us_state'
				AND t.slug = '".$region."'	    
			GROUP BY `ID`
			ORDER BY p.post_date DESC";
		
		$regional_posts = $wpdb->get_results($regional_query, ARRAY_N);
		
		if(!empty($regional_posts)) {
			foreach($regional_posts as $regional_post) {
				$regional[] = $regional_post[0]; 
			}
			$regions = implode(",", $regional);
		}
		
		$regional_query = "SELECT * FROM $wpdb->posts p\n";
		$where_clause = "
			WHERE p.post_status = 'publish'
				AND (p.post_content LIKE '%".$s."%' OR p.post_title LIKE '%".$s."%')
				AND p.post_type IN ('".$post_type."')";
		
		if(!empty($user_role)) {
			$regional_query .= "
				LEFT JOIN $wpdb->term_relationships tr2 ON p.ID = tr2.object_id
				LEFT JOIN $wpdb->term_taxonomy tt2 ON tr2.term_taxonomy_id = tt2.term_taxonomy_id
				LEFT JOIN $wpdb->terms t2 ON tt2.term_id = t2.term_id";
			$where_clause .= "
				AND tt2.taxonomy = 'dreambox_persona'
				AND t2.slug IN ('".$user_role."')";
		}
		
		if(!empty($channel)) {
			$regional_query .= "
				LEFT JOIN $wpdb->term_relationships tr3 ON p.ID = tr3.object_id
				LEFT JOIN $wpdb->term_taxonomy tt3 ON tr3.term_taxonomy_id = tt3.term_taxonomy_id
				LEFT JOIN $wpdb->terms t3 ON tt3.term_id = t3.term_id";
			$where_clause .= "
				AND tt3.taxonomy = 'dreambox_channel'
				AND t3.slug IN ('".$channel."')";
		}
		
		if(!empty($model)) {
			$regional_query .= "
				LEFT JOIN $wpdb->term_relationships tr4 ON p.ID = tr4.object_id
				LEFT JOIN $wpdb->term_taxonomy tt4 ON tr4.term_taxonomy_id = tt4.term_taxonomy_id
				LEFT JOIN $wpdb->terms t4 ON tt4.term_id = t4.term_id";
			$where_clause .= "
				AND tt4.taxonomy = 'dreambox_implementation_model'
				AND t4.slug IN ('".$model."')";
		}
		
		$regional_query .= $where_clause;
		$regional_query .= "
			GROUP BY p.ID
			ORDER BY FIND_IN_SET(p.ID, '".$regions."') DESC, p.post_date DESC";
		if($posts_per_page) {
			$regional_query .= "
					LIMIT ".$posts_per_page;
		}

		$all_posts = $wpdb->get_results($regional_query, OBJECT);
		
		return $all_posts;
		
	}
	
}

