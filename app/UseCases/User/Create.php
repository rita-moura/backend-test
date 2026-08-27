<?php

namespace App\UseCases\User;

use Throwable;
use App\UseCases\BaseUseCase;
use App\UseCases\Params\User\CreateParams;
use App\Domains\User\Create as CreateDomain;
use App\Repositories\User\Create as CreateRepository;
use App\Repositories\User\CanUseDocumentNumber;
use App\Repositories\User\CanUseEmail;

class Create extends BaseUseCase
{
    /**
     * @var CreateParams
     */
    protected CreateParams $params;

    /**
     * Usuário
     *
     * @var array
     */
    protected array $user;

    public function __construct(
        CreateParams $params
    ) {
        $this->params = $params;
    }

    /**
     * Valida o usuário
     *
     * @return CreateDomain
     */
    protected function validateUser(): CreateDomain
    {
        $isUniqueEmail = (new CanUseEmail($this->params->getEmail()))->handle();
        $isUniqueDocument = (new CanUseDocumentNumber($this->params->getDocumentNumber()))->handle();

        return (new CreateDomain(
            $this->params->getCompanyId(),
            $this->params->getName(),
            $this->params->getDocumentNumber(),
            $this->params->getEmail(),
            $this->params->getPassword(),
            $this->params->getType(),
        ))->handle($isUniqueEmail, $isUniqueDocument);
    }

    /**
     * Cria o usuário
     *
     * @param CreateDomain $domain
     *
     * @return void
     */
    protected function createUser(CreateDomain $domain): void
    {
        $params = [
            'company_id'      => $domain->getCompanyId(),
            'name'            => $domain->getName(),
            'document_number' => $domain->getDocumentNumber(),
            'email'           => $domain->getEmail(),
            'password'        => $domain->getPassword(),
            'type'            => $domain->getType(),
        ];
    
        $this->user = (new CreateRepository())->handle($params);
    }

    /**
     * Cria um usuário
     */
    public function handle()
    {
        try {
            $userDomain = $this->validateUser();
            $this->createUser($userDomain);
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
