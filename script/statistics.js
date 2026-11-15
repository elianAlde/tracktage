let netChart = null, stackChart = null;

const moneyTick = v => new Intl.NumberFormat('it-IT', { notation: 'compact', style: 'currency', currency: base_currency }).format(v);
const fmtDate = d => new Date(d + 'T00:00:00').toLocaleDateString('it-IT', { day: '2-digit', month: 'short' });

function load(days) {
	App.api('portfolio/history', { days }, 'GET').done(render).fail(msg => App.toast(msg, 'danger'));
}

function render({ categories, snapshots }) {
	const labels = snapshots.map(s => fmtDate(s.snapshot_date));

	if (snapshots.length >= 2) {
		const first = snapshots[0].net_worth, last = snapshots[snapshots.length - 1].net_worth;
		const diff = last - first;
		$('#periodChange').attr('class', 'small ' + App.signClass(diff))
			.text(`${diff > 0 ? '+' : ''}${App.money(diff, 0)}${first ? ' (' + App.pct(diff / Math.abs(first) * 100) + ')' : ''}`);
	} else {
		$('#periodChange').text('');
	}

	netChart?.destroy();
	netChart = new Chart($('#netWorthChart'), {
		type: 'line',
		data: {
			labels,
			datasets: [{
				data: snapshots.map(s => s.net_worth),
				borderColor: '#0d6efd',
				backgroundColor: 'rgba(13,110,253,.08)',
				fill: true, tension: .3, pointRadius: snapshots.length > 40 ? 0 : 3
			}]
		},
		options: {
			plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => ' ' + App.money(c.raw) } } },
			scales: { y: { ticks: { callback: moneyTick } }, x: { ticks: { maxTicksLimit: 6 } } }
		}
	});

	const assetCats = categories.filter(c => !c.is_liability);
	stackChart?.destroy();
	stackChart = new Chart($('#stackChart'), {
		type: 'line',
		data: {
			labels,
			datasets: assetCats.map(c => ({
				label: c.name,
				data: snapshots.map(s => s.breakdown[c.id] ?? 0),
				borderColor: c.color,
				backgroundColor: c.color + '55',
				fill: true, tension: .3, pointRadius: 0, borderWidth: 1
			}))
		},
		options: {
			interaction: { mode: 'index', intersect: false },
			plugins: {
				legend: { position: 'bottom', labels: { boxWidth: 10, usePointStyle: true } },
				tooltip: { callbacks: { label: c => ' ' + c.dataset.label + ': ' + App.money(c.raw) } }
			},
			scales: { y: { stacked: true, ticks: { callback: moneyTick } }, x: { ticks: { maxTicksLimit: 6 } } }
		}
	});
}

$(function () {
	load(365);
	$('#periodGroup').on('click', 'button', function () {
		$(this).addClass('active').siblings().removeClass('active');
		load($(this).data('days'));
	});
});
