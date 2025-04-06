<?php

namespace App\Repositories;

use Throwable;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Interfaces\BaseRepositoryInterface;

abstract class BaseRepository implements BaseRepositoryInterface
{
    /**
     * Model base para implementação da repository
     *
     * @var string
     */
    protected string $model;

    /**
     * Instância da model implementada
     *
     * @var Model
     */
    protected Model $instance;

    /**
     * Builder da model
     *
     * @var Builder
     */
    protected Builder $builder;

    /**
     * Construtor
     */
    public function __construct()
    {
        $this->setModel();
        $this->newInstance();
    }

    /**
     * Obter a model impletementada
     *
     * @return string
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * Instanciar a model implementada
     *
     * @return void
     */
    public function newInstance(): void
    {
        $this->instance = new $this->model();

        $this->newBuilder();
    }

    /**
     * Criar builder para model
     *
     * @return void
     */
    public function newBuilder(): void
    {
        $this->builder = $this->instance->newQuery();
    }

    /**
     * Método para buscar um registro em específico da model
     *
     * @param string|int $id
     * @param array      $columns
     *
     * @return array|null
     */
    public function find(string|int $id, array $columns = ['*']): array|null
    {
        $result = $this->builder->find($id, $columns)?->toArray();

        $this->newBuilder();

        return $result;
    }

    /**
     * Método para buscar um registro em específico da model
     *
     * @param string|int $id
     * @param array      $columns
     *
     * @return array
     *
     * @throws Throwable
     */
    public function findOrFail(string|int $id, array $columns = ['*']): array
    {
        try {
            $result = $this->builder->findOrFail($id, $columns)->toArray();

            $this->newBuilder();

            return $result;
        } catch (Throwable $th) {
            $this->newBuilder();

            throw $th;
        }
    }

    /**
     * Método para buscar o primeiro registro
     *
     * @param array $columns
     *
     * @return array|null
     */
    public function first(array $columns = ['*']): array|null
    {
        $result = $this->builder
            ->first($columns);

        $result = $result !== null ? $result->toArray() : null;

        $this->newBuilder();

        return $result;
    }

    /**
     * Método para buscar o primeiro registro ou falha
     *
     * @param array $columns
     *
     * @return array
     */
    public function firstOrFail(array $columns = ['*']): array
    {
        $result = $this->builder
            ->firstOrFail($columns)
            ->toArray();

        $this->newBuilder();

        return $result;
    }

    /**
     * Método para buscar os registros após uma query
     *
     * @param array $columns
     *
     * @return array
     */
    public function get(array $columns = ['*']): array
    {
        $result = $this->builder
            ->get($columns)
            ->toArray();

        $this->newBuilder();

        return $result;
    }

    /**
     * Método para paginar os registros após uma query
     *
     * @param array    $columns
     * @param int|null $perPage
     * @param int|null $page
     *
     * @return LengthAwarePaginator
     */
    public function paginate(
        array $columns = ['*'],
        int|null $perPage = null,
        int|null $page = null
    ): LengthAwarePaginator {
        $perPage = $perPage ?? (config('app.limit_pagination')); // Default to 15 if not set

        $result = $this->builder
            ->paginate(
                perPage:  $perPage,
                columns:  $columns,
                page:     $page
            );

        $this->newBuilder();

        return new LengthAwarePaginator(
            items:       json_decode(json_encode($result->items()), true),
            total:       $result->total(),
            perPage:     $result->perPage(),
            currentPage: $result->currentPage(),
            options: [
                'path'     => $result->path(),
                'pageName' => 'page',
            ]
        );
    }

    /**
     * Método para criar um registro de determinada model
     *
     * @param array $attributes
     *
     * @return array
     */
    public function create(array $attributes): array
    {
        return $this->instance::create($attributes)->withoutRelations()->toArray();
    }

    /**
     * Método para criar vários registros de determinada model
     *
     * @param array $groupOfAttributes
     *
     * @return bool
     */
    public function createMany(array $groupOfAttributes): bool
    {
        try {
            $formattedAttributes = [];
            foreach ($groupOfAttributes as $attributes) {
                $formattedAttributes[] = array_merge(
                    [
                        'id'         => Uuid::uuid4()->toString(),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ],
                    $attributes
                );
            }

            $result = $this->builder->insert($formattedAttributes);

            $this->newBuilder();
        } catch (Throwable $th) {
            $this->newBuilder();

            throw $th;
        }

        return $result;
    }

    /**
     * Método para encontrar/criar um registro de determinada model
     *
     * @param array $matchingAttributes
     * @param array $values
     *
     * @return array
     */
    public function firstOrCreate(array $matchingAttributes, array $values): array
    {
        try {
            $result = $this->instance::firstOrCreate(
                $matchingAttributes,
                $values
            );

            $this->newBuilder();

            return $result->withoutRelations()->toArray();
        } catch (Throwable $th) {
            $this->newBuilder();

            throw $th;
        }
    }

    /**
     * Método para restaurar/criar um registro de determinada model
     *
     * @param array $matchingAttributes
     * @param array $values
     *
     * @return array
     */
    public function restoreOrCreate(array $matchingAttributes, array $values): array
    {
        try {
            if (method_exists($this->instance, 'withTrashed')) {
                $deleted = $this->model::withTrashed()->where($matchingAttributes)->first();
            } else {
                $deleted = $this->instance::where($matchingAttributes)->first();
            }
            if (!is_null($deleted)) {
                $deleted->restore();
                $deleted->update($values);

                $result = $deleted->refresh();

                $this->newBuilder();

                return $result->withoutRelations()->toArray();
            }

            $attributes = array_merge($values, $matchingAttributes);
            $result = $this->instance::create($attributes);

            $this->newBuilder();

            return $result->withoutRelations()->toArray();
        } catch (Throwable $th) {
            $this->newBuilder();

            throw $th;
        }
    }

    /**
     * Método para criar/atualizar um registro de determinada model
     *
     * @param array $matchingAttributes
     * @param array $values
     *
     * @return array
     *
     * @throws Throwable
     */
    public function updateOrCreate(array $matchingAttributes, array $values): array
    {
        try {
            $result = $this->instance::updateOrCreate(
                $matchingAttributes,
                $values
            );

            $this->newBuilder();

            return $result->withoutRelations()->toArray();
        } catch (Throwable $th) {
            $this->newBuilder();

            throw $th;
        }
    }

    /**
     * Método para atualizar um registro de determinada model
     *
     * @param string|int $idEntity
     * @param array      $attributes
     *
     * @return array
     *
     * @throws Throwable
     */
    public function update(string|int $idEntity, array $attributes): array
    {
        try {
            $entity = $this->builder->findOrFail($idEntity);

            $entity->update($attributes);

            $this->newBuilder();

            return $entity->withoutRelations()->toArray();
        } catch (Throwable $th) {
            $this->newBuilder();

            throw $th;
        }
    }

    /**
     * Método para atualizar vários registros de determinada model
     *
     * @param array $idsEntity
     * @param array $attributes
     *
     * @return int
     *
     * @throws Throwable
     */
    public function updateMany(array $idsEntity, array $attributes): int
    {
        try {
            if (count($idsEntity) === 0) {
                return 0;
            }

            $result = $this->builder->whereIn('id', $idsEntity)->update($attributes);

            $this->newBuilder();
        } catch (Throwable $th) {
            $this->newBuilder();

            throw $th;
        }

        return $result;
    }

    /**
     * Método para deletar um registro de determinada model
     *
     * @param string|int $idEntity
     *
     * @return array
     */
    public function delete(string|int $idEntity): array
    {
        try {
            $entity = $this->builder->findOrFail($idEntity);

            $entity->delete();

            $this->newBuilder();

            return $entity->withoutRelations()->toArray();
        } catch (Throwable $th) {
            $this->newBuilder();

            throw $th;
        }
    }

    /**
     * Método para deletar mais de um registro de determinada model
     *
     * @param array $idsEntity
     *
     * @return bool
     */
    public function deleteMany(array $idsEntity): bool
    {
        try {
            $entity = $this->builder->whereIn('id', $idsEntity)->delete();

            $this->newBuilder();

            return (bool) $entity;
        } catch (Throwable $th) {
            $this->newBuilder();

            throw $th;
        }
    }

    /**
     * Método para buscar os registros após uma query e manipular através das keys
     *
     * @param string $value
     * @param string|null $key
     *
     * @return array
     */
    public function pluck(string $value, string|null $key = null): array
    {
        $getColumns[] = $value;

        if (!is_null($key)) {
            $getColumns[] = $key;
        }

        $result = $this->builder
            ->get($getColumns)
            ->pluck($value, $key)
            ->toArray();

        $this->newBuilder();

        return $result;
    }
}
