<template>
	<div class="basic_wrap">
		<div class="red_top_bg">
			<div class="back_left" @click="$router.back()"></div>
			<div class="big_tit">{{$t('user.vip')}}</div>
		</div>
		<div class="user_wrap">
			<div class="flex_center user_top">
				<div class="user_detail">
					<div class="user_header">
						<img :src="user.user_icon" alt="">
					</div>
					<div class="user_name">
						<div class="user_all">
							<p class="user_nickname">{{user.username}}</p>
							<!-- <div class="invite_code">
								<div class="copy" v-clipboard="()=>user.invite_code" v-clipboard:success="copy">
									<span>{{user.invite_code}}</span>
									<img class="copy_img" src="../img/user/copy.png">
								</div>
							</div> -->
						</div>
					</div>
				</div>
				<div class="vip_detail">
					<img :src="vip.logo">
				</div>
			</div>

		</div>
		<div class="block_div user_balance">
			<div class="title">
				{{$t('vip.myMoney')}}
			</div>
			<div class="flex_center fundding">
				<div>
					<div class="funding_money">{{common.currency_symbol_basic()}}{{common.precision_basic(user.balance)}}</div>
					<div class="funding_name">{{$t('vip.balance')}}</div>
				</div>
				<div>
					<div class="funding_money">{{common.currency_symbol_basic()}}{{common.precision_basic(user.income)}}</div>
					<div class="funding_name">{{$t('vip.income')}}</div>
				</div>
			</div>
				<!-- <div class="progress">
					<van-progress :percentage="percentage" color="#ffa325" stroke-width="5" pivot-text="" />
					<div class="flex_center progress_text">
						<p>{{vip.this_value}}</p>
						<p>{{vip.next_value}}</p>
					</div>
				</div> -->
		</div>
		<!-- 我的权益 -->
		<div class="block_div rights">
			<div class="title">
				{{$t('vip.myRights')}}
			</div>
			<div class="rights_detail">
				<div><span>{{$t('vip.rights')}}1</span>{{$t('vip.investNum')}}{{vip.invest_num}}{{$t('utils.times'+times)}}</div>
				<div><span>{{$t('vip.rights')}}2</span>{{$t('vip.direct')}}{{vip.rewards_direct}}%</div>
				<div><span>{{$t('vip.rights')}}3</span>{{$t('vip.undirect')}}{{vip.rewards_undirect}}%</div>
			</div>

		</div>
	</div>
</template>

<script>
	import Vue from 'vue';
	import bsHeader from '../../components/bsHeader.vue';
	import Fetch from '../../utils/fetch';
	import Clipboard from "v-clipboard";
	import {
		Progress
	} from 'vant';
	Vue.use(Progress).use(Clipboard);
	export default {
		name: "",
		components: {
			bsHeader
		},
		data() {
			return {
				data: [],
				vip: {
					invest_num: '',
					rewards_direct: '',
					rewards_undirect: '',
					name: '',
					logo: '',
					this_value: 0,
					next_value: 0
				},
				user: {
					balance: 0,
					income: 0,
					user_value: 0,
					username: '',
					invite_code: '',
					user_icon: ''
				},
				percentage: 0,
				times:0
			};
		},
		created() {
			if (window.plus) {
				plus.navigator.setStatusBarBackground('#000002');
				plus.navigator.setStatusBarStyle('light');
			}
			this.$parent.footer('user', false);
		},
		mounted() {
			this.start();
		},
		methods: {
			start() {
				Fetch('/user/getVip').then(r => {
					this.vip = r.data.vip;
					this.user = r.data.user;
					this.times = r.data.vip.invest_num;
					if (this.times > 1) this.times = 2;
					if (r.data.vip.next_value == r
						.data.vip.this_value) {
						this.percentage = 100;
					} else {
						this.percentage = (r.data.user.user_value - r.data.vip.this_value) / (r.data.vip
							.next_value - r
							.data.vip.this_value) * 100;
					}

				})
			},
			copy() {
				this.$toast(this.$t('recharge.copySuccess'));
			},
		}
	};
</script>

<style lang="less" scoped>
	.red_top_bg{
		background: #000002;
		position: fixed;
		z-index: 10;
		box-shadow: unset;
		.back_left{
			background: url(../img/common/back.png) no-repeat center center;
			width: 30px;
			height: 22px;
		}
		.big_tit{
			color: #FFFFFF;
			left: 55px;
		}
	}
	.user_wrap {
		height: 218px;
		padding-top: 60px;
		background: url(../img/user/vip_bg.png) no-repeat center center;
		background-size: 100% 100%;
		color: #FFFFFF;

		.user_top {
			justify-content: space-between;

			.user_detail {
				// width: 100%;
				padding: 0px 15px 22px 20px;

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

					.invite_code {
						margin-top: 5px;

						.copy {
							.copy_img {
								position: relative;
								top: 2px;
								width: 14px;
								height: 14px;
								margin-left: 2px;
							}
						}
					}

					.user_all {

						.user_nickname {
							line-height: 21px;
							font-size: 18px;
							font-weight: bold;
							margin-top: 2px;
						}
					}
				}
			}


		}

		.vip_detail {
			img {
				height: 30px;
			}
		}
	}

	.user_balance {
		position: relative;
		margin-top: -80px;
		padding: 10px 0;
		border-radius: 10px;

		.title {
			font-size: 16px;
			font-weight: bold;
			padding: 10px 20px;
		}

		.fundding {
			justify-content: space-around;
			margin: 10px 0;
			text-align: center;

			.funding_money {
				color: #ffa325;
				margin-bottom: 10px;
				font-size: 16px;
				font-weight: bold;
			}

			.funding_name {}
		}

		.progress {
			padding: 20px 10px 10px 10px;
			width: 90%;
			margin-left: 5%;

			.progress_text {
				justify-content: space-between;
				margin-top: 15px;
			}
		}
	}

	.rights {
		margin-top: 10px;
		padding: 10px 20px;
		border-radius: 10px;

		.title {
			font-size: 16px;
			font-weight: bold;
			padding: 10px 0px;
		}

		.rights_detail {
			line-height: 2;
			margin-top: 10px;

			span {
				background: #ffa325;
				padding: 2px 8px;
				border-radius: 9px;
				margin-right: 10px;
				color: #FFFFFF;
			}

			div {
				margin-bottom: 15px;
			}
		}

	}
</style>
