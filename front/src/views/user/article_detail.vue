<template>
	<div class="newdetail_wrap">
		<bsHeader title="" @backurl="$router.back()"></bsHeader>
		<div class="ctn">
			<div class="title">
				{{data.title}}
			</div>
			<div class="contract_box" v-html="data.content"></div>
		</div>
	</div>
</template>

<script>
	import Fetch from "../../utils/fetch";
	import bsHeader from '../../components/bsHeader.vue'

	export default {
		name: "aritcle",
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
				Fetch('/index/article_detail', {
					id: this.$router.history.current.params.code
				}).then(res => {
					this.data = res.data;
				})
			},
		}
	};
</script>

<style lang="less" scoped>
	.newdetail_wrap {
		overflow-x: hidden;
		overflow-y: auto;
	}

	.newdetail_wrap .ctn {
		width: 94%;
		margin: 54px 3% 0 3%;
	}

	.contract_box {
		line-height: 2;
		margin-top: 10px;
	}

	.newdetail_wrap .ctn .title {
		font-size: 16px;
		font-weight: bold;
		line-height: 2;
		border-bottom: 1px solid #ECECEC;
	}

	.newdetail_wrap .ctn .info {
		font-size: 12px;
		color: #333;
		line-height: 1.7;
	}
</style>
