<?php

declare(strict_types=1);

namespace QuebecStudioMods\ConsentKit\CraftCms\Db;

use CraftCms\Cms\Database\Table as CraftTable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The registry schema, in one place: the install migration and the update
 * migration both call it, so a fresh install and an upgraded one cannot drift.
 */
abstract class RegistrySchema
{
    public static function create(): void
    {
        self::createPresentations();
        self::createDecisions();
    }

    public static function drop(): void
    {

        Schema::dropIfExists(Table::DECISIONS);
        Schema::dropIfExists(Table::PRESENTATIONS);
    }

    /**
     * What the visitor was shown, deduplicated by hash. A presentation belongs
     * to a wording, not to a site: two sites showing the same text share one
     * row.
     */
    private static function createPresentations(): void
    {
        Schema::create(Table::PRESENTATIONS, function (Blueprint $table) {
            $table->integer('id', true);
            $table->char('hash', 64);
            $table->string('language', 16);
            $table->text('payload');
            $table->dateTime('dateCreated');
            $table->dateTime('dateUpdated');
            $table->char('uid', 36)->default('0');

            $table->unique(['hash']);
        });
    }

    /**
     * One row per decision. `siteHandle` is duplicated as text so deleting a
     * site does not take its proofs with it; `userId` is not, so deleting an
     * account does take the identity with it.
     */
    private static function createDecisions(): void
    {
        Schema::create(Table::DECISIONS, function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('presentationId');
            $table->integer('siteId')->nullable();
            $table->string('siteHandle');
            $table->integer('userId')->nullable();
            $table->string('language', 16);
            $table->dateTime('decidedAt');
            $table->string('action', 16);
            $table->string('origin', 16);
            $table->text('categories');
            $table->string('outcome', 8);
            $table->integer('consentVersion');
            $table->text('policyUrl')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('userAgent', 512)->nullable();
            $table->dateTime('dateCreated');
            $table->dateTime('dateUpdated');
            $table->char('uid', 36)->default('0');

            $table->index(['decidedAt']);
            $table->index(['outcome']);
            $table->index(['siteId', 'decidedAt']);
            $table->index(['presentationId']);
        });

        Schema::table(Table::DECISIONS, function (Blueprint $table) {

            $table->foreign('presentationId')
                ->references('id')
                ->on(Table::PRESENTATIONS)
                ->restrictOnDelete();

            $table->foreign('siteId')
                ->references('id')
                ->on(CraftTable::SITES)
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('userId')
                ->references('id')
                ->on(CraftTable::USERS)
                ->nullOnDelete();
        });
    }
}
