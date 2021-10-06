<template>
<v-app id="inspire">
  <v-app-bar app color="primary" dark fixed>
    <v-toolbar-title class="ml-4">スケジュール</v-toolbar-title>
    <v-spacer></v-spacer>
    <v-btn class="error mx-2" color="white" @click="remove">
      <v-icon>delete</v-icon>削除
    </v-btn>

    <v-btn class="accent mx-2" @click="save">
      <v-icon>save</v-icon>登録
    </v-btn>

    <v-btn class="success ml-2 mr-1" color="white" @click="restore">
      <v-icon>settings_backup_restore</v-icon>復元
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
                  :items="name_items"
                  label="名前"
                  @change="search"
                ></v-select>
              </v-col>

              <v-col cols="2">
                <v-select 
                  class="mt-6"
                  v-model="comment" 
                  :items="comment_items"
                  label="案件"
                  @change="search"
                ></v-select>
              </v-col>

              <v-btn color="success" @click.native="clear" class="mx-2">
                <v-icon>refresh</v-icon>クリア
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
              @click:date="get_data"
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

  <!-- <admin-edit
    @edit="edit($event)"
    @close="edit_close"
    v-if="edit_show"
    :date="edit_date"
    :items="edit_items"
  ></admin-edit>

  <admin-dialog
    @save_data="save_data"
    @restore_data="restore_data"
    @cancel="cancel"
    v-if="dialog"
    :text="check_text"
    :action="action"
  ></admin-dialog> -->
</v-app>
</template>

<script>
import alert from '@/components/alert.vue';
// import AdminEdit from '@/components/AdminEdit.vue';
// import AdminDialog from '@/components/AdminDialog.vue';
import axios from 'axios';

export default {
  name: 'admin',
  components: {
    alert,
    // AdminEdit,
    // AdminDialog,
  },
  data: () => ({
    item: [],  
    colors: ['blue', 'indigo', 'cyan', 'green', 'orange', 'grey darken-1', 'pink', 'purple' ,'light-green' , 'brown' , 'blue-grey'],
    search_date: {},

    name: '全員',
    name_items: ['全員'],
    comment: '',
    comment_items: [''],
    calendar: '',
    calendar_date: '',
    calendar_type: 'month',
    calendar_type_item: [
      {text: '月', value:'month'},
      {text: '週', value:'week'}
    ],
    calendar_events: [],
    calendar_events_temp: [],
    edit_show: false,
    edit_date: '',
    edit_items: {},
    edit_index: null,
    alert_show: false,
    alert_type: '',
    alert_text: '',
    dialog: false,
  }),
  created() {
    var date = new Date();
    this.calendar_date = date.getMonth()+1+"月 "+date.getFullYear();

    const time = date.getFullYear()+"-"+(date.getMonth()+1);
    this.search_date = {
      start_date: time +"-01",
      end_date: time +"-31"
    }
    this.setToday();
    this.get_data();
  },
  computed: {
    get_agenda() {
      return this.calendar_events.length + "件";
    },
  },
  methods: {
    fetch() {
      this.search_date = {
        start_time: this.$refs.calendar.lastStart.date,
        end_time: this.$refs.calendar.lastEnd.date
      }
    },
    fetch_data(data) {
      var firstTimestamp = null;
      var startTime = null;
      this.calendar_events = [];
 
      this.name_items = ['全員', ...(data.map(e => e.name).filter((obj, index) => {
        return (data.map(e => e.name)).indexOf(obj) === index;
      }))];
      this.comment_items = ['', ...(data.map(e => e.comment).filter((obj, index) => {
        return (data.map(e => e.comment)).indexOf(obj) === index;
      }))];
      this.name_items.map(obj => {
        var color = this.colors[this.rnd(0, this.colors.length - 1)]
        data.map(element => {
          if (element.name == obj) {
            firstTimestamp = new Date(`${element.date}T09:00:00`)
            startTime = new Date(firstTimestamp)
            this.calendar_events.push({
              name: obj,
              date: element.date,
              start: startTime,
              comment: element.comment,
              start_time: element.start_time,
              end_time: element.end_time,
              hour_price: element.hour_price,
              day_price: element.day_price,
              color: color
            })
          }
        })
      })
      console.log(this.calendar_events);
    },
    async get_data() {
      const url = "/schedule/app/adminGetSchedule.php";
      const data = this.search_date;
      await axios.post(url, data).then(function(response) {
        if (response.data.status === 'success') {
          this.fetch_data(response.data.data);
        } else {
          this.calendar_events = [];
          this.alert(response.data.status, response.data.message, true);
        }
      }.bind(this))
    },

    search() {
      console.log(this.calendar_events);
    },




    
    remove() {
      console.log("remove")
    },
    save() {
      console.log("save")
    },
    restore() {
      console.log("restore")
    },
    clear() {
      this.name = '全員';
      this.comment = '';
      this.get_schedule();
    },


    edit(data) {
      let item = [];
      this.edit_date = data.year +"年 "+ data.month +"月 "+ data.day +"日";
      this.events.map((e) => {
        if (e.date == data.date) {
          item.push(e);
        }
      });
      this.items = item;
      if (item.length != 0) {
        this.edit_show = true;
      }
    },
    remove_items(item) {
      this.edit = false;
      let event = [];
      this.item.map((e) => {
        if (e.name != item.name || e.date != item.date) {
          event.push(e);
        }
      });
      this.temporary_event = event;
 
      event = [];
      this.events.map((e) => {
        if (e.name != item.name || e.date != item.date) {
          event.push(e);
        }
      });
      this.events = event;
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
