<?php

namespace QuebecStudioMods\ConsentKit\CraftCms\migrations;

use craft\db\Migration;
use QuebecStudioMods\ConsentKit\CraftCms\Db\RegistrySchema;
use QuebecStudioMods\ConsentKit\CraftCms\Db\Table;

/**
 * Brings the registry tables to installs that predate them.
 */
class m260923_143000_registry_tables extends Migration
{
    public function safeUp(): bool
    {

        if ($this->db->tableExists(Table::DECISIONS)) {
            return true;
        }

        RegistrySchema::create($this);

        return true;
    }

    public function safeDown(): bool
    {
        RegistrySchema::drop($this);

        return true;
    }
}
