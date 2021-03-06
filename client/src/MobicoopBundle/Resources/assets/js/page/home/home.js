'use strict'

import { Vue, vuetify, i18n } from '@js/config/home/home'

import HomeSearch from '@components/home/HomeSearch'
import MHeader from '@components/base/MHeader'
import MFooter from '@components/base/MFooter'

new Vue({
  i18n,
  el: '#app',
  vuetify,
  components: {
    HomeSearch,
    MHeader,
    MFooter
  }
})