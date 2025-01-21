<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Definition\EnumType;
use HexagonalPlayground\Domain\User;

class UserLocaleType extends EnumType
{
    use SingletonTrait;

    public function __construct()
    {
        $config = [
            'values' => []
        ];

        foreach (User::getLocales() as $locale) {
            $config['values'][$locale] = ['value' => $locale];
        }

        parent::__construct($config);
    }
}
