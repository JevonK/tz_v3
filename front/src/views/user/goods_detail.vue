<template>
	<div class="basic_wrap">
		<bsHeader :title="$t('goods.myPoint')+user.point" @backurl="$router.back()"></bsHeader>
		<div class="goods_wrap">
			<div class="img">
				<img :src="goods.img" alt="" srcset="">
			</div>
			<div class="block_div detail">
				<div class="price">
					<span class="point">{{goods.point}}</span>
					<span class="point_tips">{{$t('goods.point')}}</span>
				</div>
				<div class="title">
					{{goods.title}}
				</div>
			</div>
			<div class="block_div content" v-html="goods.content">

			</div>
		</div>
		<div class="basic_btn sbtn" @click="submit">
			{{$t('goods.exchange')}}
		</div>
	</div>
</template>

<script>
	import Vue from 'vue';
	import bsHeader from '../../components/bsHeader.vue'
	import Fetch from '../../utils/fetch'
	export default {
		name: "goodsDetail",
		components: {
			bsHeader
		},
		data() {
			return {
				goods: [],
				user: {
					point: 0,
				},
				loading: false
			};
		},
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
				Fetch('/index/goods_detail', {
					id: this.$router.history.current.params.code
				}).then(r => {
					this.goods = r.data.goods;
					this.user = r.data.user;
				})
			},
			submit() {
				if (this.loading) return false;
				if (this.user.point < this.goods.point) {
					this.$toast(this.$t('draw.pointsEmpty'));
					return false;
				}
				this.loading = true;
				this.user.point = this.user.point - this.goods.point;
				Fetch('/user/goods_exchange', {
					id: this.goods.id
				}).then(r => {
					this.$router.replace('/goods/record');
				})
			}
		}
	};
</script>

<style lang="less" scoped>
	.goods_wrap {
		margin-top: 54px;

		.img {
			img {
				width: 100%;
			}
		}

		.detail {
			padding: 20px;
			margin: 10px 0 10px 0;
			width: 100%;

			.point {
				font-size: 18px;
				font-weight: bold;
				color: #FF0000;
			}

			.point_tips {
				margin-left: 5px;
			}

			.title {
				margin-top: 15px;
				line-height: 2;
				font-weight: bold;
			}
		}

		.content {
			margin: 10px 0 10px 0;
			width: 100%;
			// padding: 20px;
			line-height: 2;
		}
	}

	.sbtn {
		position: fixed;
		bottom: 20px;
		background: #f24d0c;
	}
</style>
