    </div>
  </div>
</div>
</body>
<script>
  (() => {
    const toggle = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('#main-sidebar');
    const closeTargets = document.querySelectorAll('[data-sidebar-close], .sidebar a');
    if (!toggle || !sidebar) return;
    const setOpen = (open) => {
      document.body.classList.toggle('sidebar-open', open);
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    };
    toggle.addEventListener('click', () => setOpen(!document.body.classList.contains('sidebar-open')));
    closeTargets.forEach((target) => target.addEventListener('click', () => setOpen(false)));
  })();
</script>
</html>
