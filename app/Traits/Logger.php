<?php

namespace App\Traits;

use Throwable;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Str;
use App\Exceptions\BaseException;
use Illuminate\Support\Facades\Log;

trait Logger
{
    /**
     * Manipula a exception para deixar mais legivel
     *
     * @param Throwable $e
     *
     * @return array
     */
    protected static function beautifyException(Throwable $e): array
    {
        return [
            'msg'  => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
        ];
    }

    /**
     * Cria um log padrão para o Chronos
     *
     * @param string         $description      Não é usado no filtro, pode ser descritivo
     * @param string         $action           Usado no filtro, texto simples
     * @param mixed          $value            Conteúdo do log
     * @param Throwable|null $error            Erro ocorrido (caso seja um log de erro)
     * @param string|null    $idUser           ID do usuário, usuário logado do JWT caso null
     * @param string|null    $idCompany        ID da empresa, empresa do usuário logado do JWT caso null
     * @param string|null    $entityId         ID da entidade
     * @param string|null    $entity           Entidade
     * @param string         $logLevel         Nivel do log: DEBUG/ERROR/INFO/CRITICAL/WARNING
     * @param string         $logType          Tipo de log: SERVER/AUDIT
     * @param Carbon|null    $requestDatetime  Datetime do ínicio de uma request
     * @param Carbon|null    $responseDatetime Datetime da response de uma request
     * @param bool           $includeAuthContext se for necessaria autenticação
     *
     * @return array
     */
    public function createLog(
        string $description,
        string $action,
        $value,
        Throwable $error = null,
        string $idUser = null,
        string $idCompany = null,
        string $entityId = null,
        string $entity = null,
        string $logLevel = 'DEBUG',
        string $logType = 'SERVER',
        Carbon $requestDatetime = null,
        Carbon $responseDatetime = null,
        bool $includeAuthContext = false
    ): array {
        try {
            $value    = is_array($value) ? $value : [$value];
            $uuid     = Uuid::uuid4()->toString();
            $logLevel = mb_strtoupper($logLevel);

            if ($error) {
                $errorLog = self::beautifyException($error);
                $value    = array_merge($value, compact('errorLog'));
            }

            $getUserResponse = $this->getAuthContext();
            $requestDuration = null;

            if ($requestDatetime && $responseDatetime) {
                $requestDuration = (float) $requestDatetime->diffInMicroseconds($responseDatetime)
                    / Carbon::MICROSECONDS_PER_MILLISECOND;
            }

            $context = [
                'description'                   => $description,
                'action'                        => $action,
                'entity_id'                     => $entityId,
                'entity'                        => mb_strtoupper($entity),
                'log_type'                      => mb_strtoupper($logType),
                'log_level'                     => $logLevel,
                'user_id'                       => $idUser ?: data_get($getUserResponse, 'user_id'),
                'company_id'                    => $idCompany ?: data_get($getUserResponse, 'company_id'),
                'admin_user_id'                 => data_get($getUserResponse, 'admin_user_id'),
                'value'                         => $value,
                'uuid'                          => $uuid,
                'date'                          => Carbon::now(),
                'request_datetime'              => $requestDatetime?->format('Y-m-d\TH:i:s.uP'),
                'response_datetime'             => $responseDatetime?->format('Y-m-d\TH:i:s.uP'),
                'request_duration_milliseconds' => $requestDuration,
                'origin'                        => request()->headers->get('origin'),
                'referer'                       => request()->headers->get('referer'),
                'url'                           => request()->url(),
                'ip'                            => request()->server('REMOTE_ADDR'),
                'endpoint'                      => [
                    'url'    => request()->url(),
                    'method' => request()->getMethod(),
                ],
            ];

            Log::channel('log_service')->{$logLevel}($context['description'], $context);
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'Falha ao enviar Log',
                'data'    => $e,
            ];
        }

        return [
            'success' => true,
            'message' => 'Log feito com sucesso',
            'data'    => $context,
            'uuid'    => $uuid,
        ];
    }

    /**
     * Verifica se é necessario conferir autenticação de usuario
     *
     * @param boolean $includeAuthContext
     * @return array
     */
    protected function getAuthData(bool $includeAuthContext): array 
    {
        return $includeAuthContext 
            ? $this->getAuthContext() 
            : $this->getEmptyAuthContext();
    }

     /**
      * Obtém informações do usuário autenticado
      *
      * @return array
      */
    protected function getAuthContext(): array
    {
        if (!app()->bound('auth') || !auth()->check()) {
            return $this->getEmptyAuthContext();
        }

        try {
            $user = auth()->user();
            return [
                'user_id' => $user->getAuthIdentifier(),
                'company_id' => $user->company_id ?? null,
                'admin_user_id' => $user->admin_user_id ?? null,
                'auth_at' => now()->toIso8601String()
            ];
        } catch (\Throwable $e) {
            return $this->getEmptyAuthContext();
        }
    }

    /**
     * Retorna contexto vazio padronizado
     *
     * @return array
     */
    private function getEmptyAuthContext(): array
    {
        return [
            'user_id' => null,
            'company_id' => null,
            'admin_user_id' => null,
            'auth_at' => null
        ];
    }

    /**
     * Tratamento padrão de exceptions
     *
     * @param Throwable   $exception
     * @param mixed|null  $data
     * @param string|null $idEntity
     * @param string|null $entity
     * @param string      $level
     *
     * @return void
     */
    public function defaultErrorHandling(
        Throwable $exception,
        $data = null,
        string $idEntity = null,
        string $entity = null,
        string $level = 'ERROR'
    ): void {
        // Caso seja um erro esperado BaseException, continua sem criar log
        // de erro inesperado
        if ($exception instanceof BaseException) {
            throw $exception;
        }

        $description = get_called_class();

        // Formatando o nome do método que o erro ocorreu
        $trace    = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $function = $trace[count($trace) - 1]['function'];
        $action   = mb_strtoupper(Str::snake($function)) . '_ERROR';

        $this->createLog(
            $description,
            $action,
            $data,
            $exception,
            null,
            null,
            $idEntity,
            $entity,
            $level
        );

        dump($exception);

        // Para evitar propagação de log duplicado, o erro é propagado como
        // BaseException
        throw new BaseException(
            'UNKNOW_ERROR_TRY_AGAIN',
            0
        );
    }
}
