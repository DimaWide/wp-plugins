<?php

namespace PostManagerOOP\Api;


use WP_REST_Request;

class PostApi {

    public function init() {
        add_action('rest_api_init', array($this, 'register_rest_routes'));

        add_action('wp_ajax_openai_generate_ai_text', [$this, 'openai_generate_ai_text']);
        add_action('wp_ajax_nopriv_openai_generate_ai_text', [$this, 'openai_generate_ai_text']);
    }

    public function register_rest_routes() {
        register_rest_route('post-manager-oop/v1', '/posts', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_posts'),
            'permission_callback' => function () {
                return current_user_can('edit_posts');
            }
        ));

        register_rest_route('post-manager-oop/v1', '/post/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_post'),
            'permission_callback' => function () {
                return current_user_can('edit_posts');
            }
        ));

        register_rest_route('post-manager-oop/v1', '/post', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_post'),
            'permission_callback' => function () {
                return current_user_can('edit_posts');
            }
        ));

        register_rest_route('post-manager-oop/v1', '/post-change/(?P<id>\d+)', array(
            'methods' => 'POST',
            'callback' => array($this, 'update_post'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route('post-manager-oop/v1', '/post/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_post'),
            'permission_callback' => function () {
                return current_user_can('delete_posts');
            }
        ));
    }


    public function get_posts(WP_REST_Request $request) {
        $paged = $request['page'] ? $request['page'] : 1;
        $args = [
            'post_type' => 'post',
            'post_status' => 'publish',
            'paged' => $paged,
        ];

        $posts = get_posts($args);
        $total_posts = wp_count_posts()->publish;
        $total_pages = ceil($total_posts / 5); // Если вы показываете 5 постов на странице

        $posts_data = [];
        foreach ($posts as $post) {
            $featured_image_id = get_post_thumbnail_id($post->ID);
            $featured_image_url = wp_get_attachment_url($featured_image_id);

            $posts_data[] = [
                'ID' => $post->ID,
                'post_title' => $post->post_title,
                'post_content' => $post->post_content,
                'featured_media' => $featured_image_url, // Добавьте URL изображения
                'permalink' => get_permalink($post->ID) // Get the permalink
            ];
        }

        return [
            'posts' => $posts_data,
            'total_pages' => $total_pages,
            'current_page' => $paged,
        ];
    }



    public function get_post(WP_REST_Request $request) {
        $post_id = $request['id'];
        $post = get_post($post_id);

        if (!$post) {
            return new WP_Error('no_post', 'Post not found', array('status' => 404));
        }

        return array(
            'id' => $post->ID,
            'title' => $post->post_title,
            'content' => $post->post_content
        );
    }



    public function create_post(WP_REST_Request $request) {
        $data = $request->get_params();
        $featured_image_id = null;

        if (!empty($_FILES['post-featured-image']['name'])) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');

            $attachment_id = media_handle_upload('post-featured-image', 0);
            if (is_wp_error($attachment_id)) {
                return new WP_Error('upload_error', 'Error uploading image', ['status' => 400]);
            }

            $featured_image_id = $attachment_id;
        }

        $post_data = [
            'post_title' => sanitize_text_field($data['title']),
            'post_content' => $data['content'],
            'post_status' => 'publish',
            'post_type' => 'post',
            'meta_input' => [
                '_thumbnail_id' => $featured_image_id,
            ],
        ];

        $post_id = wp_insert_post($post_data);

        if (is_wp_error($post_id)) {
            return new WP_Error('post_error', 'Error creating post', ['status' => 500]);
        }

        return ['id' => $post_id, 'featured_media' => wp_get_attachment_url($featured_image_id), 'adasd' => $data];
    }



    public function update_post(WP_REST_Request $request) {
        $post_id = $request['id'];
        $data = $request->get_params();
        $featured_image_id = null;

        if (!empty($_FILES['post-featured-image']['name'])) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');

            $attachment_id = media_handle_upload('post-featured-image', 0);
            if (is_wp_error($attachment_id)) {
                return new WP_Error('upload_error', 'Error uploading image', ['status' => 400]);
            }

            $featured_image_id = $attachment_id;
        }

        $post_data = array(
            'ID' => $post_id,
            'post_title' => sanitize_text_field($data['title']),
            'post_content' => $data['content'],
        );

        if (! empty($featured_image_id)) {
            $post_data['meta_input'] = [
                '_thumbnail_id' => $featured_image_id,
            ];
        }

        $result = wp_update_post($post_data);

        if (is_wp_error($result)) {
            return new WP_Error('update_failed', 'Failed to update post', array('status' => 500));
        }

        return array('id' => $post_id);
    }


    public function delete_post($data) {
        $post_id = $data['id'];

        $result = wp_delete_post($post_id);

        if (!$result) {
            return new WP_Error('delete_failed', 'Failed to delete post', array('status' => 500));
        }

        return array('message' => 'Post deleted');
    }



    public function get_permissions_check() {
        return current_user_can('edit_posts');
    }


    function openai_generate_ai_text() {
        check_ajax_referer('aiml_generate_nonce', 'security');
        $prompt = sanitize_text_field($_POST['prompt']);

        $api_request_limit = 20;
        $ip_address        = $_SERVER['REMOTE_ADDR'];
        $transient_key     = 'api_request_count_' . md5($ip_address);
        $api_request_count = get_transient($transient_key);

        if ($api_request_count > $api_request_limit) {
            wp_send_json_error(array('text' => 'API request limit exceeded for your IP.'));
            exit;
        }

        $api_request_count = $api_request_count ? $api_request_count + 1 : 1;
        set_transient($transient_key, $api_request_count, 3600);

        $api_key = 'gAAAAABnDP-dzx-2niSU_NrTusY4p2YGHp9jmh4wh51GnFlq3iL2zkgPiHv6o1c2m_NeE6cr9Ma0W5BSPR6lOfHrQKQfdgaRAipnvvfqT1B5BSxgwI-IuLfFsjvYIRqB-TcjUdFqQPkt';
        $url = 'https://api.textcortex.com/v1/texts/expansions';

        $args = array(
            'timeout' => 10,
            'body' => json_encode(array(
                'formality' => 'default',
                'max_tokens' => 1500,
                'model' => 'claude-3-haiku',
                'n' => 1,
                'source_lang' => 'en',
                'target_lang' => 'en',
                'temperature' => null,
                'text' => $prompt
            )),
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            )
        );

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            wp_send_json_error(array('text' => 'Error connecting to API'));
            exit;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['data']['outputs'][0]['text'])) {
            wp_send_json_success(array('text' => $data['data']['outputs'][0]['text'], 'data_full' => $data));
        } else {
            wp_send_json_error(array('text' => 'No text generated',  'data_full' => $data));
        }

        wp_die();
    }
}
