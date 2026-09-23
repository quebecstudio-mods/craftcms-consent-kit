<?php

namespace QuebecStudioMods\ConsentKit\CraftCms\Db;

/**
 * Registry table names, written as `{{%…}}` so the install's table prefix
 * applies.
 */
abstract class Table
{
    public const DECISIONS = '{{%cookieconsentkit_decisions}}';

    public const PRESENTATIONS = '{{%cookieconsentkit_presentations}}';
}
