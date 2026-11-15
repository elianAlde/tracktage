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

	const $list = $('#categoriesList').empty();
	if (!data.categories.length) {
		$list.html('<div class="empty-state"><i class="bi bi-folder-plus"></i>Nessuna sezione. Creane una dalle impostazioni.</div>');
	}

	data.categories.forEach(c => {
		const share = !c.is_liability && data.total_assets > 0 ? (c.total / data.total_assets * 100) : null;
		const gain = c.gain !== null
			? `<span class="${App.signClass(c.gain)}">${c.gain > 0 ? '+' : ''}${App.money(c.gain)}</span>` : '';
		const warn = c.price_errors > 0
			? ' <i class="bi bi-exclamation-triangle text-warning" title="Quotazioni non disponibili" style="font-size:14px"></i>' : '';

		$list.append(`
			<a class="list-row" href="${server_url}category?id=${c.id}">
				<span class="cat-icon" style="background:${c.color}1a;color:${c.color}"><i class="bi ${App.esc(c.icon)}"></i></span>
				<div class="flex-grow-1 min-w-0">
					<div class="fw-medium text-truncate">${App.esc(c.name)}${warn}</div>
					<div class="meta">${c.count} ${c.count === 1 ? 'voce' : 'voci'}${share !== null ? ' · ' + share.toFixed(1) + '%' : ''}${c.is_liability ? ' · passività' : ''}</div>
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
		datasets: [{ data: cats.map(c => c.total), backgroundColor: cats.map(c => c.color), borderWidth: 2, borderColor: '#fff' }]
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
	loadSummary();
	$('#btnRefresh').on('click', e => { e.preventDefault(); loadSummary(true); });
});
