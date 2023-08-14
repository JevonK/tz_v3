<template>
	<div id="app">
		<router-view></router-view>
		<div v-show="is_show_footer" id="footer" class="footer">
			<router-link to="/" class="footer_item" :class="show_icon_type === 'index' ? 'footer_item_active' : ''">
				<span class="footer_icon footer_icon_home" />
				<p class="footer_info">
					{{$t('home.index')}}
				</p>
			</router-link>
			<router-link to="/invest" class="footer_item"
				:class="show_icon_type === 'invest' ? 'footer_item_active' : ''">
				<span class="footer_icon footer_icon_invest" />
				<p class="footer_info">
					{{$t('home.invest')}}
				</p>
			</router-link>
			<router-link to="/team" class="footer_item" :class="show_icon_type === 'team' ? 'footer_item_active' : ''">
				<span class="footer_icon footer_icon_team" />
				<p class="footer_info">
					{{$t('home.team')}}
				</p>
			</router-link>
			<router-link to="/wallet_home" class="footer_item" :class="show_icon_type === 'wallet' ? 'footer_item_active' : ''">
				<span class="footer_icon " >
					<van-icon v-if="show_icon_type === 'wallet'" name="bill" style="transform: scale(2.1); top: 8px; left: 7px;" />
					<van-icon v-else name="bill-o" style="transform: scale(2.1); top: 8px; left: 7px; " />
				</span>
				<p class="footer_info">
					{{$t('home.wallet')}}
				</p>
			</router-link>
			<router-link to="/user" class="footer_item" :class="show_icon_type === 'user' ? 'footer_item_active' : ''">
				<span class="footer_icon footer_icon_user" />
				<p class="footer_info">
					{{$t('home.my')}}
				</p>
			</router-link>
		</div>
		<div v-if="show_loading_wrap" class="loading_wrap">
			<img :src="require('./views/img/index/loading.gif'+'')" alt="">
		</div>
	</div>
</template>

<script>
	import Fetch from "./utils/fetch";

	export default {
		name: 'App',
		data() {
			return {
				show_icon_type: 'index',
				is_show_footer: true,
				show_loading_wrap: true,
				lang: 'en_us'
			}
		},
		updated() {
			document.body.scrollTop = 0
			document.documentElement.scrollTop = 0
		},
		mounted() {
			this.start();
		},
		methods: {
			onSwipeLeft(even, start, end) {
				if (end['X'] - start['X'] > 60) {
					this.$router.go(-1);
				}
			},
			footer: function(type = 'index', show_footer = true) {
				this.show_icon_type = type;
				this.is_show_footer = show_footer;
			},
			start() {
				// //首次启动，判断浏览器语言，自动切换成浏览器语言
				// var lang1 = navigator.language;
				// if (localStorage.getItem("lang") == null && this.langs[lang1] != 'undefined') {
				// 	this.$i18n.locale = this.langs[lang1];
				// 	localStorage.setItem("lang", this.langs[lang1]);
				// }
				var reload = false;
				if (localStorage.getItem("reload")) {
					reload = true;
				}
				Fetch("/index/webconfig", {
					reload: reload
				}, "", false).then((r) => {
					this.setLang(r.data.language);
					window.document.title = r.data.webname;
					localStorage.setItem("logo", r.data.logo);
					localStorage.setItem("logo2", r.data.logo2);
					localStorage.setItem('currency', "");
					localStorage.setItem('precision', r.data.precision);
					localStorage.setItem('currency_symbol_basic', '$');
					localStorage.setItem('precision_basic', 2);
					localStorage.setItem("language_logo", r.data.language_logo);
				});
			},
			setLang(lang) {
				if (!localStorage.getItem("lang")) {
					localStorage.setItem("lang", lang);
					this.$i18n.locale = lang;
					localStorage.setItem("reload", true);
					location.reload();
				} else {
					this.show_loading_wrap = false;
				}
			}
		}
	}
</script>
<style lang="less">
	// @import "./assets/font/font.css";
	.kefu {
		width: 80px;
		height: 80px;
		position: fixed;
		z-index: 10;
		right: 13px;
		bottom: 50px;
		transition: all 1s;
		opacity: 1;
		transform: translateX(0);
		z-index: 80;

		img {
			width: 100%;
			height: 100%;
		}
	}

	.kefu.kefu_hide {
		transform: translateX(70%);
		opacity: 0.5;
	}

	html,
	body {
		width: 100%;
		height: 100%;
		// overflow-y: scroll;
	}

	html::-webkit-scrollbar,
	body::-webkit-scrollbar {
		width: 0px;
		height: 0px;
	}

	body {
		margin: 0;
	}

	#app {
		min-height: 100%;
		height: auto;
		width: 100%;
		max-width: 375px;
		background-color: #FAFAFA;
	}

	#app .footer {
		position: fixed;
		margin: 0 auto;
		left: 0;
		right: 0;
		-webkit-box-flex: 0;
		-ms-flex: 0 0 48px;
		flex: 0 0 48px;
		display: -webkit-box;
		display: -ms-flexbox;
		display: flex;
		-ms-flex-pack: distribute;
		justify-content: space-around;
		-webkit-box-align: center;
		-ms-flex-align: center;
		align-items: center;
		background: #fff;
		box-shadow: 0px 2px 10px 0px rgba(0, 0, 0, 0.2);
		width: 100%;
		bottom: 0;
		height: 60px;
		z-index: 4;
	}

	#app .footer .footer_item {
		display: -webkit-box;
		display: -ms-flexbox;
		display: flex;
		-webkit-box-align: center;
		-ms-flex-align: center;
		align-items: center;
		-webkit-box-orient: vertical;
		-webkit-box-direction: normal;
		-ms-flex-direction: column;
		flex-direction: column;
		cursor: pointer;
		color: #999999;
		width: 25%;
	}

	#app .footer .footer_item .footer_icon {
		display: inline-block;
		width: 26px;
		height: 26px;
		background-repeat: no-repeat;
		background-size: contain;
		background-position: center;
		margin-bottom: 6px;
	}

	#app .footer .footer_item .footer_icon.footer_icon_home {
		background-image: url(./views/img/home/home.png);
	}

	#app .footer .footer_item .footer_icon.footer_icon_invest {
		background-image: url(./views/img/home/invest.png);
	}

	#app .footer .footer_item .footer_icon.footer_icon_team {
		background-image: url(./views/img/home/team.png);
	}

	#app .footer .footer_item .footer_icon.footer_icon_user {
		background-image: url(./views/img/home/user.png);
	}

	#app .footer .footer_item .footer_info {
		font-size: 12px;
	}

	#app .footer .footer_item.footer_item_active {
		color: #3775f4;
	}

	#app .footer .footer_item.footer_item_active .footer_icon.footer_icon_home {
		background-image: url(./views/img/home/home_cur.png);
	}

	#app .footer .footer_item.footer_item_active .footer_icon.footer_icon_invest {
		background-image: url(./views/img/home/invest_cur.png);
	}

	#app .footer .footer_item.footer_item_active .footer_icon.footer_icon_team {
		background-image: url(./views/img/home/team_cur.png);
	}

	#app .footer .footer_item.footer_item_active .footer_icon.footer_icon_user {
		background-image: url(./views/img/home/user_cur.png);
	}

	.loading_wrap {
		width: 100%;
		height: 100%;
		position: absolute;
		background: #FFFFFF;
		z-index: 9999;
		display: flex;
		align-items: center;
		justify-content: center;

		img {
			width: 150px;
			height: 150px;
		}
	}

	.red_top_bg {
		width: 100%;
		max-width: 750px;
		height: 44px;
		margin: 0 auto;
		background-size: 100% 100%;
		overflow: hidden;
		box-shadow: 0px 2px 10px 0px rgba(0, 0, 0, 0.15);
	}

	.big_tit {
		position: absolute;
		height: 25px;
		font-size: 16px;
		color: #000000;
		line-height: 44px;
		top: 0;
		left: 40px;
	}

	.back_left {
		width: 20px;
		height: 20px;
		background: url(./views/img/common/back_b.png) no-repeat center center;
		background-size: auto 100%;
		margin: 12px 0 0 8px;
	}

	.top_right {
		position: absolute;
		font-size: 13px;
		line-height: 18px;
		color: #FFFFFF;
		top: 16px;
		right: 14px;
	}

	.basic_btn {
		height: 50px;
		line-height: 50px;
		font-size: 16px;
		width: 80%;
		margin-left: 10%;
		background-size: 100% 100%;
		border-radius: 23px;
		color: #FFFFFF;
		background: #0F6EFF;
		text-align: center;
	}

	.basic_btn.no_touch {
		color: rgba(255, 255, 255, 0.5);
	}

	.flex_center {
		display: flex;
		justify-content: space-between;
		align-items: center;
	}

	.eye_bi {
		width: 21px;
		height: 25px;
		background: url(./views/img/user/eye2.png) no-repeat center center;
		background-size: 100% auto;

		&.eye {
			background: url(./views/img/user/eye1.png) no-repeat center center;
			background-size: 100% auto;
		}
	}

	.block_div {
		// border-radius: 10px;
		width: 94%;
		margin-left: 3%;
		box-shadow: 0px 2px 10px 0px rgba(0, 0, 0, 0.05);
		background: #FFFFFF;
		border-radius: 5px;
	}

	.pending {
		border-bottom: 4px solid #ECECEC !important;
	}

	.color_red {
		color: #FF0000;
	}

	.color_green {
		color: #3CB371;
	}

	.color_blue {
		color: #3775f4;
	}

	.currency {
		font-size: 12px;
		margin-left: 3px;
		font-weight: initial;
	}
</style>
