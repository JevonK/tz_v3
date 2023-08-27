<template>
	<div class="user_wrap">
		<div class="red_top_bg">
			<div class="big_tit">{{$t('home.wallet')}}</div>
			<!-- <div class="msg" @click="signin()">
				<img v-if="!signinStatue" src="../img/user/signin.png">
				<img v-if="signinStatue" src="../img/user/signed.png">
			</div> -->
		</div>
		<div class="block_div flex_center money1">
			<div>
				<p @click="showMsg()">{{$t('recharge.money')}}
					<!-- <van-icon name="question-o" size="14" style="left: 2px;top:1px;" /> -->
				</p>
				<p>{{common.currency_symbol_basic()}}{{common.precision_basic(fundBalanceUsd)}}</p>
				<!-- <p class="money_usd">≈ {{common.precision(fundBalance)}} ({{currency}})</p> -->
			</div>
			<div class="recharge">
				<p @click="$router.push('/recharge')">{{$t('user.recharge')}}</p>
				<!-- <p @click="$router.push('/withdraw')">{{$t('user.withdraw')}}</p> -->
			</div>
		</div>
		<div class="block_div flex_center money1 money2">
			<div>
				<p @click="showMsg()">{{$t('user.withdrawAccount')}}
					<!-- <van-icon name="question-o" size="14" style="left: 2px;top:1px;" /> -->
				</p>
				<p>{{common.currency_symbol_basic()}}{{common.precision_basic(data.withdrawable || '0.00')}}</p>
				<!-- <p class="money_usd">≈ {{common.precision(fundBalance)}} ({{currency}})</p> -->
			</div>
			<div class="recharge">
				<!-- <p @click="$router.push('/recharge')">{{$t('user.recharge')}}</p> -->
				<p class="withdraw" @click="$router.push('/withdraw')">{{$t('user.withdraw')}}</p>
			</div>
		</div>
		<!-- <div class="block_div flex_center money1 money2"> -->
			<!-- <div> -->
				<!-- <p @click="showMsg()">{{$t('vip.points')}} -->
					<!-- <van-icon name="question-o" size="14" style="left: 2px;top:1px;" /> -->
				<!-- </p> -->
				<!-- <p>{{data.point || '0.00'}}</p> -->
				<!-- <p class="money_usd">--</p> -->
			<!-- </div> -->
		<!-- </div> -->
		<!-- <div class="block_div flex_center user_yw">
			<div @click="$router.push('/invest/record')">
				<img src="../img/user/order.png" alt="" />
				<p>{{$t('user.inviteRecord')}}</p>
			</div>
			<div @click="$router.push('/funding/record')">
				<img src="../img/user/details.png" alt="" />
				<p>{{$t('user.fundingDetails')}}</p>
			</div>
			<div @click="$router.push('/service')">
				<img src="../img/user/kf.png" alt="" />
				<p>{{$t('user.onlineService')}}</p>
			</div>
		</div> -->
		<div class="block_div list">
			<van-cell is-link v-for="(item,index) in menus1" :key="index" @click="$router.push(item.url)">
				<template #title>
					<img :src="require('../img/'+item.logo)" alt="">
					<span class="custom-title">{{item.title}}</span>
				</template>
			</van-cell>
		</div>
	</div>
</template>

<script>
	import Vue from 'vue';
	import qs from 'qs';
	import Fetch from '../../utils/fetch';
	import Api from "../../interface/index";
	import Clipboard from "v-clipboard";
	import {
		Icon,
		Cell,
		CellGroup
	} from "vant";

	Vue.use(Cell).use(CellGroup).use(Icon).use(Clipboard);

	export default {
		name: "user",
		components: {},
		data() {
			return {
				currency: '',
				menus1: [
					{
						"title": this.$t('user.luckyDraw'),
						"value": '',
						"logo": "index/luckyDraw.png",
						"url": "/draw"
					},
					{
						"title": 'Reward Redemption',
						"value": '',
						"logo": "user/reward_redemption.png",
						"url": "/red_envelope"
					},{
						"title": this.$t('user.withdrawAccount'),
						"value": '',
						"logo": "user/moneybag.png",
						"url": "/wallet"
					},
					{
						"title": this.$t('user.fundingDetails'),
						"value": '',
						"logo": "user/details.png",
						"url": "/funding/record"
					},
					// {
					// 	"title": this.$t('user.rewards'),
					// 	"value": '',
					// 	"logo": "user/rewards.png",
					// 	"url": "/rewards"
					// },
					{
						"title": this.$t('user.rechargeRecord'),
						"value": '',
						"logo": "user/recharge.png",
						"url": "/recharge/record"
					},
					{
						"title": this.$t('user.withdrawRecord'),
						"value": '',
						"logo": "user/cash.png",
						"url": "/withdraw/record"
					}
					
				],
				menus2: [
					// {
					// 	"title": this.$t('user.userAgreement'),
					// 	"logo": "user/agreement.png",
					// 	"url": "/language"
					// },
					// {
					// 	"title": this.$t('user.privacyPolicy'),
					// 	"logo": "user/secret.png",
					// 	"url": "/language"
					// },
					// {
					// 	"title": this.$t('user.languagePreference'),
					// 	"value": '',
					// 	"logo": "user/language.png",
					// 	"url": "/language"
					// },
					{
						"title": this.$t('user.faq'),
						"value": '',
						"logo": "user/question.png",
						"url": "/questions"
					}
				],
				data: {},
				fundBalance: 0.00,
				fundBalanceUsd: 0.00,
				savings: 0.00,
				eye: 1,
				signinStatue:false
			};
		},
		created() {
			if (window.plus) {
				plus.navigator.setStatusBarBackground('#051C3F');
				plus.navigator.setStatusBarStyle('light');
			}
			this.$parent.footer('wallet');
		},
		mounted() {
			this.start();
		},
		methods: {
			copy() {
				this.$toast(this.$t('recharge.copySuccess'));
			},
			showMsg() {
				this.$dialog.alert({
					closeOnClickOverlay: true,
					showConfirmButton: false,
					message: "<p style='text-align: left'>" +
						this.$t('user.fundingTips') +
						"</p>",
				}).catch(() => {
					// on close
				});
			},
			signin(){
				if(this.signinStatue){
					this.$toast(this.$t('user.signined'));
					return false;
				}
				this.signinStatue = true;
				Fetch('/user/signin').then((r) => {
					this.$toast(this.$t('user.signinSuccess'));
				});
			},
			start() {
				var isapp = 0;
				if (window.plus) {
					isapp = 1;
				}
				Fetch('/user/info', {
					isapp: isapp
				}).then((r) => {
					this.data = r.data;
					this.fundBalance = r.data.fundBalance;
					this.fundBalanceUsd = r.data.fundBalanceUsd;
					this.menus1[0]['value'] = r.data.vip_name;
					this.currency = r.data.currency;
					this.signinStatue = r.data.signin;
				});
			},
			logout() {
				localStorage.removeItem('token');
				this.$router.replace("/");
			},
		}
	};
</script>

<style lang="less" scoped>
	.user_wrap {
		margin-bottom: 60px;
	}

	.red_top_bg {
		position: fixed;
		top: 0;
		z-index: 10;
		background-color: #051C3F;
		box-shadow: unset;
		.big_tit{
			left: 50%;
			transform: translateX(-50%);
			color: #FFFFFF;
		}

		.msg {
			position: absolute;
			top: 12px;
			right: 15px;

			img {
				width: 22px;
				height: 22px;
			}
		}
	}
	.top_header_bg{
		background: #051C3F;
		width: 100%;
		height: 208px;
	}

	.top_header {
		position: relative;
		justify-content: space-between;
		margin-top: -168px;
		border-radius: 5px 5px 0 0;
		box-shadow: unset;
		color: #FFFFFF;
		position: fixed;
		z-index: 10;
		background: #051C3F;
		width: 100%;
		max-width: 750px;
		margin-left: unset;

		.user_detail {
			// width: 100%;
			padding: 28px 15px 22px 20px;
			top: 34px;
			display: flex;
			justify-content: flex-start;
			align-items: center;

			.user_header {
				width: 60px;
				height: 60px;
				overflow: hidden;
				border-radius: 50%;
				border: 3px solid rgba(255, 255, 255, 0.3);

				img {
					width: 100%;
					height: 100%;
				}
			}

			.user_name {
				margin-left: 3px;
				margin-top: 3px;
				flex: 1;
				display: flex;
				flex-direction: column;
				justify-content: flex-start;

				.user_all {
					// display: flex;
					// justify-content: flex-start;
					// align-items: center;

					.user_nickname {
						line-height: 21px;
						font-size: 18px;
						font-weight: bold;
						margin-top: 2px;
					}

					img {
						width: 22px;
						border-radius: 50%;
						margin-right: 3px;
					}

					.auth {
						justify-content: unset;
						margin-top: 3px;
					}

					.active {
						border: 2px solid #FFEB3B;
					}
				}
			}
		}

		.invite_code {
			margin-right: 30px;
			text-align: center;

			.invite_tips {
				color: #999;
				margin-bottom: 10px;
			}

			.copy {
				justify-content: space-between;
				font-size: 16px;
				font-weight: bold;

				.copy_img {
					width: 14px;
					height: 14px;
					margin-left: 2px;
				}
			}

		}
	}

	.user_yw {
		position: relative;
		padding: 15px 0;
		margin-bottom: 10px;
		justify-content: space-between;
		text-align: center;

		div {
			width: 33.33%;
		}

		p {
			margin-top: 8px;
		}

		img {
			width: 30px;
			height: 30px;
		}
	}


	.money1 {
		margin-bottom: 10px;
		margin-top: 60px;
		padding: 15px;
		justify-content: space-between;

		div:nth-child(1) {
			p:nth-child(1) {
				color: #999;
				margin-bottom: 10px;
			}

			p:nth-child(2) {
				font-size: 18px;
				font-weight: bold;
			}
		}

		div:nth-child(2) {}

		.money_usd {
			margin-top: 10px;
			color: #999;
		}

		.recharge {
			margin-top: 10px;

			p {
				padding: 5px 10px;
				background: #3775f4;
				margin: 0 10px 8px 0;
				color: #FFFFFF;
				border-radius: 8px;
			}

			p:nth-child(2) {
				background: #FF5722;
			}
		}
		.withdraw {
			background: #FF5722 !important;
		}
	}
	.money2 {
		margin-top: 10px;
	}

	.list {
		margin-top: 10px;

		img {
			vertical-align: middle;
			height: 30px;
			width: 30px;
			margin-right: 10px;
		}
	}

	.logout_btn {
		width: 80%;
		margin: 30px 10%;
	}

	/deep/ .van-cell {
		background: unset;
	}
</style>
