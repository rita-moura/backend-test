<?php

namespace App\UseCases\User;

use Throwable;
use App\UseCases\BaseUseCase;
use App\Domains\User\Create as CreateUserDomain;
use App\Repositories\Token\Create as CreateToken;
use App\UseCases\Params\User\CreateFirstUserParams;
use App\Domains\Company\Create as CreateCompanyDomain;
use App\Repositories\User\Create as CreateUserRepository;
use App\Repositories\User\CanUseDocumentNumber as CanUseUnique;
use App\Repositories\User\CanUseEmail;
use App\Repositories\Company\Create as CreateCompanyRepository;
use App\Repositories\Company\CanUseDocumentNumber;



class CreateFirstUser extends BaseUseCase
{
    /**
     * @var CreateFirstUserParams
     */
    protected CreateFirstUserParams $params;

    /**
     * Token de acesso
     *
     * @var string
     */
    protected string $token;

    /**
     * Empresa
     *
     * @var array
     */
    protected array $company;

    /**
     * Usuário
     *
     * @var array
     */
    protected array $user;

    public function __construct(
        CreateFirstUserParams $params
    ) {
        $this->params = $params;
    }

    /**
     * Valida a empresa
     *
     * @return CreateCompanyDomain
     */
    protected function validateCompany(): CreateCompanyDomain
    {
        $documentIsValid = (new CanUseDocumentNumber($this->params->getCompanyDocumentNumber()))->handle();

        return (new CreateCompanyDomain(
            $this->params->getCompanyName(),
            $this->params->getCompanyDocumentNumber()
        ))->handle($documentIsValid);
    }

    /**
     * Cria a empresa
     *
     * @param CreateCompanyDomain $domain
     *
     * @return void
     */
    protected function createCompany(CreateCompanyDomain $domain): void
    {

        $data = [
            'name'            => $domain->getName(),
            'document_number' => $domain->getDocumentNumber(),
        ];

        $this->company = (new CreateCompanyRepository())->handle($data);
    }

    /**
     * Valida o usuário
     *
     * @return CreateUserDomain
     */
    protected function validateUser(): CreateUserDomain
    {
        $isUniqueEmail = (new CanUseEmail($this->params->email))->handle();
        $isUniqueDocument = (new CanUseUnique($this->params->userDocumentNumber))->handle();

        return (new CreateUserDomain(
            $this->company['id'],
            $this->params->getUserName(),
            $this->params->getUserDocumentNumber(),
            $this->params->getEmail(),
            $this->params->getPassword(),
            'MANAGER'
        ))->handle($isUniqueEmail, $isUniqueDocument);
    }

    /**
     * Cria o usuário
     *
     * @param CreateUserDomain $domain
     *
     * @return void
     */
    protected function createUser(CreateUserDomain $domain): void
    {
        $params = [
            'company_id'      => $domain->getCompanyId(),
            'name'            => $domain->getName(),
            'document_number' => $domain->getDocumentNumber(),
            'email'           => $domain->getEmail(),
            'password'        => $domain->getPassword(),
            'type'            => $domain->getType(),
        ];

        $this->user = (new CreateUserRepository())->handle($params);
    }

    /**
     * Criação de token de acesso
     *
     * @return void
     */
    protected function createToken(): void
    {
        $this->token = (new CreateToken($this->user['id']))->handle();
    }

    /**
     * Cria um usuário MANAGER e a empresa
     */
    public function handle()
    {
        try {
            $companyDomain = $this->validateCompany();
            $this->createCompany($companyDomain);

            $userDomain = $this->validateUser();
            $this->createUser($userDomain);

            $this->createToken();
        } catch (Throwable $th) {
            $this->defaultErrorHandling(
                $th,
                [
                    'params' => $this->params->toArray(),
                ]
            );
        }

        return [
            'user'    => $this->user,
            'company' => $this->company,
            'token'   => $this->token,
        ];
    }
}
