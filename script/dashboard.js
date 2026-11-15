let allocationChart = null;

function loadSummary(refresh = false) {
	const $icon = $('#btnRefresh i').addClass('spin');

	App.api('portfolio/summary', { refresh: refresh ? 1 : 0 }, 'GET')
		.done(render)
		.fail(msg => App.toast(msg, 'danger'))
		.always(() => $icon.removeClass('spin'));
}

function render(data) {
	$('#netWorth').text(App.money(data.net_worth));
	$('#totalAssets').text(App.money(data.total_assets));
	$('#totalLiabilities').text(App.money(data.total_liabilities));

	if (data.stale_total > 0) {
		$('#staleText').text(data.stale_total === 1
			? App.t('dash.stale_one', { months: data.stale_months })
			: App.t('dash.stale_many', { n: data.stale_total, months: data.stale_months }));
		$('#staleAlert').removeClass('d-none');
	} else {
		$('#staleAlert').addClass('d-none');
	}

	const $list = $('#categoriesList').empty();
	if (!data.categories.length) {
		$list.html(`<div class="empty-state"><i class="bi bi-folder-plus"></i>${App.esc(App.t('dash.no_sections'))}</div>`);
	}

	data.categories.forEach(c => {
		const share = !c.is_liability && data.total_assets > 0 ? (c.total / data.total_assets * 100) : null;
		const gain = c.gain !== null
			? `<span class="${App.signClass(c.gain)}">${c.gain > 0 ? '+' : ''}${App.money(c.gain)}</span>` : '';
		const warn = c.price_errors > 0
			? ' <i class="bi bi-exclamation-triangle text-warning" title="' + App.esc(App.t('dash.prices_unavailable')) + '" style="font-size:14px"></i>' : '';
		const stale = c.stale_count > 0
			? ` · <span class="text-warning-emphasis"><i class="bi bi-clock-history" style="font-size:11px"></i> ${App.t('dash.to_update', { n: c.stale_count })}</span>` : '';

		$list.append(`
			<a class="list-row" href="${server_url}category?id=${c.id}">
				<span class="cat-icon" style="background:${c.color}1a;color:${c.color}"><i class="bi ${App.esc(c.icon)}"></i></span>
				<div class="flex-grow-1 min-w-0">
					<div class="fw-medium text-truncate">${App.esc(c.name)}${warn}</div>
					<div class="meta">${App.t(c.count === 1 ? 'common.items_one' : 'common.items_many', { n: c.count })}${share !== null ? ' · ' + App.pct(share).replace('+', '') : ''}${c.is_liability ? ' · ' + App.t('common.liability') : ''}${stale}</div>
				</div>
				<div class="text-end">
					<div class="amount-sm ${c.is_liability && c.total > 0 ? 'text-danger' : ''}">${c.is_liability && c.total > 0 ? '−' : ''}${App.money(c.total)}</div>
					<div class="meta">${gain}</div>
				</div>
				<i class="bi bi-chevron-right text-muted" style="font-size:14px"></i>
			</a>`);
	});

	renderChart(data.categories.filter(c => !c.is_liability && c.total > 0));
}

function renderChart(cats) {
	$('#allocationCard').toggle(cats.length > 0);
	if (!cats.length) return;

	const cfg = {
		labels: cats.map(c => c.name),
		datasets: [{ data: cats.map(c => c.total), backgroundColor: cats.map(c => c.color), borderWidth: 2, borderColor: App.cssVar('--app-surface') }]
	};
	if (allocationChart) {
		allocationChart.data = cfg;
		allocationChart.update();
		return;
	}
	allocationChart = new Chart($('#allocationChart'), {
		type: 'doughnut',
		data: cfg,
		options: {
			cutout: '65%',
			plugins: {
				legend: { position: 'bottom', labels: { boxWidth: 10, usePointStyle: true } },
				tooltip: { callbacks: { label: ctx => ' ' + ctx.label + ': ' + App.money(ctx.raw) } }
			}
		}
	});
}

$(function () {
	App.chartTheme();
	loadSummary();
	$('#btnRefresh').on('click', e => { e.preventDefault(); loadSummary(true); });
});
