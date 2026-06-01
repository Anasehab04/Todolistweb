<?php
/**
 * Migration: Add due_time column to tasks table
 * Run this file ONCE in your browser or via CLI:  php migrate.php
 */
require_once 'config.php';

try {
    $pdo->exec("ALTER TABLE tasks ADD COLUMN due_time TIME NULL AFTER due_date");
    echo "✅ Migration complete — 'due_time' column added to tasks table.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "ℹ️ Column 'due_time' already exists. Nothing to do.";
    } else {
        echo "❌ Migration failed: " . $e->getMessage();
    }
}
?>
