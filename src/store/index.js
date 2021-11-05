import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

export default new Vuex.Store({
  state: {
    calendar_events: [],
    client_agenda: [],
    staff_name: '',
  },
  getters: {
    calendar_events (state) {
      return state.calendar_events
    },
    client_agenda (state) {
      return state.client_agenda
    },
    staff_name (state) {
      return state.staff_name
    },
  },
  mutations: {
    set_calendar_events(state, payload) {
      state.calendar_events = payload
    },
    set_client_agenda(state, payload) {
      state.client_agenda = payload
    },
    set_staff_name(state, payload) {
      state.staff_name = payload
    },
  },
  actions: {
    setCalendarEvents({commit}, payload) {
      commit('set_calendar_events', payload);
    },
    setClientAgenda({commit}, payload) {
      commit('set_client_agenda', payload);
    },
  },
  modules: {
  }
})
