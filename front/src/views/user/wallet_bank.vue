<template>
	<div class="basic_wrap">
		<bsHeader title="" @backurl="$router.back()"></bsHeader>
		<div class="bind_wrap">
			<div class="bind_item">
				<p class="item_name">{{$t('wallet.bank')}}</p>
				<div class="block_div item_input">
					<!-- <van-field v-model="account.bank" type="text" :placeholder="$t('wallet.bank')" /> -->

					<van-dropdown-menu>
						<van-dropdown-item v-model="value1" :options="option1" />
					</van-dropdown-menu>
				</div>
			</div>
			<div class="bind_item">
				<p class="item_name">{{$t('wallet.name')}}</p>
				<div class="block_div item_input">
					<van-field v-model="account.name" type="text" :placeholder="$t('wallet.namePlaceholder')" />
				</div>
			</div>
			<div class="bind_item">
				<p class="item_name">{{$t('wallet.account')}}</p>
				<div class="block_div item_input">
					<van-field v-model="account.account" type="digit" :placeholder="$t('wallet.accountPlaceholder')" />
				</div>
			</div>
			<div class="tips">
				<p class="tips1"><span>*</span>{{$t('wallet.walletTips1')}}</p>
				<p class="tips2"><span>*</span>{{$t('wallet.walletTips2')}}</p>
			</div>
			<div class="basic_btn btn" :class="account.name==''||account.account==''?'no_touch':''" @click="submit()">
				{{$t('wallet.bindNow')}}
			</div>
		</div>
	</div>
</template>

<script>
	import Vue from 'vue';
	import bsHeader from '../../components/bsHeader.vue'
	import Fetch from '../../utils/fetch'
	import {
		Field,
		CellGroup,
		DropdownMenu,
		DropdownItem
	} from "vant";

	Vue.use(Field).use(CellGroup).use(DropdownMenu).use(DropdownItem);
	export default {
		name: "wallet_bank",
		components: {
			bsHeader
		},
		data() {
			return {
				data: [],
				account: {
					id: '',
					bank: '',
					name: '',
					account: '',
					bank_id:''
				},
				value1: 0,
				option1: []
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
				Fetch('/user/getWithdrawMethodById', {
					id: this.$router.history.current.params.code
				}).then(r => {
					this.account.id = r.data.withdraw.id;
					var banks = r.data.withdraw.banks;
					for (var i = 0; i < banks.length; i++) {
						this.option1.push({
							text: banks[i].name,
							value: i,
							code: banks[i].code,
							id: banks[i].id
						})

					}
				})
			},
			submit() {
				this.account.bank = this.option1[this.value1]['text'];
				this.account.bank_id = this.option1[this.value1]['id'];
				if (this.account.name.length > 60 || this.account.name.length < 2) {
					this.$toast(this.$t('wallet.nameError'));
					return false;
				}
				if (this.account.account.length > 30 || this.account.account.length < 6) {
					this.$toast(this.$t('wallet.accountError'));
					return false;
				}
				//纯数字
				var reg1 = /^[0-9]*$/;
				if (!reg1.test(this.account.account)) {
					this.$toast(this.$t('wallet.accountError'));
					return false;
				}
				Fetch('/user/setWallet', {
					...this.account,
				}).then(r => {
					this.$router.replace('/user');
				})

			}
		}
	};
</script>

<style lang="less" scoped>
	.bind_wrap {
		margin-top: 60px;

		.bind_item {
			padding: 20px 0 0 0;

			.item_name {
				color: #999;
				margin: 0 0 5px 5%;
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

		.btn {
			margin-top: 50px;
		}
	}

	/deep/ .van-dropdown-menu__bar {
		height: 44px;
		background-color: unset;
		box-shadow: unset;
		padding-left: 10px;
	}

	/deep/ .van-dropdown-menu__item {
		justify-content: left;

	}

	/deep/ .van-popup--top {
		width: 96%;
		margin-left: 2%;
	}

	/deep/ .van-overlay {
		background-color: unset;
	}
</style>
