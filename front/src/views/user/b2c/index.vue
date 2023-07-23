<template>
	<div class="basic_wrap">
		<div class="red_top_bg">
			<div class="big_tit">{{$t('home.b2c')}}</div>
			<div class="country">
				<van-dropdown-menu>
					<van-dropdown-item v-model="lang" :options="currencies" @change="change" />
				</van-dropdown-menu>
			</div>
		</div>
		<div class="tip">我要卖
			<div class="record" @click="$router.push('/b2c/record')">
				<img src="../img/user/record.png">
			</div>
		</div>
		<div class="flex_center user_balance">
			<div>出售数量</div>
			<div>{{balance}} TRX</div>
		</div>
		<div class="item flex_center">
			<input v-model.trim="amount" type="number" class="inp" placeholder="0">
			<div class="all"><span>TRX</span><span @click="amount=balance">全部</span></div>
		</div>
		<div class="price">
			<span>参考单价：{{currency.price}} {{currency.text}}</span>
		</div>
		<button class="basic_btn sbtn" :class="amount==''||amount<=0?'no_touch':''" @click="sellPop()">出售</button>
		<van-popup v-model:show="show" closeable close-icon-position="top-left" position="bottom"
			:style="{ height: '370px' }">
			<div class="popup_title">确认出售</div>
			<div class="detail">
				<div class="money">
					<div>
						<span>{{amount}}</span><span>TRX</span>
					</div>
					<div>
						<span>我将收到 </span><span>{{(amount*(currency.price)).toFixed(4)}} {{currency.text}}</span>
					</div>
				</div>
				<div class="payment">
					<div class="choose_tip">
						请选择收款方式
					</div>
					<div class="payment_detail">
						<div class="name">
							<span>银行卡</span>
						</div>
						<div class="account">
							612********123
							<span>✔</span>
						</div>
					</div>
				</div>
				<div class="add_payment">
					+添加收款方式
				</div>
			</div>
			<button class="basic_btn sbtn1">确认出售</button>
		</van-popup>

	</div>
</template>

<script>
	import Vue from 'vue';
	import bsHeader from '../../components/bsHeader.vue'
	import Fetch from '../../utils/fetch'
	import {
		Popup,
		DropdownMenu,
		DropdownItem
	} from 'vant';
	Vue.use(Popup).use(DropdownMenu).use(DropdownItem);
	export default {
		name: "",
		components: {
			bsHeader
		},
		data() {
			return {
				data: [],
				amount: '',
				balance: 0,
				show: false,
				lang: this.$i18n.locale,
				trxPrice: 0,
				currencies: [],
				currency: {
					"text": "",
					"value": "",
					"price": 0
				}
			};
		},
		created() {
			this.$parent.footer('cart');
		},
		mounted() {
			this.getWalletBalance();
			//获取显示货币符号
			this.getSymbil();
		},
		methods: {
			sellPop() {
				if (!this.amount) {
					return false;
				}
				if (this.amount <= 0) {
					return false;
				}
				this.show = true;
			},
			change(index) {
				//根据国别获取对象
				let currency = this.currencies.find((currency) => {
					return currency['value'] === index
				});
				this.currency = currency;
			},
			getWalletBalance() {
				Fetch("/user/getWalletBalance").then(r => {
					this.balance = r.data.money;
				});
			},
			getSymbil() {
				//欧元国家默认为pt_pt
				if (this.lang == "pt_pt" || this.lang == "es_es" || this.lang == "de_de" || this.lang == "fr_fr") {
					this.lang = "pt_pt";
				}
				Fetch("/index/get_currencies").then(r => {
					this.trxPrice = r.data.price.price_in_usd;
					var currencies = r.data.currencies;
					var isEUR = false;

					for (let i = 0; i < currencies.length; i++) {
						if (currencies[i].country == this.lang) {
							this.currency['text'] = currencies[i].name;
							this.currency['value'] = currencies[i].country;
							this.currency['price'] = (currencies[i].price * this.trxPrice).toFixed(4);
						}
						if (currencies[i].name == "EUR") {
							//欧元只加载一次
							if (isEUR) {
								continue;
							} else {
								isEUR = true;
							}
						}
						this.currencies.push({
							text: currencies[i].name,
							value: currencies[i].country,
							price: (currencies[i].price * this.trxPrice).toFixed(4)
						})
					}
				});
			}
		}
	};
</script>

<style lang="less" scoped>
	/deep/ .van-dropdown-menu__bar {
		height: 14px;
		box-shadow: unset;
	}

	/deep/ .van-dropdown-item {
		top: 44px !important;
	}

	/deep/ .van-popup--top {
		right: 0;
		width: 100px;
		left: unset;
	}

	.red_top_bg {
		position: fixed;
		top: 0;
		z-index: 10;
		background-color: #FFFFFF;

		.country {
			position: absolute;
			top: 10px;
			right: 15px;
			padding: 6px 10px;
			border-radius: 15px;
			background: #FFFFFF;

			span {
				font-size: 16px;
				margin-left: 5px;
			}
		}
	}

	.tip {
		margin: 60px 5% 20px 5%;
		font-size: 20px;
		font-weight: bold;

		.record {
			position: absolute;
			top: 52px;
			right: 15px;

			img {
				width: 22px;
				height: 22px;
			}
		}
	}

	.popup_title {
		text-align: center;
		line-height: 46px;
		font-size: 14px;
		color: #999;
	}

	.item {
		width: 96%;
		margin: 10px 2% 10px 2%;
		background: #FFFFFF;
		padding: 15px;
		border-radius: 5px;
		font-size: 14px;

		input {
			width: 70%;
			font-size: 30px;
		}

		.all {
			font-size: 14px;
			text-align: right;
			width: 30%;

			span:nth-child(1) {
				margin-right: 10px;
				color: #999;
			}

			span:nth-child(2) {
				color: #1989fa;
			}
		}

	}

	.price {
		color: #999;
		margin-left: 5%;
	}


	.user_balance {
		margin: 30px 5% 0 5%;

		div {
			width: 50%;
		}

		div:nth-child(1) {
			text-align: left;
		}

		div:nth-child(2) {
			text-align: right;
			color: #999;
		}
	}

	.sbtn {
		width: 80%;
		margin-left: 10%;
		position: absolute;
		bottom: 80px;
	}

	.sbtn1 {
		position: absolute;
		bottom: 30px;
		left: 8%;
	}

	.detail {
		padding: 15px;

		.money {
			text-align: center;
			margin-bottom: 25px;

			div:nth-child(1) {
				margin-bottom: 10px;

				span:nth-child(1) {
					font-size: 20px;
					font-weight: bold;
					margin-right: 5px;
				}

				span:nth-child(2) {
					font-size: 16px;
				}
			}

			div:nth-child(2) {}
		}

		.payment {
			.choose_tip {
				color: #999;
				margin-bottom: 10px;
			}

			.payment_detail {
				background: #FAFAFA;

				div:nth-child(1) {
					padding: 15px 10px 5px 10px;
					margin-bottom: 10px;
					color: #999;

					span {
						border-left: 4px solid #FF0000;
						padding-left: 10px;
					}
				}

				div:nth-child(2) {
					border: 2px solid #1989fa;
					padding: 8px;
					padding-left: 20px;

					span {
						position: absolute;
						right: 25px;
						color: #1989fa;
					}
				}
			}
		}

		.add_payment {
			text-align: center;
			margin-top: 30px;
		}
	}
</style>
