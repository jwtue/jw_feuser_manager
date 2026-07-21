<?php

declare(strict_types = 1);

/**
 * Persistence mapping of the extension.
 *
 * Regarding the explicit property mappings below:
 *
 * Otherwise Extbase derives the column name from the property name via
 * camelCaseToLowerCaseUnderscored(). For the numbered additional fields this fails,
 * because the conversion does not insert an underscore before a digit:
 *
 *   txJwfeusermanagerAdditionalText1  ->  tx_jwfeusermanager_additional_text1
 *   actual column:                        tx_jwfeusermanager_additional_text_1
 *
 * Without this mapping all 15 numbered fields point to non-existent columns and are
 * simply not persisted — without any error message.
 *
 * tx_jwfeusermanager_lastupdated and tx_jwfeusermanager_newsletter_subscribed map
 * correctly and are only listed here for completeness.
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
