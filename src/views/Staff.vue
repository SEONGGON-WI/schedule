<template>
<v-app id="inspire" style="postion: fiexd">
  <v-app-bar app color="primary" dark fixed>
    <v-toolbar-title class="mx-4">スケジュール</v-toolbar-title>
    <v-toolbar-title class="mx-3">
      {{ calendar_date }}
    </v-toolbar-title>
    <v-spacer></v-spacer>
    <v-btn class="success mx-2" color="white" @click="dialog = true" :disabled="input.username == '' || input.password == ''">
      <v-icon>cloud_upload</v-icon>登録
    </v-btn>
    <v-btn class="mx-2" color="yellow darken-4" @click="analytics()" :disabled="input.username == '' || search_condition == false">
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
            <v-form ref="form" id="login" @submit.prevent="search">
              <v-row class="mt-1" no-gutters>
                  <input
                    type="text"
                    class="form_area pa-2 mx-2 mb-4"
                    lang="en"
                    ref="username"
                    id="username"
                    name="username"
                    v-model="input.username"
                    @keydown.enter="id_enter"
                    autocomplete="username"
                    placeholder="名前"
                  >
                  <input
                    type="password"
                    class="form_area pa-2 mx-2 mb-4"
                    lang="en"
                    ref="password"
                    id="password"
                    name="password"
                    v-model="input.password"
                    @keydown.enter="password_enter"
                    autocomplete="password"
                    placeholder="パスワード"
                  >
                    <v-btn class="mx-2" icon @click="show_password">
                      <v-icon>
                        visibility
                      </v-icon>
                    </v-btn>
                  <v-btn 
                    class="accent mx-2 mt-3"
                    type="submit"
                    color="white"
                    :disabled="input.username == '' || input.password == ''"
                  ><v-icon>search</v-icon>検索
                  </v-btn>
              </v-row>
            </v-form>
          </v-toolbar>

          <v-sheet height="90%" class="mx-3">
            <v-calendar
              ref="calendar"
              v-model="calendar"
              :type="calendar_type"
              :events="calendar_events"
              event-color="grey darken-1"
              locale="ja-jp"
              :weekdays="calendar_format"
              @click:day="select"
              @change="fetch"
            >
            <template v-slot:event="{ event }">
              <div class="event_message mt-3 ml-1">
                {{ event.agenda }}
              </div>
            </template>
          </v-calendar>
          </v-sheet>
        </v-col>

        <staff-edit
          @edit="edit($event)"
          @close="edit_show = false"
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
.v-text-field--filled .v-input__control .v-input__slot fieldset{
  background-color: #BDBDBD !important;
}
.v-event {
  font-size: 30px !important;
  width: 98% !important;
  left: 1% !important;
  height: 40% !important;
  top: 1% !important;
}
@media screen and ( max-width: 1000px ) {
  .v-event {
    font-size: 30px !important;
    width: 98% !important;
    left: 1% !important;
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
    input: {
      username: '',
      password: ''
    },
    search_date: {
      start_date: '',
      end_date: ''
    },
    calendar: '',
    calendar_date: '',
    calendar_type: 'month',
    calendar_events: [],
    calendar_format : [1,2,3,4,5,6,0],
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
    root_folder: '/schedule',
  }),
  created() {
    this.$store.commit('set_root_folder', this.root_folder)
    const date = new Date();
    const year = date.getFullYear();
    const month = ("0" + (1 + date.getMonth())).slice(-2);
    const day = ("0" + date.getDate()).slice(-2);
    this.today = year + "-" + month + "-" + day;
    this.calendar_date = year + "年 " + month + "月";
    this.setToday();

    if (this.getCookie("username")) {
      this.input.username = this.getCookie("username")
    }
    if (this.getCookie("password")) {
      this.input.password = this.getCookie("password")
    }
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
      if (this.input.username != '' && this.input.password != '') {
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
    select({ date }) {
      let index = this.calendar_events.findIndex(obj => obj.date == date);
      if (index >= 0) {
        if (this.calendar_events[index].agenda == '') {
          this.calendar_events.splice(index, 1);
          return;
        } else {
          this.edit_items = JSON.parse(JSON.stringify(this.calendar_events[index]))
          this.edit_index = index;
          this.edit_show = true;
          return;
        }
      }
      if (this.id == '' || this.password == '') {
        return;
      }

      const firstTimestamp = new Date(`${date}T09:00:00`)
      const startTime = new Date(firstTimestamp)
      this.calendar_events.push({
        date: date,
        agenda: '',
        start: startTime,
        color: this.colors[0],
      });    
    },
    search() {
      const url = this.root_folder + "/app/staffSearchSchedule.php";
      const data = {
        name: this.input.username.replace(/^\s+|\s+$/gm,''),
        password: this.input.password.replace(/^\s+|\s+$/gm,''),
        start_date: this.search_date.start_date,
        end_date: this.search_date.end_date
      }
      axios.post(url, data).then(function(response) {
        if (response.data.status == true && response.data.data != '') {
          this.search_condition = true
          this.$store.commit('set_staff_name', this.input.username.replace(/^\s+|\s+$/gm,''))  
          this.fetch_data(response.data.data);
          this.setCookie('username', this.input.username, 28)
          this.setCookie('password', this.input.password, 28)
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
        if ((obj.agenda != '') && (obj.staff_day_salary != '')) {
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
      const data = JSON.parse(JSON.stringify(this.calendar_events))
      this.analytics_items = data.filter(obj => obj.agenda != '')
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
      this.calendar_events[index].admin_total_time = event.admin_total_time;
      this.calendar_events[index].staff_hour_salary = event.staff_hour_salary;
      this.calendar_events[index].staff_day_salary = event.staff_day_salary;
      this.calendar_events[index].staff_expense = event.staff_expense;

      if (this.input.username != '' && this.input.password != '') {
        const url = this.root_folder + "/app/staffEditSchedule.php";
        const data = {
          name: this.input.username.replace(/^\s+|\s+$/gm,''),
          password: this.input.password.replace(/^\s+|\s+$/gm,''),
          date: event.date,
          start_time: event.start_time,
          end_time: event.end_time,
          total_time: event.total_time,
          admin_total_time: event.admin_total_time,
          staff_hour_salary: event.staff_hour_salary,
          staff_day_salary: event.staff_day_salary,
          staff_expense: event.staff_expense
        }
        axios.post(url, data).then(function(response) {
          if (response.data.status == true) {
            this.fetch_data(this.calendar_events)
          }
          this.alert(response.data.message);
        }.bind(this))
      } else {
        this.alert("名前、パスワードを入力してください。")
      }
      this.edit_show = false;
    }, 
    async upload() {
      const event = this.calendar_events.filter(e => e.agenda == '')
      const url = this.root_folder + "/app/staffUploadSchedule.php";
      const data = {
        name: this.input.username.replace(/^\s+|\s+$/gm,''),
        password: this.input.password.replace(/^\s+|\s+$/gm,''),
        search_condition: this.search_condition,
        event: event,
        start_date: this.search_date.start_date,
        end_date: this.search_date.end_date
      }
      axios.post(url, data).then(function(response) {
        if (response.data.status == true) {
          this.search()
        }
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
    show_password() {
      var x = document.getElementById("password");
      if (x.type === "password") {
        x.type = "text";
      } else {
        x.type = "password";
      }
    },
    setCookie(cookieName, value, exdays){
      var exdate = new Date();
      exdate.setDate(exdate.getDate() + exdays);
      var cookieValue = escape(value) + ((exdays==null) ? "" : "; expires=" + exdate.toGMTString());
      document.cookie = cookieName + "=" + cookieValue;
    },
    deleteCookie(cookieName){
      var expireDate = new Date();
      expireDate.setDate(expireDate.getDate() - 1);
      document.cookie = cookieName + "= " + "; expires=" + expireDate.toGMTString();
    },
    getCookie(cookieName) {
      cookieName = cookieName + '=';
      var cookieData = document.cookie;
      var start = cookieData.indexOf(cookieName);
      var cookieValue = '';
      if(start != -1){
          start += cookieName.length;
          var end = cookieData.indexOf(';', start);
          if(end == -1)end = cookieData.length;
          cookieValue = cookieData.substring(start, end);
      }
      return unescape(cookieValue);
    }
  },
}
</script>