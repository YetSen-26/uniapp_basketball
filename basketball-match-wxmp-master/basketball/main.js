import App from './App'

// #ifndef VUE3
import Vue from 'vue'
Vue.config.productionTip = false
App.mpType = 'app'
const app = new Vue({
    ...App
})
app.$mount()
// #endif

// #ifdef VUE3
import { createSSRApp } from 'vue'
export function createApp() {
  const app = createSSRApp(App)
  return {
    app
  }
}
// #endif

//引入外部js

import * as Util from './common/js/util.js'
import * as Api from './common/js/api.js'
import * as Storage from './common/js/storage.js'

Vue.prototype.$util = Util;
Vue.prototype.$api = Api;
Vue.prototype.$storage = Storage;