<?php
class ShoppingListService {
    protected $db;
    
    public function __construct($db) {
        $this->db = $db;
    }

    public function generateFromMealPlan($planId) {
        $sql = "SELECT 
                    i.ingredient_name, 
                    SUM(ri.quantity) AS total_quantity,
                    ri.unit_of_measure,
                    i.category_id
                FROM Meal_Plan_Items mpi
                JOIN Recipe_Ingredients ri ON mpi.recipe_id = ri.recipe_id
                JOIN Ingredients i ON ri.ingredient_id = i.ingredient_id
                WHERE mpi.plan_id = ?
                GROUP BY i.ingredient_id, ri.unit_of_measure
                ORDER BY i.category_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$planId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function consolidateIngredients($items) {
        $consolidated = [];
        foreach ($items as $item) {
            $key = $item['ingredient_name'] . '|' . $item['unit_of_measure'];
            if (!isset($consolidated[$key])) {
                $consolidated[$key] = $item;
            } else {
                $consolidated[$key]['total_quantity'] += $item['total_quantity'];
            }
        }
        return array_values($consolidated);
    }
}