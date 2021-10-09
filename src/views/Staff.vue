<template>
<v-app id="inspire" style="postion: fiexd">
  <v-app-bar app color="primary" dark fixed>
    <v-toolbar-title class="mx-4">スケジュール</v-toolbar-title>
    <v-toolbar-title class="mx-3" v-if="$refs.calendar">
      {{ $refs.calendar.title }}
    </v-toolbar-title>
    <v-toolbar-title class="mx-3" v-else>
      {{ calendar_date }}
    </v-toolbar-title>
  </v-app-bar>
  <v-main class="mx-1" style="position: fixed !important; width: 100%; height: 100%;">
    <alert
      @close="alert_close"
      :type="alert_type"
      :text="alert_text"
      v-if="alert_show"
    ></alert>

    <v-container fluid class="fill-height pa-0">
      <v-row class="fill-height">
        <v-col>
          <v-toolbar class="pa-0" flat>
            <v-btn outlined class="mx-2" color="grey darken-2" @click="setToday">今日</v-btn>
            <v-btn fab text class="mx-1 pl-2" color="grey darken-2" @click="prevDate"><v-icon>arrow_back_ios</v-icon></v-btn>
            <v-btn fab text class="mx-1 pl-2" color="grey darken-2" @click="nextDate"><v-icon>arrow_forward_ios</v-icon></v-btn>
            <v-spacer></v-spacer>
            <v-col cols="2">
              <v-text-field class="mt-6" v-model="name" label="名前" id="id"></v-text-field>
            </v-col>
            <v-col cols="2">
              <v-text-field class="mt-6" v-model="password" label="パスワード" id="password"></v-text-field>
            </v-col>
            <v-btn class="accent mx-2" color="white" @click="search">
              <v-icon>search</v-icon>検索
            </v-btn>
            <v-btn class="info mx-2" color="white" @click="dialog = true" :disabled="name == '' || password == '' || calendar_events == ''">
              <v-icon>save</v-icon>登録
            </v-btn>
            <v-btn class="success mx-2" color="white" @click="clear">
              <v-icon>refresh</v-icon>クリア
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
.v-event {
  width: 99% !important;
  left: 0.5% !important;
  height: 60% !important;
  top: 1% !important;
}
</style>
<script>
import alert from '@/components/alert.vue'
import StaffEdit from '@/components/StaffEdit.vue'
import StaffDialog from '@/components/StaffDialog.vue';
import axios from "axios"

export default {
  name: "staff",
  components: {
    StaffEdit,
    StaffDialog,
    alert,
  },
  props: {
  },
  data: () => ({
    colors: ['grey darken-2','orange'],
    name: '',
    password: '',
    calendar: '',
    calendar_date: '',
    calendar_type: 'month',
    calendar_events: [],
    edit_show: false,
    edit_items: {},
    edit_index: null,
    alert_show: false,
    alert_type: '',
    alert_text: '',
    access_time: '',
    dialog: false,
  }),
  created() {
    var date = new Date();
    this.calendar_date = date.getMonth()+1+"月 "+date.getFullYear();
    this.setToday();
    document.addEventListener('keydown', (event) => {
      this.keyBoardEvent(event)
    }, false);
  },
  computed: {
  },
  methods: {
    keyBoardEvent(event) {
      if ( event.which != 13) {
        return;
      }
      this.search();
    },
    select({ date }) {
      let index = this.calendar_events.findIndex(obj => obj.date == date);
      if (index >= 0) {
        if (this.calendar_events[index].agenda == '') {
          this.calendar_events.splice(index, 1);
          return;
        } else {
          this.edit_items = this.calendar_events[index];
          this.edit_index = index;
          this.edit_show = true;
          return;
        }
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
        password: password
      }
      axios.post(url, data).then(function(response) {
        if (response.data.status === 'success') {
          this.access_time = response.data.access_time;
          this.fetch_data(response.data.data);
        } else {
          this.calendar_events = [];
          this.alert(response.data.status, response.data.message, true);
        }
      }.bind(this))
    },
    fetch_data(data) {
      var firstTimestamp = null
      var startTime = null
      var color = ''
      this.calendar_events = [];
      data.map(obj => {
        color = obj.agenda == '' ? this.colors[0] : this.colors[1];
        firstTimestamp = new Date(`${obj.date}T09:00:00`)
        startTime = new Date(firstTimestamp)
        this.calendar_events.push({
          name: obj.name,
          date: obj.date,
          agenda: obj.agenda,
          start_time: obj.start_time,
          end_time: obj.end_time,
          total_time: obj.total_time,
          staff_hour_salary: obj.staff_hour_salary,
          staff_day_salary: obj.staff_day_salary,
          staff_expense: obj.staff_expense,
          start: startTime,
          color: color
        })
      })
    },
    clear() {
      this.access_time = '';
      this.calendar_events = [];
      this.setToday();
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
    upload() {
      const url = "/schedule/app/staffUploadSchedule.php";
      const access_time = this.access_time == '' ? 0 : this.access_time; 
      const data = {
        name: this.name,
        password: this.password,
        access_time: access_time,
        event: this.calendar_events
      }
      axios.post(url, data).then(function(response) {
        this.alert(response.data.status, response.data.message, true);
      }.bind(this))
      this.dialog = false;
    },
    close() {
      this.dialog = false;
    },
    alert(type, text, show) {
      this.alert_type = type
      this.alert_text = text;
      this.alert_show = show;
    },
    alert_close() {
      this.alert_show = false;
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