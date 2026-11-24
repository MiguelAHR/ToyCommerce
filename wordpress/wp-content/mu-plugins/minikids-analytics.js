(function($){
  'use strict';
  const cfg = window.mkGaConfig || {};
  const qs = (s, r = document) => r.querySelector(s);
  const all = (s, r = document) => [...r.querySelectorAll(s)];

  const state = {
    events: [],
    debugMode: Boolean(cfg.debug),
    debug() {
      this.debugMode = !this.debugMode || true;
      console.info('[mkGa] debug activado. Eventos registrados:', this.events.length);
      this.events.forEach(ev => console.log('[mkGa]', ev.name, ev.params));
      return this.events;
    },
    track(name, params = {}) {
      const payload = { ...params };
      try {
        if (sessionStorage.getItem('mk_test') === '1') {
          payload.traffic_type = 'test';
        }
      } catch (_) {}
      this.events.push({ name, params: payload, at: Date.now() });
      if (this.debugMode) {
        console.log('[mkGa]', name, payload);
      }
      if (cfg.active && typeof window.gtag === 'function') {
        window.gtag('event', name, payload);
      }
    }
  };
  window.mkGa = state;

  function parseItem(el) {
    const holder = el?.closest('li.product')?.querySelector('.mk-ga-item') || el?.querySelector('.mk-ga-item');
    if (!holder) return null;
    try {
      return JSON.parse(holder.dataset.mkItem || 'null');
    } catch (_) {
      return null;
    }
  }

  function listContext(listEl) {
    if (!listEl) return { id: 'catalogo', name: 'Tienda' };
    if (listEl.closest('.mk12-home-track')) {
      const sec = listEl.closest('.mk4-section');
      const h2 = sec?.querySelector('h2')?.textContent.trim() || 'Destacados portada';
      return { id: 'home_' + h2.toLowerCase().replace(/[^a-z0-9]+/g, '_'), name: h2 };
    }
    if (listEl.closest('.related.products')) return { id: 'related_products', name: 'Productos relacionados' };
    if (listEl.closest('.mk5-cart-related')) return { id: 'cart_related', name: 'Artículos relacionados' };
    if (cfg.isCategory) {
      const h1 = qs('.mk19-cat-header h1')?.textContent.trim() || document.title.split('|')[0].trim();
      return { id: 'category_' + h1.toLowerCase().replace(/[^a-z0-9]+/g, '_'), name: h1 };
    }
    return { id: 'tienda_catalogo', name: 'Tienda de juguetes' };
  }

  function scanItemLists() {
    all('ul.products').forEach(ul => {
      if (ul.dataset.mkGaListSent === '1') return;
      const cards = all(':scope > li.product', ul);
      if (!cards.length) return;
      const ctx = listContext(ul);
      const items = [];
      cards.forEach((li, idx) => {
        const it = parseItem(li);
        if (it) {
          items.push({ ...it, index: idx + 1, item_list_name: ctx.name });
        }
      });
      if (items.length) {
        ul.dataset.mkGaListSent = '1';
        state.track('view_item_list', {
          item_list_id: ctx.id,
          item_list_name: ctx.name,
          items: items.slice(0, 24)
        });
      }
    });
  }

  function scanPromotions() {
    if (!cfg.isFront) return;
    const items = [];
    all('.mkr-hero-slider .mkr-ad-image, .mkr-promos .mkr-ad-image').forEach((fig, idx) => {
      const img = qs('img', fig);
      const a = qs('a', fig);
      if (!img || !a || fig.dataset.mkGaPromoSent === '1') return;
      fig.dataset.mkGaPromoSent = '1';
      const isHero = Boolean(fig.closest('.mkr-hero-slider'));
      const loc = (isHero ? 'hero_panel_' : 'home_promo_') + (idx + 1);
      const creative = (img.currentSrc || img.src || '').split('/').pop().split('?')[0];
      const name = (img.alt || '').split('.')[0].trim() || loc;
      fig.dataset.mkPromoName = name;
      fig.dataset.mkPromoCreative = creative;
      fig.dataset.mkPromoLoc = loc;
      items.push({ promotion_name: name, creative_name: creative, location_id: loc });
    });
    if (items.length) {
      state.track('view_promotion', { items });
    }
  }

  // Pending AJAX add-to-cart context
  const pendingAdd = new Map();
  let lastAuthMode = 'login';

  // Flush auth event persisted across reload
  try {
    const authPending = sessionStorage.getItem('mk_ga_auth_pending');
    if (authPending) {
      sessionStorage.removeItem('mk_ga_auth_pending');
      state.track(authPending, { method: 'email' });
    }
  } catch (_) {}

  document.addEventListener('click', async e => {
    const t = e.target instanceof Element ? e.target : null;
    if (!t) return;

    // 1. Consent banner buttons
    const cBtn = t.closest('[data-mk19-consent]');
    if (cBtn) {
      const choice = cBtn.dataset.mk19Consent === 'denied' ? 'denied' : 'granted';
      try { localStorage.setItem('mk_ga_consent', choice); } catch (_) {}
      if (typeof window.gtag === 'function') {
        window.gtag('consent', 'update', { analytics_storage: choice });
      }
      const bar = qs('#mk19-consent');
      if (bar) bar.hidden = true;
      return;
    }

    // 2. Share buttons on single product page
    const shareEl = t.closest('[data-mk-share]');
    if (shareEl) {
      const method = shareEl.dataset.mkShare;
      const itemId = shareEl.dataset.mkItemId || '';
      if (method === 'copy') {
        e.preventDefault();
        const url = shareEl.dataset.mkShareUrl || location.href;
        const title = shareEl.dataset.mkShareTitle || document.title;
        const status = qs('.mk19-share-status');
        let used = 'copy';
        try {
          if (navigator.clipboard && navigator.clipboard.writeText) {
            await navigator.clipboard.writeText(url);
            if (status) status.textContent = 'Enlace copiado';
          } else if (navigator.share) {
            used = 'web_share';
            await navigator.share({ title, url });
            if (status) status.textContent = 'Enlace compartido';
          } else {
            throw new Error('clipboard unavailable');
          }
        } catch (_) {
          if (navigator.share) {
            try {
              used = 'web_share';
              await navigator.share({ title, url });
              if (status) status.textContent = 'Enlace compartido';
            } catch (__) {}
          }
        }
        state.track('share', { method: used, content_type: 'product', item_id: itemId });
        return;
      }
      state.track('share', { method, content_type: 'product', item_id: itemId });
      return;
    }

    // 3. Promotion click on homepage
    const promoLink = t.closest('.mkr-hero-slider .mkr-ad-image a, .mkr-promos .mkr-ad-image a');
    if (promoLink) {
      const fig = promoLink.closest('.mkr-ad-image');
      state.track('select_promotion', {
        promotion_name: fig?.dataset.mkPromoName || '',
        creative_name: fig?.dataset.mkPromoCreative || '',
        location_id: fig?.dataset.mkPromoLoc || 'home_promo'
      });
      return;
    }

    // 4. Card AJAX add_to_cart click capture
    const ajaxBtn = t.closest('li.product .ajax_add_to_cart');
    if (ajaxBtn) {
      const li = ajaxBtn.closest('li.product');
      const it = parseItem(li);
      const qtyInput = qs('.mkr-quantity input, input.qty', li);
      const qty = Math.max(1, parseInt(qtyInput?.value || ajaxBtn.dataset.quantity || '1', 10) || 1);
      const pid = String(ajaxBtn.dataset.product_id || '');
      if (it && pid) {
        const ctx = listContext(li.closest('ul.products'));
        pendingAdd.set(pid, { ...it, quantity: qty, item_list_name: ctx.name });
      }
      return;
    }

    // 5. Card select_item click
    const cardLink = t.closest('li.product a:not(.ajax_add_to_cart):not(.added_to_cart)');
    if (cardLink) {
      const li = cardLink.closest('li.product');
      const it = parseItem(li);
      if (it) {
        const ul = li.closest('ul.products');
        const ctx = listContext(ul);
        const idx = all(':scope > li.product', ul).indexOf(li) + 1;
        state.track('select_item', {
          item_list_id: ctx.id,
          item_list_name: ctx.name,
          items: [{ ...it, index: idx || 1, item_list_name: ctx.name }]
        });
      }
      return;
    }

    // 6. Remove from cart (table, mini-cart drawer, checkout summary)
    const rem = t.closest('a.remove[data-cart_item_key], a.remove[data-product_id], .mk9-product-actions [data-remove]');
    if (rem) {
      let item = null;
      const key = rem.dataset.cart_item_key || rem.closest('[data-key]')?.dataset.key || '';
      const pid = rem.dataset.product_id || '';
      const map = cfg.cartMap || {};
      if (key && map[key]) {
        item = { ...map[key] };
      } else if (pid && map['id_' + pid]) {
        item = { ...map['id_' + pid] };
      }
      if (!item) {
        const row = rem.closest('tr.cart_item, li.woocommerce-mini-cart-item, .mk9-product');
        const qtyEl = row?.querySelector('input.qty, input[type="number"]');
        const qty = Math.max(1, parseInt(qtyEl?.value || '1', 10) || 1);
        const name = row?.querySelector('.product-name a, .mk11-mini-name, a')?.textContent.trim() || 'Producto';
        item = { item_id: rem.dataset.product_sku || pid || key || 'item', item_name: name, price: 0, quantity: qty };
      } else {
        const row = rem.closest('tr.cart_item, li.woocommerce-mini-cart-item, .mk9-product');
        const qtyEl = row?.querySelector('input.qty, input[type="number"]');
        if (qtyEl) item.quantity = Math.max(1, parseInt(qtyEl.value || String(item.quantity), 10) || item.quantity);
      }
      state.track('remove_from_cart', {
        currency: 'PEN',
        value: Number(((item.price || 0) * (item.quantity || 1)).toFixed(2)),
        items: [item]
      });
      return;
    }

    // 7. Reviews modal open
    if (t.closest('[data-mk16-reviews-open], a[href$="#reviews"], a[href$="#comments"]')) {
      state.track('view_reviews', { item_id: cfg.product?.item_id || '' });
    }
  });

  // Track single product non-AJAX add-to-cart form submit
  document.addEventListener('submit', e => {
    const form = e.target;
    if (!(form instanceof HTMLFormElement)) return;
    if (form.matches('form.cart') && cfg.isProduct && cfg.product) {
      const qtyEl = qs('input.qty', form);
      const qty = Math.max(1, parseInt(qtyEl?.value || '1', 10) || 1);
      const item = { ...cfg.product, quantity: qty };
      state.track('add_to_cart', {
        currency: 'PEN',
        value: Number((item.price * qty).toFixed(2)),
        items: [item]
      });
      return;
    }
    if (form.matches('#mk12-auth form[data-mk12-view]')) {
      lastAuthMode = form.dataset.mk12View === 'register' ? 'sign_up' : 'login';
    }
  });

  document.addEventListener('mk17:auth-success', () => {
    const evName = lastAuthMode === 'sign_up' ? 'sign_up' : 'login';
    try { sessionStorage.setItem('mk_ga_auth_pending', evName); } catch (_) {}
    state.track(evName, { method: 'email' });
  });

  if ($) {
    $(document.body).on('added_to_cart', function(ev, fragments, cartHash, $button) {
      const pid = String($button?.data?.('product_id') || $button?.attr?.('data-product_id') || '');
      let item = pendingAdd.get(pid);
      pendingAdd.delete(pid);
      if (!item && $button && $button[0]) {
        const li = $button[0].closest('li.product');
        const parsed = parseItem(li);
        if (parsed) {
          const qty = Math.max(1, parseInt($button.attr('data-quantity') || '1', 10) || 1);
          item = { ...parsed, quantity: qty };
        }
      }
      if (item) {
        state.track('add_to_cart', {
          currency: 'PEN',
          value: Number((item.price * item.quantity).toFixed(2)),
          items: [item]
        });
      }
    });
  }

  // Checkout funnel tracking (begin_checkout, add_shipping_info, add_payment_info)
  let shippingSent = '';
  let paymentSent = '';
  function initCheckoutTracking() {
    if (!cfg.isCheckout || !cfg.checkout) return;
    state.track('begin_checkout', cfg.checkout);

    document.addEventListener('click', e => {
      const t = e.target instanceof Element ? e.target : null;
      if (!t) return;
      const cards = all('.mk9-card');
      // Step 1 ("Envío") Continuar button clicked -> emit add_shipping_info if step 2 becomes visible shortly
      if (cards[1] && cards[1].contains(t) && t.matches('button:not(.mk9-edit)')) {
        setTimeout(() => {
          if (cards[2] && cards[2].classList.contains('mk9-open')) {
            const checked = qs('input[name^="shipping_method"]:checked, input[type="hidden"][name^="shipping_method"]');
            const label = checked?.closest('li, td')?.querySelector('label')?.textContent.trim() || checked?.value || 'Envío estándar';
            if (label !== shippingSent) {
              shippingSent = label;
              state.track('add_shipping_info', { ...cfg.checkout, shipping_tier: label });
            }
          }
        }, 60);
      }
    });

    document.addEventListener('change', e => {
      const t = e.target;
      if (t instanceof HTMLInputElement && t.name === 'payment_method' && t.checked) {
        const label = t.closest('li')?.querySelector('label')?.textContent.trim() || t.value;
        if (label !== paymentSent) {
          paymentSent = label;
          state.track('add_payment_info', { ...cfg.checkout, payment_type: label });
        }
      }
    });

    // Also fire add_payment_info if default gateway is already checked on entering Pago
    document.addEventListener('click', e => {
      const t = e.target instanceof Element ? e.target : null;
      if (!t) return;
      if (t.matches('#place_order')) {
        const checked = qs('input[name="payment_method"]:checked');
        const label = checked?.closest('li')?.querySelector('label')?.textContent.trim() || checked?.value || '';
        if (label && label !== paymentSent) {
          paymentSent = label;
          state.track('add_payment_info', { ...cfg.checkout, payment_type: label });
        }
      }
    });
  }

  function initConsentBar() {
    const bar = qs('#mk19-consent');
    if (!bar) return;
    let saved = null;
    try { saved = localStorage.getItem('mk_ga_consent'); } catch (_) {}
    if (!saved) {
      bar.hidden = false;
    }
  }

  function initOnLoad() {
    initConsentBar();
    scanItemLists();
    scanPromotions();

    if (cfg.isProduct && cfg.product) {
      state.track('view_item', {
        currency: 'PEN',
        value: cfg.product.price,
        items: [cfg.product]
      });
    }
    if (cfg.isCart && cfg.cart) {
      state.track('view_cart', cfg.cart);
    }
    if (cfg.searchTerm) {
      state.track('search', { search_term: cfg.searchTerm });
    }
    if (cfg.purchase) {
      const k = 'mk_ga_purchase_' + cfg.purchase.transaction_id;
      let already = false;
      try { already = Boolean(localStorage.getItem(k)); } catch (_) {}
      if (!already) {
        try { localStorage.setItem(k, '1'); } catch (_) {}
        state.track('purchase', cfg.purchase);
      }
    }
    initCheckoutTracking();

    // Debounced live catalog search tracking
    const catSearch = qs('#minikids-catalog-search');
    if (catSearch) {
      let timer = 0, lastTerm = cfg.searchTerm || '';
      catSearch.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(() => {
          const val = catSearch.value.trim();
          if (val.length >= 2 && val !== lastTerm) {
            lastTerm = val;
            state.track('search', { search_term: val });
          }
        }, 800);
      });
    }

    if (location.hash === '#reviews' && qs('#mk16-reviews-dialog')) {
      state.track('view_reviews', { item_id: cfg.product?.item_id || '' });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initOnLoad);
  } else {
    initOnLoad();
  }
})(window.jQuery);
