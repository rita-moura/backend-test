<?php

namespace App\Exceptions;

use Throwable;
use Carbon\Carbon;
use App\Traits\Logger;
use Illuminate\Http\Request;
use App\Exceptions\BaseException;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\InternalError;
use App\Http\Responses\DefaultResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class Handler extends ExceptionHandler
{
    use Logger;

    /**
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        // Impedindo a escrita da Exception de política no arquivo
        // de log do laravel
        if ($exception instanceof PolicyException) {
            return;
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof NotFoundHttpException) {
            $response = new DefaultResponse(
                null,
                false,
                [],
                404
            );

            return response()->json($response->toArray(), 404);
        }

        // Pegando a exception de erros esperados
        // e evitando que erros inesperados tratados para não terem logs duplicados
        // Retorne 200
        if (
            $exception instanceof BaseException
            && get_class($exception) !== BaseException::class
        ) {
            return parent::render($request, $exception);
        }

        // Não realiza o log da exception padrão do FormRequest (The given data was invalid)
        if ($exception instanceof ValidationException) {
            return parent::render($request, $exception);
        }

        // Erro de não authorizado
        if ($exception instanceof UnauthorizedHttpException) {
            $response = new DefaultResponse(
                null,
                false,
                [
                    new InternalError(
                        'UNAUTHORIZED'
                    )
                ],
                401
            );

            return response()->json($response->toArray(), 401);
        }

        $route = $request->route();

        if (!is_null($route)) {
            $context = [
                'endpoint' => [
                    'method' => $route->methods[0],
                    'url'    => $request->url()
                ],
                'request'  => [
                    'query' => $request->query(),
                    'body'  => $request->post()
                ],
                'request_time' => Carbon::now()->setTimezone(config('app.timezone'))
            ];

            $this->createLog(
                'Erro na execução da API ' . $route->methods[0] . ' ' . $route->uri,
                'REQUEST_'
                    . strtoupper($route->methods[0] . '_' . $this->applyUuidRegexPattern($route->uri))
                    . '_ERROR',
                $context,
                $exception
            );
        }

        dump($exception);

        $response = new DefaultResponse(
            null,
            false,
            [
                new InternalError(
                    'INTERNAL_SERVER_ERROR'
                )
            ],
            500
        );

        return response()->json($response->toArray(), 500);
    }

    /**
     * Convert a validation exception into a JSON response.
     *
     * @param  Request  $request
     * @param  ValidationException  $exception
     * @return JsonResponse
     */
    protected function invalidJson($request, ValidationException $exception): JsonResponse
    {
        $errors = [];
        foreach ($exception->errors() as $fieldError) {
            foreach ($fieldError as $error) {
                $errors[] = new InternalError(
                    $error
                );
            }
        }

        $response = new DefaultResponse(
            null,
            false,
            $errors,
            422
        );

        return response()->json($response->toArray(), 422);
    }

    /**
     * Aplica um regex na url informada utilizando o pattern de uuid para utilizarmos
     * em nossos logs
     *
     * @param string $path
     *
     * @return string
     */
    protected function applyUuidRegexPattern(string $path): string
    {
        $pattern = '/[A-Fa-f0-9]{8}\-?[A-Fa-f0-9]{4}\-?[A-Fa-f0-9]{4}\-?[A-Fa-f0-9]{4}\-?[A-Fa-f0-9]{12}/';

        return str_replace(['/', '-'], '_', preg_replace($pattern, 'UUID', $path));
    }
}
