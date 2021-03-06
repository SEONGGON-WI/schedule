import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

export default new Vuex.Store({
  state: {
    fetch_calendar_events: [],
    fetch_analytic_events: [],
    calendar_events: [],
    client_agenda: [],
    staff_name: '',
    root_folder: '',
  },
  getters: {
    fetch_calendar_events (state) {
      return state.fetch_calendar_events
    },
    fetch_analytic_events (state) {
      return state.fetch_analytic_events
    },
    calendar_events (state) {
      return state.calendar_events
    },
    client_agenda (state) {
      return state.client_agenda
    },
    staff_name (state) {
      return state.staff_name
    },
    root_folder (state) {
      return state.root_folder
    },
  },
  mutations: {
    set_fetch_calendar_events(state, payload) {
      state.fetch_calendar_events = payload
    },
    set_fetch_analytic_events(state, payload) {
      state.fetch_analytic_events = payload
    },
    set_calendar_events(state, payload) {
      state.calendar_events = payload
    },
    set_client_agenda(state, payload) {
      state.client_agenda = payload
    },
    set_staff_name(state, payload) {
      state.staff_name = payload
    },
    set_root_folder(state, payload) {
      state.root_folder = payload
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
