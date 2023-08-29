<template>
	<div class="basic_wrap">
		<div class="red_top_bg">
			<div class="back_left" @click="$router.back()"></div>
		</div>
		<div class="withdraw_wrap">
			<van-action-sheet v-model="show" :actions="wallets" cancel-text=""
				:description="$t('withdraw.accountPlaceholder')" close-on-click-action @select="onSelect" />
			<div class="block_div item">
				<div class="">
					<p class="withdraw_money_tips">Reward Redemption</p>
					<div class="flex_center">
						<van-field v-model="wallet.money" type="text"
							:placeholder="'Please Enter Reward Code'" />
					</div>
				</div>
			</div>
		</div>
		<div class="basic_btn btn" :class="wallet.money?'':'no_touch'" @click="submit()">
			Receive
		</div>
		<van-dialog v-model:show="show_tips" title="" :show-confirm-button="false">
			<div class="tips-show">
				<span class="tips-span">{{ show_money }}</span><br>
				<van-icon name="close" color="#fff" size="40" @click="show_tips=false" />
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
				show_money: "",
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
			// showDialog({
			// 	message: '生命远不止连轴转和忙到极限，人类的体验远比这辽阔、丰富得多。',
			// 	theme: 'round-button',
			// }).then(() => {
			// // on close
			// });
		},
		methods: {
			onSelect(item) {
				if (item.value == 0) {
					this.$router.push('/wallet')
				}
				this.withdraw_name = item.name;
				this.wallet.id = item.value;
			},
			changeAmount() {},
			submit() {
				if (this.wallet.money == "") {
					this.$toast("Please fill in the red envelope code");
					return false;
				}
				Fetch('/user/red_envelope_redemption', {
					'code' : this.wallet.money,
				}).then(r => {
					if (r.code == 1) {
						this.show_money = r.data.money
						this.show_tips = true;
					}
					// this.$router.replace('/user');
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
			margin-top: 66px;

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
	.tips-show {
		width: 80%;
		margin: 0 auto;
		margin-top: 150px;
		text-align: center;
	}
	.tips-span {
		color: #FD4B2F;
		font-size: 57px;
    	font-weight: bold;
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
		background-image: url(../img/user/red_envelope.png);
	}
	/deep/ .van-icon {
		margin-top: 200px;
	}
</style>
