<template>
	<div class="basic_wrap">
		<bsHeader :title="account.name" @backurl="$router.back()"></bsHeader>
		<div class="bind_wrap">
			<div class="bind_item">
				<p class="item_name">{{accountTips}}</p>
				<div class="block_div item_input">
					<van-field v-model="account.account" type="text" :placeholder="accountPlaceholder" />
				</div>
			</div>
			<div class="bind_item">
				<p class="item_name">{{$t('wallet.qrcode')}}</p>
				<div class="block_div item_qrcode">
					<van-uploader v-model="fileList" multiple :max-count="1" class="upload" :max-size="2 * 1024 * 1024"
						@oversize="onOversize" :after-read="afterRead" :before-read="beforeRead">
					</van-uploader>
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
		Uploader,
		Toast
	} from "vant";

	Vue.use(Field).use(CellGroup).use(Uploader).use(Toast);
	import Api from "../../interface/index";
	import axios from 'axios'
	export default {
		name: "withdraw_bank",
		components: {
			bsHeader
		},
		data() {
			return {
				data: [],
				fileList: [],
				account: {
					id: '',
					name: '',
					account: '',
					img: ""
				},
				accountTips: "",
				accountPlaceholder: "",
				accountError: ""
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
					this.account.name = r.data.withdraw.name;
					switch (r.data.withdraw.type) {
						case 1:
							this.accountTips = this.$t('wallet.address');
							this.accountPlaceholder = this.$t('wallet.addressPlaceholder');
							this.accountError = this.$t('wallet.addressError');
							break;
						case 2:
							this.accountTips = this.$t('wallet.alipay');
							this.accountPlaceholder = this.$t('wallet.alipayPlaceholder');
							this.accountError = this.$t('wallet.alipayError')
							break;
						case 3:
							this.accountTips = this.$t('wallet.wechat');
							this.accountPlaceholder = this.$t('wallet.wechatPlaceholder');
							this.accountError = this.$t('wallet.wechatError')
							break;
					}
				})
			},
			submit() {
				if (this.account.account.length > 34 || this.account.account.length < 2) {
					this.$toast(this.accountError);
					return false;
				}
				if (this.account.img == "") {
					this.$toast(this.$t('wallet.qrcodeEmpty'));
					return false;
				}

				Fetch('/user/setWallet', {
					...this.account,
				}).then(r => {
					this.$router.replace('/user');
				})
			},
			onOversize(file) {
				this.$toast(this.$t('wallet.qrcodeLong'));
			},
			beforeRead(file) {
				if (file.type !== 'image/jpeg' && file.type !== 'image/jpg' && file.type !== 'image/png') {
					this.$toast(this.$t('wallet.qrcodeError'));
					return false;
				}
				return true;
			},
			afterRead(file) {
				let formData = new FormData();
				formData.append('language', this.$i18n.locale || "en_us");
				formData.append('file', file.file);
				formData.append('token', localStorage.getItem('token'));
				Toast.loading({
					forbidClick: true,
					duration: 20000
				});
				axios.post(Api.commonUrl + "/api/user/imgUpload", formData).then((r) => {
					Toast.clear();
					if (r.data.code === 1) {
						this.account.img = r.data.data;
					} else {
						this.$toast(this.$t(r.data.info));
					}
				});
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

			.item_qrcode {
				height: 150px;
				text-align: center;
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

	/deep/ .van-uploader__preview-image {
		width: 150px;
		height: 150px;
	}

	/deep/ .van-uploader__upload {
		background-color: unset;
		width: 150px;
		height: 150px;
	}

	// /deep/ .van-uploader {
	// 	background: url(../img/user/qrcode.png) no-repeat center center;
	// 	background-size: auto 60%;
	// 	opacity: 0.2;
	// }

	// /deep/ .van-icon-photograph:before {
	// 	content: unset;
	// }
</style>
