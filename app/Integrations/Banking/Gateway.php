<?php

namespace App\Integrations\Banking;

use Carbon\Carbon;
use App\Traits\Logger;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\PendingRequest;
use App\Exceptions\BankingRequestException;
use Psr\SimpleCache\InvalidArgumentException;

class Gateway
{
    use Logger;

    /**
     * @var string|null
     */
    protected string|null $authenticationToken = null;

    /**
     * Envio de Requisição para BaaS
     *
     * @param string $method
     * @param string $url
     * @param string $action
     * @param array  $params
     * @param bool   $logActive
     *
     * @return Response
     */
    public function sendRequest(
        string $method,
        string $url,
        string $action,
        array $params = [],
        bool $logActive = true
    ): Response {
        $this->createLog(
            description: 'Request para a BaaS',
            action:      "GATEWAY_BANKING_REQUEST_CALL_{$action}_INFO",
            value:       compact('url', 'method', 'params'),
            error:       null,
            idUser:      null,
            idCompany:   null,
            entityId:    null,
            entity:      'BANKING'
        );

        $requestDatetime = Carbon::now();

        $response = $this->authenticatedClient()
            ->{$method}(
                $url,
                $params
            );

        $responseDatetime = Carbon::now();

        $code = $response->status();
        $body = 'Log Desativado';

        if ($logActive) {
            $body = $response->json() ?? $response->body();
        }

        if (!$response->successful()) {
            $expectedStatusCode = [
                401,
                404,
                429,
            ];
            $logLevel = in_array($response->status(), $expectedStatusCode, true)
                ? 'DEBUG'
                : 'ERROR';

            $this->createLog(
                description:      'Erro desconhecido ao enviar request para o Saas',
                action:           "GATEWAY_BANKING_REQUEST_CALL_{$action}_ERROR",
                value:            compact('url', 'method', 'params', 'code', 'body'),
                error:            null,
                idUser:           null,
                idCompany:        null,
                entityId:         null,
                entity:           'BANKING',
                logLevel:         $logLevel,
                logType:          'SERVER',
                requestDatetime:  $requestDatetime,
                responseDatetime: $responseDatetime,
            );

            return $response;
        }

        $this->createLog(
            description:      'Sucesso ao enviar request para a BaaS',
            action:           "GATEWAY_BANKING_REQUEST_CALL_{$action}_SUCCESS",
            value:            compact('url', 'method', 'params', 'code', 'body'),
            error:            null,
            idUser:           null,
            idCompany:        null,
            entityId:         null,
            entity:           'BANKING',
            logLevel:         'DEBUG',
            logType:          'SERVER',
            requestDatetime:  $requestDatetime,
            responseDatetime: $responseDatetime,
        );

        return $response;
    }

    /**
     * Obtém um client http com o header de autenticação.
     *
     * @return PendingRequest
     */
    public function authenticatedClient(): PendingRequest
    {
        $this->generateAuthenticationToken();

        return $this->newClient()
            ->withToken($this->getAuthenticationToken());
    }

    /**
     * Função responsável por gerar um novo token ou obter um toke válido já gerado.
     *
     * @return void
     */
    public function generateAuthenticationToken(): void
    {
        $token = Cache::get('banking_authentication_token');

        if ($token !== null) {
            $this->setAuthenticationToken($token);

            return;
        }

        $data = $this->newClient()
            ->post(
                $this->getAuthUrl(),
                [
                    'grant_type'    => 'client_credentials',
                    'client_id'     => $this->getClientId(),
                    'client_secret' => config('auth.banking_client_secret'),
                ]
            )
            ->json();

        $token = $data['access_token'] ?? null;

        if ($token !== null) {
            Cache::put('banking_authentication_token', $token, 180);
            $this->setAuthenticationToken($token);
        }
    }

    /**
     * Função responsável por gerar um novo cliente http.
     *
     * @return PendingRequest
     */
    public function newClient(): PendingRequest
    {
        return Http::baseUrl($this->getBaseUrl())
            ->withHeaders(
                [
                    'Accept'          => 'application/json',
                    'Accept-Encoding' => 'gzip, deflate, br',
                    'Content-Type'    => 'application/json',
                ]
            );
    }

    /**
     * Obtém a url base da API da BaaS.
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        return config('auth.banking_base_url');
    }

    /**
     * Obtém a uri de geração de token.
     *
     * @return string
     */
    public function getAuthUrl(): string
    {
        return '/auth/' . $this->getClientId() . '/token';
    }

    /**
     * Obtém o clientId a ser usado na requisação.
     *
     * @return string
     */
    public function getClientId(): string
    {
        return config('auth.banking_client_id');
    }

    /**
     * Obtém o token de autenticação gerado.
     *
     * @return string|null
     */
    public function getAuthenticationToken(): string|null
    {
        return $this->authenticationToken;
    }

    /**
     * Seta o token de autenticação gerado
     *
     * @param string|null $authenticationToken
     */
    public function setAuthenticationToken(string|null $authenticationToken): void
    {
        $this->authenticationToken = $authenticationToken;
    }

    /**
     * Invalida o token de modo a gerar um novo.
     *
     * @throws InvalidArgumentException
     */
    public function invalidateAuthenticationToken(): void
    {
        if (Cache::has('banking_authentication_token')) {
            Cache::delete('banking_authentication_token');
            $this->setAuthenticationToken(null);
        }
    }

    /**
     * Formata a resposta obtida pela BaaS no cenário de listas.
     *
     * @param Response $response
     *
     * @return array
     */
    public function formatListResponse(Response $response): array
    {
        $formattedResponse = $this->formatResponse($response);

        $actualPage = (int) $response->header('x-page');
        $totalPages = (int) $response->header('x-page-count');

        return (new GatewayListResponse(
            $formattedResponse['data'],
            $actualPage,
            $totalPages
        ))->response();
    }

    /**
     * Formata a resposta obtida pelo BaaS.
     *
     * @param Response $response
     *
     * @return array
     */
    protected function formatResponse(Response $response): array
    {
        $responseCollect = collect($response->json());

        if (!$response->successful()) {
            throw new BankingRequestException(
                message:    'BANKING_REQUEST_ERROR',
                response:   $responseCollect->toArray(),
                statusCode: $response->status(),
            );
        }

        $data = $responseCollect->except(['error'])->toArray();

        return [
            'data' => $data,
        ];
    }

    /**
     * Formata a resposta obtida pelo BaaS no cenário de detalhes.
     *
     * @param Response $response
     *
     * @return array
     */
    protected function formatDetailsResponse(Response $response): array
    {
        $formattedResponse = $this->formatResponse($response);

        return (new GatewayDetailsResponse(
            $formattedResponse['data']
        ))->response();
    }
}
