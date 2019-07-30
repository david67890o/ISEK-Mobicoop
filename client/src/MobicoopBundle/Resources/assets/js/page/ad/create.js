'use strict';

// any CSS you require will output into a single css file (app.css in this case)
import { Vue, vuetify, i18n, VApp } from '../../config/vue-config'
import '../../../css/page/ad/create.scss';

// Vue components
import Adcreateform from '../../components/Adcreateform';
import Vueheader from '../../components/Vueheader';
import Vuefooter from '../../components/Vuefooter';

new Vue({
  el: '#app',
  vuetify,
  i18n,
  components: {
    VApp,
    Adcreateform,
    Vueheader,
    Vuefooter
  }
})