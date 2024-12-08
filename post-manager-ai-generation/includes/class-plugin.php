<?php
namespace PostManagerOOP;

use PostManagerOOP\Admin\AdminPage;
use PostManagerOOP\Api\PostApi;

class Plugin {
    public function init() {
        $admin_page = new AdminPage();
        $admin_page->init();

        $post_api = new PostApi();
        $post_api->init();
    }
}

