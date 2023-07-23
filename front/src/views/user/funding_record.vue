<template>
	<div class="basic_wrap">
		<bsHeader :title="$t('user.fundingDetails')" @backurl="$router.back()"></bsHeader>
		<div class="item_wrap">
			<div class="tabs">
				<van-tabs v-model="active" @click="sort">
					<van-tab v-for="index in tabs" :title="index" :key="index"></van-tab>
				</van-tabs>
			</div>
			<div class="item_list">
				<van-list v-model="loading" loading-text=" " offset="0" :finished="finished"
					:finished-text="$t('utils.noData')" @load="onLoad">
					<div class="block_div item" v-for="(item,index) in list">
						<div class="flex_center">
							<p>{{item.act_time}}</p>
							<p v-if="item.type==1" class="color_red">{{$t('tabs.income')}}</p>
							<p v-if="item.type==2" class="color_green">{{$t('tabs.expenditure')}}</p>
						</div>
						<div class="flex_center">
							<p>{{$t('fundType.type'+item.fund_type)}}</p>
							<p v-bind:class="{color_red: item.type==1,color_green: item.type==2}">
								{{currency_symbol_basic}}{{precision_basic(item.money)}}<span class="currency"></span>
							<br><span class="money_usd">≈ {{precision(item.money2)}} {{item.currency}}</span>
							</p>
						</div>
					</div>
				</van-list>
			</div>
		</div>
	</div>
</template>

<script>
	import Vue from 'vue';
	import bsHeader from '../../components/bsHeader.vue'
	import Fetch from '../../utils/fetch'
	import {
		Tab,
		Tabs,
		List,
		Empty
	} from 'vant';
	Vue.use(Tab).use(Tabs).use(List).use(Empty);
	export default {
		name: "",
		components: {
			bsHeader
		},
		data() {
			return {
				currency_symbol_basic: localStorage.getItem('currency_symbol_basic'),
				data: [],
				tabs: [
					this.$t('tabs.all'),
					this.$t('tabs.income'),
					this.$t('tabs.expenditure')
				],
				active: 0,
				empty: false,
				loading: false,
				finished: false,
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
			if (this.$router.history.current.query.type) {
				this.active = parseInt(this.$router.history.current.query.type);
			}
		},
		methods: {
			precision(money) {
				var precision = localStorage.getItem('precision');
				return (money / 1).toFixed(precision);
			},
			precision_basic(money) {
				var precision = localStorage.getItem('precision_basic');
				return (money / 1).toFixed(precision);
			},
			sort(type) {
				this.page = 1;
				this.empty = false;
				this.list = [];
				this.finished = false;
				this.loading = true;
				this.onLoad();
			},
			onLoad() {

				Fetch('/user/fundingRecord', {
					page: this.page,
					listRows: this.listRows,
					type: this.active
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

	.item_wrap {
		width: 100%;
		padding: 44px 0 0 0;

		.tabs {
			position: fixed;
			z-index: 100;
			width: 100%;
		}

		.item_list {
			margin-top: 54px;

			.item {
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

					p:nth-child(1) {
						max-width: 60%;
						text-align: left;
					}

					p:nth-child(2) {
						text-align: right;
						font-weight: bold;
						font-size: 14px;
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
	}
</style>
