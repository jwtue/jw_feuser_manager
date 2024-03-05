<?php

declare(strict_types = 1);

return [
    \JwTue\FeUserManager\Domain\Model\FrontendUser::class => [
        'tableName' => 'fe_users',
    ],
    \JwTue\FeUserManager\Domain\Model\FrontendUserGroup::class => [
        'tableName' => 'fe_groups',
    ],
    \JwTue\FeUserManager\Domain\Model\EditorField::class => [
        'tableName' => 'tx_jwfeusermanager_editorfield',
        'recordType' => '\JwTue\FeUserManager\Domain\Model\EditorField'
    ],
];