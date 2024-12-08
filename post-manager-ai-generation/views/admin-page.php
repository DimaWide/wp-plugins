<div class="sct-1-post-manager">
    <div class="sct-1-container">
        <div class="cmp-1-form-ai data-aiml-form">
            <form class="cmp1-form" id="aiml-form" >
                <label for="prompt" class="cmp1-form-label">Enter your prompt:</label>
                <input type="text" class="cmp1-form-input" id="prompt" name="prompt" required>
                <button type="submit" class="cmp1-form-submit">Generate Text</button>
            </form>

            <div class="cmp1-result hidden" id="result-container">
                <label for="result" class="cmp1-result-label">Generated Text:</label>
                <textarea id="result" class="cmp1-result-area" readonly></textarea>
                <button id="copy-button" class="cmp1-result-copy">Copy to Clipboard</button>
            </div>
        </div>
    </div>

    <div class="cmp-2-post-form data-post-manager">
        <div class="cmp2-container post-manager-container">
            <h1 class="cmp2-title">
                Manage Posts
            </h1>

            <form class="cmp2-form data-post-manager-form" id="post-form">
                <div class="cmp2-form-field">
                    <input type="hidden" id="post-id" class="cmp2-post-id" name="post-id" value="">
                    <label for="post-title" class="cmp2-title-label">Title:</label>
                    <input type="text" class="cmp2-title-input" id="post-title" name="post-title" required>
                </div>

                <div class="cmp2-form-field">
                    <label for="post-content" class="cmp2-content-label">Content:</label>
                    <?php
                    wp_editor('', 'post-content', [
                        'textarea_name' => 'post-content',
                        'editor_height' => 300,
                        'media_buttons' => true,
                    ]);
                    ?>
                </div>

                <div class="cmp2-form-field cmp2-b1">
                    <div class="cmp2-b1-file-input file-input-wrapper">
                        <label for="post-featured-image" class="cmp2-b1-label">Featured Image:</label>
                        <input type="file" class="cmp2-b1-input" id="post-featured-image" name="post-featured-image" accept="image/*">
                        <label for="post-featured-image" class="cmp2-b1-label-file custom-file-input">Choose file</label>
                    </div>

                    <div class="cmp2-b1-image-preview" id="image-preview"></div>
                </div>

                <div class="cmp2-form-btn-out">
                    <button type="submit" class="test cmp2-form-btn" id="save-post">Save Post</button>
                </div>
            </form>

            <div class="cmp-3-posts">
                <ul class="cmp3-list posts-list"></ul>

                <div class="cmp-4-pagination pagination"></div>
            </div>
        </div>