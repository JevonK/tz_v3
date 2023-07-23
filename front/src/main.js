import Vue from 'vue'
import App from './App.vue'
import router from './router'
import store from './store/'
import './registerServiceWorker'
import '@/assets/styles/reset.scss'
import common from '@/assets/js/common.js'
import { Notify, Dialog } from 'vant'
import vueTouch from "vue-plugin-touch"
import touch from 'vue-directive-touch'
import VueI18n from 'vue-i18n'
import 'lib-flexible'
import 'vant/lib/index.css';
Vue.use(Notify).use(Dialog).use(vueTouch).use(touch).use(VueI18n);
Vue.prototype.common=common;

const i18n = new VueI18n({
    locale: localStorage.getItem("lang") || "en_us", // 将要切换的语言，可以通过url拼的参数获取，用户行为select选择获取，本地manifest配置获取等，根据场景动态获取
    messages: {
      'zh_cn': require('./assets/languages/zh_CN.json'),  // 本地资源文件，我这里配2个语言，中文&英文，src下根据个人情况放置
      'zh_hk': require('./assets/languages/zh_HK.json'), 
	  'en_us': require('./assets/languages/en_US.json'),
	  'th_th': require('./assets/languages/th_TH.json'),
	  'vi_vn': require('./assets/languages/vi_VN.json'),
	  'ja_jp': require('./assets/languages/ja_JP.json'),
	  'ko_kr': require('./assets/languages/ko_KR.json'),
	  'ms_my': require('./assets/languages/ms_MY.json'),
	  'pt_pt': require('./assets/languages/pt_PT.json'),
	  'tr_tr': require('./assets/languages/tr_TR.json'),
	  'es_es': require('./assets/languages/es_ES.json'),
	  'de_de': require('./assets/languages/de_DE.json'),
	  'fr_fr': require('./assets/languages/fr_FR.json'),
	  'id_id': require('./assets/languages/id_ID.json')
    }
});
Vue.config.productionTip = false;

window.vm = new Vue({
  router,
  i18n,
  store,
  render: h => h(App)
}).$mount('#app');
