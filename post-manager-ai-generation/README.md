# Post Manager OOP Plugin

This WordPress plugin provides functionality for managing posts via REST API and includes integration with an external AI service for text generation.

### Interface / Gif
https://github.com/DimaWide/wp-plugins/tree/main/assets/post-manager-ai-generation/post-manager-OOP.gif
![Example of use](https://github.com/DimaWide/wp-plugins/tree/main/assets/post-manager-ai-generation/post-manager-OOP.gif)

## Features:

### AI Text Generation
- **AJAX Integration with TextCortex API**:
  - **Action Hooks:** 
    - `wp_ajax_openai_generate_ai_text`
    - `wp_ajax_nopriv_openai_generate_ai_text`
  - **Description:** Generates AI-based text using the provided prompt.
  - **Rate Limiting:** Limits API requests to 2 per hour per IP address.
  - **Security:** Uses `check_ajax_referer` for nonce validation.


### REST API Endpoints
1. **Get Posts**
   - **Route:** `/post-manager-oop/v1/posts`
   - **Method:** `GET`
   - **Description:** Retrieves a paginated list of published posts with details such as title, content, featured image URL, and permalink.
   - **Permissions:** Requires the `edit_posts` capability.

2. **Get a Specific Post**
   - **Route:** `/post-manager-oop/v1/post/{id}`
   - **Method:** `GET`
   - **Description:** Fetches details of a specific post by ID.
   - **Permissions:** Requires the `edit_posts` capability.

3. **Create a Post**
   - **Route:** `/post-manager-oop/v1/post`
   - **Method:** `POST`
   - **Description:** Creates a new post, optionally uploading a featured image.
   - **Permissions:** Requires the `edit_posts` capability.

4. **Update a Post**
   - **Route:** `/post-manager-oop/v1/post-change/{id}`
   - **Method:** `POST`
   - **Description:** Updates an existing post's content or metadata.
   - **Permissions:** Open to all users (adjust as needed).

5. **Delete a Post**
   - **Route:** `/post-manager-oop/v1/post/{id}`
   - **Method:** `DELETE`
   - **Description:** Deletes a post by ID.
   - **Permissions:** Requires the `delete_posts` capability.


### Additional Features
- **File Uploads:** Allows uploading featured images using WordPress's media handling functions.
- **Pagination:** Supports paginated responses for retrieving posts.
- **User Permissions:** Ensures proper capability checks for all actions.
- **Error Handling:** Implements detailed error responses using `WP_Error`.

