<?php
/**
 * Soft Delete Helper — MySQL version
 */
function ensureSoftDelete($db) {
    // MySQL: silently add deleted_at if missing
    $tables = ['lessons', 'dictionary', 'proverbs', 'articles', 'grammar_topics', 'media', 'translations', 'contact_messages'];
    foreach ($tables as $table) {
        try {
            $db->exec("ALTER TABLE `$table` ADD COLUMN deleted_at DATETIME DEFAULT NULL");
        } catch (Exception $e) {
            // Column already exists — ignore
        }
    }
}

function softDelete($db, $table, $id) {
    $stmt = $db->prepare("UPDATE `$table` SET deleted_at = NOW() WHERE id = ?");
    return $stmt->execute([$id]);
}

function restoreRecord($db, $table, $id) {
    $stmt = $db->prepare("UPDATE `$table` SET deleted_at = NULL WHERE id = ?");
    return $stmt->execute([$id]);
}

function hardDelete($db, $table, $id) {
    $stmt = $db->prepare("DELETE FROM `$table` WHERE id = ?");
    return $stmt->execute([$id]);
}
