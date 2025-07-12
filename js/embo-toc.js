document.addEventListener('DOMContentLoaded', () => {
  class EmboTOC {
    constructor() {
      // Only for single posts
      if (!document.body.classList.contains('single-post')) return;

      this.article       = document.querySelector('article');
      this.existingAside = document.querySelector('.global-aside:not(.toc-aside)');
      this.artContent    = this.article?.querySelector('.entry-content, .article-content');
      if (!this.article || !this.existingAside || !this.artContent) return;

      this.stickyTop = 20; // px from top
      this._buildTOC();
      this._initResponsive();
    }

    // 1) Build the TOC and a mobile copy
    _buildTOC() {
      // Main TOC block
      this.tocBlock = document.createElement('div');
      this.tocBlock.className = 'toc-block';
      this.tocBlock.innerHTML = `
        <h3 class="menu-label">${EmboSettingsI18n.tabToc}</h3>
        <ul class="toc-list"></ul>
      `;
      const list = this.tocBlock.querySelector('.toc-list');

      // Loop through headings and add them to the list
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

      // Wrapper for desktop
      this.tocAside = document.createElement('div');
      this.tocAside.className = 'global-aside toc-aside';
      this.tocAside.appendChild(this.tocBlock);

      // Clone for mobile
      this.mobileWrapper = document.createElement('div');
      this.mobileWrapper.className = 'toc-inline';
      this.mobileWrapper.appendChild(this.tocBlock.cloneNode(true));
    }

    // 2) Initialize ScreenObserver for switching desktop ↔ mobile
    _initResponsive() {
      this._cleanup();  // in case of previous subscriptions
      this.screenObs = new ScreenObserver(1025);
      this.screenObs.onEnter(() => this._insertDesktop());
      this.screenObs.onLeave(() => this._insertMobile());
    }

    // Insert for desktop
    _insertDesktop() {
      this._cleanup();
      this.mobileWrapper.remove();
      this.existingAside.parentNode.insertBefore(this.tocAside, this.existingAside);
      this._updateTocHeight();
      this._bindSticky();
    }

    // Insert for mobile
    _insertMobile() {
      this._cleanup();
      this.tocAside.remove();
      const meta = this.article.querySelector('.article-meta');
      if (meta) meta.insertAdjacentElement('afterend', this.mobileWrapper);
    }

    // Remove all listeners and reset sticky styles
    _cleanup() {
      if (this._stickyHandler) {
        window.removeEventListener('scroll', this._stickyHandler);
        window.removeEventListener('resize', this._stickyHandler);
        window.removeEventListener('load', this._stickyHandler);
        this.article.querySelectorAll('img').forEach(img =>
          img.removeEventListener('load', this._stickyHandler)
        );
      }
      // reset style changes
      Object.assign(this.existingAside.style, {
        position: '', top: '', bottom: ''
      });
      if (this.tocAside) {
        Object.assign(this.tocAside.style, {
          position: '', top: '', bottom: ''
        });
      }
    }

    // 3) Subscribe to scroll/resize/load and image loading
    _bindSticky() {
      this._stickyHandler = this._updateSticky.bind(this);
      // compute once immediately
      this._updateSticky();
      window.addEventListener('scroll', this._stickyHandler);
      window.addEventListener('resize', this._stickyHandler);
      window.addEventListener('load', this._stickyHandler);
      this.article.querySelectorAll('img').forEach(img =>
        img.addEventListener('load', this._stickyHandler)
      );
    }

    // 4) Calculate where to stick
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

    // 5) Update the CSS variable with TOC height
    _updateTocHeight() {
      const h = this.tocAside.offsetHeight;
      document.documentElement.style.setProperty('--toc-height', `${h}px`);
    }
  }

  new EmboTOC();
});