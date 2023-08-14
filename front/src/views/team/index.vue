<template>
	<div class="basic_wrap">
		<div class="block_div top_header">
			<div class="flex_center user_detail_wrap">
				<div class="user_detail">
					<div class="user_header">
						<img :src="user_info.user_icon" alt="">
					</div>
					<div class="user_name">
						<div class="user_all">
							<p class="user_nickname">{{user_info.username}}</p>
							<p v-if="user_info.vip_img"><img :src="user_info.vip_img" style="width: 60px;" alt="" srcset=""></p>
						</div>
					</div>
				</div>
				<div class="invite_code">
					<p class="invite_tips">{{$t('user.invite_code')}}</p>
					<div class="flex_center copy" @click="show_share = true">
						<p>{{user_info.invite_code}}</p>
						<img class="copy_img" src="../img/user/copy.png">
					</div>
				</div>
			</div>
			<!-- <div class="flex_center invite_link_wrap"  v-clipboard="()=>user_info.share_link" v-clipboard:success="copy">
				<div class="invite_link" >
					<span>{{$t('team.inviteLink')}}</span>
					<span class="link">{{user_info.share_link}}</span>
				</div>
				<img class="copy_img" src="../img/user/copy.png">
			</div> -->
			<div class="user_earing">
				<div class="flex_center today_earing">
					<div class="total_earing">
						<p>{{common.currency_symbol_basic()}}{{common.precision_basic(report.income)}}</p>
						<p>{{$t('team.totalRevenue')}}</p>
					</div>
					<div class="today">
						<p>{{common.currency_symbol_basic()}}{{common.precision_basic(report.income_to)}}</p>
						<p>{{$t('team.today')}}</p>
					</div>
					<div class="yesterday">
						<p>{{common.currency_symbol_basic()}}{{common.precision_basic(report.income_ye)}}</p>
						<p>{{$t('team.yesterday')}}</p>
					</div>
				</div>
			</div>
		</div>
		<div class="block_div my_team_wrap">
			<div class="flex_center team_detail">
				<div>
					<p>{{report.add_count}}</p>
					<p>{{$t('team.total')}}({{$t('team.people')}})</p>
				</div>
				<div>
					<p>{{report.add_count_to}}</p>
					<p>{{$t('team.today')}}({{$t('team.people')}})</p>
				</div>
			</div>
		</div>
		<div class="block_div team_list_wrap">
			<div class="team_list_title">
				{{$t('team.teamList')}}
			</div>
			<van-tabs v-model:active="active" @change="onChangeTab">
				<van-tab title="B"></van-tab>
				<van-tab title="C"></van-tab>
				<van-tab title="D"></van-tab>
			</van-tabs>
			<div class="list_item">
				<van-list v-model="loading" offset="0" :finished="finished"  @load="onLoad">
					<div class="item" v-for="(item,index) in list">
						<div class="flex_center">
							<p>{{item.act_time}}</p>
							<!-- <p v-if="item.level==1" class="color_red">{{$t('team.direct')}}</p>
							<p v-if="item.level!=1">{{$t('team.indirect')}}</p> -->
						</div>
						<div class="flex_center">
							<p>
								{{item.username}}
								<img :src="item.logo" style="position: relative;top: 5px;width: 55px;" alt="" srcset="">
							</p>
							<!-- <p>{{common.currency_symbol_basic()+common.precision_basic(item.recharge_sum)}}</p> -->
						</div>
						<div class="flex_center">
							<p>{{$t('team.username')}}</p>
							<!-- <p>{{$t('team.totalRecharge')}}</p> -->
						</div>
					</div>
				</van-list>
				<van-empty v-if="empty" :description="$t('utils.noData')" />
			</div>
		</div>
		<van-popup v-model:show="show_share" position="bottom" closeable close-icon-position="top-left">
			<div class="share_wrap">
				<div class="share">
					<div class="invite_friend">
						<p></p>
					</div>
					<div class="share_code">
						<img :src="user_info.share_code">
					</div>
					<div class="invite_code flex_center">
						<p>{{$t('user.invite_code')}}:</p>
						<p class="code_link" v-clipboard="()=>user_info.invite_code" v-clipboard:success="copy">
							{{user_info.invite_code}}
							<img class="copy_img" src="../img/user/copy.png">
						</p>
					</div>
					<div class="invite_code flex_center">
						<p>{{$t('team.inviteLink')}}</p>
						<div class="flex_center invite_link">
							<p class="code_link" v-clipboard="()=>user_info.share_link" v-clipboard:success="copy">
								{{user_info.share_link}}
							</p>
							<img class="copy_img" src="../img/user/copy.png">
						</div>
					</div>
				</div>
			</div>
		</van-popup>
		<!-- 客服图标 -->
		<!-- <div class="kefu share_btn" @click="show_share = true;">
			<img class="kefu_img" src="../img/user/share.png">
		</div> -->
	</div>
</template>

<script>
	import Vue from 'vue';
	import Fetch from '../../utils/fetch'
	import Clipboard from "v-clipboard";
	import {
		Icon,
		List,
		Empty,
		Tab, Tabs,
		Popup
	} from "vant";

	Vue.use(Tab).use(Tabs).use(Icon).use(Clipboard).use(List).use(Empty).use(Popup);
	export default {
		name: "team",
		data() {
			return {
				user_info: [],
				active: 0,
				report: {
					"direct_count": 0,
					"indirect_count": 0,
					"income": 0,
					"income_to": 0,
					"income_ye": 0,
					"add_count": 0,
					"add_count_to": 0,
					"add_count_ye": 0
				},
				show_share: false,
				empty: false,
				loading: false,
				finished: false,
				list: [],
				page: 1,
				listRows: 8,
				tab_value: 1
			};
		},
		created() {
			if (window.plus) {
				plus.navigator.setStatusBarBackground('#0F6EFF');
				plus.navigator.setStatusBarStyle('light');
			}
			this.$parent.footer('team');
		},
		mounted() {
			this.start();
		},
		methods: {
			copy() {
				this.$toast(this.$t('recharge.copySuccess'));
			},
			start() {
				Fetch('/user/myTeam').then((r) => {
					this.user_info = r.data.user_info;
					this.report = r.data.report;
					this.rate = 100;
				});
			},
			onLoad() {
				Fetch('/user/teamList', {
					page: this.page,
					listRows: this.listRows,
					level: (this.active + 1)
				}).then(r => {
					if (r.data.length == 0) this.empty = true;
					var list = r.data.list;
					for (var i = 0; i < list.length; i++) {
						this.list.push(list[i]);
					}
					if (this.list.length >= r.data.length) {
						this.finished = true;
						return;
					}
					this.page = this.page + 1;
					this.loading = false;
				});
			},
			onChangeTab() {
				this.page = 1;
				this.loading = true;
				this.list = [];
				this.onLoad();
			},
		}
	};
</script>

<style lang="less" scoped>
	.basic_wrap {
		margin-bottom: 60px;
	}


	.top_header {
		justify-content: space-between;
		margin-bottom: 10px;
		background: #0F6EFF;
		width: 100%;
		margin: 0;
		border-radius: 0;
		color: #FFFFFF;
		height: 240px;
		.user_detail_wrap{
			position: fixed;
			    background: #0F6EFF;
			    width: 100%;
			    z-index: 10;
		}

		.user_detail {
			// width: 100%;
			padding: 28px 15px 15px 20px;
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
		.invite_link_wrap{
			top: 105px;
			margin: 0 10%;
			position: relative;
			.invite_link{
				overflow: hidden;
				text-overflow: ellipsis;
				white-space: nowrap;
				font-size: 14px;
				font-weight: bold;
				.link{
					font-weight: 100;
				}
			}
			.copy_img {
				justify-content: space-between;
				width: 14px;
				height: 14px;
				margin-left: 2px;
			}
		}
		

		.user_earing {
			padding-bottom: 15px;
			text-align: center;
			position: relative;
			top: 115px;

			.today_earing {
				justify-content: space-around;

				.total_earing {
					p:nth-child(1) {
						font-size: 16px;
						font-weight: bold;
						margin-bottom: 8px;
						margin-top: 5px;
					}

					p:nth-child(2) {
						// color: #999;
					}
				}

				.today {
					p:nth-child(1) {
						font-size: 16px;
						font-weight: bold;
						margin-bottom: 8px;
						margin-top: 5px;
					}

					p:nth-child(2) {
						// color: #999;
					}
				}

				.yesterday {
					p:nth-child(1) {
						font-size: 16px;
						font-weight: bold;
						margin-bottom: 8px;
						margin-top: 5px;
					}

					p:nth-child(2) {
						// color: #999;
					}
				}
			}
		}
	}

	.my_team_wrap {
		margin-top: -50px;
		padding: 20px;
		margin-bottom: 10px;

		.team_detail {
			justify-content: space-around;
			padding: 10px 0;

			div {
				text-align: center;

				p:nth-child(1) {
					font-size: 16px;
					font-weight: bold;
					margin-bottom: 15px;

				}

				p:nth-child(2) {
					// color: #999;
				}
			}
		}

	}

	.report_wrap {
		margin-bottom: 20px;

		.report_item {
			margin-bottom: 10px;
			padding: 20px;
		}

		.title {
			padding: 0 0 15px 0px;
			font-size: 16px;
			border-bottom: 1px solid #F7F7F7;
		}

		.report_detail {
			justify-content: space-around;
			padding: 10px 0;


			div {
				text-align: center;

				p:nth-child(1) {
					font-size: 16px;
					font-weight: bold;
					margin-bottom: 15px;
					margin-top: 5px;
					color: #3CB371;
				}

				p:nth-child(2) {
					// color: #999;
				}
			}
		}

	}
	.team_list_wrap{
		background: unset;
		box-shadow: unset;
		.team_list_title{
			padding: 15px 0 10px 3%;
			font-size: 16px;
		}
		.list_item {
		
			.item {
				box-shadow: 0px 2px 10px 0px rgba(0, 0, 0, 0.05);
				padding: 20px;
				background: #FFFFFF;
				margin-bottom: 10px;
		
				div:nth-child(1) {
					margin-bottom: 20px;
					justify-content: space-between;
					border-bottom: 1px solid #ECECEC;
					padding-bottom: 15px;
					color: #999;
		
					p:nth-child(1) {
						text-align: left;
					}
		
					p:nth-child(2) {
						text-align: right;
					}
				}
		
				div:nth-child(2) {
					justify-content: space-between;
					margin-bottom: 10px;
					font-size: 14px;
		
					p:nth-child(1) {
						text-align: left;
						
					}
		
					p:nth-child(2) {
						text-align: right;
					}
				}
		
				div:nth-child(3) {
					justify-content: space-between;
					color: #999;
		
					p:nth-child(1) {
						
						text-align: right;
						
					}
		
					p:nth-child(2) {
						text-align: left;
						
					}
				}
		
			}
		
		}
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
	.share_btn{
		width: 50px;
		height: 50px;
		bottom: 73px;
	}

	.color999 {
		color: #999;
		font-size: 12px;
		margin-left: 5px;
	}
</style>
