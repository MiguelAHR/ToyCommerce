document.addEventListener('DOMContentLoaded',()=>{
  document.querySelector('[data-mkpr-print]')?.addEventListener('click',()=>window.print());
  document.querySelector('.mkpr-errors')?.focus();
  const form=document.querySelector('.mkpr-form');
  if(!form)return;
  const minor=form.querySelector('[name=mkpr_minor]');
  const guardian=form.querySelector('[name=mkpr_guardian]');
  const syncMinor=()=>{guardian.required=minor.checked;};
  minor.addEventListener('change',syncMinor);syncMinor();
  form.addEventListener('invalid',event=>event.target.setAttribute('aria-invalid','true'),true);
  form.addEventListener('input',event=>{
    if(event.target.matches('input,textarea')&&event.target.validity.valid)event.target.removeAttribute('aria-invalid');
  });
  form.addEventListener('submit',()=>{
    // No desactivar el submit: su nombre identifica la operación en el servidor.
    form.setAttribute('aria-busy','true');
    form.querySelector('button[type=submit]').textContent='Registrando…';
  });
});
