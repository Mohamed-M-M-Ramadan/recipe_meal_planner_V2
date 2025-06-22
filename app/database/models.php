<?php
require_once __DIR__ . '/db_connection.php';

abstract class Model {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
}

class UserModel extends Model {
    public function create($username, $email, $password, $userLevelId = 2) {
        $stmt = $this->db->prepare("INSERT INTO Users (username, email, password, user_level_id) 
                                   VALUES (?, ?, ?, ?)");
        return $stmt->execute([
            $username, 
            $email, 
            password_hash($password, PASSWORD_BCRYPT), 
            $userLevelId
        ]);
    }

    public function findByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM Users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM Users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateRememberToken($userId, $token) {
        $stmt = $this->db->prepare("UPDATE Users SET remember_token = ? WHERE user_id = ?");
        return $stmt->execute([$token, $userId]);
    }

    public function clearRememberToken($userId) {
        $stmt = $this->db->prepare("UPDATE Users SET remember_token = NULL WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }
    public function getUserById($userId) {
        $stmt = $this->db->prepare("SELECT * FROM Users WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}




class UserService {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getFavorites($userId) {
        $stmt = $this->db->prepare("SELECT r.* 
                                   FROM Recipes r
                                   JOIN User_Favorites uf ON r.recipe_id = uf.recipe_id
                                   WHERE uf.user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

class RecipeModel extends Model {
    public function create($userId, $title, $description, $instructions, $prepTime, $cookTime, $servings, $status = 'private') {
        $stmt = $this->db->prepare("INSERT INTO Recipes (user_id, title, description, instructions, prep_time, cook_time, servings, status) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$userId, $title, $description, $instructions, $prepTime, $cookTime, $servings, $status]);
    }

    public function updateStatus($recipeId, $status) {
        $stmt = $this->db->prepare("UPDATE Recipes SET status = ? WHERE recipe_id = ?");
        return $stmt->execute([$status, $recipeId]);
    }

    public function delete($recipeId) {
        $stmt = $this->db->prepare("DELETE FROM Recipes WHERE recipe_id = ?");
        return $stmt->execute([$recipeId]);
    }

    public function getRecipesByStatus($status) {
        $stmt = $this->db->prepare("SELECT * FROM Recipes WHERE status = ?");
        $stmt->execute([$status]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecipeById($recipeId) {
        $stmt = $this->db->prepare("SELECT * FROM Recipes WHERE recipe_id = ?");
        $stmt->execute([$recipeId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserRecipes($userId) {
        $stmt = $this->db->prepare("SELECT * FROM Recipes WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPublicRecipes() {
        $stmt = $this->db->prepare("SELECT * FROM Recipes WHERE status = 'public'");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}   

class MealPlanModel extends Model {
    public function create($userId, $name, $startDate, $endDate) {
        $stmt = $this->db->prepare("INSERT INTO meal_plans (user_id, name, start_date, end_date) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$userId, $name, $startDate, $endDate]);
    }

    public function getUserMealPlans($userId) {
        $stmt = $this->db->prepare("SELECT * FROM Meal_Plans WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMealPlanById($id) {
        $stmt = $this->db->prepare("SELECT * FROM meal_plans WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $name, $startDate, $endDate) {
        $stmt = $this->db->prepare("UPDATE meal_plans SET name = ?, start_date = ?, end_date = ? WHERE id = ?");
        return $stmt->execute([$name, $startDate, $endDate, $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM meal_plans WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

// Additional models for Ingredients, MealPlans, etc. would follow similar patterns

