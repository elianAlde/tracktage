const isMarket = CATEGORY.kind === 'market';
let assets = [];
let modal;
let selectedQuote = null;

function load(refresh = false) {
	const $icon = $('#btnRefresh i').addClass('spin');
	App.api('assets/list', { category_id: CATEGORY.id, refresh: refresh ? 1 : 0 }, 'GET')
		.done(render)
		.fail(msg => App.toast(msg, 'danger'))
		.always(() => $icon.removeClass('spin'));
}

function render(data) {
	assets = data.assets;
	$('#categoryTotal').text(App.money(data.category.total));

	if (isMarket) {
		const withCost = assets.filter(a => a.gain !== null);
		if (withCost.length) {
			const gain = withCost.reduce((s, a) => s + a.gain, 0);
			const cost = withCost.reduce((s, a) => s + a.cost, 0);
			$('#categoryGain').html(`<span class="${App.signClass(gain)}">${gain > 0 ? '+' : ''}${App.money(gain)} (${App.pct(cost ? gain / cost * 100 : 0)})</span> <span class="text-muted">rispetto al carico</span>`);
		} else {
			$('#categoryGain').empty();
		}
		const times = assets.filter(a => a.quote).map(a => a.quote.fetched_at);
		$('#pricesUpdated').text(times.length ? 'Quotazioni aggiornate ' + App.timeAgo(Math.min(...times)) : '');
	}

	const $list = $('#assetsList').empty();
	if (!assets.length) {
		$list.html(`<div class="empty-state"><i class="bi bi-plus-circle"></i>Nessuna voce.<br>Tocca <strong>+</strong> per aggiungerne una.</div>`);
		return;
	}

	assets.forEach(a => {
		let meta = '', right = '';
		if (isMarket) {
			const q = a.quote;
			meta = `${App.esc(a.ticker)} · ${App.number(a.quantity)} quote`;
			if (q) meta += ` · ${App.number(q.price, 2)} ${App.esc(q.currency)}`;
			right = a.price_error
				? '<div class="meta text-warning"><i class="bi bi-exclamation-triangle" style="font-size:12px"></i> prezzo n/d</div>'
				: (a.gain !== null
					? `<div class="meta ${App.signClass(a.gain)}">${App.pct(a.gain_pct)}</div>`
					: (q && q.change_pct !== null ? `<div class="meta ${App.signClass(q.change_pct)}">${App.pct(q.change_pct)} oggi</div>` : ''));
		} else {
			const updated = new Date(a.updated_at.replace(' ', 'T') + 'Z').toLocaleDateString('it-IT');
			meta = a.stale
				? `<span class="text-warning-emphasis"><i class="bi bi-clock-history" style="font-size:11px"></i> Da aggiornare · fermo dal ${updated}</span>`
				: (a.notes ? App.esc(a.notes) : 'Aggiornato il ' + updated);
		}
		$list.append(`
			<div class="list-row" data-id="${a.id}">
				<div class="flex-grow-1 min-w-0">
					<div class="fw-medium text-truncate">${App.esc(a.name)}</div>
					<div class="meta text-truncate">${meta}</div>
				</div>
				<div class="text-end">
					<div class="amount-sm">${App.money(a.current_value)}</div>
					${right}
				</div>
			</div>`);
	});
}

function openModal(asset = null) {
	const $f = $('#formAsset');
	$f[0].reset();
	$f.removeClass('was-validated');
	$('#searchResults').addClass('d-none');
	$('#previewValue').addClass('d-none');
	$('#tickerInfo').text('Cerca e seleziona lo strumento sulla borsa in cui lo hai comprato.');
	selectedQuote = asset?.quote || null;

	$('#assetId').val(asset?.id || '');
	$('#modalAssetTitle').text(asset ? 'Modifica' : 'Aggiungi');
	$('#btnDelete').toggleClass('d-none', !asset);
	$('#staleHint').toggleClass('d-none', !asset?.stale)
		.find('span').text(asset?.stale ? `Valore fermo da ${asset.months_since_update} mesi. Controllalo e salva: anche senza modifiche risulterà aggiornato a oggi.` : '');

	if (asset) {
		$('#name').val(asset.name);
		$('#notes').val(asset.notes || '');
		if (isMarket) {
			$('#ticker').val(asset.ticker);
			$('#quantity').val(asset.quantity !== null ? String(asset.quantity).replace('.', ',') : '');
			$('#avg_cost').val(asset.avg_cost !== null ? String(asset.avg_cost).replace('.', ',') : '');
			updatePreview();
		} else {
			$('#value').val(String(asset.value ?? '').replace('.', ','));
		}
	}
	modal.show();
}

// Stessa logica di inputNumber() lato server: "7.400" -> 7400, "1.234,5" -> 1234.5
function parseNum(v) {
	v = String(v || '').replace(/[\s€]/g, '');
	if (v.includes(',')) v = v.replace(/\./g, '').replace(',', '.');
	else if (/^-?\d{1,3}(\.\d{3})+$/.test(v)) v = v.replace(/\./g, '');
	const n = parseFloat(v);
	return isNaN(n) ? null : n;
}

function updatePreview() {
	const qty = parseNum($('#quantity').val());
	if (!selectedQuote || selectedQuote.price_base === null || qty === null) {
		$('#previewValue').addClass('d-none');
		return;
	}
	$('#previewValue').removeClass('d-none').html(
		`Prezzo attuale <strong>${App.money(selectedQuote.price_base)}</strong> · Valore <strong>${App.money(qty * selectedQuote.price_base)}</strong>`
	);
}

function selectSymbol(symbol, name) {
	$('#ticker').val(symbol);
	$('#searchResults').addClass('d-none');
	if (!$('#name').val() && name) $('#name').val(name);
	$('#tickerInfo').html('<span class="spinner-border spinner-border-sm"></span> Recupero quotazione…');
	App.api('market/quote', { symbol }, 'GET')
		.done(q => {
			selectedQuote = q;
			$('#tickerInfo').html(`<i class="bi bi-check-circle text-success" style="font-size:13px"></i> ${App.esc(q.name)} — ${App.number(q.price, 2)} ${App.esc(q.currency)}`);
			updatePreview();
			$('#quantity').trigger('focus');
		})
		.fail(msg => { selectedQuote = null; $('#tickerInfo').html(`<span class="text-danger">${App.esc(msg)}</span>`); updatePreview(); });
}

$(function () {
	modal = new bootstrap.Modal('#modalAsset');
	load();

	$('#btnAdd').on('click', e => { e.preventDefault(); openModal(); });
	$('#btnRefresh').on('click', e => { e.preventDefault(); load(true); });
	$('#assetsList').on('click', '.list-row', function () {
		openModal(assets.find(a => a.id == $(this).data('id')));
	});

	// Ricerca ticker con debounce
	let searchTimer, searchSeq = 0;
	$('#ticker').on('input', function () {
		clearTimeout(searchTimer);
		selectedQuote = null;
		updatePreview();
		const q = $(this).val().trim();
		if (q.length < 2) { $('#searchResults').addClass('d-none'); return; }
		searchTimer = setTimeout(() => {
			const seq = ++searchSeq;
			App.api('market/search', { q }, 'GET').done(results => {
				if (seq !== searchSeq) return;
				const $r = $('#searchResults').empty();
				if (!results.length) { $r.addClass('d-none'); return; }
				results.forEach(r => $r.append(
					$(`<button type="button" class="list-group-item list-group-item-action py-2">
						<div class="d-flex justify-content-between"><strong></strong><small class="text-muted"></small></div>
						<div class="small text-truncate"></div></button>`)
						.find('strong').text(r.symbol).end()
						.find('small').text([r.type, r.exchange].filter(Boolean).join(' · ')).end()
						.find('div.small').text(r.name).end()
						.on('click', () => selectSymbol(r.symbol, r.name))
				));
				$r.removeClass('d-none');
			});
		}, 300);
	}).on('blur', function () {
		setTimeout(() => {
			$('#searchResults').addClass('d-none');
			const v = $('#ticker').val().trim().toUpperCase();
			if (v && !selectedQuote && /^[A-Z0-9.\-=^]{1,20}$/.test(v)) selectSymbol(v, null);
		}, 200);
	});
	// Evita che il click su un risultato faccia scattare prima il blur del campo
	$('#searchResults').on('mousedown', e => e.preventDefault());
	$('#quantity').on('input', updatePreview);

	$('#formAsset').on('submit', function (e) {
		e.preventDefault();
		$(this).addClass('was-validated');
		if (!this.checkValidity()) return;
		const $btn = $('button[form=formAsset]').prop('disabled', true);
		App.api('assets/save', $(this).serialize())
			.done(() => { modal.hide(); App.toast('Salvato'); load(); })
			.fail(msg => App.toast(msg, 'danger'))
			.always(() => $btn.prop('disabled', false));
	});

	$('#btnDelete').on('click', function () {
		if (!confirm('Eliminare questa voce?')) return;
		App.api('assets/delete', { id: $('#assetId').val() })
			.done(() => { modal.hide(); App.toast('Eliminato'); load(); })
			.fail(msg => App.toast(msg, 'danger'));
	});
});
