<?php

namespace QuebecStudioMods\ConsentKit\CraftCms\Db;

use craft\db\Migration;
use craft\db\Table as CraftTable;

/**
 * The registry schema, in one place: the install migration and the update
 * migration both call it, so a fresh install and an upgraded one cannot drift.
 */
abstract class RegistrySchema
{
    public static function create(Migration $migration): void
    {
        self::createPresentations($migration);
        self::createDecisions($migration);
    }

    public static function drop(Migration $migration): void
    {

        $migration->dropTableIfExists(Table::DECISIONS);
        $migration->dropTableIfExists(Table::PRESENTATIONS);
    }

    /**
     * What the visitor was shown, deduplicated by hash. A presentation belongs
     * to a wording, not to a site.
     */
    private static function createPresentations(Migration $migration): void
    {
        $migration->createTable(Table::PRESENTATIONS, [
            'id' => $migration->primaryKey(),
            'hash' => $migration->char(64)->notNull(),
            'language' => $migration->string(16)->notNull(),
            'payload' => $migration->text()->notNull(),
            'dateCreated' => $migration->dateTime()->notNull(),
            'dateUpdated' => $migration->dateTime()->notNull(),
            'uid' => $migration->uid(),
        ]);

        $migration->createIndex(null, Table::PRESENTATIONS, ['hash'], true);
    }

    /**
     * One row per decision. `siteHandle` is duplicated as text so deleting a
     * site does not take its proofs with it; `userId` is not, so deleting an
     * account does take the identity with it.
     */
    private static function createDecisions(Migration $migration): void
    {
        $migration->createTable(Table::DECISIONS, [
            'id' => $migration->primaryKey(),
            'presentationId' => $migration->integer()->notNull(),
            'siteId' => $migration->integer(),
            'siteHandle' => $migration->string()->notNull(),
            'userId' => $migration->integer(),
            'language' => $migration->string(16)->notNull(),
            'decidedAt' => $migration->dateTime()->notNull(),
            'action' => $migration->string(16)->notNull(),
            'origin' => $migration->string(16)->notNull(),
            'categories' => $migration->text()->notNull(),
            'outcome' => $migration->string(8)->notNull(),
            'consentVersion' => $migration->integer()->notNull(),
            'policyUrl' => $migration->text(),
            'ip' => $migration->string(45),
            'userAgent' => $migration->string(512),
            'dateCreated' => $migration->dateTime()->notNull(),
            'dateUpdated' => $migration->dateTime()->notNull(),
            'uid' => $migration->uid(),
        ]);

        $migration->createIndex(null, Table::DECISIONS, ['decidedAt']);
        $migration->createIndex(null, Table::DECISIONS, ['outcome']);
        $migration->createIndex(null, Table::DECISIONS, ['siteId', 'decidedAt']);
        $migration->createIndex(null, Table::DECISIONS, ['presentationId']);

        $migration->addForeignKey(
            null,
            Table::DECISIONS,
            ['presentationId'],
            Table::PRESENTATIONS,
            ['id'],
            'RESTRICT'
        );

        $migration->addForeignKey(
            null,
            Table::DECISIONS,
            ['siteId'],
            CraftTable::SITES,
            ['id'],
            'SET NULL'
        );

        $migration->addForeignKey(
            null,
            Table::DECISIONS,
            ['userId'],
            CraftTable::USERS,
            ['id'],
            'SET NULL'
        );
    }
}
