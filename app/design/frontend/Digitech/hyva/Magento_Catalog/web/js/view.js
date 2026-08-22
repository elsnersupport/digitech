document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('category-view-container');
    if (container && container.innerHTML.trim().length === 0) {
        container.style.display = 'none';
    }
});
