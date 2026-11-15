// Helper comuni a tutte le pagine
$.ajaxSetup({
	headers: { 'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content') }
});

const App = {
	lang: I18N.lang,
	locale: I18N.locale,

	/** Traduce una chiave sostituendo i segnaposto {nome}. */
	t(key, params = {}) {
		let s = I18N.strings[key] ?? key;
		for (const [k, v] of Object.entries(params)) s = s.split('{' + k + '}').join(v);
		return s;
	},

	/** Cambia lingua (cookie per dispositivo, 1 anno) e ricarica la pagina. */
	setLang(lang) {
		const path = new URL(server_url, location.href).pathname;
		document.cookie = `${I18N.cookie}=${lang}; path=${path}; max-age=31536000; SameSite=Lax`;
		location.reload();
	},

	/** Legge un numero scritto secondo la lingua: it "7.400,50", en "7,400.50". */
	parseNum(v) {
		v = String(v ?? '').replace(/[\s€]/g, '');
		if (App.lang === 'en') v = v.replace(/,/g, '');
		else if (v.includes(',')) v = v.replace(/\./g, '').replace(',', '.');
		else if (/^-?\d{1,3}(\.\d{3})+$/.test(v)) v = v.replace(/\./g, '');
		const n = parseFloat(v);
		return isNaN(n) ? null : n;
	},

	/** Numero da mostrare in un campo di input (senza separatore delle migliaia). */
	inputValue(n) {
		if (n === null || n === undefined || n === '') return '';
		const s = String(n);
		return App.lang === 'it' ? s.replace('.', ',') : s;
	},

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
				const msg = xhr.responseJSON?.message || App.t('common.conn_error');
				return $.Deferred().reject(msg).promise();
			}
		);
	},

	money(value, decimals = 2) {
		return new Intl.NumberFormat(App.locale, {
			style: 'currency', currency: base_currency,
			minimumFractionDigits: decimals, maximumFractionDigits: decimals, useGrouping: 'always'
		}).format(value || 0);
	},

	number(value, decimals = 4) {
		return new Intl.NumberFormat(App.locale, { maximumFractionDigits: decimals, useGrouping: 'always' }).format(value || 0);
	},

	pct(value) {
		if (value === null || value === undefined) return '';
		return (value > 0 ? '+' : '') + new Intl.NumberFormat(App.locale, { maximumFractionDigits: 2 }).format(value) + '%';
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

	// Colori di default dei grafici coerenti con il tema (chiamare dopo aver caricato Chart.js)
	chartTheme() {
		Chart.defaults.color = App.cssVar('--bs-secondary-color');
		Chart.defaults.borderColor = App.cssVar('--bs-border-color');
	},

	cssVar(name) {
		return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
	},

	date(value, options = {}) {
		return new Date(value).toLocaleDateString(App.locale, options);
	},

	timeAgo(unix) {
		const diff = Math.floor(Date.now() / 1000 - unix);
		if (diff < 60) return App.t('ago.now');
		if (diff < 3600) return App.t('ago.min', { n: Math.floor(diff / 60) });
		if (diff < 86400) return App.t('ago.h', { n: Math.floor(diff / 3600) });
		return App.t('ago.d', { n: Math.floor(diff / 86400) });
	}
};
