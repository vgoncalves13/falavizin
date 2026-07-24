<?php

namespace App\Livewire\Admin;

use App\Actions\SaveNeighborhoodAction;
use App\Actions\SetNeighborhoodStatusAction;
use App\Models\Neighborhood;
use App\Services\NeighborhoodCache;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;

class NeighborhoodManager extends Component
{
    public string $name = '';

    public string $slug = '';

    public string $city = '';

    public string $citySlug = '';

    public string $stateCode = '';

    public ?string $latitude = null;

    public ?string $longitude = null;

    public int $sortOrder = 0;

    public bool $isActive = true;

    public ?int $editingId = null;

    public bool $showForm = false;

    public function mount(): void
    {
        abort_unless(auth()->user()?->is_admin, 403);
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    #[On('edit')]
    public function edit(int $id): void
    {
        $neighborhood = Neighborhood::findOrFail($id);

        $this->editingId = $id;
        $this->name = $neighborhood->name;
        $this->slug = $neighborhood->slug;
        $this->city = $neighborhood->city;
        $this->citySlug = $neighborhood->city_slug;
        $this->stateCode = $neighborhood->state_code;
        $this->latitude = $neighborhood->latitude ? (string) $neighborhood->latitude : null;
        $this->longitude = $neighborhood->longitude ? (string) $neighborhood->longitude : null;
        $this->sortOrder = $neighborhood->sort_order;
        $this->isActive = $neighborhood->is_active;
        $this->showForm = true;
    }

    public function save(SaveNeighborhoodAction $action, NeighborhoodCache $cache): void
    {
        $neighborhood = $this->editingId
            ? Neighborhood::findOrFail($this->editingId)
            : new Neighborhood;

        $data = [
            'name' => $this->name,
            'slug' => $this->slug,
            'city' => $this->city,
            'city_slug' => $this->citySlug,
            'state_code' => $this->stateCode,
            'latitude' => $this->latitude !== '' && $this->latitude !== null ? (float) $this->latitude : null,
            'longitude' => $this->longitude !== '' && $this->longitude !== null ? (float) $this->longitude : null,
            'sort_order' => $this->sortOrder,
            'is_active' => $this->isActive,
        ];

        $action->execute($neighborhood, $data);

        $cache->forgetActive();

        $this->resetForm();

        session()->flash('success', $this->editingId ? 'Bairro atualizado com sucesso.' : 'Bairro criado com sucesso.');
    }

    public function toggleActive(int $id, SetNeighborhoodStatusAction $action, NeighborhoodCache $cache): void
    {
        $neighborhood = Neighborhood::findOrFail($id);

        try {
            $action->execute($neighborhood, ! $neighborhood->is_active);
        } catch (ValidationException $exception) {
            throw $exception;
        }

        $cache->forgetActive();
        $cache->forget($neighborhood);
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->name = '';
        $this->slug = '';
        $this->city = '';
        $this->citySlug = '';
        $this->stateCode = '';
        $this->latitude = null;
        $this->longitude = null;
        $this->sortOrder = 0;
        $this->isActive = true;
        $this->editingId = null;
        $this->showForm = false;
    }

    public function render()
    {
        $neighborhoods = Neighborhood::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('livewire.admin.neighborhood-manager', compact('neighborhoods'))
            ->layout('layouts.app')
            ->layoutData(['title' => 'Bairros']);
    }
}
