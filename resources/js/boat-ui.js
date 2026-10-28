/*!
 * Wocody Boat UI — behaviour for server-rendered components. No dependencies.
 * Public API: window.BoatUI.{toast, modal, views, refresh}.
 */
(() => {
	'use strict';

	if (window.BoatUI) return;

	const config = window.BoatUIConfig || {};
	const text = Object.assign({
		search: 'Quick search…',
		searchPlaceholder: 'Search…',
		noResults: 'No results found.',
		close: 'Close',
		dismiss: 'Dismiss',
		showMore: 'Show more',
		showLess: 'Show less',
		unsaved: 'You have unsaved changes. Leave this page?',
	}, config.strings || {});

	const $ = (selector, scope = document) => scope.querySelector(selector);
	const $$ = (selector, scope = document) => Array.from(scope.querySelectorAll(selector));
	// Phosphor Icons (regular, MIT).
	const icons = {
		success: '<path d="M173.66,98.34a8,8,0,0,1,0,11.32l-56,56a8,8,0,0,1-11.32,0l-24-24a8,8,0,0,1,11.32-11.32L112,148.69l50.34-50.35A8,8,0,0,1,173.66,98.34ZM232,128A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"/>',
		error: '<path d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm0,192a88,88,0,1,1,88-88A88.1,88.1,0,0,1,128,216Zm-8-80V80a8,8,0,0,1,16,0v56a8,8,0,0,1-16,0Zm20,36a12,12,0,1,1-12-12A12,12,0,0,1,140,172Z"/>',
		warning: '<path d="M236.8,188.09,149.35,36.22h0a24.76,24.76,0,0,0-42.7,0L19.2,188.09a23.51,23.51,0,0,0,0,23.72A24.35,24.35,0,0,0,40.55,224h174.9a24.35,24.35,0,0,0,21.33-12.19A23.51,23.51,0,0,0,236.8,188.09ZM222.93,203.8a8.5,8.5,0,0,1-7.48,4.2H40.55a8.5,8.5,0,0,1-7.48-4.2,7.59,7.59,0,0,1,0-7.72L120.52,44.21a8.75,8.75,0,0,1,15,0l87.45,151.87A7.59,7.59,0,0,1,222.93,203.8ZM120,144V104a8,8,0,0,1,16,0v40a8,8,0,0,1-16,0Zm20,36a12,12,0,1,1-12-12A12,12,0,0,1,140,180Z"/>',
		info: '<path d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm0,192a88,88,0,1,1,88-88A88.1,88.1,0,0,1,128,216Zm16-40a8,8,0,0,1-8,8,16,16,0,0,1-16-16V128a8,8,0,0,1,0-16,16,16,0,0,1,16,16v40A8,8,0,0,1,144,176ZM112,84a12,12,0,1,1,12,12A12,12,0,0,1,112,84Z"/>',
		x: '<path d="M205.66,194.34a8,8,0,0,1-11.32,11.32L128,139.31,61.66,205.66a8,8,0,0,1-11.32-11.32L116.69,128,50.34,61.66A8,8,0,0,1,61.66,50.34L128,116.69l66.34-66.35a8,8,0,0,1,11.32,11.32L139.31,128Z"/>',
	};
	const svg = (name, className) => `<svg class="${className}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true">${icons[name] || icons.info}</svg>`;

	/* Modal ------------------------------------------------------------- */

	const modal = {
		open(target, opener) {
			const dialog = typeof target === 'string' ? document.getElementById(target) : target;
			if (!dialog || dialog.open) return dialog;
			dialog._buiOpener = opener || document.activeElement;
			if (typeof dialog.showModal === 'function') dialog.showModal();
			else dialog.setAttribute('open', '');
			dialog.dispatchEvent(new CustomEvent('bui:open'));
			return dialog;
		},
		close(target) {
			const dialog = typeof target === 'string' ? document.getElementById(target) : target;
			if (!dialog || !dialog.open) return;
			if (typeof dialog.close === 'function') dialog.close();
			else dialog.removeAttribute('open');
		},
	};

	document.addEventListener('click', (event) => {
		const opener = event.target.closest('[data-bui-modal-open]');
		if (opener) {
			event.preventDefault();
			modal.open(opener.getAttribute('data-bui-modal-open'), opener);
			return;
		}
		const closer = event.target.closest('[data-bui-modal-close]');
		if (closer) {
			modal.close(closer.closest('dialog'));
			return;
		}
		// A click on the backdrop lands on the dialog element itself.
		if (event.target instanceof HTMLDialogElement && event.target.open && (event.target.classList.contains('bui-modal') || event.target.classList.contains('bui-mobile-navigation__dialog'))) {
			const box = (event.target.querySelector('.bui-modal__panel, .bui-mobile-navigation__panel') || event.target).getBoundingClientRect();
			if (event.clientX < box.left || event.clientX > box.right || event.clientY < box.top || event.clientY > box.bottom) modal.close(event.target);
		}
		const dismiss = event.target.closest('[data-bui-dismiss]');
		if (dismiss) {
			const box = dismiss.closest('.bui-alert, .bui-banner, .bui-notification');
			if (box) {
				box.dispatchEvent(new CustomEvent('bui:dismiss', { bubbles: true }));
				box.remove();
			}
		}
		const confirmable = event.target.closest('[data-bui-confirm]');
		if (confirmable && !window.confirm(confirmable.getAttribute('data-bui-confirm'))) {
			event.preventDefault();
			event.stopImmediatePropagation();
		}
	}, true);

	document.addEventListener('close', (event) => {
		const dialog = event.target;
		if (dialog instanceof HTMLDialogElement && dialog._buiOpener && typeof dialog._buiOpener.focus === 'function') {
			dialog._buiOpener.focus();
			dialog._buiOpener = null;
		}
	}, true);

	/* Toasts ------------------------------------------------------------ */

	function toast(options = {}) {
		const region = $('[data-bui-notifications]');
		if (!region) return null;
		const variant = options.variant || 'success';
		const item = document.createElement('div');
		item.className = `bui-notification bui-notification--${variant}${options.size === 'large' ? ' bui-notification--large' : ''}`;
		item.setAttribute('role', variant === 'error' ? 'alert' : 'status');
		item.innerHTML = `${svg(variant, 'bui-notification__icon')}<div class="bui-notification__body"><p class="bui-notification__title"></p></div><button type="button" class="bui-notification__dismiss" data-bui-dismiss><span class="bui-sr-only"></span>${svg('x', '')}</button>`;
		$('.bui-notification__title', item).textContent = options.title || '';
		$('.bui-notification__dismiss .bui-sr-only', item).textContent = text.dismiss;
		if (options.description) {
			const description = document.createElement('p');
			description.className = 'bui-notification__description';
			description.textContent = options.description;
			$('.bui-notification__body', item).append(description);
		}
		region.append(item);
		const duration = options.duration === undefined ? 5000 : options.duration;
		if (duration > 0) {
			setTimeout(() => {
				item.classList.add('bui-is-leaving');
				setTimeout(() => item.remove(), 160);
			}, duration);
		}
		return item;
	}

	/* Hash views (one [data-bui-view] visible at a time) ---------------- */

	const views = {
		root: null,
		current: '',
		list() {
			return this.root ? $$('[data-bui-view]', this.root) : [];
		},
		show(name, options = {}) {
			const all = this.list();
			if (!all.length) return;
			const target = all.find((view) => view.getAttribute('data-bui-view') === name) || all[0];
			const selected = target.getAttribute('data-bui-view');
			all.forEach((view) => { view.hidden = view !== target; });
			$$('[data-bui-nav]', this.root).forEach((link) => {
				const active = link.getAttribute('data-bui-nav') === selected;
				link.classList.toggle('bui-is-active', active);
				if (active) link.setAttribute('aria-current', 'page');
				else link.removeAttribute('aria-current');
			});
			const changed = this.current !== selected;
			this.current = selected;
			if (options.updateHash && location.hash !== `#${selected}`) history.replaceState(null, '', `#${selected}`);
			if (changed) this.root.dispatchEvent(new CustomEvent('bui:view', { bubbles: true, detail: { view: selected } }));
			const mobile = document.getElementById('bui-mobile-navigation');
			if (mobile) modal.close(mobile);
		},
		sync() {
			this.show(decodeURIComponent(location.hash.replace(/^#\/?/, '')));
			// A hash matching an element id makes the browser jump to it; views always start at the top.
			if (document.getElementById(this.current)) window.scrollTo(0, 0);
		},
	};

	/* Quick search ------------------------------------------------------ */

	function initSearch(root) {
		const dialog = $('#bui-search', root);
		if (!dialog) return;
		const input = $('[data-bui-search-input]', dialog);
		const results = $('[data-bui-search-results]', dialog);
		const mac = /Mac|iPhone|iPad/.test(navigator.platform || navigator.userAgent);
		$$('[data-bui-search-shortcut]', root).forEach((kbd) => { kbd.textContent = mac ? '⌘K' : 'Ctrl K'; });
		// The desktop sidebar is the first navigation; the mobile copy repeats it.
		const index = $$('.bui-layout__sidebar .bui-nav__link', root).map((link) => {
			const group = link.closest('.bui-nav__group');
			const heading = group ? $('.bui-nav__label', group) : null;
			return { label: $('.bui-nav__text', link).textContent.trim(), group: heading ? heading.textContent.trim() : '', link };
		});
		let active = 0;

		function render() {
			const query = input.value.trim().toLowerCase();
			const matches = index.filter((entry) => !query || `${entry.label} ${entry.group}`.toLowerCase().includes(query));
			results.replaceChildren();
			active = Math.min(active, Math.max(0, matches.length - 1));
			if (!matches.length) {
				const empty = document.createElement('p');
				empty.className = 'bui-search-modal__empty';
				empty.textContent = text.noResults;
				results.append(empty);
				input.removeAttribute('aria-activedescendant');
				return;
			}
			matches.forEach((entry, position) => {
				const option = document.createElement('a');
				option.className = 'bui-search-modal__result';
				option.id = `bui-search-result-${position}`;
				option.href = entry.link.getAttribute('href');
				option.setAttribute('role', 'option');
				option.setAttribute('aria-selected', String(position === active));
				option.textContent = entry.label;
				if (entry.group) {
					const group = document.createElement('small');
					group.textContent = entry.group;
					option.append(group);
				}
				option.addEventListener('click', () => modal.close(dialog));
				results.append(option);
			});
			input.setAttribute('aria-activedescendant', `bui-search-result-${active}`);
		}

		input.addEventListener('input', () => { active = 0; render(); });
		input.addEventListener('keydown', (event) => {
			const options = $$('[role=option]', results);
			if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
				event.preventDefault();
				if (!options.length) return;
				active = (active + (event.key === 'ArrowDown' ? 1 : -1) + options.length) % options.length;
				render();
				$(`#bui-search-result-${active}`, results).scrollIntoView({ block: 'nearest' });
			} else if (event.key === 'Enter' && options[active]) {
				event.preventDefault();
				options[active].click();
			}
		});
		dialog.addEventListener('bui:open', () => {
			input.value = '';
			active = 0;
			render();
			input.focus();
		});
		$$('[data-bui-search-open]', root).forEach((button) => button.addEventListener('click', () => {
			const mobile = document.getElementById('bui-mobile-navigation');
			if (mobile) modal.close(mobile);
			modal.open(dialog, button);
		}));
		// Capture on window so the screen's search wins over the WordPress command palette (also ⌘K).
		window.addEventListener('keydown', (event) => {
			if ((event.metaKey || event.ctrlKey) && !event.altKey && !event.shiftKey && event.key.toLowerCase() === 'k') {
				event.preventDefault();
				event.stopImmediatePropagation();
				if (dialog.open) modal.close(dialog);
				else modal.open(dialog);
			}
		}, true);
	}

	/* Forms: dirty footer, file inputs ---------------------------------- */

	function snapshot(form) {
		return $$('input, select, textarea', form).map((field) => {
			if (field.type === 'checkbox' || field.type === 'radio') return field.checked;
			if (field.type === 'file') return field.files.length;
			if (field.multiple) return $$('option', field).map((option) => option.selected).join();
			return field.value;
		}).join('\u0000');
	}

	function initDirty(scope) {
		$$('[data-bui-dirty-footer]', scope).forEach((footer) => {
			const form = footer.closest('form') || $('form', footer.closest('.bui-paper') || scope);
			if (!form || form._buiDirty) return;
			form._buiDirty = true;
			let initial = snapshot(form);
			const update = () => {
				const dirty = snapshot(form) !== initial;
				footer.classList.toggle('bui-is-dirty', dirty);
				form.classList.toggle('bui-is-dirty', dirty);
			};
			form.addEventListener('input', update);
			form.addEventListener('change', update);
			form.addEventListener('reset', () => setTimeout(update));
			form.addEventListener('submit', () => {
				if (form.hasAttribute('data-bui-keep-dirty')) return;
				initial = snapshot(form);
				setTimeout(update);
			});
			form.addEventListener('bui:saved', () => { initial = snapshot(form); update(); });
			if (form.hasAttribute('data-bui-warn-unsaved')) {
				window.addEventListener('beforeunload', (event) => {
					if (form.classList.contains('bui-is-dirty')) {
						event.preventDefault();
						event.returnValue = text.unsaved;
					}
				});
			}
		});
	}

	function initFileInputs(scope) {
		$$('[data-bui-file-input]', scope).forEach((zone) => {
			if (zone._buiFile) return;
			zone._buiFile = true;
			const input = $('input[type=file]', zone);
			const name = $('[data-bui-file-name]', zone);
			const show = () => {
				if (!name) return;
				const files = Array.from(input.files || []);
				name.hidden = !files.length;
				name.textContent = files.map((file) => file.name).join(', ');
			};
			input.addEventListener('change', show);
			['dragenter', 'dragover'].forEach((type) => zone.addEventListener(type, (event) => {
				if (input.disabled) return;
				event.preventDefault();
				zone.classList.add('bui-is-drag-over');
			}));
			['dragleave', 'dragend'].forEach((type) => zone.addEventListener(type, (event) => {
				if (!zone.contains(event.relatedTarget)) zone.classList.remove('bui-is-drag-over');
			}));
			zone.addEventListener('drop', (event) => {
				zone.classList.remove('bui-is-drag-over');
				if (input.disabled || !event.dataTransfer || !event.dataTransfer.files.length) return;
				event.preventDefault();
				input.files = event.dataTransfer.files;
				input.dispatchEvent(new Event('change', { bubbles: true }));
				zone.dispatchEvent(new CustomEvent('bui:drop', { bubbles: true, detail: { files: input.files } }));
			});
		});
	}

	function initLimiters(scope) {
		$$('[data-bui-limiter]', scope).forEach((button) => {
			if (button._buiLimiter) return;
			button._buiLimiter = true;
			button.addEventListener('click', () => {
				const target = document.getElementById(button.getAttribute('aria-controls'));
				const open = button.getAttribute('aria-expanded') !== 'true';
				button.setAttribute('aria-expanded', String(open));
				if (target) target.hidden = !open;
				$('[data-bui-limiter-label]', button).textContent = open ? text.showLess : text.showMore;
			});
		});
	}

	/* Boot -------------------------------------------------------------- */

	function refresh(scope = document) {
		initDirty(scope);
		initFileInputs(scope);
		initLimiters(scope);
	}

	function boot() {
		$$('.bui-root').forEach((root) => {
			if (root._buiBooted) return;
			root._buiBooted = true;
			initSearch(root);
			if (root.hasAttribute('data-bui-router') && !views.root) {
				views.root = root;
				window.addEventListener('hashchange', () => views.sync());
				views.sync();
			}
		});
		refresh();
		// Mirror WordPress' admin notices position: they arrive in .bui-wp-notices.
		document.documentElement.classList.add('bui-js');
	}

	window.BoatUI = { toast, modal, views, refresh, version: '1.0.0' };
	if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
	else boot();
})();
