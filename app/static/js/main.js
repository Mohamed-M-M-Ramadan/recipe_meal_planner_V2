// API Client Functions
function apiRequest(endpoint, method, data, callback) {
    fetch(`/app/api/${endpoint}`, {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(callback)
    .catch(error => {
        console.error('API Error:', error);
        alert('An error occurred. Please try again.');
    });
}

// DOM Ready Functions
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize favorite buttons
    document.querySelectorAll('.favorite-btn').forEach(btn => {
        btn.addEventListener('click', toggleFavorite);
    });
    
    // Initialize image upload previews
    document.querySelectorAll('.image-upload').forEach(input => {
        input.addEventListener('change', previewImage);
    });
});

// Toggle Favorite Recipe
function toggleFavorite(e) {
    const btn = e.currentTarget;
    const recipeId = btn.dataset.id;
    const isFavorite = btn.classList.contains('active');
    
    apiRequest('user_api.php', 'POST', {
        action: isFavorite ? 'removeFavorite' : 'addFavorite',
        recipeId: recipeId
    }, function(response) {
        if (response.status === 'success') {
            btn.classList.toggle('active');
            btn.querySelector('i').textContent = isFavorite ? 'favorite_border' : 'favorite';
        }
    });
}

// Image Preview
function previewImage(e) {
    const input = e.target;
    const previewId = input.dataset.preview;
    const preview = document.getElementById(previewId);
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}

// Print Shopping List
function printShoppingList() {
    window.print();
}