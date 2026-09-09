(function (blocks, element, components, serverSideRender, i18n) {
  const el = element.createElement;
  const SelectControl = components.SelectControl;
  const ServerSideRender = serverSideRender;
  const __ = i18n.__;

  blocks.registerBlockType('minikids-pages/site', {
    apiVersion: 3,
    title: __('MiniKids · Página', 'minikids-pages'),
    description: __('Vista y contenido de una página pública de MiniKids.', 'minikids-pages'),
    icon: 'store',
    category: 'widgets',
    attributes: { page: { type: 'string', default: 'home' } },
    supports: { html: false, reusable: false },
    edit: function (props) {
      return el('div', { className: 'mkp-editor-preview' }, [
        el('div', { className: 'mkp-editor-toolbar', key: 'toolbar' }, [
          el('div', { className: 'mkp-editor-heading', key: 'heading' }, [
            el('span', { className: 'mkp-editor-icon', key: 'icon', 'aria-hidden': true }, '✦'),
            el('div', { key: 'copy' }, [
              el('strong', { key: 'title' }, 'MiniKids · diseño de página'),
              el('span', { key: 'caption' }, 'Vista previa de la página pública')
            ])
          ]),
          el(SelectControl, {
            key: 'select', label: __('Página', 'minikids-pages'), value: props.attributes.page,
            options: [
              { label: 'Inicio', value: 'home' },
              { label: 'Tienda', value: 'shop' },
              { label: 'Nosotros', value: 'about' },
              { label: 'Contacto', value: 'contact' }
            ],
            onChange: function (page) { props.setAttributes({ page: page }); }
          })
        ]),
        el('div', { className: 'mkp-editor-canvas', key: 'preview' },
          el('div', { className: 'mkp-editor-browser' }, [
            el('div', { className: 'mkp-editor-browser-bar', key: 'bar' }, [
              el('span', { className: 'mkp-editor-dots', key: 'dots', 'aria-hidden': true }, '● ● ●'),
              el('span', { className: 'mkp-editor-url', key: 'url' }, 'minikids.pe/' + (props.attributes.page === 'home' ? '' : props.attributes.page))
            ]),
            el('div', { className: 'mkp-editor-viewport', key: 'viewport' },
              el('div', { className: 'mkp-editor-site-scale' },
                el(ServerSideRender, { block: 'minikids-pages/site', attributes: props.attributes })
              )
            )
          ])
        )
      ]);
    },
    save: function () { return null; }
  });
})(window.wp.blocks, window.wp.element, window.wp.components, window.wp.serverSideRender, window.wp.i18n);
