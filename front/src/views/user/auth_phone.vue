<template>
	<div class="basic_wrap">
		<bsHeader :title="$t('auth.authPhone')" @backurl="$router.back()"></bsHeader>
		<div class="block_div auth_wrap">
			<form class="form" @submit.prevent="submit">
				<div class="tittle_verified">
					{{$t('auth.authPhone')}}
				</div>
				<div class="tittle_verified_tips">
					{{$t('auth.authPhoneTip')}}
				</div>
				<div class="item">
					<van-dropdown-menu :overlay="false">
						<van-dropdown-item v-model="value" :options="country_code" />
					</van-dropdown-menu>
					<input v-model.trim="phone" type="text" class="inp" :placeholder="$t('auth.phoneEmpty')">
				</div>
				<div class="item">
					<input v-model.trim="code" type="text" class="inp" :placeholder="$t('auth.phoneCode')">
					<van-count-down :time="time" class="btn get_captcha" @finish="timeCall">
						<template v-slot="timeData">
							<span @click="sendCode">{{
			        timeData.seconds > 0
			          ? timeData.seconds
			          : $t('auth.sendPhoneCode')
			      }}</span>
						</template>
					</van-count-down>
				</div>
				<button type="submit" class="basic_btn sbtn"
					:class="phone==''||code==''?'no_touch':''">{{$t('auth.submit')}}</button>
			</form>
			<!-- 获取验证码需先验证 -->
			<van-popup v-model:show="show_sms_verify" :round="true" :style="{ width: '90%' }">
				<div class="sms_verify">
					<div>
						<img style="height: 40px;" :src="verify_img" @click="getVerifyCode()">
						<br><input v-model.trim="verify_code" type="text" class="inp"
							:placeholder="$t('auth.charEmpty')">
					</div>
					<div class="btn flex_center">
						<div @click="show_sms_verify=false;">{{$t('utils.cancel')}}</div>
						<div @click="getSmsCode()">{{$t('utils.confirm')}}</div>
					</div>
				</div>
			</van-popup>
		</div>
	</div>
</template>

<script>
	import Vue from "vue";

	import Fetch from '../../utils/fetch';
	import bsHeader from '../../components/bsHeader.vue';
	import {
		CountDown,
		Checkbox,
		DropdownMenu,
		DropdownItem,
		Popup
	} from "vant";
	Vue.use(CountDown).use(Checkbox).use(DropdownMenu).use(DropdownItem).use(Popup);
	export default {
		name: "setpwd",
		components: {
			bsHeader
		},
		data() {
			return {
				lang: this.$i18n.locale || "en_us",
				data: {},
				country_code: [],
				value: 0,
				time: 0,
				phone: '',
				code: '',
				verify_img: "",
				is_send: false,
				show_sms_verify: false,
				verify_code: ''
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
			this.getVerifyCode();
			this.start();
		},
		methods: {
			start() {
				Fetch('/index/getLanguages').then((r) => {
					var list = r.data.list;
					var countryCode = [];
					for (var i = 0; i < list.length; i++) {
						countryCode.push({
							text: list[i]['country_code'],
							value: i,
							counrty: list[i]['country']
						});
					}
					this.country_code = countryCode;
					let country = this.country_code.find((country) => {
						return country['counrty'] === this.lang;
					});
					this.value = country.value;
				});
			},
			timeCall() {
				this.is_send = false;
				this.time = 0;
			},
			sendCode() {
				this.verify_code = '';
				if (this.is_send) {
					return false;
				}
				if (!this.phone) {
					this.$toast(this.$t('auth.phoneEmpty'));
					return false;
				}
				if (this.phone.length < 6 || this.phone.length > 16) {
					this.$toast(this.$t('auth.phoneError'));
					return;
				}
				this.show_sms_verify = true;
			},
			getSmsCode() {
				if (this.is_send) {
					return false;
				}
				if (!this.phone) {
					this.$toast(this.$t('auth.phoneEmpty'));
					return false;
				}
				if (this.phone.length < 6 || this.phone.length > 16) {
					this.$toast(this.$t('auth.phoneError'));
					return;
				}
				if (!this.verify_code) {
					this.$toast(this.$t('auth.charEmpty'));
					return false;
				}
				this.is_send = true;
				Fetch("/user/getSmsCode", {
					phone: this.phone,
					country_code: this.country_code[this.value]['text'],
					verify_code: this.verify_code
				}).then(() => {
					this.show_sms_verify = false;
					this.time = 60 * 1000;
					this.$toast({
						background: "#07c160",
						message: this.$t('auth.sendsuccess'),
					});
				}).catch(() => {
					this.show_sms_verify = false;
					this.is_send = false;
					this.getVerifyCode();
				});
			},
			submit() {
				if (!this.phone) {
					this.$toast(this.$t('auth.phoneEmpty'));
					return false;
				}
				if (this.phone.length < 6 || this.phone.length > 16) {
					this.$toast(this.$t('auth.phoneError'));
					return;
				}
				if (!this.code) {
					this.$toast(this.$t('auth.codeEmpty'));
					return false;
				}
				Fetch('/user/authPhone', {
					phone: this.phone,
					code: this.code,
					country_code: this.country_code[this.value]['text']
				}).then(() => {
					this.$router.replace('/user')
				});
			},
			getVerifyCode() {
				Fetch("/index/captcha").then((r) => {
					this.verify_img = r.data.image;
				});
			}
		}
	};
</script>

<style lang="less" scoped>
	.auth_wrap {
		margin-top: 60px;
		padding: 20px 0;
	}

	.auth_1 {
		text-align: center;
		margin-top: 50%;

		p {
			margin-top: 40px;
			font-size: 20px;
			color: green;
			font-weight: bold;
		}
	}

	.tittle_verified {
		margin-top: 10px;
		margin-bottom: 20px;
		font-size: 26px;
	}

	.tittle_verified_tips {
		line-height: 1.5;
	}

	.form {
		width: 100%;
		// margin-top: 65px;
		padding: 0 20px;

		.item {
			width: 100%;
			height: 45px;
			border-radius: 2px;
			border-bottom: 1px solid rgba(0, 0, 0, 0.1);
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin: 40px 0;

			input {
				flex: 1;
				padding: 0 10px;
				height: 22px;
				font-size: 16px;
				line-height: 22px;
			}
		}
	}

	.sms_verify {
		padding: 20px 20px 0 20px;

		input {
			margin-top: 15px;
			height: 20px;
			font-size: 16px;
		}

		.btn {
			margin-top: 15px;
			justify-content: space-around;
			color: #ee0a24;
			height: 48px;
			font-size: 16px;
			border-top: 1px solid #ebedf0;

			div {
				width: 50%;
				text-align: center;
			}

			div:nth-child(1) {
				border-right: 1px solid #ebedf0;
			}
		}
	}

	.sbtn {
		margin-top: 10px;
	}

	/deep/ .van-dropdown-menu__bar {
		background-color: unset;
		box-shadow: unset;
	}

	/deep/ .van-popup--top {
		left: 2%;
		width: 30%;
	}
</style>
