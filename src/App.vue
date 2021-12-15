<template>
  <div id="app">
    <!-- <div id="nav"> -->
      <!-- <router-link to="/">Home</router-link> |
      <router-link to="/about">About</router-link> -->
    <!-- </div> -->
    <router-view></router-view>
  </div>
</template>
<style>
tbody tr td {
  padding-top: 0px !important;
  padding-bottom: 0px !important;
  padding-left: 8px !important;
  padding-right: 8px !important;
}

.confirm {
  font-size: 50px !important;
}

.confirm_action {
  font-size: 40px !important;
}

.form_area {
  width: 200px !important;
  line-height: 1 !important;
  border: 0;
  outline: 0;
  background: transparent;
  border-bottom: 1px solid grey;
  border-radius: 0;
  -webkit-appearance: none;
  -webkit-border-radius:0px;
}

.name_agenda {
  color: black !important;
  font-size: 30px !important;
}

.empty_salary {
  background-color: #FFEA00;
}

.filled_salary {
  background-color: white;
}

.paid_schedule {
  background-color: #81C784;
}

.v-input__control .v-text-field__details .v-messages .v-messages__wrapper .v-messages__message {
  font-size: 18px !important;
  margin-top: 5px !important;
}

.custom_dialog {
  max-height: 80% !important;
  width: 95% !important;
  position: fixed !important;
  top: 10% !important;
  display: flex !important;
  flex-direction: column !important;
  align-items: center !important;
  align-content: normal !important;
  justify-content: normal !important;
}

@media screen and ( max-width: 1000px ) {
  .custom_dialog {
    max-height: 80% !important;
    position: fixed !important;
    width: 90% !important;
    top: 10% !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    align-content: normal !important;
    justify-content: normal !important;
  }
}

.v-select__selections {
  line-height: 35px !important;
  font-size: 25px !important;
}

.text-center .v-input__control .v-input__slot {
  padding-left: 4px !important;
  padding-right: 0px !important;
}

.v-input--is-disabled {
  color: black !important;
}

.v-calendar-weekly__day:hover{
  cursor:pointer !important;
}

.v-calendar-weekly__day:hover{
  cursor:pointer !important;
}

.v-calendar-weekly__day-label .v-btn__content{
  font-size: 15px !important;
}

.v-calendar-daily_head-weekday{
  background-color: #1976D2 !important;
  color: white !important;
  font-size: 20px !important;
}

.v-calendar-weekly__head-weekday{
  background-color: #1976D2 !important;
  color: white !important;
  font-size: 20px !important;
}

.v-present .v-calendar-weekly__day-label .v-btn--round .v-btn__content{
  color: white !important;
}

.v-present .v-calendar-weekly__day-label .v-btn--round{
  width: 100% !important;
  border-radius: 0% !important;
  background-color: #E53935 !important;
  height: 30px !important;
}

.v-present .v-calendar-weekly__day-label {
  margin-top: 0px !important;
  height: 40px !important;
}

.v-calendar-weekly__day.v-past .v-calendar-weekly__day-label .v-btn--round{
  width: 100% !important;
  border-radius: 4px !important;
  height: 30px !important;
}

.v-calendar-weekly__day.v-past .v-calendar-weekly__day-label {
  margin-top: 0px !important;
  height: 40px !important;
}

.v-calendar-weekly__day.v-future .v-calendar-weekly__day-label .v-btn--round{
  width: 100% !important;
  border-radius: 4px !important;
  height: 30px !important;
}

.v-calendar-weekly__day.v-future .v-calendar-weekly__day-label {
  margin-top: 0px !important;
  height: 40px !important;
}

.v-present .v-calendar-daily_head-day-label .v-btn--round .v-btn__content{
  color: white !important;
}

.v-present .v-calendar-daily_head-day-label .v-btn--round{
  width: 100% !important;
  border-radius: 0% !important;
  background-color: #E53935 !important;
  height: 30px !important;
}

.v-present .v-calendar-daily_head-day-label {
  margin-top: 0px !important;
  height: 40px !important;
}

.v-calendar-daily_head-day.v-past .v-calendar-daily_head-day-label .v-btn--round{
  width: 100% !important;
  border-radius: 4px !important;
  height: 30px !important;
}

.v-calendar-daily_head-day.v-past .v-calendar-daily_head-day-label {
  margin-top: 0px !important;
  height: 40px !important;
}

.v-calendar-daily_head-day.v-future .v-calendar-daily_head-day-label .v-btn--round{
  width: 100% !important;
  border-radius: 4px !important;
  height: 30px !important;
}

.v-calendar-daily_head-day.v-future .v-calendar-daily_head-day-label {
  margin-top: 0px !important;
  height: 40px !important;
}
</style>
<script>
export default {
  name: 'App',
  components: {
    
  },
  created() {
    this.disableScroll
    this.removeEvent
  },
  data: () => ({
  }),
  methods: {
    disableScroll() {
      document.querySelector('body').addEventListener('touchmove', this.removeEvent, { passive: false });
      document.querySelector('body').addEventListener('onclick', this.removeEvent, { passive: false });
      document.querySelector('body').addEventListener('mousewheel', this.removeEvent, { passive: false });
    },
    removeEvent(e) {
      e.preventDefault();
      e.stopPropagation();
    }
  }
}
</script>
