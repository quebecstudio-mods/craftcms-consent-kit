<?php

/**
 * French translation. English is the source language; the keys are the English
 * strings as they appear in the code.
 */

return [
    'General' => 'Général',
    'Couldn’t save settings.' => 'Impossible d’enregistrer les réglages.',

    'Handle' => 'Identifiant',
    'handle' => 'identifiant',
    'Add a category' => 'Ajouter une catégorie',
    'Add' => 'Ajouter',
    'Delete' => 'Supprimer',
    'A category with no declared cookie stays hidden, so adding one costs nothing until it is used. Its handle is permanent: the consent cookie stores it, and renaming it would strand every consent already given.'
        => 'Une catégorie sans témoin déclaré reste masquée : en ajouter une ne coûte rien tant qu’elle ne sert pas. Son identifiant est définitif — le témoin de consentement le mémorise, et le renommer laisserait sans objet tous les consentements déjà donnés.',
    'Delete the category “{name}”? Its cookies go with it. Visitors who accepted it keep that in their consent cookie until the policy version is bumped, and any tag marked with its handle stops being activated.'
        => 'Supprimer la catégorie « {name} » ? Ses témoins partent avec elle. Les visiteurs qui l’avaient acceptée gardent ce choix dans leur témoin de consentement jusqu’à l’incrément de la version de la politique, et toute balise marquée de son identifiant cesse d’être activée.',
    'No category' => 'Aucune catégorie',
    'Label' => 'Libellé',
    'Description' => 'Description',
    'Shown under the category in the manage panel.' => 'Affichée sous la catégorie dans le panneau « Gérer ».',
    '— always on' => '— toujours active',
    'Wording is stored per site. Only the selected site is edited here; the others are preserved when you save.'
        => 'Les libellés sont conservés par site. Seul le site sélectionné est modifié ici ; les autres sont préservés à l’enregistrement.',
    'A handle identifies a cookie across every site, and survives a change of technical name. Leave it empty and one is derived from the name.'
        => 'L’identifiant désigne un témoin sur tous les sites et survit à un changement de nom technique. Laissé vide, il est dérivé du nom.',
    'Skip the banner on a Global Privacy Control refusal' => 'Masquer le bandeau si le navigateur signale un refus',
    'Some browsers send a signal meaning “I refuse optional cookies”. That refusal is always honoured: optional categories start off, and nothing is set before consent. This setting only decides whether the banner still asks. Turn it off to keep telling every visitor what the site uses. With the banner skipped, a visitor changes their mind from the reopen tab — so keep that tab on, or provide your own entry point.'
        => 'Certains navigateurs envoient un signal signifiant « je refuse les témoins facultatifs ». Ce refus est toujours respecté : les catégories facultatives démarrent désactivées et rien n’est déposé avant consentement. Ce réglage décide seulement si le bandeau pose quand même la question. Désactivez-le pour continuer d’informer chaque visiteur de ce que le site utilise. Le bandeau masqué, le visiteur revient sur son refus par l’onglet de réouverture : gardez cet onglet actif, ou fournissez votre propre point d’entrée.',
    'The banner is skipped for these visitors and the reopen tab is off, so they have no way to accept unless the site provides its own entry point.'
        => 'Le bandeau est masqué pour ces visiteurs et l’onglet de réouverture est désactivé : ils n’ont aucun moyen d’accepter, sauf si le site fournit son propre point d’entrée.',

    'Copy from' => 'Copier depuis',
    'Replace the wording on this screen with {site}’s? Nothing is saved until you click Save.'
        => 'Remplacer les libellés de cet écran par ceux de {site} ? Rien n’est enregistré avant que vous cliquiez sur « Enregistrer ».',

    'Inventory table' => 'Tableau de l’inventaire',
    'The cookie table can be shown in a page of the site — the privacy policy, most of the time. It has no style of its own: it takes on the style of the page around it.'
        => 'Le tableau des témoins peut être affiché dans une page du site — la politique de confidentialité, le plus souvent. Il n’a pas de style à lui : il prend celui de la page qui l’accueille.',
    'From a template:' => 'Depuis un gabarit :',
    'From content, once the field goes through the filter:' => 'Depuis le contenu, une fois le champ passé par le filtre :',
    'CSS framework' => 'Framework CSS',
    'The shipped sets match one version of each framework. Custom writes your own.'
        => 'Les jeux fournis correspondent à une version donnée de chaque framework. « Personnalisé » permet d’écrire les vôtres.',
    'Custom' => 'Personnalisé',
    'Wrapper' => 'Conteneur',
    'Category title' => 'Titre de catégorie',
    'Category description' => 'Description de catégorie',
    'Table' => 'Tableau',
    'Table header' => 'En-tête du tableau',
    'Table body' => 'Corps du tableau',
    'Row' => 'Ligne',
    'Header cell' => 'Cellule d’en-tête',
    'Cell' => 'Cellule',

    'Category that lifts the facade' => 'Catégorie qui lève la façade',
    'A visitor who accepted this category gets the video loaded outright, without clicking. Left on “No category”, the facade always applies — which is the safer answer: consent for a category is broader than consent for one video, and Law 25 asks for specific consent. Withdrawing consent restores the facade on the next page load; a player already on screen stays.'
        => 'Un visiteur ayant accepté cette catégorie voit la vidéo chargée d’emblée, sans cliquer. Laissée à « Aucune catégorie », la façade s’applique toujours — c’est la réponse la plus sûre : le consentement à une catégorie est plus large que le consentement à une vidéo, et la Loi 25 demande un consentement spécifique. Le retrait du consentement rétablit la façade au chargement suivant ; un lecteur déjà affiché, lui, reste.',

    'Measurement' => 'Mesure',
    'Which category a visitor has to accept before Google Consent Mode and Matomo are granted. A site that measures nothing leaves both on “None”.'
        => 'La catégorie qu’un visiteur doit accepter pour que Google Consent Mode et Matomo soient autorisés. Un site qui ne mesure rien laisse les deux à « Aucune ».',
    'Analytics category' => 'Catégorie de statistiques',
    'Drives Matomo and Google analytics_storage.' => 'Pilote Matomo et analytics_storage de Google.',
    'Marketing category' => 'Catégorie de marketing',
    'Drives Google ad_storage, ad_user_data and ad_personalization.'
        => 'Pilote ad_storage, ad_user_data et ad_personalization de Google.',
    'Cookie inventory' => 'Inventaire des témoins',

    'Consent cookie' => 'Témoin de consentement',
    'Privacy policy' => 'Politique de confidentialité',
    'Appearance' => 'Apparence',
    'Behaviour' => 'Comportement',

    'Plugin name' => 'Nom du plugin',
    'Shown in the control panel. Leave it empty to use the plugin name.'
        => "Affiché dans le panneau d'administration. Laisser vide pour utiliser le nom du plugin.",

    'Cookie name' => 'Nom du témoin',
    'Name of the cookie that remembers the visitor’s choice. Renaming it invalidates existing consents — bump the policy version at the same time.'
        => "Nom du témoin qui mémorise le choix du visiteur. Le renommer invalide les consentements déjà donnés — l'accompagner d'un incrément de version.",
    'Lifetime' => 'Durée de conservation',
    'In seconds. 15,552,000 is 180 days.' => 'En secondes. 15 552 000 correspond à 180 jours.',
    'Policy version' => 'Version de la politique',
    'Bump this when a cookie appears in a non-necessary category, a category is added, or a purpose changes. Visitors will then be asked again.'
        => "À incrémenter lorsqu'un témoin apparaît dans une catégorie non nécessaire, qu'une catégorie est ajoutée, ou qu'une finalité change. Les visiteurs seront alors resollicités.",

    'Privacy policy page' => 'Page de la politique de confidentialité',
    'Link to' => 'Pointer vers',
    'A page on this site' => 'Une page de ce site',
    'A custom URL' => 'Une URL personnalisée',
    'This choice is made per site.' => 'Ce choix est fait par site.',
    'The link follows the slug if the page is renamed.'
        => 'Le lien suit le slug si la page est renommée.',
    'For a page outside Craft, or an environment variable.'
        => "Pour une page hors Craft, ou une variable d'environnement.",
    'Choose a page' => 'Choisir une page',

    'Privacy policy URL' => 'Lien vers la politique de confidentialité',
    'Video' => 'Vidéo',

    'Fallback language' => 'Langue de repli',
    'Used when the current locale has no wording. The list holds the languages the plugin ships with, plus any the site adds in translations/vendor/cookie-consent-kit.'
        => 'Utilisée lorsque la langue courante n’a aucun texte. La liste réunit les langues fournies par le plugin et celles que le site ajoute dans translations/vendor/cookie-consent-kit.',
    'Colour scheme' => 'Thème',
    '“Auto” follows the visitor’s system preference. The dark scheme uses the palette set through --qsm-ck-dark-*.'
        => '« Auto » suit la préférence système du visiteur. Le thème sombre utilise la palette définie par les variables --qsm-ck-dark-*.',
    'Auto (recommended)' => 'Auto (recommandé)',
    'Light' => 'Clair',
    'Dark' => 'Sombre',

    'Panel backdrop' => 'Arrière-plan du panneau',
    'Effect applied behind the “Manage” panel. Blur signals the modality without hiding the page.'
        => 'Effet appliqué derrière le panneau « Gérer ». Le flou signale la modalité sans masquer la page.',
    'Blur (recommended)' => 'Flou (recommandé)',
    'Dim' => 'Voile sombre',
    'None' => 'Aucun',

    'Display mode' => 'Disposition',
    'Full width along the bottom, or a box: floating in the middle, or in a bottom corner. On a narrow screen every mode is full width.'
        => 'Pleine largeur en bas de l’écran, ou une boîte : flottante au centre, ou dans un coin inférieur. Sur un écran étroit, toutes les dispositions passent en pleine largeur.',
    'Full width' => 'Pleine largeur',
    'Floating box' => 'Boîte flottante',
    'Bottom left corner' => 'Coin inférieur gauche',
    'Bottom right corner' => 'Coin inférieur droit',

    'YouTube facade' => 'Façade YouTube',
    'YouTube videos load only when the visitor clicks, so nothing reaches Google beforehand — the click is the consent, for that video alone. Only YouTube is covered: videos hosted elsewhere are untouched by this setting, and each template is responsible for them. Turning this off embeds YouTube directly, which lets Google set cookies as soon as the page is displayed, without any consent.'
        => "Les vidéos YouTube ne se chargent qu'au clic du visiteur : rien n'est transmis à Google avant. Le clic vaut consentement, pour cette vidéo seulement. Seul YouTube est couvert : les vidéos hébergées ailleurs ne sont pas touchées par ce réglage, et relèvent de chaque gabarit. Désactiver ce réglage intègre YouTube directement, ce qui laisse Google déposer ses témoins dès l'affichage de la page, sans aucun consentement.",
    'YouTube videos currently load without consent.' => 'Les vidéos YouTube se chargent actuellement sans consentement.',

    'YouTube thumbnails' => 'Vignettes YouTube',
    'Show the real thumbnail on the facade. The server fetches it from YouTube once, caches it, and serves it from this domain — the visitor never contacts Google before clicking. Turning this off falls back to a plain gradient.'
        => 'Affiche la véritable vignette sur la façade. Le serveur la récupère une fois auprès de YouTube, la met en cache et la sert depuis ce domaine : le visiteur ne contacte jamais Google avant son clic. Désactiver ce réglage rétablit un simple dégradé.',

    'Reopen tab' => 'Onglet de réouverture',
    'Reopen tab position' => 'Position de l’onglet de réouverture',
    'Bottom edge of the screen, on this side. “Auto” follows the display mode: on the right for a bottom right corner, on the left otherwise.'
        => 'Bord inférieur de l’écran, de ce côté. « Auto » suit la disposition : à droite pour le coin inférieur droit, à gauche sinon.',
    'Auto' => 'Auto',
    'Left' => 'Gauche',
    'Right' => 'Droite',
    'Small tab shown once the visitor has decided, so the banner can be reopened. Required for compliance — withdrawal must be as easy as consent. Turn it off only if the site provides its own entry point calling window.qsmConsentKit.open().'
        => "Petit onglet affiché une fois la décision prise, permettant de rouvrir la bannière. Nécessaire à la conformité : le retrait doit être aussi simple que l'octroi. À désactiver seulement si le site fournit son propre point d'entrée appelant window.qsmConsentKit.open().",

    'Automatic injection' => 'Injection automatique',
    'Places the banner as the first child of <body>, without touching any template. Turn this off only if the site needs to position it itself. The <head> bootstrap always stays automatic.'
        => "Place la bannière en premier enfant du <body>, sans toucher aux gabarits. À désactiver seulement si le site doit la positionner lui-même. L'amorçage du <head>, lui, reste toujours automatique.",
    'Template folder' => 'Dossier de gabarits',
    'A template placed in this folder of the site overrides the plugin’s own. For example templates/_consent/banner.twig.'
        => 'Un gabarit placé dans ce dossier du site remplace celui du plugin. Par exemple templates/_consent/banner.twig.',

    'This inventory is a compliance record: it must reflect what the site actually sets. A category with no declared cookie is not shown in the banner.'
        => "Cet inventaire est une pièce de conformité : il doit refléter ce que le site dépose réellement. Une catégorie sans témoin déclaré n'apparaît pas dans la bannière.",
    'The inventory is defined in config/cookie-consent-kit.php and cannot be edited here.'
        => "L'inventaire est défini dans config/cookie-consent-kit.php et n'est pas modifiable ici.",
    'Category' => 'Catégorie',
    'Name' => 'Nom',
    'Set by' => 'Déposé par',
    'This site' => 'Ce site',
    'Purpose' => 'Finalité',
    'Retention' => 'Conservation',
    'Add a cookie' => 'Ajouter un témoin',
    'Cookies' => 'Témoins',
    'In the banner' => 'Dans la bannière',
    'Shown' => 'Affichée',
    'always on' => 'toujours active',
    'Hidden — no cookie declared' => 'Masquée — aucun témoin déclaré',


    'Consent' => 'Consentements',
    'Consent register' => 'Registre des consentements',
    'Register' => 'Registre',
    'Keeping a server-side register is part of the Pro edition. Standard collects and honours consent; Pro archives the proof.'
        => 'Tenir un registre côté serveur relève de l’édition Pro. Standard recueille le consentement et le respecte ; Pro en archive la preuve.',
    'Learn more' => 'En savoir plus',
    'Record decisions' => 'Consigner les décisions',
    'Each decision is written down as the browser makes it: the server clock, the site, the categories answered, and a fingerprint of the wording that was on screen. The cookie’s own timestamp lives on the visitor’s device and proves nothing. Off by default — a register is something a site announces in its privacy policy.'
        => 'Chaque décision est consignée au moment où le navigateur la prend : horloge du serveur, site, catégories répondues, et empreinte du texte qui était à l’écran. L’horodatage du témoin, lui, vit sur l’appareil du visiteur et ne prouve rien. Désactivé par défaut — un registre s’annonce dans la politique de confidentialité.',
    'Keep records for' => 'Conserver les entrées',
    'Months kept beyond the life of the consent cookie itself, so a proof outlives what it attests. Zero keeps every record until it is purged by hand.'
        => 'Mois de conservation au-delà de la durée du témoin de consentement, pour qu’une preuve survive à ce qu’elle atteste. Zéro conserve tout jusqu’à une purge manuelle.',
    'Record the signed-in user' => 'Consigner l’utilisateur connecté',
    'When a decision comes from someone signed in, their account is recorded with it. This is the one identity the server can assert rather than be told. Deleting an account clears the link and leaves the decision.'
        => 'Quand la décision vient d’une personne connectée, son compte est consigné avec elle. C’est la seule identité que le serveur constate au lieu de se la faire déclarer. Supprimer un compte efface le lien et laisse la décision.',
    'Record where the decision came from' => 'Consigner la provenance de la décision',
    'The visitor’s address and browser, stored as they are, so a record answers where a decision came from — which a hash cannot. It also makes the register personal data, to be declared and to be answered for. Off by default.'
        => 'L’adresse et le navigateur du visiteur, conservés tels quels, pour qu’une entrée réponde d’où venait la décision — ce qu’une empreinte ne permet pas. Cela fait aussi du registre une donnée personnelle, à déclarer et à assumer. Désactivé par défaut.',

    'Collection is off. What is listed here was recorded while it was on.'
        => 'La collecte est arrêtée. Ce qui est listé ici a été consigné pendant qu’elle était active.',
    'This install asks for a register in its configuration. Without the Pro edition it stays dormant, and nothing is written. Records already kept remain readable, exportable and purgeable.'
        => 'Cette installation demande un registre dans sa configuration. Sans l’édition Pro, il reste en sommeil et rien n’est consigné. Les entrées déjà conservées restent consultables, exportables et purgeables.',
    'All sites' => 'Tous les sites',
    'Language' => 'Langue',
    'Clear filters' => 'Retirer les filtres',
    'From' => 'Du',
    'To' => 'Au',
    'Export' => 'Exporter',
    'Export…' => 'Exporter…',
    'Format' => 'Format',
    'JSON' => 'JSON',
    'with the wording of every screen' => 'avec le texte de chaque écran',
    'Limit' => 'Limite',
    'No limit' => 'Aucune limite',
    'Back to the register' => 'Retour au registre',
    'Includes the wording of every screen' => 'Comprend le texte de chaque écran',
    'Export CSV' => 'Exporter en CSV',
    'Granted' => 'Accordé',
    'Any' => 'Peu importe',
    'Everything' => 'Tout',
    'Required only' => 'Requis seulement',
    'Some categories' => 'Certaines catégories',

    'Accepted everything' => 'Tout accepté',
    'Refused everything' => 'Tout refusé',
    'Chose category by category' => 'Choisi catégorie par catégorie',
    'from the banner' => 'depuis la bannière',
    'from the preferences panel' => 'depuis le panneau de préférences',
    'from the browser’s Global Privacy Control signal' => 'depuis le signal Global Privacy Control du navigateur',

    'Decided' => 'Décidé le',
    'Answer' => 'Réponse',
    'Accepted' => 'Acceptée',
    'Refused' => 'Refusée',
    'Screen' => 'Écran',
    'No decision recorded yet.' => 'Aucune décision consignée pour l’instant.',
    'Page {page} of {pages}' => 'Page {page} sur {pages}',
    '{total} decisions' => '{total} décisions',

    'Decision {id}' => 'Décision {id}',
    'What was recorded' => 'Ce qui a été consigné',
    'Policy' => 'Politique',
    'Address' => 'Adresse',
    'Browser' => 'Navigateur',
    'Signed in as' => 'Connecté comme',
    'The account has since been deleted.' => 'Le compte a été supprimé depuis.',
    'The site has since been deleted.' => 'Le site a été supprimé depuis.',
    'Categories answered' => 'Catégories répondues',
    'What was on screen' => 'Ce qui était à l’écran',
    'Fingerprint of this screen. Decisions sharing it were shown exactly the same wording, and it lets anyone recompute the text below to check that nothing moved.'
        => 'Empreinte de cet écran. Les décisions qui la partagent ont vu exactement la même formulation, et elle permet de recalculer le texte ci-dessous pour vérifier que rien n’a bougé.',
    'Categories shown' => 'Catégories affichées',
    'Buttons' => 'Boutons',
    'Every string that was displayed' => 'Toutes les chaînes affichées',
    'Always on' => 'Toujours active',
    'Optional, unchecked by default' => 'Optionnelle, décochée par défaut',
    '{count} cookies listed' => '{count} témoins listés',
    'The presentation was not stored.' => 'La présentation n’a pas été conservée.',

    '{count} decisions recorded, the oldest on {date}.' => '{count} décisions consignées, la plus ancienne le {date}.',
    'Delete records older than' => 'Supprimer les entrées de plus de',
    'Retention removes outlived records on its own. This is for a deletion that cannot wait.'
        => 'La rétention supprime d’elle-même les entrées périmées. Ceci sert à une suppression qui ne peut pas attendre.',
    '{n} months' => '{n} mois',
    'All records' => 'Toutes les entrées',
    'Purge' => 'Purger',
    'Deleting records cannot be undone. Continue?' => 'La suppression est définitive. Continuer ?',
    'Purging frees nobody: consent lives in the visitor’s cookie and keeps applying. What goes is the proof of it, and a site that still acts on a consent it can no longer show has the worst of both.'
        => 'Purger ne libère personne : le consentement vit dans le témoin du visiteur et continue de s’appliquer. Ce qui disparaît, c’est la preuve — et un site qui agit encore sur un consentement qu’il ne peut plus montrer cumule les deux inconvénients.',
    'Consent version' => 'Version du consentement',
    '{count} records deleted.' => '{count} entrées supprimées.',
    'You do not have permission to purge the register.' => 'Vous n’avez pas la permission de purger le registre.',

    'View the consent register' => 'Consulter le registre des consentements',
    'Export the consent register' => 'Exporter le registre des consentements',
    'Purge the consent register' => 'Purger le registre des consentements',

    'Overridden by config/cookie-consent-kit.php' => 'Surchargé par config/cookie-consent-kit.php',
    'These settings are stored in the project config, which this environment does not allow changing (`allowAdminChanges` is off). Edit them in development and deploy.'
        => 'Ces réglages sont conservés dans le project config, que cet environnement interdit de modifier (`allowAdminChanges` est désactivé). Modifiez-les en développement, puis déployez.',
];
