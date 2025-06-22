document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('recipe-image');
    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('image-preview').src = event.target.result;
                    document.getElementById('image-preview').style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Favorite button functionality
    document.querySelectorAll('.favorite-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const recipeId = this.dataset.id;
            const isFavorite = this.classList.contains('active');
            
            fetch(`/app/api/user_api.php?action=${isFavorite ? 'removeFavorite' : 'addFavorite'}`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({recipeId})
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    this.classList.toggle('active');
                    this.querySelector('i').textContent = isFavorite ? 'favorite_border' : 'favorite';
                }
            });
        });
    });
});