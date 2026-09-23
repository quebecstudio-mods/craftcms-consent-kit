<?php

namespace QuebecStudioMods\ConsentKit\CraftCms\migrations;

use craft\db\Migration;
use QuebecStudioMods\ConsentKit\CraftCms\Db\RegistrySchema;

/**
 * Creates the registry tables on a fresh install. Existing installs get them
 * through the numbered migration instead; both call the same schema.
 *
 * Settings are not seeded here — `Plugin::registerSeeding()` explains why.
 */
class Install extends Migration
{
    public function safeUp(): bool
    {
        RegistrySchema::create($this);

        return true;
    }

    /** Uninstalling the plugin destroys the register. That is Craft's contract. */
    public function safeDown(): bool
    {
        RegistrySchema::drop($this);

        return true;
    }
}
