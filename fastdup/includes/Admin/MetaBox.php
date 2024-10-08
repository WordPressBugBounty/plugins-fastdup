<?php

/**
 * Metabox Renderer
 */

namespace NJT\FastDup\Admin;

use NJT\FastDup as NJT_FastDup;
use NJT\FastDup\Admin\Helper\Helper as Helper;

defined('ABSPATH') || exit;
class MetaBox
{
  /**
   * Hook Suffix of the plugin.
   *
   * @since    1.0
   * @access   private
   */
  private $hook_suffix;

  /**
   * Instance of this class.
   *
   * @since    1.0
   * @var      object
   */
  protected static $instance = null;

  /**
   * Return an instance of this class.
   *
   * @since     1.0
   * @return    object    A single instance of this class.
   */
  public static function get_instance()
  {
    if (null == self::$instance) {
      self::$instance = new self;
      self::$instance->do_hooks();
    }
    return self::$instance;
  }

  /**
   * Private construct
   *
   * @since     1.0
   */
  private function __construct()
  {
    $this->hook_suffix = array(
      'njt_fastdup_page',
    );
  }

  private function do_hooks()
  {
    // Load admin Stylesheet and JavaScript.
    add_action('admin_menu', array($this, 'register_admin_menu'));
    add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

    add_filter('screen_options_show_screen', array($this, 'remove_screen_options'));
    add_filter('plugin_action_links_' . FAST_DUP_BASE_NAME, array($this, 'add_action_links'));
    add_filter('plugin_row_meta', array($this, 'add_plugin_description_meta'), 10, 2);
  }

  public function add_plugin_description_meta($links, $file)
  {
    if (strpos($file, 'fastdup.php') !== false) {
      $new_links = array(
        '<a href="https://ninjateam.gitbook.io/fastdup/" target="_blank">Documentation</a>',
        '<a href="https://m.me/ninjateam.org" target="_blank">Support</a>'
      );

      $links = array_merge($links, $new_links);
    }

    return $links;
  }

  public function add_action_links($links)
  {
    $custom_links = array(
      '<a href="' . esc_url(admin_url('/admin.php?page=njt-fastdup')) . '">' . __('Create Package', 'fastdup') . '</a>',
    );

    return array_merge($custom_links, $links);
  }

  public function remove_screen_options($show_screen)
  {
    if (isset($GLOBALS['hook_suffix']) && ($GLOBALS['hook_suffix'] == $this->hook_suffix['njt_fastdup_page'])) {
      return false;
    }
    return $show_screen;
  }

  /**
   * Register and enqueue admin scripts
   *
   */
  public function enqueue_admin_scripts($hook_suffix)
  {
    if ($hook_suffix == $this->hook_suffix['njt_fastdup_page']) {
      wp_register_script('njt-fastdup', NJT_FASTDUP_URL . '/assets/admin/build/js/app.js', array(), Helper::get_version(), true);
      wp_enqueue_script('njt-fastdup');
      wp_localize_script('njt-fastdup', 'njt_fastdup', array(
        'nonce' => wp_create_nonce('njt_fastdup'),
        'i18n' => NJT_FastDup\Translation\I18n_Admin::get_translation(),
      ));

      wp_localize_script('njt-fastdup', 'njt_fastdup_data', array(
        'apiNonce' => wp_create_nonce('wp_rest'),
        'apiUrl' => rest_url('njt-fastdup/v1'),
        'wpApiUrl' => rest_url('wp/v2'),
        'currentTime' => current_time('mysql'),
      ));
    }
  }

  /**
   * Register and enqueue admin scripts
   */
  public function register_admin_menu($hook_suffix)
  {
    $this->hook_suffix['njt_fastdup_page'] = add_menu_page(
      __('FastDup', 'fastdup'),
      __('FastDup', 'fastdup'),
      'manage_options',
      'njt-fastdup/',
      array($this, 'render_setting_options'),
      NJT_FASTDUP_URL . '/assets/admin/icon/flash.svg'
    );
  }

  public function render_setting_options()
  {
    include_once NJT_FASTDUP_PLUGIN_PATH . 'includes/Admin/Views/html-setting-options.php';
  }
}
