<?php

namespace App\UseCases\Company;

use Throwable;
use App\UseCases\BaseUseCase;
use App\Domains\Company\Create as CreateDomain;
use App\Repositories\Company\Create as CreateRepository;

class Create extends BaseUseCase
{
    /**
     * Nome da empresa
     *
     * @var string
     */
    protected string $name;

    /**
     * Número do documento da empresa
     *
     * @var string
     */
    protected string $documentNumber;

    /**
     * Empresa
     *
     * @var array
     */
    protected array $company;

    public function __construct(string $name, string $documentNumber)
    {
        $this->name           = $name;
        $this->documentNumber = $documentNumber;
    }

    public function handle(): array
    {
        $domain = (new CreateDomain($this->name, $this->documentNumber))->handle();

        $data = [
            'name'            => $domain->getName(),
            'document_number' => $domain->getDocumentNumber(),
        ];

        $this->company = (new CreateRepository())->handle($data);

        return $this->company;
    }
}
