<?php

namespace App\Domains\User;

use App\Domains\BaseDomain;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\InternalErrorException;

class Update extends BaseDomain
{
    /**
     * Id do usuário
     *
     * @var string
     */
    protected string $id;

    /**
     * Empresa
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
        $this->id        = $id;
        $this->companyId = $companyId;
        $this->name      = $name;
        $this->email     = $email;
        $this->type      = $type;

        $this->cryptPassword($password);
    }

    /**
     * Id do usuário
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
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
     * Nome da empresa
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }


    /**
     * Email
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Senha
     *
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Tipo
     *
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Encripta a senha
     *
     * @param string|null $password
     *
     * @return void
     */
    protected function cryptPassword(?string $password): void
    {
        $this->password = !is_null($password) ? Hash::make($password) : null;
    }

    /**
     * Email deve ser únicos no sistema
     *
     * @return void
     */
    protected function checkEmail($isUniqueEmail): void
    {
        if (is_null($this->email)) {
            return;
        }
        if (!$isUniqueEmail) {
            throw new InternalErrorException(
                'Não é possível adicionar o E-mail informado',
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
        if (is_null($this->type)) {
            return;
        }
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
    public function handle($isUniqueEmail): self
    {
        $this->checkEmail($isUniqueEmail);
        $this->checkType();

        return $this;
    }
}
