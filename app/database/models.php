<?php
require_once __DIR__ . '/db_connection.php';

abstract class Model {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
        if ($this->db === null) {
            throw new Exception("Database connection is not established");
        }
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

    public function update($userId, $data) {
        $sql = "UPDATE Users SET username = ?, email = ?";
        $params = [$data['username'], $data['email']];
        
        if (isset($data['password'])) {
            $sql .= ", password = ?";
            $params[] = $data['password'];
        }
        
        $sql .= " WHERE user_id = ?";
        $params[] = $userId;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function getAllUsers() {
    $stmt = $this->db->prepare("SELECT user_id, username, email, user_level_id FROM users");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        public function __construct() {
        parent::__construct();
        error_log("RecipeModel initialized with DB: " . get_class($this->db));
    }

        public function update($recipeId, $data) {
        global $db;
        $fields = [];
        $params = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $params[] = $value;
        }
        $params[] = $recipeId;
        $sql = "UPDATE recipes SET " . implode(', ', $fields) . " WHERE recipe_id = ?";
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }

        public function delete($recipeId) {
        $stmt = $this->db->prepare("DELETE FROM Recipes WHERE recipe_id = ?");
        return $stmt->execute([$recipeId]);
    }

    public function updateStatus($recipeId, $status) {
        $stmt = $this->db->prepare("UPDATE Recipes SET status = ? WHERE recipe_id = ?");
        return $stmt->execute([$status, $recipeId]);
    }

    public function incrementViews($recipeId) {
        // First check if views column exists
        $stmt = $this->db->prepare("SHOW COLUMNS FROM Recipes LIKE 'views'");
        $stmt->execute();
        $columnExists = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($columnExists) {
            $stmt = $this->db->prepare("UPDATE Recipes SET views = views + 1 WHERE recipe_id = ?");
            $stmt->execute([$recipeId]);
        }
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
    private function getOrCreateIngredient($name) {
        $stmt = $this->db->prepare("SELECT ingredient_id FROM Ingredients WHERE ingredient_name = ?");
        $stmt->execute([$name]);
        $ing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($ing) {
            return $ing['ingredient_id'];
        }
        
        $stmt = $this->db->prepare("INSERT INTO Ingredients (ingredient_name) VALUES (?)");
        $stmt->execute([$name]);
        return $this->db->lastInsertId();
    }

        public function getIngredientsForRecipe($recipeId) {
        $ingredientModel = new IngredientModel();
        return $ingredientModel->getIngredientsForRecipe($recipeId);
    }

    public function saveIngredients($recipeId, $ingredientsData) {
        // First delete existing ingredients
        $stmt = $this->db->prepare("DELETE FROM Recipe_Ingredients WHERE recipe_id = ?");
        $stmt->execute([$recipeId]);
        
        // Insert new ingredients
        $ingredientModel = new IngredientModel();
        
        foreach ($ingredientsData as $ing) {
            $ingredientId = $ingredientModel->getOrCreateIngredient($ing['name']);
            
            $stmt = $this->db->prepare("INSERT INTO Recipe_Ingredients 
                (recipe_id, ingredient_id, quantity, unit_of_measure, notes)
                VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $recipeId,
                $ingredientId,
                $ing['quantity'],
                $ing['unit'],
                $ing['notes'] ?? ''
            ]);
        }
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

class shoppingListService extends Model {

    protected $db;

        public function __construct() {
        $this->db = Database::getInstance();
    }

    public function generateFromMealPlan($planId) {
        $sql = "SELECT 
                    i.ingredient_id, 
                    i.name AS ingredient_name,
                    SUM(ri.quantity) AS total_quantity,
                    ri.unit_of_measure,
                    c.category_name,
                    c.category_id
                FROM Meal_Plan_Items mpi
                JOIN Recipe_Ingredients ri ON mpi.recipe_id = ri.recipe_id
                JOIN Ingredients i ON ri.ingredient_id = i.ingredient_id
                LEFT JOIN Categories c ON i.category_id = c.category_id
                WHERE mpi.plan_id = ?
                GROUP BY i.ingredient_id, ri.unit_of_measure
                ORDER BY c.category_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$planId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function consolidateIngredients($items) {
        $consolidated = [];
        foreach ($items as $item) {
            $key = $item['ingredient_id'] . '|' . $item['unit_of_measure'];
            
            if (!isset($consolidated[$key])) {
                $consolidated[$key] = [
                    'ingredient_id' => $item['ingredient_id'],
                    'ingredient_name' => $item['ingredient_name'],
                    'unit_of_measure' => $item['unit_of_measure'],
                    'category_id' => $item['category_id'],
                    'category_name' => $item['category_name'],
                    'total_quantity' => 0
                ];
            }
            
            $consolidated[$key]['total_quantity'] += $item['total_quantity'];
        }
        
        return array_values($consolidated);
    }
}

class IngredientModel extends Model {
    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM Ingredients");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($ingredientId) {
        $stmt = $this->db->prepare("SELECT * FROM Ingredients WHERE ingredient_id = ?");
        $stmt->execute([$ingredientId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($ingredientName, $categoryId = null) {
        $stmt = $this->db->prepare("INSERT INTO Ingredients (ingredient_name, category_id) VALUES (?, ?)");
        return $stmt->execute([$ingredientName, $categoryId]);
    }

    public function update($ingredientId, $ingredientName, $categoryId = null) {
        $stmt = $this->db->prepare("UPDATE Ingredients SET ingredient_name = ?, category_id = ? WHERE ingredient_id = ?");
        return $stmt->execute([$ingredientName, $categoryId, $ingredientId]);
    }

    public function delete($ingredientId) {
        $stmt = $this->db->prepare("DELETE FROM Ingredients WHERE ingredient_id = ?");
        return $stmt->execute([$ingredientId]);
    }

        /**
     * Get all ingredients for a specific recipe.
     *
     * @param int $recipeId
     * @return array
     */


        public function getIngredientsForRecipe($recipeId) {
        $stmt = $this->db->prepare("SELECT ri.*, i.name as ingredient_name 
                                   FROM Recipe_Ingredients ri
                                   JOIN Ingredients i ON ri.ingredient_id = i.ingredient_id
                                   WHERE ri.recipe_id = ?");
        $stmt->execute([$recipeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getOrCreateIngredient($name) {
        $stmt = $this->db->prepare("SELECT ingredient_id FROM Ingredients WHERE name = ?");
        $stmt->execute([$name]);
        $ing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($ing) {
            return $ing['ingredient_id'];
        }
        
        $stmt = $this->db->prepare("INSERT INTO Ingredients (name) VALUES (?)");
        $stmt->execute([$name]);
        return $this->db->lastInsertId();
    }
}

class CategoryModel extends Model {
    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM Categories");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($categoryId) {
        $stmt = $this->db->prepare("SELECT * FROM Categories WHERE category_id = ?");
        $stmt->execute([$categoryId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($categoryName) {
        $stmt = $this->db->prepare("INSERT INTO Categories (category_name) VALUES (?)");
        return $stmt->execute([$categoryName]);
    }

    public function update($categoryId, $categoryName) {
        $stmt = $this->db->prepare("UPDATE Categories SET category_name = ? WHERE category_id = ?");
        return $stmt->execute([$categoryName, $categoryId]);
    }

    public function delete($categoryId) {
        $stmt = $this->db->prepare("DELETE FROM Categories WHERE category_id = ?");
        return $stmt->execute([$categoryId]);
    }
}

class FavoriteModel extends Model {
    public function isFavorite($userId, $recipeId) {
        $stmt = $this->db->prepare("SELECT 1 FROM User_Favorites WHERE user_id = ? AND recipe_id = ?");
        $stmt->execute([$userId, $recipeId]);
        return $stmt->fetchColumn() !== false;
    }

    public function addFavorite($userId, $recipeId) {
        $stmt = $this->db->prepare("INSERT IGNORE INTO User_Favorites (user_id, recipe_id) VALUES (?, ?)");
        return $stmt->execute([$userId, $recipeId]);
    }

    public function removeFavorite($userId, $recipeId) {
        $stmt = $this->db->prepare("DELETE FROM User_Favorites WHERE user_id = ? AND recipe_id = ?");
        return $stmt->execute([$userId, $recipeId]);
    }

    public function getUserFavorites($userId) {
        $stmt = $this->db->prepare("SELECT recipe_id FROM User_Favorites WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}