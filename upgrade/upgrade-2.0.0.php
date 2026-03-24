<?php
/**
 * Upgrade do wersji 1.1.0
 * 
 * - Dodaje pole related_product_ids do tablicy panda_blog_post
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @param Panda_blog $module
 * @return bool
 */
function upgrade_module_2_0_0($module)
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



    if (!in_array('related_product_ids', $columnNames)) {
        $queries[] = "ALTER TABLE `{$tableName}` 
                  ADD COLUMN `related_product_ids` JSON NOT NULL DEFAULT ('[]')";
    }

    // Wykonaj wszystkie zapytania
    foreach ($queries as $query) {
        if (!$db->execute($query)) {
            PrestaShopLoggerCore::addLog(
                'Panda Blog Upgrade 2.0.0 failed: ' . $db->getMsgError(),
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

