<?php

namespace App\Livewire\Business;

use App\Actions\CreateBusinessAction;
use App\Actions\UpdateBusinessAction;
use App\Models\Business;
use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithFileUploads;

class BusinessForm extends Component
{
    use WithFileUploads;

    public ?Business $business = null;

    public string $name = '';

    public ?int $categoryId = null;

    public string $description = '';

    /** @var array<int, string> */
    public array $phones = [''];

    public string $whatsapp = '';

    public string $address = '';

    public string $neighborhood = '';

    public string $city = '';

    public string $website = '';

    /** @var array<int, array{day: string, open: string, close: string, closed: bool}> */
    public array $openingHours = [];

    public $coverPhoto = null;

    private const WEEK_DAYS = [
        'Segunda-feira', 'Terça-feira', 'Quarta-feira',
        'Quinta-feira', 'Sexta-feira', 'Sábado', 'Domingo',
    ];

    public function mount(?Business $business = null): void
    {
        if ($business?->exists) {
            $this->business = $business;
            $this->name = $business->name;
            $this->categoryId = $business->category_id;
            $this->description = $business->description ?? '';
            $this->phones = $business->phone ?: [''];
            $this->whatsapp = $business->whatsapp ?? '';
            $this->address = $business->address ?? '';
            $this->neighborhood = $business->neighborhood;
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
        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'categoryId' => ['required', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:2000'],
            'phones' => ['nullable', 'array', 'max:5'],
            'phones.*' => ['nullable', 'string', 'max:20'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'openingHours' => ['nullable', 'array'],
            'openingHours.*.open' => ['nullable', 'string', 'max:10'],
            'openingHours.*.close' => ['nullable', 'string', 'max:10'],
            'openingHours.*.closed' => ['nullable', 'boolean'],
            'coverPhoto' => ['nullable', 'image', 'max:5120'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'O nome do negócio é obrigatório.',
            'name.min' => 'O nome deve ter pelo menos 3 caracteres.',
            'categoryId.required' => 'Selecione uma categoria.',
            'neighborhood.required' => 'O bairro é obrigatório.',
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
            'category_id' => $this->categoryId,
            'description' => $this->description ?: null,
            'phone' => array_values(array_filter(array_map('trim', $this->phones))) ?: null,
            'whatsapp' => $this->whatsapp ?: null,
            'address' => $this->address ?: null,
            'neighborhood' => $this->neighborhood,
            'city' => $this->city ?: '',
            'website' => $this->website ?: null,
            'opening_hours' => $this->buildOpeningHours(),
        ];

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
            $this->redirect(route('businesses.show', $this->business));
        } else {
            (new CreateBusinessAction)->execute(
                user: auth()->user(),
                data: $data,
                coverPhoto: $uploadedPhoto,
            );
            session()->flash('success', 'Negócio enviado! Aguarda aprovação do admin.');
            $this->redirect(route('businesses.index'));
        }
    }

    public function render()
    {
        $categories = Category::query()
            ->whereIn('type', ['business', 'both'])
            ->orderBy('sort_order')
            ->get();

        return view('livewire.business.business-form', compact('categories'));
    }
}
