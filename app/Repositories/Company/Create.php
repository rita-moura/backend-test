<?php

namespace App\Repositories\Company;

use App\Models\Company;
use App\Repositories\BaseRepository;

class Create extends BaseRepository
{

    /**
     * Setar a model da empresa
     *
     * @return void
     */
    public function setModel(): void
    {
        $this->model = Company::class;
    }

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Criação de empresa
     *
     * @param array $data
     * @return array
     */
    public function handle(array $data): array
    {
        return $this->create($data);
    }
}
