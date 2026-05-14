<?php
/* Template Name: Media Gallery */
get_header();
amia_premium_page_hero( 'Media Gallery', 'Lazy-loaded masonry gallery with optimized profile media, infinite-scroll ready layout and premium hover states.' );
$users = amia_premium_agum_users( array( 'per_page' => 48 ) );
?>
<section class="amia-section"><div class="amia-container"><div class="amia-gallery-toolbar amia-glass"><button class="amia-btn amia-btn-ghost amia-global-search-open" type="button"><?php esc_html_e( 'Search Gallery', 'amia-gallery-premium' ); ?></button><span class="amia-pill"><?php esc_html_e( 'WebP ready · Lazy loaded · Masonry', 'amia-gallery-premium' ); ?></span></div><div class="amia-masonry-gallery" data-infinite="true"><?php foreach ( $users['items'] as $index => $user ) : $src = $user->profile_photo ? $user->profile_photo : amia_premium_user_image( $user->wp_user_id ); ?><article class="amia-gallery-tile amia-card"><img loading="lazy" decoding="async" src="<?php echo esc_url( $src ); ?>" alt="<?php echo esc_attr( $user->name ); ?>"><div><h3><?php echo esc_html( $user->name ); ?></h3><span><?php echo esc_html( $user->role ); ?></span></div></article><?php endforeach; ?></div><div class="amia-infinite-sentinel amia-skeleton"></div></div></section><?php get_footer(); ?>
