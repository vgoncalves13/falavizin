<?php

namespace App\Livewire\Business;

use App\Actions\CreateBusinessAction;
use App\Actions\UpdateBusinessAction;
use App\Models\Business;
use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Livewire\Component;
use Livewire\WithFileUploads;

class BusinessForm extends Component
{
    use WithFileUploads;

    public ?Business $business = null;

    public string $name = '';

    public ?int $categoryId = null;

    public string $description = '';

    public string $phone = '';

    public string $whatsapp = '';

    public string $address = '';

    public string $neighborhood = '';

    public string $city = '';

    public string $website = '';

    public $coverPhoto = null;

    public function mount(?Business $business = null): void
    {
        if ($business?->exists) {
            $this->business = $business;
            $this->name = $business->name;
            $this->categoryId = $business->category_id;
            $this->description = $business->description ?? '';
            $this->phone = $business->phone ?? '';
            $this->whatsapp = $business->whatsapp ?? '';
            $this->address = $business->address ?? '';
            $this->neighborhood = $business->neighborhood;
            $this->city = $business->city ?? '';
            $this->website = $business->website ?? '';
        }
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'categoryId' => ['required', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:2000'],
            'phone' => ['nullable', 'string', 'max:20'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
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

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'category_id' => $this->categoryId,
            'description' => $this->description ?: null,
            'phone' => $this->phone ?: null,
            'whatsapp' => $this->whatsapp ?: null,
            'address' => $this->address ?: null,
            'neighborhood' => $this->neighborhood,
            'city' => $this->city ?: '',
            'website' => $this->website ?: null,
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
            $business = $this->business;
        } else {
            $business = (new CreateBusinessAction)->execute(
                user: auth()->user(),
                data: $data,
                coverPhoto: $uploadedPhoto,
            );
        }

        $this->redirect(route('businesses.show', $business));
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
