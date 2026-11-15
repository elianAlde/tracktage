// Helper comuni a tutte le pagine
$.ajaxSetup({
	headers: { 'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content') }
});

const App = {
	api(endpoint, data = {}, method = 'POST') {
		return $.ajax({
			url: server_url + 'api/endpoint/' + endpoint + '.php',
			type: method,
			dataType: 'json',
			data: data
		}).then(
			json => json.data,
			xhr => {
				if (xhr.status === 401) { window.location.href = server_url + 'login'; }
				const msg = xhr.responseJSON?.message || 'Errore di connessione. Riprova.';
				return $.Deferred().reject(msg).promise();
			}
		);
	},

	money(value, decimals = 2) {
		return new Intl.NumberFormat('it-IT', {
			style: 'currency', currency: base_currency,
			minimumFractionDigits: decimals, maximumFractionDigits: decimals, useGrouping: 'always'
		}).format(value || 0);
	},

	number(value, decimals = 4) {
		return new Intl.NumberFormat('it-IT', { maximumFractionDigits: decimals, useGrouping: 'always' }).format(value || 0);
	},

	pct(value) {
		if (value === null || value === undefined) return '';
		return (value > 0 ? '+' : '') + new Intl.NumberFormat('it-IT', { maximumFractionDigits: 2 }).format(value) + '%';
	},

	signClass(value) {
		return value > 0 ? 'text-success' : (value < 0 ? 'text-danger' : 'text-muted');
	},

	esc(str) {
		return $('<div>').text(str ?? '').html();
	},

	toast(message, type = 'dark') {
		let $wrap = $('#toastWrap');
		if (!$wrap.length) {
			$wrap = $('<div id="toastWrap" class="toast-container position-fixed start-50 translate-middle-x p-3"></div>').appendTo('body');
		}
		const $t = $(`<div class="toast align-items-center text-bg-${type} border-0" role="alert">
			<div class="d-flex"><div class="toast-body"></div>
			<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>`);
		$t.find('.toast-body').text(message);
		$wrap.append($t);
		const toast = new bootstrap.Toast($t[0], { delay: 3000 });
		$t.on('hidden.bs.toast', () => $t.remove());
		toast.show();
	},

	timeAgo(unix) {
		const diff = Math.floor(Date.now() / 1000 - unix);
		if (diff < 60) return 'adesso';
		if (diff < 3600) return Math.floor(diff / 60) + ' min fa';
		if (diff < 86400) return Math.floor(diff / 3600) + ' h fa';
		return Math.floor(diff / 86400) + ' g fa';
	}
};
