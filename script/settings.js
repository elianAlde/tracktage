let categories = [];
let catModal;

function loadCategories() {
	App.api('categories/list', {}, 'GET').done(list => {
		categories = list;
		const $l = $('#categoriesList').empty();
		if (!list.length) $l.html('<div class="empty-state">Nessuna sezione</div>');
		list.forEach((c, i) => $l.append(`
			<div class="list-row" data-id="${c.id}">
				<span class="cat-icon" style="background:${c.color}1a;color:${c.color}"><i class="bi ${App.esc(c.icon)}"></i></span>
				<div class="flex-grow-1 min-w-0">
					<div class="fw-medium text-truncate">${App.esc(c.name)}</div>
					<div class="meta">${c.kind === 'market' ? 'Quotazione di mercato' : 'Valore manuale'}${c.is_liability ? ' · passività' : ''} · ${c.count} voci</div>
				</div>
				<button class="btn btn-sm btn-soft btn-move" data-dir="-1" ${i === 0 ? 'disabled' : ''} aria-label="Su"><i class="bi bi-chevron-up" style="font-size:14px"></i></button>
				<button class="btn btn-sm btn-soft btn-move" data-dir="1" ${i === list.length - 1 ? 'disabled' : ''} aria-label="Giù"><i class="bi bi-chevron-down" style="font-size:14px"></i></button>
			</div>`));
	}).fail(msg => App.toast(msg, 'danger'));
}

function setColor(color) {
	$('#catColor').val(color);
	$('#colorPicker .color-swatch').removeClass('active').filter(`[data-color="${color}"]`).addClass('active');
}

function openCategory(c = null) {
	const $f = $('#formCategory');
	$f[0].reset();
	$f.removeClass('was-validated');
	$('#catId').val(c?.id || '');
	$('#catName').val(c?.name || '');
	$('#catKind').val(c?.kind || 'manual');
	$('#catIcon').val(c?.icon || 'bi-wallet2');
	$('#catLiability').prop('checked', !!c?.is_liability);
	setColor(c?.color || '#0d6efd');
	$('#modalCategoryTitle').text(c ? 'Modifica sezione' : 'Nuova sezione');
	$('#btnDeleteCategory').toggleClass('d-none', !c);
	$('#kindWarning').text(c && c.count > 0 ? 'Attenzione: cambiando tipo, le voci esistenti andranno ricompilate.' : '');
	catModal.show();
}

$(function () {
	catModal = new bootstrap.Modal('#modalCategory');
	loadCategories();

	$('#btnAddCategory').on('click', e => { e.preventDefault(); openCategory(); });
	$('#categoriesList').on('click', '.list-row', function (e) {
		if ($(e.target).closest('.btn-move').length) return;
		openCategory(categories.find(c => c.id == $(this).data('id')));
	});
	$('#categoriesList').on('click', '.btn-move', function () {
		const id = $(this).closest('.list-row').data('id');
		const i = categories.findIndex(c => c.id == id);
		const j = i + Number($(this).data('dir'));
		[categories[i], categories[j]] = [categories[j], categories[i]];
		App.api('categories/reorder', { ids: categories.map(c => c.id) }).always(loadCategories);
	});
	$('#colorPicker').on('click', '.color-swatch', function () { setColor($(this).data('color')); });

	$('#formCategory').on('submit', function (e) {
		e.preventDefault();
		$(this).addClass('was-validated');
		if (!this.checkValidity()) return;
		App.api('categories/save', $(this).serialize())
			.done(() => { catModal.hide(); App.toast('Sezione salvata'); loadCategories(); })
			.fail(msg => App.toast(msg, 'danger'));
	});

	$('#btnDeleteCategory').on('click', function () {
		const c = categories.find(x => x.id == $('#catId').val());
		if (!confirm(`Eliminare la sezione "${c.name}"${c.count ? ' e le sue ' + c.count + ' voci' : ''}? L'operazione non è reversibile.`)) return;
		App.api('categories/delete', { id: c.id })
			.done(() => { catModal.hide(); App.toast('Sezione eliminata'); loadCategories(); })
			.fail(msg => App.toast(msg, 'danger'));
	});

	$('#formPassword').on('submit', function (e) {
		e.preventDefault();
		$(this).addClass('was-validated');
		if (!this.checkValidity()) return;
		App.api('auth/change_password', $(this).serialize())
			.done(() => { bootstrap.Modal.getInstance('#modalPassword').hide(); this.reset(); $(this).removeClass('was-validated'); App.toast('Password aggiornata', 'success'); })
			.fail(msg => App.toast(msg, 'danger'));
	});

	// Tema (preferenza salvata su questo dispositivo)
	let theme = 'auto';
	try { theme = localStorage.getItem('theme') || 'auto'; } catch (e) {}
	$(`#themeGroup input[value="${theme}"]`).prop('checked', true);
	$('#themeGroup').on('change', 'input', function () {
		try { localStorage.setItem('theme', this.value); } catch (e) {}
		window.applyTheme(this.value);
	});

	if (location.hash === '#sections') document.getElementById('sections').scrollIntoView();
});

/* ---------- Installazione PWA ---------- */
let deferredPrompt = null;
window.addEventListener('beforeinstallprompt', (e) => { e.preventDefault(); deferredPrompt = e; });

$(document).on('click', '#addToHomeBtn', function (e) {
	e.preventDefault();
	if (/iphone|ipad|ipod/i.test(navigator.userAgent)) {
		new bootstrap.Modal('#iosInstallModal').show();
	} else if (deferredPrompt) {
		deferredPrompt.prompt();
		deferredPrompt.userChoice.finally(() => deferredPrompt = null);
	} else {
		App.toast("Installazione non disponibile da questo browser.");
	}
});
