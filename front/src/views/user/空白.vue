<template>
	<div class="basic_wrap">
		<bsHeader title="" @backurl="$router.back()"></bsHeader>
	</div>
</template>

<script>
	import Vue from 'vue';
	import bsHeader from '../../components/bsHeader.vue'
	import Fetch from '../../utils/fetch'
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
			this.start();
		},
		methods: {
			start() {
				Fetch('').then(r => {})
			}
		}
	};
</script>

<style lang="less" scoped>
</style>
