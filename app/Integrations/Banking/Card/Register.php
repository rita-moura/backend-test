<?php

namespace App\Integrations\Banking\Card;

use App\Integrations\Banking\Gateway;
use App\Exceptions\InternalErrorException;

class Register extends Gateway
{
    /**
     * Id externo do cartão
     *
     * @var string
     */
    protected string $externalCardId;

    /**
     * Id externo da conta
     *
     * @var string
     */
    protected string $externalAccountId;


    public function __construct() {}

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
     * Busca os dados de conta
     *
     * @return void
     */
    protected function findCardData($account): void
    {

        if (is_null($account)) {
            throw new InternalErrorException(
                'CARD_NOT_FOUND',
                149001001
            );
        }

        $this->externalCardId = $account['external_id'];
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
    public function handle($account, array $params): array
    {
        $this->findAccountData($account);
        $this->findCardData($account);

        $url = $this->requestUrl();

        $request = $this->sendRequest(
            method: 'post',
            url:    $url,
            action: 'REGISTER_CARD',
            params: $params
        );

        return $this->formatDetailsResponse($request);
    }
}
