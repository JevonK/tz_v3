<template>
	<div class="basic_wrap">
		<bsHeader :title="$t('savings.redeemRecord')" @backurl="$router.back()"></bsHeader>
		<div class="item_wrap">
			<div class="item_list">
				<van-list v-model="loading" loading-text=" " offset="0" :finished="finished"
					:finished-text="$t('utils.noData')" @load="onLoad">
					<div class="block_div item" v-for="(item,index) in list">
						<div class="flex_center time">
							<p>{{item.act_time}}</p>
							<p class="color_blue">{{$t('tabs.done')}}</p>
						</div>
						<div class="detail">
							<van-cell-group :border="false">
								<van-cell :title="$t('savings.redeemRecordMoney')" value-class="value_class" :border="false"
									:value="common.currency_symbol_basic()+common.precision(item.money)" />
								<!-- <van-cell :title="$t('savings.redeemRecordType')" value-class="value_class" :border="false"
									:value="item.type==1?$t('savings.redeemRecordTypeTips1'):$t('savings.redeemRecordTypeTips2')" /> -->
							</van-cell-group>
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
		List,
		Cell,
		CellGroup
	} from 'vant';
	Vue.use(List).use(Cell).use(CellGroup);
	export default {
		name: "recharge",
		components: {
			bsHeader
		},
		data() {
			return {
				data: [],
				loading: false,
				finished: false,
				list: [],
				page: 1,
				listRows: 8
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
		mounted() {},
		methods: {
			sort(type) {
				this.page = 1;
				this.list = [];
				this.finished = false;
				this.loading = true;
				this.onLoad();
			},
			onLoad() {

				Fetch('/user/savingsRedeemRecord', {
					page: this.page,
					listRows: this.listRows
				}).then(r => {
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
	.item_wrap {
		width: 100%;
		padding: 44px 0 0 0;

		.item_list {
			margin-top: 10px;

			.item {
				padding: 20px;
				background: #FFFFFF;
				margin-bottom: 10px;

				.time {
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

				.detail {
					line-height: 2;

					.title {
						font-size: 14px;
						font-weight: bold;
						margin-bottom: 10px;
					}

					.value_class {
						font-weight: bold;
						color: #3CB371;
					}
				}

			}

		}
	}

	/deep/ .van-cell {
		font-size: unset;
		padding: 5px 0;
	}
</style>
