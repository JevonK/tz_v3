<template>
	<div class="home_wrap" id="home_wrap">
		<!-- 标头 -->
		<div class="red_top_bg" id="red_top_bg">
			<div class="logo">
				<img :src="logo">
			</div>
			<!-- <div class="language" @click="$router.push('/language')">
				<img :src="language_logo">
			</div> -->
		</div>
		<!-- 轮播图 -->
		<div class="swiper_container">
			<van-swipe v-show="loading" :autoplay="2500" class="swiper-container">
				<van-swipe-item v-for="(image, index) in banners" :key="index" @click="jump(image.url)">
					<img :src="image.img">
				</van-swipe-item>
			</van-swipe>
		</div>
		<!-- 滚动消息 -->
		<div class="block_div notice" @click="$router.push('/notice')">
			<van-notice-bar background="#FFFFFF" left-icon="volume-o" :text="notice.title" />
		</div>
		<!-- 快捷菜单 -->
		<!-- <div class="block_div  work_box">
			<div class="flex_center">
				<div v-for="(item,index) in menus" class="item" @click='jump(item.url)'>
					<div class="item_img"><img :src="require('../img/'+item.logo)" alt=""></div>
					<p class="item_title">{{item.title}}</p>
				</div>
			</div>
			<div class="flex_center">
				<div v-for="(item,index) in menus2" class="item" @click='jump(item.url)'>
					<div class="item_img"><img :src="require('../img/'+item.logo)" alt=""></div>
					<p class="item_title">{{item.title}}</p>
				</div>
			</div>
		</div> -->
		<!-- 货币价格 -->
		<!-- <div class="currency_tips">
			{{$t('index.exchangeRate')}}
		</div>
		<div class="block_div currency_wrap">
			<van-swipe style="height: 70px;" :autoplay="2000" vertical :show-indicators="false">
				<van-swipe-item v-for="(lang,index) in langs" :key="index" @click="$router.push('/currency')">
					<div class="currency_detail">
						<div class="flex_center currency">
							<div class="flex_center logo">
								<img :src="lang.logo" alt="">
								<p>{{lang.symbol}}</p>
							</div>
							<div class="price">
								<div>
									<span>1 USD</span>
									<span> ≈ {{lang.price}} {{lang.symbol}}</span>
								</div>
							</div>
						</div>
					</div>
				</van-swipe-item>
			</van-swipe>
		</div> -->

		<!-- 项目列表 -->
		<div class="currency_tips">
			{{$t('index.recommendedItem')}}
			<span class="see-more" @click="$router.push('/invest')">See more >></span>
		</div>
		
		<div class="items">
			  <van-grid :border="false" :column-num="3" gutter="10">
				<van-grid-item v-for="(item,index) in items" v-if="index < 3" @click="$router.push('/invest/detail/'+item.id)">
				  <van-image
				  height="80"
					:src="item.img2"
				  />
				  <p class="grid-title">{{item.title}}</p>
				  <p class="grid-price">{{common.currency_symbol_basic()}}{{common.precision_basic(item.min)}}</p>
				</van-grid-item>
			  </van-grid>
			<!-- <div v-for="(item,index) in items" class="block_div item"
				@click="$router.push('/invest/detail/'+item.id)">
				<div class="logo">
					<img v-lazy="item.img2" />
					<div>{{item.title}}</div>
				</div>
				<div class="flex_center detail">
					<div>
						<div><span class="detail_name">{{$t('index.amount')}}</span><span class="detail_num">{{common.currency_symbol_basic()}}{{common.precision_basic(item.min)}}</span></div>
						<div><span class="detail_name">{{$t('index.cycle')}}</span><span class="detail_num">{{item.day}}{{item.type==3?$t('index.hour'):$t('index.day')}}</span></div>
					</div>
					<div>
						<div><span class="detail_name">{{item.type==1?$t('index.dailyRate'):$t('index.rate')}}</span><span class="detail_num">{{item.rate}}%</span></div>
						<div><span class="detail_name">{{$t('invest.income')}}</span><span class="detail_num">{{common.currency_symbol_basic()}}{{change(item)}}</span></div>
					</div>
				</div>
			</div> -->
		</div>
		<!-- 新聞 -->
		<div class="currency_tips">
			{{$t('index.news')}}
		</div>
		
		<div class="items">
			<van-card
				v-for="(item, index) in news"
				:key="index"
				@click="$router.push('/article/'+item.id)"
				:desc="item.title_en_us"
				:title="item.release_time"
				:thumb="item.img2"
				>
				<template #footer>
					<span class="read-num">Reading Volume：{{ item.read_num }}</span>
				</template>
			</van-card>
		</div>
		<!-- APP -->
		<div v-if="!isApp&&app.show" id="download_app" class="flex_center">
			<div class="flex_center">
				<div class="app_close" @click="closeDown()">
					X
				</div>
				<div class="app_logo">
					<img :src="app.logo" alt="" srcset="">
				</div>
				<div class="app_name">
					<p>{{app.name}}</p>
				</div>
			</div>
			<div class="basic_btn down_btn" @click="jump(app.url)">
				{{$t('index.downApp')}}
			</div>
		</div>
		<!-- 弹窗 -->
		<van-dialog v-model="show_tc" :confirmButtonText="$t('utils.confirm')">
			<div class="popup" v-html="tc_content"></div>
		</van-dialog>
		<!-- 客服图标 -->
		<div class="kefu" :class="show_kefu?'':'kefu_hide'" @click="kefu_to">
			<img class="kefu_img" src="../img/index/kefu.png">
		</div>
	</div>
</template>

<script>
	import Vue from 'vue';
	import Fetch from '../../utils/fetch';
	import Api from "../../interface/index"
	import axios from 'axios'
	import {
		Swipe,
		SwipeItem,
		Progress,
		Grid,
		GridItem,
		Card,
		Dialog,
		Field,
		Popup,
		NoticeBar,
		Lazyload,
		Image,
		Search,
		Icon
	} from "vant";

	Vue.use(Icon).use(Card).use(Image).use(Grid).use(GridItem).use(Progress).use(Dialog).use(Field).use(Popup).use(NoticeBar).use(Lazyload).use(Search);

	export default {
		name: "Index",
		components: {
			VanSwipe: Swipe,
			VanSwipeItem: SwipeItem,
		},
		data() {
			return {
				language_logo: localStorage.getItem('language_logo'),
				logo: localStorage.getItem("logo2"),
				menus: [{
						"title": this.$t('user.recharge'),
						"logo": "index/receive.png",
						"url": "/recharge"
					},
					{
						"title": this.$t('user.withdraw'),
						"logo": "index/send.png",
						"url": "/withdraw"
					},
					{
						"title": this.$t('savings.savings'),
						"logo": "user/savings.png",
						"url": "/savings"
					}
				],
				menus2: [
					{
						"title": this.$t('goods.goods'),
						"logo": "index/goods.png",
						"url": "/goods"
					},
					{
						"title": this.$t('user.luckyDraw'),
						"logo": "index/luckyDraw.png",
						"url": "/draw"
					},
					{
						"title": this.$t('user.faq'),
						"logo": "user/question.png",
						"url": "/questions"
					}
				],
				show_kefu: false,
				loading: false,
				notice: [],
				show_tc: false,
				tc_content: "",
				banners: [],
				items: [],
				langs: [],
				news: [],
				isApp: true,
				app: {
					"show": false,
					"name": "",
					"url": "",
					"logo": "",
				},
				value:''
			};
		},
		created() {
			if (window.plus) {
				plus.navigator.setStatusBarBackground('#FFFFFF');
				plus.navigator.setStatusBarStyle('dark');
			}
			this.$parent.footer('index');
		},
		mounted() {
			this.start();
			var that = this;
			document.body.addEventListener("scroll", function() {
				if (!that.show_kefu) {
					return;
				}
				that.show_kefu = false;
			}, false);
			document.addEventListener('click', function(ev) {
				if (ev.target.className != 'kefu_img') {
					that.show_kefu = false;
				}
			}, false);
		},
		methods: {
			change(item) {
				if (item.type == 1) {
					return this.common.precision_basic(item.min * item.rate * item.day / 100);
				} else {
					return this.common.precision_basic(item.min * item.rate / 100);
				}
			},
			kefu_to() {
				if (this.show_kefu) {
					this.$router.push('/service');
				}
				this.show_kefu = !this.show_kefu;
			},
			jump(url) {
				if (url.indexOf('http') == 0) {
					console.log('http');
					window.location.href = url;
				} else {
					this.$router.push(url);
				}
			},
			start() {
				Fetch('/index/int').then((r) => {
					block: {
						if (r.data.popup.show == 1) {
							var popup_show = sessionStorage.getItem("popup_show");
							sessionStorage.setItem("popup_show", r.data.popup.num);
							if (popup_show != null && popup_show == 1) {
								break block;
							}
							this.tc_content = r.data.popup.content;
							this.show_tc = true;
						}
					}
					this.banners = r.data.banner;
					this.notice = r.data.notice;
					this.langs = r.data.langs;
					this.items = r.data.items;
					this.news = r.data.news;
					this.loading = true;

					this.app = r.data.version;
					this.showApp();
				});
			},
			showApp() {
				if (window.plus) {
					this.isApp = true;
				} else {
					if (this.app.show) {
						this.isApp = false;
						document.getElementById('red_top_bg').style.top = '60px';
						document.getElementById('home_wrap').style.paddingTop = '60px';
					}
				}
			},
			closeDown(url) {
				this.app.show = false;
				document.getElementById('red_top_bg').style.top = '0';
				document.getElementById('home_wrap').style.paddingTop = '0';
			}
		}
	};
</script>

<style lang="less" scoped>
	.home_wrap {
		margin-bottom: 60px;
	}
	.see-more {
		float: right;
		margin-right: 20px;
		text-decoration: underline;
		color: #0F6EFF;
	}

	.red_top_bg {
		position: fixed;
		top: 0;
		z-index: 10;
		height: 68px;
		padding: 10px 0;
		background: #FFFFFF;
		.logo {
			position: absolute;
			top: 10px;
			left: 3%;
			height: 48px;
		
			img {
				height: 100%;
			}
		}
		.language {
			position: absolute;
			top: 19px;
			right: 2%;
			height: 36px;
		
			img {
				height: 100%;
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
		margin: 80px 0 0 0;
		

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


	.notice {
		margin-bottom: 10px;
	}
	
	.work_box {
		padding: 10px 0 0 0;
		height: 160px;
		margin-bottom: 15px;

		.item {
			width: 33.33%;
			text-align: center;
			margin-bottom: 10px;

			.item_img {
				width: 100%;
				border-radius: 5px;
				overflow: hidden;

				img {
					width: 44px;
					height: 44px;
					padding: 4px;
				}
			}

			.item_title {
				width: 100%;
				line-height: 14px;
				font-size: 12px;
				color: #333333;
				text-align: center;
				transform: scale(0.85);
			}
		}
	}

	.currency_wrap {
		margin-bottom: 15px;

		img {
			width: 40px;
		}

		.currency_detail {
			padding: 20px 0;
		}

		.currency {
			justify-content: space-around;

			p {
				font-size: 14px;
				font-weight: bold;
			}

			.logo {
				p {
					margin-left: 5px;
				}
			}

			.price {
				color: #3CB371;
				font-weight: bold;
			}
		}
	}

	.popup {
		-webkit-box-flex: 1;
		-webkit-flex: 1;
		flex: 1;
		max-height: 60vh;
		padding: 26px 24px;
		overflow-y: auto;
		font-size: 14px;
		line-height: 20px;
	}
	
	.currency_tips{
		margin-bottom: 10px;
		margin-left: 3%;
		border-left: 5px solid #0F6EFF;
		padding-left: 10px;
		font-size: 14px;
		font-weight: bold;
	}
	
	.items {
		margin-bottom: 20px;

		.item {
			margin-bottom: 10px;
			

			.logo {
				position: relative;
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

	#download_app {
		position: fixed;
		background: #fff;
		width: 100%;
		top: 0;
		height: 60px;
		z-index: 11;
		justify-content: space-between;
		box-shadow: 0px 2px 10px 0px rgba(0, 0, 0, 0.2);
		padding: 0 20px 0 0;

		.app_close {
			color: #999;
			width: 50px;
			line-height: 60px;
			height: 60px;
			text-align: center;
		}

		.app_logo {
			margin-right:5px;
			img {
				height: 30px;
			}
		}

		.down_btn {
			padding: 0 15px;
			width: unset;
			height: 32px;
			line-height: 32px;
		}
	}
	/deep/.van-notice-bar{
		border-radius:  0 0 5px 5px;
	}
	.grid-title {
		font-size: 16px;
		padding: 10px;
	}
	.grid-price {
		font-size: 16px;
		color: #ed6a0c;
	}
	.read-num {
		color: #5d5d5d;
	}
</style>
