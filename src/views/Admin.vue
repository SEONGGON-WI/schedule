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
    <v-btn class="mx-2" color="yellow darken-4" @click="analytics_show = true">
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
                  :items="client_items"
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
                  :items="name_items"
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
                  :items="agenda_items"
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
              @click:day="edit(event)"
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
    v-if="edit_show"
    @close="edit_close($event)"
    :client="client"
    :name="name"
    :agenda="agenda"
    :date="edit_date"
  ></admin-edit>

  <admin-analytics
    v-if="analytics_show"
    @close="analytics_close($event)"
    :client="client"
    :name="name"
    :agenda ="agenda"
    :date="calendar_date"
  ></admin-analytics>

  <admin-dialog
    v-if="remove_show"
    @accept="remove"
    @close="remove_close"
    text="削除しますか？"
  ></admin-dialog>

  <alert
    v-if="alert_show"
    @close="alert_show = false"
    :text="alert_text"
  ></alert>

  <client-list-dialog
    v-if="client_show"
    @close="client_close"
    :agenda_items="agenda_items"
    :date="search_date"
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
    colors: ['grey darken-2','orange','teal accent-4'],
    search_date: {start_date: '', end_date: ''},
    today: '',
    client: '',
    client_items: [],
    name: '全員',
    name_items: [],
    agenda: '',
    agenda_items: [],
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
    edit_date: '',
    analytics_show: false,
    remove_show: false,
    alert_show: false,
    alert_text: '',
    downloading: false,
    toggle_key: 0,
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
  },
  computed: {
    get_agenda() {
      return this.calendar_events.length + "件";
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
          this.remove_show = true;
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
    async get_data() {
      const url = this.root_folder + "/app/adminGetSchedule.php";
      const data = this.search_date;
      await axios.post(url, data).then(function(response) {
        if (response.data.status) {
          if (response.data.status == true && response.data.data != '') {
            this.get_events_items()
            this.$store.commit('set_calendar_events', response.data.data)  
          } else {
            this.name_items = []
            this.agenda_items = []
            this.calendar_events = [];
            this.$store.commit('set_calendar_events', []);
            if (response.data.status == false) {
              this.alert(response.data.message);
            }
          }
        }
      }.bind(this))
    },
    get_events_items() {
      const data = this.$store.getters.calendar_events
      let name_items = []
      let agenda_items = []
      data.map(element => {
        name_items = name_items.concat(element.name)
        agenda_items = agenda_items.concat(element.agenda)
      })
      const name_items_set = new Set(name_items)
      const agenda_items_set = new Set(agenda_items)
      this.name_items = ['全員', ...name_items_set.sort(function (a, b) {
        return a.localeCompare(b, 'ja')
      })]
      this.agenda_items = ['', '空きスケジュール', 'スタッフ日給未入力', '管理者日給未入力', ...agenda_items_set.sort(function (a, b) {
        return a.localeCompare(b, 'ja')
      })]
    },
    async get_client() {
      const url = this.root_folder + "/app/adminGetClient.php";
      const data = {
        start_date: this.search_date.start_date
      }
      await axios.post(url, data).then(function(response) {
        if (response.data.status) {
          if (response.data.status == true && response.data.data != '') {
            this.get_client_items()
            this.$store.commit('set_client_agenda', response.data.data)
          } else {
            this.clients_items = []
            this.$store.commit('set_client_agenda', []);
            if (response.data.status == false) {
              this.alert(response.data.message);
            }
          }
        }
      }.bind(this))
    },
    get_client_items() {
      const clients = this.$store.getters.client_agenda
      let clients_items = clients.map(element => element.client);
      const clients_items_set = new Set(clients_items)
      this.client_items = ['', ...clients_items_set.sort(function (a, b) {
        return a.localeCompare(b, 'ja')
      })]
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
    edit(item) {
      console.log(item)

      this.edit_date = item.date
      if (item.event.length != 0) {
        this.edit_show = true
      }
    },
    edit_close(event) {
      if(event == true) {
        this.reload()
      }
      this.edit_show = false
    },
    analytics_close(event) {
      if (event == true) {
        this.reload()
      }
      this.analytics_show = false
    },
    client_close() {
      this.get_client_items()
      this.client_show = false
    },
    async csv_download() {
      const file_name = "経理表_" + this.$refs.calendar.lastStart.year + "_" + this.$refs.calendar.lastStart.month + ".csv"
       var config = {
        responseType: "blob",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
      };

      this.csvdownloading = true
      const url = this.root_folder + "/app/csvDownload.php"
      const data = this.search_date
      await axios.post(url, data, config).then(function (response) {
        this.downloadCSV(file_name, response)
      }.bind(this))
    },
    async csv_download2() {
      if (this.client == '') {
        return
      }
      const file_name = "請求書_" + this.client + "_" + this.$refs.calendar.lastStart.year + "_" + this.$refs.calendar.lastStart.month + ".csv"
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
      const url = this.root_folder + "/app/csvDownload2.php"
      await axios.post(url, data, config).then(function (response) {
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
    remove() {
      const url = this.root_folder + "/app/adminRemoveSchedule.php";
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
      this.remove_show = false
    },
    remove_close(event) {
      if (event == true) {
        this.remove()
      }
      this.remove_show = false;
    },
    alert(text) {
      this.alert_text = text;
      this.alert_show = true;
    },
    async reload() {
      await this.get_data()
      await this.get_client()
      await this.search()
    },
    clear() {
      this.client = ''
      this.name = '全員'
      this.agenda = ''
      this.search();
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
  }
}
</script>
