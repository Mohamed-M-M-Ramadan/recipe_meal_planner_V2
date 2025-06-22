<?php
// app/database/models.php

require_once __DIR__ . '/db_connection.php'; // Include the database connection

/**
 * Base Model class to handle common database operations.
 * All specific models (User, Recipe, etc.) will extend this.
 */
class BaseModel {
    protected $table;
    protected $db;

    public function __construct($table) {
        $this->table = $table;
        $this->db = get_db_connection();
    }

    /**
     * Finds a record by its primary key.
     * @param int $id The ID of the record.
     * @param string $idColumn The name of the ID column (e.g., 'user_id', 'recipe_id').
     * @return array|false The record as an associative array, or false if not found.
     */
    public function find($id, $idColumn) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$idColumn} = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Gets all records from the table.
     * @return array An array of associative arrays, each representing a record.
     */
    public function all() {
        $stmt = $this->db->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll();
    }

    // More generic CRUD methods (insert, update, delete) can be added here
    // or specifically in child classes if they require unique logic.
}

/**
 * User Model
 */
class User extends BaseModel {
    public function __construct() {
        parent::__construct('Users');
    }

    /**
     * Finds a user by username.
     * @param string $username The username.
     * @return array|false The user record, or false if not found.
     */
    public function findByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM Users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Finds a user by email.
     * @param string $email The email.
     * @return array|false The user record, or false if not found.
     */
    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM Users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Creates a new user.
     * @param string $username
     * @param string $password (plain text, will be hashed)
     * @param string $email
     * @param int $user_level_id
     * @param string|null $dietary_preferences
     * @return int|false Inserted user ID on success, false on failure.
     */
    public function create($username, $password, $email, $user_level_id, $dietary_preferences = null) {
        // Hash the password before storing
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare(
            "INSERT INTO Users (username, password, email, user_level_id, dietary_preferences)
             VALUES (:username, :password, :email, :user_level_id, :dietary_preferences)"
        );
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':user_level_id', $user_level_id, PDO::PARAM_INT);
        $stmt->bindParam(':dietary_preferences', $dietary_preferences);

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Verifies user password.
     * @param string $username
     * @param string $password
     * @return array|false User data if credentials are valid, false otherwise.
     */
    public function verifyPassword($username, $password) {
        $user = $this->findByUsername($username);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }
}

/**
 * UserLevel Model
 */
class UserLevel extends BaseModel {
    public function __construct() {
        parent::__construct('User_Levels');
    }

    /**
     * Gets a user level by name.
     * @param string $levelName The name of the user level.
     * @return array|false The user level record, or false if not found.
     */
    public function findByName($levelName) {
        $stmt = $this->db->prepare("SELECT * FROM User_Levels WHERE level_name = :level_name");
        $stmt->bindParam(':level_name', $levelName);
        $stmt->execute();
        return $stmt->fetch();
    }
}

// You would add more model classes here for Recipe, Ingredient, MealPlan, etc.
// Example for Category:
class Category extends BaseModel {
    public function __construct() {
        parent::__construct('Categories');
    }

    public function findByName($name) {
        $stmt = $this->db->prepare("SELECT * FROM Categories WHERE category_name = :name");
        $stmt->bindParam(':name', $name);
        $stmt->execute();
        return $stmt->fetch();
    }
}

// Example for Ingredient:
class Ingredient extends BaseModel {
    public function __construct() {
        parent::__construct('Ingredients');
    }

    // You can add more specific methods here, like searchByName, createIngredient, etc.
}

// Example for Recipe:
class Recipe extends BaseModel {
    public function __construct() {
        parent::__construct('Recipes');
    }

    // Add methods specific to recipes, e.g., getPublicRecipes, searchRecipes, addRecipe, etc.
}

// You can create instances to test basic functionality if needed
/*
// Example Test:
$userModel = new User();
$adminLevel = (new UserLevel())->findByName('Admin');
if ($adminLevel) {
    echo "Admin User Level ID: " . $adminLevel['user_level_id'] . "<br>";
    $newUserId = $userModel->create('admin_user', 'adminpass', 'admin@example.com', $adminLevel['user_level_id'], 'none');
    if ($newUserId) {
        echo "Admin user created with ID: " . $newUserId . "<br>";
    } else {
        echo "Failed to create admin user.<br>";
    }
} else {
    echo "Admin user level not found.<br>";
}

$registeredLevel = (new UserLevel())->findByName('Registered User');
if ($registeredLevel) {
    $newUserId = $userModel->create('test_user', 'testpass', 'test@example.com', $registeredLevel['user_level_id'], 'Vegetarian');
    if ($newUserId) {
        echo "Test user created with ID: " . $newUserId . "<br>";
    } else {
        echo "Failed to create test user.<br>";
    }
} else {
    echo "Registered User level not found.<br>";
}
*/
?>