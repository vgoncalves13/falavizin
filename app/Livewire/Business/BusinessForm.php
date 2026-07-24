<?php

namespace App\Livewire\Business;

use App\Actions\CreateBusinessAction;
use App\Actions\UpdateBusinessAction;
use App\Models\Business;
use App\Models\Category;
use App\Models\Neighborhood;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithFileUploads;

class BusinessForm extends Component
{
    use WithFileUploads;

    public ?Business $business = null;

    public Neighborhood $neighborhood;

    public string $name = '';

    /** @var array<int, int> */
    public array $categoryIds = [];

    public string $description = '';

    /** @var array<int, string> */
    public array $phones = [''];

    public string $whatsapp = '';

    public string $address = '';

    public string $city = '';

    public string $website = '';

    /** @var array<int, array{day: string, open: string, close: string, closed: bool}> */
    public array $openingHours = [];

    public $coverPhoto = null;

    private const WEEK_DAYS = [
        'Segunda-feira', 'Terça-feira', 'Quarta-feira',
        'Quinta-feira', 'Sexta-feira', 'Sábado', 'Domingo',
    ];

    public function mount(Neighborhood $neighborhood, ?Business $business = null): void
    {
        $this->neighborhood = $neighborhood;

        if ($business?->exists) {
            $this->business = $business;
            $this->name = $business->name;
            $this->categoryIds = $business->categories->pluck('id')->toArray();
            $this->description = $business->description ?? '';
            $this->phones = $business->phone ?: [''];
            $this->whatsapp = $business->whatsapp ?? '';
            $this->address = $business->address ?? '';
            $this->city = $business->city ?? '';
            $this->website = $business->website ?? '';
            $this->openingHours = $this->initOpeningHours($business->opening_hours);
        } else {
            $this->openingHours = $this->initOpeningHours(null);
        }
    }

    /** @param  array<int, array{day: string, open: string, close: string, closed: bool}>|null  $stored */
    private function initOpeningHours(?array $stored): array
    {
        $byDay = [];
        foreach ($stored ?? [] as $row) {
            if (isset($row['day'])) {
                $byDay[$row['day']] = $row;
            }
        }

        return array_map(fn (string $day) => [
            'day' => $day,
            'open' => $byDay[$day]['open'] ?? '08:00',
            'close' => $byDay[$day]['close'] ?? '18:00',
            'closed' => (bool) ($byDay[$day]['closed'] ?? true),
        ], self::WEEK_DAYS);
    }

    protected function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'categoryIds' => ['required', 'array', 'min:1'],
            'categoryIds.*' => ['integer', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:2000'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'coverPhoto' => ['nullable', 'image', 'max:5120'],
        ];

        if ($this->business?->exists) {
            $rules['phones'] = ['nullable', 'array', 'max:5'];
            $rules['phones.*'] = ['nullable', 'string', 'max:20'];
            $rules['address'] = ['nullable', 'string', 'max:255'];
            $rules['city'] = ['nullable', 'string', 'max:255'];
            $rules['website'] = ['nullable', 'url', 'max:255'];
            $rules['openingHours'] = ['nullable', 'array'];
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Informe o nome do negócio.',
            'name.min' => 'O nome deve ter pelo menos 3 caracteres.',
            'categoryIds.required' => 'Selecione ao menos uma categoria.',
            'categoryIds.min' => 'Selecione ao menos uma categoria.',
            'coverPhoto.image' => 'O arquivo deve ser uma imagem.',
            'coverPhoto.max' => 'A imagem não pode ter mais de 5MB.',
        ];
    }

    /** @return array<int, array{day: string, open: string, close: string, closed: bool}>|null */
    private function buildOpeningHours(): ?array
    {
        $hasAnyOpen = collect($this->openingHours)->contains(fn ($h) => ! ($h['closed'] ?? true));

        if (! $hasAnyOpen) {
            return null;
        }

        return array_map(fn (array $h) => [
            'day' => $h['day'],
            'open' => $h['closed'] ? '' : ($h['open'] ?? ''),
            'close' => $h['closed'] ? '' : ($h['close'] ?? ''),
            'closed' => (bool) ($h['closed'] ?? true),
        ], $this->openingHours);
    }

    public function addPhone(): void
    {
        if (count($this->phones) < 5) {
            $this->phones[] = '';
        }
    }

    public function removePhone(int $index): void
    {
        unset($this->phones[$index]);
        $this->phones = array_values($this->phones);

        if (empty($this->phones)) {
            $this->phones = [''];
        }
    }

    public function save(): void
    {
        if ($this->business?->exists) {
            Gate::authorize('update', $this->business);
        }

        $this->validate();

        $data = [
            'name' => $this->name,
            'category_ids' => $this->categoryIds,
            'description' => $this->description ?: null,
            'whatsapp' => $this->whatsapp ?: null,
        ];

        if ($this->business?->exists) {
            $data['phone'] = array_values(array_filter(array_map('trim', $this->phones))) ?: null;
            $data['address'] = $this->address ?: null;
            $data['city'] = $this->city ?: '';
            $data['website'] = $this->website ?: null;
            $data['opening_hours'] = $this->buildOpeningHours();
        }

        $uploadedPhoto = null;

        if ($this->coverPhoto) {
            $uploadedPhoto = $this->coverPhoto->getRealPath()
                ? new UploadedFile(
                    $this->coverPhoto->getRealPath(),
                    $this->coverPhoto->getClientOriginalName(),
                    $this->coverPhoto->getMimeType(),
                )
                : null;
        }

        if ($this->business?->exists) {
            (new UpdateBusinessAction)->execute($this->business, $data, $uploadedPhoto);
            $this->redirect($this->business->canonicalUrl());
        } else {
            (new CreateBusinessAction)->execute(
                user: auth()->user(),
                neighborhood: $this->neighborhood,
                data: $data,
                coverPhoto: $uploadedPhoto,
            );
            session()->flash('success', 'Negócio enviado! Aguarda aprovação do admin.');
            $this->redirect(route('neighborhood.businesses.index', $this->neighborhood->routeParameters()));
        }
    }

    public function render()
    {
        $categories = Category::query()
            ->whereIn('type', ['business', 'both'])
            ->orderBy('sort_order')
            ->get();

        return view('livewire.business.business-form', compact('categories'))
            ->with('neighborhood', $this->neighborhood);
    }
}
