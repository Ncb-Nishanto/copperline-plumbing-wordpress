<?php
/**
 * Run with wp eval-file scripts/seed.php from a dedicated local installation.
 */

if (!class_exists('Elementor\Plugin') || !class_exists('Copperline_Quotes') || 'copperline' !== get_stylesheet()) {
    WP_CLI::error('Activate Elementor, Copperline Quotes, and the Copperline theme first.');
}

$project_path = dirname(__DIR__);
$pages = [];
foreach (['home' => 'Home', 'services' => 'Services', 'about' => 'About', 'contact' => 'Contact'] as $slug => $title) {
    $page = get_page_by_path($slug);
    if ($page && !get_post_meta($page->ID, '_copperline_seed', true)) {
        WP_CLI::error("A page with slug {$slug} already exists. Use a fresh WordPress installation.");
    }
    if (!$page) {
        $id = wp_insert_post([
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_title' => $title,
            'post_name' => $slug,
            'meta_input' => ['_copperline_seed' => 1],
        ], true);
        if (is_wp_error($id)) {
            WP_CLI::error($id->get_error_message());
        }
        $pages[$slug] = (int) $id;
    } else {
        $pages[$slug] = $page->ID;
    }
}

update_option('blogname', 'Copperline Plumbing');
update_option('blogdescription', 'Residential plumbing in Portland');
update_option('show_on_front', 'page');
update_option('page_on_front', $pages['home']);
update_option('elementor_disable_color_schemes', 'yes');
update_option('elementor_disable_typography_schemes', 'yes');
update_option('elementor_experiment-container', 'active');
update_option('permalink_structure', '/%postname%/');
flush_rewrite_rules(false);

$route_links = [
    '/contact/#quote' => get_permalink($pages['contact']) . '#quote',
    '/services/' => get_permalink($pages['services']),
];

$fix_links = static function (array $elements) use (&$fix_links, $route_links): array {
    foreach ($elements as &$element) {
        $url = $element['settings']['link']['url'] ?? '';
        if (isset($route_links[$url])) {
            $element['settings']['link']['url'] = $route_links[$url];
        }
        if (!empty($element['elements'])) {
            $element['elements'] = $fix_links($element['elements']);
        }
    }
    return $elements;
};

foreach ($pages as $slug => $id) {
    if (get_post_meta($id, '_elementor_data', true)) {
        continue;
    }
    $template = json_decode(file_get_contents($project_path . '/elementor/' . $slug . '.json'), true, flags: JSON_THROW_ON_ERROR);
    update_post_meta($id, '_elementor_edit_mode', 'builder');
    update_post_meta($id, '_elementor_template_type', 'wp-page');
    update_post_meta($id, '_elementor_version', ELEMENTOR_VERSION);
    update_post_meta($id, '_elementor_data', wp_slash(wp_json_encode($fix_links($template['content']))));
}

$menu = wp_get_nav_menu_object('Copperline Primary');
$menu_id = $menu ? $menu->term_id : wp_create_nav_menu('Copperline Primary');
if (is_wp_error($menu_id)) {
    WP_CLI::error($menu_id->get_error_message());
}
$existing = array_map('intval', wp_list_pluck(wp_get_nav_menu_items($menu_id) ?: [], 'object_id'));
foreach ($pages as $id) {
    if (!in_array($id, $existing, true)) {
        $result = wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-object-id' => $id,
            'menu-item-object' => 'page',
            'menu-item-type' => 'post_type',
            'menu-item-status' => 'publish',
        ]);
        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
        }
    }
}
$locations = get_nav_menu_locations();
$locations['primary'] = (int) $menu_id;
set_theme_mod('nav_menu_locations', $locations);
\Elementor\Plugin::$instance->files_manager->clear_cache();
WP_CLI::success('Copperline is ready at ' . home_url('/'));
