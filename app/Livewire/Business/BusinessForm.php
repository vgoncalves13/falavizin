<?php

namespace App\Livewire\Business;

use App\Actions\CreateBusinessAction;
use App\Actions\UpdateBusinessAction;
use App\Models\Business;
use App\Models\Category;
use App\Models\Setting;
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

    public string $whatsapp = '';

    public string $neighborhood = '';

    public $coverPhoto = null;

    public function mount(?Business $business = null): void
    {
        if ($business?->exists) {
            $this->business = $business;
            $this->name = $business->name;
            $this->categoryId = $business->category_id;
            $this->description = $business->description ?? '';
            $this->whatsapp = $business->whatsapp ?? '';
            $this->neighborhood = $business->neighborhood;
        } else {
            $this->neighborhood = Setting::get('neighborhood_name', '');
        }
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'categoryId' => ['required', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:2000'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'neighborhood' => ['required', 'string', 'max:255'],
            'coverPhoto' => ['nullable', 'image', 'max:5120'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Informe o nome do negócio.',
            'name.min' => 'O nome deve ter pelo menos 3 caracteres.',
            'categoryId.required' => 'Selecione uma categoria.',
            'neighborhood.required' => 'Informe o bairro.',
            'coverPhoto.image' => 'O arquivo deve ser uma imagem.',
            'coverPhoto.max' => 'A imagem não pode ter mais de 5MB.',
        ];
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
            'whatsapp' => $this->whatsapp ?: null,
            'neighborhood' => $this->neighborhood,
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
