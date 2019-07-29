'use strict';

// any CSS you require will output into a single css file (app.css in this case)
// import 'babel-polyfill';
// import Vue from 'vue';

import { Vue, vuetify, i18n } from '../config/vue-config'

// Vue components
import Homesearchform from '../components/Homesearchform';
import Vueheader from '../components/Vueheader';
import Vuefooter from '../components/Vuefooter';

import '../../css/page/home.scss';

new Vue({
  i18n,
  el: '#app',
  vuetify,
  components: {
    Homesearchform,
    Vueheader,
    Vuefooter
  }
})