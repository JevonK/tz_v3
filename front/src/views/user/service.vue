<template>
	<div class="kefu_wrap">
		<bsHeader :title="$t('user.onlineService')" @backurl="$router.back()"></bsHeader>
		<div class="list">
			<div class="block_div item" v-for="(item,index) in data.list">
				<van-cell is-link @click="$router.push('/service/'+item.id)">
					<template #title>
						<img :src="item.logo" alt="" />
						<span class="custom-title">{{item.title}}</span>
					</template>
				</van-cell>
			</div>

		</div>
	</div>

</template>

<script>
	import Vue from 'vue';
	import Fetch from "../../utils/fetch";
	import bsHeader from '../../components/bsHeader.vue'
	import {
		Cell,
		CellGroup
	} from "vant";

	Vue.use(Cell).use(CellGroup);
	export default {
		name: "service",
		components: {
			bsHeader
		},
		data() {
			return {
				data: {},
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
				Fetch('/index/service_list').then(res => {
					this.data = res.data;
				})
			},
		}
	};
</script>

<style lang="less" scoped>
	/deep/ .van-cell__right-icon {
		line-height: 40px;
	}

	.list {
		margin-top: 55px;
		border-radius: 5px;

		img {
			vertical-align: middle;
			height: 40px;
			width: 40px;
			margin-right: 15px;
		}

		.item {
			margin-bottom: 10px;
		}
	}
</style>
