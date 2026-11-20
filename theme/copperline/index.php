<?php get_header(); ?>
<main id="main" tabindex="-1">
    <?php while (have_posts()) : the_post(); ?>
        <?php if ('builder' === get_post_meta(get_the_ID(), '_elementor_edit_mode', true)) : ?>
            <?php the_content(); ?>
        <?php else : ?>
            <article class="cl-wrap cl-default">
                <h1><?php the_title(); ?></h1>
                <?php the_content(); ?>
            </article>
        <?php endif; ?>
    <?php endwhile; ?>
</main>
<?php get_footer(); ?>
