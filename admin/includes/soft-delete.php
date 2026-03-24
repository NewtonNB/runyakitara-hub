<?php
/**
 * Soft Delete Helper
 * Adds deleted_at column to all content tables if not present.
 * Call once per request via ensureSoftDelete($db).
 */
function ensureSoftDelete($db) {
    $tables = ['lessons', 'dictionary', 'proverbs', 'articles', 'grammar_topics', 'media', 'translations'];
    foreach ($tables as $table) {
        try {
            $db->exec("ALTER TABLE $table ADD COLUMN deleted_at DATETIME DEFAULT NULL");
        } catch (Exception $e) {
            // Column already exists — ignore
        }
    }
}

function softDelete($db, $table, $id) {
    $stmt = $db->prepare("UPDATE $table SET deleted_at = datetime('now') WHERE id = ?");
    return $stmt->execute([$id]);
}

function restoreRecord($db, $table, $id) {
    $stmt = $db->prepare("UPDATE $table SET deleted_at = NULL WHERE id = ?");
    return $stmt->execute([$id]);
}

function hardDelete($db, $table, $id) {
    $stmt = $db->prepare("DELETE FROM $table WHERE id = ?");
    return $stmt->execute([$id]);
}
