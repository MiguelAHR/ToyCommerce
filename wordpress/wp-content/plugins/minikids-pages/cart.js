(function () {
  const key = 'minikids-cart';
  let cart = JSON.parse(localStorage.getItem(key) || '[]');
  const drawer = document.querySelector('.cart-drawer');
  const itemsEl = document.querySelector('.cart-items');
  const totalEl = document.querySelector('.cart-total-value');
  const countEls = document.querySelectorAll('.cart-count');
  const money = n => 'S/ ' + Number(n).toFixed(2);
  function save() { localStorage.setItem(key, JSON.stringify(cart)); render(); }
  function render() {
    const count = cart.reduce((sum, item) => sum + item.qty, 0);
    const total = cart.reduce((sum, item) => sum + item.price * item.qty, 0);
    countEls.forEach(el => el.textContent = count);
    if (!itemsEl) return;
    totalEl.textContent = money(total);
    itemsEl.innerHTML = cart.length ? cart.map((item, index) => `<div class="cart-item"><div class="cart-item-thumb">${item.emoji}</div><div><p class="cart-item-name">${item.name}</p><span class="cart-item-price">${item.qty} × ${money(item.price)}</span></div><button class="cart-remove" data-index="${index}" type="button">Quitar</button></div>`).join('') : '<div class="cart-empty">Tu carrito está esperando una aventura ✨</div>';
  }
  document.addEventListener('click', event => {
    const add = event.target.closest('.js-add-cart');
    if (add) {
      const product = JSON.parse(add.dataset.product), found = cart.find(item => item.id === product.id);
      found ? found.qty++ : cart.push({ ...product, qty: 1 }); save(); open(); add.innerHTML = '¡Añadido! ✓'; setTimeout(() => add.innerHTML = 'Añadir al carrito <span>＋</span>', 1200);
    }
    if (event.target.closest('.js-open-cart')) open();
    if (event.target.closest('.cart-close') || event.target === drawer) close();
    const remove = event.target.closest('.cart-remove'); if (remove) { cart.splice(Number(remove.dataset.index), 1); save(); }
  });
  function open() { drawer?.classList.add('is-open'); drawer?.setAttribute('aria-hidden', 'false'); document.body.style.overflow = 'hidden'; }
  function close() { drawer?.classList.remove('is-open'); drawer?.setAttribute('aria-hidden', 'true'); document.body.style.overflow = ''; }
  render();
})();
