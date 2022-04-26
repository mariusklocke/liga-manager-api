<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\v2\Type\Input;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Domain\Value\ContactPerson;
use HexagonalPlayground\Infrastructure\API\GraphQL\CustomObjectType;

class ContactInputType extends InputObjectType implements CustomObjectType
{
    public function __construct()
    {
        parent::__construct([
            'fields' => function () {
                return [
                    'firstName' => [
                        'type' => Type::nonNull(Type::string())
                    ],
                    'lastName' => [
                        'type' => Type::nonNull(Type::string())
                    ],
                    'phone' => [
                        'type' => Type::nonNull(Type::string())
                    ],
                    'email' => [
                        'type' => Type::nonNull(Type::string())
                    ]
                ];
            }
        ]);
    }

    public function parseCustomValue($value): object
    {
        return new ContactPerson(
            $value['firstName'],
            $value['lastName'],
            $value['phone'],
            $value['email']
        );
    }
}
