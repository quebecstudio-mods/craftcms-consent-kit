<?php

use QuebecStudioMods\ConsentKit\Core\Paths;
use QuebecStudioMods\ConsentKit\Core\Wording;

/**
 * French wording for the control panel. What is shared with the rest of the
 * suite comes from the core, in the placeholders Craft CMS substitutes; what
 * only this plugin says is listed below.
 *
 * English is the source language: the keys are the English strings as they
 * appear in the code.
 */
return array_merge(Wording::braces(Paths::cpStrings('fr')), [
    'Add'
        => 'Ajouter',
    'A template placed in this folder of the site overrides the plugin’s own. For example templates/_consent/banner.twig.'
        => 'Un gabarit placé dans ce dossier du site remplace celui du plugin. Par exemple templates/_consent/banner.twig.',
    'Copy from'
        => 'Copier depuis',
    'Couldn’t save settings.'
        => 'Impossible d’enregistrer les réglages.',
    'Delete'
        => 'Supprimer',
    'Delete the category “{name}”? Its cookies go with it. Visitors who accepted it keep that in their consent cookie until the policy version is bumped, and any tag marked with its handle stops being activated.'
        => 'Supprimer la catégorie « {name} » ? Ses témoins partent avec elle. Les visiteurs qui l’avaient acceptée gardent ce choix dans leur témoin de consentement jusqu’à l’incrément de la version de la politique, et toute balise marquée de son identifiant cesse d’être activée.',
    'Export CSV'
        => 'Exporter en CSV',
    'In the banner'
        => 'Dans la bannière',
    'Overridden by config/cookie-consent-kit.php'
        => 'Surchargé par config/cookie-consent-kit.php',
    'Places the banner at the end of <body>, without touching any template. Turn this off only if the site needs to position it itself, with craft.consent.banner(). The <head> bootstrap always stays automatic.'
        => 'Place la bannière à la fin du <body>, sans toucher aux gabarits. À désactiver seulement si le site doit la positionner lui-même, avec craft.consent.banner(). L’amorçage du <head>, lui, reste toujours automatique.',
    'Plugin name'
        => 'Nom du plugin',
    'Replace the wording on this screen with {site}’s? Nothing is saved until you click Save.'
        => 'Remplacer les libellés de cet écran par ceux de {site} ? Rien n’est enregistré avant que vous cliquiez sur « Enregistrer ».',
    'Shown in the control panel. Leave it empty to use the plugin name.'
        => 'Affiché dans le panneau d\'administration. Laisser vide pour utiliser le nom du plugin.',
    'The inventory is defined in config/cookie-consent-kit.php and cannot be edited here.'
        => 'L\'inventaire est défini dans config/cookie-consent-kit.php et n\'est pas modifiable ici.',
    'These settings are stored in the project config, which this environment does not allow changing (`allowAdminChanges` is off). Edit them in development and deploy.'
        => 'Ces réglages sont conservés dans le project config, que cet environnement interdit de modifier (`allowAdminChanges` est désactivé). Modifiez-les en développement, puis déployez.',
    'Used when the current locale has no wording. The list holds the languages the plugin ships with, plus any the site adds in translations/vendor/cookie-consent-kit.'
        => 'Utilisée lorsque la langue courante n’a aucun texte. La liste réunit les langues fournies par le plugin et celles que le site ajoute dans translations/vendor/cookie-consent-kit.',
]);
