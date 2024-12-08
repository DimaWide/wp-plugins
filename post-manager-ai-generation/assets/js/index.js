import { setupEditor } from './modules/editor';
import { setupFormHandler } from './modules/formHandler';
import { setupPostHandler } from './modules/postHandler';
import { setupImagePreview } from './modules/imagePreview';
import { setupPagination } from './modules/pagination';

document.addEventListener('DOMContentLoaded', function () {
    setupEditor();
    setupFormHandler();
    setupPostHandler();
    setupImagePreview();
    setupPagination();

});