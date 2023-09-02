<template>
	<div class="basic_wrap">
		<div class="red_top_bg">
			<div class="back_left" @click="$router.back()"></div>
			<div class="record" @click="$router.push('/withdraw/record')">
				<img src="../img/user/record_b.png">
			</div>
		</div>
		<div class="desc_warp">
			<img src="../img/user/withdraw.png" />
			<div class="flex_center userBalance">
				<p>{{$t('withdraw.fundBalance')}}</p>
				<!-- <van-icon v-show="frozenAmount!=0" name="question-o" size="16" color="#999" style="margin-left: 5px;"
					@click="showMsg()" /> -->
			</div>
			<p>{{common.currency_symbol_basic()}}{{common.precision_basic(userBalance)}}</p>
		</div>
		<div class="withdraw_wrap">
			<div class="block_div item choose_wallet">
				<div class="flex_center">
					<p>{{$t('withdraw.account')}}</p>
					<div @click="show=true">
						<span>{{withdraw_name}}</span>
						<van-icon name="arrow" color="#999" />
					</div>
				</div>
			</div>
			<van-action-sheet v-model="show" :actions="wallets" cancel-text=""
				:description="$t('withdraw.accountPlaceholder')" close-on-click-action @select="onSelect" />
			<div class="block_div item">
				<div class="">
					<p class="withdraw_money_tips">{{$t('withdraw.amount')}}</p>
					<div class="flex_center">
						<van-field v-model="wallet.money" type="number"
							:placeholder="$t('withdraw.amountPlaceholder')" />
					</div>
				</div>
			</div>
		</div>
		<!-- <p class="withdraw_time_tips">
			{{$t('withdraw.withdrawNum')}}{{withdraw_num}}{{$t('utils.times'+times)}}<br>
			{{$t('withdraw.withdrawAmount')}}{{common.currency_symbol_basic()}}{{common.precision_basic(withdraw_min)}}<br>
			{{$t('withdraw.withdrawTips')}}
		</p> -->
		<div class="basic_btn btn" :class="wallet.money>0?'':'no_touch'" @click="submit()">
			{{$t('withdraw.withdrawNow')}}
		</div>

		<van-dialog v-model:show="show_tips" title="" :show-confirm-button="false">
			<div class="tips-show">
				<van-icon name="close" color="#fff" size="30" @click="show_tips=false" />
				<h3>How to withdraw money</h3>
				<p>
					1. Click to bind the bank<br>
					2. Click Apply for Withdrawal<br>
					Withdrawal rules:<br>
					Application withdrawal time: 10:00 am to 6:00 pm.
					The minimum withdrawal amount is: 20000Rp
					Withdrawal will be completed within t+1-T+3 days
					(Banks are closed on Sundays and do not process withdrawals,
					Withdrawals are processed on Monday)
					Withdrawal tax: 10%
					(Withdraw 1000, actually received 900)
				</p>
				<div class="basic_btn tips-btn"  @click="show_tips=false">
					OK
				</div>
			</div>
		</van-dialog>
	</div>
</template>

<script>
	import Vue from 'vue';
	import bsHeader from '../../components/bsHeader.vue'
	import Fetch from '../../utils/fetch'
	import {
		Icon,
		ActionSheet,
		Field,
		Dialog
	} from 'vant';
	Vue.use(Icon).use(ActionSheet).use(Field).use(Dialog);
	export default {
		name: "",
		components: {
			bsHeader
		},
		data() {
			return {
				rate: localStorage.getItem('rate'),
				data: [],
				userBalance: "0",
				frozenAmount: '0',
				withdraw_name: this.$t('withdraw.bindAccount'),
				show: false,
				show_tips: false,
				wallets: [],
				wallet: {
					money: '',
					id: 0,
				},
				withdraw_num: 0,
				withdraw_min: 0,
				times: 0
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
			this.show_tips = true
		},
		methods: {
			start() {
				Fetch('/user/getWallets').then(r => {
					this.userBalance = r.data.userBalance;
					this.frozenAmount = r.data.frozenAmount;
					this.withdraw_min = r.data.withdrawMin;
					this.withdraw_num = r.data.withdrawNum;
					this.times = r.data.withdrawNum;
					if (this.times > 1) this.times = 2;
					var walletList = [];
					r.data.wallets.forEach(item => {
						walletList.push({
							name: item.wname + "(" + item.account + ")",
							value: item.id,
							type: item.type,
						})
					})

					if (walletList.length == 0) {
						walletList.push({
							name: this.$t('withdraw.bindAccount'),
							value: 0,
							color: "#FF0000",
						})
					}
					this.withdraw_name = walletList[0].name;
					this.wallet.id = walletList[0].value;
					this.wallets = walletList;
				})
			},
			onSelect(item) {
				if (item.value == 0) {
					this.$router.push('/wallet')
				}
				this.withdraw_name = item.name;
				this.wallet.id = item.value;
			},
			changeAmount() {},
			submit() {
				if (this.wallet.id == 0) {
					this.$toast(this.$t('withdraw.accountEmpty'));
					return false;
				}
				if (this.wallet.money == "") {
					this.$toast(this.$t('withdraw.amountEmpty'));
					return false;
				}
				if (this.wallet.money - this.userBalance > 0) {
					this.$toast(this.$t('withdraw.amountError'));
					return false;
				}
				if (this.wallet.money - this.withdraw_min < 0) {
					this.$toast(this.$t('withdraw.withdrawAmount')+this.common.currency_symbol_basic()+this.common.precision_basic(this.withdraw_min));
					return false;
				}
				Fetch('/user/withdraw', {
					...this.wallet,
				}).then(r => {
					this.$router.replace('/withdraw/record');
				})
			},
			showMsg() {
				this.$dialog.alert({
					closeOnClickOverlay: true,
					showConfirmButton: false,
					message: "<p style='text-align: left'>" +
						this.$t('withdraw.frozenAmount') +
						this.common.currency_symbol_basic()+
						this.common.precision_basic(this.frozenAmount) +
						" " +
						"<br>" +
						this.$t('withdraw.needInvest') +
						"</p>",
				}).catch(() => {
					// on close
				});
			}
		}
	};
</script>

<style lang="less" scoped>
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

	.desc_warp {
		margin-top: 60px;
		text-align: center;
		.userBalance{
			justify-content: center;
		}

		img {
			width: 100px;
			height: 100px;
		}

		p:nth-child(2) {
			font-size: 14px;
			color: #999;
		}

		p:nth-child(3) {
			font-size: 20px;
			font-weight: bold;
			margin: 10px 0;
		}
	}

	.withdraw_wrap {
		.item {
			margin-bottom: 10px;
			padding: 20px;

			.flex_center {
				justify-content: space-between;
			}

			.withdraw_money_tips {
				margin-bottom: 10px;
			}

			.money_mark {
				font-size: 18px;
				font-weight: bold;
				margin-left: 6px;
			}

			div:nth-child(1) {
				font-size: 14px;
				// font-weight: bold;
				color: #999;
			}

			// div:nth-child(2) {
			// 	text-align: right;
			// 	font-size: 14px;
			// 	font-weight: bold;
			// 	color: #999;
			// }
		}

	}
	.tips-show {
		width: 80%;
		margin: 0 auto;
		margin-top: 150px;
		text-align: center;
	}
	.tips-btn {
		height: 30px;
		line-height: 30px;
		width: 56%;
		margin: 0 auto;
		margin-top: 18px;
	}

	.withdraw_time_tips {
		padding: 10px 16px 14px 16px;
		line-height: 2;
		font-size: 12px;
		color: #f12211;
	}

	.btn {
		width: 80%;
		margin: 50px 0 0 10%;
	}

	/deep/ .van-cell {
		padding: 10px 16px 10px 10px;
	}

	/deep/ .van-action-sheet__item {
		text-align: left;
	}

	/deep/ .van-action-sheet__item {
		padding: 10px 16px;
		font-size: 14px;
	}

	/deep/ .van-dialog__message {
		text-align: left;
	}
	/deep/.van-action-sheet{
		padding-bottom: 10px;
	}
	/deep/ .van-dialog {
		width: 80%;
		height: 450px;
		background-color: transparent;
		background-repeat: no-repeat;
		background-size: contain;
		background-position: center;
		background-image: url(../img/user/withdraw_tips.png);
	}
	/deep/ .van-icon {
		position: absolute;
		top: 0;
		right: 0;
	}
</style>
