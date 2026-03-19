@props(['url', 'title'])

<div
    x-data="{
        copied: false,
        share() {
            if (navigator.share) {
                navigator.share({ title: {{ Js::from($title) }}, url: {{ Js::from($url) }} }).catch(() => {});
            } else {
                navigator.clipboard.writeText({{ Js::from($url) }}).then(() => {
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
