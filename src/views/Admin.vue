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
    <v-toolbar-title class="mx-3">
      {{ get_agenda }}
    </v-toolbar-title>
    <v-btn class="mx-2" color="yellow darken-4" @click="analytics()">
      <v-icon>analytics</v-icon>集計
    </v-btn>
    <v-btn class="success mx-2" color="white" @click="reload()">
      <v-icon>autorenew</v-icon>更新
    </v-btn>
  </v-app-bar>

  <v-main style="position: fixed !important; width: 100%; height: 100%;">
    <v-progress-linear :indeterminate="downloading" color="black"></v-progress-linear>
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
              <v-col cols="2">
                <v-select 
                  height="30"
                  class="mt-6"
                  v-model="client" 
                  :items="get_client_items"
                  label="顧客"
                  @change="search"
                  :menu-props="{ maxHeight: '800' }"
                  append-icon="arrow_drop_down"
                ></v-select>
              </v-col>
              <v-col cols="2">
                <v-select 
                  height="30"
                  class="mt-6"
                  v-model="name" 
                  :items="get_name_items"
                  label="名前"
                  @change="search"
                  :menu-props="{ maxHeight: '800' }"
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
                  :menu-props="{ maxHeight: '800' }"
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
              :weekdays="calendar_format"
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
    @prev="prev_edit()"
    @next="next_edit()"
    @close="edit_close()"
    v-if="edit_show"
    :items="edit_items"
    :date="edit_date"
  ></admin-edit>

  <admin-analytics
    @change="change_analytics($event)"
    @reload="reload()"
    @close="analytics_show = false"
    v-if="analytics_show"
    :items="analytics_items"
    :name_items="analytics_name_items"
    :name="analytics_name"
    :agenda_items="analytics_agenda_items"
    :agenda ="analytics_agenda"
    :date="calendar_date"
  ></admin-analytics>

  <admin-dialog
    @accept="accept"
    @close="close"
    v-if="dialog"
    :text="text"
  ></admin-dialog>

  <alert
    @close="alert_show = false"
    :text="alert_text"
    v-if="alert_show"
  ></alert>

  <client-list-dialog
    v-if="client_show"
    :agenda_items="get_agenda_items"
    :today="today"
    :start_date="search_date.start_date"
    @accept="click(2)"
    @close="client_show = false"
  ></client-list-dialog>

  <staff-list-dialog
    v-if="staff_show"
    @close="staff_show = false"
  ></staff-list-dialog>
</v-app>
</template>
<style lang="scss">
.v-textarea .v-input__control .v-input__slot .v-text-field__slot textarea{
  font-size : 25px !important;
}
.v-data-table > .v-data-table__wrapper > table > thead > tr > th {
  font-size: 20px !important;
}
.v-input.v-input--selection-controls .v-input__control .v-input__slot label{
  font-size: 25px !important;
}
.v-input__control .v-input__slot .v-text-field__slot input {
  font-size: 25px !important;
}
.v-input__control .v-input__slot .v-text-field__slot label {
  font-size: 20px !important;
}
.v-text-field--filled .v-input__control{
  background-color: #BDBDBD !important;
}
.v-event-more{
  width: 90% !important;
  left: 5% !important;
  height: 20px !important;
  top: 1% !important;
}
.v-calendar-weekly__day .v-event {
  width: 94% !important;
  left: 3% !important;
  height: 21% !important;
  top: 1% !important;
  margin-bottom: 1px !important;
}
.v-calendar-daily_head-day .v-event {
  width: 94% !important;
  left: 3% !important;
  height: 35px !important;
  top: 1% !important;
  margin-bottom: 5px !important;
}
@media screen and ( max-width: 1000px ) {
  .v-calendar-weekly__day .v-event {
    width: 94% !important;
    left: 3% !important;
    height: 10% !important;
    top: 1% !important;
    margin-bottom: 5px !important;
  }
  .v-calendar-daily_head-day .v-event {
    width: 94% !important;
    left: 3% !important;
    height: 35px !important;
    top: 1% !important;
    margin-bottom: 5px !important;
  }
}
</style>
<script>
import alert from '@/components/alert.vue';
import AdminEdit from '@/components/AdminEdit.vue';
import AdminDialog from '@/components/AdminDialog.vue';
import ClientListDialog from '../components/clientListDialog.vue';
import StaffListDialog from '@/components/staffListDialog.vue';
import AdminAnalytics from '@/components/AdminAnalytics.vue';
import axios from 'axios';

export default {
  name: 'admin',
  components: {
    alert,
    AdminEdit,
    AdminDialog,
    ClientListDialog,
    StaffListDialog,
    AdminAnalytics,
  },
  data: () => ({
    menu_item: [
      { icon: "business", text: "クライアント管理", action: "client"},
      { icon: "people", text: "スタッフ管理", action: "staff"},
      { icon: "delete", text: "データ削除", action: "remove"},
      { icon: "cloud_download", text: "経理表出力", action: "download"},
      { icon: "cloud_download", text: "請求書出力", action: "download2"}
    ],
    client_show: false,
    staff_show: false,
    data_show: false,
    colors: ['grey darken-2','orange','teal accent-4'],
    search_date: {},
    client: '',
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
    calendar_format : [1,2,3,4,5,6,0],
    edit_show: false,
    editItems: [],
    edit_items: [],
    edit_date: '',
    analytics_show: false,
    analytics_items: [],
    analytics_name_items: [],
    analytics_name: '',
    analytics_agenda_items: [],
    analytics_agenda: '',
    alert_show: false,
    alert_type: '',
    alert_text: '',
    action: 0,
    text: '',
    dialog: false,
    downloading: false,
    today: '',
    toggle_key: 0,
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
    get_client_items() {
      // let clients = JSON.parse(JSON.stringify(this.$store.getters.client_agenda))
      const clients = this.$store.getters.client_agenda
      const client_items = clients.map(element => element.client);
      // return ['', ...client_items.filter((obj, index) => {
      //   return client_items.indexOf(obj) === index;
      // })];
      return ['', ...client_items.filter((obj, index) => {
        return client_items.indexOf(obj) === index;
      }).sort(function (a, b) {
        return a.localeCompare(b, 'ja')
      })];
    },
    get_name_items() {
      // const data = JSON.parse(JSON.stringify(this.$store.getters.calendar_events))
      const data = this.$store.getters.calendar_events
      const name_items = data.map(element => element.name);
      // return ['全員', ...name_items.filter((obj, index) => {
      //   return name_items.indexOf(obj) === index;
      // })];
      // return ['全員', ...name_items.filter((obj, index) => {
      //   return name_items.indexOf(obj) === index;
      // }).sort(function (a, b) {
      //   return a.localeCompare(b, 'ja')
      // })];
      return ['全員', ...name_items.filter((obj, index) => {
        return name_items.indexOf(obj) === index;
      }).sort(this.sort_by([{key:'name'}]))];
    },
    get_agenda_items() {
      // const data = JSON.parse(JSON.stringify(this.$store.getters.calendar_events))
      const data = this.$store.getters.calendar_events
      const agenda_items = data.map(element => element.agenda);
    //   return ['', '空きスケジュール', 'スタッフ日給未入力', '管理者日給未入力', ...agenda_items.filter((obj, index) => {
    //     return agenda_items.indexOf(obj) === index;
    //   }).sort(function(a, b) {
    //     const upperCaseA = a.toUpperCase();
    //     const upperCaseB = b.toUpperCase();
        
    //     if(upperCaseA > upperCaseB) return 1;
    //     if(upperCaseA < upperCaseB) return -1;
    //     if(upperCaseA === upperCaseB) return 0;
    //   })];
    // },
      return ['', '空きスケジュール', 'スタッフ日給未入力', '管理者日給未入力', ...agenda_items.filter((obj, index) => {
        return agenda_items.indexOf(obj) === index;
      }).sort(function (a, b) {
        return a.localeCompare(b, 'ja')
      })];
    },
  },
  methods: {
    menu_action(type) {
      switch (type) {
        case 'client':
          this.client_show = true
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

        case 'download2':
          this.csv_download2()
          break;
        
        default:
          break;
      }
    },
    fetch() {
      this.search_date = {
        start_date: this.$refs.calendar.lastStart.date,
        end_date: this.$refs.calendar.lastEnd.date
      }
      this.calendar_date = this.$refs.calendar.lastStart.year + "年 " + this.$refs.calendar.lastStart.month + "月"
      this.reload()
    },
    fetch_data(data) {
      this.calendar_events = [];
      var firstTimestamp = null;
      var startTime = null;
      data.map(element => {
        firstTimestamp = new Date(`${element.date}T09:00:00`)
        startTime = new Date(firstTimestamp)
        if ((element.date <= this.today) && (element.staff_day_salary != '')) {
          element.color = this.colors[2]
        } else if (element.agenda != '') {
          element.color = this.colors[1]
        } else {
          element.color = this.colors[0]
        }
        element.start = startTime
      })
      this.calendar_events = data;
      this.toggle()
    },
    async get_data() {
      const url = "/schedule/app/adminGetSchedule.php";
      const data = this.search_date;
      await axios.post(url, data).then(function(response) {
        if (response.data.status) {
          if (response.data.status == true && response.data.data != '') {
            this.$store.commit('set_calendar_events', response.data.data)  
          } else {
            this.calendar_events = [];
            this.$store.commit('set_calendar_events', []);
            if (response.data.status == false) {
              this.alert(response.data.message);
            }
          }
        }
      }.bind(this))
    },
    async get_edit_data() {
      const url = "/schedule/app/adminGetEditSchedule.php";
      const data = {
        date: this.edit_date
      }
      await axios.post(url, data).then(function(response) {
        if (response.data.status) {
          if (response.data.status == true && response.data.data != '') {
            this.editItems = response.data.data
          } else {
            this.editItems = []
            if (response.data.status == false) {
              this.alert(response.data.message);
            }
          }
        }
      }.bind(this))
    },
    async get_client() {
      const url = "/schedule/app/adminGetClient.php";
      const data = {
        start_date: this.search_date.start_date
      }
      await axios.post(url, data).then(function(response) {
        if (response.data.status) {
          if (response.data.status == true && response.data.data != '') {
            this.$store.commit('set_client_agenda', response.data.data)
          } else {
            this.$store.commit('set_client_agenda', []);
            if (response.data.status == false) {
              this.alert(response.data.message);
            }
          }
        }
      }.bind(this))
    },
    search() {
      let data = JSON.parse(JSON.stringify(this.$store.getters.calendar_events))
      const client = this.client;
      const name = this.name;
      const agenda = this.agenda;
      if (client != '') {
        data = data.filter(obj => obj.client == client);
      }
      if (name != '全員') {
        data = data.filter(obj => obj.name == name);
      }
      if (agenda != '') {
        if (agenda == '空きスケジュール') {
          data = data.filter(obj => obj.agenda == '');
        } else if (agenda == 'スタッフ日給未入力') {
          data = data.filter(obj => obj.agenda != '' && obj.staff_day_salary == '');
        } else if (agenda == '管理者日給未入力') {
          data = data.filter(obj => obj.agenda != '' && obj.admin_day_salary == '');
        } else {
          data = data.filter(obj => obj.agenda == agenda);
        }
      }
      this.fetch_data(data)
    },
    analytics() {
      let data = JSON.parse(JSON.stringify(this.$store.getters.calendar_events))
      const client = this.client;
      const name = this.name;
      const agenda = this.agenda;
      data = data.filter(obj => obj.agenda != '' && (obj.staff_day_salary != '' || obj.admin_day_salary != ''));
      
      if (client != '') {
        data = data.filter(obj => obj.client == client);
      }

      const name_items = data.map(element => element.name);
      this.analytics_name_items =  ['全員', ...name_items.filter((obj, index) => {
        return name_items.indexOf(obj) === index;
      }).sort(this.sort_by([{key:'name'}]))];

      const agenda_items = data.map(element => element.agenda);
      this.analytics_agenda_items =  ['', ...agenda_items.filter((obj, index) => {
        return agenda_items.indexOf(obj) === index;
      }).sort(this.sort_by([{key:'agenda'}]))];
      // data.sort(function(a, b) {
      //   // if (a.date < b.date) {
      //   //   return -1
      //   // } else if (a.agenda < b.agenda) {
      //   //   return -1
      //   // }
      //   return a.date < b.date ? -1 : a.date > b.date ? 1 : 0;
      // });
      // this.analytics_name_items =  ['全員', ...name_items.filter((obj, index) => {
      //   return name_items.indexOf(obj) === index;
      // }).sort(function (a, b) {
      //   return a.localeCompare(b, 'ja')
      // })];
      if (agenda != '' && agenda != '空きスケジュール' && agenda != 'スタッフ日給未入力' && agenda != '管理者日給未入力') {
        data = data.filter(obj => obj.agenda == agenda);
      }
      if (name != '全員') {
        data = data.filter(obj => obj.name == name);
      }
      const sort_list = [
        {key:'date'},
        {key:'agenda'}
      ]
      data.sort(this.sort_by(sort_list))
      this.analytics_name = name
      this.analytics_agenda = agenda
      this.analytics_items = data
      this.analytics_show = true
    },
    change_analytics(condition) {
      const name = condition.name
      const agenda = condition.agenda
      let data = JSON.parse(JSON.stringify(this.$store.getters.calendar_events))
      const client = this.client;
      data = data.filter(obj => obj.agenda != '' && (obj.staff_day_salary != '' || obj.admin_day_salary != ''));
      if (client != '') {
        data = data.filter(obj => obj.client == client);
      }
      if (name != '全員') {
        data = data.filter(obj => obj.name == name);
      }
      if (agenda != '' && agenda != '空きスケジュール' && agenda != 'スタッフ日給未入力' && agenda != '管理者日給未入力') {
        data = data.filter(obj => obj.agenda == agenda);
      }
      const sort_list = [
        {key:'date'},
        {key:'agenda'}
      ]
      data.sort(this.sort_by(sort_list))
      this.analytics_name = name
      this.analytics_agenda = agenda
      this.analytics_items = data
    },
    remove() {
      const url = "/schedule/app/adminRemoveSchedule.php";
      const today = new Date();
      const current_date = today.getFullYear() +"-"+ (today.getMonth()+1) +"-"+ today.getDate();
      const data = {
        current_date: current_date
      }
      axios.post(url, data).then(function(response) {
        if (response.data.status == true) {
          this.reload()
        } else {
          this.alert(response.data.message);
        }
      }.bind(this))
    },
    clear() {
      this.client = ''
      this.name = '全員'
      this.agenda = ''
      // const data = JSON.parse(JSON.stringify(this.$store.getters.calendar_events));
      // const data = this.$store.getters.calendar_events
      this.search();
    },
    async edit(item) {
      if (!this.calendar_events.find(e => e.date == item.date)) {
        return;
      }
      this.edit_date = item.date
      await this.get_edit_data()
      let edit_items = this.editItems
      if (edit_items == []) {
        return
      }
      // let edit_items = JSON.parse(JSON.stringify(this.$store.getters.calendar_events))
      if (this.client != '') {
        edit_items = edit_items.filter(obj => obj.client == this.client);
      } 
      if (this.name != '全員') {
        edit_items = edit_items.filter(obj => obj.name == this.name);
      }
      if (this.agenda != '') {
        if (this.agenda == '空きスケジュール') {
          edit_items = edit_items.filter(obj => obj.agenda == '');
        } else if (this.agenda == 'スタッフ日給未入力') {
          edit_items = edit_items.filter(obj => obj.agenda != '' && obj.staff_day_salary == '');
        } else if (this.agenda == '管理者日給未入力') {
          edit_items = edit_items.filter(obj => obj.agenda != '' && obj.admin_day_salary == '');
        } else {
          edit_items = edit_items.filter(obj => obj.agenda == this.agenda);
        }
      }
      this.edit_items = edit_items
      if (this.edit_items.length != 0) {
        this.edit_show = true;
      }
      await this.search()
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
    async get_edit() {
      await this.get_edit_data()
      let edit_items = this.editItems
      if (edit_items == []) {
        return
      }
      // let edit_items = JSON.parse(JSON.stringify(this.$store.getters.calendar_events))
      if (this.client != '') {
        edit_items = edit_items.filter(obj => obj.client == this.client);
      }
      if (this.name != '全員') {
        edit_items = edit_items.filter(obj => obj.name == this.name);
      }
      if (this.agenda != '') {
        if (this.agenda == '空きスケジュール') {
          edit_items = edit_items.filter(obj => obj.agenda == '');
        } else if (this.agenda == 'スタッフ日給未入力') {
          edit_items = edit_items.filter(obj => obj.agenda != '' && obj.staff_day_salary == '');
        } else if (this.agenda == '管理者日給未入力') {
          edit_items = edit_items.filter(obj => obj.agenda != '' && obj.admin_day_salary == '');
        } else {
          edit_items = edit_items.filter(obj => obj.agenda == this.agenda);
        }
      }
      this.edit_items = edit_items
      this.search();
    },
    prev_edit() {
      this.edit_date = this.calculate_edit_date('prev')
      this.get_edit()
    },
    next_edit() {
      this.edit_date = this.calculate_edit_date('next')
      this.get_edit()
    },
    edit_close() {
      this.reload()
      this.edit_show = false
    },
    click(action) {
      if (action == 1) {
        this.text = "削除しますか？"
      } else if (action == 2) {
        this.text = "反映しますか？"
      }
      this.action = action;
      this.dialog = true;
    },
    accept() {
      this.dialog = false;
      if (this.action == 1) {
        this.remove(); 
      } else if (this.action == 2) {
        this.load(); 
      }
      this.action = 0;
    },
    close() {
      this.dialog = false;
      this.action = 0;
    },
    alert(text) {
      this.alert_text = text;
      this.alert_show = true;
    },
    async csv_download() {
      const file_name = "経理表_" + this.$refs.calendar.lastStart.year + "_" + this.$refs.calendar.lastStart.month + ".csv"
       var config = {
        responseType: "blob",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
      };

      this.csvdownloading = true
      const data = this.search_date
      await axios.post("/schedule/app/csvDownload.php", data, config).then(function (response) {
        this.downloadCSV(file_name, response)
      }.bind(this))
    },
    async csv_download2() {
      if (this.client == '') {
        return
      }
      const file_name = "請求書_" + this.$refs.calendar.lastStart.year + "_" + this.$refs.calendar.lastStart.month + ".csv"
       var config = {
        responseType: "blob",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
      };
      this.csvdownloading = true
      const data = {
        start_date: this.search_date.start_date,
        end_date: this.search_date.end_date,
        client: this.client
      }
      await axios.post("/schedule/app/csvDownload2.php", data, config).then(function (response) {
        this.downloadCSV(file_name, response)
      }.bind(this))
    },
    downloadCSV(file_name, res) {
      var blob = new Blob([res.data], { type: "text/csv" });
      if (blob.size === 0) {
        return;
      }
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
      this.csvdownloading = false
    },
    async reload() {
      await this.get_data()
      await this.get_client()
      await this.search()
    },
    async load() {
      const url = "/schedule/app/adminUploadSchedule.php";
      const data = {
        start_date: this.search_date.start_date,
        end_date: this.search_date.end_date,
      }
      await axios.post(url, data).then(function(response) {
        if (response.data.status == true) {
          this.reload()
        } else {
          this.alert(response.data.message);
        }
      }.bind(this))
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
    toggle() {
      this.toggle_key = this.toggle_key === 0 ? 1 : 0
    },
    sort_by: function(orderlist) {
      // .sort(this.sort_by(this.sort_params))
      return (a, b) => {
        for (let i=0; i<orderlist.length; i++) {
          if (a[orderlist[i].key] < b[orderlist[i].key]) return -1 ;
          if (a[orderlist[i].key] > b[orderlist[i].key]) return -1 * -1;
        }
        return 0;
      }
    },
  }
}
</script>
