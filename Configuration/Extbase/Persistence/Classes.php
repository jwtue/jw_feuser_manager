<?php

declare(strict_types = 1);

/**
 * Persistence-Mapping der Extension.
 *
 * Zu den expliziten Property-Zuordnungen unten:
 *
 * Extbase leitet den Spaltennamen sonst per camelCaseToLowerCaseUnderscored() aus dem
 * Property-Namen ab. Bei den durchnummerierten Zusatzfeldern schlaegt das fehl, weil die
 * Umwandlung vor einer Ziffer keinen Unterstrich setzt:
 *
 *   txJwfeusermanagerAdditionalText1  ->  tx_jwfeusermanager_additional_text1
 *   tatsaechliche Spalte:                 tx_jwfeusermanager_additional_text_1
 *
 * Ohne diese Zuordnung zeigen alle 15 nummerierten Felder auf nicht existierende Spalten
 * und werden schlicht nicht persistiert — ohne Fehlermeldung.
 *
 * tx_jwfeusermanager_lastupdated und tx_jwfeusermanager_newsletter_subscribed bilden
 * korrekt ab und stehen hier nur der Vollstaendigkeit halber.
 */

$feUserProperties = [
    'txJwfeusermanagerLastupdated' => ['fieldName' => 'tx_jwfeusermanager_lastupdated'],
    'txJwfeusermanagerNewsletterSubscribed' => ['fieldName' => 'tx_jwfeusermanager_newsletter_subscribed'],
];

foreach (['Text' => 'text', 'Boolean' => 'boolean', 'Bitfield' => 'bitfield'] as $camel => $snake) {
    for ($i = 1; $i <= 5; $i++) {
        $feUserProperties['txJwfeusermanagerAdditional' . $camel . $i] = [
            'fieldName' => 'tx_jwfeusermanager_additional_' . $snake . '_' . $i,
        ];
    }
}

return [
    \JwTue\FeUserManager\Domain\Model\FrontendUser::class => [
        'tableName' => 'fe_users',
        'properties' => $feUserProperties,
    ],
    \JwTue\FeUserManager\Domain\Model\FrontendUserGroup::class => [
        'tableName' => 'fe_groups',
    ],
    \JwTue\FeUserManager\Domain\Model\EditorField::class => [
        'tableName' => 'tx_jwfeusermanager_editorfield',
        'recordType' => '\JwTue\FeUserManager\Domain\Model\EditorField'
    ],
];
