<?php

namespace App\UseCases\Company;

use Throwable;
use App\UseCases\BaseUseCase;
use App\Domains\Company\Update as UpdateDomain;
use App\Repositories\Company\Update as UpdateRepository;

class Update extends BaseUseCase
{

    /**
     * Id da empresa
     *
     * @var string
     */
    protected string $id;

    /**
     * Nome da empresa
     *
     * @var string
     */
    protected string $name;

    public function __construct(string $id, string $name)
    {
        $this->id   = $id;
        $this->name = $name;
    }

    public function handle(): array
    {
        $domain = (new UpdateDomain($this->id, $this->name))->handle();

        $data = [
            'name' => $domain->getName()
        ];

        return (new UpdateRepository())->handle($domain->getId(), $data);
    }
}
