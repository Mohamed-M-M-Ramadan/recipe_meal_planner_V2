<?php
class UserService {
    protected $db;
    
    public function __construct($db) {
        $this->db = $db;
    }

    public function addFavorite($userId, $recipeId) {
        $stmt = $this->db->prepare("INSERT INTO User_Favorites (user_id, recipe_id) VALUES (?, ?)");
        return $stmt->execute([$userId, $recipeId]);
    }

    public function removeFavorite($userId, $recipeId) {
        $stmt = $this->db->prepare("DELETE FROM User_Favorites WHERE user_id = ? AND recipe_id = ?");
        return $stmt->execute([$userId, $recipeId]);
    }

    public function getFavorites($userId) {
        $stmt = $this->db->prepare("SELECT r.* FROM Recipes r 
                                   JOIN User_Favorites uf ON r.recipe_id = uf.recipe_id
                                   WHERE uf.user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}