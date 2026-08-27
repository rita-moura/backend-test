<?php

namespace App\UseCases\Account;

use Throwable;
use App\UseCases\BaseUseCase;
use App\Repositories\Account\FindByUser;
use App\Repositories\Account\UpdateStatus as RepositoryUpdateStatus;
use App\Integrations\Banking\Account\UpdateStatus as IntegrationUpdateStatus;
use App\Domains\Account\VerifyAccount;

class Active extends BaseUseCase
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
     * Atualiza no banco de dados
     *
     * @return void
     */
    protected function updateDatabase(): void
    {
        (new RepositoryUpdateStatus($this->userId, 'active'))->handle();
    }

    /**
     * Atualiza a conta
     *
     * @return void
     */
    protected function updateStatus(): void
    {
        $account = (new FindByUser($this->userId))->handle() ?? null;

        (new VerifyAccount())->handle($account);

        $this->account = (new IntegrationUpdateStatus($this->userId, 'active'))->handle($account);
    }

    /**
     * Ativa a conta
     */
    public function handle(): void
    {
        try {
            $this->updateDatabase();
            $this->updateStatus();
        } catch (Throwable $th) {
            $this->defaultErrorHandling(
                $th,
                [
                    'userId' => $this->userId,
                ]
            );
        }
    }
}
