<?php

namespace App\UseCases\Card;

use Throwable;
use App\UseCases\BaseUseCase;
use App\Repositories\Card\Create;
use App\Repositories\Account\FindByUser;
use App\Repositories\Card\CanUseExternalId;
use App\Domains\Card\Register as RegisterDomain;
use App\Integrations\Banking\Card\Register as LinkCard;
use App\Integrations\Banking\Card\Find;


class Register extends BaseUseCase
{
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

    /**
     * Cartão
     *
     * @var array
     */
    protected array $card;

    public function __construct(string $userId, string $pin, string $cardId)
    {
        $this->userId = $userId;
        $this->pin    = $pin;
        $this->cardId = $cardId;
    }

    /**
     * Checa se é possível registrar
     *
     * @return RegisterDomain
     */
    protected function checkIfCanRegister(): RegisterDomain
    {
        $account = (new FindByUser($this->userId))->handle();
        
        $externalId = (new CanUseExternalId($this->cardId))->handle();
        
        return (new RegisterDomain($this->userId, $this->pin, $this->cardId))->handle($account, $externalId);
    }

    /**
     * Registra o cartão
     *
     * @return void
     */
    protected function register(RegisterDomain $domain): void
    {
        $account = (new FindByUser($this->userId))->handle();
        
        $params = [
            'pin' => $domain->getPin(),
            'id'  => $domain->getCardId()
        ];

        $this->card = (new LinkCard())->handle($account, $params);
    }

    /**
     * Registra no banco de dados
     *
     * @return void
     */
    protected function store(RegisterDomain $domain): void
    {
        $data = [
            'account_id' => $domain->getAccountId(),
            'external_id' => $domain->getCardId(),
            'status'      => 'ACTIVE',
        ];

        (new Create())->handle($data);
    }

    /**
     * Registra o cartão
     */
    public function handle(): array
    {
        $this->card = [];
        try {
            $domain = $this->checkIfCanRegister();
            $this->register($domain);
            $this->store($domain);
        } catch (Throwable $th) {
            $this->defaultErrorHandling(
                $th,
                [
                    'userId' => $this->userId,
                    'pin'    => $this->pin,
                    'cardId' => $this->cardId,
                ]
            );
        }

        return $this->card;
    }
}
