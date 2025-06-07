// File: /src/js/embo-toc.js

document.addEventListener('DOMContentLoaded', () => {
  class EmboTOC {
    constructor() {
      // Лише для одиночних постів
      if (!document.body.classList.contains('single-post')) return;

      this.article       = document.querySelector('article');
      this.existingAside = document.querySelector('.global-aside:not(.toc-aside)');
      this.artContent    = this.article?.querySelector('.entry-content, .article-content');
      if (!this.article || !this.existingAside || !this.artContent) return;

      this.stickyTop = 20; // px від верху
      this._buildTOC();
      this._initResponsive();
    }

    // 1) Формуємо TOC та мобільну копію
    _buildTOC() {
      // Основний блок TOC
      this.tocBlock = document.createElement('div');
      this.tocBlock.className = 'toc-block';
      this.tocBlock.innerHTML = `
        <h3 class="menu-label">Навігація по сторінці</h3>
        <ul class="toc-list"></ul>
      `;
      const list = this.tocBlock.querySelector('.toc-list');

      // Перебираємо заголовки та додаємо їх до списку
      this.article.querySelectorAll('h1,h2,h3,h4,h5,h6').forEach(el => {
        let id = el.id;
        if (!id) {
          id = el.textContent.trim().toLowerCase().replace(/\s+/g, '-');
          el.id = id;
        }
        const li = document.createElement('li');
        li.className = `toc-${el.tagName.toLowerCase()}`;
        li.innerHTML = `<a href="#${id}">${el.textContent}</a>`;
        list.appendChild(li);
      });

      // Обгортка для десктопа
      this.tocAside = document.createElement('div');
      this.tocAside.className = 'global-aside toc-aside';
      this.tocAside.appendChild(this.tocBlock);

      // Клон для мобільних
      this.mobileWrapper = document.createElement('div');
      this.mobileWrapper.className = 'toc-inline';
      this.mobileWrapper.appendChild(this.tocBlock.cloneNode(true));
    }

    // 2) Ініціалізуємо ScreenObserver для перемикання desktop ↔ mobile
    _initResponsive() {
      this._cleanup();  // на випадок попередніх підписок
      this.screenObs = new ScreenObserver(1025);
      this.screenObs.onEnter(() => this._insertDesktop());
      this.screenObs.onLeave(() => this._insertMobile());
    }

    // Вставка для десктопа
    _insertDesktop() {
      this._cleanup();
      this.mobileWrapper.remove();
      this.existingAside.parentNode.insertBefore(this.tocAside, this.existingAside);
      this._updateTocHeight();
      this._bindSticky();
    }

    // Вставка для мобільних
    _insertMobile() {
      this._cleanup();
      this.tocAside.remove();
      const meta = this.article.querySelector('.article-meta');
      if (meta) meta.insertAdjacentElement('afterend', this.mobileWrapper);
    }

    // Прибираємо всі слухачі та скидаємо стилі «прилипання»
    _cleanup() {
      if (this._stickyHandler) {
        window.removeEventListener('scroll', this._stickyHandler);
        window.removeEventListener('resize', this._stickyHandler);
        window.removeEventListener('load', this._stickyHandler);
        this.article.querySelectorAll('img').forEach(img =>
          img.removeEventListener('load', this._stickyHandler)
        );
      }
      // скидання стилевих змін
      Object.assign(this.existingAside.style, {
        position: '', top: '', bottom: ''
      });
      if (this.tocAside) {
        Object.assign(this.tocAside.style, {
          position: '', top: '', bottom: ''
        });
      }
    }

    // 3) Підписуємося на scroll/resize/load і завантаження зображень
    _bindSticky() {
      this._stickyHandler = this._updateSticky.bind(this);
      // відразу обчислюємо один раз
      this._updateSticky();
      window.addEventListener('scroll', this._stickyHandler);
      window.addEventListener('resize', this._stickyHandler);
      window.addEventListener('load', this._stickyHandler);
      this.article.querySelectorAll('img').forEach(img =>
        img.addEventListener('load', this._stickyHandler)
      );
    }

    // 4) Обчислюємо, де «прилипнути»
    _updateSticky() {
      const scrollY = window.scrollY || window.pageYOffset;
      const artRect = this.artContent.getBoundingClientRect();
      const artTop = artRect.top + scrollY;
      const artBottom = artTop + this.artContent.offsetHeight;
      const firstH = this.tocAside.offsetHeight;
      const marginB = parseInt(getComputedStyle(this.tocAside).marginBottom, 10) || 0;
      const breakPoint = artBottom - this.stickyTop - firstH - marginB;

      if (scrollY < breakPoint) {
        Object.assign(this.tocAside.style, {
          position: 'sticky',
          top: `${this.stickyTop}px`,
          bottom: 'auto'
        });
        Object.assign(this.existingAside.style, {
          position: 'sticky',
          top: `${this.stickyTop + firstH + marginB}px`,
          bottom: 'auto'
        });
      } else {
        Object.assign(this.tocAside.style, {
          position: 'static',
          top: 'auto',
          bottom: 'auto'
        });
        Object.assign(this.existingAside.style, {
          position: 'sticky',
          top: `${this.stickyTop}px`,
          bottom: 'auto'
        });
      }
    }

    // 5) Оновлюємо CSS-змінну з висотою TOC
    _updateTocHeight() {
      const h = this.tocAside.offsetHeight;
      document.documentElement.style.setProperty('--toc-height', `${h}px`);
    }
  }

  new EmboTOC();
});