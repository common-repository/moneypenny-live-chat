<?php
/**
 * Plugin Name: Moneypenny Live Chat
 * Plugin URI: https://www.moneypenny.com/uk/
 * Description: Use our live chat in wordpress
 * Version: 1.1
 * Author: Moneypenny
 * Author URI: https://www.moneypenny.com/
 * License: GPL2
 */

class MoneyPennySettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_moneypenny_page' ) );
        add_action( 'admin_init', array( $this, 'moneypenny_init' ) );
    }

    /**
     * Add options page
     */
    public function add_moneypenny_page()
    {
        // This page will be under "Settings"
        add_menu_page(
            'Settings Admin',
            'MP Live Chat',
            'manage_options',
            'moneypenny_settings',
            array( $this, 'create_admin_page' ),
            plugin_dir_url( __FILE__ ) . 'img/bird.png'
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'moneypenny_option' );
        $img = plugins_url( 'img/moneypenny.png' , __FILE__ );

        ?>
        <div class="wrap">
            <?php printf('<a target="_blank" class="logo" href="http://www.moneypenny.com"><img src="%s" alt="MoneyPenny"></a>', $img); ?>
            <h2>Moneypenny Live Chat Settings</h2>
            <p>By setting your MoneyPenny Widget ID below and enabling the widget you'll install Moneypenny Live Chat on your Wordpress site.</p>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'moneypenny_group' );
                do_settings_sections( 'moneypenny_settings' );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function moneypenny_init()
    {
        register_setting(
            'moneypenny_group', // Option group
            'moneypenny_option', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'Settings', // Title
            array( $this, 'print_section_info' ), // Callback
            'moneypenny_settings' // Page
        );

        add_settings_field(
            'enable',
            'Enable Moneypenny Live Chat',
            array( $this, 'enabled_callback' ),
            'moneypenny_settings',
            'setting_section_id'
        );

        add_settings_field(
            'widget_id', // ID
            'Widget ID', // Title
            array( $this, 'widget_id_callback' ), // Callback
            'moneypenny_settings', // Page
            'setting_section_id' // Section
        );

        add_settings_field(
            'gtm_ua', // ID
            'GTM Code', // Title
            array( $this, 'gtm_ua_callback' ), // Callback
            'moneypenny_settings', // Page
            'setting_section_id' // Section
        );

    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['widget_id'] ) )
            $new_input['widget_id'] = sanitize_text_field( $input['widget_id'] );

        if( isset( $input['enable'] ) )
            $new_input['enable'] = $input['enable'];

        if( isset( $input['gtm_ua'] ) )
            $new_input['gtm_ua'] = sanitize_text_field( $input['gtm_ua'] );

        return $new_input;
    }

    /**
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Please enter your Moneypenny data below:';
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function widget_id_callback()
    {
        printf(
            '<input type="text" id="widget_id" name="moneypenny_option[widget_id]" value="%s" />',
            isset( $this->options['widget_id'] ) ? esc_attr( $this->options['widget_id']) : ''
        );
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function enabled_callback()
    {
    	$checked = '';
    	if ($this->options['enable'] == 'checked') {
    		$checked = 'checked="true"';
    	}
        printf(
            '<input type="checkbox" id="enable" name="moneypenny_option[enable]" value="checked" %s/>',
            $checked
        );
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function gtm_ua_callback()
    {
        printf(
            '<input type="text" id="gtm_ua" name="moneypenny_option[gtm_ua]" value="%s" />',
            isset( $this->options['gtm_ua'] ) ? esc_attr( $this->options['gtm_ua']) : ''
        );
    }
}

if( is_admin() )
    $moneypenny = new MoneyPennySettingsPage();

function get_moneypenny_code($widget_id) {
	$moneypenny = get_option( 'moneypenny_option' );
	if($moneypenny['enable'] && $moneypenny['widget_id']) {
		printf('<!-- begin Moneypenny code --><script type="text/javascript">(function() {var se = document.createElement(\'script\'); se.type = \'text/javascript\'; se.async = true;se.src = \'//storage.googleapis.com/moneypennychat/js/%s.js\';var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(se, s);})();</script><!-- end Moneypenny code -->', $moneypenny['widget_id']);
	}
  if($moneypenny['enable'] && $moneypenny['gtm_ua']) {
    printf('<!-- begin Moneypenny GTM code --><script>(function(i, s, o, g, r, a, m) { i[\'GoogleAnalyticsObject\'] = r; i[r] = i[r] || function() {(i[r].q = i[r].q || []).push(arguments)}, i[r].l = 1 * new Date(); a = s.createElement(o), m = s.getElementsByTagName(o)[0]; a.async = 1; a.src = g; m.parentNode.insertBefore(a, m) })(window, document, \'script\', \'https://www.google-analytics.com/analytics.js\', \'ga\'); ga(\'create\', \'%s\', \'auto\');</script><!-- end Moneypenny GTM code -->', $moneypenny['gtm_ua']);
  }
}
add_action( 'wp_footer', 'get_moneypenny_code' );

?>
