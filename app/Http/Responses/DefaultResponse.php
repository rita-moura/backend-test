<?php

namespace App\Http\Responses;

use Exception;
use App\Http\Responses\InternalError;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DefaultResponse
{
    /**
     * @var array
     */
    private $parameters;

    /**
     * @param mixed                $data    Dados de retorno
     * @param bool                 $success Processado com sucesso
     * @param array<InternalError> $errors  Lista de erros internos acontecidos
     * @param int                  $code    HTTP Code response
     */
    public function __construct(
        $data = null,
        bool $success = true,
        array $errors = [],
        int $code = 200
    ) {
        $this->parameters = [
            'success' => $success,
            'request' => request()->fullUrl(),
            'method' => strtoupper(request()->method()),
            'code' => $code,
        ];

        $this->parameters['data'] = (empty($data))
            ? null
            : $data;

        // Formatando dados paginados que serão retornados
        if ($data instanceof ResourceCollection && $data->resource instanceof LengthAwarePaginator) {
            $this->formatPaginatedData($data);
        }

        // Formatando os erros que serão retornados
        if (count($errors)) {
            $this->parameters['errors'] = array_map(function ($error) {
                if (!$error instanceof InternalError) {
                    throw new Exception('Error inserido não é do tipo InternalError');
                }

                return $error->toArray();
            }, $errors);
        }
    }

    /**
     * Get the HTTP status code for the response.
     *
     * @return int
     */
    public function getCode(): int
    {
        return $this->parameters['code'];
    }

    /**
     * Retorna o array de parametros dessa classe
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->parameters;
    }

    /**
     * Metodo para pegar algum parametro declarado na classe,
     * retorna null se não existir
     *
     * @param  string $parameter
     *
     * @return mixed
     */
    public function __get($parameter)
    {
        return $this->parameters[$parameter] ?? null;
    }

    /**
     * Formata os dados do tipo paginação que são retornados
     * na response do serviço de cartões
     *
     * @param ResourceCollection|LengthAwarePaginator $data
     * @return void
     */
    private function formatPaginatedData($data): void
    {
        $paginator = $data->resource;

        // Adiciona os query params da request no atributo request
        $requestParams = [];
        parse_str(request()->getQueryString(), $requestParams);

        if (count($requestParams)) {
            $this->parameters['request'] = request()->url() . '?' . http_build_query($requestParams);
        }

        // Adiciona os parametros get da url, ex: filtros para paginação
        $paginator->appends($requestParams);

        $this->parameters = array_merge($this->parameters, $paginator->toArray());

        if ($this->parameters['total'] === 0) {
            $this->parameters['data'] = null;
        }
    }
}
