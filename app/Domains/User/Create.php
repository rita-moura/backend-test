<?php

namespace App\Domains\User;

use App\Domains\BaseDomain;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\InternalErrorException;

class Create extends BaseDomain
{
    /**
     * Empresa
     *
     * @var string
     */
    protected string $companyId;

    /**
     * Nome
     *
     * @var string
     */
    protected string $name;

    /**
     * CPF
     *
     * @var string
     */
    protected string $documentNumber;

    /**
     * Email
     *
     * @var string
     */
    protected string $email;

    /**
     * Senha
     *
     * @var string
     */
    protected string $password;

    /**
     * Tipo
     *
     * @var string
     */
    protected string $type;

    public function __construct(
        string $companyId,
        string $name,
        string $documentNumber,
        string $email,
        string $password,
        string $type
    ) {
        $this->companyId      = $companyId;
        $this->name           = $name;
        $this->documentNumber = $documentNumber;
        $this->email          = $email;
        $this->type           = $type;

        $this->cryptPassword($password);
    }


    /**
     * Empresa
     *
     * @return string
     */
    public function getCompanyId(): string
    {
        return $this->companyId;
    }

    /**
     * Nome
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * CPF
     *
     * @return string
     */
    public function getDocumentNumber(): string
    {
        return $this->documentNumber;
    }

    /**
     * Email
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Senha
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Tipo
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Encripta a senha
     *
     * @param string $password
     *
     * @return void
     */
    protected function cryptPassword(string $password): void
    {
        $this->password = Hash::make($password);
    }

    /**
     * Email deve ser únicos no sistema
     *
     * @return void
     */
    protected function checkEmail($isUniqueEmail): void
    {
        if (!$isUniqueEmail) {
            throw new InternalErrorException(
                'Não é possível adicionar o E-mail informado',
                0
            );
        }
    }

    /**
     * Email deve ser únicos no sistema
     *
     * @return void
     */
    protected function checkDocumentNumber($isUniqueDocument): void
    {
        if (!$isUniqueDocument) {
            throw new InternalErrorException(
                'Não é possível adicionar o CPF informado',
                0
            );
        }
    }

    /**
     * Valida o tipo
     *
     * @return void
     */
    protected function checkType(): void
    {
        if (!in_array($this->type, ['USER', 'VIRTUAL', 'MANAGER'])) {
            throw new InternalErrorException(
                'Não é possível adicionar o tipo informado',
                0
            );
        }
    }

    /**
     * Checa se é possível realizar a criação do usuário
     *
     * @return self
     */
    public function handle($isUniqueEmail, $isUniqueDocument): self
    {
        $this->checkEmail($isUniqueEmail);
        $this->checkDocumentNumber($isUniqueDocument);
        $this->checkType();

        return $this;
    }
}
