<template>
	<div class="basic_wrap">
		<div class="red_top_bg">
			<div class="back_left" @click="$router.back()"></div>
			<div class="record" @click="$router.push('/savings/redeem_record')">
				<img src="../img/user/record_b.png">
			</div>
		</div>
		<div class="savings_wrap">
			<div class="block_div savings_money_wrap">
				<p class="tips">{{$t('savings.redeemMoney')}}</p>
				<div class="flex_center">
					<van-field v-model="money" type="number" :placeholder="$t('savings.redeemMoneyPlaceholder')" />
				</div>
				<p class="userBalance" @click="showMsg()">{{$t('savings.redeemMoneyAva')}}<span>{{common.currency_symbol_basic()}}{{common.precision_basic(balance)}}</span> <van-icon name="question-o" size="14" style="left: 2px;top:1px;" /></p>
			</div>
			<div class="block_div savings_rules_wrap">
				<p class="tips">{{$t('savings.redeemRules')}}</p>
				<van-steps active="3">
					<van-step>T0</van-step>
					<van-step>T0</van-step>
				</van-steps>
				<div class="flex_center savings_rules_name">
					<p>{{$t('savings.redeemRulesTips1')}}</p>
					<p>{{$t('savings.redeemRulesTips2')}}</p>
				</div>
			</div>
			<div class="block_div savings_time_wrap">
				<p class="tips">{{$t('savings.redeemTime')}}</p>
				<div class="flex_center time">
					<p>{{$t('savings.redeemTimeTips1')}}</p>
					<p>{{time}}</p>
				</div>
				<div class="flex_center time">
					<p>{{$t('savings.redeemTimeTips2')}}</p>
					<p>{{time}}</p>
				</div>
			</div>
			<div class="block_div savings_detail_wrap">
				<div class="basic_btn " :class="money>0?'':'no_touch'" @click="submit">
					{{$t('savings.redeem')}}
				</div>
			</div>

		</div>
	</div>
</template>

<script>
	import Vue from 'vue';
	import Fetch from '../../utils/fetch'
	import {
		Field,
		Step,
		Steps,
		Icon
	} from 'vant';
	Vue.use(Field).use(Step).use(Steps).use(Icon);
	export default {
		name: "",
		data() {
			return {
				balance: '',
				money: '',
				days: '',
				time:'',
				loading: false,
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
			showMsg() {
				this.$dialog.alert({
					closeOnClickOverlay: true,
					showConfirmButton: false,
					message: "<p style='text-align: left'>" +
						this.$t('savings.redeemTips1') + this.days + this.$t('savings.redeemTips2') +
						"</p>",
				}).catch(() => {
					// on close
				});
			},
			start() {
				Fetch('/user/savingsRedeemDetail').then(r => {
					this.balance = r.data.balance;
					this.days = r.data.days;
					this.time = r.data.time;
				})
			},
			submit() {
				if (this.loading) return false;

				if (this.money <= 0) {
					this.$toast(this.$t('savings.redeemMoneyError'));
					return false;
				}

				if (this.money - this.balance > 0) {
					this.$toast(this.$t('savings.redeemMoneyNotEnough'));
					return false;
				}
				this.loading = true;
				Fetch('/user/savingsRedeem', {
					money: this.money
				}).then(r => {
					this.$router.replace('/savings/redeem_record');
				})
			}
		}
	};
</script>

<style lang="less" scoped>
	.basic_wrap {
		margin-bottom: 70px;
	}

	.red_top_bg {
		position: fixed;
		top: 0;
		z-index: 10;
		background-color: #FFFFFF;

		.record {
			position: absolute;
			top: 11px;
			right: 15px;

			img {
				width: 22px;
				height: 22px;
			}
		}
	}

	.savings_wrap {
		margin-top: 54px;

		.tips {
			margin-bottom: 10px;
			font-size: 14px;
			color: #999;
		}

		.savings_days_wrap {
			padding: 20px;
			margin-bottom: 10px;
			font-size: 14px;
			color: #999;

			.savings_type {
				justify-content: space-between;
			}

			.margin0 {
				margin-bottom: 0;
			}
		}

		.savings_money_wrap {
			padding: 20px;
			margin-bottom: 10px;
			font-size: 14px;
			color: #999;

			.userBalance {
				margin: 10px 15px;
				font-size: 12px;

				span {
					margin-left:5px;
					color: #000;
				}
			}
		}

		.savings_income_wrap {
			padding: 20px;
			margin-bottom: 10px;

			.spacebetween {
				justify-content: space-between;
				padding: 0 10px;
			}

			.margin0 {
				margin-bottom: 0;
			}

			.rate {
				color: #999;
			}
		}

		.savings_rules_wrap {
			padding: 20px;
			margin-bottom: 10px;

			.savings_rules_name {
				padding: 0 10px;
				justify-content: space-between;

				p:nth-child(1) {
					text-align: left;
				}

				p:nth-child(2) {
					text-align: right;
				}


				p {
					width: 50%;

				}
			}
		}

		.savings_time_wrap {
			padding: 20px;
			margin-bottom: 10px;
			.tips{
				margin-bottom:20px;
			}
			.time {
				justify-content: space-between;

				p {
					margin-bottom: 10px;
				}
			}
		}

		.savings_detail_wrap {
			position: fixed;
			bottom: 0px;
			width: 100%;
			margin: 0;
			justify-content: space-between;
			padding: 15px;

			.detail_money {
				color: #FF0000;
				font-weight: bold;
				font-size: 16px;
				margin-left: 5px;
			}

			.btn {
				width: unset;
			}
		}
	}
</style>
