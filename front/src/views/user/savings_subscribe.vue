<template>
	<div class="basic_wrap">
		<div class="red_top_bg">
			<div class="back_left" @click="$router.back()"></div>
			<div class="record" @click="$router.push('/savings/subscribe_record')">
				<img src="../img/user/record_b.png">
			</div>
		</div>
		<div class="savings_wrap">
			<div class="block_div savings_days_wrap">
				<div v-if="type==1" class="flex_center savings_type">
					<p class="tips margin0">{{$t('savings.cycle')}}</p>
					<p class="flexible">{{$t('savings.flexible')}}</p>
				</div>
				<p v-if="type==2" class="tips">{{$t('savings.cycle')}}</p>
				<div v-if="type==2" class="flex_center">
					<van-field v-model="days" @input="change" type="digit" :placeholder="$t('savings.cycleTips1')+minDay+$t('savings.cycleTips2')" />
					<p><span>{{$t('savings.days')}}</span></p>
				</div>
			</div>
			<div class="block_div savings_money_wrap">
				<p class="tips">{{$t('savings.subscribeMoney')}}</p>
				<div class="flex_center">
					<van-field v-model="money" type="number" :placeholder="$t('savings.subscribeMoneyPlaceholder')+common.currency_symbol_basic()+common.precision_basic(minMoney)" />
				</div>
				<p class="userBalance">{{$t('savings.userBalance')}}<span>{{common.currency_symbol_basic()}}{{common.precision_basic(userBalance)}}</span></p>
			</div>
			<div class="block_div savings_income_wrap">
				<p class="tips">{{$t('savings.subscribeIncome')}}</p>
				<div class="flex_center spacebetween">
					<p class="margin0">{{$t('savings.subscribeIncomeTips1')}}</p>
					<p class="rate">{{rate}}%</p>
				</div>
			</div>
			<div class="block_div savings_rules_wrap">
				<p class="tips">{{$t('savings.subscribeRules1')}}</p>

				<van-steps active="3">
					<van-step>T0</van-step>
					<van-step>T0</van-step>
					<van-step>T+1</van-step>
				</van-steps>
				<div class="flex_center savings_rules_name">
					<p>{{$t('savings.subscribeRules1Tips1')}}</p>
					<p>{{$t('savings.subscribeRules1Tips2')}}</p>
					<p>{{$t('savings.subscribeRules1Tips3')}}</p>
				</div>
			</div>
			<div class="block_div savings_rules_wrap">
				<p class="tips">{{$t('savings.subscribeRules2')}}</p>

				<van-steps active="3">
					<van-step>T0</van-step>
					<van-step>T0</van-step>
					<van-step>T+{{minDay}}</van-step>
				</van-steps>
				<div class="flex_center savings_rules_name">
					<p>{{$t('savings.subscribeRules2Tips1')}}</p>
					<p>{{$t('savings.subscribeRules2Tips2')}}</p>
					<p v-if="type==1">{{$t('savings.subscribeRules2Tips3')}}</p>
					<p v-if="type==2">{{$t('savings.subscribeRules2Tips4')}}</p>
				</div>
			</div>
			<div class="block_div savings_time_wrap">
				<p class="tips">{{$t('savings.subscribeTime')}}</p>
				<div class="flex_center time">
					<p>{{$t('savings.subscribeTimeTips1')}}</p>
					<p>{{time1}}</p>
				</div>
				<div class="flex_center time">
					<p>{{$t('savings.subscribeTimeTips2')}}</p>
					<p v-if="type==1">{{time2}}</p>
					<p v-if="type==2">{{$t('savings.subscribeTimeTips3')}}</p>
				</div>
			</div>
			<div class="block_div savings_detail_wrap">
				<div v-if="type==1" class="basic_btn " :class="money>0?'':'no_touch'" @click="submit">
					{{$t('savings.subscribe')}}
				</div>
				<div v-if="type==2" class="basic_btn " :class="money>0&&days>0?'':'no_touch'" @click="submit">
					{{$t('savings.subscribe')}}
				</div>
			</div>

		</div>
	</div>
</template>

<script>
	import Vue from 'vue';
	import Fetch from '../../utils/fetch'
	import {
		Field,
		Step,
		Steps
	} from 'vant';
	Vue.use(Field).use(Step).use(Steps);
	export default {
		name: "",
		data() {
			return {
				userBalance: '',
				money: '',
				days: '',
				minMoney: 0,
				minDay: 0,
				minRate: 0,
				maxRate: 0,
				rate: 0,
				incRate: 0,
				time1: '',
				time2: '',
				type: this.$router.history.current.params.code,
				loading: false,
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
				Fetch('/user/savingsSubscribeDetail', {
					type: this.type
				}).then(r => {
					this.userBalance = r.data.userBalance;
					this.minMoney = r.data.min;
					this.minDay = r.data.day;
					this.minRate = r.data.rate;
					this.maxRate = r.data.max_rate;
					this.rate = r.data.rate;
					this.incRate = r.data.inc_rate;
					this.time1 = r.data.time1;
					this.time2 = r.data.time2;
				})
			},
			change() {
				if (this.days > 0 && this.incRate > 0) {
					this.rate = (this.minRate * 1 + this.incRate * (this.days - this.minDay)).toFixed(2);
					if (this.rate < this.minRate) {
						this.rate = this.minRate;
					}
					if (this.rate > this.maxRate) {
						this.rate = this.maxRate;
					}
				}
			},
			submit() {
				if (this.loading) return false;
				if (this.type == 2 && (this.days < this.minDay)) {
					this.$toast(this.$t('savings.subscribeDaysError'));
					return false;
				}
				
				if (this.money <= 0 || this.money - this.minMoney<0) {
					this.$toast(this.$t('savings.subscribeMoneyError'));
					return false;
				}

				if (this.money - this.userBalance > 0) {
					this.$toast(this.$t('savings.userMoneyNotEnough'));
					return false;
				}
				this.loading = true;
				Fetch('/user/savingsSubscribe', {
					type: this.type,
					money: this.money,
					days: this.days
				}).then(r => {
					this.$router.replace('/savings/subscribe_record');
				})


			}
		}
	};
</script>

<style lang="less" scoped>
	.basic_wrap {
		margin-bottom: 70px;
	}

	.red_top_bg {
		position: fixed;
		top: 0;
		z-index: 10;
		background-color: #FFFFFF;

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

	.savings_wrap {
		margin-top: 54px;

		.tips {
			margin-bottom: 10px;
			font-size: 14px;
			color: #999;
		}

		.savings_days_wrap {
			padding: 20px;
			margin-bottom: 10px;
			font-size: 14px;
			color: #999;

			.savings_type {
				justify-content: space-between;
			}

			.margin0 {
				margin-bottom: 0;
			}
		}

		.savings_money_wrap {
			padding: 20px;
			margin-bottom: 10px;
			font-size: 14px;
			color: #999;

			.userBalance {
				padding: 10px 15px;
				font-size: 12px;

				span {
					color: #000;
					margin-left: 5px;
				}
			}
		}

		.savings_income_wrap {
			padding: 20px;
			margin-bottom: 10px;

			.spacebetween {
				justify-content: space-between;
				padding: 10px;
			}

			.margin0 {
				margin-bottom: 0;
			}

			.rate {
				color: #999;
			}
		}

		.savings_rules_wrap {
			padding: 20px;
			margin-bottom: 10px;

			.savings_rules_name {
				padding: 0 10px;
				justify-content: space-between;

				p:nth-child(1) {
					text-align: left;
				}

				p:nth-child(2) {
					text-align: center;
				}

				p:nth-child(3) {
					text-align: right;
				}

				p {
					width: 33%;

				}
			}
		}

		.savings_time_wrap {
			padding: 20px;
			margin-bottom: 10px;
			.tips{
				margin-bottom:20px;
			}
			.time {
				justify-content: space-between;

				p {
					margin-bottom: 10px;
				}
			}
		}

		.savings_detail_wrap {
			position: fixed;
			bottom: 0px;
			width: 100%;
			margin: 0;
			justify-content: space-between;
			padding: 15px;

			.detail_money {
				color: #FF0000;
				font-weight: bold;
				font-size: 16px;
				margin-left: 5px;
			}

			.btn {
				width: unset;
			}
		}
	}
</style>
