document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-favorite]');
    if (!button) return;
    button.disabled = true;
    try {
        const response = await fetch('favorite.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({recipe_id: button.dataset.favorite, csrf: button.dataset.csrf})
        });
        const result = await response.json();
        if (!response.ok) throw new Error(result.message || 'Unable to update favorite.');
        button.classList.toggle('saved', result.saved);
        button.textContent = result.saved ? '♥ Saved' : '♡ Save favorite';
        const count = document.querySelector('[data-favorite-count]');
        if (count) count.textContent = result.count;
    } catch (error) {
        alert(error.message);
    } finally {
        button.disabled = false;
    }
});

const ingredientList = document.querySelector('[data-ingredients]');
if (ingredientList) {
    document.querySelector('[data-add-ingredient]').addEventListener('click', () => {
        const row = document.createElement('div');
        row.className = 'ingredient-row';
        row.innerHTML = '<input name="quantity[]" placeholder="Quantity" required><input name="ingredient[]" placeholder="Ingredient" required><button type="button" class="button button-secondary remove-ingredient">Remove</button>';
        ingredientList.appendChild(row);
    });
    ingredientList.addEventListener('click', (event) => {
        if (event.target.closest('.remove-ingredient') && ingredientList.children.length > 1) {
            event.target.closest('.ingredient-row').remove();
        }
    });
}
