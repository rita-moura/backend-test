<?php

namespace App\Repositories\Card;

use App\Models\Card;
use App\Repositories\BaseRepository;

class Create extends BaseRepository
{

    /**
     * Setar a model do cartão
     *
     * @return void
     */
    public function setModel(): void
    {
        $this->model = Card::class;
    }

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Cria um cartão
     *
     * @param array $data
     * @return array
     */
    public function handle(array $data): array
    {
        return $this->create($data);
    }
}
