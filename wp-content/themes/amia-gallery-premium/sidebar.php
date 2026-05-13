<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<aside class="amia-sidebar"><?php if ( is_active_sidebar( 'sidebar-1' ) ) { dynamic_sidebar( 'sidebar-1' ); } else { ?><section class="amia-card"><h3>AMIA Gallery</h3><p class="amia-muted">Add widgets from Appearance → Widgets.</p></section><?php } ?></aside>
