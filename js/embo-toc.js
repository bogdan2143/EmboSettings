jQuery(document).ready(function($) {
    if (!$('body').hasClass('single-post')) return;

    const $article = $('article');
    const $existingAside = $('.global-aside').not('.toc-aside').first();
    if (!$article.length || !$existingAside.length) return;

    const $tocBlock = $('<div class="toc-block"><h3 class="menu-label">Навігація по сторінці</h3><ul class="toc-list"></ul></div>');

    $article.find('h1,h2,h3,h4,h5,h6').each(function() {
        const $h = $(this);
        let id = $h.attr('id');
        if (!id) {
            id = $h.text().toLowerCase().replace(/\s+/g, '-');
            $h.attr('id', id);
        }
        const tag = this.tagName.toLowerCase();
        $tocBlock.find('.toc-list').append(
            '<li class="toc-' + tag + '"><a href="#' + id + '">' + $h.text() + '</a></li>'
        );
    });

    // Обгортка- aside для десктопа
    const $tocAside = $('<div class="global-aside toc-aside"></div>').append($tocBlock);

    // Обгортка для мобілки (без aside)
    const $mobileTocWrapper = $('<div class="toc-inline"></div>').append($tocBlock.clone());

    // Вставити початковий стан
    if (window.innerWidth > 1024) {
        $existingAside.before($tocAside);
    } else {
        const $meta = $article.find('.article-meta');
        if ($meta.length) {
            $meta.after($mobileTocWrapper);
        }
    }

    // Обробник ресайзу
    let isDesktop = window.innerWidth > 1024;

    $(window).on('resize', function() {
        const nowDesktop = window.innerWidth > 1024;
        if (nowDesktop === isDesktop) return; // ничего не менялось

        // Видалити поточні версії
        $('.toc-aside, .toc-inline').remove();

        if (nowDesktop) {
            // Десктоп: вставити як aside
            $existingAside.before($tocAside);
        } else {
            // Мобільний: всередину статті
            const $meta = $article.find('.article-meta');
            if ($meta.length) {
                $meta.after($mobileTocWrapper);
            }
        }

        isDesktop = nowDesktop;
    });
});