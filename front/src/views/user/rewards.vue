<template>
	<div class="basic_wrap">
		<bsHeader :title="$t('user.rewards')" @backurl="$router.back()"></bsHeader>
		<div class="rewards_wrap">
			<div class="block_div balance">
				<div class="user_balance">
					<p>{{$t('rewards.balance')}}</p>
					<p>{{common.currency_symbol_basic()}}{{common.precision_basic(user.balance)}}</p>
				</div>
				<div class="flex_center user_rewards">
					<div>
						<p>{{common.currency_symbol_basic()}}{{common.precision_basic(user.today)}}</p>
						<p>{{$t('rewards.today')}}</p>
					</div>
					<!-- <div>
						<p>{{common.precision_basic(user.yesterday)}}</p>
						<p>昨日奖励</p>
					</div> -->
					<div>
						<p>{{common.currency_symbol_basic()}}{{common.precision_basic(user.total)}}</p>
						<p>{{$t('rewards.total')}}</p>
					</div>
				</div>
			</div>
		</div>
		<div class="block_div rewards_list">
			<van-cell v-show="rewards.register>0" :value="$t('tabs.done')">
				<template #title>
					<span class="custom-title">{{$t('rewards.register')}}</span>
				</template>
			</van-cell>
			<van-cell v-show="rewards.invite>0" is-link :value="common.currency_symbol_basic()+common.precision_basic(rewards.invite)" value-class="go"
				@click="$router.push('team')">
				<template #title>
					<span class="custom-title">{{$t('rewards.invite')}}</span>
				</template>
			</van-cell>
			<van-cell v-show="rewards.authentication>0" v-if="user.auth_status!=0" is-link :value="user.auth_status"
				value-class="go" @click="$router.push('auth')">
				<template #title>
					<span class="custom-title">{{$t('rewards.auth')}}</span>
				</template>
			</van-cell>
			<van-cell v-show="rewards.authentication>0" v-if="user.auth_status==0" is-link
				:value="common.currency_symbol_basic()+common.precision_basic(rewards.authentication)" value-class="go" @click="$router.push('auth')">
				<template #title>
					<span class="custom-title">{{$t('rewards.auth')}}</span>
				</template>
			</van-cell>
			<van-cell v-show="rewards.invest>0" v-if="user.invest_status==0" is-link
				:value="common.currency_symbol_basic()+common.precision_basic(rewards.invest)" value-class="go" @click="$router.push('invest')">
				<template #title>
					<span class="custom-title">{{$t('rewards.invest')}}</span>
				</template>
			</van-cell>
			<van-cell v-show="rewards.invest>0" v-if="user.invest_status!=0" :value="$t('tabs.done')">
				<template #title>
					<span class="custom-title">{{$t('rewards.invest')}}</span>
				</template>
			</van-cell>
			<van-cell v-show="rewards.login>0" :value="$t('tabs.done')">
				<template #title>
					<span class="custom-title">{{$t('rewards.login')}}</span>
				</template>
			</van-cell>
		</div>
		<div class="block_div rewards_record">
			<van-list v-model="loading" offset="0" :finished="finished" :finished-text="$t('utils.noData')"
				@load="onLoad">
				<div class="item" v-for="(item,index) in list">
					<div class="flex_center">
						<p>{{item.act_time}}</p>
						<p></p>
					</div>
					<div class="flex_center">
						<p>{{$t('fundType.type'+item.fund_type)}}</p>
						<p>{{common.currency_symbol_basic()}}{{common.precision_basic(item.money)}}</p>
					</div>
				</div>
			</van-list>
		</div>
	</div>
</template>

<script>
	import Vue from 'vue';
	import bsHeader from '../../components/bsHeader.vue'
	import Fetch from '../../utils/fetch'
	import {
		Icon,
		Cell,
		CellGroup,
		List
	} from "vant";

	Vue.use(Cell).use(CellGroup).use(Icon).use(List);
	export default {
		name: "",
		components: {
			bsHeader
		},
		data() {
			return {
				data: [],
				empty: false,
				loading: false,
				finished: false,
				list: [],
				user: {
					balance: 0,
					today: 0,
					yesterday: 0,
					total: 0,
					auth_status: 0,
					invest_status: 0
				},
				rewards: {
					authentication: 0,
					invest: 0,
					invite: 0,
					authentication_status: true,
					invest_status: true,
					login: 0,
					register: 0
				},
				list: [],
				page: 1,
				listRows: 8,
			};
		},
		// //创建前设置
		// beforeCreate() {
		// 	//解决List上拉加载bug
		// 	document.querySelector('body').style["overflow-y"] = "unset";
		// },
		// //销毁前清除
		// beforeDestroy() {
		// 	document.querySelector('body').style["overflow-y"] = "scroll";
		// },
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
				Fetch('/user/getReward').then(r => {
					this.user = r.data.user;
					this.rewards = r.data.reward;
				})
			},
			onLoad() {
				Fetch('/user/rewardRecord', {
					page: this.page,
					listRows: this.listRows,
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
			}
		}
	};
</script>

<style lang="less" scoped>
	.basic_wrap {
		position: relative;
		margin-bottom: 100px;
	}
	.rewards_wrap {
		margin-top: 54px;
	}

	.balance {
		padding: 20px 10px;
		text-align: center;

		.user_balance {
			margin: 10px 0;

			p {
				margin: 8px 0;
			}

			p:nth-child(1) {
				color: #999;
			}

			p:nth-child(2) {

				font-size: 18px;
				font-weight: bold;
			}
		}

		.user_rewards {
			justify-content: space-around;
			margin-top: 20px;

			p {
				margin: 8px 0;
			}

			p:nth-child(1) {
				font-size: 16px;
				font-weight: bold;
			}

			p:nth-child(2) {
				color: #999;
			}

			div {
				width: 50%;
			}

			div:nth-child(1) {
				border-right: 1px solid #ebedf0;
			}

			// div:nth-child(2) {
			// 	border-left: 1px solid #ebedf0;
			// 	border-right: 1px solid #ebedf0;
			// }
		}
	}

	.rewards_list {
		margin-top: 10px;
	}

	.go {
		color: #3CB371;
		font-weight: bold;
	}

	.rewards_record {
		margin-top: 10px;
		background: unset;

		.item {
			padding: 20px;
			background: #FFFFFF;
			margin-bottom: 5px;

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


				p:nth-child(1) {
					max-width: 60%;
					text-align: left;
				}

				p:nth-child(2) {
					text-align: right;
					font-weight: bold;
					font-size: 14px;
					color: #3CB371;
				}
			}
			.money_usd {
				text-align: right;
				margin-top: 5px;
				color: #999;
				font-size: 12px;
				font-weight: normal;
				position: relative;
				top: 5px;
			}
		}
	}
</style>
