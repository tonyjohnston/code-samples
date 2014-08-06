<?php

/**
 * @package   Dreambox_Custom
 * @author    Tony Johnston <tony.johnston@glg.com>
 *
 * Description:       Video marquee widget for primary hub pages
 * Version:           1.0.0
 * Text Domain:       dreambox-custom
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */
class Hub_Video_Marquee extends WP_Widget {

    /**
     * The variable name is used as the text domain when internationalizing strings
     * of text. Its value should match the Text Domain file header in the main
     * widget file.
     *
     * @since    1.0.0
     *
     * @var      string
     */
    protected $widget_slug = 'hub-video-marquee';

    /* -------------------------------------------------- */
    /* Constructor
      /*-------------------------------------------------- */

    /**
     * Specifies the classname and description, instantiates the widget,
     * loads localization files, and includes necessary stylesheets and JavaScript.
     */
    public function __construct() {

        // load plugin text domain
        //add_action('init', array($this, 'widget_textdomain'));

        // Hooks fired when the Widget is activated and deactivated
        // register_activation_hook(__FILE__, array($this, 'activate'));
        // register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        parent::__construct(
            $this->get_widget_slug(), __('Hub Video Marquee', $this->get_widget_slug()), array(
                'classname' => $this->get_widget_slug() . '-class',
                'description' => __('Featured video content for parimary hub marquees.', $this->get_widget_slug())
            )
        );

        // Register admin styles and scripts
        // add_action('admin_print_styles', array($this, 'register_admin_styles'));
        add_action('admin_enqueue_scripts', array($this, 'register_admin_scripts'));

        // Register public styles and scripts
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

        $widget_string = $before_widget;

        // TODO: Here is where you manipulate your widget's values based on their input fields
        ob_start();
        include( plugin_dir_path(__FILE__) . 'views/hub-video-marquee-widget.php' );
        $widget_string .= ob_get_clean();
        $widget_string .= $after_widget;


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
        // TODO: Define default values for your variables
        $instance = wp_parse_args(
                (array) $instance
        );
        
        // TODO: Store the values of the widget in their own variable
        // Display the admin form
        include( plugin_dir_path(__FILE__) . 'views/hub-video-marquee-widget-admin.php' );
    }

// end form
    /**
     * Registers and enqueues admin-specific styles. Needs to be fixed since this code used to be a standalone plugin.
     */
    public function register_admin_styles() {

        //wp_enqueue_style($this->get_widget_slug() . '-admin-styles', plugins_url('css/admin.css', __FILE__));
    }

// end register_admin_styles

    /**
     * Registers and enqueues admin-specific JavaScript.
     */
    public function register_admin_scripts() {

       wp_enqueue_script($this->get_widget_slug() . '-admin-script', plugins_url('js/admin.js', __FILE__), array('jquery'));
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

        // wp_enqueue_script($this->get_widget_slug() . '-script', plugins_url('js/admin.js', __FILE__), array('jquery'));
    }

// end register_widget_scripts
	
}

// end class
add_action('widgets_init', create_function('', 'register_widget("Hub_Video_Marquee");'));
