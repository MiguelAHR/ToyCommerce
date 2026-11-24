(function () {
 'use strict';
 // Delegation is intentional: wp_footer prints these dialogs after footer scripts.
 let active = null, opener = null, overlay = null, savedOverflow = '', savedPriority = '';
 const focusable = dialog => Array.from(dialog.querySelectorAll('button,a[href],input,select,textarea,[tabindex]')).filter(el => !el.disabled && el.tabIndex >= 0 && el.getClientRects().length);
 function restore() {
  const button = opener;
  if (active) { active.removeAttribute('open'); active.classList.remove('is-open'); }
  if (overlay) overlay.remove();
  document.body.style.setProperty('overflow', savedOverflow, savedPriority);
  document.body.classList.remove('mk10-modal-open');
  active = opener = overlay = null;
  if (button && button.isConnected) button.focus({preventScroll:true});
 }
 function close() {
  if (!active) return;
  const dialog = active;
  // Clear state synchronously; a queued native close event must not close a new dialog.
  if (typeof dialog.close === 'function') { try { dialog.close(); } catch (_) {} }
  restore();
 }
 function open(button) {
  const dialog = document.getElementById('mk8-' + button.dataset.mk8Dialog);
  if (!dialog || !dialog.matches('.mk8-dialog')) return;
  if (active === dialog) return;
  if (active) close();
  active = dialog; opener = button;
  savedOverflow = document.body.style.getPropertyValue('overflow');
  savedPriority = document.body.style.getPropertyPriority('overflow');
  document.body.style.setProperty('overflow','hidden','important');
  document.body.classList.add('mk10-modal-open');
  try {
   if (typeof dialog.showModal !== 'function') throw new Error('Dialog API unavailable');
   dialog.showModal();
  } catch (_) {
   dialog.setAttribute('open',''); dialog.classList.add('is-open');
   overlay = document.createElement('div'); overlay.className = 'mk10-modal-overlay';
   overlay.setAttribute('aria-hidden','true'); document.body.append(overlay);
  }
  (dialog.querySelector('.mk8-close') || dialog).focus({preventScroll:true});
 }
 document.addEventListener('click', event => {
  const target = event.target instanceof Element ? event.target : null;
  if (!target) return;
  const button = target.closest('[data-mk8-dialog]');
  if (button) { event.preventDefault(); open(button); return; }
  if (!active) return;
  if (target.closest('.mk8-close,.mk8-dismiss') && active.contains(target)) { event.preventDefault(); close(); return; }
  if (target === overlay) { close(); return; }
  if (target === active) {
   const r = active.getBoundingClientRect();
   if (event.clientX < r.left || event.clientX > r.right || event.clientY < r.top || event.clientY > r.bottom) close();
  }
 });
 document.addEventListener('cancel', event => { if (event.target === active) { event.preventDefault(); close(); } }, true);
 document.addEventListener('close', event => { if (event.target === active && !active.hasAttribute('open')) restore(); }, true);
 document.addEventListener('keydown', event => {
  if (!active) return;
  if (event.key === 'Escape') { event.preventDefault(); close(); return; }
  if (event.key !== 'Tab') return;
  const list = focusable(active), first = list[0], last = list[list.length - 1];
  if (!first) { event.preventDefault(); active.focus(); return; }
  if (event.shiftKey && (document.activeElement === first || !active.contains(document.activeElement))) { event.preventDefault(); last.focus(); }
  else if (!event.shiftKey && (document.activeElement === last || !active.contains(document.activeElement))) { event.preventDefault(); first.focus(); }
 });
 document.addEventListener('focusin', event => {
  if (active && !active.contains(event.target)) (focusable(active)[0] || active).focus({preventScroll:true});
 });
 window.addEventListener('pagehide',close);
 window.addEventListener('pageshow',() => { if (active) close(); });
})();
