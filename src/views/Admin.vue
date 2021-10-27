<template>
<v-app id="inspire">
  <v-app-bar app color="primary" dark fixed>
    <v-menu offset-y :nudge-width="200" bottom class="mx-2">
      <template v-slot:activator="{ on, attrs }">
        <v-app-bar-nav-icon v-bind="attrs" v-on="on">
          <v-icon large>menu</v-icon>
        </v-app-bar-nav-icon>
      </template>
      <v-card outlined>
        <v-list>
          <v-list-item
            v-for="(item, i) in menu_item"
            :key="i"
            @click="menu_action(item.action)"
            class="my-2"
          >
            <v-list-item-action>
              <v-icon large>{{ item.icon }}</v-icon>
            </v-list-item-action>
            <v-list-item-content>
              <v-list-item-title>
                {{ item.text }}
              </v-list-item-title>
            </v-list-item-content>
          </v-list-item>
        </v-list>
      </v-card>
    </v-menu>

    <v-toolbar-title class="mx-4">スケジュール</v-toolbar-title>
    <v-toolbar-title class="mx-3">
      {{ calendar_date }}
    </v-toolbar-title>
    <v-spacer></v-spacer>
    <v-btn class="mx-2" color="yellow darken-4" @click="analytics()">
      <v-icon>analytics</v-icon>集計
    </v-btn>
    <v-btn class="success mx-2" color="white" @click="click(2)">
      <v-icon>autorenew</v-icon>更新
    </v-btn>
  </v-app-bar>

  <v-main style="position: fixed !important; width: 100%; height: 100%;">
    <v-progress-linear :indeterminate="downloading" color="black"></v-progress-linear>

    <alert
      @close="alert_show = false"
      :text="alert_text"
      :type="alert_type"
      v-if="alert_show"
    ></alert>

    <v-container fluid class="fill-height pa-0">
      <v-row class="fill-height">
        <v-col>
          <v-sheet>
            <v-toolbar class="pa-0" flat>
              <v-btn outlined class="mx-2" color="grey darken-2" @click="setToday">今日</v-btn>
              <v-btn icon text class="mx-1 pl-2" color="grey darken-2" @click="prevDate"><v-icon>arrow_back_ios</v-icon></v-btn>
              <v-btn icon text class="mx-1 pl-2" color="grey darken-2" @click="nextDate"><v-icon>arrow_forward_ios</v-icon></v-btn>
              <v-col cols="2">
                <v-select 
                  height="30"
                  class="mt-6 ml-2"
                  v-model="calendar_type" 
                  :items="calendar_type_item"
                  append-icon="arrow_drop_down"
                ></v-select>
              </v-col>
              <v-spacer></v-spacer>
              <div class="total mr-4">
                {{ get_agenda }}
              </div>
              <v-col cols="2">
                <v-select 
                  height="30"
                  class="mt-6"
                  v-model="name" 
                  :items="get_name_items"
                  label="名前"
                  @change="search"
                  append-icon="arrow_drop_down"
                ></v-select>
              </v-col>
              <v-col cols="2">
                <v-select 
                  height="30"
                  class="mt-6"
                  v-model="agenda" 
                  :items="get_agenda_items"
                  label="案件"
                  @change="search"
                  append-icon="arrow_drop_down"
                ></v-select>
              </v-col>
              <v-btn icon @click.native="clear" class="mr-2">
                <v-icon>clear</v-icon>
              </v-btn>
            </v-toolbar>
          </v-sheet>

          <v-sheet height="90%" class="mx-3">
            <v-calendar
              ref="calendar"
              v-model="calendar"
              :type="calendar_type"
              :events="calendar_events"
              :event-color="get_event_color"
              locale="ja-jp"
              event-more-text="•••"
              interval-count=0
              @click:day="edit"
              @change="fetch"
            >
              <template v-slot:event="{ event }">
                <div class="pt-1 ml-1" v-if="event.agenda == ''">
                  {{ event.name }}
                </div>
                <div class="pt-1 ml-1" v-else>
                  {{ event.name }} - {{ event.agenda }}
                </div>
              </template>
            </v-calendar>
          </v-sheet>
        </v-col>
      </v-row>
    </v-container>
  </v-main>

  <admin-edit
    @accept="accept_edit($event)"
    @prev="prev_edit($event)"
    @next="next_edit($event)"
    @close="close_edit"
    v-if="edit_show"
    :items="edit_items"
    :date="edit_date"
  ></admin-edit>

  <admin-analytics
    @change="change_analytics($event)"
    @close="close_analytics"
    v-if="analytics_show"
    :items="analytics_items"
    :name_items="analytics_name_items"
    :name="analytics_name"
    :date="calendar_date"
  ></admin-analytics>

  <admin-dialog
    @accept="accept"
    @close="close"
    v-if="dialog"
    :text="text"
  ></admin-dialog>

  <staff-list-dialog
    v-if="staff_show"
    @close="staff_show = false"
  >

  </staff-list-dialog>
</v-app>
</template>
<style lang="scss">
.v-textarea .v-input__control .v-input__slot .v-text-field__slot textarea{
  font-size : 25px !important;
}
.v-event {
  width: 94% !important;
  left: 3% !important;
  height: 25% !important;
  top: 1% !important;
  margin-bottom: 0px 0px 0px 0px !important;
}
@media screen and ( max-width: 1000px ) {
  .v-event {
    width: 94% !important;
    left: 3% !important;
    height: 10% !important;
    top: 1% !important;
    margin-bottom: 5px !important;
  }
}
.v-event-more{
  width: 90% !important;
  left: 5% !important;
  height: 20px !important;
  top: 1% !important;
}
.custom_dialog {
  max-height: 80% !important;
  width: 80% !important;
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
    width: 90% !important;
    position: fixed !important;
    top: 10% !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    align-content: normal !important;
    justify-content: normal !important;
  }
}
</style>
<script>
import alert from '@/components/alert.vue';
import AdminEdit from '@/components/AdminEdit.vue';
import AdminDialog from '@/components/AdminDialog.vue';
import StaffListDialog from '@/components/staffListDialog.vue';
import AdminAnalytics from '@/components/AdminAnalytics.vue'
import axios from 'axios';

export default {
  name: 'admin',
  components: {
    alert,
    AdminEdit,
    AdminDialog,
    StaffListDialog,
    AdminAnalytics,
  },
  data: () => ({
    menu_item: [
      { icon: "settings", text: "システム設定", action: "system"},
      { icon: "people", text: "スタッフ管理", action: "staff"},
      { icon: "delete", text: "データ削除", action: "remove"},
      { icon: "file_download", text: "CSV出力", action: "download"}
    ],
    system_show: false,
    staff_show: false,
    data_show: false,
    colors: ['grey darken-2','orange','teal accent-4'],
    search_date: {},
    name: '全員',
    agenda: '',
    calendar: '',
    calendar_date: '',
    calendar_type: 'month',
    calendar_type_item: [
      {text: '月', value:'month'},
      {text: '週', value:'week'}
    ],
    calendar_events: [],
    edit_show: false,
    edit_items: [],
    edit_date: '',
    analytics_show: false,
    analytics_items: [],
    analytics_name_items: [],
    analytics_name: '',
    alert_show: false,
    alert_type: '',
    alert_text: '',
    action: 0,
    text: '',
    dialog: false,
    downloading: false,
    today: '',
  }),
  created() {
    const date = new Date();
    const year = date.getFullYear();
    const month = ("0" + (1 + date.getMonth())).slice(-2);
    const day = ("0" + date.getDate()).slice(-2);
    this.today = year + "-" + month + "-" + day;
    this.calendar_date = year + "年 " + month + "月";
    this.setToday();
  },
  computed: {
    get_agenda() {
      return this.calendar_events.length + "件";
    },
    get_name_items() {
      const data = this.$store.getters.calendar_events;
      const name_items = data.map(element => element.name);
      return ['全員', ...name_items.filter((obj, index) => {
        return name_items.indexOf(obj) === index;
      })];
    },
    get_agenda_items() {
      const data = this.$store.getters.calendar_events;
      const agenda_items = data.map(element => element.agenda);
      return ['', ...agenda_items.filter((obj, index) => {
        return agenda_items.indexOf(obj) === index;
      })];
    },
  },
  methods: {
    menu_action(type) {
      switch (type) {
        case 'system':
          console.log("system")
          break;
        
        case 'staff':
          this.staff_show = true
          break;

        case 'remove':
          this.click(1)
          break;

        case 'download':
          this.csv_download()
          break;
        
        default:
          break;
      }
    },
    async fetch() {
      this.search_date = {
        start_date: this.$refs.calendar.lastStart.date,
        end_date: this.$refs.calendar.lastEnd.date
      }
      this.calendar_date = this.$refs.calendar.lastStart.year + "年 " + this.$refs.calendar.lastStart.month + "月"
      this.get_data();
    },
    fetch_data(data) {
      this.calendar_events = [];
      var firstTimestamp = null;
      var startTime = null;
      data.map(element => {
        firstTimestamp = new Date(`${element.date}T09:00:00`)
        startTime = new Date(firstTimestamp)

        if ((element.date < this.today) && (element.staff_day_salary != '')) {
          element.color = this.colors[2]
        } else if (element.agenda != '') {
          element.color = this.colors[1]
        } else {
          element.color = this.colors[0]
        }
        element.start = startTime
        // this.calendar_events.push({
        //   name: element.name,
        //   date: element.date,
        //   agenda: element.agenda,
        //   start_time: element.start_time,
        //   end_time: element.end_time,
        //   total_time: element.total_time,
        //   staff_hour_salary: element.staff_hour_salary,
        //   staff_day_salary: element.staff_day_salary,
        //   staff_expense: element.staff_expense,
        //   admin_hour_salary: element.admin_hour_salary,
        //   admin_day_salary: element.admin_day_salary,
        //   admin_expense: element.admin_expense,
        //   start: startTime,
        //   color: element.agenda == ''? this.colors[0] : this.colors[1]
        // })
      })
      this.calendar_events = data;
    },
    async get_data() {
      const url = "/schedule/app/adminGetSchedule.php";
      const data = this.search_date;
      await axios.post(url, data).then(function(response) {
        if (response.data.status) {
          if (response.data.status === 'success') {
            this.$store.commit('set_calendar_events', response.data.data)
            this.search();
          } else {
            this.calendar_events = [];
            this.$store.commit('set_calendar_events', []);
            this.alert(response.data.status, response.data.message, true);
          }
        }
      }.bind(this))
    },
    search() {
      let data = this.$store.getters.calendar_events;
      const name = this.name;
      const agenda = this.agenda;
      if (name != '全員') {
        data = data.filter(obj => obj.name == name);
      }
      if (agenda != '') {
        data = data.filter(obj => obj.agenda == agenda);
      }
      this.fetch_data(data)
    },
    analytics() {
      const data = this.$store.getters.calendar_events;
      this.analytics_name = this.name
      this.analytics_items = this.name == '全員' ? data.filter(obj => obj.agenda != '' ) : data.filter(obj => obj.name == this.name && obj.agenda != '' )
      const name_items = data.map(element => element.name);
      this.analytics_name_items =  ['全員', ...name_items.filter((obj, index) => {
        return name_items.indexOf(obj) === index;
      })];
      this.analytics_show = true
    },
    change_analytics(name) {
      const data = this.$store.getters.calendar_events;
      this.analytics_name = name
      this.analytics_items = name == '全員' ? data : data.filter(obj => obj.name == name)
    },
    close_analytics() {
      this.analytics_name = ''
      this.analytics_items = []
      this.analytics_show = false
    },
    export_event() {
      const calendar_date = this.today
      const export_data = JSON.stringify(this.$store.getters.calendar_events)
      const export_file = `${calendar_date}_schedule.json`
      let bom = new Uint8Array([0xEF, 0xBB, 0xBF]);
      let blob = new Blob([ bom,export_data ], { "type" : "text/json" });
      // IE11 ( msSaveBlog が有効なら)
      if (window.navigator.msSaveBlob) {
          window.navigator.msSaveBlob(blob, export_file)
          window.navigator.msSaveOrOpenBlob(blob, export_file)
      }
      // IE11 以外なら( Chrome, Firefox, Android, etc...)
      else {
          const url = window.URL.createObjectURL(blob)
          const link = document.createElement('a')
          link.href = url
          link.setAttribute('download', export_file)
          link.click()
      }
    },
    // async save() {
    //   const url = "/schedule/app/adminUploadSchedule.php";
    //   const data = {
    //     start_date: this.search_date.start_date,
    //     end_date: this.search_date.end_date,
    //     event: this.$store.getters.calendar_events
    //   }
    //   await axios.post(url, data).then(function(response) {
    //     if (response.data.status){
    //       if (response.data.status == "success") {
    //         if (this.action != 0) {
    //           this.alert(response.data.status, response.data.message, true);
    //         }
    //       } else {
    //         this.alert(response.data.status, response.data.message, true);
    //       }
    //     }
    //   }.bind(this))
    // },
    async remove() {
      const url = "/schedule/app/adminRemoveSchedule.php";
      const today = new Date();
      const current_date = today.getFullYear() +"-"+ (today.getMonth()+1) +"-"+ today.getDate();
      const data = {
        current_date: current_date
      }
      await axios.post(url, data).then(function(response) {
        if (response.data.status === 'success') {
          this.get_data()
        } else {
          this.alert(response.data.status, response.data.message, true);
        }
      }.bind(this))
    },
    clear() {
      this.name = '全員'
      this.agenda = ''
      const data = this.$store.getters.calendar_events;
      this.fetch_data(data);
    },
    edit(item) {
      if (!this.calendar_events.find(e => e.date == item.date)) {
        return;
      }
      const lodash = require("lodash");
      const data = this.$store.getters.calendar_events;
      if (this.name != '全員') {
        this.edit_items = lodash.cloneDeep(data.filter(obj => {
          if (obj.date == item.date && obj.name == this.name) {
            return obj
          }
        }));
      } else {
        this.edit_items = lodash.cloneDeep(data.filter(obj => obj.date == item.date));
      }
      this.edit_date = item.date
      if (this.edit_items.length != 0) {
        this.edit_show = true;
      }
    },
    accept_edit(item) {
      const data = this.$store.getters.calendar_events
      let event = []
      if (this.name != '全員') {
        event = data.filter(obj => {
          if (obj.date != this.edit_date || obj.name != this.name) {
            return obj
          }
        });
      } else {
        event = data.filter(obj => obj.date != this.edit_date)
      }
      event.push(...item)
      this.$store.commit('set_calendar_events', event);
      this.edit_show = false;
      this.search();
    },
    close_edit() {
      this.edit_show = false
      this.edit_items = []
      this.edit_date = ''
    },
    calculate_edit_date(type) {
      var date = this.edit_date.split("-")
      var time = type === 'next' ? new Date(parseInt(date[0]), parseInt(date[1]) -1, parseInt(date[2]) + 1)
                                 : new Date(parseInt(date[0]), parseInt(date[1]) -1, parseInt(date[2]) - 1);
      date = time.getFullYear() +"-"
      if (parseInt((time.getMonth()+1)) < 10) {
        date = date + "0" + (time.getMonth()+1) + "-"
      } else {
        date = date + (time.getMonth()+1) + "-"
      }
      if (parseInt(time.getDate()) < 10) {
        date = date + "0" + time.getDate()
      } else {
        date = date + time.getDate()
      }
      return date
    },
    prev_edit(item) {
      const edit_data = this.$store.getters.calendar_events
      let event = []
      if (this.name != '全員') {
        event = edit_data.filter(obj => {
          if (obj.date != this.edit_date || obj.name != this.name) {
            return obj
          }
        });
      } else {
        event = edit_data.filter(obj => obj.date != this.edit_date)
      }
      event.push(...item)

      this.$store.commit('set_calendar_events', event);
      this.edit_date = this.calculate_edit_date('prev')
      const data = this.$store.getters.calendar_events;
      const lodash = require("lodash");
      if (this.name != '全員') {
        this.edit_items = lodash.cloneDeep(data.filter(obj => {
          if (obj.date == this.edit_date && obj.name == this.name) {
            return obj
          }
        }));
      } else {
        this.edit_items = lodash.cloneDeep(data.filter(obj => obj.date == this.edit_date));
      }
      this.search();
    },
    next_edit(item) {
      const edit_data = this.$store.getters.calendar_events
      let event = []
      if (this.name != '全員') {
        event = edit_data.filter(obj => {
          if (obj.date != this.edit_date || obj.name != this.name) {
            return obj
          }
        });
      } else {
        event = edit_data.filter(obj => obj.date != this.edit_date)
      }
      event.push(...item)

      this.$store.commit('set_calendar_events', event);
      this.edit_date = this.calculate_edit_date('next')
      const data = this.$store.getters.calendar_events;
      const lodash = require("lodash");
      if (this.name != '全員') {
        this.edit_items = lodash.cloneDeep(data.filter(obj => {
          if (obj.date == this.edit_date && obj.name == this.name) {
            return obj
          }
        }));
      } else {
        this.edit_items = lodash.cloneDeep(data.filter(obj => obj.date == this.edit_date));
      }
      this.search();
    },
    click(action) {
      if (action == 1) {
        this.text = "削除しますか？"
      } else if (action == 2) {
        this.text = "更新しますか？"
      }
      this.action = action;
      this.dialog = true;
    },
    accept() {
      this.dialog = false;
      if (this.action == 1) {
        this.remove(); 
      } else if (this.action == 2) {
        this.get_data(); 
      }
      this.action = 0;
    },
    close() {
      this.dialog = false;
      this.action = 0;
    },
    alert(type, text, show) {
      this.alert_type = type
      this.alert_text = text;
      this.alert_show = show;
    },
    async csv_download() {
       var config = {
        responseType: "blob",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
      };

      this.csvdownloading = true
      const data = this.search_date
      await axios.post("/schedule/app/csvDownload.php", data, config).then(function (response) {
        this.downloadCSV(response)
      }.bind(this))
    },
    downloadCSV(res) {
      var blob = new Blob([res.data], { type: "text/csv" });
      if (blob.size === 0) {
        return;
      }
      const file_name = this.$refs.calendar.lastStart.year + "_" + this.$refs.calendar.lastStart.month + ".csv"

      if (window.navigator.msSaveBlob) {
        window.navigator.msSaveBlob(blob, file_name);
        window.navigator.msSaveOrOpenBlob(blob, file_name);
      }
      // IE11 以外なら( Chrome, Firefox, Android, etc...)
      else {
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement("a");
        link.href = url;
        link.setAttribute("download", file_name);
        link.click();
      }
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
    get_event_color(event) {
      return event.color;
    },
  }
}
</script>
