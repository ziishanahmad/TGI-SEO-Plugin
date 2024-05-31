<?php
/*
Plugin Name: TGI SEO Plugin
Description: Manage titles, descriptions, and keywords for posts and pages.
Version: 1.0
Author: Zeeshan Ahmad
Author URI: https://www.linkedin.com/in/zeeshan-ahmad-10a84b18/
website: Tabsgi.com
Author Email: ziishanahmad@gmail.com
License: MIT
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Admin menu
add_action('admin_menu', 'tgi_meta_manager_admin_menu');
function tgi_meta_manager_admin_menu() {
    add_menu_page('TGI SEO Plugin', 'TGI SEO Plugin', 'manage_options', 'tgi-meta-manager', 'tgi_meta_manager_settings_page', 'dashicons-admin-generic');
    add_submenu_page(null, 'Edit Meta', 'Edit Meta', 'manage_options', 'tgi-meta-manager-edit', 'tgi_meta_manager_edit_meta_page');
}

// Display all posts and pages with filters
function tgi_meta_manager_settings_page() {
    $post_type = isset($_GET['post_type']) ? $_GET['post_type'] : 'post'; // Default to posts
    $post_status = isset($_GET['post_status']) ? $_GET['post_status'] : 'publish'; // Default to published

    ?>
    <div class="wrap">
        <h2><?php esc_html_e('TGI SEO Plugin', 'tgi-meta-manager'); ?></h2>
        <form method="get">
            <input type="hidden" name="page" value="tgi-meta-manager" />
            <select name="post_type">
                <option value="post" <?php selected($post_type, 'post'); ?>><?php esc_html_e('Posts', 'tgi-meta-manager'); ?></option>
                <option value="page" <?php selected($post_type, 'page'); ?>><?php esc_html_e('Pages', 'tgi-meta-manager'); ?></option>
            </select>
            <select name="post_status">
                <option value="publish" <?php selected($post_status, 'publish'); ?>><?php esc_html_e('Published', 'tgi-meta-manager'); ?></option>
                <option value="draft" <?php selected($post_status, 'draft'); ?>><?php esc_html_e('Draft', 'tgi-meta-manager'); ?></option>
            </select>
            <input type="submit" value="<?php esc_attr_e('Filter', 'tgi-meta-manager'); ?>" class="button" />
        </form>
        <table class="widefat fixed" cellspacing="0">
            <thead>
                <tr>
                    <th><?php esc_html_e('Title', 'tgi-meta-manager'); ?></th>
                    <th><?php esc_html_e('Type', 'tgi-meta-manager'); ?></th>
                    <th><?php esc_html_e('Status', 'tgi-meta-manager'); ?></th>
                    <th><?php esc_html_e('View', 'tgi-meta-manager'); ?></th>
                    <th><?php esc_html_e('Edit Meta', 'tgi-meta-manager'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $args = array(
                    'post_type' => $post_type,
                    'post_status' => $post_status,
                    'posts_per_page' => -1
                );
                $query = new WP_Query($args);
                if ($query->have_posts()) : while ($query->have_posts()) : $query->the_post();
                    ?>
                    <tr>
                        <td><?php echo get_the_title(); ?></td>
                        <td><?php echo get_post_type(get_the_ID()); ?></td>
                        <td><?php echo get_post_status(get_the_ID()); ?></td>
                        <td><a href="<?php echo get_permalink(get_the_ID()); ?>" target="_blank"><?php esc_html_e('View', 'tgi-meta-manager'); ?></a></td>
                        <td><a href="<?php echo admin_url('admin.php?page=tgi-meta-manager-edit&post_id=' . get_the_ID()); ?>"><?php esc_html_e('Edit Meta', 'tgi-meta-manager'); ?></a></td>
                    </tr>
                    <?php
                endwhile; endif;
                wp_reset_postdata();
                ?>
            </tbody>
        </table>
    </div>
    <?php
}

// Meta edit form
function tgi_meta_manager_edit_meta_page() {
    $post_id = isset($_GET['post_id']) ? $_GET['post_id'] : false;
    if (!$post_id) {
        wp_die(__('Post not found', 'tgi-meta-manager'));
    }

    $post = get_post($post_id);
    $meta_title = get_post_meta($post_id, '_meta_title', true);
    $meta_description = get_post_meta($post_id, '_meta_description', true);
    $meta_keywords = get_post_meta($post_id, '_meta_keywords', true);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Sanitize and validate input
        $meta_title = isset($_POST['meta_title']) ? sanitize_text_field($_POST['meta_title']) : '';
        $meta_description = isset($_POST['meta_description']) ? sanitize_textarea_field($_POST['meta_description']) : '';
        $meta_keywords = isset($_POST['meta_keywords']) ? sanitize_text_field($_POST['meta_keywords']) : '';

        // Update meta fields
        update_post_meta($post_id, '_meta_title', $meta_title);
        update_post_meta($post_id, '_meta_description', $meta_description);
        update_post_meta($post_id, '_meta_keywords', $meta_keywords);
        echo '<div class="updated"><p>' . esc_html__('Meta updated.', 'tgi-meta-manager') . '</p></div>';
    }

    ?>
    <div class="wrap">
        <h2><?php echo esc_html__('Edit Meta Tags for:', 'tgi-meta-manager') . ' ' . esc_html(get_the_title($post_id)); ?></h2>
        <a href="<?php echo admin_url('admin.php?page=tgi-meta-manager'); ?>" class="button"><?php esc_html_e('Back', 'tgi-meta-manager'); ?></a>
        <form method="post" action="">
            <p><label><?php esc_html_e('Title:', 'tgi-meta-manager'); ?> <input type="text" name="meta_title" value="<?php echo esc_attr($meta_title); ?>" class="widefat" /></label></p>
            <p><label><?php esc_html_e('Description:', 'tgi-meta-manager'); ?> <textarea name="meta_description" class="widefat"><?php echo esc_textarea($meta_description); ?></textarea></label></p>
            <p><label><?php esc_html_e('Keywords:', 'tgi-meta-manager'); ?> <input type="text" name="meta_keywords" value="<?php echo esc_attr($meta_keywords); ?>" class="widefat" /></label></p>
            <p><input type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes', 'tgi-meta-manager'); ?>" /></p>
        </form>
    </div>
    <?php
}

// Output meta tags in the header
add_action('wp_head', 'tgi_meta_manager_add_meta_tags', 1); // Ensure this runs before WordPress adds its <title> tag
function tgi_meta_manager_add_meta_tags() {
    if (is_single() || is_page()) {
        $post_id = get_the_ID();
        $meta_title = get_post_meta($post_id, '_meta_title', true);
        $meta_description = get_post_meta($post_id, '_meta_description', true);
        $meta_keywords = get_post_meta($post_id, '_meta_keywords', true);

        if (!empty($meta_description)) {
            echo '<meta name="description" content="' . esc_attr($meta_description) . '" />';
        }
        if (!empty($meta_keywords)) {
            echo '<meta name="keywords" content="' . esc_attr($meta_keywords) . '" />';
        }
        // Remove default WordPress <title> tag if meta title is set
        if (!empty($meta_title)) {
            remove_action('wp_head', '_wp_render_title_tag', -1111);
            echo '<title>' . esc_html($meta_title) . '</title>';
        }
    }
}

// Modify the default title tag
add_filter('pre_get_document_title', 'tgi_meta_manager_modify_document_title', 10);
function tgi_meta_manager_modify_document_title($title) {
    if (is_single() || is_page()) {
        $post_id = get_the_ID();
        $meta_title = get_post_meta($post_id, '_meta_title', true);

        // If meta title is set, use it instead of the default title
        if (!empty($meta_title)) {
            return $meta_title;
        }
    }

    // If meta title is not set or not on a single post/page, return the default title
    return $title;
}
?>
