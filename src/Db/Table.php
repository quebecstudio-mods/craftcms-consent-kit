<?php

declare(strict_types=1);

namespace QuebecStudioMods\ConsentKit\CraftCms\Db;

/**
 * Registry table names. The connection applies the install's table prefix.
 */
abstract class Table
{
    public const string DECISIONS = 'cookieconsentkit_decisions';

    public const string PRESENTATIONS = 'cookieconsentkit_presentations';
}
