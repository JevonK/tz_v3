<template>
	<div class="basic_wrap">
		<div class="red_top_bg">
			<div class="back_left" @click="$router.back()"></div>
			<div class="record" @click="$router.push('/recharge/record')">
				<img src="../img/user/record_b.png">
			</div>
		</div>
		<div class="recharge_wrap">
            <div class="block_div common_money_wrap">
				<p class="tips">{{$t('recharge.money')}}</p>
				<div class="flex_center common_money">
					<p v-for="(v,i) in commonMoney" @click="money=v;active_money=i;"
						v-bind:class="{active_money: active_money==i}">{{common.currency_symbol_basic()}}{{v}}</p>
				</div>
			</div>
			<div class="block_div recharge_money_wrap">
				<p class="tips">{{$t('recharge.anotherMoney')}}</p>
				<div class="flex_center">
					<van-field v-model="money" type="number" :placeholder="$t('recharge.moneyMinPlaceholder')+common.currency_symbol_basic()+minMoney" />
				</div>
			</div>
			<div class="block_div recharge_method_wrap">
				<p class="tips">{{$t('recharge.method')}}</p>
				<div class="recharge_item">
					<div class="flex_center item" v-for="(item,index) in rechargeMethod"
						v-bind:class="{active_item: active==index}" @click="active = index">
						<div class=" flex_center item_name">
							<img :src="item.logo">
							<p>{{item.name}}</p>
						</div>
						<div class="gou" v-bind:class="{active_gou: active==index}">
							<span>√</span>
						</div>
					</div>
				</div>
			</div>
			<div class="block_div flex_center recharge_detail_wrap">
				<div>
					<!-- {{$t('recharge.money')}} <span class="detail_money">{{common.currency_symbol_basic()}}{{money}}</span> -->
				</div>
				<div class="basic_btn btn" @click="submit">
					Confirm
				</div>
			</div>
		</div>
		<van-dialog v-model:show="show_tips" title="" :show-confirm-button="false">
			<div class="tips-show">
				<van-icon name="close" color="#333" size="30" @click="show_tips=false" />
				<h3>{{ $t('invest.recharge_instructions') }}</h3>
				<p size="10">
					{{ $t('invest.recharge_notes') }}<br>
					{{ $t('invest.recharge_title1') }}<br>
					{{ $t('invest.recharge_title2') }}<br>
					{{ $t('invest.recharge_title3') }}<br>
					{{ $t('invest.recharge_title4') }}
				</p>
				<div class="basic_btn tips-btn"  @click="show_tips=false">
					OK
				</div>
			</div>
		</van-dialog>
	</div>
</template>

<script>
	import Vue from 'vue';
	import Fetch from '../../utils/fetch'
	import {
		Field,
		RadioGroup, Radio,
		Dialog,
		Icon
	} from 'vant';
	Vue.use(Field).use(RadioGroup).use(Radio).use(Dialog).use(Icon);
	export default {
		name: "",
		data() {
			return {
				commonMoney: [],
				userBalance: '',
				money: '',
				rechargeMethod: [],
				active: 0,
				show_tips: false,
				pay_code: '936',
				active_money: -1,
				minMoney:0,
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
			this.show_tips = true
		},
		methods: {
			start() {
				Fetch('/user/getRechargeMethod').then(r => {
					this.userBalance = r.data.userBalance;
					this.commonMoney = r.data.commonMoney;
					this.rechargeMethod = r.data.rechargeMethod;
					this.minMoney = r.data.minMoney;
				})
			},
			submit() {
				//银行卡
				if (this.rechargeMethod[this.active]['type'] == 4) {
					this.$router.replace('/recharge/bank?money=' + this.money + "&id=" + this.rechargeMethod[this.active][
						'id'
					]);
				} else if (this.rechargeMethod[this.active]['type'] == 3) {
                    if (this.money == "") {
						this.$toast(this.$t('recharge.moneyEmpty'));
						return false;
					}
					if (this.money <=0) {
						this.$toast(this.$t('recharge.moneyError'));
						return false;
					}
					if (this.money - this.minMoney < 0) {
						this.$toast(this.$t('recharge.moneyMinError')+this.common.currency_symbol_basic()+this.minMoney);
						return false;
					}
					this.loading = true;
					Fetch('/user/recharge',{
						'money': this.money,
						'pay_code': 11,
						'id': this.rechargeMethod[this.active]['id'],
					}).then(r => {
						console.log(r.data);
						window.location.href = r.data.paymentUrl
					}).catch(() => {
						this.loading = false;
					})
                } else {
					this.$router.replace('/recharge/qrcode?money=' + this.money + "&id=" + this.rechargeMethod[this.active]
						['id']);
				}
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

	.recharge_wrap {
		margin-top: 54px;

		.tips {
			margin-bottom: 20px;
			font-size: 14px;
			color: #999;
		}

		.common_money_wrap {
			padding: 20px;
			margin-bottom: 10px;

			.common_money {
				justify-content: space-around;

				p {
					padding: 12px 8px;
					margin-left: 5px;
					background: #dfdfdf;
					border-radius: 5px;
				}

				.active_money {
					background: #1989fa;
					color: #FFFFFF;
				}
			}

		}

		.recharge_money_wrap {
			padding: 20px;
			margin-bottom: 10px;
			font-size: 14px;
			color: #999;
		}

		.recharge_method_wrap {
			padding: 20px;

			.recharge_item {
				.item {
					justify-content: space-between;
					border: 2px solid #dfdfdf;
					border-radius: 54px;
					margin-bottom: 15px;
					padding: 10px 15px;

					.item_name {
						p {
							margin-left: 10px;
						}
					}

					img {
						width: 32px;
						height: 32px;
					}

					.gou {
						padding: 5px;
						background: #dfdfdf;
						color: #FFFFFF;
						border-radius: 50%;
						width: 20px;
						height: 20px;
						text-align: center;
					}

					.active_gou {
						background: #1989fa;
					}
				}

				.active_item {
					border: 2px solid #1989fa;
				}
			}
		}

		.recharge_detail_wrap {
			position: fixed;
			bottom: 0px;
			width: 100%;
			max-width: 750px;
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
				height: unset;
				line-height: unset;
				padding: 5px 15px;
				margin-left: unset;
			}
		}
	}
	.tips-btn {
		height: 30px;
		line-height: 30px;
		width: 56%;
		margin: 0 auto;
		margin-top: 15px;
	}
	.tips-show {
		width: 80%;
		
		margin: 0 auto;
		margin-top: 139px;
	}
	/deep/ .van-radio{
		margin: 10px 0;
	}

	/deep/ .van-dialog {
		width: 80%;
		height: 70%;
		background-color: transparent;
		background-repeat: no-repeat;
		background-size: 100% 100%;
		background-position: center;
		background-image: url(../img/user/recharge_tips.png);
	}
	/deep/ .van-icon {
		position: absolute;
		top: 100px;
		right: 12px;
	}
</style>
