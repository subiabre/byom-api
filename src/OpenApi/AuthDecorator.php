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

        $pathItem = new Model\PathItem(
            ref: 'Auth',
            post: new Model\Operation(
                operationId: 'postCredentialsItem',
                tags: ['User'],
                responses: [
                    '204' => [
                        'description' => 'Get authenticated User resource',
                    ],
                    '400' => [
                        'description' => 'Invalid request',
                    ],
                ],
                summary: 'Authenticates a User resource.',
                requestBody: new Model\RequestBody(
                    description: 'Validates the User username and password.',
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'properties' => [
                                    'username' => [
                                        'type' => 'string',
                                        'example' => 'johndoe',
                                    ],
                                    'password' => [
                                        'type' => 'string',
                                        'example' => 'apassword',
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

        return $openApi;
    }
}