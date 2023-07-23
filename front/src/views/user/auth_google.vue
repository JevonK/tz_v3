<template>
	<div class="basic_wrap">
		<bsHeader :title="$t('auth.authAuthenticator')" @backurl="$router.back()"></bsHeader>
		<div class="block_div auth_wrap">
			<p class="step1"><span>1</span>{{$t('auth.step1Tips1')}}</p>
			<p class="step1_tips">{{$t('auth.step1Tips2')}}</p>
			<div class="auth_info">
				<img :src="qr_code">
				<div class="flex_center copy" v-clipboard="()=>key" v-clipboard:success="copy">
					<p>{{$t('auth.key')}} {{key}}</p>
					<img class="copy_img" src="../img/user/copy.png">
				</div>

			</div>
			<p class="step2"><span>2</span>{{$t('auth.step2Tips1')}}</p>
			<div class="code_wrap">
				<input v-model.trim="code" type="text" class="inp" :placeholder="$t('auth.codePlaceholde')">
			</div>
			<button class="basic_btn sbtn" :class="code==''?'no_touch':''"
				@click="submit()">{{$t('utils.submit')}}</button>
		</div>
	</div>
</template>

<script>
	import Vue from 'vue';
	import bsHeader from '../../components/bsHeader.vue'
	import Fetch from '../../utils/fetch'
	import Clipboard from "v-clipboard";
	Vue.use(Clipboard);
	export default {
		name: "",
		components: {
			bsHeader
		},
		data() {
			return {
				data: [],
				code: '',
				key: '',
				qr_code: ''
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
				Fetch('/user/getAuthStatus').then(r => {
					this.key = r.data.authenticator_key;
					this.qr_code = r.data.authenticator_qrcode;
				})
			},
			copy() {
				this.$toast(this.$t('recharge.copySuccess'));
			},
			submit() {
				if (!this.code) {
					this.$toast(this.$t('auth.codePlaceholde'));
					return false;
				}
				Fetch('/user/authAuthenticator', {
					code: this.code
				}).then(() => {
					this.$toast({
						background: '#07c160',
						message: this.$t('auth.success')
					});
					this.$router.replace('/user')
				});

			}
		}
	};
</script>

<style lang="less" scoped>
	.auth_wrap {
		margin-top: 60px;
		padding: 20px;

		.step1 {
			font-size: 14px;
			line-height: 1.5;
			margin-bottom: 10px;

			span {
				background: #9c27b0;
				width: 15px;
				height: 15px;
				border-radius: 50%;
				color: #FFFFFF;
				display: inline-block;
				text-align: center;
				line-height: 16px;
				margin-right: 10px;
			}

			p:nth-child(1) {}
		}

		.step1_tips {
			color: #FF9800;
			line-height: 1.5;
		}

		.auth_info {
			font-size: 14px;
			margin-bottom: 20px;

			img {
				width: 140px;
				height: 140px;
				padding: 15px;
			}

			.copy {
				justify-content: left;

				.copy_img {
					width: 14px;
					height: 14px;
					margin-left: 2px;
					padding: unset;
				}
			}
		}

		.step2 {
			font-size: 14px;
			line-height: 1.5;
			margin-bottom: 10px;

			span {
				background: #9c27b0;
				width: 15px;
				height: 15px;
				border-radius: 50%;
				color: #FFFFFF;
				display: inline-block;
				text-align: center;
				line-height: 16px;
				margin-right: 10px;
			}
		}

		.code_wrap {
			margin: 15px 0;
			border-bottom: 1px solid rgba(0, 0, 0, 0.1);

			input {
				flex: 1;
				padding: 0 10px;
				height: 35px;
				font-size: 16px;
				line-height: 22px;
				width: 100%;
			}
		}

		.sbtn {
			margin-top: 10px;
		}
	}
</style>
