<?php
/**
 * Add theme support
 */
add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('menus');
    add_theme_support('editor-styles');
    add_editor_style('dist/editor.css');

    register_nav_menus([
        'main_navigation' => __('Main Navigation'),
    ]);

    // Add ACF options page
    if (function_exists('acf_add_options_page')) {
        acf_add_options_page([
            'page_title' => 'Global Options',
            'menu_slug' => 'global-options',
        ]);
    }
});

/**
 * Enqueue script and styles
 */
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('app', assets_url('/dist/app.css'), [], null);
    wp_enqueue_script('app', assets_url('/dist/app.js'), ['jquery'], null, true);

    // Register script for blocks
    // If needed, separate the script per block
    wp_register_script('blocks/text', assets_url('/dist/blocks/text.js'), ['jquery'], null, true);
});

function pr($data) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}

function pd($data) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}