export default {
	precision(money) {
		var precision = localStorage.getItem('precision');
		return (money / 1).toFixed(precision);
	},
	precision_basic(money) {
		var precision = localStorage.getItem('precision_basic');
		return (money / 1).toFixed(precision);
	},
	currency() {
		return localStorage.getItem('currency');
	},
	currency_symbol_basic(money) {
		// return localStorage.getItem('currency_symbol_basic');
		return "";
	}
}
