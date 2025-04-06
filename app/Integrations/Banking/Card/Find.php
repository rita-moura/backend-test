<?php

namespace App\Integrations\Banking\Card;

use App\Integrations\Banking\Gateway;
use App\Exceptions\InternalErrorException;

class Find extends Gateway
{
    /**
     * Id externo da conta
     *
     * @var string
     */
    protected string $externalAccountId;

    /**
     * Id do usuário
     *
     * @var string
     */
    protected string $userId;

    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Busca os dados de conta
     *
     * @return void
     */
    protected function findAccountData($account): void
    {

        if (is_null($account)) {
            throw new InternalErrorException(
                'ACCOUNT_NOT_FOUND',
                161001001
            );
        }

        $this->externalAccountId = $account['external_id'];
    }

    /**
     * Constroi a url da request
     *
     * @return string
     */
    protected function requestUrl(): string
    {
        return "account/$this->externalAccountId/card";
    }

    /**
     * Cria de uma conta
     *
     * @return array
     */
    public function handle($account): array
    {
        $this->findAccountData($account);

        $url = $this->requestUrl();

        $request = $this->sendRequest(
            method: 'get',
            url:    $url,
            action: 'FIND_CARD',
            params: []
        );

        return $this->formatDetailsResponse($request);
    }
}
