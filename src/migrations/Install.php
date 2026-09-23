<?php

declare(strict_types=1);

use CraftCms\Cms\Database\Migration;
use Illuminate\Support\Facades\Schema;
use QuebecStudioMods\ConsentKit\CraftCms\Db\RegistrySchema;
use QuebecStudioMods\ConsentKit\CraftCms\Db\Table;

/**
 * Creates the registry tables on a fresh install. Existing installs get them
 * through the dated migration instead; both call the same schema.
 *
 * Settings are not seeded here — `SeedCategories` explains why.
 */
return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable(Table::DECISIONS)) {
            return;
        }

        RegistrySchema::create();
    }

    /** Uninstalling the plugin destroys the register. That is Craft CMS's contract. */
    public function down(): void
    {
        RegistrySchema::drop();
    }
};
