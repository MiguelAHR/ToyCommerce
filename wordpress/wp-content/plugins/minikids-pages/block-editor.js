(function (blocks, element, components, blockEditor, serverSideRender, i18n) {
  const el = element.createElement;
  const Fragment = element.Fragment;
  const __ = i18n.__;
  const SelectControl = components.SelectControl;
  const TextControl = components.TextControl;
  const TextareaControl = components.TextareaControl;
  const Button = components.Button;
  const PanelBody = components.PanelBody;
  const InspectorControls = blockEditor.InspectorControls;
  const ServerSideRender = serverSideRender;

  /*
   * Campos editables por pagina.
   * El texto de `def` es solo el placeholder que se muestra cuando el campo
   * esta vacio; el valor real por defecto vive en mkp_copy_defaults() (PHP),
   * que es quien manda al renderizar.
   */
  const FIELDS = {
    home: [
      { key: 'hero_eyebrow',  label: 'Portada · Antetítulo', def: 'La diversión empieza aquí' },
      { key: 'hero_title',    label: 'Portada · Título',     def: 'Juega, imagina y <em>sonríe.</em>', help: 'Admite <em> y <strong>' },
      { key: 'hero_text',     label: 'Portada · Texto',      def: 'Descubre juguetes que despiertan la curiosidad y convierten cada día en una nueva aventura.', long: true },
      { key: 'hero_cta',      label: 'Portada · Botón',      def: 'Ver juguetes' },
      { key: 'cats_title',    label: 'Categorías · Título',  def: 'Compra por categoría' },
      { key: 'fav_title',     label: 'Favoritos · Título',   def: 'Favoritos de la semana' },
      { key: 'promo_eyebrow', label: 'Promoción · Antetítulo', def: 'Pequeños precios, grandes momentos' },
      { key: 'promo_title',   label: 'Promoción · Título',   def: 'Todo por debajo de S/ 29.90' },
      { key: 'news_title',    label: 'Suscripción · Título', def: 'Recibe un poquito de magia' },
      { key: 'news_cta',      label: 'Suscripción · Botón',  def: 'Suscribirme' }
    ],
    shop: [
      { key: 'shop_title', label: 'Tienda · Título',    def: 'Todos los juguetes' },
      { key: 'shop_text',  label: 'Tienda · Subtítulo', def: 'Elige una nueva aventura para cada día.', long: true },
      { key: 'shop_empty', label: 'Tienda · Sin resultados', def: 'No encontramos ese juguete. Prueba con “peluche”, “juego” o “bloques”.', long: true }
    ],
    about: [
      { key: 'about_title',      label: 'Nosotros · Título',   def: 'Jugar es crecer' },
      { key: 'about_text',       label: 'Nosotros · Subtítulo', def: 'Conoce la historia detrás de MiniKids.', long: true },
      { key: 'about_band_title', label: 'Historia · Título',   def: 'Pequeñas sorpresas para grandes sonrisas.' },
      { key: 'about_band_text',  label: 'Historia · Texto',    def: 'En MiniKids creemos que el mejor regalo no es solo un juguete: es el momento de descubrir, imaginar y compartir. Seleccionamos productos bonitos, seguros y llenos de posibilidades para cada etapa.', long: true }
    ],
    contact: [
      { key: 'contact_title',      label: 'Contacto · Título',    def: 'Hablemos' },
      { key: 'contact_text',       label: 'Contacto · Subtítulo', def: 'Estamos aquí para ayudarte a encontrar el regalo perfecto.', long: true },
      { key: 'contact_band_title', label: 'Aviso · Título',       def: '¿Tienes una pregunta?' },
      { key: 'contact_band_text',  label: 'Aviso · Texto',        def: 'Escríbenos a hola@minikids.pe y te responderemos pronto.', long: true },
      { key: 'contact_email',      label: 'Correo del botón',     def: 'hola@minikids.pe', help: 'Destino del enlace «Escribir ahora»' },
      { key: 'contact_cta',        label: 'Aviso · Botón',        def: 'Escribir ahora' }
    ]
  };

  blocks.registerBlockType('minikids-pages/site', {
    apiVersion: 3,
    title: __('MiniKids · Página', 'minikids-pages'),
    description: __('Vista y contenido de una página pública de MiniKids.', 'minikids-pages'),
    icon: 'store',
    category: 'widgets',
    attributes: {
      page: { type: 'string', default: 'home' },
      copy: { type: 'object', default: {} }
    },
    supports: { html: false, reusable: false },

    edit: function (props) {
      const attrs = props.attributes;
      const copy = attrs.copy || {};
      const fields = FIELDS[attrs.page] || FIELDS.home;

      function setCopy(key, value) {
        const next = Object.assign({}, copy);
        if (value === '' || value === undefined || value === null) {
          delete next[key];
        } else {
          next[key] = value;
        }
        props.setAttributes({ copy: next });
      }

      const controls = fields.map(function (f) {
        const Cmp = f.long ? TextareaControl : TextControl;
        return el(Cmp, {
          key: f.key,
          label: f.label,
          help: f.help,
          placeholder: f.def,
          value: copy[f.key] || '',
          onChange: function (v) { setCopy(f.key, v); }
        });
      });

      const inspector = el(InspectorControls, { key: 'inspector' },
        el(PanelBody, { title: __('Textos de la página', 'minikids-pages'), initialOpen: true },
          el('p', { key: 'hint', style: { fontSize: '12px', color: '#757575', marginTop: 0 } },
            __('Deja un campo vacío para usar el texto por defecto. Los productos, filtros y el carrito siguen siendo dinámicos.', 'minikids-pages')),
          controls,
          el(Button, {
            key: 'reset',
            variant: 'secondary',
            isDestructive: true,
            onClick: function () { props.setAttributes({ copy: {} }); }
          }, __('Restaurar textos por defecto', 'minikids-pages'))
        )
      );

      const preview = el('div', { className: 'mkp-editor-preview', key: 'preview' }, [
        el('div', { className: 'mkp-editor-toolbar', key: 'toolbar' }, [
          el('div', { className: 'mkp-editor-heading', key: 'heading' }, [
            el('span', { className: 'mkp-editor-icon', key: 'icon', 'aria-hidden': true }, '✦'),
            el('div', { key: 'copy' }, [
              el('strong', { key: 'title' }, 'MiniKids · diseño de página'),
              el('span', { key: 'caption' }, 'Vista previa de la página pública')
            ])
          ]),
          el(SelectControl, {
            key: 'select', label: __('Página', 'minikids-pages'), value: attrs.page,
            options: [
              { label: 'Inicio', value: 'home' },
              { label: 'Tienda', value: 'shop' },
              { label: 'Nosotros', value: 'about' },
              { label: 'Contacto', value: 'contact' }
            ],
            onChange: function (page) { props.setAttributes({ page: page }); }
          })
        ]),
        el('div', { className: 'mkp-editor-canvas', key: 'canvas' },
          el('div', { className: 'mkp-editor-browser' }, [
            el('div', { className: 'mkp-editor-browser-bar', key: 'bar' }, [
              el('span', { className: 'mkp-editor-dots', key: 'dots', 'aria-hidden': true }, '● ● ●'),
              el('span', { className: 'mkp-editor-url', key: 'url' }, 'minikids.pe/' + (attrs.page === 'home' ? '' : attrs.page))
            ]),
            el('div', { className: 'mkp-editor-viewport', key: 'viewport' },
              el('div', { className: 'mkp-editor-site-scale' },
                el(ServerSideRender, { block: 'minikids-pages/site', attributes: attrs })
              )
            )
          ])
        )
      ]);

      return el(Fragment, {}, [inspector, preview]);
    },

    save: function () { return null; }
  });
})(window.wp.blocks, window.wp.element, window.wp.components, window.wp.blockEditor, window.wp.serverSideRender, window.wp.i18n);
