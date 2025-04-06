<?php

namespace App\Repositories\User;

use App\Models\User;
use App\Repositories\BaseRepository;

class Create extends BaseRepository
{

    /**
     * Setar a model do usuário
     *
     * @return void
     */
    public function setModel(): void
    {
        $this->model = User::class;
    }

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Criação de usuário
     *
     * @param array $params
     * @return array
     */
    public function handle($params): array
    {
        return $this->create($params);
    }
}
