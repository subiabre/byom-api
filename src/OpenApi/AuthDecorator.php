<?php

namespace App\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\OpenApi;
use ApiPlatform\OpenApi\Model;

final class AuthDecorator implements OpenApiFactoryInterface
{
    public function __construct(
        private OpenApiFactoryInterface $decorated
    ) {}

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);
        $schemas = $openApi->getComponents()->getSchemas();

        $schemas['Token'] = new \ArrayObject([
            'type' => 'object',
            'properties' => [
                'token' => [
                    'type' => 'string',
                    'required' => true
                ],
            ],
        ]);

        $pathItem = new Model\PathItem(
            ref: 'Auth',
            get: new Model\Operation(
                operationId: 'getCredentialsItem',
                tags: ['Auth'],
                responses: [
                    '204' => [
                        'description' => 'Get authenticated User resource',
                        'headers' => [
                            'Location' => [
                                'description' => 'The IRI of the authenticated User resource',
                                'type' => 'string'
                            ]
                        ]
                    ],
                    '400' => [
                        'description' => 'Invalid request',
                    ],  
                ],
                summary: 'Get authenticated User resource.',
                security: [],
            ),
            post: new Model\Operation(
                operationId: 'postCredentialsItem',
                tags: ['Auth'],
                responses: [
                    '204' => [
                        'description' => 'Get authenticated User resource',
                        'headers' => [
                            'Location' => [
                                'description' => 'The IRI of the authenticated User resource',
                                'type' => 'string'
                            ],
                            'Set-Cookie' => [
                                'description' => 'HttpOnly cookie with the Authentication key to be used in further API requests',
                                'type' => 'string'
                            ]
                        ]
                    ],
                    '400' => [
                        'description' => 'Invalid request',
                    ],
                ],
                summary: 'Authenticates a User resource.',
                requestBody: new Model\RequestBody(
                    description: 'The User credentials',
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'properties' => [
                                    'username' => [
                                        'type' => 'string',
                                        'example' => 'johndoe',
                                        'required' => true
                                    ],
                                    'password' => [
                                        'type' => 'string',
                                        'example' => 'apassword',
                                        'required' => true
                                    ],
                                ]
                            ],
                        ],
                    ]),
                ),
                security: [],
            ),
        );

        $openApi->getPaths()->addPath('/api/auth', $pathItem);

        $pathItem = new Model\PathItem(
            ref: 'Auth',
            get: new Model\Operation(
                operationId: 'getTokenItem',
                tags: ['Auth'],
                responses: [
                    '201' => [
                        'description' => 'Get Authentication Token',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/Token',
                                ]
                            ]
                        ]
                    ],
                    '400' => [
                        'description' => 'Invalid request',
                    ],
                ],
                summary: 'Get an Authentication Token for the authenticated User.',
                description: 'This will generate a new token and delete all the previously existing ones for the User.',
                security: [],
            ),
            post: new Model\Operation(
                operationId: 'postTokenItem',
                tags: ['Auth'],
                responses: [
                    '204' => [
                        'description' => 'Get authenticated User resource',
                        'headers' => [
                            'Location' => [
                                'description' => 'The IRI of the authenticated User resource',
                                'type' => 'string'
                            ],
                            'Set-Cookie' => [
                                'description' => 'HttpOnly cookie with the Authentication key to be used in further API requests',
                                'type' => 'string'
                            ]
                        ]
                    ],
                    '400' => [
                        'description' => 'Invalid request',
                    ],
                ],
                summary: 'Authenticates a User resource via an Authentication Token.',
                description: 'Once the User is authenticated the Authentication Token will not be available again.',
                requestBody: new Model\RequestBody(
                    description: 'The Authentication Token to be verified',
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/Token'
                            ],
                        ],
                    ]),
                ),
                security: [],
            ),
        );

        $openApi->getPaths()->addPath('/api/auth/token', $pathItem);

        return $openApi;
    }
}