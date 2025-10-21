//Rewrite rule for product urls 
add_action('init', function () {
    // Add the URL rewrite
    add_rewrite_rule(
        '^secure-download/([0-9]+)/?',
        'index.php?secure_download_pid=$matches[1]',
        'top'
    );

    // Register the query var
    add_rewrite_tag('%secure_download_pid%', '([0-9]+)');
});

//DELETE USERS DOWNLOAD HISTORY FOR TESTING
// add_action('init', function() {
//     if (is_user_logged_in()) {
//         delete_user_meta(2, 'downloaded_items');
//     }
// });

//Product handler
add_action('template_redirect', function () {
    $product_id = get_query_var('secure_download_pid', false);
    if (!$product_id) return;

    if (!is_user_logged_in()) {
        wp_die('You must be logged in to download this file.', 'Unauthorized', ['response' => 401]);
    } else {
        $user_id = get_current_user_id();
        $downloads = get_user_meta( $user_id, 'downloaded_items', true );
        
        if(!is_array($downloads)) {
            $downloads = [];
        }
        
        if(!in_array($product_id, $downloads)) {
            $downloads[] = $product_id;
            update_user_meta( $user_id, 'downloaded_items', $downloads);
        }
    }

    //Check for active MemberPress membership
    $user = MeprUtils::get_currentuserinfo();
    if (!$user && !$user->is_active()) {
        wp_die('You do not have permission to download this file.', 'Forbidden', ['response' => 403]);
    }

    $product = wc_get_product($product_id);
    if (!$product || !$product->is_downloadable()) {
        wp_die('Invalid product or no downloads.', 'Not Found', ['response' => 404]);
    }

    $downloads = $product->get_downloads();
    if (empty($downloads)) {
        wp_die('No downloadable files found for this product.', 'Not Found', ['response' => 404]);
    }

    //For this example, we’ll just serve the *first* download
    $download = reset($downloads);
    $file_path = $download->get_file();
    $filename  = $download->get_name(); 
    
    // Secure file delivery using WooCommerce's built-in handler
    try {
        WC_Download_Handler::download_file_force($file_path, $filename);
        exit;
    } catch (Exception $e) {
        wp_die('Download failed: ' . $e->getMessage(), 'Error', ['response' => 500]);
    }
});
