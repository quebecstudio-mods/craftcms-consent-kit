<?php

use QuebecStudioMods\ConsentKit\Core\Paths;
use QuebecStudioMods\ConsentKit\Core\Wording;

/**
 * English overrides: titles in title case, which a key written as a sentence
 * does not give. Anything absent falls back to the key itself.
 */
return Wording::braces(Paths::cpStrings('en'));
