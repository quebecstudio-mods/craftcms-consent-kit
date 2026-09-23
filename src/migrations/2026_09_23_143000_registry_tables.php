<?php

declare(strict_types=1);

use CraftCms\Cms\Database\Migration;
use Illuminate\Support\Facades\Schema;
use QuebecStudioMods\ConsentKit\CraftCms\Db\RegistrySchema;
use QuebecStudioMods\ConsentKit\CraftCms\Db\Table;

/**
 * Brings the registry tables to installs that predate them.
 */
return new class () extends Migration {
    public function up(): void
    {

        if (Schema::hasTable(Table::DECISIONS)) {
            return;
        }

        RegistrySchema::create();
    }

    public function down(): void
    {
        RegistrySchema::drop();
    }
};
