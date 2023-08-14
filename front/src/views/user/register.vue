<template>
	<div class="basic_wrap">
		<div class="back_left" @click="$router.back()">
			<img src="../img/common/back_b.png">
		</div>
		<!-- <div class="language" @click="$router.push('/language')">
			<img :src="language_logo">
		</div> -->
		<div class="logo">
			<img :src="config.logo" />
		</div>
		<!-- 非手机注册 start -->
		<div class="form_div" v-if="!config.register_phone">
			<form class="form">
				<div class="item">
					<input v-model.trim="data.username" type="text" class="inp" :placeholder="$t('login.phone')">
				</div>
				<div class="item">
					<input v-model.trim="data.password" :type="password" class="inp"
						:placeholder="$t('login.password')">
					<div class="eye_bi" :class="password == 'text' ? 'eye' : ''" @click="showPwd" />
				</div>
				<div class="item">
					<input v-if="config.invite_code" v-model.trim="data.invite_code" type="text" class="inp"
						:placeholder="$t('register.inviteCode')">
					<input v-if="!config.invite_code" v-model.trim="data.invite_code" type="text" class="inp"
						:placeholder="$t('register.inviteCodeOptional')">
				</div>
				<div class="item">
					<input v-model.trim="data.code" type="text" class="inp" :placeholder="$t('login.code')">
					<img style="height: 32px;margin-bottom: 8px;" :src="verify_img" @click="getVerifyCode()">
				</div>
				<div class="register_btn"
					:class="data.username == '' || data.password == '' || data.code == ''? 'no_touch' : ''"
					@click="submit">
					→
				</div>
			</form>
		</div>
		<!-- 非手机注册 end -->
		<!-- 手机注册 start -->
		<div class="form_div" v-if="config.register_phone">
			<form class="form">
				<div class="item">
					<van-dropdown-menu :overlay="false">
						<van-dropdown-item v-model="value" :options="country_code" />
					</van-dropdown-menu>
					<input v-model.trim="data.phone" type="number" class="inp" :placeholder="$t('login.phone')">
				</div>
				<div class="item">
					<input v-model.trim="data.smsCode" type="text" class="inp" :placeholder="$t('login.code')">
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
				<div class="item">
					<input v-model.trim="data.password" :type="password" class="inp"
						:placeholder="$t('login.password')">
					<div class="eye_bi" :class="password == 'text' ? 'eye' : ''" @click="showPwd" />
				</div>
				<div class="item">
					<input v-if="config.invite_code" v-model.trim="data.invite_code" type="text" class="inp"
						:placeholder="$t('register.inviteCode')">
					<input v-if="!config.invite_code" v-model.trim="data.invite_code" type="text" class="inp"
						:placeholder="$t('register.inviteCodeOptional')">
				</div>
				<div class="register_btn"
					:class="data.phone == '' || data.smsCode == ''|| data.password == ''? 'no_touch' : ''"
					@click="submit">
					→
				</div>
			</form>
		</div>
		<!-- 获取验证码需先验证 -->
		<van-popup v-model:show="show_sms_verify" :round="true" :style="{ width: '90%' }">
			<div class="sms_verify">
				<div>
					<img style="height: 40px;" :src="verify_img" @click="getVerifyCode()">
					<br><input v-model.trim="verify_code" type="text" class="inp" :placeholder="$t('auth.charEmpty')">
				</div>
				<div class="btn flex_center">
					<div @click="show_sms_verify=false;">{{$t('utils.cancel')}}</div>
					<div @click="getSmsCode()">{{$t('utils.confirm')}}</div>
				</div>
			</div>
		</van-popup>
		<!-- 手机注册 end -->
		<div class="login_register">
			<router-link to="/login">
				<div>
					{{$t('login.loginNow')}}
				</div>
			</router-link>
		</div>

		<div class="kefu" :class="show_kefu ? '' : 'kefu_hide'" @click="kefu_to">
			<img class="kefu_img" src="../img/index/kefu.png">
		</div>
	</div>
</template>

<script>
	import Vue from "vue";
	import bsHeader from '../../components/bsHeader.vue'
	import Fetch from "../../utils/fetch";
	import {
		CountDown,
		Checkbox,
		Dialog,
		DropdownMenu,
		DropdownItem,
		Popup
	} from "vant";
	Vue.use(CountDown).use(Checkbox).use(Dialog).use(DropdownMenu).use(DropdownItem).use(Popup);

	export default {
		name: "register",
		components: {
			bsHeader
		},
		data() {
			return {
				lang: this.$i18n.locale || "en_us",
				language_logo: localStorage.getItem('language_logo'),
				show_kefu: false,
				password: "password",
				loading: false,
				data: {
					username: "",
					password: "",
					invite_code: "",
					is_system: 0,
					code: "",
					smsCode: "",
					phone: "",
					country_code: ''
				},
				config: {
					invite_code: 0,
					register_phone: 1
				},
				verify_img: "",
				country_code: [],
				value: 0,
				time: 0,
				is_send: false,
				show_sms_verify: false,
				verify_code: ''
			};
		},
		created() {
			if (window.plus) {
				plus.navigator.setStatusBarBackground('#FAFAFA');
				plus.navigator.setStatusBarStyle('dark');
			}
			this.$parent.footer("user", false);
		},

		mounted() {
			var that = this;
			document.body.addEventListener(
				"scroll",
				function() {
					if (!that.show_kefu) {
						return;
					}
					that.show_kefu = false;
				},
				false
			);
			document.addEventListener(
				"click",
				function(ev) {
					if (ev.target.className != "kefu_img") {
						that.show_kefu = false;
					}
				},
				false
			);
			this.start();
			this.getLanguages();
			this.getparams();
			this.getVerifyCode();
		},
		methods: {
			start() {
				Fetch("/index/getWebInfo").then((r) => {
					this.config = r.data;
				});
			},
			getLanguages() {
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
				if (!this.data.phone) {
					this.$toast(this.$t('auth.phoneEmpty'));
					return false;
				}
				if (this.data.phone.length < 6 || this.data.phone.length > 16) {
					this.$toast(this.$t('auth.phoneError'));
					return;
				}
				this.show_sms_verify = true;
			},
			getSmsCode() {
				if (this.is_send) {
					return false;
				}
				if (!this.data.phone) {
					this.$toast(this.$t('auth.phoneEmpty'));
					return false;
				}
				if (this.data.phone.length < 6 || this.data.phone.length > 16) {
					this.$toast(this.$t('auth.phoneError'));
					return;
				}
				if (!this.verify_code) {
					this.$toast(this.$t('auth.charEmpty'));
					return false;
				}
				this.is_send = true;
				Fetch("/user/getSmsCode", {
					phone: this.data.phone,
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
					this.is_send = false;
					this.show_sms_verify = false;
					this.getVerifyCode();
				});
			},
			kefu_to() {
				if (this.show_kefu) {
					this.$router.push("/service");
				}
				this.show_kefu = !this.show_kefu;
			},
			showPwd() {
				if (this.password == "password") {
					this.password = "text";
				} else {
					this.password = "password";
				}
			},
			getparams() {
				var invite_code = this.$route.query.code;
				var is_system = this.$route.query.is_system | 0;
				this.data.invite_code = invite_code;
				this.data.is_system = is_system;
			},
			getQueryVariable(variable)
			{
				var query = window.location.search.substring(1);
				var vars = query.split("&");
				for (var i=0;i<vars.length;i++) {
					var pair = vars[i].split("=");
					if(pair[0] == variable){return pair[1];}
				}
				return(false);
			},
			submit() {
				if (this.loading) {
					return false;
				}
				//数字、字母或下划线
				var reg1 = /^[0-9a-zA-Z_]{1,}$/;
				//非手机号注册判断
				if (!this.config.register_phone) {
					if (!this.data.username) {
						this.$toast(this.$t('login.usernameEmpty'));
						return false;
					}
					if (!reg1.test(this.data.username)) {
						this.$toast(this.$t('login.usernameLimit'));
						return false;
					}
					if (this.data.username.length > 16 || this.data.username.length < 4) {
						this.$toast(this.$t('login.usernameLength'));
						return false;
					}
				}
				//手机号注册判断
				else {
					if (!this.data.phone) {
						this.$toast(this.$t('auth.phoneEmpty'));
						return false;
					}
					if (this.data.phone.length < 6 || this.data.phone.length > 16) {
						this.$toast(this.$t('auth.phoneError'));
						return;
					}
					if (!this.data.smsCode) {
						this.$toast(this.$t('login.codeEmpty'));
						return false;
					}
					this.data.country_code = this.country_code[this.value]['text'];
				}
				if (!this.data.password) {
					this.$toast(this.$t('login.passwordEmpty'));
					return false;
				}
				if (!reg1.test(this.data.password)) {
					this.$toast(this.$t('login.passwordLimit'));
					return false;
				}
				if (this.data.password.length > 16 || this.data.password.length < 6) {
					this.$toast(this.$t('login.passwordLength'));
					return false;
				}
				if (this.config.invite_code && !this.data.invite_code) {
					this.$toast(this.$t('register.inviteCodeEmpty'));
					return false;
				}
				if (!this.config.register_phone && !this.data.code) {
					this.$toast(this.$t('login.codeEmpty'));
					return false;
				}
				this.loading = true;
				Fetch("/index/register", {
					...this.data,
				}).then((res) => {
					if (res.data.token) {
						localStorage.setItem('token', res.data.token);
					}
					this.$toast(this.$t('register.registerSuccess'));
					this.$router.replace("/");
				}).catch((res) => {
					this.getVerifyCode();
					this.loading = false;
				});
			},
			getVerifyCode() {
				Fetch("/index/captcha").then((r) => {
					this.verify_img = r.data.image;
				});
			}
		},
	};
</script>

<style lang="less" scoped>
	.basic_wrap{
		position: relative;
	}
	.form_div {
		height: 600px;
		width: 100%;
		background: url(../img/user/login_background.png) no-repeat center center;
		background-size: 150% 100%;
		position: relative;
		top: -88px;

		.form {
			position: absolute;
			margin: 200px auto 0 5%;
			width: 90%;

			.register_btn {
				background: #68326C;
				color: #FFFFFF;
				width: 60px;
				height: 60px;
				border-radius: 50%;
				float: right;
				margin-right: 3%;
				text-align: center;
				line-height: 60px;
				font-size: 36px;
				font-weight: bold;
			}
		}
	}

	.back_left {
		background: unset;

		img {
			width: 20px;
			position: fixed;
			top: 10px;
			left: 10px;
		}
	}

	.language {
		position: absolute;
		top: 6px;
		right: 6px;
		height: 30px;
		z-index: 11;

		img {
			height: 100%;
		}
	}

	.logo {
		margin: 50px 0 0 30px;

		img {
			height: 80px;
		}
	}

	.item {
		height: 50px;
		font-size: 14px;
		display: flex;
		justify-content: flex-start;
		align-items: flex-end;
		padding-bottom: 4px;
		background: #FFFFFF;
		margin-bottom: 15px;
		border-radius: 25px;
		padding: 0 20px;

		input {
			height: 50px;
			flex: 1;
		}

		.get_captcha {
			line-height: 50px;
		}

		.eye_bi {
			margin-bottom: 12px;
		}
	}

	.sms_verify {
		padding: 20px 20px 0 20px;

		input {
			margin-top: 15px;
			height: 20px;
			font-size: 16px;
			width: 100%;
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

	.login_register {
		position: relative;
		top: -60px;
		font-size: 14px;
		margin-left: 50px;
	
	}

	/deep/ .van-dropdown-menu__bar {
		background-color: unset;
		box-shadow: unset;
		height: 50px;
		margin-right: 10px;
	}

	/deep/ .van-popup--top {
		left: 5%;
		width: 30%;
	}
</style>
