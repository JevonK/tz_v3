<template>
	<div class="basic_wrap">
		<bsHeader :title="$t('goods.exchangeRecord')" @backurl="$router.back()"></bsHeader>
		<div class="item_wrap">
			<div class="item_list">
				<van-list v-model="loading" loading-text=" " offset="0" :finished="finished"
					:finished-text="$t('utils.noData')" @load="onLoad">
					<div class="block_div item" v-for="(item,index) in list">
						<div class="time">
							<p>{{item.act_time}}</p>
						</div>
						<div class="flex_center detail">
							<img class="logo" :src="item.img">
							<div class="goods_detail">
								<div class="title">{{item.title}}</div>
								<div class="price">
									<span class="point">{{item.point}}</span>
									<span class="point_tips">{{$t('goods.point')}}</span>
								</div>
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
		List
	} from 'vant';
	Vue.use(List);
	export default {
		name: "recharge",
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
				page: 1,
				listRows: 10
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
		mounted() {},
		methods: {
			precision(money) {
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

				Fetch('/user/goodsRecord', {
					page: this.page,
					listRows: this.listRows
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
	}

	.item_wrap {
		width: 100%;
		padding: 44px 0 0 0;

		.item_list {
			margin-top: 10px;

			.item {
				padding: 20px;
				background: #FFFFFF;
				margin-bottom: 10px;
				justify-content: space-between;

				.time {
					text-align: left;
					color: #999;
					border-bottom: 1px solid #ECECEC;
					padding-bottom: 15px;
				}

				.detail {
					margin-top: 15px;

					.logo {
						width: 80px;
						margin-right: 10px;
					}

					.goods_detail {
						overflow: hidden;
						width: 100%;
					
						.title {
							overflow: hidden;
							text-overflow: ellipsis;
							white-space: nowrap;
						}
						.price{
							margin-top: 20px;
							// text-align: right;
							.point {
								font-size: 18px;
								font-weight: bold;
								color: #0F6EFF;
							}
							
							.point_tips {
								margin-left: 5px;
							}
						}
					}
				}
			}

		}
	}
</style>
