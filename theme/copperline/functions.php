<?php

add_action('after_setup_theme', static function (): void {
    add_theme_support('title-tag');
    add_theme_support('html5', ['search-form', 'style', 'script']);
    register_nav_menus(['primary' => 'Primary navigation']);
});

add_action('wp_enqueue_scripts', static function (): void {
    wp_enqueue_style('copperline', get_stylesheet_uri(), [], wp_get_theme()->get('Version'));
    wp_enqueue_script('copperline-navigation', get_theme_file_uri('/assets/navigation.js'), [], '1.0.0', true);
});

add_shortcode('copperline_hero_photo', static function (): string {
    return sprintf(
        '<img src="%s" width="1122" height="1402" alt="A plumber tightening a copper fitting beneath a bathroom vanity" fetchpriority="high">',
        esc_url(get_theme_file_uri('/assets/plumber.jpg'))
    );
});

function copperline_contact_url(): string
{
    $page = get_page_by_path('contact');
    return ($page ? get_permalink($page) : home_url('/contact/')) . '#quote';
}
