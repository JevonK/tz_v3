<template>
	<div class="basic_wrap">
		<bsHeader :title="$t('recharge.recharge')" @backurl="$router.back()"></bsHeader>
		<div class="recharge_wrap">
			<div class="block_div flex_center recharge_method_wrap">
				<img :src="recharge.logo">
				<p>{{recharge.name}}</p>
			</div>
			<div class="block_div recharge_detail_wrap">
				<img :src="recharge.img">
				<!-- <div class="save_img">保存二维码</div> -->
				<div class="address" v-if="recharge.type==1||recharge.type==5||recharge.type==6">
					<p>{{$t('recharge.address')}}</p>
					<div class="flex_center copy" v-clipboard="()=>recharge.account" v-clipboard:success="copy">
						<p>{{recharge.account}}</p>
						<img class="copy_img" src="../img/user/copy.png">
					</div>
				</div>
			</div>
			<div class="block_div recharge_voucher_wrap">
				<div class="recharge_money">
					<p>{{$t('recharge.money')}}</p>
					<div>
						<span class="money">{{common.currency_symbol_basic()}}{{common.precision_basic(money)}}</span>
					</div>
				</div>
				<div class="" v-if="recharge.type==5">
					<p>Hash</p>
					<div class="">
						<van-field v-model="hash" type="text" :placeholder="$t('recharge.hashEmpty')" />
					</div>
				</div>
				<div class="" v-if="recharge.type==1||recharge.type==2||recharge.type==3||recharge.type==4">
					<p>{{$t('recharge.voucher')}}</p>
					<div class="qrcode">
						<van-uploader v-model="fileList" multiple :max-count="1" class="upload"
							:max-size="2 * 1024 * 1024" @oversize="onOversize" :after-read="afterRead"
							:before-read="beforeRead">
						</van-uploader>
					</div>
				</div>
			</div>
			<div class="tips" v-if="recharge.type!=6">
				<p class="tips1"><span>*</span>{{$t('recharge.tips1')}}</p>
			</div>
			<div class="recharge_btn_wrap" v-if="recharge.type!=6">
				<div class="basic_btn btn" @click="submit">
					{{$t('recharge.submit')}}
				</div>
			</div>
		</div>
	</div>

</template>

<script>
	import Vue from 'vue';
	import bsHeader from '../../components/bsHeader.vue'
	import Fetch from '../../utils/fetch'
	import Api from "../../interface/index";
	import axios from 'axios'
	import Clipboard from "v-clipboard";
	import {
		Uploader,
		Toast,
		Field
	} from "vant";

	Vue.use(Uploader).use(Toast).use(Field).use(Clipboard);
	export default {
		name: "recharge_qrcode",
		components: {
			bsHeader
		},
		data() {
			return {
				currency:'',
				rate: 1,
				data: [],
				fileList: [],
				recharge: [],
				id: this.$route.query.id,
				money: this.$route.query.money,
				voucher: '',
				hash: ''
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
				Fetch('/user/getRechargeById', {
					id: this.id,
					money: this.money
				}).then(r => {
					this.recharge = r.data.recharge;
					this.rate = r.data.rate;
					this.currency = r.data.currency;
				})
			},
			submit() {
				if (this.money == "") {
					this.$toast(this.$t('recharge.moneyEmpty'));
					return false;
				}
				if (this.money <= 0) {
					this.$toast(this.$t('recharge.moneyError'));
					return false;
				}
				if (this.recharge.type != 5 && this.voucher == '') {
					this.$toast(this.$t('recharge.voucherEmpty'));
					return false;
				}
				if (this.recharge.type == 5 && this.hash == '') {
					this.$toast(this.$t('recharge.hashEmpty'));
					return false;
				}
				Fetch('/user/recharge', {
					id: this.id,
					money: this.money,
					voucher: this.voucher,
					hash: this.hash
				}).then(r => {
					this.$router.replace('/recharge/record');
				})
			},
			copy() {
				this.$toast(this.$t('recharge.copySuccess'));
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
						this.voucher = r.data.data;
					} else {
						this.$toast(this.$t(r.data.info));
					}
				});
			}
		}
	};
</script>

<style lang="less" scoped>
	.recharge_wrap {
		margin-top: 54px;
	}

	.recharge_method_wrap {
		justify-content: left;
		padding: 15px 20px;
		margin-bottom: 10px;

		img {
			width: 32px;
			height: 32px;
			margin-right: 10px;
		}
	}

	.recharge_detail_wrap {
		padding: 20px;
		margin-bottom: 10px;
		text-align: center;

		img {
			max-width: 180px;
			max-height: 180px;
		}

		.save_img {
			border: 1px solid #dfdfdf;
			padding: 10px;
			width: 50%;
			margin-left: 25%;
			border-radius: 34px;
		}

		.address {
			margin-top: 20px;

			p:nth-child(1) {
				color: #999;
				font-size: 14px;
				margin-bottom: 10px;
			}

			.copy {
				p {
					margin-top: 8px;
				}
			}
		}
	}

	.recharge_voucher_wrap {
		margin-bottom: 10px;
		padding: 20px;

		.recharge_money {
			margin-bottom: 20px;

			p {
				margin-bottom: 10px;

			}

			.money {
				font-size: 18px;
				font-weight: bold;
			}

			.currency {
				color: #999;
			}
		}

		p {
			color: #999;
		}

		.qrcode {
			text-align: center;
			margin-top: 20px;
		}
	}

	.recharge_btn_wrap {
		margin-top: 30px;
	}

	.copy_img {
		width: 20px;
		height: 20px;
		margin-left: 3px;
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
</style>
