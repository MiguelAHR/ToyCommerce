(function($) {
  if (!$) return;

  function openDrawer() {
    $('#minikids-cart-drawer').addClass('active').attr('aria-hidden', 'false');
    $('body').addClass('minikids-cart-open');
  }

  function closeDrawer() {
    $('#minikids-cart-drawer').removeClass('active').attr('aria-hidden', 'true');
    $('body').removeClass('minikids-cart-open');
  }

  // 1. Click on header cart icon / item opens drawer (desktop & mobile)
  $(document).on('click', '.ct-header-cart a, .ct-cart-item', function(e) {
    if (window.location.pathname.indexOf('/cart/') === -1 && window.location.pathname.indexOf('/checkout/') === -1) {
      e.preventDefault();
      openDrawer();
    }
  });

  // 2. Click close button, backdrop, or "Seguir comprando" closes drawer
  $(document).on('click', '.minikids-cart-close, .minikids-cart-drawer-backdrop, .drawer-continue-btn', function(e) {
    e.preventDefault();
    closeDrawer();
  });

  // 3. ESC key closes drawer
  $(document).on('keydown', function(e) {
    if (e.key === 'Escape' && $('#minikids-cart-drawer').hasClass('active')) {
      closeDrawer();
    }
  });

  // 4. Auto-open drawer when a product is added via AJAX!
  $(document.body).on('added_to_cart', function(event, fragments, cart_hash, $button) {
    setTimeout(function() {
      openDrawer();
    }, 200);
  });

  // 5. Update drawer count when fragments change
  $(document.body).on('wc_fragments_refreshed wc_fragments_loaded added_to_cart', function() {
    setTimeout(function() {
      var count = $('.ct-dynamic-count-cart').first().text() || '0';
      $('.minikids-cart-drawer .cart-count-badge').text('(' + count.trim() + ')');
    }, 100);
  });

  // 6. Feedback interactivo para el formulario de newsletter en el footer
  $(document).on('submit', '.footer-newsletter-form', function(e) {
    e.preventDefault();
    var $form = $(this);
    var $input = $form.find('input[type="email"]');
    var $btn = $form.find('button');
    var val = $input.val();
    if (!val || val.indexOf('@') === -1) return;

    $btn.text('¡Suscrito!').css({
      'background': '#148043',
      'cursor': 'default',
      'transition': 'all 0.3s ease'
    });
    $input.val('').attr('placeholder', '¡Gracias por suscribirte! 🎉').prop('disabled', true);
  });

  // 7. Feedback interactivo para el formulario de contacto
  $(document).on('submit', '#minikids-contact-form', function(e) {
    e.preventDefault();
    var $form = $(this);
    var $btn = $form.find('#contact-submit-btn');
    var $msg = $form.find('#contact-success-msg');
    
    $btn.text('Enviado ✔').prop('disabled', true).css({
      'background': '#e1f6ec',
      'color': '#148043',
      'cursor': 'default'
    });
    $form.find('input, textarea').prop('disabled', true);
    $msg.slideDown(250);
  });

  // 8. Sincronización continua de etiquetas flotantes en el checkout (evita solapamiento en autofill o prellenado)
  function syncCheckoutFloatingLabels() {
    $('.wc-block-components-text-input').each(function() {
      var $wrap = $(this);
      var $input = $wrap.find('input, textarea');
      if ($input.length) {
        var val = $input.val();
        if ((val && val.toString().trim().length > 0) || $input.is(':focus')) {
          $wrap.addClass('is-active');
        } else if (!$input.is(':focus')) {
          $wrap.removeClass('is-active');
        }
      }
    });
  }

  $(document).on('focus blur input change keyup', '.wc-block-components-text-input input, .wc-block-components-text-input textarea', function() {
    syncCheckoutFloatingLabels();
  });

  if (window.location.pathname.indexOf('/checkout/') !== -1) {
    setInterval(syncCheckoutFloatingLabels, 400);
  }

})(window.jQuery);
