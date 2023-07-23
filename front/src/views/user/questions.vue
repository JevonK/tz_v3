<template>
	<div class="basic_wrap">
		<bsHeader :title="$t('user.faq')" @backurl="$router.back()"></bsHeader>
		<div class="block_div wrap_box">
			<div class="item">
				<van-cell is-link v-for="(item,index) in data.list" :key="index"
					@click="$router.push('article/'+item.id)">
					<template #title>
						<div class="custom-title">{{item.title}}</div>
					</template>
				</van-cell>
			</div>
		</div>
	</div>
</template>

<script>
	import Vue from 'vue';
	import bsHeader from '../../components/bsHeader.vue'
	import Fetch from '../../utils/fetch'
	import {
		Cell,
		CellGroup
	} from 'vant';
	Vue.use(Cell).use(CellGroup);
	export default {
		name: "",
		components: {
			bsHeader
		},
		data() {
			return {
				data: [],
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
			Fetch('/index/questions').then(r => {
				this.data = r.data;
			})
		},
		methods: {}
	};
</script>

<style lang="less" scoped>
	.wrap_box {
		margin-top: 54px;

		.custom-title {
			overflow: hidden;
			text-overflow: ellipsis;
			white-space: nowrap;
		}
	}

	/deep/ .van-cell__title {
		width: 80%;
	}
	/deep/.van-cell{
		padding: 15px;
	}
</style>
