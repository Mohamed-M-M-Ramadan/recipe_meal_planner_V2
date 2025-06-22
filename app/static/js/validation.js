function validateLoginForm() {
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    
    if (!username || !password) {
        alert('Both username and password are required');
        return false;
    }
    return true;
}

function validateRecipeForm() {
    const title = document.getElementById('title').value;
    const prepTime = document.getElementById('prep_time').value;
    const cookTime = document.getElementById('cook_time').value;
    const servings = document.getElementById('servings').value;
    
    if (!title) {
        alert('Recipe title is required');
        return false;
    }
    
    if (isNaN(prepTime) || prepTime < 0) {
        alert('Prep time must be a positive number');
        return false;
    }
    
    if (isNaN(cookTime) || cookTime < 0) {
        alert('Cook time must be a positive number');
        return false;
    }
    
    if (isNaN(servings) || servings < 1) {
        alert('Servings must be at least 1');
        return false;
    }
    
    return true;
}

function validateRegistrationForm() {
    const username = document.getElementById('username').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('confirm_password').value;
    
    if (!username || username.length < 3) {
        alert('Username must be at least 3 characters');
        return false;
    }
    
    if (!email || !email.includes('@')) {
        alert('Valid email is required');
        return false;
    }
    
    if (!password || password.length < 6) {
        alert('Password must be at least 6 characters');
        return false;
    }
    
    if (password !== confirm) {
        alert('Passwords do not match');
        return false;
    }
    
    return true;
}