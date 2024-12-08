/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./assets/js/modules/editor.js":
/*!*************************************!*\
  !*** ./assets/js/modules/editor.js ***!
  \*************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   setupEditor: () => (/* binding */ setupEditor)
/* harmony export */ });
var _templateObject;
function _taggedTemplateLiteral(e, t) { return t || (t = e.slice(0)), Object.freeze(Object.defineProperties(e, { raw: { value: Object.freeze(t) } })); }
function setupEditor() {
  tinymce.init({
    selector: '#post-content',
    setup: function setup(editor) {
      editor.on('init', function () {
        console.log('TinyMCE is initialized');
      });
    }
  });
}
""(_templateObject || (_templateObject = _taggedTemplateLiteral([""])));

/***/ }),

/***/ "./assets/js/modules/formHandler.js":
/*!******************************************!*\
  !*** ./assets/js/modules/formHandler.js ***!
  \******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   setupFormHandler: () => (/* binding */ setupFormHandler)
/* harmony export */ });
function setupFormHandler() {
  document.getElementById('aiml-form').addEventListener('submit', function (e) {
    e.preventDefault();
    var section = document.querySelector('.data-aiml-form');
    var prompt = document.getElementById('prompt').value;
    section.querySelector('button').classList.add('blink');
    section.querySelector('button').setAttribute('disabled', 'disabled');
    fetch(postManagerApi.ajax_url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
      },
      body: new URLSearchParams({
        action: 'openai_generate_ai_text',
        prompt: prompt,
        security: postManagerApi.nonce_02
      })
    }).then(function (response) {
      return response.json();
    }).then(function (data) {
      var text = data.data.text;
      if (data.success) {
        var lines = text.split('\n');
        lines.splice(0, 2);
        text = lines.join('\n');
      }

      // document.getElementById('prompt').value = '';
      document.getElementById('result').value = text;
      section.querySelector('button').classList.remove('blink');
      section.querySelector('button').removeAttribute('disabled');
      document.getElementById('result-container').classList.remove('hidden');
      console.log(data);
    })["catch"](function (error) {
      return console.error('Error:', error);
    });
  });
  document.getElementById('copy-button').addEventListener('click', function () {
    var resultTextArea = document.getElementById('result');
    resultTextArea.select();
    document.execCommand('copy');
    alert('Text copied to clipboard!');
  });
}

/***/ }),

/***/ "./assets/js/modules/imagePreview.js":
/*!*******************************************!*\
  !*** ./assets/js/modules/imagePreview.js ***!
  \*******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   setupImagePreview: () => (/* binding */ setupImagePreview)
/* harmony export */ });
function setupImagePreview() {
  document.getElementById('post-featured-image').addEventListener('change', function () {
    var file = this.files[0];
    var preview = document.getElementById('image-preview');
    if (file) {
      var reader = new FileReader();
      reader.onload = function (e) {
        preview.innerHTML = "<img src=\"".concat(e.target.result, "\" alt=\"Image Preview\">");
        document.getElementById('image-preview').style.display = 'block';
      };
      reader.readAsDataURL(file);
    } else {
      preview.innerHTML = '';
      document.getElementById('image-preview').style.display = 'none';
    }
  });
}

/***/ }),

/***/ "./assets/js/modules/pagination.js":
/*!*****************************************!*\
  !*** ./assets/js/modules/pagination.js ***!
  \*****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   setupPagination: () => (/* binding */ setupPagination)
/* harmony export */ });
/* harmony import */ var _postHandler__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./postHandler */ "./assets/js/modules/postHandler.js");

function setupPagination(totalPages) {
  var currentPage = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 1;
  if (totalPages < 2) {
    return;
  }
  var paginationContainer = document.querySelector('.pagination');
  paginationContainer.innerHTML = '';
  var minPagesToShow = Math.min(totalPages, 4);
  if (currentPage > 1) {
    var prevButton = document.createElement('button');
    prevButton.textContent = '←';
    prevButton.className = 'page-button';
    prevButton.addEventListener('click', function () {
      currentPage--;
      fetchPosts(currentPage);
      scrollToSection();
    });
    paginationContainer.appendChild(prevButton);
  }
  if (totalPages > 1 && currentPage > 3) {
    var firstPageButton = document.createElement('button');
    firstPageButton.textContent = '1';
    firstPageButton.className = 'page-button';
    firstPageButton.disabled = currentPage === 1;
    firstPageButton.addEventListener('click', function () {
      currentPage = 1;
      fetchPosts(currentPage);
      scrollToSection();
    });
    paginationContainer.appendChild(firstPageButton);
    if (currentPage > 3 && totalPages > 4) {
      var ellipsis = document.createElement('span');
      ellipsis.className = 'page-dots';
      ellipsis.textContent = '...';
      paginationContainer.appendChild(ellipsis);
    }
  }
  var startPage, endPage;
  if (totalPages <= 4) {
    startPage = 2;
    endPage = totalPages;
  } else {
    startPage = Math.max(2, currentPage - 1);
    endPage = Math.min(totalPages - 1, currentPage + 1);
    if (endPage - startPage < 2) {
      if (startPage === 2) {
        endPage = 4;
      } else {
        startPage = totalPages - 3;
      }
    }
  }
  var _loop = function _loop(i) {
    var pageButton = document.createElement('button');
    pageButton.textContent = i;
    pageButton.className = 'page-button';
    pageButton.disabled = i === currentPage;
    if (i == currentPage) {
      pageButton.classList.add('active');
    }
    pageButton.addEventListener('click', function () {
      currentPage = i;
      fetchPosts(currentPage);
      scrollToSection();
    });
    paginationContainer.appendChild(pageButton);
  };
  for (var i = startPage - 1; i <= endPage; i++) {
    _loop(i);
  }
  if (totalPages > 4 && currentPage < totalPages - 2) {
    var _ellipsis = document.createElement('span');
    _ellipsis.className = 'page-dots';
    _ellipsis.textContent = '...';
    paginationContainer.appendChild(_ellipsis);
  }
  if (totalPages > 1 && currentPage > 5) {
    var lastPageButton = document.createElement('button');
    lastPageButton.textContent = totalPages;
    lastPageButton.className = 'page-button';
    lastPageButton.disabled = currentPage === totalPages;
    lastPageButton.addEventListener('click', function () {
      currentPage = totalPages;
      fetchPosts(currentPage);
      scrollToSection();
    });
    paginationContainer.appendChild(lastPageButton);
  }
  if (currentPage < totalPages) {
    var nextButton = document.createElement('button');
    nextButton.textContent = '→';
    nextButton.className = 'page-button';
    nextButton.addEventListener('click', function () {
      currentPage++;
      fetchPosts(currentPage);
      scrollToSection();
    });
    paginationContainer.appendChild(nextButton);
  }
  function scrollToSection() {
    var element = document.querySelector('.cmp-3-posts');
    if (element) {
      var elementPosition = element.getBoundingClientRect().top + window.pageYOffset - 50;
      window.scrollTo({
        top: elementPosition,
        behavior: "smooth"
      });
    }
  }
}
function fetchPosts() {
  var page = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 1;
  console.log(page);
  fetch("".concat(postManagerApi.api_url, "posts?page=").concat(page), {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json',
      'X-WP-Nonce': postManagerApi.nonce
    }
  }).then(function (response) {
    return response.json();
  }).then(function (data) {
    (0,_postHandler__WEBPACK_IMPORTED_MODULE_0__.displayPosts)(data.posts);
    setupPagination(data.total_pages, data.current_page);
  })["catch"](function (error) {
    return console.error('Error:', error);
  });
}

/***/ }),

/***/ "./assets/js/modules/postHandler.js":
/*!******************************************!*\
  !*** ./assets/js/modules/postHandler.js ***!
  \******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   displayPosts: () => (/* binding */ displayPosts),
/* harmony export */   setupPostHandler: () => (/* binding */ setupPostHandler)
/* harmony export */ });
/* harmony import */ var _pagination__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./pagination */ "./assets/js/modules/pagination.js");

function setupPostHandler() {
  var currentPage = 1;
  fetchPosts(currentPage);
  document.getElementById('post-form').addEventListener('submit', function (e) {
    e.preventDefault();
    var self = this;
    self.querySelector('#save-post').classList.add('blink');
    self.querySelector('button').setAttribute('disabled', 'disabled');
    var postId = document.getElementById('post-id').value;
    var title = document.getElementById('post-title').value;
    var content = tinyMCE.get('post-content').getContent(); // Получение контента из WYSIWYG редактора
    var formData = new FormData(this); // Используем FormData для отправки файла

    formData.append('title', title);
    formData.append('content', content);
    var method = 'POST';
    var apiUrl = postManagerApi.api_url + (postId ? 'post-change/' + postId : 'post');
    fetch(apiUrl, {
      method: method,
      headers: {
        'X-WP-Nonce': postManagerApi.nonce
      },
      body: formData
    }).then(function (response) {
      return response.json();
    }).then(function (data) {
      if (data.id) {
        alert('Post saved successfully!');
        clearForm();
        fetchPosts();
        document.getElementById('image-preview').innerHTML = '';
        document.getElementById('image-preview').style.display = 'none';
      } else {
        alert('Error saving post.');
      }
      self.querySelector('#save-post').classList.remove('blink');
      self.querySelector('button').removeAttribute('disabled');
    })["catch"](function (error) {
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
function displayPosts(posts) {
  var postsList = document.querySelector('.posts-list');
  postsList.innerHTML = '';
  posts.forEach(function (post) {
    var postItem = document.createElement('li');
    postItem.classList.add('cmp3-item');
    postItem.classList.add("post-".concat(post.ID));
    var sanitizedContent = sanitizeContent(post.post_content);
    var trimmedContent = sanitizedContent.length > 200 ? sanitizedContent.substring(0, 200) + '...' : sanitizedContent;
    postItem.innerHTML = "\n               <div class=\"cmp3-item-content\">\n\t             <div class=\"cmp3-item-img\">\n               ".concat(post.featured_media ? "<img src=\"".concat(post.featured_media, "\" alt=\"Featured Image\" >") : '', "\n               </div>\n               <div class=\"cmp3-item-info\">\n                 <h3 class=\"cmp3-item-item-title\">").concat(post.post_title, "</h3>\n                  <div class=\"cmp3-item-desc\">\n         ").concat(trimmedContent, "\n                 </div>\n                </div>\n                </div>\n            \n                <div class=\"cmp3-item-btns\">\n                 <button class=\"edit-post\" data-id=\"").concat(post.ID, "\">Edit</button>\n                <button class=\"delete-post\" data-id=\"").concat(post.ID, "\">Delete</button>\n                  <a href=\"").concat(post.permalink, "\" class=\"view-post\" target=\"_blank\">View Post</a> <!-- Link to the post -->\n                </div>\n            ");
    postsList.appendChild(postItem);
  });
  document.querySelectorAll('.edit-post').forEach(function (button) {
    button.addEventListener('click', function () {
      var postId = this.getAttribute('data-id');
      editPost(postId);
      var element = document.querySelector('.cmp-2-post-form');
      if (element) {
        var elementPosition = element.getBoundingClientRect().top + window.pageYOffset - 60;
        window.scrollTo({
          top: elementPosition,
          behavior: "smooth"
        });
      }
    });
  });
  document.querySelectorAll('.delete-post').forEach(function (button) {
    button.addEventListener('click', function () {
      var postId = this.getAttribute('data-id');
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
  }).then(function (response) {
    return response.json();
  }).then(function (post) {
    document.getElementById('post-id').value = post.id;
    document.getElementById('post-title').value = post.title;
    tinyMCE.get('post-content').setContent(post.content);
  })["catch"](function (error) {
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
  }).then(function (response) {
    return response.json();
  }).then(function (data) {
    console.log(data);
    alert('Post deleted successfully!');
    fetchPosts();
  })["catch"](function (error) {
    console.log(error);
    alert('Error deleting post');
  });
}
function sanitizeContent(content) {
  return content.replace(/<(?!\/?p\b)[^>]+>/gi, '');
}
function fetchPosts() {
  var page = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 1;
  fetch("".concat(postManagerApi.api_url, "posts?page=").concat(page), {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json',
      'X-WP-Nonce': postManagerApi.nonce
    }
  }).then(function (response) {
    return response.json();
  }).then(function (data) {
    displayPosts(data.posts);
    (0,_pagination__WEBPACK_IMPORTED_MODULE_0__.setupPagination)(data.total_pages, data.current_page);
  })["catch"](function (error) {
    return console.error('Error:', error);
  });
}

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
/*!****************************!*\
  !*** ./assets/js/index.js ***!
  \****************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _modules_editor__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./modules/editor */ "./assets/js/modules/editor.js");
/* harmony import */ var _modules_formHandler__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./modules/formHandler */ "./assets/js/modules/formHandler.js");
/* harmony import */ var _modules_postHandler__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./modules/postHandler */ "./assets/js/modules/postHandler.js");
/* harmony import */ var _modules_imagePreview__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./modules/imagePreview */ "./assets/js/modules/imagePreview.js");
/* harmony import */ var _modules_pagination__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./modules/pagination */ "./assets/js/modules/pagination.js");





document.addEventListener('DOMContentLoaded', function () {
  (0,_modules_editor__WEBPACK_IMPORTED_MODULE_0__.setupEditor)();
  (0,_modules_formHandler__WEBPACK_IMPORTED_MODULE_1__.setupFormHandler)();
  (0,_modules_postHandler__WEBPACK_IMPORTED_MODULE_2__.setupPostHandler)();
  (0,_modules_imagePreview__WEBPACK_IMPORTED_MODULE_3__.setupImagePreview)();
  (0,_modules_pagination__WEBPACK_IMPORTED_MODULE_4__.setupPagination)();
});
})();

/******/ })()
;
//# sourceMappingURL=bundle.js.map