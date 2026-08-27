<?php

namespace App\Domains\Account;

use App\Domains\BaseDomain;
use App\Exceptions\InternalErrorException;

class VerifyAccount extends BaseDomain
{
    /**
     * Valida se a conta existe
     *
     * @param array|null $account
     * @throws InternalErrorException
     */
    private function existAccount(?array $account): void
    {
        if (is_null($account)) {
            throw new InternalErrorException(
                'ACCOUNT_NOT_FOUND',
                161001001
            );
        }
    }

    /**
     * Valida se a conta existe
     *
     * @param array|null $account
     * @return array
     */
    public function handle(?array $account): array
    {
        $this->existAccount($account);
        
        return $account;
    }
}
