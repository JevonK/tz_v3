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
		<div class="form_div">
			<form class="form">
				<div v-if="!config.register_phone" class="item">
					<input v-model.trim="data.username" type="text" class="inp" :placeholder="$t('login.phone')">
				</div>
				<div v-if="config.register_phone" class="item">
					<input v-model.trim="data.username" type="number" class="inp" :placeholder="$t('login.phone')">
				</div>
				<div class="item">
					<input v-model.trim="data.password" :type="password" class="inp" :placeholder="$t('login.password')">
					<div class="eye_bi" :class="password == 'text' ? 'eye' : ''" @click="showPwd" />
				</div>
				<div class="item">
					<input v-model.trim="data.code" type="text" class="inp" :placeholder="$t('login.code')">
					<img style="height: 32px;margin-bottom: 8px;" :src="verify_img" @click="getVerifyCode()">
				</div>
				<!-- <van-button icon="plus" type="primary">按钮</van-button> -->
				<div class="register_btn"
					 :class="data.username == '' || data.password == '' || data.code == ''? 'no_touch' : ''" @click="submit">
					Login
				</div>
			</form>
			<div class="login_register">
					<router-link to="/register">
						<div>
							{{$t('login.registerNow')}}
						</div>
					</router-link>
				</div>
		</div>

		
		
		<div class="kefu" :class="show_kefu ? '' : 'kefu_hide'" @click="kefu_to">
			<img class="kefu_img" src="../img/index/kefu.png">
		</div>
	</div>
</template>

<script>
	import Vue from "vue";
	import bsHeader from '../../components/bsHeader.vue'
	import {
		CountDown,
		Checkbox,
		Dialog,
		Button
	} from "vant";
	import Fetch from "../../utils/fetch";

	Vue.use(CountDown)
		.use(Checkbox)
		.use(Button)
		.use(Dialog);

	export default {
		name: "login",
		components: {
			bsHeader
		},
		data() {
			return {
				language_logo: localStorage.getItem('language_logo'),
				show_kefu: false,
				password: "password",
				loading: false,
				data: {
					username: "",
					password: "",
					code: "",
				},
				config: {
					register_phone: 1
				},
				verify_img: "",
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
			// if (this.$route.query.agent) {
			// 	localStorage.setItem('agent', this.$route.query.agent);
			// 	this.data.agent = this.$route.query.agent;
			// } else {
			// 	if (localStorage.getItem('agent')) {
			// 		this.data.agent = localStorage.getItem('agent');
			// 	}
			// }
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
			this.getVerifyCode();
		},
		methods: {
			start() {
				Fetch("/index/getWebInfo").then((r) => {
					this.config = r.data;
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
					if (!this.data.username) {
						this.$toast(this.$t('auth.phoneEmpty'));
						return false;
					}
					if (this.data.username.length < 6 || this.data.username.length > 16) {
						this.$toast(this.$t('auth.phoneError'));
						return;
					}
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
				if (!this.data.code) {
					this.$toast(this.$t('login.codeEmpty'));
					return false;
				}
				this.loading = true;
				Fetch("/index/login", {
					...this.data,
				}).then((res) => {
					if (res.data.token) {
						localStorage.setItem('token', res.data.token);
					}
					this.$toast(this.$t('login.loginSuccess'));
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
		// background: url(../img/user/login_background.png) no-repeat center center;
		background-size: 150% 100%;
		position: relative;
		top: -88px;

		.form {
			position: absolute;
			margin: 120px auto 0 5%;
			width: 90%;

			.register_btn {
				background: #333333;
    			color: #FFFFFF;
				width: 100%;
				height: 50px;
				border-radius: 32px;
				float: right;
				margin-right: 3%;
				text-align: center;
				line-height: 50px;
				font-size: 19px;
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
		margin: 50px 0 0 0;
		text-align: center;

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
		border: 1px solid #EEEEEE;

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
		position: absolute;
		// top: -60px;
		font-size: 17px;
		color: #333333;
		// margin-left: 50px;
		margin-top: 100%;
    	margin-left: 4%;
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
