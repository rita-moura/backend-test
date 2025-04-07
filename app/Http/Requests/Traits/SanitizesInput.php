<?php

namespace App\Http\Requests\Traits;

use Illuminate\Support\Arr;
use Elegant\Sanitizer\Sanitizer;

trait SanitizesInput
{
    /**
     *  Sanitize input before validating.
     *
     *  @return void
     */
    public function validateResolved(): void
    {
        $filters = $this->filters();

        if ($this->hasBefore()) {
            $this->sanitize($filters['before']);
        }

        parent::validateResolved();

        if ($this->hasAfter()) {
            $this->sanitize($filters['after']);
        }

        if (!$this->hasAfter() && !$this->hasBefore()) {
            $this->sanitize($filters);
        }
    }

    private function hasBefore(): bool
    {
        $filters = $this->filters();

        return key_exists('before', $filters) && is_array($filters['before']);
    }

    private function hasAfter(): bool
    {
        $filters = $this->filters();

        return key_exists('after', $filters) && is_array($filters['after']);
    }

    /**
     *  Sanitize this request's input
     *
     *  @return void
     */
    public function sanitize($filters): void
    {
        $filters = Arr::dot(Arr::only(Arr::undot($filters), array_keys($this->input())));

        $sanitizer = new Sanitizer($this->input(), $filters);
        
        // Codigo para manter apenas os inputs pré existentes na request passados
        // pelo form, pois o sanitizer está criando as chaves dos inputs só
        // por existirem nas regras
        $keysBefore = array_keys($this->all());
        
        $sanitizedInputs = $sanitizer->sanitize();

        $result = [];

        foreach ($keysBefore as $key) {
            $result[$key] = $sanitizedInputs[$key];
        }
        ////////////////////////////////////////////////////////////////////////

        $this->replace($result);
    }

    /**
     *  Filters to be applied to the input.
     *
     *  @return array
     */
    public function filters(): array
    {
        return [];
    }
}
