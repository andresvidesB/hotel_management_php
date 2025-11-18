<?php

declare(strict_types=1);

namespace Src\Users\Infrastructure\Factories;

use Src\Users\Domain\Entities\WriteUser;
use Src\Users\Domain\ValueObjects\UserPassword;
use Src\Shared\Domain\ValueObjects\Identifier;

final class UserFactory
{
    public static function writeUserFromArray(array $data): WriteUser
    {
        return new WriteUser(
            new Identifier($data['user_id_person']),
            new UserPassword($data['user_password']),
            new Identifier($data['user_role_id'])
        );
    }
}
