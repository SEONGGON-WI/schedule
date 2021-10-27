import Vue from 'vue';
import Vuetify from 'vuetify/lib/framework';
import 'material-icons/iconfont/material-icons.css'

Vue.use(Vuetify);

const opts = {
  icons: {
    iconfont: 'md', // default - only for display purposes
  },
}

export default new Vuetify(opts);