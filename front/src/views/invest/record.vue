<template>
	<div class="basic_wrap">
		<bsHeader :title="$t('user.inviteRecord')" @backurl="$router.back()"></bsHeader>
		<div class="item_wrap">
			<div class="item_list">
				<van-list v-model="loading" loading-text=" " offset="0" :finished="finished"
					:finished-text="$t('utils.noData')" @load="onLoad">
					<div class="block_div item" v-for="(item,index) in list">
						<div class="flex_center time">
							<p>{{item.time_actual}}</p>
							<p class="color_blue" v-if="item.status==0">{{$t('tabs.ongoing')}}</p>
							<p class="color_green" v-if="item.status==1">{{$t('tabs.done')}}</p>
						</div>
						<div class="detail-f">
							<div class="detail" >
								<div class="title">
									{{item.title}}
								</div>
								<p class="detail-p" v-if="item.source == 3">
									<span class="lable-class">{{ $t('invest.amount') }}</span>
									<span class="value_class">0</span>
								</p>
								<p class="detail-p" v-else>
									<span class="lable-class">{{ $t('invest.amount') }}</span>
									<span class="value_class">{{ common.currency_symbol_basic()+common.precision_basic(item.money) }}</span>
								</p>
								<p class="detail-p">
									<span class="lable-class"> {{ $t('invest.cycle') }}</span>
									<span class="value_class">{{  item.day+(item.type==3?$t('index.hour'):$t('index.day')) }}</span>
								</p>
								<p class="detail-p" v-if="item.type==1 || item.type==4">
									<span class="lable-class">{{ (item.type==1 || item.type==4)?$t('index.dailyRate'):$t('index.rate') }}</span>
									<span class="value_class">{{ common.precision_basic(item.total_interest/item.day) }}</span>
								</p>
								<p class="detail-p">
									<span class="lable-class">{{ $t('invest.income') }}</span>
									<span class="value_class">{{ common.currency_symbol_basic()+common.precision_basic(item.total_interest) }}</span>
								</p>
							</div>
							<div class="detail-img" style="float: left;">
								<img :src="item.img" alt="">
								<p v-if="item.is_receive == 1 && item.pause_time == 0" class="receive" @click="receive_click(item)">Receive</p>
								<p v-else class="receive-disabled" :color="'#707070b0'" >Receive</p>
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
		List,
		Dialog,
		Cell,
		CellGroup
	} from 'vant';
	Vue.use(List).use(Cell).use(CellGroup).use(Dialog);
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
			if (window.plus) {
				plus.navigator.setStatusBarBackground('#FFFFFF');
				plus.navigator.setStatusBarStyle('dark');
			}
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

				Fetch('/user/investRecord', {
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
			},
			receive_click (item) {
				this.loading = true;
				Fetch("/user/invest_settle", {
					'invest_id' : item['id'],
					'type' : item['type'],
				}).then((res) => {
					Dialog.alert({
						message: item['title'] + " income：" + res.data.income,
						confirmButtonText: this.$t('utils.confirm')
					}).then(() => {
						window.location.reload();
					})
					this.loading = false;
				}).catch((res) => {
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
					float: left;
					width: 70%;
					.detail-p {
						padding: 5px 0;
						overflow: hidden;
					}

					.title {
						font-size: 14px;
						font-weight: bold;
						margin-bottom: 10px;
					}

					.lable-class {
						float: left;
					}
					.value_class {
						font-weight: bold;
						color: #3CB371;
						word-break:normal; 
						width:100px; 
						display:block; 
						white-space:pre-wrap;
						word-wrap : break-word ;
						overflow: hidden ;
						float: left;
					}
				}
				.detail-f {
					overflow: hidden;
				}
				.detail-img {
					width: 30%;
					img {
						width: 100%;
						height: 100px;
						margin-top: 36px;
					}
				}

			}

		}
	}

	.receive {
		padding: 5px 10px;
		background: #3775f4;
		margin: 0 10px 8px 0;
		color: #FFFFFF;
		border-radius: 8px;
		text-align: center;
		width: 100%;
	}
	.receive-disabled {
		padding: 5px 10px;
		background: #8e8e8f;
		margin: 0 10px 8px 0;
		color: #FFFFFF;
		border-radius: 8px;
		text-align: center;
		width: 100%;
	}

	/deep/ .van-cell {
		font-size: unset;
		padding: 5px 0;
		.van-cell__value {
			text-align: left;
		}
	}
</style>
