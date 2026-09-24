<?php

use QuebecStudioMods\ConsentKit\Core\Paths;
use QuebecStudioMods\ConsentKit\Core\Wording;

/**
 * English is the source language: the keys are the English strings as they
 * appear in the code, and what the core holds are the title-case forms a
 * sentence-case key does not give.
 */
return Wording::braces(Paths::cpStrings('en'));
