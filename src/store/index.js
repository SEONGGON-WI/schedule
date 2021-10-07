import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

export default new Vuex.Store({
  state: {
    calendar_events: [],

  },
  getters: {
    calendar_events (state) {
      return state.calendar_events
    },

  },
  mutations: {
    set_calendar_events(state, payload) {
      state.calendar_events = payload
    },

  },
  actions: {
    setCalendarEvents({commit}, payload) {
      commit('set_calendar_events', payload);
    },

  },
  modules: {
  }
})
