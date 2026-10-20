(function($) {
  'use strict';
  if (!$) return;

  // Función utilitaria para normalizar texto (sin tildes, minúsculas, sin espacios extra)
  function normalizeText(str) {
    if (!str) return '';
    return str
      .toString()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .toLowerCase()
      .trim();
  }

  $(function() {
    // 1. Interceptar formularios de búsqueda de la cabecera en TODAS las páginas
    $(document).on('submit', '.ct-search-form, header form[role="search"], #search-modal form', function(e) {
      var $input = $(this).find('input[type="search"], input[name="s"], .modal-field');
      var query = $input.val();
      if (query && query.trim().length > 0) {
        e.preventDefault();
        var cleanQ = query.trim();

        // Si ya estamos en la tienda, actualizar el buscador local y cerrar modal
        if (window.location.pathname.indexOf('/tienda/') !== -1) {
          var $localSearch = $('#minikids-catalog-search');
          if ($localSearch.length) {
            $localSearch.val(cleanQ).trigger('input');
            // Cerrar modal de búsqueda si está abierto
            $('.ct-panel[data-behaviour="modal"].active, #search-modal.active').removeClass('active');
            $('body').removeClass('ct-panel-open');
            return;
          }
        }

        // Si estamos en otra página, redirigir a la tienda con el término
        window.location.href = minikidsCatalog.shopUrl + '?q=' + encodeURIComponent(cleanQ);
      }
    });

    // 2. Lógica del Catálogo en Vivo en /tienda/
    var $grid = $('.minikids-tienda-grid ul.products');
    if (!$grid.length) return;

    var $items = $grid.find('li.product');
    var $pills = $('.minikids-category-pills .minikids-pill:not(.minikids-pill-reset)');
    var $pillReset = $('.minikids-pill-reset');
    var $searchInput = $('#minikids-catalog-search');
    var $searchClear = $('#minikids-search-clear');
    var $noResults = $('.minikids-no-results');

    var currentCat = 'all';
    var currentQuery = '';

    var catLabels = {
      'all': 'Todos los juguetes',
      'peluches': 'Peluches',
      'didacticos': 'Didácticos y Madera',
      'vehiculos': 'Vehículos',
      'juegos-de-rol': 'Juegos de rol',
      'creatividad': 'Creatividad',
      'juegos-familiares': 'Juegos en familia',
      'tecnologia': 'Tecnología'
    };

    function applyFilter() {
      var visibleCount = 0;
      var needle = normalizeText(currentQuery);

      $items.each(function() {
        var $el = $(this);
        var title = $el.find('.woocommerce-loop-product__title').text();
        var catText = $el.find('.entry-meta, .meta-categories').text();
        var classList = $el.attr('class') || '';

        // Comprobación de categoría
        var matchesCat = false;
        if (currentCat === 'all') {
          matchesCat = true;
        } else if ($el.hasClass('product_cat-' + currentCat)) {
          matchesCat = true;
        }

        // Comprobación de texto con normalización de acentos
        var matchesSearch = true;
        if (needle.length > 0) {
          var haystack = normalizeText(title + ' ' + catText + ' ' + classList);
          matchesSearch = (haystack.indexOf(needle) !== -1);
        }

        if (matchesCat && matchesSearch) {
          $el.removeClass('minikids-product-hidden');
          visibleCount++;
        } else {
          $el.addClass('minikids-product-hidden');
        }
      });

      // Actualizar estado visual de píldoras
      $pills.removeClass('minikids-pill-active');
      $pills.filter('[data-cat="' + currentCat + '"]').addClass('minikids-pill-active');

      // Mostrar píldora de "Limpiar filtro ✕" SOLO cuando hay un filtro o búsqueda activa
      var hasFilter = (currentCat && currentCat !== 'all') || (needle && needle.length > 0);
      if (hasFilter) {
        $pillReset.addClass('is-visible').show();
      } else {
        $pillReset.removeClass('is-visible').hide();
      }

      // Mensaje cuando no hay resultados
      if (visibleCount === 0) {
        $noResults.stop(true, true).fadeIn(200);
      } else {
        $noResults.hide();
      }

      // Botón limpiar búsqueda (cruz en el input)
      if (currentQuery.length > 0) {
        $searchClear.show();
      } else {
        $searchClear.hide();
      }
    }

    // Clic en píldoras / etiquetas de categoría
    $(document).on('click', '.minikids-category-pills .minikids-pill:not(.minikids-pill-reset)', function(e) {
      e.preventDefault();
      var cat = $(this).attr('data-cat') || 'all';
      currentCat = cat;

      var newUrl = new URL(window.location);
      if (currentCat !== 'all') {
        newUrl.searchParams.set('cat', currentCat);
      } else {
        newUrl.searchParams.delete('cat');
      }
      window.history.pushState({}, '', newUrl);

      applyFilter();
    });

    // Clic en botón "Limpiar filtro ✕" o en botón de reiniciar de "Sin resultados"
    $(document).on('click', '.minikids-pill-reset, .btn-reset-catalog', function(e) {
      e.preventDefault();
      currentCat = 'all';
      currentQuery = '';
      $searchInput.val('');
      $pillReset.removeClass('is-visible').hide();
      var newUrl = new URL(window.location);
      newUrl.searchParams.delete('cat');
      newUrl.searchParams.delete('categoria');
      newUrl.searchParams.delete('q');
      newUrl.searchParams.delete('s');
      window.history.pushState({}, '', newUrl);
      applyFilter();
    });

    // Entrada en tiempo real en el buscador
    $searchInput.on('input keyup', function() {
      currentQuery = $(this).val();
      var newUrl = new URL(window.location);
      if (currentQuery.trim().length > 0) {
        newUrl.searchParams.set('q', currentQuery.trim());
      } else {
        newUrl.searchParams.delete('q');
        newUrl.searchParams.delete('s');
      }
      window.history.pushState({}, '', newUrl);
      applyFilter();
    });

    // Evitar recarga de página al presionar Enter en el buscador
    $searchInput.on('keydown', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        currentQuery = $(this).val();
        applyFilter();
      }
    });

    // Limpiar input con la cruz ✕
    $searchClear.on('click', function() {
      $searchInput.val('');
      currentQuery = '';
      var newUrl = new URL(window.location);
      newUrl.searchParams.delete('q');
      newUrl.searchParams.delete('s');
      window.history.pushState({}, '', newUrl);
      applyFilter();
      $searchInput.focus();
    });

    // Leer parámetros iniciales de la URL (?cat= y ?q= / ?s=)
    var urlParams = new URLSearchParams(window.location.search);
    var initCat = urlParams.get('cat') || urlParams.get('categoria');
    var initSearch = urlParams.get('q') || urlParams.get('s') || urlParams.get('buscar');

    if (initCat) {
      currentCat = initCat;
    }
    if (initSearch) {
      currentQuery = initSearch;
      $searchInput.val(initSearch);
    }

    applyFilter();
  });
})(window.jQuery);
