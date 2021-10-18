<template>
<v-app id="inspire">
  <v-app-bar app color="primary" dark fixed>
    <v-toolbar-title class="mx-4">スケジュール</v-toolbar-title>
    <v-toolbar-title class="mx-3" v-if="$refs.calendar">
      {{ $refs.calendar.title }}
    </v-toolbar-title>
    <v-toolbar-title class="mx-3" v-else>
      {{ calendar_date }}
    </v-toolbar-title>
    <v-spacer></v-spacer>
    <v-btn class="error mx-2" color="white" @click="click(1)">
      <v-icon>delete</v-icon>削除
    </v-btn>
    <v-btn class="success mx-2" color="white" @click="click(2)">
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

          <v-sheet height="95%" class="mx-3">
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
                <div class="mt-1 ml-1" v-if="event.agenda == ''">
                  {{ event.name }}
                </div>
                <div class="mt-1 ml-1" v-else>
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
<style lang="scss">
.v-event {
  width: 99% !important;
  left: 0.5% !important;
  height: 25% !important;
  top: 1% !important;
}
.v-event-more{
  width: 90% !important;
  left: 5% !important;
  height: 20px !important;
  top: 1% !important;
}
</style>
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
    colors: ['grey darken-2','orange'],
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
    edit_items: {},
    edit_date: '',
    alert_show: false,
    alert_type: '',
    alert_text: '',
    action: 0,
    text: '',
    dialog: false,
  }),
  created() {
    const event = [
    {
        "name": "中西洋介",
        "date": "2021-10-01",
        "agenda": "渋谷ワクチン",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "岡宗一郎",
        "date": "2021-10-01",
        "agenda": "渋谷ワクチン",
        "start_time": "1100",
        "end_time": "2000",
        "total_time": "8",
        "staff_hour_salary": "1350",
        "staff_day_salary": "10800",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "槻澤尚英",
        "date": "2021-10-01",
        "agenda": "渋谷ワクチン",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "横張勝彦",
        "date": "2021-10-01",
        "agenda": "渋谷ワクチン",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "美和優輝",
        "date": "2021-10-01",
        "agenda": "DMM",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "田島 美彩",
        "date": "2021-10-01",
        "agenda": "アプリ",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "山中聖也",
        "date": "2021-10-02",
        "agenda": "アプリ登録",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "14964",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "岡宗一郎",
        "date": "2021-10-02",
        "agenda": "アプリ登録",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "13000",
        "staff_expense": "2340",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "槻澤尚英",
        "date": "2021-10-02",
        "agenda": "渋谷ワクチン",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": "23000"
    },
    {
        "name": "横張勝彦",
        "date": "2021-10-02",
        "agenda": "渋谷ワクチン",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "美和優輝",
        "date": "2021-10-02",
        "agenda": "渋谷ワクチン",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "田島 美彩",
        "date": "2021-10-02",
        "agenda": "アプリ",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "槻澤尚英",
        "date": "2021-10-03",
        "agenda": "渋谷ワクチン",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": "23000"
    },
    {
        "name": "田島 美彩",
        "date": "2021-10-03",
        "agenda": "アプリ",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "槻澤尚英",
        "date": "2021-10-04",
        "agenda": "",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "山中聖也",
        "date": "2021-10-05",
        "agenda": "渋谷ワクチン",
        "start_time": "1100",
        "end_time": "2000",
        "total_time": "8h",
        "staff_hour_salary": "1350",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": "NaN"
    },
    {
        "name": "岡宗一郎",
        "date": "2021-10-05",
        "agenda": "渋谷ワクチン",
        "start_time": "1100",
        "end_time": "1945",
        "total_time": "0745",
        "staff_hour_salary": "1350",
        "staff_day_salary": "1005750",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "槻澤尚英",
        "date": "2021-10-05",
        "agenda": "渋谷ワクチン",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": "23000"
    },
    {
        "name": "田島 美彩",
        "date": "2021-10-05",
        "agenda": "東銀座",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "山中聖也",
        "date": "2021-10-06",
        "agenda": "渋谷ワクチン",
        "start_time": "1100",
        "end_time": "1945",
        "total_time": "7.45h",
        "staff_hour_salary": "1350",
        "staff_day_salary": "NaN",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": "NaN"
    },
    {
        "name": "岡宗一郎",
        "date": "2021-10-06",
        "agenda": "渋谷ワクチン",
        "start_time": "1100",
        "end_time": "1945",
        "total_time": "0745",
        "staff_hour_salary": "1350",
        "staff_day_salary": "1005750",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "槻澤尚英",
        "date": "2021-10-06",
        "agenda": "渋谷ワクチン",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": "23000"
    },
    {
        "name": "美和優輝",
        "date": "2021-10-06",
        "agenda": "DMM",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "田島 美彩",
        "date": "2021-10-06",
        "agenda": "アプリ",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "岡宗一郎",
        "date": "2021-10-07",
        "agenda": "アプリ",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "10800",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "槻澤尚英",
        "date": "2021-10-07",
        "agenda": "渋谷ワクチン",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": "23000"
    },
    {
        "name": "横張勝彦",
        "date": "2021-10-07",
        "agenda": "チルアウト",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "美和優輝",
        "date": "2021-10-07",
        "agenda": "DMM",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "田島 美彩",
        "date": "2021-10-07",
        "agenda": "渋谷ワクチン",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "岡宗一郎",
        "date": "2021-10-08",
        "agenda": "渋谷ワクチン",
        "start_time": "1000",
        "end_time": "1700",
        "total_time": "6",
        "staff_hour_salary": "1350",
        "staff_day_salary": "8100",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "槻澤尚英",
        "date": "2021-10-08",
        "agenda": "渋谷ワクチン 未定",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "横張勝彦",
        "date": "2021-10-08",
        "agenda": "チルアウト",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "美和優輝",
        "date": "2021-10-08",
        "agenda": "東銀座",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "田島 美彩",
        "date": "2021-10-08",
        "agenda": "DMM",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "岡宗一郎",
        "date": "2021-10-09",
        "agenda": "アプリ",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "10800",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "槻澤尚英",
        "date": "2021-10-09",
        "agenda": "fu",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": "20000"
    },
    {
        "name": "山中聖也",
        "date": "2021-10-10",
        "agenda": "アプリ",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "12000",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "槻澤尚英",
        "date": "2021-10-10",
        "agenda": "fu",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": "23000"
    },
    {
        "name": "横張勝彦",
        "date": "2021-10-10",
        "agenda": "チルアウト",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "田島 美彩",
        "date": "2021-10-10",
        "agenda": "リクナビ",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "西村澪",
        "date": "2021-10-11",
        "agenda": "東銀座",
        "start_time": "0930",
        "end_time": "1845",
        "total_time": "8.25",
        "staff_hour_salary": "1300",
        "staff_day_salary": "10725",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": "0"
    },
    {
        "name": "中西洋介",
        "date": "2021-10-11",
        "agenda": "東洋ワクチン",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "山中聖也",
        "date": "2021-10-11",
        "agenda": "東洋ワクチン",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "12000",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "岡宗一郎",
        "date": "2021-10-11",
        "agenda": "東洋ワクチン",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "12000",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "槻澤尚英",
        "date": "2021-10-11",
        "agenda": "",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "横張勝彦",
        "date": "2021-10-11",
        "agenda": "DMM",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "美和優輝",
        "date": "2021-10-11",
        "agenda": "東洋ワクチン",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "田島 美彩",
        "date": "2021-10-11",
        "agenda": "",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "西村澪",
        "date": "2021-10-12",
        "agenda": "東銀座",
        "start_time": "0930",
        "end_time": "1845",
        "total_time": "8.25",
        "staff_hour_salary": "1300",
        "staff_day_salary": "10725",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": "0"
    },
    {
        "name": "山中聖也",
        "date": "2021-10-12",
        "agenda": "東洋ワクチン",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "12000",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "岡宗一郎",
        "date": "2021-10-12",
        "agenda": "東洋ワクチン",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "12000",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": "20000"
    },
    {
        "name": "槻澤尚英",
        "date": "2021-10-12",
        "agenda": "渋谷ワクチン 撤去",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": "23000"
    },
    {
        "name": "横張勝彦",
        "date": "2021-10-12",
        "agenda": "JOC",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "美和優輝",
        "date": "2021-10-12",
        "agenda": "東銀座",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "西村澪",
        "date": "2021-10-13",
        "agenda": "東銀座",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "山中聖也",
        "date": "2021-10-13",
        "agenda": "東洋ワクチン",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "12000",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "岡宗一郎",
        "date": "2021-10-13",
        "agenda": "東洋ワクチン",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "12000",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "槻澤尚英",
        "date": "2021-10-13",
        "agenda": "",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "横張勝彦",
        "date": "2021-10-13",
        "agenda": "東銀座",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "西村澪",
        "date": "2021-10-14",
        "agenda": "東銀座",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "山中聖也",
        "date": "2021-10-14",
        "agenda": "東洋ワクチン",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "12000",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "岡宗一郎",
        "date": "2021-10-14",
        "agenda": "東洋ワクチン",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "12000",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "西村澪",
        "date": "2021-10-15",
        "agenda": "東銀座",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "山中聖也",
        "date": "2021-10-15",
        "agenda": "東洋ワクチン",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "12000",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "岡宗一郎",
        "date": "2021-10-15",
        "agenda": "東洋ワクチン",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": "14000"
    },
    {
        "name": "槻澤尚英",
        "date": "2021-10-17",
        "agenda": "",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "山中聖也",
        "date": "2021-10-18",
        "agenda": "東洋ワクチン",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "12000",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "槻澤尚英",
        "date": "2021-10-18",
        "agenda": "",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "槻澤尚英",
        "date": "2021-10-19",
        "agenda": "",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "槻澤尚英",
        "date": "2021-10-20",
        "agenda": "チルアウト",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": "27000"
    },
    {
        "name": "槻澤尚英",
        "date": "2021-10-21",
        "agenda": "",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "槻澤尚英",
        "date": "2021-10-22",
        "agenda": "",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "槻澤尚英",
        "date": "2021-10-24",
        "agenda": "",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "槻澤尚英",
        "date": "2021-10-25",
        "agenda": "",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "槻澤尚英",
        "date": "2021-10-26",
        "agenda": "",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "槻澤尚英",
        "date": "2021-10-27",
        "agenda": "",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "槻澤尚英",
        "date": "2021-10-28",
        "agenda": "",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "山中聖也",
        "date": "2021-10-29",
        "agenda": "アプリ",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "12000",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "槻澤尚英",
        "date": "2021-10-29",
        "agenda": "",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": ""
    },
    {
        "name": "槻澤尚英",
        "date": "2021-10-30",
        "agenda": "チルアウト",
        "start_time": "",
        "end_time": "",
        "total_time": "",
        "staff_hour_salary": "",
        "staff_day_salary": "",
        "staff_expense": "",
        "admin_hour_salary": "",
        "admin_day_salary": "27000"
    }
]   
  this.$store.commit('set_calendar_events', event)
    this.fetch_data(event)

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
    get_agenda_items() {
      const data = this.$store.getters.calendar_events;
      const agenda_items = data.map(element => element.agenda);
      return ['', ...agenda_items.filter((obj, index) => {
        return agenda_items.indexOf(obj) === index;
      })];
    },
  },
  methods: {
    async fetch() {
      this.search_date = {
        start_date: this.$refs.calendar.lastStart.date,
        end_date: this.$refs.calendar.lastEnd.date
      }
      this.get_data();
    },
    fetch_data(data) {
      var firstTimestamp = null;
      var startTime = null;
      this.calendar_events = [];
      data.map(element => {
        firstTimestamp = new Date(`${element.date}T09:00:00`)
        startTime = new Date(firstTimestamp)
        this.calendar_events.push({
          name: element.name,
          date: element.date,
          agenda: element.agenda,
          start_time: element.start_time,
          end_time: element.end_time,
          total_time: element.total_time,
          staff_hour_salary: element.staff_hour_salary,
          staff_day_salary: element.staff_day_salary,
          staff_expense: element.staff_expense,
          admin_hour_salary: element.admin_hour_salary,
          admin_day_salary: element.admin_day_salary,
          start: startTime,
          color: element.agenda == ''? this.colors[0] : this.colors[1]
        })
      })
    },
    async get_data() {
      const url = "/schedule/app/adminGetSchedule.php";
      const data = this.search_date;
      await axios.post(url, data).then(function(response) {
        if (response.data.status) {
          if (response.data.status === 'success') {
            this.$store.commit('set_calendar_events', response.data.data)
            this.fetch_data(response.data.data);
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
    async save() {
      const url = "/schedule/app/adminUploadSchedule.php";
      const data = {
        start_date: this.search_date.start_date,
        end_date: this.search_date.end_date,
        event: this.$store.getters.calendar_events
      }
      await axios.post(url, data).then(function(response) {
        if (response.data.status){
          if (response.data.status == "success") {
            if (this.action != 0) {
              this.alert(response.data.status, response.data.message, true);
            }
          } else {
            this.alert(response.data.status, response.data.message, true);
          }
        }
      }.bind(this))
    },
    async remove() {
      const url = "/schedule/app/adminRemoveSchedule.php";
      const today = new Date();
      const current_date = today.getFullYear() +"-"+ (today.getMonth()+1) +"-"+ today.getDate();
      const data = {
        current_date: current_date
      }
      await axios.post(url, data).then(function(response) {
        if (response.data.status){
          this.alert(response.data.status, response.data.message, true);
        }
      }.bind(this))

      this.get_data();
    },
    refresh() {
      this.name = '全員'
      this.agenda = ''
      this.get_data();
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
    prev_edit(item) {
      const edit_data = this.$store.getters.calendar_events
      let event = edit_data.filter(obj => obj.date != this.edit_date)
      event.push(...item)
      this.$store.commit('set_calendar_events', event);

      var date = this.edit_date.split("-")
      var time = new Date(parseInt(date[0]), parseInt(date[1]) -1, parseInt(date[2]) -1)

      date = time.getFullYear() +"-"+ (time.getMonth()+1) +"-"
      if (parseInt(time.getDate()) < 10) {
        date = date + "0" + time.getDate()
      } else {
        date = date + time.getDate()
      }
      this.edit_date = date
      const lodash = require("lodash");
      const data = this.$store.getters.calendar_events;
      this.edit_items = lodash.cloneDeep(data.filter(obj => obj.date == date));
      this.search();
    },
    next_edit(item) {
      const edit_data = this.$store.getters.calendar_events
      let event = edit_data.filter(obj => obj.date != this.edit_date)
      event.push(...item)
      this.$store.commit('set_calendar_events', event);

      var date = this.edit_date.split("-")
      var time = new Date(parseInt(date[0]), parseInt(date[1]) -1, parseInt(date[2]) + 1)
      date = time.getFullYear() +"-"+ (time.getMonth()+1) +"-"

      if (parseInt(time.getDate()) < 10) {
        date = date + "0" + time.getDate()
      } else {
        date = date + time.getDate()
      }
      this.edit_date = date
      const lodash = require("lodash");
      const data = this.$store.getters.calendar_events;
      this.edit_items = lodash.cloneDeep(data.filter(obj => obj.date == date));
      this.search();
    },
    edit_close() {
      this.edit_show = false;
    },
    accept() {
      this.dialog = false;
      if (this.action == 1) {
        this.remove(); 
      } else if (this.action == 2) {
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
  }
}
</script>
