<?php

namespace App\Repositories\Company;

use App\Models\Company;
use App\Repositories\BaseRepository;

class Update extends BaseRepository
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
     * Modificação de empresa
     *
     * @param string $id
     * @param array $data
     * @return array
     */
    public function handle(string $id, array $data): array
    {
        return $this->update($id, $data);
    }
}
