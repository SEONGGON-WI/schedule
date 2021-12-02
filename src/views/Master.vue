<template>
<v-app id="inspire">
  <v-app-bar app color="primary" dark fixed>
    <v-toolbar-title class="mx-4">スケジュール</v-toolbar-title>
    <v-toolbar-title class="mx-3">
      {{ calendar_date }}
    </v-toolbar-title>
    <v-spacer></v-spacer>
    <v-toolbar-title class="mx-3">
      {{ get_agenda }}
    </v-toolbar-title>
    <v-btn class="mx-2" color="yellow darken-4" @click="get_json()">
      <v-icon>autorenew</v-icon>json
    </v-btn>
    <v-btn class="mx-2" color="yellow darken-4" @click="get_data()">
      <v-icon>autorenew</v-icon>更新
    </v-btn>
    <v-btn class="success mx-2" color="white" @click="clear()">
      <v-icon>clear</v-icon>クリア
    </v-btn>
    <v-btn class="success mx-2" color="white" @click="export_event()">
      <v-icon>download</v-icon>JSON出力
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
              <v-col cols="4">
                <v-file-input
                  v-model="importFile"
                ></v-file-input>
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
                <v-radio-group class="mt-0 pa-2" v-model="type" row>  
                  <v-radio label="上書き" value="upload"></v-radio>
                  <v-radio label="挿入" value="ignore" class="pl-2"></v-radio>
                </v-radio-group>
              </v-col>
              <v-col cols="4">
                <v-btn color="info" class="ma-2" @click.native="importEvent">
                  <v-icon>cloud_upload</v-icon>アップロード
                </v-btn>
                <v-btn color="success" class="ma-2" @click.native="upload">
                  <v-icon>save</v-icon>保存
                </v-btn>
              </v-col>
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
import axios from 'axios';
export default {
  name: 'master',
  components: {
  },
  data: () => ({
    colors: ['grey darken-2','orange','teal accent-4'],
    search_date: {},
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
    today: '',
    importFile: null,
    type: 'upload',
    name: '全員',
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
    get_name_items() {
      // const data = JSON.parse(JSON.stringify(this.$store.getters.calendar_events))
      const data = this.$store.getters.calendar_events
      const name_items = data.map(element => element.name);
      return ['全員', ...name_items.filter((obj, index) => {
        return name_items.indexOf(obj) === index;
      })];
    },
  },
  methods: {
    async fetch() {
      this.search_date = {
        start_date: this.$refs.calendar.lastStart.date,
        end_date: this.$refs.calendar.lastEnd.date
      }
      this.calendar_date = this.$refs.calendar.lastStart.year + "年 " + this.$refs.calendar.lastStart.month + "月"
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
      })
      this.calendar_events = data;
    },
    async get_data() {
      const url = this.root_folder + "/app/adminGetSchedule.php";
      const data = this.search_date;
      await axios.post(url, data).then(function(response) {
        if (response.data.status) {
          if (response.data.status == true && response.data.data != '') {
            this.$store.commit('set_calendar_events', response.data.data)  
            this.fetch_data(response.data.data)
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
    search() {
      let data = JSON.parse(JSON.stringify(this.$store.getters.calendar_events))
      const name = this.name;
      if (name != '全員') {
        data = data.filter(obj => obj.name == name);
      }
      this.fetch_data(data)
    },
    importEvent() {
      if (this.importFile == null) {
        return;
      }
      const loadFunc = async () => {
        const upload_list = JSON.parse(reader.result)
        this.importFile = null
        this.$store.commit('set_calendar_events', upload_list) 
        this.fetch_data(upload_list)
      }
      const reader = new FileReader();
      reader.onload = loadFunc;
      reader.readAsText(this.importFile);
    },
    async upload() {
      const url = this.root_folder + "/app/adminSchedule.php";
      const data = {
        type: this.type,
        event: this.$store.getters.calendar_events,
      }
      await axios.post(url, data).then(function(response) {
        if (response.data.status == false){
          this.alert(response.data.message);
        }
      }.bind(this))
    },
    export_event() {
      const calendar_date = this.today
      const export_data = JSON.stringify(this.$store.getters.calendar_events, null, 2)
      const export_file = `${calendar_date}_${this.$store.getters.calendar_events.length}_schedule.json`
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
    get_json() {
      const upload_list = []
      const export_data = JSON.stringify(upload_list, null, 2)
      const export_file = `schedule.json`
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
    clear() {
      this.$store.commit('set_calendar_events', [])  
      this.calendar_events = []
    },
    sort(data) {
      data.sort(function(a, b) {
          return a.date > b.date ? -1 : a.date < b.date ? 1 : 0;
      });
    },
  }
}
</script>
