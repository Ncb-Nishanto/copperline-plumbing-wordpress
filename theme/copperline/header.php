<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="cl-skip" href="#main">Skip to content</a>
<header class="cl-header">
    <div class="cl-wrap cl-header-row">
        <a class="cl-brand" href="<?php echo esc_url(home_url('/')); ?>">
            <svg aria-hidden="true" viewBox="0 0 40 40" fill="none">
                <rect x="1" y="1" width="38" height="38" rx="8" fill="#f3bd8c"/>
                <path d="M27 11h-9a7 7 0 0 0-7 7v11m20-13h-9a6 6 0 0 0-6 6v9M9 25h9m5-16v9" stroke="#142a35" stroke-width="2.4" stroke-linecap="round"/>
            </svg>
            <span>Copperline<small>Plumbing</small></span>
        </a>
        <button type="button" class="cl-toggle" aria-expanded="false" aria-controls="cl-navigation">Menu</button>
        <nav class="cl-nav" id="cl-navigation" aria-label="Main navigation">
            <?php wp_nav_menu(['theme_location' => 'primary', 'container' => false, 'fallback_cb' => 'wp_page_menu', 'depth' => 1]); ?>
        </nav>
        <a class="cl-button" href="<?php echo esc_url(copperline_contact_url()); ?>">Request a quote</a>
    </div>
</header>
