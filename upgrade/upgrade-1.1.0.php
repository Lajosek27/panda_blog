<?php
/**
 * Upgrade do wersji 1.1.0
 * 
 * - Dodaje kolumny: author
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @param Panda_blog $module
 * @return bool
 */
function upgrade_module_1_1_0($module)
{
    $db = Db::getInstance();
    $prefix = _DB_PREFIX_;
    $tableName = $prefix . 'panda_blog_post';

    // Sprawdź czy tabela istnieje
    $tableExists = $db->executeS("SHOW TABLES LIKE '{$tableName}'");
    if (empty($tableExists)) {
        // Jeśli tabeli nie ma, to prawdopodobnie świeża instalacja - nic nie rób
        return true;
    }

    // Pobierz listę kolumn
    $columns = $db->executeS("SHOW COLUMNS FROM `{$tableName}`");
    $columnNames = array_column($columns, 'Field');

    $queries = [];



    if (!in_array('author', $columnNames)) {
        $queries[] = "ALTER TABLE `{$tableName}` 
                  ADD COLUMN `author` VARCHAR(255) NOT NULL DEFAULT 'Admin'";
    }

    // Wykonaj wszystkie zapytania
    foreach ($queries as $query) {
        if (!$db->execute($query)) {
            PrestaShopLoggerCore::addLog(
                'Panda Blog Upgrade 1.1.0 failed: ' . $db->getMsgError(),
                3,
                null,
                'Module',
                $module->id
            );
            return false;
        }
    }


    return true;
}

