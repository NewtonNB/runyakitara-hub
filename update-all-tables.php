<?php
/**
 * Comprehensive Database Schema Update
 * This script checks and updates all tables to match the expected schema
 */

require_once 'config/database.php';

function checkAndAddColumn($db, $table, $column, $type, $default = null) {
    $stmt = $db->query("PRAGMA table_info($table)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $hasColumn = false;
    foreach ($columns as $col) {
        if ($col['name'] === $column) {
            $hasColumn = true;
            break;
        }
    }
    
    if (!$hasColumn) {
        echo "  Adding '$column' column to $table...\n";
        $sql = "ALTER TABLE $table ADD COLUMN $column $type";
        if ($default !== null) {
            $sql .= " DEFAULT $default";
        }
        $db->exec($sql);
        echo "  ✓ Column added successfully!\n";
        return true;
    }
    return false;
}

function renameColumn($db, $table, $oldColumn, $newColumn, $schema) {
    $stmt = $db->query("PRAGMA table_info($table)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $hasOld = false;
    $hasNew = false;
    
    foreach ($columns as $col) {
        if ($col['name'] === $oldColumn) $hasOld = true;
        if ($col['name'] === $newColumn) $hasNew = true;
    }
    
    if ($hasOld && !$hasNew) {
        echo "  Renaming '$oldColumn' to '$newColumn' in $table...\n";
        
        $db->exec("BEGIN TRANSACTION");
        
        // Create new table
        $db->exec("CREATE TABLE {$table}_new $schema");
        
        // Get column list for copying
        $stmt = $db->query("PRAGMA table_info($table)");
        $oldColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $columnNames = array_map(function($col) use ($oldColumn, $newColumn) {
            return $col['name'] === $oldCol