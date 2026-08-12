@props(['url', 'title', 'trackUrl' => null])

<div
    x-data="{
        copied: false,
        track() {
            @if($trackUrl)
                fetch({{ Js::from($trackUrl) }}, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']')?.content ?? '',
                    },
                }).catch(() => {});
            @endif
        },
        share() {
            if (navigator.share) {
                navigator.share({ title: {{ Js::from($title) }}, url: {{ Js::from($url) }} })
                    .then(() => this.track())
                    .catch(() => {});
            } else {
                navigator.clipboard.writeText({{ Js::from($url) }}).then(() => {
                    this.track();
                    this.copied = true;
                    setTimeout(() => this.copied = false, 2000);
                });
            }
        }
    }"
>
    <button
        type="button"
        @click="share"
        class="inline-flex items-center gap-1.5 text-xs text-stone-400 hover:text-amber-600 transition-colors"
        :title="copied ? 'Copiado!' : 'Compartilhar'"
    >
        <template x-if="!copied">
            <x-heroicon-o-share class="w-4 h-4" />
        </template>
        <template x-if="copied">
            <x-heroicon-o-check class="w-4 h-4 text-green-500" />
        </template>
        <span x-text="copied ? 'Copiado!' : 'Compartilhar'" :class="copied ? 'text-green-500' : ''"></span>
    </button>
</div>
