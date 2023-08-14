<template>
	<div class="basic_wrap">
		<div class="red_top_bg">
			<div class="back_left" @click="$router.back()"></div>
			<div class="big_tit">{{item.title}}</div>
		</div>
		<div class="item_info_bg"></div>
		<div class="block_div item_info info1">
			<div class="title">
				{{item.title}}
			</div>
			<div class="detail">
				<van-cell-group :border="false">
					<van-cell :title="item.type==1?$t('index.dailyRate'):$t('index.rate')" value-class="value_class"
						:border="false" :value="item.rate+'%'" />
					<van-cell :title="$t('invest.cycle')" value-class="value_class" :border="false"
						:value="item.day+(item.type==3?$t('index.hour'):$t('index.day'))" />
					<van-cell :title="$t('invest.amount')" value-class="value_class" :border="false"
						:value="common.currency_symbol_basic()+common.precision_basic(item.min)" />
					<!-- <van-cell :title="$t('invest.type')" value-class="value_class" :border="false"
						:value="$t('index.method'+item.type)" /> -->
					<van-cell :title="$t('invest.income')" value-class="value_class" :border="true" 
						:value="common.currency_symbol_basic() + income " />
				</van-cell-group>
			</div>
		</div>
		
		<!-- <div class="block_div proCharts" ref='charts'>
		</div> -->
		
		<div class="block_div item_detail">
			<div class="title">
				{{$t('invest.detail')}}
			</div>
			<div class="content" v-html="item.content"></div>
		</div>
		<div class="btn-gr">
			<div class="basic_btn btn" @click="showPopupClick(false)">
				{{$t('invest.investBalance')}} 5%
			</div>
			<div class="basic_btn btn" @click="showPopupClick(true)">
				{{$t('invest.investWithdrawal')}}
			</div>
		</div>
		
		<van-popup v-model:show="showPopup" position="bottom" closeable close-icon-position="top-left">
			<div class="item_info popup_info">
				<div class="title">
					{{$t('invest.orderInfo')}}
				</div>
				<div class="detail">
					<van-cell-group :border="false">
						<van-cell :title="item.type==1?$t('index.dailyRate'):$t('index.rate')" value-class="value_class"
							:border="false" :value="item.rate+'%'" />
						<van-cell :title="$t('invest.cycle')" value-class="value_class" :border="false"
							:value="item.day+(item.type==3?$t('index.hour'):$t('index.day'))" />
						<van-cell v-if="!is_withdrawal_purchase" :title="$t('invest.amount')" value-class="value_class" :border="false"
							:value="common.currency_symbol_basic()+common.precision_basic(item.min)" />
						<van-cell v-else :title="$t('invest.amount')" value-class="value_class" :border="false"
							:value="common.currency_symbol_basic()+common.precision_basic(item.min*item.withdrawal_purchase/100)" />
						<!-- <van-cell :title="$t('invest.type')" value-class="value_class" :border="false"
							:value="$t('index.method'+item.type)" /> -->
						<van-cell v-if="!is_withdrawal_purchase" v-show="user.login" :title="$t('invest.paymentType')" value-class="value_class"
							:border="false"
							:value="$t('user.fundingAccount')+' ('+common.currency_symbol_basic()+common.precision_basic(user.balance)+')'" />
						<van-cell v-else v-show="user.login" :title="$t('invest.paymentType')" value-class="value_class"
							:border="false"
							:value="$t('user.withdrawAccount')+' ('+common.currency_symbol_basic()+common.precision_basic(user.withdrawable)+')'" />
					</van-cell-group>
				</div>
				<div class=" flex_center invest_detail_wrap">
					<div>
						{{$t('invest.income')}} <span class="detail_money">{{common.currency_symbol_basic()}}{{income}} </span>
					</div>
					<div class="basic_btn sbtn" @click="submit">
						{{$t('invest.submit')}}
					</div>
				</div>
			</div>
		</van-popup>
	</div>
</template>

<script>
	import Vue from 'vue';
	import bsHeader from '../../components/bsHeader.vue'
	import Fetch from '../../utils/fetch'
	import * as echarts from 'echarts';
	import {
		Icon,
		Popup,
		Field,
		Cell,
		CellGroup
	} from 'vant';
	Vue.use(Icon).use(Popup).use(Field).use(Cell).use(CellGroup);
	export default {
		name: "",
		components: {
			bsHeader
		},
		data() {
			return {
				data: [],
				item: {
					min: 0,
					type: 1,
					rate: 0,
					day: 0
				},
				user: [],
				money: "",
				income: 0,
				showPopup: false,
				loading: false,
				is_withdrawal_purchase: false,
			};
		},
		created() {
			if (window.plus) {
				plus.navigator.setStatusBarBackground('#0F6EFF');
				plus.navigator.setStatusBarStyle('light');
			}
			this.$parent.footer('user', false);
		},
		mounted() {
			this.start();
		},
		methods: {
			showPopupClick(type) {
				this.showPopup = true;
				this.is_withdrawal_purchase = type;
			},
			start() {
				Fetch('/index/item_detail', {
					id: this.$router.history.current.params.code
				}).then(r => {
					this.item = r.data.item;
					this.user = r.data.user;
					this.change();
					// this.mycharts(this.item.k_x,this.item.k_y_12m);
				})
			},
			change() {
				if (this.item.type == 1) {
					this.income = this.common.precision_basic(this.item.min * this.item.rate * this.item.day / 100);
				} else {
					this.income = this.common.precision_basic(this.item.min * this.item.rate / 100);
				}
			},
			submit() {
				if (this.loading) return false;
				if (!this.user.login) {
					this.$toast(this.$t('invest.loginFirst'));
					return false;
				}
				if (this.user.limit) {
					var times = this.item.num;
					if (this.item.num >= 2) times = 2;
					this.$toast(this.$t('invest.investNum') + this.item.num + this.$t('utils.times' + times));
					return false;
				}
				// if (this.user.balance-this.item.min<0) {
				// 	this.$toast(this.$t('invest.moneyNotEnough'));
				// 	return false;
				// }
				if (this.user.limit_today) {
					this.$toast(this.$t('invest.investNumEmpty'));
					return false;
				}
				this.loading = true;
				Fetch('/user/invest', {
					id: this.item.id,
					is_withdrawal_purchase: this.is_withdrawal_purchase
				}).then(r => {
					this.$router.replace('/invest/record');
				}).catch(e => {
					this.loading = false;
				})

			},
			mycharts(k_x,k_y) {
				let myChart = echarts.init(this.$refs.charts, "macarons");
				myChart.setOption({
					 tooltip: {
					    trigger: 'axis',
						confine:true,
					    position: function (pt) {
					      return [pt[0], '10%'];
					    }
					  },
					  xAxis: {
					    type: 'category',
					    boundaryGap: false,
					    data: k_y
					  },
					  yAxis: {
					    type: 'value',
					    // boundaryGap: [0, '100%'],
						// splitNumber:7
					  },
					  grid:[{
						  top :'15px'
					  }],
					  dataZoom: [
					    {
					      type: 'inside',
					      start: 90,
					      end: 100
					    },
					    {
					      start: 90,
					      end: 100
					    }
					  ],
					  series: [
					    {
					      name: '',
					      type: 'line',
					      symbol: 'none',
					      sampling: 'lttb',
					      itemStyle: {
					        color: 'rgb(255, 70, 131)'
					      },
					      areaStyle: {
					        color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
					          {
					            offset: 0,
					            color: 'rgb(15, 110, 255)'
					          },
					          {
					            offset: 1,
					            color: 'rgb(17, 232, 255)'
					          }
					        ])
					      },
					      data: k_x
					    }
					  ]
					
				});
				//图表自适应
				window.addEventListener("resize", function() {
					myChart.resize() // myChart 是实例对象
				})
			}
		}
	};
</script>

<style lang="less" scoped>
	.proCharts {
		margin-bottom: 10px;
		padding-top:15px;
		height:268px;
	}

	.basic_wrap {
		margin-bottom: 60px;
	}
	.red_top_bg{
		background: #0F6EFF;
		position: fixed;
		z-index: 10;
		box-shadow: unset;
		.back_left{
			background: url(../img/common/back.png) no-repeat center center;
			width: 30px;
			height: 22px;
		}
		.big_tit{
			color: #FFFFFF;
			left: 55px;
		}
	}
	.item_info_bg{
		background: #0F6EFF;
		width: 100%;
		height: 168px;
		border-radius:  0 0 30px 30px;
	}

	.item_title {
		margin-top: 54px;
		line-height: 2;
		font-size: 16px;
		font-weight: bold;
		padding: 10px 20px;
	}

	.logo {
		margin-bottom: 10px;

		img {
			width: 100%;
		}
	}

	.item_info {
		margin-bottom: 10px;
		.title {
			padding: 15px 0 10px 15px;
			font-size: 16px;
			border-bottom: 1px solid #F7F7F7;
		}

		.detail {
			padding: 10px 0;
			line-height: 2;

			.value_class {
				font-weight: bold;
				color: #3CB371;
			}
		}
	}
	.info1{
		margin-top: -116px;
	}

	.popup_info {
		.title {
			text-align: center;
			padding: 16px;
		}

		.invest_amount {
			color: #999;
			padding: 15px 20px 0 0;
			font-size: 14px;
		}

		.invest_detail_wrap {
			width: 100%;
			margin: 0;
			justify-content: space-between;
			padding: 15px;
			border-top: 1px solid #F7F7F7;

			.detail_money {
				color: #FF0000;
				font-weight: bold;
				font-size: 16px;
				margin-left: 5px;
			}

			.sbtn {
				width: unset;
				height: unset;
				line-height: unset;
				padding: 5px 15px;
			}
		}
	}

	.item_detail {
		margin-bottom: 20px;

		.title {
			padding: 15px 0 10px 15px;
			font-size: 16px;
			border-bottom: 1px solid #F7F7F7;
		}

		.content {
			padding: 10px 15px 15px 15px;
			line-height: 2;

		}
	}

	.btn-gr {
		width: 100%;
		position: fixed;
		bottom: 8px;
	}
	.btn {
		float: left;
		// position: fixed;
		// bottom: 20px;
		width: 36%;
		margin-left: 10%;
	}

	/deep/ .van-cell {
		font-size: unset;
		padding: 5px 16px;
	}
</style>
