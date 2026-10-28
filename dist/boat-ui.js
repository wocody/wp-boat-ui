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
	const icons = {
		success: '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>',
		error: '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>',
		warning: '<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>',
		info: '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>',
		x: '<path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>',
	};
	const svg = (name, className) => `<svg class="${className}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">${icons[name] || icons.info}</svg>`;

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
				link.classList.toggle('bui-sidebar-navigation__item--active', active);
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
		const index = $$('.bui-layout__sidebar .bui-sidebar-navigation__link', root).map((link) => {
			const group = link.closest('.bui-sidebar-navigation__collapsible');
			const summary = group ? $('summary', group) : null;
			return { label: link.textContent.trim(), group: summary ? summary.textContent.trim() : '', link };
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

	window.BoatUI = { toast, modal, views, refresh, version: '1.0.1' };
	if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
	else boot();
})();
