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
            $('#categoryGain').html(`<span class="${App.signClass(gain)}">${gain > 0 ? '+' : ''}${App.money(gain)} (${App.pct(cost ? gain / cost * 100 : 0)})</span> <span class="text-muted">${App.t('cat.vs_cost')}</span>`);
        } else {
            $('#categoryGain').empty();
        }
        const times = assets.filter(a => a.quote).map(a => a.quote.fetched_at);
        $('#pricesUpdated').text(times.length ? App.t('cat.prices_updated', { ago: App.timeAgo(Math.min(...times)) }) : '');
    }

    const $list = $('#assetsList').empty();
    if (!assets.length) {
        $list.html(`<div class="empty-state"><i class="bi bi-plus-circle"></i>${App.t('cat.empty')}</div>`);
        return;
    }

    assets.forEach(a => {
        let meta = '', right = '';
        if (isMarket) {
            const q = a.quote;
            meta = `${App.esc(a.ticker)} · ${App.t('cat.shares_n', { n: App.number(a.quantity) })}`;
            if (q) meta += ` · ${App.number(q.price, 2)} ${App.esc(q.currency)}`;
            right = a.price_error
                ? '<div class="meta text-warning"><i class="bi bi-exclamation-triangle" style="font-size:12px"></i> ' + App.t('cat.price_na') + '</div>'
                : (a.gain !== null
                    ? `<div class="meta ${App.signClass(a.gain)}">${App.pct(a.gain_pct)}</div>`
                    : (q && q.change_pct !== null ? `<div class="meta ${App.signClass(q.change_pct)}">${App.t('cat.today', { pct: App.pct(q.change_pct) })}</div>` : ''));
        } else {
            const updated = App.date(a.updated_at.replace(' ', 'T') + 'Z');
            meta = a.stale
                ? `<span class="text-warning-emphasis"><i class="bi bi-clock-history" style="font-size:11px"></i> ${App.t('cat.stale_row', { date: updated })}</span>`
                : (a.notes ? App.esc(a.notes) : App.t('cat.updated_on', { date: updated }));
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
    $('#tickerInfo').text(App.t('cat.ticker_help'));
    selectedQuote = asset?.quote || null;

    $('#assetId').val(asset?.id || '');
    $('#modalAssetTitle').text(App.t(asset ? 'cat.edit' : 'cat.add'));
    $('#btnDelete').toggleClass('d-none', !asset);
    $('#staleHint').toggleClass('d-none', !asset?.stale)
        .find('span').text(asset?.stale ? App.t('cat.stale_hint', { months: asset.months_since_update }) : '');

    if (asset) {
        $('#name').val(asset.name);
        $('#notes').val(asset.notes || '');
        if (isMarket) {
            $('#ticker').val(asset.ticker);
            $('#quantity').val(App.inputValue(asset.quantity));
            $('#avg_cost').val(App.inputValue(asset.avg_cost));
            updatePreview();
        } else {
            $('#value').val(App.inputValue(asset.value));
        }
    }
    modal.show();
}

function updatePreview() {
    const qty = App.parseNum($('#quantity').val());
    if (!selectedQuote || selectedQuote.price_base === null || qty === null) {
        $('#previewValue').addClass('d-none');
        return;
    }
    $('#previewValue').removeClass('d-none').html(
        App.t('cat.preview', { price: App.money(selectedQuote.price_base), value: App.money(qty * selectedQuote.price_base) })
    );
}

function selectSymbol(symbol, name) {
    $('#ticker').val(symbol);
    $('#searchResults').addClass('d-none');
    if (!$('#name').val() && name) $('#name').val(name);
    $('#tickerInfo').html('<span class="spinner-border spinner-border-sm"></span> ' + App.esc(App.t('cat.fetching')));
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

    // Debounced instrument search
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
    // Keep focus on the input while clicking a result, so blur doesn't fire first
    $('#searchResults').on('mousedown', e => e.preventDefault());
    $('#quantity').on('input', updatePreview);

    $('#formAsset').on('submit', function (e) {
        e.preventDefault();
        $(this).addClass('was-validated');
        if (!this.checkValidity()) return;
        const $btn = $('button[form=formAsset]').prop('disabled', true);
        App.api('assets/save', $(this).serialize())
            .done(() => { modal.hide(); App.toast(App.t('cat.saved')); load(); })
            .fail(msg => App.toast(msg, 'danger'))
            .always(() => $btn.prop('disabled', false));
    });

    $('#btnDelete').on('click', function () {
        if (!confirm(App.t('cat.confirm_delete'))) return;
        App.api('assets/delete', { id: $('#assetId').val() })
            .done(() => { modal.hide(); App.toast(App.t('cat.deleted')); load(); })
            .fail(msg => App.toast(msg, 'danger'));
    });
});
