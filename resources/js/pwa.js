const DISMISS_STORAGE_KEY = 'falavizin-pwa-dismissed-at';
const PUSH_OWNER_STORAGE_KEY = 'falavizin-push-owner';
const PUSH_OFFER_DISMISS_STORAGE_KEY = 'falavizin-push-offer-dismissed-at';
const PUSH_OFFER_ACCEPTED_STORAGE_KEY = 'falavizin-push-offer-accepted';
const INSTALL_DISMISS_DAYS = 14;

let deferredInstallPrompt = null;

const metaContent = (name) => document.querySelector(`meta[name="${name}"]`)?.content || '';
const isStandalone = () => window.matchMedia('(display-mode: standalone)').matches
    || window.navigator.standalone === true;
const isIos = () => {
    const platform = navigator.userAgentData?.platform || navigator.platform || '';

    return /iPhone|iPad|iPod/i.test(platform)
        || (/Mac/i.test(platform) && navigator.maxTouchPoints > 1)
        || /iPhone|iPad|iPod/i.test(navigator.userAgent);
};
const isMobile = () => window.matchMedia('(max-width: 767px)').matches
    || navigator.maxTouchPoints > 0;
const supportsPush = () => 'serviceWorker' in navigator
    && 'PushManager' in window
    && 'Notification' in window;

const installPromptElement = () => document.querySelector('[data-pwa-install-prompt]');

function hideInstallPrompt() {
    installPromptElement()?.classList.add('hidden');
}

function showInstallPrompt(manual = false) {
    const prompt = installPromptElement();

    if (!prompt || isStandalone()) {
        return;
    }

    const iosInstructions = prompt.querySelector('[data-pwa-ios-instructions]');
    const confirmButton = prompt.querySelector('[data-pwa-confirm]');
    const description = prompt.querySelector('[data-pwa-install-description]');
    const title = prompt.querySelector('[data-pwa-title]');
    const showIosInstructions = isIos() && !deferredInstallPrompt;

    prompt.dataset.mode = 'install';
    iosInstructions?.classList.toggle('hidden', !showIosInstructions);

    if (title) {
        title.textContent = 'Leve o FalaVizin com você';
    }

    if (description) {
        description.textContent = !deferredInstallPrompt && !showIosInstructions
            ? 'A instalação não está disponível neste navegador. Procure a opção de instalar aplicativo no menu do navegador.'
            : 'Instale o FalaVizin para acessar mais rápido e receber novidades da sua vizinhança.';
    }

    if (confirmButton) {
        confirmButton.textContent = deferredInstallPrompt ? 'Instalar' : 'Entendi';
        confirmButton.disabled = false;
        confirmButton.classList.remove('hidden');
    }

    if (!deferredInstallPrompt && !showIosInstructions && !manual) {
        return;
    }

    prompt.classList.remove('hidden');
}

function wasInstallRecentlyDismissed() {
    const dismissedAt = Number(localStorage.getItem(DISMISS_STORAGE_KEY) || 0);
    const dismissalPeriod = INSTALL_DISMISS_DAYS * 24 * 60 * 60 * 1000;

    return dismissedAt > Date.now() - dismissalPeriod;
}

function wasPushOfferRecentlyDismissed() {
    const dismissedAt = Number(localStorage.getItem(PUSH_OFFER_DISMISS_STORAGE_KEY) || 0);
    const dismissalPeriod = INSTALL_DISMISS_DAYS * 24 * 60 * 60 * 1000;

    return dismissedAt > Date.now() - dismissalPeriod;
}

async function showPushOffer() {
    if (
        !supportsPush()
        || !metaContent('vapid-public-key')
        || Notification.permission === 'denied'
        || localStorage.getItem(PUSH_OFFER_ACCEPTED_STORAGE_KEY)
        || wasPushOfferRecentlyDismissed()
        || await currentSubscription()
    ) {
        return;
    }

    const prompt = installPromptElement();

    if (!prompt) {
        return;
    }

    prompt.dataset.mode = 'push';
    prompt.querySelector('[data-pwa-title]').textContent = 'Quer receber novidades da sua vizinhança?';
    prompt.querySelector('[data-pwa-install-description]').textContent = 'Escolha quais avisos quer receber, como comentários, respostas e reações.';
    prompt.querySelector('[data-pwa-ios-instructions]')?.classList.add('hidden');
    prompt.querySelector('[data-pwa-confirm]').textContent = 'Configurar notificações';
    prompt.classList.remove('hidden');
}

async function confirmInstall() {
    if (!deferredInstallPrompt) {
        hideInstallPrompt();

        return;
    }

    deferredInstallPrompt.prompt();
    await deferredInstallPrompt.userChoice;
    deferredInstallPrompt = null;
    hideInstallPrompt();
}

function registerInstallExperience() {
    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredInstallPrompt = event;

        if (
            isMobile()
            && metaContent('pwa-install-safe') === 'true'
            && !wasInstallRecentlyDismissed()
            && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName)
        ) {
            window.setTimeout(() => showInstallPrompt(), 1800);
        }
    });

    window.addEventListener('appinstalled', () => {
        deferredInstallPrompt = null;
        localStorage.removeItem(DISMISS_STORAGE_KEY);
        hideInstallPrompt();
        document.querySelectorAll('[data-pwa-install]').forEach((button) => button.classList.add('hidden'));
        window.setTimeout(showPushOffer, 300);
    });

    document.addEventListener('click', (event) => {
        if (event.target.closest('[data-pwa-install]')) {
            showInstallPrompt(true);
        }

        if (event.target.closest('[data-pwa-dismiss]')) {
            const storageKey = installPromptElement()?.dataset.mode === 'push'
                ? PUSH_OFFER_DISMISS_STORAGE_KEY
                : DISMISS_STORAGE_KEY;

            localStorage.setItem(storageKey, String(Date.now()));
            hideInstallPrompt();
        }

        if (event.target.closest('[data-pwa-confirm]')) {
            if (installPromptElement()?.dataset.mode === 'push') {
                localStorage.setItem(PUSH_OFFER_ACCEPTED_STORAGE_KEY, 'true');
                window.location.assign('/minha-conta?tab=notifications');
            } else {
                confirmInstall();
            }
        }
    });

    if (isIos() && isMobile() && metaContent('pwa-install-safe') === 'true' && !wasInstallRecentlyDismissed()) {
        window.setTimeout(() => showInstallPrompt(), 1800);
    }

    if (isStandalone()) {
        document.querySelectorAll('[data-pwa-install]').forEach((button) => button.classList.add('hidden'));
    }
}

function base64UrlToUint8Array(value) {
    const padding = '='.repeat((4 - (value.length % 4)) % 4);
    const base64 = (value + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);

    return Uint8Array.from([...rawData].map((character) => character.charCodeAt(0)));
}

function subscriptionPayload(subscription, types) {
    const payload = subscription.toJSON();

    return {
        endpoint: payload.endpoint,
        keys: payload.keys,
        content_encoding: window.PushManager.supportedContentEncodings?.[0] || 'aes128gcm',
        ...(types ? { types } : {}),
    };
}

async function apiRequest(method, payload, signal) {
    const response = await fetch('/push-subscriptions', {
        method,
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': metaContent('csrf-token'),
        },
        body: JSON.stringify(payload),
        signal,
    });

    if (!response.ok) {
        throw new Error(`Push subscription request failed with status ${response.status}.`);
    }
}

async function currentSubscription() {
    if (!supportsPush()) {
        return null;
    }

    const registration = await navigator.serviceWorker.ready;

    return registration.pushManager.getSubscription();
}

async function saveSubscription(subscription, types = null) {
    await apiRequest('POST', subscriptionPayload(subscription, types));
    localStorage.setItem(PUSH_OWNER_STORAGE_KEY, metaContent('authenticated-user-id'));
}

function selectedPushTypes(container) {
    return [...container.querySelectorAll('[data-push-type]:checked')].map((checkbox) => checkbox.value);
}

function updatePushSettings(container, subscription = null) {
    if (!container) {
        return;
    }

    const status = container.querySelector('[data-push-status]');
    const enableButton = container.querySelector('[data-push-enable]');
    const disableButton = container.querySelector('[data-push-disable]');
    const guidance = container.querySelector('[data-push-guidance]');
    const supported = supportsPush();
    const permission = supported ? Notification.permission : 'unsupported';
    const hasSelectedTypes = selectedPushTypes(container).length > 0;

    if (status) {
        status.textContent = !supported
            ? 'Este navegador não oferece suporte a notificações push.'
            : permission === 'denied'
                ? 'As notificações estão bloqueadas nas configurações do navegador.'
                : subscription
                    ? 'Notificações ativas neste dispositivo.'
                    : 'Notificações desativadas neste dispositivo.';
    }

    guidance?.classList.toggle('hidden', permission !== 'denied');

    if (enableButton) {
        enableButton.classList.toggle('hidden', Boolean(subscription) || !supported || permission === 'denied');
        enableButton.disabled = !hasSelectedTypes;
    }

    disableButton?.classList.toggle('hidden', !subscription);
    container.querySelector('[data-push-unsupported]')?.classList.toggle('hidden', supported);
}

async function enablePush(container) {
    const types = selectedPushTypes(container);

    if (types.length === 0) {
        updatePushSettings(container);

        return;
    }

    const button = container.querySelector('[data-push-enable]');
    button.disabled = true;
    button.textContent = 'Ativando...';

    try {
        const permission = Notification.permission === 'granted'
            ? 'granted'
            : await Notification.requestPermission();

        if (permission !== 'granted') {
            updatePushSettings(container);

            return;
        }

        const registration = await navigator.serviceWorker.ready;
        const publicKey = metaContent('vapid-public-key');

        if (!publicKey) {
            throw new Error('VAPID public key is not configured.');
        }

        const subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: base64UrlToUint8Array(publicKey),
        });

        await saveSubscription(subscription, types);
        updatePushSettings(container, subscription);
    } catch {
        const status = container.querySelector('[data-push-status]');

        if (status) {
            status.textContent = 'Não foi possível ativar. Verifique as permissões do navegador e tente novamente.';
        }
    } finally {
        button.textContent = 'Receber notificações neste dispositivo';
        button.disabled = false;
    }
}

async function disablePush(container) {
    const button = container.querySelector('[data-push-disable]');
    let serverCleanupFailed = false;

    button.disabled = true;
    button.textContent = 'Desativando...';

    try {
        const subscription = await currentSubscription();

        if (subscription) {
            try {
                await apiRequest('DELETE', { endpoint: subscription.endpoint });
            } catch {
                serverCleanupFailed = true;
            }

            await subscription.unsubscribe();
        }

        localStorage.removeItem(PUSH_OWNER_STORAGE_KEY);
        updatePushSettings(container, null);

        if (serverCleanupFailed) {
            container.querySelector('[data-push-status]').textContent = 'Notificações desativadas neste dispositivo.';
        }
    } catch {
        const status = container.querySelector('[data-push-status]');

        if (status) {
            status.textContent = 'Não foi possível desativar agora. Tente novamente.';
        }
    } finally {
        button.textContent = 'Desativar neste dispositivo';
        button.disabled = false;
    }
}

async function initializePushSettings() {
    const container = document.querySelector('[data-push-settings]');

    if (!container) {
        return;
    }

    try {
        updatePushSettings(container, await currentSubscription());
    } catch {
        const status = container.querySelector('[data-push-status]');

        if (status) {
            status.textContent = 'Não foi possível consultar as notificações deste dispositivo.';
        }
    }
}

async function syncSubscriptionOwner() {
    const userId = metaContent('authenticated-user-id');

    if (!userId || !supportsPush() || Notification.permission !== 'granted') {
        return;
    }

    const subscription = await currentSubscription();

    if (subscription && localStorage.getItem(PUSH_OWNER_STORAGE_KEY) !== userId) {
        await saveSubscription(subscription);
    }
}

async function unsubscribeBeforeLogout(form) {
    try {
        const subscription = await currentSubscription();

        if (subscription) {
            const endpointInput = document.createElement('input');
            endpointInput.type = 'hidden';
            endpointInput.name = 'push_endpoint';
            endpointInput.value = subscription.endpoint;
            form.appendChild(endpointInput);

            const controller = new AbortController();
            const timeout = window.setTimeout(() => controller.abort(), 1200);

            try {
                await apiRequest('DELETE', { endpoint: subscription.endpoint }, controller.signal);
            } finally {
                window.clearTimeout(timeout);
                await subscription.unsubscribe();
            }
        }
    } catch {
        // A falha de limpeza nunca deve impedir o logout.
    } finally {
        localStorage.removeItem(PUSH_OWNER_STORAGE_KEY);
        HTMLFormElement.prototype.submit.call(form);
    }
}

function registerPushActions() {
    document.addEventListener('change', (event) => {
        const container = event.target.closest('[data-push-settings]');

        if (container && event.target.matches('[data-push-type]')) {
            currentSubscription().then((subscription) => updatePushSettings(container, subscription));
        }
    });

    document.addEventListener('click', (event) => {
        const enableButton = event.target.closest('[data-push-enable]');
        const disableButton = event.target.closest('[data-push-disable]');

        if (enableButton) {
            enablePush(enableButton.closest('[data-push-settings]'));
        }

        if (disableButton) {
            disablePush(disableButton.closest('[data-push-settings]'));
        }
    });

    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (form.matches('form[action$="/logout"]') && !form.dataset.pushCleanupStarted) {
            event.preventDefault();
            form.dataset.pushCleanupStarted = 'true';
            unsubscribeBeforeLogout(form);
        }
    });

    document.addEventListener('livewire:navigated', initializePushSettings);
    window.addEventListener('preferences-saved', initializePushSettings);
}

async function initializePwa() {
    registerInstallExperience();
    registerPushActions();

    if ('serviceWorker' in navigator && window.isSecureContext) {
        await navigator.serviceWorker.register('/sw.js', { scope: '/' });
        await Promise.allSettled([
            initializePushSettings(),
            syncSubscriptionOwner(),
        ]);

        if (isStandalone()) {
            window.setTimeout(showPushOffer, 1200);
        }
    } else {
        initializePushSettings();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    initializePwa().catch(() => {
        initializePushSettings();
    });
});
