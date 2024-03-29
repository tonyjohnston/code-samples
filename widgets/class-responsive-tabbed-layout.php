<?php

/**
 * @package   Responsive_Tabs
 * @author    Tony Johnston <tony.johnston@glg.com>
 *
 * Description:       Responsive tabbed layout widget. Mobile view it's an accordion.
 * Version:           1.0.0
 * Text Domain:       responsive-tabbed-layout
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */
class Responsive_Tabbed_Layout extends WP_Widget {

    /**
     * The variable name is used as the text domain when internationalizing strings
     * of text. Its value should match the Text Domain file header in the main
     * widget file.
     *
     * @since    1.0.0
     *
     * @var      string
     */
    protected $widget_slug = 'responsive-tabbed-layout';

    /* -------------------------------------------------- */
    /* Constructor
      /*-------------------------------------------------- */

    /**
     * Specifies the classname and description, instantiates the widget,
     * loads localization files, and includes necessary stylesheets and JavaScript.
     */
    public function __construct() {

        // load plugin text domain
        add_action('init', array($this, 'widget_textdomain'));

        // Hooks fired when the Widget is activated and deactivated
        // register_activation_hook(__FILE__, array($this, 'activate'));
        // register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // TODO: update description
        parent::__construct(
            $this->get_widget_slug(), __('Responsive Tabbed Layout', $this->get_widget_slug()), array(
                'classname' => $this->get_widget_slug() . '-class',
                'description' => __('Responsive tabbed-to-accordion layout widget.', $this->get_widget_slug())
            )
        );

        // Register admin styles and scripts
        // add_action('admin_print_styles', array($this, 'register_admin_styles'));
        // add_action('admin_enqueue_scripts', array($this, 'register_admin_scripts'));

        // Register site styles and scripts
        // add_action('wp_enqueue_scripts', array($this, 'register_widget_styles'));
        // add_action('wp_enqueue_scripts', array($this, 'register_widget_scripts'));

        // Refreshing the widget's cached output with each new post
        add_action('save_post', array($this, 'flush_widget_cache'));
        add_action('deleted_post', array($this, 'flush_widget_cache'));
        add_action('switch_theme', array($this, 'flush_widget_cache'));
    }

// end constructor

    /**
     * Return the widget slug.
     *
     * @since    1.0.0
     *
     * @return    Plugin slug variable.
     */
    public function get_widget_slug() {
        return $this->widget_slug;
    }

    /* -------------------------------------------------- */
    /* Widget API Functions
      /*-------------------------------------------------- */

    /**
     * Outputs the content of the widget.
     *
     * @param array args  The array of form elements
     * @param array instance The current instance of the widget
     */
    public function widget($args, $instance) {


        // Check if there is a cached output
        $cache = wp_cache_get($this->get_widget_slug(), 'widget');

        if (!is_array($cache))
            $cache = array();

        if (!isset($args['widget_id']))
            $args['widget_id'] = $this->id;

        if (isset($cache[$args['widget_id']]))
            return print $cache[$args['widget_id']];

        // go on with your widget logic, put everything into a string and …


        extract($args, EXTR_SKIP);

        ob_start();
        include( plugin_dir_path(__FILE__) . 'views/tabbed-layouts-widget.php' );
        $widget_string = ob_get_clean();

        $cache[$args['widget_id']] = $widget_string;

        wp_cache_set($this->get_widget_slug(), $cache, 'widget');

        print $widget_string;
    }

// end widget

    public function flush_widget_cache() {
        wp_cache_delete($this->get_widget_slug(), 'widget');
    }

    /**
     * Processes the widget's options to be saved.
     *
     * @param array new_instance The new instance of values to be generated via the update.
     * @param array old_instance The previous instance of values before the update.
     */
    public function update($new_instance, $old_instance) {
        if ($new_instance != $old_instance) {

            $instance = $new_instance;
        } else {
            $instance = $old_instance;
        }

        return $instance;
    }

// end widget

    /**
     * Generates the administration form for the widget.
     *
     * @param array instance The array of keys and values for the widget.
     */
    public function form($instance) {
        // TODO: Define default values for variables
        $instance = wp_parse_args(
                (array) $instance
        );

        // Display the admin form
        include( plugin_dir_path(__FILE__) . 'views/tabbed-layouts-widget-admin.php' );
    }

// end form

    /* -------------------------------------------------- */
    /* Public Functions
      /*-------------------------------------------------- */

    /**
     * Loads the Widget's text domain for localization and translation.
     */
    public function widget_textdomain() {

        load_plugin_textdomain($this->get_widget_slug(), false, plugin_dir_path(__FILE__) . 'lang/');
    }


    /**
     * Fired when the plugin is activated.
     *
     * @param  boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
     */
    public function activate($network_wide) {
        // TODO define activation functionality here
    }

// end activate

    /**
     * Fired when the plugin is deactivated.
     *
     * @param boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
     */
    public function deactivate($network_wide) {
        // TODO define deactivation functionality here
    }

// end deactivate

    /**
     * Registers and enqueues admin-specific styles.
     */
    public function register_admin_styles() {

        // wp_enqueue_style($this->get_widget_slug() . '-admin-styles', plugins_url('css/admin.css', __FILE__));
    }

// end register_admin_styles

    /**
     * Registers and enqueues admin-specific JavaScript.
     */
    public function register_admin_scripts() {

        // wp_enqueue_script($this->get_widget_slug() . '-admin-script', plugins_url('js/admin.js', __FILE__), array('jquery'));
    }

// end register_admin_scripts

    /**
     * Registers and enqueues widget-specific styles.
     */
    public function register_widget_styles() {

        // wp_enqueue_style($this->get_widget_slug() . '-widget-styles', plugins_url('css/widget.css', __FILE__));
    }

// end register_widget_styles

    /**
     * Registers and enqueues widget-specific scripts.
     */
    public function register_widget_scripts() {

        // wp_enqueue_script($this->get_widget_slug() . '-script', plugins_url('js/tabbed-layouts-widget.js', __FILE__), array('jquery'));
    }

// end register_widget_scripts
}

// end class
// TODO: Remember to change 'Widget_Name' to match the class name definition
add_action('widgets_init', create_function('', 'register_widget("Responsive_Tabbed_Layout");'));
