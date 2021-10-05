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
                  :items="get_name"
                  label="名前"
                  @change="search_schedule"
                ></v-select>
              </v-col>

              <v-col cols="2">
                <v-select 
                  class="mt-6"
                  v-model="comment" 
                  :items="get_comment"
                  label="案件"
                  @change="search_schedule"
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
              :events="events"
              :event-color="get_event_color"
              locale="ja-jp"
              event-more-text="..."
              interval-count=0
              @click:date="addEvent"
              @click:day="show_edit"
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

  <edit-schedule
    @remove="remove_items($event)"
    @edit_close="edit_close"
    v-if="edit_show"
    :date="edit_date"
    :items="edit_items"
  ></edit-schedule>

  <admin-dialog
    :text="check_text"
    :action="action"
    @cancel="cancel"
    @save_data="save_data"
    @restore_data="restore_data"
    v-if="dialog"
  ></admin-dialog>
</v-app>
</template>

<script>
import Alert from '@/components/alert.vue';
import EditSchedule from '@/components/EditSchedule.vue';
import AdminDialog from '@/components/AdminDialog.vue';
import axios from 'axios';

export default {
  name: 'Admin',
  components: {
    Alert,
    EditSchedule,
    AdminDialog,
  },
  data: () => ({
    item: [],
    events: [],
    name: '全員',
    name_items: ['全員'],
    comment: '',
    comment_items: [''],


    
    colors: ['blue', 'indigo', 'cyan', 'green', 'orange', 'grey darken-1', 'pink', 'purple' ,'light-green' , 'brown' , 'blue-grey'],

    edit_show: false,
    edit_date: '',
    edit_items: [],
    calendar: '',
    calendar_date: '',
    calendar_type: 'month',
    calendar_type_item: [
      {text: '月', value:'month'},
      {text: '週', value:'week'}
    ],
    alert_show: false,
    alert_type: '',
    alert_text: '',
  }),
  created() {
    var date = new Date();
    this.calendar_date = date.getMonth()+1+"月 "+date.getFullYear();
    this.setToday();
    this.get_schedule();
  },
  computed: {
    get_agenda() {
      return this.events.length + "件";
    },
    get_name() {
      let name_items = ['全員'];
      if(!this.item) {
        return;
      }
      this.item.map((e) => {
        name_items.push(e.name);
      });
      return name_items;
    },
    get_comment() {
      let comment_items = [''];
      if(!this.item) {
        return;
      }
      this.item.map((e) => {
        comment_items.push(e.comment);
      });
      return comment_items;
    },
  },
  methods: {
    fetch_data(data) {
      if (!data) {
        return;
      }
      let event = [];
      this.name_items = ['全員'];
      this.comment_items = [''];
      data.map((e, index) => {
        this.name_items[index+1] = e.name;
        var color = this.colors[this.rnd(0, this.colors.length - 1)];
        JSON.parse(e.data).map((item, jndex) => {
          this.comment_items[jndex+1] = item.comment;
          var timeStamp = new Date(`${item.date}T00:00:00`);
          var time = new Date(timeStamp);
          event.push({
            name: e.name,
            date: item.date,
            comment: item.comment,
            color: color,
            start: time
          });
        });
      });
      this.temporary_event = event;
      this.events = event;
    },
    async get_schedule() {
      const url = "/vue/app/test.php";
      let date = "12";
      const data = {
        'date': date
      };
      await axios.post(url, data)
        .then(function(response){
          console.log(response)
        }.bind(this))
    },

    search_schedule(key) {
      const url = "/vue/app/test.php";
      const data = {
        'key': key
      };
      axios.post(url, data)
        .then(function(response) {
          console.log(response);
        }.bind(this))
    },


    show_edit(data) {
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

    get_event_color(event) {
      return event.color;
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
    alert_close() {
      this.alert_show = false;
    },
  }
}
</script>
