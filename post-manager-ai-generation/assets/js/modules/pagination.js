import { displayPosts } from './postHandler';

export function setupPagination(totalPages, currentPage = 1) {

    if (totalPages < 2) {
        return
    }

    const paginationContainer = document.querySelector('.pagination');
    paginationContainer.innerHTML = '';

    const minPagesToShow = Math.min(totalPages, 4);

    if (currentPage > 1) {
        const prevButton = document.createElement('button');
        prevButton.textContent = '←';
        prevButton.className = 'page-button';
        prevButton.addEventListener('click', () => {
            currentPage--;
            fetchPosts(currentPage);
            scrollToSection()
        });
        paginationContainer.appendChild(prevButton);
    }

    if (totalPages > 1 && currentPage > 3) {
        const firstPageButton = document.createElement('button');
        firstPageButton.textContent = '1';
        firstPageButton.className = 'page-button';
        firstPageButton.disabled = (currentPage === 1);
        firstPageButton.addEventListener('click', () => {
            currentPage = 1;
            fetchPosts(currentPage);
            scrollToSection()
        });
        paginationContainer.appendChild(firstPageButton);

        if (currentPage > 3 && totalPages > 4) {
            const ellipsis = document.createElement('span');
            ellipsis.className = 'page-dots'
            ellipsis.textContent = '...';
            paginationContainer.appendChild(ellipsis);
        }
    }

    let startPage, endPage;
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

    for (let i = startPage - 1; i <= endPage; i++) {
        const pageButton = document.createElement('button');
        pageButton.textContent = i;
        pageButton.className = 'page-button';
        pageButton.disabled = (i === currentPage);

        if (i == currentPage) {
            pageButton.classList.add('active');
        }

        pageButton.addEventListener('click', () => {
            currentPage = i;
            fetchPosts(currentPage);
            scrollToSection()
        });
        paginationContainer.appendChild(pageButton);
    }

    if (totalPages > 4 && currentPage < totalPages - 2) {
        const ellipsis = document.createElement('span');
        ellipsis.className = 'page-dots'
        ellipsis.textContent = '...';
        paginationContainer.appendChild(ellipsis);
    }

    if (totalPages > 1 && currentPage > 5) {
        const lastPageButton = document.createElement('button');
        lastPageButton.textContent = totalPages;
        lastPageButton.className = 'page-button';
        lastPageButton.disabled = (currentPage === totalPages);
        lastPageButton.addEventListener('click', () => {
            currentPage = totalPages;
            fetchPosts(currentPage);
            scrollToSection()
        });
        paginationContainer.appendChild(lastPageButton);
    }

    if (currentPage < totalPages) {
        const nextButton = document.createElement('button');
        nextButton.textContent = '→';
        nextButton.className = 'page-button';
        nextButton.addEventListener('click', () => {
            currentPage++;
            fetchPosts(currentPage);
            scrollToSection()
        });
        paginationContainer.appendChild(nextButton);
    }


    function scrollToSection() {
        let element = document.querySelector('.cmp-3-posts');
        if (element) {
            let elementPosition = element.getBoundingClientRect().top + window.pageYOffset - 50;
            window.scrollTo({
                top: elementPosition,
                behavior: "smooth"
            });
        }
    }
}


function fetchPosts(page = 1) {
    console.log(page)
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
