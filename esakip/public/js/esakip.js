try { if (sessionStorage.getItem('seen')) document.documentElement.classList.add('seen'); } catch (e) {}
document.addEventListener('alpine:init', () => {
  Alpine.data('drawer', (name, action, method, defaults) => ({
    open: false, action, method, form: JSON.parse(JSON.stringify(defaults || {})), defaults, title: null,
    init() {
      window.addEventListener('open-drawer', (e) => {
        if (e.detail.name !== name) return;
        this.form = Object.assign(JSON.parse(JSON.stringify(this.defaults || {})), e.detail.data || {});
        this.action = e.detail.action || action;
        this.method = e.detail.method || method;
        this.title = e.detail.title || null;
        this.open = true;
        this.$nextTick(() => window.lucide && lucide.createIcons());
      });
    },
  }));

  Alpine.data('countUp', (to, dec = 0) => ({
    v: 0,
    init() {
      const start = performance.now(), dur = 1000;
      const step = (t) => {
        const p = Math.min((t - start) / dur, 1), e = 1 - Math.pow(1 - p, 4);
        this.v = (to * e).toLocaleString('id-ID', { minimumFractionDigits: dec, maximumFractionDigits: dec });
        if (p < 1) requestAnimationFrame(step);
      };
      setTimeout(() => requestAnimationFrame(step), 80);
    },
  }));
});

document.addEventListener('DOMContentLoaded', () => {
  window.lucide && lucide.createIcons();
  try { sessionStorage.setItem('seen', '1'); } catch (e) {}

  // confirm dialogs
  document.addEventListener('submit', (e) => {
    const f = e.target;
    if (f.dataset.confirm && !f.dataset.confirmed) {
      e.preventDefault();
      window.dispatchEvent(new CustomEvent('ask-confirm', { detail: { msg: f.dataset.confirm, form: f } }));
      return;
    }
    const btn = f.querySelector('[type=submit]');
    if (btn && !f.target) { btn.disabled = true; btn.classList.add('opacity-70'); }
  }, true);

  // page transition + top bar
  const bar = document.getElementById('topbar');
  document.addEventListener('click', (e) => {
    const a = e.target.closest('a[href]');
    if (!a || a.target || a.hasAttribute('download') || e.metaKey || e.ctrlKey) return;
    const href = a.getAttribute('href');
    if (!href || href.startsWith('#') || href.startsWith('javascript') || a.dataset.noTransition !== undefined) return;
    if (a.host !== location.host) return;
    if (bar) { bar.style.opacity = 1; bar.style.width = '70%'; }
    document.querySelector('main')?.classList.add('leaving');
  });
  window.addEventListener('pageshow', () => {
    document.querySelector('main')?.classList.remove('leaving');
    if (bar) { bar.style.width = '100%'; setTimeout(() => { bar.style.opacity = 0; bar.style.width = 0; }, 300); }
  });
});
