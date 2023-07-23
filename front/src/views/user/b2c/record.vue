<template>
	<div class="basic_wrap">
		<bsHeader title="订单列表" @backurl="$router.back()"></bsHeader>
		<div class="item_wrap">
			<div class="item_list">
				<van-list v-model="loading" offset="0" :finished="finished" finished-text="没有更多了" @load="onLoad">
					<div class="item" v-for="(item,index) in list">
						<div class="flex_center">
							<p>2022-02-01 18:01:02</p>
							<p>已完成{{index}}</p>
						</div>
						<div class="flex_center">
							<p>单价：6.32 CNY</p>
						</div>
						<div class="flex_center">
							<p>数量：1000 TRX</p>
							<p class="blue">30000.00 CNY</p>
						</div>
					</div>
				</van-list>
				<!-- 
				<div class="item">
					<div class="flex_center">
						<p>2022-02-01 18:01:02</p>
						<p>已完成</p>
					</div>
					<div class="flex_center">
						<p>单价：6.32 CNY</p>
					</div>
					<div class="flex_center">
						<p>数量：1000 TRX</p>
						<p class="blue">30000.00 CNY</p>
					</div>
				</div>
				<div class="item">
					<div class="flex_center">
						<p>2022-02-01 18:01:02</p>
						<p class="red">失败</p>
					</div>
					<div class="flex_center">
						<p>单价：6.32 CNY</p>
					</div>
					<div class="flex_center">
						<p>数量：1000 TRX</p>
						<p class="red">30000.00 CNY</p>
					</div>
				</div>
				<div class="item">
					<div class="flex_center">
						<p>2022-02-01 18:01:02</p>
						<p class="green">进行中</p>
					</div>
					<div class="flex_center">
						<p>单价：6.32 CNY</p>
					</div>
					<div class="flex_center">
						<p>数量：1000 TRX</p>
						<p class="green">30000.00 CNY</p>
					</div>
				</div> -->
				<van-empty description="暂无记录" v-if="empty" />
			</div>
		</div>
	</div>
</template>

<script>
	import Vue from 'vue';
	import bsHeader from '../../components/bsHeader.vue'
	import Fetch from '../../utils/fetch'
	import {
		List,
		Empty
	} from 'vant';
	Vue.use(List).use(Empty);
	export default {
		name: "b2cRecord",
		components: {
			bsHeader
		},
		data() {
			return {
				data: [],
				empty: false,
				loading: false,
				finished: false,
				list: []
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
			this.$parent.footer('user', false);
		},
		mounted() {
			// Fetch('/user/').then(r => {
			// })
		},
		methods: {
			onLoad() {
				for (let i = 0; i < 8; i++) {
					this.list.push({
						price: i
					});
				}
				this.loading = false;
				if (this.list.length >= 100) {
					this.finished = true;
				}
			}
		}
	};
</script>

<style lang="less" scoped>
	.item_wrap {
		width: 100%;
		padding: 44px 0 0 0;

		.item_list {
			width: 96%;
			margin: 10px 2% 0 2%;

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
					margin-bottom: 20px;
					color: #999;

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

				div:nth-child(3) {
					justify-content: space-between;
					color: #999;

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

			}

		}
	}

	.pending {
		border-bottom: 4px solid #ECECEC !important;
	}

	.red {
		color: #FF0000;
	}

	.green {
		color: #3CB371;
	}

	.blue {
		color: #3775f4;
	}
</style>
