<?php

namespace App\UseCases\Account;

use Throwable;
use App\UseCases\BaseUseCase;
use App\Domains\Account\VerifyAccount;
use App\Repositories\Account\FindByUser;
use App\Integrations\Banking\Account\Find;

class Show extends BaseUseCase
{
    /**
     * Id do usuário
     *
     * @var string
     */
    protected string $userId;

    /**
     * Conta
     *
     * @var array
     */
    protected array $account;

    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Encontra a conta
     *
     * @return void
     */
    protected function find(): void
    {
        $account = (new FindByUser($this->userId))->handle() ?? null;

        (new VerifyAccount())->handle($account);

        $this->account = (new Find($this->userId))->handle($account);
    }

    /**
     * Retorna a conta
     */
    public function handle(): array
    {
        try {
            $this->find();
        } catch (Throwable $th) {
            $this->defaultErrorHandling(
                $th,
                [
                    'userId' => $this->userId,
                ]
            );
        }

        return $this->account;
    }
}
