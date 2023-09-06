<template>
	<div class="basic_wrap">
		<bsHeader :title="$t('wallet.walletCenter')" @backurl="$router.back()"></bsHeader>
		<div class="wallet_wrap">
			<div class="wallet_item">
				<div class="block_div item" v-for="(item,index) in withdrawMethod" :key="index">
					<van-cell>
						<template #title>
							<div class="flex_center detail">
								<div class="flex_center">
									<div>
										<img :src="item.logo" alt="">
									</div>
									<div>
										<p>{{item.name}}</p>
										<p class="bind_tips">{{item.tips}}</p>
									</div>
								</div>
								<div v-if="item.type==4" @click="$router.push('/wallet/bank/'+item.id)">
									<p class="bind" v-if="!item.status">{{$t('wallet.toBind')}}</p>
								</div>
								<div v-if="item.type!=4" @click="$router.push('/wallet/qrcode/'+item.id)">
									<p class="bind" v-if="!item.status">{{$t('wallet.toBind')}}</p>
								</div>
								<van-icon v-if="item.status" name="close" color="red" @click="delWallet(item.wallet_id)" size="20"/>
							</div>
						</template>
					</van-cell>
				</div>
				<div class="tips">
					<p class="tips1"><span>*</span>{{$t('wallet.walletTips1')}}</p>
					<p class="tips2"><span>*</span>{{$t('wallet.walletTips2')}}</p>
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
		Tag,
		Icon,
		Toast
	} from "vant";

	Vue.use(Cell).use(Tag).use(Icon).use(Toast);
	export default {
		name: "wallet",
		components: {
			bsHeader
		},
		data() {
			return {
				data: [],
				withdrawMethod: [],
				wallets: []
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
			Fetch('/user/getWithdrawalMethod').then(r => {
				this.withdrawMethod = r.data.withdrawMethod;
				this.wallets = r.data.wallets;
				for (var i = 0; i < this.withdrawMethod.length; i++) {
					this.withdrawMethod[i]['status'] = 0;
					this.withdrawMethod[i]['tips'] = this.$t('wallet.bind') + this.withdrawMethod[i]['name'];
					for (var j = 0; j < this.wallets.length; j++) {
						if (this.wallets[j]['wid'] == this.withdrawMethod[i]['id']) {
							this.withdrawMethod[i]['status'] = 1;
							this.withdrawMethod[i]['wallet_id'] = this.wallets[j]['id'];
							this.withdrawMethod[i]['tips'] = this.wallets[j][
								'account'
							];
							if (this.withdrawMethod[i]['type'] == 4) {
								this.withdrawMethod[i]['name'] = this.wallets[j][
									'wname'
								];
								if (this.wallets[j]['bank']) {
									this.withdrawMethod[i]['logo'] = this.wallets[j]['bank']['logo'];
								}
							}
						}
					}
				}
			})
		},
		methods: {
			delWallet(id) {
				this.$dialog.alert({
					closeOnClickOverlay: true,
					showConfirmButton: true,
					showCancelButton: true,
					cancelButtonText: "Cancel",
					confirmButtonText: "Confirm",
					title: "Delete withdraw account",
					message: "<p style='text-align: left'>Are you sure you want to delete the withdrawal account?</p>",
				}).then(() => {
					// on confirm
					Toast.loading({
						forbidClick: true,
						duration: 20000
					});
					Fetch('/user/delWallets', {id}).then(r => {
						Toast.clear();
						window.location.reload();
					})
				}).catch(() => {
					// on close
				});
				
			}
		}
	};
</script>

<style lang="less" scoped>
	.wallet_item {
		margin-top: 54px;

		.item {
			margin-bottom: 10px;

			.detail {
				justify-content: space-between;

				img {
					vertical-align: middle;
					height: 50px;
					width: 50px;
					margin-right: 10px;
				}

				.bind_tips {
					font-size: 12px;
					color: #999;
				}

				.bind {
					color: #999;
				}
			}

		}

		.tips {
			line-height: 2;
			width: 92%;
			margin: 20px 0 0 4%;
			font-size: 14px;

			span {
				margin-right: 5px;
			}

			.tips1 {
				color: #FF0000;
			}
		}
	}
</style>
