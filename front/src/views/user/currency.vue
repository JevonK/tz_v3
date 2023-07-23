<template>
	<div class="basic_wrap">
		<bsHeader :title="$t('index.exchangeRate')" @backurl="$router.back()"></bsHeader>
		<div class="currency_wrap">
			<div v-for="(currency,index) in currencies" class="block_div item">
				<div class="currency_detail">
					<div class="flex_center currency">
						<div class="flex_center logo">
							<img :src="currency.logo" alt="">
							<p>{{currency.symbol}}</p>
						</div>
						<div class="price">
							<div>
								<span>1 USD</span>
								<span> ≈ {{currency.price}} {{currency.symbol}}</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	import Vue from 'vue';
	import bsHeader from '../../components/bsHeader.vue'
	import Fetch from '../../utils/fetch'
	import {
		Cell,
		CellGroup
	} from 'vant';
	Vue.use(Cell).use(CellGroup);
	export default {
		name: "currency",
		components: {
			bsHeader
		},
		data() {
			return {
				data: [],
				currencies: []
			};
		},
		created() {
			if (window.plus) {
				plus.navigator.setStatusBarBackground('#FFFFFF');
				plus.navigator.setStatusBarStyle('dark');
			}
			this.$parent.footer('user', false);
		},
		mounted() {
			this.start();
		},
		methods: {
			start() {
				Fetch('/index/getCurrencyPrice').then(r => {
					this.currencies = r.data.list;
				})
			}
		}
	};
</script>

<style lang="less" scoped>
	.currency_wrap {
		padding-top: 55px;

		.item {
			margin-bottom: 5px;
		}

		img {
			width: 40px;
		}

		.currency_detail {
			padding: 20px 0;
		}

		.currency {
			

			p {
				font-size: 14px;
				font-weight: bold;
			}

			.logo {
				width: 40%;
				justify-content: center;
				p {
					margin-left: 5px;
				}
			}

			.price {
				width: 60%;
				color: #3CB371;
				font-weight: bold;
				text-align: left;
			}
		}
	}
</style>
