<template>
	<div class="basic_wrap">
		<bsHeader :title="$t('auth.authCenter')" @backurl="$router.back()"></bsHeader>
		<div class=" desc_warp">
			<img src="../img/user/certification.png" />
			<p>{{$t('auth.authSafe')}}</p>
			<p>{{$t('auth.authTips1')}}</p>
		</div>
		<div class="item_warp">
			<div v-show="data.auth_phone_status" class="block_div flex_center item">
				<div class="flex_center item_info">
					<div>
						<img src="../img/user/phone.png">
					</div>
					<div>
						<p>{{$t('auth.authPhone')}}</p>
						<p v-if="!bind_phone">{{$t('auth.bindPhone')}}</p>
						<p v-if="bind_phone">{{phone}}</p>
					</div>
				</div>
				<div>
					<p v-if="bind_phone" class="btn_0">{{$t('auth.authed')}}</p>
					<p v-if="!bind_phone" class="btn_1 basic_btn" @click="$router.replace('/authPhone')">
						{{$t('auth.toAuth')}}</p>
				</div>
			</div>
			<div v-show="data.auth_email_status" class="block_div flex_center item">
				<div class="flex_center item_info">
					<div>
						<img src="../img/user/email.png">
					</div>
					<div>
						<p>{{$t('auth.authEmail')}}</p>
						<p v-if="!bind_email">{{$t('auth.bindEmail')}}</p>
						<p v-if="bind_email">{{email}}</p>
					</div>
				</div>
				<div>
					<p v-if="bind_email" class="btn_0">{{$t('auth.authed')}}</p>
					<p v-if="!bind_email" class="btn_1 basic_btn" @click="$router.replace('/authEmail')">
						{{$t('auth.toAuth')}}</p>
				</div>
			</div>
			<div v-show="data.auth_authenticator_status" class="block_div flex_center item">
				<div class="flex_center item_info">
					<div>
						<img src="../img/user/authenticator.png">
					</div>
					<div>
						<p>{{$t('auth.authAuthenticator')}}</p>
						<p v-if="!bind_authenticator">{{$t('auth.bindAuthenticator')}}</p>
						<p v-if="bind_authenticator">{{authenticator}}</p>
					</div>
				</div>
				<div>
					<p v-if="bind_authenticator" class="btn_0">{{$t('auth.authed')}}</p>
					<p v-if="!bind_authenticator" class="btn_1 basic_btn" @click="$router.replace('/authGoogle')">
						{{$t('auth.toAuth')}}</p>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	import Vue from "vue";
	import Fetch from '../../utils/fetch';
	import bsHeader from '../../components/bsHeader.vue';
	import {
		CountDown,
		Checkbox
	} from "vant";
	Vue.use(CountDown)
		.use(Checkbox);
	export default {
		name: "auth",
		components: {
			bsHeader
		},
		data() {
			return {
				data: {},
				bind_phone: true,
				bind_email: true,
				bind_authenticator: true,
				phone: this.$t('auth.bindPhone'),
				email: this.$t('auth.bindEmail'),
				authenticator: this.$t('auth.bindAuthenticator')
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
				Fetch('/user/getAuthStatus').then((r) => {
					this.data = r.data;
					this.bind_phone = r.data.auth_phone;
					this.phone = r.data.phone;
					this.bind_email = r.data.auth_email;
					this.email = r.data.email;
					this.bind_authenticator = r.data.auth_authenticator;
					this.authenticator = r.data.authenticator_key;
				});
			},
		}
	};
</script>

<style lang="less" scoped>
	.desc_warp {
		margin-top: 100px;
		text-align: center;

		img {
			width: 100px;
			height: 100px;
		}

		p:nth-child(2) {
			font-size: 20px;
			font-weight: bold;
			margin: 20px 0;
		}

		p:nth-child(3) {
			font-size: 14px;
			color: #999;
			line-height: 1.5;
			padding: 0 20px;
		}
	}

	.item_warp {
		margin-top: 30px;

		.item {
			margin-bottom: 10px;
			padding: 30px 20px;
			justify-content: space-between;


			.item_info {
				div:nth-child(1) {
					img {
						width: 40px;
					}
				}

				div:nth-child(2) {
					margin-left: 15px;
					line-height: 1.5;

					p:nth-child(1) {
						font-size: 16px;
						font-weight: bold;
					}

					p:nth-child(2) {
						color: #999;
					}
				}
			}

			.btn_0 {
				color: #999;
			}

			.btn_1 {
				font-size: 14px;
				height: unset;
				line-height: unset;
				width: unset;
				border-radius: 14px;
				padding: 5px 10px;
				margin-left: unset;
			}
		}
	}
</style>
