<?php

namespace App\Domains\Company;

use App\Domains\BaseDomain;

class Update extends BaseDomain
{
    /**
     * Id da empresa
     *
     * @var string
     */
    protected string $id;

    /**
     * Nome
     *
     * @var string
     */
    protected string $name;

    public function __construct(string $id, string $name)
    {
        $this->id   = $id;
        $this->name = $name;
    }

    /**
     * Retorna o id da empresa
     *
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Retorna o nome da empresa
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }


    /**
     * Checa se é possível modificar a empresa
     *
     * @return self
     */
    public function handle(): self
    {
        // Nenhuma validação necessária

        return $this;
    }
}
