export function setupEditor() {
    tinymce.init({
        selector: '#post-content',
        setup: function (editor) {
            editor.on('init', function () {
                console.log('TinyMCE is initialized');
            });
        }
    });
}
````