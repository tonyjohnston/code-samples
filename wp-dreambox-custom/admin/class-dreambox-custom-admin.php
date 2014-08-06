<?php

/**
 * 
 * Plugin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * If you're interested in introducing public-facing
 * functionality, then refer to `class-dreambox-custom-public.php`
 *
 * @package   Dreambox_Custom
 * @author    Tony Johnston <tony.johnston@glg.com>
 */
class Dreambox_Custom_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		/*
		 * @TODO :
		 *
		 * - Uncomment following lines if the admin class should only be available for super admins
		 */
		/* if( ! is_super_admin() ) {
		  return;
		  } */

		/*
		 * Call $plugin_slug from public plugin class.
		 *
		 */
		$plugin = Dreambox_Custom::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		// Load admin style sheet and JavaScript.
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

		// Add the options page and menu item.
		add_action('admin_menu', array($this, 'add_plugin_admin_menu'));
		add_action('add_meta_boxes', array($this, 'add_plugin_admin_metaboxes'));

		// Add an action link pointing to the options page.
		//$plugin_basename = plugin_basename(plugin_dir_path(__DIR__) . $this->plugin_slug . '.php');
		//add_filter('plugin_action_links_' . $plugin_basename, array($this, 'add_action_links'));

		/*
		 * Define custom functionality.
		 *
		 * Read more about actions and filters:
		 * http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		 *
		 *  add_action( '@TODO', array( $this, 'action_method_name' ) );
		 *  add_filter( '@TODO', array( $this, 'filter_method_name' ) );
		 */
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		/*
		 * @TODO :
		 *
		 * - Uncomment following lines if the admin class should only be available for super admins
		 */
		/* if( ! is_super_admin() ) {
		  return;
		  } */

		// If the single instance hasn't been set, set it now.
		if (null == self::$instance) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		if (!isset($this->plugin_screen_hook_suffix)) {
			return;
		}

		$screen = get_current_screen();
		if ($this->plugin_screen_hook_suffix == $screen->id) {
			wp_enqueue_style($this->plugin_slug . '-admin-styles', plugins_url('assets/css/admin.css', __FILE__), array(), Dreambox_Custom::VERSION);
		}
	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		if (!isset($this->plugin_screen_hook_suffix)) {
			return;
		}

		$screen = get_current_screen();
		if ($this->plugin_screen_hook_suffix == $screen->id) {
			wp_enqueue_script($this->plugin_slug . '-admin-script', plugins_url('assets/js/admin.js', __FILE__), array('jquery'), Dreambox_Custom::VERSION);
		}
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		/*
		 * Add a settings page for this plugin to the Settings menu.
		 *
		 * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
		 *
		 *        Administration Menus: http://codex.wordpress.org/Administration_Menus
		 *
		 *   For reference: http://codex.wordpress.org/Roles_and_Capabilities
		 */
		$this->plugin_screen_hook_suffix = add_options_page(
				__('Dreambox Custom', $this->plugin_slug), __('Dreambox Custom', $this->plugin_slug), 'manage_options', $this->plugin_slug, array($this, 'display_plugin_admin_page')
		);

		add_menu_page('Content', 'Content', 'manage_options', 'dreambox_content', array($this, 'display_plugin_admin_page'), null, 6);
		add_menu_page('Tagged Media', 'Tagged Media', 'manage_options', 'dreambox_media', array($this, 'display_plugin_admin_page'), null, 7);
		add_menu_page('Layouts', 'Layouts', 'manage_options', 'dreambox_layouts', array($this, 'display_plugin_admin_page'), null, 8);
		add_menu_page('Taxonomy', 'Taxonomy', 'manage_options', 'dreambox_taxonomy', array($this, 'display_plugin_admin_page'), null, 9);
		
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once( 'views/admin.php' );
	}

	public function add_plugin_admin_metaboxes() {
		
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links($links) {

		return array_merge(
				array(
			'settings' => '<a href="' . admin_url('options-general.php?page=' . $this->plugin_slug) . '">' . __('Settings', $this->plugin_slug) . '</a>'
				), $links
		);
	}

	/**
	 * NOTE:     Actions are points in the execution of a page or process
	 *           lifecycle that WordPress fires.
	 *
	 *           Actions:    http://codex.wordpress.org/Plugin_API#Actions
	 *           Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 * @since    1.0.0
	 *
	 *  public function action_method_name() {
	 * 	    // @TODO: Define your action hook callback here
	 *  }
	 */

	/**
	 * NOTE:     Filters are points of execution in which WordPress modifies data
	 *           before saving it or sending it to the browser.
	 *
	 *           Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *           Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 * @since    1.0.0
	 *
	 *   public function filter_method_name() {
	 * 	// @TODO: Define your filter hook callback here
	 *   }

	  /**
	 * Set which columns are shown in the admin listing
	 * @since 1.0.0
	 * 
	 * @param array $columns
	 * @return array
	 */
	public function custom_columns($columns) {

		$new_columns = array(
			'shortcode' => 'Shortcode'
		);
		$columns = array_slice($columns, 0, 2, true) + $new_columns + array_slice($columns, 3, count($columns) - 1, true);

		return $columns;
	}

	/**
	 * Display values for custom columns
	 * @since 1.0.0
	 * 
	 * @param string $column
	 * @param int $post_id
	 */
	public function custom_columns_values($column, $post_id) {
		switch ($column) {
			case 'shortcode':
				echo '[shortcode id="' . $post_id . '" /]';
				break;
			default:
				break;
		}
	}

}
