export function setupImagePreview() {
    
    document.getElementById('post-featured-image').addEventListener('change', function () {
        const file = this.files[0];
        const preview = document.getElementById('image-preview');

        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.innerHTML = `<img src="${e.target.result}" alt="Image Preview">`;
                 document.getElementById('image-preview').style.display = 'block'
            };
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = ''; 
               document.getElementById('image-preview').style.display = 'none'
        }
    });
}
