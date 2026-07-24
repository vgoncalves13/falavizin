<?php

namespace App\Livewire\Feed;

use App\Actions\CreatePostAction;
use App\Models\Category;
use App\Models\Neighborhood;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class CreatePost extends Component
{
    use WithFileUploads;

    public Neighborhood $neighborhood;

    public string $title = '';

    public string $body = '';

    public string $location = '';

    public ?int $categoryId = null;

    public ?int $serviceCategoryId = null;

    /** @var array<int, TemporaryUploadedFile> */
    public array $images = [];

    public string $eventStartsAt = '';

    public string $eventEndsAt = '';

    public bool $hasPoll = false;

    public string $pollQuestion = '';

    /** @var array<int, string> */
    public array $pollOptions = ['', ''];

    public string $pollEndsAt = '';

    public function mount(Neighborhood $neighborhood): void
    {
        $this->neighborhood = $neighborhood;
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'body' => ['required', 'string', 'min:10'],
            'categoryId' => ['required', 'integer', 'exists:categories,id'],
            'serviceCategoryId' => ['nullable', 'integer', 'exists:categories,id'],
            'location' => ['nullable', 'string', 'max:255'],
            'images' => ['array', 'max:4'],
            'images.*' => ['image', 'max:2048', 'mimes:jpg,jpeg,png,webp'],
            'eventStartsAt' => ['nullable', 'date'],
            'eventEndsAt' => ['nullable', 'date'],
            'pollQuestion' => ['nullable', 'string', 'min:5', 'max:255', 'required_if:hasPoll,true'],
            'pollOptions.*' => ['nullable', 'string', 'max:255', 'required_if:hasPoll,true'],
            'pollEndsAt' => ['nullable', 'date'],
        ];
    }

    protected function messages(): array
    {
        return [
            'title.required' => 'O título é obrigatório.',
            'title.min' => 'O título deve ter pelo menos 5 caracteres.',
            'body.required' => 'O conteúdo é obrigatório.',
            'body.min' => 'O conteúdo deve ter pelo menos 10 caracteres.',
            'categoryId.required' => 'Selecione uma categoria.',
            'images.max' => 'Você pode enviar no máximo 4 fotos.',
            'images.*.image' => 'Cada arquivo deve ser uma imagem.',
            'images.*.max' => 'Cada imagem deve ter no máximo 2MB.',
            'images.*.mimes' => 'Formatos aceitos: JPG, PNG, WebP.',
            'pollQuestion.required_if' => 'A pergunta da enquete é obrigatória.',
            'pollQuestion.min' => 'A pergunta deve ter pelo menos 5 caracteres.',
            'pollOptions.*.required_if' => 'Preencha todas as opções da enquete.',
        ];
    }

    public function addPollOption(): void
    {
        $this->pollOptions[] = '';
    }

    public function removePollOption(int $index): void
    {
        if (count($this->pollOptions) <= 2) {
            return;
        }

        array_splice($this->pollOptions, $index, 1);
        $this->pollOptions = array_values($this->pollOptions);
    }

    public function removeImage(int $index): void
    {
        if (! array_key_exists($index, $this->images)) {
            return;
        }

        array_splice($this->images, $index, 1);
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->validate();

        $pollData = null;
        if ($this->hasPoll && $this->pollQuestion) {
            $pollData = [
                'question' => $this->pollQuestion,
                'options' => array_filter($this->pollOptions),
                'ends_at' => $this->pollEndsAt ?: null,
            ];
        }

        (new CreatePostAction)->execute(
            user: auth()->user(),
            neighborhood: $this->neighborhood,
            data: [
                'title' => $this->title,
                'body' => $this->body,
                'category_id' => $this->categoryId,
                'service_category_id' => $this->serviceCategoryId,
                'location' => $this->location ?: null,
            ],
            images: $this->images,
            eventStartsAt: $this->eventStartsAt ? Carbon::parse($this->eventStartsAt) : null,
            eventEndsAt: $this->eventEndsAt ? Carbon::parse($this->eventEndsAt) : null,
            pollData: $pollData,
        );

        $this->redirect(route('neighborhood.feed.index', $this->neighborhood->routeParameters()), navigate: false);
        session()->flash('success', 'Post enviado! Aguarda aprovação do admin.');
    }

    public function render()
    {
        $categories = Category::query()
            ->whereIn('type', ['post', 'both'])
            ->orderBy('sort_order')
            ->get();

        $serviceCategories = Category::query()
            ->whereIn('type', ['business', 'both'])
            ->orderBy('sort_order')
            ->get();

        return view('livewire.feed.create-post', compact('categories', 'serviceCategories'))
            ->with('neighborhood', $this->neighborhood);
    }
}
