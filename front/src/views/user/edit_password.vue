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
					<p class="withdraw_money_tips">Change password</p>
					<div class="flex_center">
						<van-field v-model="wallet.old_password" label="Old password" type="password"
							:placeholder="'Please enter a old password'" />
					</div>
				</div>
				<div class="">
					<div class="flex_center">
						<van-field v-model="wallet.new_password" label="New password" type="password"
							:placeholder="'Please enter a new password'" />
					</div>
				</div>
				<div class="">
					<div class="flex_center">
						<van-field v-model="wallet.verify_password" label="Verify new password" type="password"
							:placeholder="'Please enter a verify new password'" />
					</div>
				</div>
			</div>
		</div>
		<div class="basic_btn btn"  @click="submit()">
			Submit
		</div>
	</div>
</template>

<script>
	import Vue from 'vue';
	import bsHeader from '../../components/bsHeader.vue'
	import Fetch from '../../utils/fetch'
	import {
		Icon,
		ActionSheet,
		Field
	} from 'vant';
	Vue.use(Icon).use(ActionSheet).use(Field);
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
				wallets: [],
				wallet: {
					old_password: '',
					new_password: '',
					verify_password: '',
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
			this.start()
		},
		methods: {
			start() {
				var isapp = 0;
				if (window.plus) {
					isapp = 1;
				}
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
				if (this.wallet.new_password == "" || this.wallet.old_password == "" || this.wallet.verify_password == "") {
					this.$toast("Please enter a password");
					return false;
				}
				if (this.wallet.new_password != this.wallet.verify_password) {
					this.$toast("The new password does not match the verified password");
					return false;
				}
				Fetch('/user/edit_password', {
					...this.wallet,
				}).then(r => {
					this.$router.replace('/user');
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
</style>
