<?php

namespace JwTue\FeUserManager\Hooks;

/**
 * AutoConfiguration-Hook for RealURL
 *
 */
class RealUrlAutoConfiguration
{

    /**
     * Generates additional RealURL configuration and merges it with provided configuration
     *
     * @param       array $params Default configuration
     * @return      array Updated configuration
     */
    public function addUserListConfig($params)
    {

        return array_merge_recursive($params['config'], [
                'postVarSets' => [
                    '_DEFAULT' => [
                        "jw_feuser_manager" => [
                            [
                                'GETvar' => 'filter',
                                'lookUpTable' => [
                                    'table' => 'fe_groups',
                                    'id_field' => 'uid',
                                    'alias_field' => 'title',
                                    'useUniqueCache' => 1,
                                    'useUniqueCache_conf' => [
                                        'strtolower' => 1,
                                        'spaceCharacter' => '-',
                                    ],
                                ],
                            ],
                           /* [
                                'GETvar' => 'user',
                                'lookUpTable' => [
                                    'table' => 'fe_users',
                                    'id_field' => 'uid',
                                    'alias_field' => 'username',
                                    'useUniqueCache' => 1,
                                    'useUniqueCache_conf' => [
                                        'strtolower' => 1,
                                        'spaceCharacter' => '-',
                                    ],
                                ],
                            ],*/
                            [
                                'GETvar' => 'download',
								'valueMap' => array(
								  'pdf' => 'pdf',
								  'csv' => 'csv'
								),
                            ],
                        ],
                    ]
                ]
            ]
        );
    }
}
