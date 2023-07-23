<template>
	<div class="basic_wrap">
		<bsHeader :title="$t('auth.authEmail')" @backurl="$router.back()"></bsHeader>
		<div class="block_div auth_wrap">
			<form class="form" @submit.prevent="submit">
				<div class="tittle_verified">
					{{$t('auth.authEmail')}}
				</div>
				<div class="tittle_verified_tips">
					{{$t('auth.authEmailTip')}}
				</div>
				<div class="item">
					<input v-model.trim="email" type="text" class="inp" :placeholder="$t('auth.emailEmpty')">
				</div>
				<div class="item">
					<input v-model.trim="code" type="text" class="inp" :placeholder="$t('auth.emailCode')">
					<van-count-down :time="time" class="btn get_captcha" @finish="timeCall">
						<template v-slot="timeData">
							<span @click="sendcode">{{
			        timeData.seconds > 0
			          ? timeData.seconds
			          : $t('auth.sendEmailCode')
			      }}</span>
						</template>
					</van-count-down>
				</div>
				<button type="submit" class="basic_btn sbtn"
					:class="email==''||code==''?'no_touch':''">{{$t('auth.submit')}}</button>
			</form>
		</div>
	</div>
</template>

<script>
	import Vue from "vue";
	import {
		CountDown,
		Checkbox
	} from "vant";
	import Fetch from '../../utils/fetch';
	import bsHeader from '../../components/bsHeader.vue';
	Vue.use(CountDown)
		.use(Checkbox);
	export default {
		name: "setpwd",
		components: {
			bsHeader
		},
		data() {
			return {
				data: {},
				time: 0,
				email: '',
				code: '',
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
			// this.start();
		},
		methods: {
			start() {
				Fetch('/user/getAuthStatus').then((r) => {
					this.data = r.data;
					this.auth = r.data.auth_email;
				});
			},
			timeCall() {
				this.is_send = false;
				this.time = 0;
			},
			sendcode() {
				if (this.is_send) {
					return false;
				}
				if (!this.email) {
					this.$toast(this.$t('auth.emailEmpty'));
					return false;
				}
				var reg = /^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/;
				if (!reg.test(this.email)) {
					this.$toast(this.$t('auth.emailError'));
					return false;
				}
				this.is_send = true;

				Fetch("/user/getEmailCode", {
					email: this.email
				}).then(() => {
					this.time = 60 * 1000;
					this.$toast({
						background: "#07c160",
						message: this.$t('auth.sendsuccess'),
					});
				}).catch(() => {
					this.is_send = false;
				});
			},
			submit() {
				if (!this.email) {
					this.$toast(this.$t('auth.emailEmpty'));
					return false;
				}
				var reg = /^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/;
				if (!reg.test(this.email)) {
					this.$toast(this.$t('auth.emailError'));
					return false;
				}
				if (!this.code) {
					this.$toast(this.$t('auth.codeEmpty'));
					return false;
				}
				Fetch('/user/authEmail', {
					email: this.email,
					code: this.code
				}).then(() => {
					this.$toast({
						background: '#07c160',
						message: this.$t('auth.success')
					});
					this.$router.replace('/user')
				});
			},
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

	.sbtn {
		margin-top: 10px;
	}
</style>
