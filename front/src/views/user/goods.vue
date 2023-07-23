<template>
	<div class="basic_wrap">
		<div class="red_top_bg">
			<div class="back_left" @click="$router.back()"></div>
			<div class="big_tit" v-if="user.login">{{$t('goods.myPoint')}}{{user.point}}</div>
			<div class="record" @click="$router.push('/goods/record')">
				<img src="../img/user/record_b.png">
			</div>
		</div>
		<!-- 轮播图 -->
		<div class="swiper_container">
			<van-swipe v-show="loading" :autoplay="2500" class="swiper-container">
				<van-swipe-item v-for="(item, index) in list" :key="index" @click="$router.push('/goods/detail/'+item.id)" v-if="index<3">
					<img :src="item.img">
				</van-swipe-item>
			</van-swipe>
		</div>
		<div class="item_wrap">
			<div class="item_tips">
				{{$t('goods.hotGoods')}}
			</div>
			<div class="item_list">
				<van-list v-model="loading" loading-text=" " offset="0" :finished="finished" finished-text=""
					@load="onLoad">
					<div class="item" v-for="(item,index) in list" @click="$router.push('/goods/detail/'+item.id)">
						<div class="img">
							<img :src="item.img">
						</div>
						<div class="title">
							{{item.title}}
						</div>
						<div class="point_wrap">
							<span class="point">{{item.point}}</span>
							<span class="point_tips">{{$t('goods.point')}}</span>
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
		Swipe,
		SwipeItem,
		List
	} from 'vant';
	Vue.use(Swipe).use(SwipeItem).use(List);
	export default {
		name: "goods",
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
				listRows: 10,
				user: {
					login:false,
					point: 0,
				},
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

				Fetch('/index/goods_list', {
					page: this.page,
					listRows: this.listRows
				}).then(r => {
					this.user = r.data.user;
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

	.red_top_bg {
		position: fixed;
		background: #FFFFFF;
		z-index: 10;
		.record {
			position: absolute;
			top: 11px;
			right: 15px;

			img {
				width: 22px;
				height: 22px;
			}
		}
	}
	.swiper_container{
		// min-height: 182px;
		position: relative;
		background: #FFFFFF;
	}
	.swiper-container {
		width: 100%;
		margin: 54px 0 0 0;
		
	
		.van-swipe-item {
			overflow: hidden;
		}
	
		img {
			border-radius: 5px;
			margin-left: 3%;
			height: 100%;
			width: 94%;
		}
	}
	
	.item_wrap {
		width: 100%;
		margin-top: 15px;
		.item_tips{
			margin-bottom: 10px;
			margin-left: 3%;
			border-left: 5px solid #0F6EFF;
			padding-left: 10px;
			font-size: 14px;
			font-weight: bold;
		}
		.item_list {
			margin-top: 10px;

			.item {
				background: #FFFFFF;
				margin-bottom: 10px;
				width: 45.5%;
				float: left;
				margin-left: 3%;

				.img {

					img {
						width: 100%;
					}
				}

				.title {
					overflow: hidden;
					text-overflow: ellipsis;
					white-space: nowrap;
					padding: 15px 10px;
				}

				.point_wrap {
					padding: 0 10px 10px 10px;
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
</style>
