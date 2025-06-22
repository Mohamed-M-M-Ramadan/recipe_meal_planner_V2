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
        return $stmt->execute([$username, $email, password_hash($password, PASSWORD_BCRYPT), $userLevelId]);
    }

    public function findByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM Users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

class RecipeModel extends Model {
    public function create($userId, $title, $description, $instructions, $prepTime, $cookTime, $servings, $status = 'private') {
        $stmt = $this->db->prepare("INSERT INTO Recipes (user_id, title, description, instructions, prep_time, cook_time, servings, status) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$userId, $title, $description, $instructions, $prepTime, $cookTime, $servings, $status]);
    }

    public function getPublicRecipes() {
        $stmt = $this->db->prepare("SELECT * FROM Recipes WHERE status = 'public'");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}   

// Additional models for Ingredients, MealPlans, etc. would follow similar patterns

