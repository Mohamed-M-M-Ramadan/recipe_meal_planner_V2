<?php
class MealPlanService {
    protected $db;
    
    public function __construct($db) {
        $this->db = $db;
    }

    public function createMealPlan($userId, $planName, $startDate, $endDate) {
        $stmt = $this->db->prepare("INSERT INTO Meal_Plans (user_id, plan_name, start_date, end_date) 
                                   VALUES (?, ?, ?, ?)");
        return $stmt->execute([$userId, $planName, $startDate, $endDate]);
    }

    public function addRecipeToPlan($planId, $recipeId, $dayOfWeek, $mealType) {
        $stmt = $this->db->prepare("INSERT INTO Meal_Plan_Items (plan_id, recipe_id, day_of_week, meal_type) 
                                   VALUES (?, ?, ?, ?)");
        return $stmt->execute([$planId, $recipeId, $dayOfWeek, $mealType]);
    }

    public function getMealPlan($planId) {
        $stmt = $this->db->prepare("SELECT * FROM Meal_Plans WHERE plan_id = ?");
        $stmt->execute([$planId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getPlanItems($planId) {
        $stmt = $this->db->prepare("SELECT * FROM Meal_Plan_Items WHERE plan_id = ?");
        $stmt->execute([$planId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}