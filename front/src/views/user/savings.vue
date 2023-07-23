<template>
	<div class="basic_wrap">
		<div class="red_top_bg">
			<div class="back_left" @click="$router.back()"></div>
			<div class="big_tit">{{$t('savings.savings')}}</div>
		</div>
		<div class="savings_wrap">
			<div class="flex_center savings_top">
				<div class="savings_top_left">
					<p>{{$t('savings.savings')}}</p>
					<p>{{$t('savings.savingsTips1')}} | {{$t('savings.savingsTips2')}}</p>
				</div>
				<div class="savings_top_right">
					<img src="../img/user/savings_usd.png">
				</div>
			</div>
			<div class="block_div flex_center money1">
				<div>
					<p>{{$t('savings.flexible')}} 
					</p>
					<p>{{common.currency_symbol_basic()}}{{common.precision_basic(flexible_usd)}}</p>
					<p class="money_usd">{{$t('invest.income')}}{{common.currency_symbol_basic()}}{{common.precision_basic(income)}}</p>
				</div>
				<div class="subscribe">
					<p @click="$router.push('/savings/subscribe/1')">{{$t('savings.subscribe')}}</p>
					<p v-if="flexible_usd>0" @click="$router.push('/savings/redeem')">{{$t('savings.redeem')}}</p>
				</div>
			</div>
			<!-- <div class="block_div flex_center money1">
				<div>
					<p>{{$t('savings.fixed')}} 
					</p>
					<p>{{common.currency_symbol_basic()}}{{common.precision_basic(fixed_usd)}}</p>
				</div>
				<div class="subscribe ">
					<p @click="$router.push('/savings/subscribe/2')">{{$t('savings.subscribe')}}</p>
				</div>
			</div> -->
			<div class="block_div savings_detail">
				<div class="title">
					{{$t('savings.introduce')}}
				</div>
				<div class="content" v-html="savings.content"></div>
			</div>
		</div>
	</div>
</template>

<script>
	import Vue from 'vue';
	import bsHeader from '../../components/bsHeader.vue'
	import Fetch from '../../utils/fetch'
	import {
		Icon
	} from "vant";
	
	Vue.use(Icon);
	export default {
		name: "",
		components: {
			bsHeader
		},
		data() {
			return {
				data: [],
				savings: {
					content: ''
				},
				flexible: 0,
				flexible_usd: 0,
				fixed: 0,
				fixed_usd: 0,
				income:0
			};
		},
		created() {
			if (window.plus) {
				plus.navigator.setStatusBarBackground('#000002');
				plus.navigator.setStatusBarStyle('light');
			}
			this.$parent.footer('user', false);
		},
		mounted() {
			this.start();
		},
		methods: {
			showMsg() {
				this.$dialog.alert({
					closeOnClickOverlay: true,
					showConfirmButton: false,
					message: "<p style='text-align: left'>" +
						this.$t('user.fundingTips') +
						"</p>",
				}).catch(() => {
					// on close
				});
			},
			start() {
				Fetch('/user/savingsDetail').then(r => {
					this.savings = r.data.savings;
					this.flexible = r.data.flexible;
					this.flexible_usd = r.data.flexible_usd;
					// this.fixed = r.data.fixed;
					// this.fixed_usd = r.data.fixed_usd;
					this.income = r.data.income;
				})
			}
		}
	};
</script>

<style lang="less" scoped>
	.red_top_bg{
		background: #000002;
		position: fixed;
		z-index: 10;
		box-shadow: unset;
		.back_left{
			background: url(../img/common/back.png) no-repeat center center;
			width: 30px;
			height: 22px;
		}
		.big_tit{
			color: #FFFFFF;
			left: 55px;
		}
	}

	.basic_wrap {
		height: 100%;
		width: 100%;
	}

	.savings_wrap {
		position: absolute;
		height: 100%;
		width: 100%;
		padding-top: 64px;
		background: url(../img/user/vip_bg.png) no-repeat center center;
		background-size: 100% 100%;
		padding-bottom: 30px;

		.savings_top {
			justify-content: space-between;
			margin-left: 20px;
			font-size: 26px;
			margin-bottom: 30px;

			.savings_top_left {
				p:nth-child(1) {
					margin-bottom: 20px;
					color: #FFFFFF;
				}

				p:nth-child(2) {
					color: #b2b2b2;
					font-size: 16px;
				}
			}

			.savings_top_right {
				img {
					height: 90px;
					margin-right: 20px;
				}
			}

		}

		.money1 {
			margin-bottom: 10px;
			padding: 15px;
			justify-content: space-between;

			div:nth-child(1) {
				p:nth-child(1) {
					color: #999;
					margin-bottom: 10px;
				}

				p:nth-child(2) {
					font-size: 18px;
					font-weight: bold;
				}
			}

			div:nth-child(2) {}

			.money_usd {
				margin-top: 10px;
				color: #999;
			}

			.subscribe {
				margin-top: 10px;

				p {
					padding: 6px 12px;
					background: #3775f4;
					margin: 0 10px 8px 0;
					color: #FFFFFF;
					border-radius: 8px;
				}

				p:nth-child(2) {
					background: #FF5722;
				}
			}

			.btn_1 {
				p {
					background: #FF5722;
				}
			}
		}

		.savings_detail {
			.title {
				padding: 15px 0 10px 15px;
				font-size: 16px;
				border-bottom: 1px solid #F7F7F7;
			}

			.content {
				padding: 10px 15px 15px 15px;
				line-height: 2;

			}
		}

	}
</style>
