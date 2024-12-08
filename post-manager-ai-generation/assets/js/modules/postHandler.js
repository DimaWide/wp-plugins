import { setupPagination } from './pagination';

export function setupPostHandler() {
    let currentPage = 1;
    fetchPosts(currentPage);

    document.getElementById('post-form').addEventListener('submit', function (e) {
        e.preventDefault();
        let self = this
        self.querySelector('#save-post').classList.add('blink');
        self.querySelector('button').setAttribute('disabled', 'disabled')

        const postId = document.getElementById('post-id').value;
        const title = document.getElementById('post-title').value;
        const content = tinyMCE.get('post-content').getContent(); // Получение контента из WYSIWYG редактора
        const formData = new FormData(this); // Используем FormData для отправки файла

        formData.append('title', title);
        formData.append('content', content);

        let method = 'POST';
        let apiUrl = postManagerApi.api_url + (postId ? 'post-change/' + postId : 'post');

        fetch(apiUrl, {
            method: method,
            headers: {
                'X-WP-Nonce': postManagerApi.nonce,
            },
            body: formData,
        })
            .then(response => response.json())
            .then(data => {
                if (data.id) {
                    alert('Post saved successfully!');
                    clearForm();
                    fetchPosts();
                    document.getElementById('image-preview').innerHTML = '';
                    document.getElementById('image-preview').style.display = 'none'
                } else {
                    alert('Error saving post.');
                }

                self.querySelector('#save-post').classList.remove('blink');
                self.querySelector('button').removeAttribute('disabled')
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
    });

    function clearForm() {
        document.getElementById('post-id').value = '';
        document.getElementById('post-title').value = '';
        tinyMCE.get('post-content').setContent('');
        document.getElementById('post-featured-image').value = '';
    }
}

export function displayPosts(posts) {
    const postsList = document.querySelector('.posts-list');
    postsList.innerHTML = '';
    posts.forEach(post => {
        const postItem = document.createElement('li');
        postItem.classList.add('cmp3-item');
        postItem.classList.add(`post-${post.ID}`);

        const sanitizedContent = sanitizeContent(post.post_content);

        const trimmedContent = sanitizedContent.length > 200
            ? sanitizedContent.substring(0, 200) + '...'
            : sanitizedContent;

        postItem.innerHTML = `
               <div class="cmp3-item-content">
	             <div class="cmp3-item-img">
               ${post.featured_media ? `<img src="${post.featured_media}" alt="Featured Image" >` : ''}
               </div>
               <div class="cmp3-item-info">
                 <h3 class="cmp3-item-item-title">${post.post_title}</h3>
                  <div class="cmp3-item-desc">
         ${trimmedContent}
                 </div>
                </div>
                </div>
            
                <div class="cmp3-item-btns">
                 <button class="edit-post" data-id="${post.ID}">Edit</button>
                <button class="delete-post" data-id="${post.ID}">Delete</button>
                  <a href="${post.permalink}" class="view-post" target="_blank">View Post</a> <!-- Link to the post -->
                </div>
            `;
        postsList.appendChild(postItem);
    });

    document.querySelectorAll('.edit-post').forEach(button => {
        button.addEventListener('click', function () {
            const postId = this.getAttribute('data-id');
            editPost(postId);

            let element = document.querySelector('.cmp-2-post-form');
            if (element) {
                let elementPosition = element.getBoundingClientRect().top + window.pageYOffset - 60;
                window.scrollTo({
                    top: elementPosition,
                    behavior: "smooth"
                });
            }
        });
    });

    document.querySelectorAll('.delete-post').forEach(button => {
        button.addEventListener('click', function () {
            const postId = this.getAttribute('data-id');
            deletePost(postId);
        });
    });
}


function editPost(postId) {
    fetch(postManagerApi.api_url + 'post/' + postId, {
        method: 'GET',
        headers: {
            'X-WP-Nonce': postManagerApi.nonce
        }
    })
        .then(response => response.json())
        .then(post => {
            document.getElementById('post-id').value = post.id;
            document.getElementById('post-title').value = post.title;
            tinyMCE.get('post-content').setContent(post.content)
        })
        .catch(error => {
            alert('Error fetching post');
        });
}

function deletePost(postId) {
    if (!confirm('Are you sure you want to delete this post?')) {
        return;
    }

    fetch(postManagerApi.api_url + 'post/' + postId, {
        method: 'DELETE',
        headers: {
            'X-WP-Nonce': postManagerApi.nonce
        }
    })
        .then(response => response.json())
        .then(data => {
            console.log(data)
            alert('Post deleted successfully!');
            fetchPosts();
        })
        .catch(error => {
            console.log(error)
            alert('Error deleting post');
        });
}

function sanitizeContent(content) {
    return content.replace(/<(?!\/?p\b)[^>]+>/gi, '');
}

function fetchPosts(page = 1) {
    fetch(`${postManagerApi.api_url}posts?page=${page}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': postManagerApi.nonce,
        },
    })
        .then(response => response.json())
        .then(data => {
            displayPosts(data.posts);
            setupPagination(data.total_pages, data.current_page);
        })
        .catch(error => console.error('Error:', error));
}