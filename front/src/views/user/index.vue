<template>
	<div class="user_wrap">
		<div class="red_top_bg">
			<div class="big_tit">{{$t('home.my')}}</div>
			<!-- <div class="msg" @click="signin()">
				<img v-if="!signinStatue" src="../img/user/signin.png">
				<img v-if="signinStatue" src="../img/user/signed.png">
			</div> -->
		</div>
		<div class="top_header_bg"></div>
		<div class="block_div flex_center top_header">
			<div class="user_detail">
				<div class="user_header">
					<img :src="data.user_icon" alt="">
				</div>
				<div class="user_name">
					<div class="user_all">
						<p class="user_nickname">
							{{data.username}}
							<img :src="data.vip_img" style="width: 70px;position: relative;top: 9px;" alt="" srcset="">
						</p>

					</div>
				</div>
			</div>
			<div class="invite_code">
				<p class="invite_tips">{{$t('user.invite_code')}}</p>
				<div class="flex_center copy" @click="show_share = true">
					<p>{{data.invite_code}}</p>
					<img class="copy_img" src="../img/user/copy.png">
				</div>
			</div>
		</div>
		<div class="block_div flex_center user_yw">
			<div >
				<p>{{$t('user.total_earnings')}}</p>
				<p>{{data.invest_reward}}</p>
			</div>
			<div >
				<p>{{$t('user.total_assets')}}</p>
				<p>{{data.invest_sum}}</p>
			</div>
			<div >
				<p>{{$t('user.total_withdrawal')}}</p>
				<p>{{data.withdraw_sum}}</p>
			</div>
			<div >
				<p>{{$t('user.total_recharge')}}</p>
				<p>{{data.recharge_sum}}</p>
			</div>
			<div >
				<p>{{$t('user.points')}}</p>
				<p>{{data.point}}</p>
			</div>
			<div >
				<p>{{$t('user.today_earnings')}}</p>
				<p>{{data.day_invest_reward}}</p>
			</div>
		</div>
		<div class="block_div list">
			<template v-for="(item,index) in menus1">
				<van-cell v-if="item.url == 'signout'" :key="index" is-link @click="logout">
					<template #title>
						<img :src="require('../img/'+item.logo)" alt="">
						<span class="custom-title">{{item.title}}</span>
					</template>
				</van-cell>
				<van-cell v-else-if="item.url == 'download'" is-link @click="jump(data.version.url)">
					<template #title>
						<img :src="require('../img/'+item.logo)" alt="">
						<span class="custom-title">{{item.title}}</span>
					</template>
				</van-cell>
				<van-cell v-else is-link  @click="$router.push(item.url)">
					<template #title>
						<img :src="require('../img/'+item.logo)" alt="">
						<span class="custom-title">{{item.title}}</span>
					</template>
				</van-cell>
			</template>
		</div>
		<div class="block_div list">
			<template v-for="(item,index) in menus2">
				<van-cell v-if="item.url == 'signout'" :key="index" is-link @click="logout">
					<template #title>
						<van-icon name="revoke" />
						<span class="custom-title">{{item.title}}</span>
					</template>
				</van-cell>
				<van-cell v-else is-link  @click="$router.push(item.url)">
					<template #title>
						<img :src="require('../img/'+item.logo)" alt="">
						<span class="custom-title">{{item.title}}</span>
					</template>
				</van-cell>
			</template>
			
		</div>
		<!-- <button class="basic_btn logout_btn" @click="logout">{{$t('user.signOut')}}</button> -->
		<van-popup v-model:show="show_share" position="bottom" closeable close-icon-position="top-left">
			<div class="share_wrap">
				<div class="share">
					<div class="invite_friend">
						<p></p>
					</div>
					<div class="share_code">
						<!-- <img :src="data.share_code"> -->
					</div>
					<div class="invite_code flex_center">
						<p>{{$t('user.invite_code')}}:</p>
						<p class="code_link" v-clipboard="()=>data.invite_code" v-clipboard:success="copy">
							{{data.invite_code}}
							<img class="copy_img" src="../img/user/copy.png">
						</p>
					</div>
					<div class="invite_code flex_center">
						<p>{{$t('team.inviteLink')}}</p>
						<div class="flex_center invite_link">
							<p class="code_link" v-clipboard="()=>data.share_link" v-clipboard:success="copy">
								{{data.share_link}}
							</p>
							<img class="copy_img" src="../img/user/copy.png">
						</div>
					</div>
				</div>
			</div>
		</van-popup>
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
		Popup,
		Row,
		Col,
		Grid,
		GridItem,
		CellGroup
	} from "vant";

	Vue.use(GridItem).use(Row).use(Col).use(Grid).use(Popup).use(Cell).use(CellGroup).use(Icon).use(Clipboard);

	export default {
		name: "user",
		components: {},
		data() {
			return {
				currency: '',
				show_share:false,
				menus1: [
					// {
					// 	"title": this.$t('savings.savings'),
					// 	"value": '',
					// 	"logo": "user/savings.png",
					// 	"url": "/savings"
					// },
					// {
					// 	"title": this.$t('user.vip'),
					// 	"value": '',
					// 	"logo": "user/vip.png",
					// 	"url": "/vip"
					// },
					{
						"title": this.$t('user.inviteRecord'),
						"value": '',
						"logo": "user/order.png",
						"url": "/invest/record"
					},
					// {
					// 	"title": this.$t('user.rewards'),
					// 	"value": '',
					// 	"logo": "user/rewards.png",
					// 	"url": "/rewards"
					// },
					{
						"title": this.$t('user.address'),
						"value": '',
						"logo": "user/address.png",
						"url": "/address"
					},
					{
						"title": this.$t('user.edit_password'),
						"value": '',
						"logo": "user/edit_password.png",
						"url": "/edit_password"
					},
					{
						"title": this.$t('user.faq'),
						"value": '',
						"logo": "user/question.png",
						"url": "/questions"
					},
					{
						"title": this.$t('user.signOut'),
						"value": '',
						"logo": "user/log_out.png",
						"url": "signout"
					},
					{
						"title": this.$t('user.download'),
						"value": '',
						"logo": "user/download_app.png",
						"url": "download"
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
					// {
					// 	"title": this.$t('user.faq'),
					// 	"value": '',
					// 	"logo": "user/question.png",
					// 	"url": "/questions"
					// }
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
			this.$parent.footer('user');
		},
		mounted() {
			this.start();
		},
		methods: {
			copy() {
				this.$toast(this.$t('recharge.copySuccess'));
			},
			jump(url) {
				if (url.indexOf('http') == 0) {
					console.log('http');
					window.location.href = url;
				} else {
					this.$router.push(url);
				}
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

	.share_wrap {
		text-align: center;

		.share {
			margin-top: 6%;
		}

		.invite_code {
			margin: 10px 20px 20px 20px;
			justify-content: space-between;

			.code_link {
				color: #FF0000;
				font-weight: bold;
				overflow: hidden;
				// white-space: nowrap;
				text-overflow: ellipsis;
				margin-left: -40%;
			}

			.invite_link {
				max-width: 50%;
			}

			img {
				width: 16px;
				height: 16px;
			}
		}

		.invite_friend {
			padding: 10px;
			font-size: 16px;
			font-weight: bold;
		}

		.share_code {
			margin-bottom: 20px;
		}

		img {
			max-width: 60%;
		}
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
		flex-wrap: wrap !important;
		top: -60px;

		div {
			width: 50%;
		}

		p:nth-child(1) {
			margin-top: 8px;
			font-size: 14px;
		}
		p:nth-child(2) {
			margin-top: 10px;
			font-size: 17px;
			font-weight: 700;
			color: cadetblue;
		}

		img {
			width: 30px;
			height: 30px;
		}
	}


	.money1 {
		margin-bottom: 10px;
		margin-top: -55px;
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
		margin-top: -55px;

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
