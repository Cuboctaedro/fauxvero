import Alpine from 'alpinejs';
import './cart';

window.Alpine = Alpine;

Alpine.start();

/*
 * Disclosure toggles (main menu, language dropdown, ...).
 *
 * A button with `data-disclosure` shows/hides the element named by its
 * `aria-controls`. Optional attributes on the button:
 *   data-disclosure-open-class  extra classes added to the panel while open
 *   data-label-open / data-label-closed  aria-label for each state
 * Escape or a click outside the button and panel closes it.
 */
document.querySelectorAll('[data-disclosure]').forEach((button) => {
  const panel = document.getElementById(button.getAttribute('aria-controls'));

  if (!panel) return;

  const openClasses = (button.dataset.disclosureOpenClass || '').split(' ').filter(Boolean);
  const isOpen = () => button.getAttribute('aria-expanded') === 'true';

  const setOpen = (open) => {
    button.setAttribute('aria-expanded', String(open));

    const label = open ? button.dataset.labelOpen : button.dataset.labelClosed;
    if (label) button.setAttribute('aria-label', label);

    panel.classList.toggle('hidden', !open);
    openClasses.forEach((cls) => panel.classList.toggle(cls, open));
  };

  button.addEventListener('click', () => setOpen(!isOpen()));

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && isOpen()) {
      setOpen(false);
      button.focus();
    }
  });

  document.addEventListener('click', (event) => {
    if (isOpen() && !button.contains(event.target) && !panel.contains(event.target)) {
      setOpen(false);
    }
  });
});
