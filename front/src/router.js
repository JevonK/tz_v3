import Vue from "vue";
import Router from "vue-router";

Vue.use(Router);

export default new Router({
    //   mode: "history",
    mode: 'hash',
    base: process.env.BASE_URL,
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition;
        } else {
            return {x: 0, y: 0};
        }
    },
    routes: [
		{
		    path: "/",
		    redirect: {
		        name: "index"
		    }
		},
		{
		    path: "/login",
		    name: "login",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/user/login.vue")
		},
		{
		    path: "/register",
		    name: "register",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/user/register.vue")
		},
        {
            path: "/user",
            name: "user",
            component: () =>
                import(/* webpackChunkName: "home" */ "./views/user/index.vue")
        },
        {
            path: "/wallet_home",
            name: "wallet_home",
            component: () =>
                import(/* webpackChunkName: "home" */ "./views/wallet/index.vue")
        },
		{
		    path: "/service",
		    name: "service",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/user/service.vue")
		},
		{
		    path: "/edit_password",
		    name: "edit_password",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/user/edit_password.vue")
		},
		{
		    path: "/service/:code",
		    name: "service_detail",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/user/service_online.vue")
		},
		{
		    path: "/auth",
		    name: "auth",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/user/auth.vue")
		},
		{
		    path: "/authPhone",
		    name: "authPhone",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/user/auth_phone.vue")
		},
		{
		    path: "/authEmail",
		    name: "authEmail",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/user/auth_email.vue")
		},
		{
		    path: "/authGoogle",
		    name: "authGoogle",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/user/auth_google.vue")
		},
		{
		    path: "/language",
		    name: "language",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/user/language.vue")
		},
		{
		    path: "/wallet",
		    name: "wallet",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/user/wallet.vue")
		},
		{
		    path: "/wallet/bank/:code",
		    name: "wallet_bank",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/user/wallet_bank.vue")
		},
		{
		    path: "/wallet/qrcode/:code",
		    name: "wallet_qrcode",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/user/wallet_qrcode.vue")
		},
		{
		    path: "/withdraw",
		    name: "withdraw",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/user/withdraw.vue")
		},
		{
		    path: "/withdraw/record",
		    name: "withdraw_record",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/user/withdraw_record.vue")
		},
		{
		    path: "/red_envelope",
		    name: "red_envelope",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/user/red_envelope.vue")
		},
		{
		    path: "/address",
		    name: "address",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/user/address.vue")
		},
		{
		    path: "/recharge",
		    name: "recharge",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/user/recharge.vue")
		},
		{
		    path: "/recharge/bank",
		    name: "recharge_bank",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/user/recharge_bank.vue")
		},
		{
		    path: "/recharge/qrcode",
		    name: "recharge_qrcode",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/user/recharge_qrcode.vue")
		},
		{
		    path: "/recharge/record",
		    name: "recharge_record",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/user/recharge_record.vue")
		},
		{
		    path: "/recharge/method",
		    name: "recharge_method",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/user/recharge_method.vue")
		},
		{
		    path: "/team",
		    name: "team",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/team/index.vue")
		},
		{
		    path: "/funding/record",
		    name: "funding_record",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/user/funding_record.vue")
		},
		{
		    path: "/notice",
		    name: "notice",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/user/notice.vue")
		},
		{
		    path: "/vip",
		    name: "vip",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/user/vip.vue")
		},
		{
		    path: "/rewards",
		    name: "rewards",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/user/rewards.vue")
		},
		{
		    path: "/index",
		    name: "index",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/index/index.vue")
		},
		{
		    path: "/invest",
		    name: "invest",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/invest/index.vue")
		},
		{
		    path: "/invest/detail/:code",
		    name: "investDetail",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/invest/detail.vue")
		},
		{
		    path: "/invest/record",
		    name: "investRecord",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/invest/record.vue")
		},
		{
		    path: "/questions",
		    name: "questions",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/user/questions.vue")
		},
		{
		    path: "/currency",
		    name: "currency",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/user/currency.vue")
		},
		{
		    path: "/draw",
		    name: "draw",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/user/draw.vue")
		},
		{
		    path: "/draw/record",
		    name: "drawRecord",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/user/draw_record.vue")
		},
        {
            path: "/savings",
            name: "savings",
            component: () =>
                import(/* webpackChunkName: "home" */ "./views/user/savings.vue")
        },
		{
		    path: "/savings/redeem",
		    name: "redeem",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/user/savings_redeem.vue")
		},
		{
		    path: "/savings/subscribe/:code",
		    name: "subscribe",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/user/savings_subscribe.vue")
		},
		{
		    path: "/savings/redeem_record",
		    name: "redeem_record",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/user/savings_redeem_record.vue")
		},
		{
		    path: "/savings/subscribe_record",
		    name: "subscribe_record",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/user/savings_subscribe_record.vue")
		},
		{
		    path: "/goods",
		    name: "goods",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/user/goods.vue")
		},
		{
		    path: "/goods/detail/:code",
		    name: "goodsDetail",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/user/goods_detail.vue")
		},
		{
		    path: "/goods/record",
		    name: "goodsRecord",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/user/goods_record.vue")
		},
		
        {
            path: "/activity",
            name: "activity",
            component: () =>
                import(/* webpackChunkName: "home" */ "./views/user/activity.vue")
        },
        {
            path: "/activity/:code",
            name: "activityDetail",
            component: () =>
                import(/* webpackChunkName: "home" */ "./views/user/activity_detail.vue")
        },
		{
		    path: "/article/:code",
		    name: "article_detail",
		    component: () =>
		        import(/* webpackChunkName: "home" */ "./views/user/article_detail.vue")
		},
    ]
});
