<?php

namespace App\Domains\Company;

use App\Domains\BaseDomain;
use App\Exceptions\InternalErrorException;

class Create extends BaseDomain
{
    /**
     * Nome
     *
     * @var string
     */
    protected string $name;

    /**
     * CNPJ
     *
     * @var string
     */
    protected string $documentNumber;

    public function __construct(string $name, string $documentNumber)
    {
        $this->name           = $name;
        $this->documentNumber = $documentNumber;
    }


    /**
     * retona o nome da empresa
     *
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * retona o CNPJ da empresa
     *
     * @return string
     */
    public function getDocumentNumber(): string {
        return $this->documentNumber;
    }

    /**
     * Documento de empresa deve ser único no sistema
     */
    protected function checkDocumentNumber()
    {
        if (!(new CanUseDocumentNumber($this->documentNumber))->handle()) {
            throw new InternalErrorException(
                'Não é possível adicionar o CNPJ informado',
                0
            );
        }
    }

    /**
     * Checa se é possível criar a empresa
     *
     * @return self
     */
    public function handle(): self
    {
        $this->checkDocumentNumber();

        return $this;
    }
}
