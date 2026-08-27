<?php

namespace App\UseCases\Params\User;

use App\UseCases\Params\BaseParams;

class CreateParams extends BaseParams
{
    /**
     * Id da empresa
     *
     * @var string
     */
    protected string $companyId;

    /**
     * Nome do usuário
     *
     * @var string
     */
    protected string $name;

    /**
     * CPF do usuário
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
        $this->password       = $password;
        $this->type           = $type;
    }

    /**
     * Função para retornar o id da empresa
     *
     * @return string
     */
    public function getCompanyId(): string
    {
        return $this->companyId;
    }

    /**
     * Função para retornar o nome do usuário
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
    
    /**
     * Função para retornar o documento do usuário
     *
     * @return string
     */
    public function getDocumentNumber(): string
    {
        return $this->documentNumber;
    }

    /**
     * Função para retornar o email do usuário
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Função para retornar a senha do usuário
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Função para retornar o tipo do usuário
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}
