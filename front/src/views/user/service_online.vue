<template>
	<div class="kefu_v">
		<iframe :src="url"></iframe>
	</div>
</template>

<script>
	import Fetch from '../../utils/fetch'

	export default {
		name: "index",
		components: {},
		data() {
			return {
				data: {},
				url: ''
			};
		},
		created() {
			if (window.plus) {
				plus.navigator.setStatusBarBackground('#FFFFFF');
				plus.navigator.setStatusBarStyle('dark');
			}
			this.$parent.footer('user');
		},
		mounted() {
			this.start();
		},
		methods: {
			start() {
				Fetch('/index/service_detail', {
					id: this.$router.history.current.params.code
				}).then(res => {
					if (res.data.service.type == 1) {
						this.url = res.data.service.url;
					} else {
						window.location.href = res.data.service.url;
						setTimeout(() => {
							this.$router.back();
						}, 3000);
					}
				})
			},
		}
	};
</script>

<style lang="less" scoped>
	.kefu_v {
		position: fixed;
		width: 100%;
		height: 100%;
		-webkit-overflow-scrolling: touch;
		overflow-y: scroll;

		iframe {
			padding-bottom: 55px;
			width: 100%;
			height: 100%;
		}
	}
</style>
