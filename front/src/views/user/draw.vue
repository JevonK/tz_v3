<template>
	<div class="basic_wrap">
		<div class="red_top_bg">
			<div class="back_left" @click="$router.back()"></div>
			<div class="record" @click="$router.push('/draw/record')">
				<img src="../img/user/record_w.png">
			</div>
		</div>
		<div class="zp-bg1">
			<div class="top_bg">
			</div>
			<div class="overall flex_center">
				<div class="zp-top">
					<div class="zp-box">
						<div class="panzi">
							<div class="bck-box" :style="` transform: rotate(${-90+180/list.length}deg)`">
								<div class="bck" v-for="(i,index) in list" :key="index"
									:style="`transform: rotate(${-index*360/list.length}deg) skew(${-90 + 360/list.length}deg);`">
								</div>
							</div>
							<div class="jiang"
								:style="`transform: rotate(${-index*360/list.length}deg) translateY(-85px);`"
								v-for="(i,index) in list" :key="index">
								<span class="title">{{i.title}}</span>
								<div class="img">
									<img :src="i.img" alt />
								</div>
							</div>
						</div>
						<div class="start-btn" @click="draw()"></div>
					</div>
				</div>
			</div>
			<div class="zp-num">
				<span>{{$t('draw.myPoints')}}</span>
				<span>{{point_total}}</span>
			</div>
		</div>
		<div class="zp-bg2">
			<div class="block_div item">
				<div class="title">
					{{$t('draw.rule')}}
				</div>
				<div class="title_bg">

				</div>
				<div class="content" v-html="rule_content"></div>
			</div>
		</div>
	</div>
</template>

<script>
	import Fetch from '../../utils/fetch'
	import bsHeader from '../../components/bsHeader.vue'
	import Vue from 'vue';
	import {
		Empty,
		Icon,
		Dialog
	} from 'vant';
	Vue.use(Icon).use(Empty).use(Dialog);
	export default {
		computed: {
			animationClass() {
				//对应css样式中定义的class属性值,如果有更多的话可以继续添加  case 8:   return 'wr8'
				switch (this.winner) {
					case 0:
						return 'wr0'
					case 1:
						return 'wr1'
					case 2:
						return 'wr2'
					case 3:
						return 'wr3'
					case 4:
						return 'wr4'
					case 5:
						return 'wr5'
					case 6:
						return 'wr6'
					case 7:
						return 'wr7'
				}
			}
		},
		data() {
			return {
				winner: 2, //指定获奖下标 specified为true时生效
				specified: true, //是否指定获奖结果，false时为随机
				loading: false, //抽奖执行状态，防止用户多次点击
				panziElement: null,
				listLength: 0,
				list: [],
				rule_content: '',
				draw_num: 0,
				times: 0,
				point_total:0,
				point:9999999
			}
		},
		created() {
			if (window.plus) {
				plus.navigator.setStatusBarBackground('#071c45');
				plus.navigator.setStatusBarStyle('light');
			}
			this.$parent.footer('user', false);
		},
		mounted() {
			this.start();
		},
		methods: {
			start() {
				Fetch('/user/prizeList').then((r) => {
					this.list = r.data.prizeList;
					this.draw_num = r.data.drawNum;
					this.point_total = r.data.point_total;
					this.point = r.data.point;
					this.rule_content = r.data.set.content;
					this.times = r.data.drawNum;
					if (this.times > 1) this.times = 2;
					//通过获取奖品个数，来改变css样式中每个奖品动画的旋转角度
					// var(--nums) 实现css动画根据奖品个数，动态改变
					let root = document.querySelector(':root');
					root.style.setProperty('--nums', this.list.length);
				});
			},
			//开始抽奖
			draw() {
				if (!this.loading) {
					if (this.point_total < this.point) {
						this.$toast(this.$t('draw.pointsEmpty'));
						return false;
					}
					this.panziElement = document.querySelector('.panzi')
					this.panziElement.classList.remove(this.animationClass)
					if (this.specified) { //此处可指定后端返回的指定奖品
						Fetch('/user/draw').then((r) => {
							let index = this.list.findIndex(item => item.id == r.data.draw.id);
							this.winner = index;
							this.winCallback(r.data.drid);
							this.point_total = this.point_total - this.point;
						});
					} else {
						this.winner = this.random(0, this.list.length - 1)
						this.winCallback()
					}
					this.loading = true
				}
			},
			//中奖返回方法
			winCallback() {
				setTimeout(() => {
					/* 此处是为了解决当下次抽中的奖励与这次相同，动画不重新执行的 */
					/* 添加一个定时器，是为了解决动画属性的替换效果，实现动画的重新执行 */
					this.panziElement.classList.add(this.animationClass)
				}, 0)
				// 因为动画时间为 3s ，所以这里3s后获取结果，其实结果早就定下了，只是何时显示，告诉用户
				setTimeout(() => {
					this.loading = false
					if (this.list[this.winner]['type'] == 1 || this.list[this.winner]['type'] == 2) {
						Dialog.alert({
							message: this.$t('draw.congratulation') + this.list[this.winner]['title'],
							confirmButtonText: this.$t('utils.confirm')
						})
					} else if (this.list[this.winner]['type'] == 3) {
						this.$toast(this.$t('draw.unfortunately'));
					}
				}, 3000)
			},
			//随机一个整数的方法
			random(min, max) {
				return parseInt(Math.random() * (max - min + 1) + min)
			}
		}
	}
</script>
<style lang="scss" scoped>
	$zp_size: 270px; //转盘尺寸
	$btn_size: 60px; //抽奖按钮尺寸
	$time: 3s; //转动多少秒后停下的时间

	.basic_wrap {
		margin-bottom: -60px;
	}

	.red_top_bg {
		position: fixed;
		top: 0;
		z-index: 10;
		background-color: #071c45;
		box-shadow: unset;
		.back_left{
			background: url(../img/common/back.png) no-repeat center center;
			width: 32px;
			height: 24px;
		}
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

	.top_bg {
		// background: url(../img/draw/top_bg.png) no-repeat center center;
		background-size: 100% 100%;
		width: 100%;
		height: 60px;
	}

	.zp-bg1 {
		background: url(../img/draw/bg1.png) no-repeat center center;
		background-size: 100% 100%;
		width: 100%;
		height: 430px;
		margin-top: 44px;
	}

	.zp-bg2 {
		background: url(../img/draw/bg2.png) no-repeat center center;
		background-size: 100% 100%;
		width: 100%;
		min-height: 400px;

		.item {
			background: unset;
			padding-bottom: 40px;

			.title {
				background: url(../img/draw/title_bg.png) no-repeat center center;
				background-size: 100% 100%;
				padding: 15px 10px;
				text-align: center;
				width: 50%;
				margin-left: 25%;
				color: #ffe6a5;
				font-size: 16px;
				font-weight: bold;
			}

			.title_bg {
				background: url(../img/draw/title_bg2.png) no-repeat center center;
				background-size: 100% 100%;
				height: 10px;
			}

			.content {
				background: url(../img/draw/content_bg.png) no-repeat center center;
				background-size: 100% 100%;
				width: 96%;
				margin-left: 2%;
				line-height: 2;
				padding: 20px;
			}
		}

	}
	.overall {
		justify-content: center;
	}
	.zp-num {
		text-align: center;
		font-size: 16px;
		font-weight: bold;
		color: #FFFFFF;
		margin-top: 20px;

		span:nth-child(1) {}

		span:nth-child(2) {
			font-size: 20px;
			font-weight: bold;
			color: #bdff1e;
		}
	}

	.zp-top {
		background: url(../img/draw/draw.png) no-repeat center center;
		background-size: 100% 100%;
		width: 300px;
		height: 300px;
	}

	.zp-box {
		user-select: none;
		display: flex;
		justify-content: center;
		align-items: center;
		position: relative;
		width: $zp_size;
		height: $zp_size;
		left: 16px;
		top: 16px;

		/* 抽奖按钮 */
		.start-btn {
			background: url(../img/draw/start.png) no-repeat center center;
			background-size: 100% 100%;
			width: 60px;
			height: 73px;
			z-index: 1;
		}

		/* 盘子样式 */
		.panzi {
			overflow: hidden;
			border-radius: 50%;
			position: absolute;
			width: 100%;
			height: 100%;
			left: 0;
			top: 0;
			display: flex;
			justify-content: center;
			align-items: center;
			box-sizing: border-box;

			/* 每个奖项的样式 */
			.jiang {
				position: absolute;

				.title {
					font-weight: bold;
					font-size: 12px;
					color: #FF0000;
				}

				.img {
					margin: 7px auto;
					width: 36px;
					height: 36px;
					line-height: 36px;
					border: 2px dashed #f87373;
					overflow: hidden;
					color: white;

					img {
						height: 100%;
					}
				}
			}
		}

		.bck-box {
			overflow: hidden;
			position: absolute;
			width: 100%;
			height: 100%;
			left: 0;
			top: 0;
			// background: blue;
			border-radius: 50%;

			/* 转盘扇形背景 */
			.bck {
				box-sizing: border-box;
				position: absolute;
				width: 100%;
				height: 100%;
				opacity: 1;
				top: -50%;
				left: 50%;
				transform-origin: 0% 100%;
				// transform:skew(30deg);
			}

			/* 转盘背景色 */
			.bck:nth-child(2n) {
				background: #FFFFFF;
			}

			.bck:nth-child(2n + 1) {
				background: #ffdd82;
			}
		}

		/* 下面的css样式为每个奖品的旋转动画，这里写了对应8个奖品的动画，如果想要更多的话，可以添加 */
		/* 例如： .wr8  @keyframes play8 */
		.wr0,
		.wr1,
		.wr2,
		.wr3,
		.wr4,
		.wr5,
		.wr6,
		.wr7 {
			animation-duration: $time;
			animation-timing-function: ease;
			animation-fill-mode: both;
			animation-iteration-count: 1;
		}

		.wr0 {
			animation-name: play0;
		}

		.wr1 {
			animation-name: play1;
		}

		.wr2 {
			animation-name: play2;
		}

		.wr3 {
			animation-name: play3;
		}

		.wr4 {
			animation-name: play4;
		}

		.wr5 {
			animation-name: play5;
		}

		.wr6 {
			animation-name: play6;
		}

		.wr7 {
			animation-name: play7;
		}

		@keyframes play0 {
			to {
				transform: rotate(calc(5 * 360deg + 360deg / var(--nums) * 0));
			}
		}

		@keyframes play1 {
			to {
				transform: rotate(calc(5 * 360deg + 360deg / var(--nums) * 1));
			}
		}

		@keyframes play2 {
			to {
				transform: rotate(calc(5 * 360deg + 360deg / var(--nums) * 2));
			}
		}

		@keyframes play3 {
			to {
				transform: rotate(calc(5 * 360deg + 360deg / var(--nums) * 3));
			}
		}

		@keyframes play4 {
			to {
				transform: rotate(calc(5 * 360deg + 360deg / var(--nums) * 4));
			}
		}

		@keyframes play5 {
			to {
				transform: rotate(calc(5 * 360deg + 360deg / var(--nums) * 5));
			}
		}

		@keyframes play6 {
			to {
				transform: rotate(calc(5 * 360deg + 360deg / var(--nums) * 6));
			}
		}

		@keyframes play7 {
			to {
				transform: rotate(calc(5 * 360deg + 360deg / var(--nums) * 7));
			}
		}
	}
</style>
