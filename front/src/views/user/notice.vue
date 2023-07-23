<template>
	<div class="basic_wrap">
		<bsHeader :title="$t('index.notice')" @backurl="$router.back()"></bsHeader>
		<div class="item_wrap">
			<div class="item_list">
				<van-list v-model="loading" loading-text=" " offset="0" :finished="finished"
					:finished-text="$t('utils.noData')" @load="onLoad">
					<div class="block_div item">
						<van-collapse v-model="activeNames">
							<van-collapse-item v-for="(item,index) in list" :key="index" :name="index">
								<template #title>
									<div class="notice_title">{{item.title}}</div>
									<div class="notice_time">{{item.time}}</div>
								</template>
								<div v-html="item.content"></div>
							</van-collapse-item>
						</van-collapse>
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
		Collapse,
		CollapseItem
	} from 'vant';
	Vue.use(List).use(Collapse).use(CollapseItem);
	export default {
		name: "notice",
		components: {
			bsHeader
		},
		data() {
			return {
				data: [],
				empty: false,
				loading: false,
				finished: false,
				list: [],
				page: 1,
				listRows: 10,
				activeNames: [0]
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
			precision(money) {
				var precision = localStorage.getItem('precision_basic');
				return (money / 1).toFixed(precision);
			},
			sort(type) {
				this.page = 1;
				this.empty = false;
				this.list = [];
				this.finished = false;
				this.loading = true;
				this.onLoad();
			},
			onLoad() {
				Fetch('/index/notice_list', {
					page: this.page,
					listRows: this.listRows
				}).then(r => {
					if (r.data.length == 0) this.empty = true;
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
				.notice_time {
					font-size: 12px;
					color: #999;
				}

				.notice_title {
					overflow: hidden;
					text-overflow: ellipsis;
					white-space: nowrap;
				}
			}
		}
	}

	/deep/ .van-cell__title {
		width: 80%;
	}
	/deep/ .van-cell{
		padding: 15px;
	}
</style>
