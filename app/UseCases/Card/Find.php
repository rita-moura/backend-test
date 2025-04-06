<?php

namespace App\UseCases\Card;

use Throwable;
use App\UseCases\BaseUseCase;
use App\Integrations\Banking\Card\Find as FindIntegration;
use App\Repositories\Account\FindByUser;


class Find extends BaseUseCase
{
    /**
     * Id do usuário
     *
     * @var string
     */
    protected string $userId;

    /**
     * Dados do cartão
     *
     * @var array
     */
    protected array $card;

    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Busca os dados do cartão
     *
     * @return void
     */
    protected function findCardData(): void
    {
        $account = (new FindByUser($this->userId))->handle();
        $this->card = (new FindIntegration($this->userId))->handle($account);
    }

    /**
     * Retorna o cartão do usuário
     *
     * @return string
     */
    public function handle(): array
    {
        $this->findCardData();

        return $this->card;
    }

}