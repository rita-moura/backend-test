<?php

namespace App\UseCases\Params\User;

use App\UseCases\Params\BaseParams;

class UpdateParams extends BaseParams
{
    /**
     * Id do usuário
     *
     * @var string
     */
    protected string $id;

    /**
     * Id da empresa
     *
     * @var string
     */
    protected string $companyId;

    /**
     * Nome
     *
     * @var string|null
     */
    protected ?string $name;

    /**
     * Email
     *
     * @var string|null
     */
    protected ?string $email;

    /**
     * Senha
     *
     * @var string|null
     */
    protected ?string $password;

    /**
     * Tipo
     *
     * @var string|null
     */
    protected ?string $type;

    public function __construct(
        string $id,
        string $companyId,
        ?string $name,
        ?string $email,
        ?string $password,
        ?string $type
    ) {
        $this->id             = $id;
        $this->companyId      = $companyId;
        $this->name           = $name;
        $this->email          = $email;
        $this->password       = $password;
        $this->type           = $type;
    }

    /**
     * Retorna o id do usuário
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Retorna o id da empresa
     *
     * @return string
     */
    public function getCompanyId(): string
    {
        return $this->companyId;
    }

    /**
     * Retorna o nome
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Retorna o email
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Retorna a senha
     *
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Retorna o tipo
     *
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }
}
