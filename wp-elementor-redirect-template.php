<php
// Prevent WP from redirecting /credit-transfer-articulation/{slug} to /program/{slug}
add_filter('redirect_canonical', function($redirect_url) {
    $request_path = trim($_SERVER['REQUEST_URI'], '/');
    if (preg_match('#^credit-transfer-articulation/([^/]+)$#', $request_path)) {
        return false; // Disable redirect for this path
    }
    return $redirect_url;
});
  
// /credit-transfer-articulation/{slug} requests
add_action('template_include', function($template) {
    $request_path = trim($_SERVER['REQUEST_URI'], '/');

    // Match /credit-transfer-articulation/{slug}
    if (preg_match('#^credit-transfer-articulation/([^/]+)$#', $request_path, $matches)) {
        $slug = $matches[1];

        // Lookup the Programs CPT post by slug
        $post = get_posts([
            'name'        => $slug,
            'post_type'   => 'program',
            'post_status' => 'publish',
            'numberposts' => 1,
        ]);

        if ($post) {
            global $wp_query;
            $wp_query->post             = $post[0];
            $wp_query->posts            = [$post[0]];
            $wp_query->queried_object   = $post[0];
            $wp_query->is_singular      = true;
            $wp_query->is_single        = true;
            $wp_query->is_404           = false;
            $wp_query->found_posts      = 1;
            $wp_query->post_count       = 1;

            // Flag for Elementor template
            set_query_var('use_alt_elementor_template', true);
        }
    }
    return $template;
});

// Swap Elementor single template for Programs based on URL
add_filter('elementor/theme/get_location_templates/template_id', function($template_id, $location) {
    if (
        'single' === $location &&
        is_singular('program') &&
        get_query_var('use_alt_elementor_template')
    ) {
        return 23175; // Elementor template ID
    }
    return $template_id;
}, 10, 2);

