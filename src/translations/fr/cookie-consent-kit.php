<?php

use QuebecStudioMods\ConsentKit\Core\Paths;
use QuebecStudioMods\ConsentKit\Core\Wording;

/**
 * French wording for the control panel, shared with the Statamic addon so the
 * two products say the same thing. The core writes `:name`; Craft CMS's own
 * translator substitutes `{name}`.
 */
return Wording::braces(Paths::cpStrings('fr'));
