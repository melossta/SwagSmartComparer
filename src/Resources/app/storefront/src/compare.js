document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('compare-container');
    if (!container) return; // not on compare page

    const searchUrl = container.dataset.searchUrl;
    const addUrl = container.dataset.addUrl;

    const searchInput = document.getElementById('compare-search');
    const hiddenSelect = document.getElementById('compare-select');
    const suggestions = document.getElementById('compare-suggestions');
    const form = document.getElementById('compare-add-form');
    let timer = null;

    searchInput.addEventListener('input', function () {
        const query = this.value.trim();
        hiddenSelect.value = '';

        if (timer) clearTimeout(timer);
        if (!query) {
            suggestions.innerHTML = '';
            return;
        }

        timer = setTimeout(() => {
            fetch(searchUrl + '?q=' + encodeURIComponent(query))
                .then(response => response.json())
                .then(data => {
                    suggestions.innerHTML = '';
                    data.forEach(product => {
                        const item = document.createElement('button');
                        item.type = 'button';
                        item.className = 'list-group-item list-group-item-action';
                        item.textContent = product.name + (product.productNumber ? ` (${product.productNumber})` : '');
                        item.dataset.id = product.id;
                        item.addEventListener('click', function () {
                            searchInput.value = this.textContent;
                            hiddenSelect.value = this.dataset.id;
                            suggestions.innerHTML = '';
                        });
                        suggestions.appendChild(item);
                    });
                });
        }, 300);
    });

    form.addEventListener('submit', function (e) {
        if (!hiddenSelect.value) {
            e.preventDefault();
            return;
        }
        form.action = addUrl.replace('REPLACE', hiddenSelect.value);
    });

    document.addEventListener('click', function (e) {
        if (!suggestions.contains(e.target) && e.target !== searchInput) {
            suggestions.innerHTML = '';
        }
    });
});
