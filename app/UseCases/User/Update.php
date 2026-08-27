<?php

namespace App\UseCases\User;

use Throwable;
use App\UseCases\BaseUseCase;
use App\UseCases\Params\User\UpdateParams;
use App\Domains\User\Update as UpdateDomain;
use App\Repositories\User\Update as UpdateRepository;
use App\Repositories\User\CanUseEmail;


class Update extends BaseUseCase
{
    /**
     * @var UpdateParams
     */
    protected UpdateParams $params;

    /**
     * Usuário
     *
     * @var array
     */
    protected array $user;

    public function __construct(
        UpdateParams $params
    ) {
        $this->params = $params;
    }

    /**
     * Valida o usuário
     *
     * @return UpdateDomain
     */
    protected function validateUser(): UpdateDomain
    {
    
        if ($this->params->getEmail() !== null) {
            $isUniqueEmail = (new CanUseEmail($this->params->getEmail()))->handle();
        }
        if ($this->params->getEmail() === null) {
            $isUniqueEmail = null;
        }

        return (new UpdateDomain(
            $this->params->getId(),
            $this->params->getCompanyId(),
            $this->params->getName(),
            $this->params->getEmail(),
            $this->params->getPassword(),
            $this->params->getType()
        ))->handle($isUniqueEmail);
    }

    /**
     * Modifica o usuário
     *
     * @param UpdateDomain $domain
     *
     * @return void
     */
    protected function updateUser(UpdateDomain $domain): void
    {
        $params = [
            'name'     => $domain->getName(),
            'email'    => $domain->getEmail(),
            'password' => $domain->getPassword(),
            'type'     => $domain->getType(),
        ];

        $this->user = (new UpdateRepository())->handle($domain->getId(), $params);
    }

    /**
     * Modifica um usuário
     */
    public function handle()
    {
        try {
            $userDomain = $this->validateUser();
            $this->updateUser($userDomain);
        } catch (Throwable $th) {
            $this->defaultErrorHandling(
                $th,
                [
                    'params' => $this->params->toArray(),
                ]
            );
        }

        return $this->user;
    }
}
