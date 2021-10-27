import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

export default new Vuex.Store({
  state: {
    calendar_events: [],
    staff_name: '',
  },
  getters: {
    calendar_events (state) {
      return state.calendar_events
    },
    staff_name (state) {
      return state.staff_name
    },
  },
  mutations: {
    set_calendar_events(state, payload) {
      state.calendar_events = payload
    },
    set_staff_name(state, payload) {
      state.staff_name = payload
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
