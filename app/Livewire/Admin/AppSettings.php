<?php

namespace App\Livewire\Admin;

use App\Models\Setting;
use Livewire\Component;

class AppSettings extends Component
{
    public string $neighborhoodName = '';

    public string $neighborhoodLat = '';

    public string $neighborhoodLng = '';

    public function mount(): void
    {
        $this->neighborhoodName = Setting::get('neighborhood_name', '');
        $this->neighborhoodLat = Setting::get('neighborhood_lat', '');
        $this->neighborhoodLng = Setting::get('neighborhood_lng', '');
    }

    public function save(): void
    {
        $this->validate([
            'neighborhoodName' => ['required', 'string', 'max:100'],
            'neighborhoodLat' => ['required', 'numeric', 'between:-90,90'],
            'neighborhoodLng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        Setting::set('neighborhood_name', $this->neighborhoodName);
        Setting::set('neighborhood_lat', $this->neighborhoodLat);
        Setting::set('neighborhood_lng', $this->neighborhoodLng);

        session()->flash('success', 'Configurações salvas com sucesso.');
    }

    public function render()
    {
        return view('livewire.admin.app-settings')
            ->layout('layouts.app')
            ->layoutData(['title' => 'Configurações']);
    }
}
