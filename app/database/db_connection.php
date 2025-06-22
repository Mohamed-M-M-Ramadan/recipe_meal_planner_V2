<?php
// app/database/db_connection.php

require_once __DIR__ . '/../config/settings.php'; // Include database settings

function get_db_connection() {
    static $conn = null; // Use static to store the connection and reuse it

    if ($conn === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch rows as associative arrays
            PDO::ATTR_EMULATE_PREPARES   => false,                  // Disable emulation for better security and performance
        ];

        try {
            $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
            // echo "Database connection successful!<br>"; // For testing purposes, can remove later
        } catch (PDOException $e) {
            // Log the error and display a user-friendly message
            error_log("Database connection failed: " . $e->getMessage());
            die("Could not connect to the database. Please try again later.");
        }
    }
    return $conn;
}

// You can test the connection by temporarily adding this line
get_db_connection();
// If no error message appears, the connection is successful.
?>