<template>
    <div class="basic_wrap">
		<div class="red_top_bg">
		    <div class="big_tit">{{$t('home.activity')}}</div>
		</div>
		<div class="item_wrap">
			<div v-for="i in data.list" :key="i.id" class="item" @click="jump(i)">
				<img :src="i['img_'+lang]">
				<p>{{i['title_'+lang]}}</p>
				<p>{{i.time}}</p>
			</div>
		</div>
    </div>
</template>

<script>
    import Fetch from '../../utils/fetch';
	import Api from "../../interface/index";
	
    export default {
        name: "activity",
        data() {
            return {
				lang:this.$i18n.locale||"zh_cn",
                data: {},
            }
        },
        created() {
            this.$parent.footer('cart');
        },
        mounted() {

            this.start();
        },
        methods: {
            start() {
                Fetch('/index/activity_list').then(r => {
					this.data = r.data;
				})
            },
			jump(activity){
				if(activity.url == ""){
					this.$router.push("/activity/"+activity.id);
				}else{
					this.$router.push(activity.url);
				}
			}
        },
    }
</script>

<style lang="less" scoped>
	.red_top_bg {
	    position: fixed;
	    top: 0;
	    z-index: 10;
		background-color: #FFFFFF;
	}
	.basic_wrap {
	    position: relative;
	}
	.big_tit{
		color: #000000;
	}
	.item_wrap{
		width: 100%;
		padding: 44px 0 0 0;
		.item{
			width: 96%;
			background: #FFFFFF;
			margin: 10px 0 10px 2%;
			border-radius: 5px;
			img{
				width: 100%;
				border-radius: 5px 5px 0 0;
			}
			p:nth-child(2){
				padding: 10px 0 10px 10px;
				font-weight: bold;
			}
			p:nth-child(3){
				padding: 0px 0 10px 10px;
				color: #999;
			}
		}
	}
</style>
