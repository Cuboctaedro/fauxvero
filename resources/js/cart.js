import Alpine from 'alpinejs';

// Only product ids and quantities live in the browser. Names and prices always
// come from the server (/cart/lines), so they can't go stale or be tampered with.
const STORAGE_KEY = 'fauxvero.cart';
const MAX_QTY = 99;

function sanitize(items) {
    if (!Array.isArray(items)) {
        return [];
    }

    return items
        .map((item) => ({ id: Number.parseInt(item?.id, 10), qty: Number.parseInt(item?.qty, 10) }))
        .filter((item) => item.id > 0 && item.qty > 0)
        .map((item) => ({ id: item.id, qty: Math.min(item.qty, MAX_QTY) }));
}

function read() {
    try {
        return sanitize(JSON.parse(localStorage.getItem(STORAGE_KEY)));
    } catch {
        return [];
    }
}

function write(items) {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
    } catch {
        // Storage blocked or full: the cart still works for this page view.
    }
}

Alpine.store('cart', {
    items: read(),

    get count() {
        return this.items.reduce((total, item) => total + item.qty, 0);
    },

    replace(items) {
        this.items = sanitize(items);
        write(this.items);
    },

    add(id, qty = 1) {
        const existing = this.items.find((item) => item.id === id);

        this.replace(
            existing
                ? this.items.map((item) => (item.id === id ? { id, qty: item.qty + qty } : item))
                : [...this.items, { id, qty }],
        );
    },

    setQty(id, qty) {
        if (!Number.isFinite(qty) || qty < 1) {
            return;
        }

        this.replace(this.items.map((item) => (item.id === id ? { id, qty } : item)));
    },

    remove(id) {
        this.replace(this.items.filter((item) => item.id !== id));
    },

    clear() {
        this.replace([]);
    },
});

// Keep other open tabs in sync.
window.addEventListener('storage', (event) => {
    if (event.key === STORAGE_KEY) {
        Alpine.store('cart').items = read();
    }
});

// Server-priced view of the cart, shared by the cart and checkout pages.
Alpine.data('cartLines', () => ({
    lines: [],
    subtotal: '0.00',
    purchasable: false,
    loading: true,
    failed: false,
    request: 0,

    init() {
        this.load();
        this.$watch('$store.cart.items', () => this.load());
    },

    async load() {
        const request = ++this.request;
        const cart = this.$store.cart;

        if (cart.items.length === 0) {
            Object.assign(this, { lines: [], subtotal: '0.00', purchasable: false, loading: false, failed: false });
            return;
        }

        try {
            const response = await fetch('/cart/lines', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
                body: JSON.stringify({ items: cart.items }),
            });

            if (!response.ok) {
                throw new Error(`Cart request failed: ${response.status}`);
            }

            const data = await response.json();

            // A newer request has started since; let it win.
            if (request !== this.request) {
                return;
            }

            Object.assign(this, {
                lines: data.lines,
                subtotal: data.subtotal,
                purchasable: data.purchasable,
                loading: false,
                failed: false,
            });

            // Products that were deleted or deactivated drop out of the stored cart.
            if (data.missing_ids.length > 0) {
                cart.replace(cart.items.filter((item) => !data.missing_ids.includes(item.id)));
            }
        } catch (error) {
            if (request === this.request) {
                Object.assign(this, { loading: false, failed: true });
            }

            console.error(error);
        }
    },
}));
