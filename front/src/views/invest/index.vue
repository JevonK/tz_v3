<template>
	<div class="basic_wrap">
		<div class="red_top_bg">
			<div class="big_tit">{{$t('home.invest')}}</div>
		</div>
		<div class="item_wrap">
			<div class="tabs" v-show="show_tabs">
				<van-tabs type="card" v-model="active" @click="sort">
					<van-tab v-for="(tab,index) in tabs" :title="tab.title" :key="index"></van-tab>
				</van-tabs>
			</div>
			<div class="item_list">
				<van-list v-model="loading" loading-text=" " offset="0" :finished="finished"
					:finished-text="$t('utils.noData')" @load="onLoad">
					<div v-for="(item,index) in list" class="block_div item"
						@click="$router.push('/invest/detail/'+item.id)">
						<div class="logo">
							<img v-lazy="item.img" />
							<div>{{item.title}}</div>
						</div>
						<div class="flex_center detail">
							<div>
								<div><span class="detail_name">{{$t('index.amount')}}</span><span class="detail_num">{{common.currency_symbol_basic()}}{{common.precision_basic(item.min)}}</span></div>
								<div><span class="detail_name">{{$t('index.cycle')}}</span><span class="detail_num">{{item.day}}{{item.type==3?$t('index.hour'):$t('index.day')}}</span></div>
							</div>
							<div>
								<div><span class="detail_name">{{$t('invest.income')}}</span><span class="detail_num">{{common.currency_symbol_basic()}}{{change(item)}}</span></div>
								<div v-if="item.type==1 || item.type==4"><span class="detail_name">{{(item.type==1 || item.type==4)?$t('index.dailyRate'):$t('index.rate')}}</span><span class="detail_num">{{common.precision_basic(change(item)/item.day)}}</span></div>
							</div>
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
		Empty,
		Lazyload
	} from 'vant';
	Vue.use(Tab).use(Tabs).use(List).use(Empty).use(Lazyload);
	export default {
		name: "",
		components: {
			bsHeader
		},
		data() {
			return {
				data: [],
				show_tabs:false,
				tabs: [
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
				plus.navigator.setStatusBarBackground('#FAFAFA');
				plus.navigator.setStatusBarStyle('dark');
			}
			this.$parent.footer('invest', true);
		},
		mounted() {
			if (this.$router.history.current.query.type) {
				this.active = parseInt(this.$router.history.current.query.type);
			}
			this.start();
		},
		methods: {
			change(item) {
				if (item.type == 1 || item.type == 4) {
					return this.common.precision_basic(item.min * item.rate * item.day / 100);
				} else {
					return this.common.precision_basic(item.min * item.rate / 100);
				}
			},
			start() {
				Fetch('/index/item_class').then((r) => {
					var tabs1 = [];
					r.data.classes.forEach(item => {
						tabs1.push({
							id: item.id,
							title: item.title,
						})
					})
					this.tabs = tabs1;
					this.show_tabs = true;
					this.active = 0;
					if (this.tabs) {
						this.onLoad();
					}
				});
			},
			sort(index) {
				this.page = 1;
				this.empty = false;
				this.list = [];
				this.finished = false;
				this.loading = true;
				this.onLoad();
			},
			onLoad() {
				Fetch('/index/item_list', {
					page: this.page,
					listRows: this.listRows,
					type: this.tabs[this.active]['id'] || 0
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

	.red_top_bg {
		position: fixed;
		top: 0;
		z-index: 10;
		box-shadow:unset;
		background: #FAFAFA;
		.big_tit{
			left: 50%;
			transform: translateX(-50%);
		}
	}

	.item_wrap {
		width: 100%;
		margin-top: 44px;
		max-width: 750px;

		.tabs {
			position: fixed;
			z-index: 100;
			width: 100%;
			background: #FAFAFA;
		}

		.item_list {
			position: relative;
			top: 45px;
			margin-bottom: 20px;

			.item {
			margin-bottom: 10px;
			

			.logo {
				position: relative;
				width: 90%;
				margin: 0 auto;
				div{
					position: absolute;
					bottom: 0px;
					width: 100%;
					background: #FFFFFF;
					height: 30px;
					line-height: 30px;
					opacity: 0.8;
					font-weight: bold;
					padding: 0 10px;
					overflow: hidden;
					text-overflow: ellipsis;
					white-space: nowrap;
				}
				img {
					width: 100%;
					border-radius: 5px;
					max-height: 260px;
				}
			}

			.detail {
				padding: 15px;
				line-height: 2;

				.detail_name {
				}

				.detail_num {
					font-weight: bold;
					color: #3CB371;
				}
			}
		}

		}
	}
	/deep/.van-tabs__nav--card{
		margin: 0 2%;
	}
	/deep/.van-tabs{
		margin-bottom: 10px;
	}
	/deep/.van-tabs__nav--card .van-tab{
		border-right: unset;
		background: #FFFFFF;
		margin-right: 10px;
		border-radius: 15px;
	}
	/deep/.van-tabs__nav--card{
		border: unset;
	}
	/deep/.van-tabs__nav{
		background: unset;
	}
	/deep/.van-tabs__nav--card .van-tab.van-tab--active{
		background: #0F6EFF;
	}
	/deep/ .tabs {
		max-width: 750px;
	}
</style>
