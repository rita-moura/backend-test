<?php

namespace App\Repositories\User;

use App\Models\User;
use App\Repositories\BaseRepository;

class Update extends BaseRepository
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
     * Modificação de usuário
     *
     * @param array $params
     * @return array
     */
    public function handle($id, $params): array
    {
        return $this->update(
            $id,
            array_filter($params)
        );
    }
}
