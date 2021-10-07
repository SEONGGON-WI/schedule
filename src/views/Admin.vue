<template>
<v-app id="inspire">
  <v-app-bar app color="primary" dark fixed>
    <v-toolbar-title class="ml-4">スケジュール</v-toolbar-title>
    <v-spacer></v-spacer>
    <v-btn class="accent mx-2" @click="click(1)">
      <v-icon>save</v-icon>登録
    </v-btn>
    <v-btn class="error mx-2" color="white" @click="click(2)">
      <v-icon>delete</v-icon>削除
    </v-btn>
    <v-btn class="success mx-2" color="white" @click="click(3)">
      <v-icon>autorenew</v-icon>更新
    </v-btn>
  </v-app-bar>
  <v-main class="mx-1" style="position: fixed !important; width: 100%; height: 100%;">
    <alert
      @close="alert_close"
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
              <v-btn fab text class="mx-1 pl-2" color="grey darken-2" @click="prevDate"><v-icon>arrow_back_ios</v-icon></v-btn>
              <v-btn fab text class="mx-1 pl-2" color="grey darken-2" @click="nextDate"><v-icon>arrow_forward_ios</v-icon></v-btn>
              <v-toolbar-title class="mx-1" v-if="$refs.calendar">
                {{ $refs.calendar.title }}
              </v-toolbar-title>
              <v-toolbar-title class="mx-1" v-else>
                {{ calendar_date }}
              </v-toolbar-title>

              <v-col cols="1">
                <v-select 
                  class="mt-6 ml-2"
                  v-model="calendar_type" 
                  :items="calendar_type_item"
                ></v-select>
              </v-col>

              <v-spacer></v-spacer>
              <div class="total mr-2">
                {{ get_agenda }}
              </div>

              <v-col cols="2">
                <v-select 
                  class="mt-6"
                  v-model="name" 
                  :items="get_name_items"
                  label="名前"
                  @change="search"
                ></v-select>
              </v-col>

              <v-col cols="2">
                <v-select 
                  class="mt-6"
                  v-model="comment" 
                  :items="get_comment_items"
                  label="案件"
                  @change="search"
                ></v-select>
              </v-col>

              <v-btn icon small @click.native="clear" class="mr-2">
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
              event-more-text="..."
              interval-count=0
              @click:day="edit"
              @change="fetch"
            >
              <template v-slot:event="{ event }">
                <div class="event_message mt-1 ml-1">
                  {{ event.name }}
                </div>
                <div class="event_message mt-1 ml-1">
                  {{ event.comment }}
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
    @close="edit_close"
    v-if="edit_show"
    :items="edit_items"
    :date="edit_date"
  ></admin-edit>

  <admin-dialog
    @accept="accept"
    @close="close"
    v-if="dialog"
    :text="text"
  ></admin-dialog>
</v-app>
</template>

<script>
import alert from '@/components/alert.vue';
import AdminEdit from '@/components/AdminEdit.vue';
import AdminDialog from '@/components/AdminDialog.vue';
import axios from 'axios';

export default {
  name: 'admin',
  components: {
    alert,
    AdminEdit,
    AdminDialog,
  },
  data: () => ({
    colors: ['blue', 'indigo', 'cyan', 'green', 'orange', 'grey darken-1', 'pink', 'purple' ,'light-green' , 'brown' , 'blue-grey'],
    search_date: {},
    name: '全員',
    comment: '',
    calendar: '',
    calendar_date: '',
    calendar_type: 'month',
    calendar_type_item: [
      {text: '月', value:'month'},
      {text: '週', value:'week'}
    ],
    calendar_events: [],
    edit_show: false,
    edit_items: {},
    edit_date: '',
    alert_show: false,
    alert_type: '',
    alert_text: '',
    action: 0,
    text: '',
    dialog: false,
    create: false,
  }),
  created() {
    const data = [
    {
        "name": "1",
        "date": "2021-10-01",
        "comment": "1",
        "hour_salary": "",
        "day_salary": ""
    },
    {
        "name": "1",
        "date": "2021-10-02",
        "comment": "",
        "hour_salary": "",
        "day_salary": ""
    },
    {
        "name": "1",
        "date": "2021-10-05",
        "comment": "",
        "hour_salary": "",
        "day_salary": ""
    },
    {
        "name": "1",
        "date": "2021-10-06",
        "comment": "",
        "hour_salary": "",
        "day_salary": ""
    },
    {
        "name": "1",
        "date": "2021-10-07",
        "comment": "",
        "hour_salary": "",
        "day_salary": ""
    },
    {
        "name": "1",
        "date": "2021-10-08",
        "comment": "",
        "hour_salary": "",
        "day_salary": ""
    },
    {
        "name": "1",
        "date": "2021-10-09",
        "comment": "",
        "hour_salary": "",
        "day_salary": ""
    },
    {
        "name": "2",
        "date": "2021-10-08",
        "comment": "2",
        "hour_salary": "",
        "day_salary": ""
    },
    {
        "name": "2",
        "date": "2021-10-12",
        "comment": "",
        "hour_salary": "",
        "day_salary": ""
    },
    {
        "name": "2",
        "date": "2021-10-13",
        "comment": "",
        "hour_salary": "",
        "day_salary": ""
    },
    {
        "name": "2",
        "date": "2021-10-14",
        "comment": "",
        "hour_salary": "",
        "day_salary": ""
    },
    {
        "name": "2",
        "date": "2021-10-19",
        "comment": "",
        "hour_salary": "",
        "day_salary": ""
    },
    {
        "name": "2",
        "date": "2021-10-20",
        "comment": "",
        "hour_salary": "",
        "day_salary": ""
    },
    {
        "name": "2",
        "date": "2021-10-21",
        "comment": "",
        "hour_salary": "",
        "day_salary": ""
    },
    {
        "name": "2",
        "date": "2021-10-26",
        "comment": "",
        "hour_salary": "",
        "day_salary": ""
    },
    {
        "name": "2",
        "date": "2021-10-27",
        "comment": "",
        "hour_salary": "",
        "day_salary": ""
    },
    {
        "name": "3",
        "date": "2021-10-01",
        "comment": "3",
        "hour_salary": "",
        "day_salary": ""
    },
    {
        "name": "3",
        "date": "2021-10-02",
        "comment": "",
        "hour_salary": "",
        "day_salary": ""
    },
    {
        "name": "3",
        "date": "2021-10-09",
        "comment": "",
        "hour_salary": "",
        "day_salary": ""
    },
    {
        "name": "3",
        "date": "2021-10-22",
        "comment": "",
        "hour_salary": "",
        "day_salary": ""
    },
    {
        "name": "3",
        "date": "2021-10-29",
        "comment": "",
        "hour_salary": "",
        "day_salary": ""
    },
    {
        "name": "3",
        "date": "2021-10-30",
        "comment": "",
        "hour_salary": "",
        "day_salary": ""
    },
    {
        "name": "4",
        "date": "2021-10-11",
        "comment": "",
        "hour_salary": "",
        "day_salary": ""
    },
    {
        "name": "4",
        "date": "2021-10-12",
        "comment": "",
        "hour_salary": "",
        "day_salary": ""
    },
    {
        "name": "4",
        "date": "2021-10-18",
        "comment": "",
        "hour_salary": "",
        "day_salary": ""
    },
    {
        "name": "4",
        "date": "2021-10-24",
        "comment": "",
        "hour_salary": "",
        "day_salary": ""
    },
    {
        "name": "5",
        "date": "2021-10-03",
        "comment": "",
        "hour_salary": "",
        "day_salary": ""
    },
    {
        "name": "5",
        "date": "2021-10-04",
        "comment": "",
        "hour_salary": "",
        "day_salary": ""
    },
    {
        "name": "5",
        "date": "2021-10-05",
        "comment": "",
        "hour_salary": "",
        "day_salary": ""
    },
    {
        "name": "5",
        "date": "2021-10-06",
        "comment": "",
        "hour_salary": "",
        "day_salary": ""
    },
    {
        "name": "5",
        "date": "2021-10-07",
        "comment": "",
        "hour_salary": "",
        "day_salary": ""
    },
    {
        "name": "5",
        "date": "2021-10-08",
        "comment": "",
        "hour_salary": "",
        "day_salary": ""
    },
    {
        "name": "5",
        "date": "2021-10-09",
        "comment": "",
        "hour_salary": "",
        "day_salary": ""
    }
]
    this.$store.commit('set_calendar_events', data)
    this.fetch_data(data);


    const date = new Date();
    this.calendar_date = date.getMonth()+1+"月 "+date.getFullYear();
    // this.setToday();
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
    get_comment_items() {
      const data = this.$store.getters.calendar_events;
      const comment_items = data.map(element => element.comment);
      return ['', ...comment_items.filter((obj, index) => {
        return comment_items.indexOf(obj) === index;
      })];
    },
  },
  methods: {
    async fetch() {
      if (this.create) {
        this.save();
      }
      if (!this.create) {
        this.create = true;
      }
      this.search_date = {
        start_date: this.$refs.calendar.lastStart.date,
        end_date: this.$refs.calendar.lastEnd.date
      }
      this.get_data();
    },
    fetch_data(data) {
      var firstTimestamp = null;
      var startTime = null;
      var color = [];
      let name_items = data.map(element => element.name);
      name_items = [...name_items.filter((obj, index) => {
        return name_items.indexOf(obj) === index;
      })];
      this.calendar_events = [];
      name_items.map(obj => {
        color = this.colors[this.rnd(0, this.colors.length - 1)]
        data.map(element => {
          if (element.name == obj) {
            firstTimestamp = new Date(`${element.date}T09:00:00`)
            startTime = new Date(firstTimestamp)
            this.calendar_events.push({
              name: obj,
              date: element.date,
              start: startTime,
              comment: element.comment,
              hour_salary: element.hour_salary,
              day_salary: element.day_salary,
              color: color
            })
          }
        })
      })
    },
    async get_data() {
      const url = "/schedule/app/adminGetSchedule.php";
      const data = this.search_date;
      await axios.post(url, data).then(function(response) {
        if (response.data.status === 'success') {
          this.$store.commit('set_calendar_events', response.data.data)
          this.fetch_data(response.data.data);
        } else {
          this.calendar_events = [];
          this.$store.commit('set_calendar_events', []);
          this.alert(response.data.status, response.data.message, true);
        }
      }.bind(this))
    },
    search() {
      let data = this.$store.getters.calendar_events;
      const name = this.name;
      const comment = this.comment;
      if (name != '全員') {
        data = data.filter(obj => obj.name == name);
      }
      if (comment != '') {
        data = data.filter(obj => obj.comment == comment);
      }
      this.fetch_data(data)
    },
    click(action) {
      if (action == 1) {
        this.text = "登録しますか？"
      } else if (action == 2) {
        this.text = "削除しますか？"
      } else if (action == 3) {
        this.text = "更新しますか？"
      }
      this.action = action;
      this.dialog = true;
    },
    save() {
      const url = "/schedule/app/adminUploadSchedule.php";
      const data = {
        date: this.search_date,
        event: this.$store.getters.calendar_events
      }
      axios.post(url, data).then(function(response) {
        if (response.data.status == "success") {
          if (this.action != 0) {
            this.alert(response.data.status, response.data.message, true);
          }
        } else {
          this.alert(response.data.status, response.data.message, true);
        }
      }.bind(this))
    },
    remove() {
      let data = this.$store.getters.calendar_events;
      const name = this.name;
      if (name != '全員') {
        data = data.filter(obj => obj.name != name)
        this.$store.dispatch('setCalendarEvents', data)
        this.fetch_data(data)
      }
    },
    refresh() {
      this.create = false;
      this.calendar_type = 'month'
      this.name = '全員'
      this.comment = ''
    },
    clear() {
      this.name = '全員'
      this.comment = ''
      const data = this.$store.getters.calendar_events;
      this.fetch_data(data);
    },
    edit(item) {
      const lodash = require("lodash");
      const data = this.$store.getters.calendar_events;
      this.edit_items = lodash.cloneDeep(data.filter(obj => obj.date == item.date));
      this.edit_date = item.date
      if (this.edit_items.length != 0) {
        this.edit_show = true;
      }
    },
    accept_edit(item) {
      const data = this.$store.getters.calendar_events
      let event = data.filter(obj => obj.date != this.edit_date)
      event.push(...item)
      this.$store.commit('set_calendar_events', event);
      this.edit_show = false;
      this.search();
    },
    edit_close() {
      this.edit_show = false;
    },
    accept() {
      this.dialog = false;
      if (this.action == 1) {
        this.save(); 
      } else if (this.action == 2) {
        this.remove(); 
      } else if (this.action == 3) {
        this.refresh(); 
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
    get_event_color(event) {
      return event.color;
    },
    rnd (a, b) {
      return Math.floor((b - a + 1) * Math.random()) + a
    },
  }
}
</script>
