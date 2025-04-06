<?php

namespace App\Domains\Card;

use App\Domains\BaseDomain;
use App\Exceptions\InternalErrorException;

class Register extends BaseDomain
{
    /**
     * Id da conta
     *
     * @var string
     */
    protected string $accountId;

    /**
     * Id do usuário
     *
     * @var string
     */
    protected string $userId;

    /**
     * Id do cartão
     *
     * @var string
     */
    protected string $cardId;

    /**
     * PIN do cartão
     *
     * @var string
     */
    protected string $pin;

    public function __construct(string $userId, string $pin, string $cardId)
    {
        $this->userId = $userId;
        $this->pin    = $pin;
        $this->cardId = $cardId;
    }

    /**
     * Retorna o id do usuário
     *
     * @return string
     */
    public function getAccountId(): string
    {
        return $this->accountId;
    }

    /**
     * Retorna o id do usuário
     *
     * @return string
     */
    public function getCardId(): string
    {
        return $this->cardId;
    }

    /**
     * Retorna o pin do usuário
     *
     * @return string
     */
    public function getPin(): string
    {
        return $this->pin;
    }

    /**
     * Busca o id de conta
     *
     * @return void
     */
    protected function findAccountId($account): void
    {

        if (is_null($account)) {
            throw new InternalErrorException(
                'ACCOUNT_NOT_FOUND',
                161001001
            );
        }

        $this->accountId = $account['id'];
    }

    /**
     * Cartão não pode já estar vinculado
     */
    protected function checkExternalId($externalId)
    {
        if (!$externalId) {
            throw new InternalErrorException(
                'Não é possível vincular esse cartão',
                0
            );
        }
    }

    /**
     * Checa se é possível vincular o cartão
     *
     * @return self
     */
    public function handle($account, $externalId): self
    {
        $this->findAccountId($account);
        $this->checkExternalId($externalId);

        return $this;
    }
}
