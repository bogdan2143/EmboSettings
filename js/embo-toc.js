jQuery(function($) {
  if (!$('body').hasClass('single-post')) return;

  const $article       = $('article');
  const $existingAside = $('.global-aside').not('.toc-aside').first();
  const $artContent    = $article.find('.entry-content, .article-content');
  if (!$article.length || !$existingAside.length || !$artContent.length) return;

  // Збираємо TOC
  const $tocBlock = $('<div class="toc-block"><h3 class="menu-label">Навігація по сторінці</h3><ul class="toc-list"></ul></div>');
  $article.find('h1,h2,h3,h4,h5,h6').each(function() {
    const $h = $(this);
    let id = $h.attr('id');
    if (!id) {
      id = $h.text().toLowerCase().trim().replace(/\s+/g, '-');
      $h.attr('id', id);
    }
    $tocBlock.find('.toc-list')
             .append(`<li class="toc-${this.tagName.toLowerCase()}"><a href="#${id}">${$h.text()}</a></li>`);
  });
  const $tocAside         = $('<div class="global-aside toc-aside"></div>').append($tocBlock);
  const $mobileTocWrapper = $('<div class="toc-inline"></div>').append($tocBlock.clone());

  // Поріг «десктопа»
  let isDesktop = window.matchMedia('(min-width:1025px)').matches;
  const stickyTop = 20; // px від верху

  // Перерахунок і оновлення sticky-стану
  function updateSticky() {
    const scrollY    = $(window).scrollTop();
    const artTop     = $artContent.offset().top;
    const artBottom  = artTop + $artContent.outerHeight();
    const firstH     = $tocAside.outerHeight();
    const marginB    = parseInt($tocAside.css('margin-bottom'), 10) || 0;
    // точка, після якої перший aside відлипне
    const breakPoint = artBottom - stickyTop - firstH - marginB;

    if (scrollY < breakPoint) {
      // перший прилипає зверху, другий під першим
      $tocAside.css({ position: 'sticky', top: stickyTop + 'px', bottom: 'auto' });
      $existingAside.css({
        position: 'sticky',
        top: (stickyTop + firstH + marginB) + 'px',
        bottom: 'auto'
      });
    } else {
      // перший static (залишається місці), другий прилипає зверху
      $tocAside.css({ position: 'static', top: 'auto', bottom: 'auto' });
      $existingAside.css({ position: 'sticky', top: stickyTop + 'px', bottom: 'auto' });
    }
  }

  // Прив'язка обробників scroll/resize та подій load
  function bindSticky() {
    updateSticky();
    $(window).on('scroll resize', updateSticky);
    // на випадок, якщо сторінка завантажена принижній позиції або підвантажилися зображення
    $(window).on('load', updateSticky);
    $article.find('img').on('load', updateSticky);
  }

  // Початкова вставка блоків
  if (isDesktop) {
    $existingAside.before($tocAside);
    bindSticky();
  } else {
    const $meta = $article.find('.article-meta').first();
    if ($meta.length) $meta.after($mobileTocWrapper);
  }

  // Перемикання desktop ↔ mobile
  $(window).on('resize', function() {
    const nowDesk = window.matchMedia('(min-width:1025px)').matches;
    if (nowDesk === isDesktop) return;

    if (nowDesk) {
      $('.toc-inline').remove();
      $existingAside.before($tocAside);
      bindSticky();
    } else {
      $(window).off('scroll resize load', updateSticky);
      $tocAside.remove();
      const $meta = $article.find('.article-meta').first();
      if ($meta.length) $meta.after($mobileTocWrapper);
      $existingAside.css({ position: 'static', top: 'auto' });
    }
    isDesktop = nowDesk;
  });
});