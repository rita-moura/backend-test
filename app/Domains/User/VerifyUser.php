<?php

namespace App\Domains\User;

use App\Domains\BaseDomain;
use App\Exceptions\InternalErrorException;

class VerifyUser extends BaseDomain
{
    /**
     * Valida se o usuário existe
     *
     * @param array|null $User
     * @throws InternalErrorException
     */
    private function existUser(?array $user): void
    {
        if (is_null($user)) {
            throw new InternalErrorException(
                'USER_NOT_FOUND',
                146001001
            );
        }
    }

    /**
     * Valida se o usuário existe
     *
     * @param array|null $user
     * @return array
     */
    public function handle(?array $user): array
    {
        $this->existUser($user);
        
        return $user;
    }
}
