<template>
<v-app id="inspire" style="postion: fiexd">
  <v-app-bar app color="primary" dark fixed>
    <v-toolbar-title class="mx-4">スケジュール</v-toolbar-title>
    <v-toolbar-title class="mx-3">
      {{ calendar_date }}
    </v-toolbar-title>
    <v-spacer></v-spacer>
    <v-btn class="mx-2" color="yellow darken-4" @click="analytics()" :disabled="name == '' || search_condition == false">
      <v-icon>analytics</v-icon>集計
    </v-btn>
  </v-app-bar>
  <v-main class="mx-1" style="position: fixed !important; width: 100%; height: 100%;">
    <alert
      @close="alert_show = false"
      :text="alert_text"
      v-if="alert_show"
    ></alert>

    <v-container fluid class="fill-height pa-0">
      <v-row class="fill-height">
        <v-col>
          <v-toolbar class="pa-0" flat>
            <v-btn outlined class="mx-2" color="grey darken-2" @click="setToday">今日</v-btn>
            <v-btn icon text class="mx-1 pl-2" color="grey darken-2" @click="prevDate"><v-icon>arrow_back_ios</v-icon></v-btn>
            <v-btn icon text class="mx-1 pl-2" color="grey darken-2" @click="nextDate"><v-icon>arrow_forward_ios</v-icon></v-btn>
            <v-spacer></v-spacer>
            <v-form ref="form" @submit.prevent="search" autocomplete="on">
              <v-row class="mt-1" no-gutters>
                  <input
                    class="form_area pa-2 mx-2 mb-4"
                    lang="en"
                    ref="name"
                    v-model="name"
                    id="name"
                    name="name"
                    @keydown.enter="id_enter"
                    placeholder="名前"
                  >
                  <input
                    class="form_area pa-2 mx-2 mb-4"
                    lang="en"
                    ref="password"
                    v-model="password"
                    id="password"
                    name="password"
                    @keydown.enter="password_enter"
                    placeholder="パスワード"
                  >
                  <v-btn 
                    class="accent mx-2 mt-3"
                    type="submit"
                    color="white"
                    :disabled="name == '' || password == ''"
                  ><v-icon>search</v-icon>検索
                  </v-btn>
              </v-row>
            </v-form>
            <v-btn class="info mx-2" color="white" @click="dialog = true" :disabled="name == '' || password == '' || calendar_events == ''">
              <v-icon>save</v-icon>登録
            </v-btn>
          </v-toolbar>

          <v-sheet height="90%" class="mx-3">
            <v-calendar
              ref="calendar"
              v-model="calendar"
              :type="calendar_type"
              :events="calendar_events"
              event-color="grey darken-1"
              locale="ja-jp"
              @click:day="select"
              @change="fetch"
            >
            <template v-slot:event="{ event }">
              <div class="event_message mt-2 ml-4">
                {{ event.agenda }}
              </div>
            </template>
          </v-calendar>
          </v-sheet>
        </v-col>

        <staff-edit
          @edit="edit($event)"
          @close="edit_close"
          v-if="edit_show"
          :items="edit_items"
        ></staff-edit>

        <staff-analytics
          @close="close_analytics"
          v-if="analytics_show"
          :items="analytics_items"
          :date="calendar_date"
        ></staff-analytics>

        <staff-dialog
          @upload="upload"
          @close="close"
          v-if="dialog"
        ></staff-dialog>
      </v-row>
    </v-container>
</v-main>
</v-app>
</template>
<style lang="scss">
.v-data-table > .v-data-table__wrapper > table > thead > tr > th {
  font-size: 25px !important;
}
.v-input.v-input--selection-controls .v-input__control .v-input__slot label{
  font-size: 30px !important;
}
.v-input__control .v-input__slot .v-text-field__slot input {
  font-size: 25px !important;
}
.v-input__control .v-input__slot .v-text-field__slot label {
  font-size: 20px !important;
}
.v-event {
  width: 94% !important;
  left: 3% !important;
  height: 40% !important;
  top: 1% !important;
}
@media screen and ( max-width: 1000px ) {
  .v-event {
    width: 94% !important;
    left: 3% !important;
    height: 40% !important;
    top: 1% !important;
  }
}
</style>
<script>
import alert from '@/components/alert.vue'
import StaffEdit from '@/components/StaffEdit.vue'
import StaffDialog from '@/components/StaffDialog.vue';
import StaffAnalytics from '@/components/StaffAnalytics.vue'
import axios from "axios"

export default {
  name: "staff",
  components: {
    StaffEdit,
    StaffDialog,
    StaffAnalytics,
    alert,
  },
  props: {
  },
  data: () => ({
    colors: ['grey darken-2','orange','teal accent-4'],
    name: '',
    password: '',
    search_date: {},
    calendar: '',
    calendar_date: '',
    calendar_type: 'month',
    calendar_events: [],
    edit_show: false,
    edit_items: {},
    edit_index: null,
    analytics_items: [],
    analytics_show: false,
    alert_show: false,
    alert_type: '',
    alert_text: '',
    search_condition: false,
    today: '',
    dialog: false,
  }),
  created() {
    const date = new Date();
    const year = date.getFullYear();
    const month = ("0" + (1 + date.getMonth())).slice(-2);
    const day = ("0" + date.getDate()).slice(-2);
    this.today = year + "-" + month + "-" + day;
    this.calendar_date = year + "年 " + month + "月";
    this.setToday();
    // document.addEventListener('keydown', (event) => {
    //   this.keyBoardEvent(event)
    // }, false);
  },
  computed: {
  },
  methods: {
    async fetch() {
      this.search_date = {
        start_date: this.$refs.calendar.lastStart.date,
        end_date: this.$refs.calendar.lastEnd.date
      }
      this.search_condition = false
      this.calendar_events = [];
      this.calendar_date = this.$refs.calendar.lastStart.year + "年 " + this.$refs.calendar.lastStart.month + "月"
      if (this.name != '' || this.password != '') {
        this.search();
      }
    },
    id_enter(event) {
      event.preventDefault()
      this.$refs.password.focus()
    },
    password_enter(event) {
      event.preventDefault()
      this.search()
    },
    // keyBoardEvent(event) {
    //   if ( event.which != 13) {
    //     return;
    //   }
    //   this.search();
    // },
    select({ date }) {
      let index = this.calendar_events.findIndex(obj => obj.date == date);
      if (index >= 0) {
        if (this.calendar_events[index].agenda == '') {
          this.calendar_events.splice(index, 1);
          return;
        } else {
          const lodash = require("lodash");
          this.edit_items = lodash.cloneDeep(this.calendar_events[index]);
          this.edit_index = index;
          this.edit_show = true;
          return;
        }
      }
      if (this.name == '' || this.password == '') {
        return;
      }

      const firstTimestamp = new Date(`${date}T09:00:00`)
      const startTime = new Date(firstTimestamp)
      this.calendar_events.push({
        date: date,
        agenda: '',
        start_time: '',
        end_time: '',
        total_time: '',
        staff_hour_salary: '',
        staff_day_salary: '',
        staff_expense: '',
        start: startTime,
        color: this.colors[0],
      });    
    },
    search() {
      let name = this.name;
      name = name.replace(/^\s+|\s+$/gm,'')
      let password = this.password;
      password = password.replace(/^\s+|\s+$/gm,'')
      const url = "/schedule/app/staffSearchSchedule.php";
      const data = {
        name: name,
        password: password,
        start_date: this.search_date.start_date,
        end_date: this.search_date.end_date
      }
      axios.post(url, data).then(function(response) {
        if (response.data.status == true && response.data.data != '') {
          this.search_condition = true
          this.$store.commit('set_staff_name', name)  
          this.fetch_data(response.data.data);
        } else {
          this.search_condition = false
          this.$store.commit('set_staff_name', '')  
          this.calendar_events = [];
          if (response.data.status == false) {
            this.alert(response.data.message);
          }
        }
      }.bind(this))
    },
    fetch_data(data) {
      var firstTimestamp = null
      var startTime = null
      this.calendar_events = [];
      data.map(obj => {
        firstTimestamp = new Date(`${obj.date}T09:00:00`)
        startTime = new Date(firstTimestamp)
        if ((obj.date < this.today) && (obj.staff_day_salary != '')) {
          obj.color = this.colors[2]
        } else if (obj.agenda != '') {
          obj.color = this.colors[1]
        } else {
          obj.color = this.colors[0]
        }
        obj.start = startTime
      })
      this.calendar_events = data;
    },
    analytics() {
      const lodash = require("lodash");
      const data = lodash.cloneDeep(this.calendar_events);
      this.analytics_items = data.filter(obj => obj.name == this.name && obj.agenda != '')
      this.analytics_show = true
    },
    close_analytics() {
      this.analytics_items = []
      this.analytics_show = false
    },
    edit(event) {
      const index = this.edit_index;
      this.calendar_events[index].start_time = event.start_time;
      this.calendar_events[index].end_time = event.end_time;
      this.calendar_events[index].total_time = event.total_time;
      this.calendar_events[index].staff_hour_salary = event.staff_hour_salary;
      this.calendar_events[index].staff_day_salary = event.staff_day_salary;
      this.calendar_events[index].staff_expense = event.staff_expense;
      this.edit_show = false;
    }, 
    edit_close() {
      this.edit_show = false;
    },
    async upload() {
      const url = "/schedule/app/staffUploadSchedule.php";
      const data = {
        name: this.name,
        password: this.password,
        search_condition: this.search_condition,
        event: this.calendar_events,
        start_date: this.search_date.start_date,
        end_date: this.search_date.end_date
      }
      axios.post(url, data).then(function(response) {
        this.alert(response.data.message);
      }.bind(this))
      this.dialog = false;
    },
    close() {
      this.dialog = false;
    },
    alert(text) {
      this.alert_text = text;
      this.alert_show = true
    },
    setToday() {
      this.calendar = ''
    },
    prevDate() {
      this.$refs.calendar.prev()
    },
    nextDate() {
      this.$refs.calendar.next()
    },
  },
}
</script>