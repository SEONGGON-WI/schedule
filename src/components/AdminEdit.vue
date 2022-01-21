<template>
  <v-dialog content-class="custom_dialog" v-model="dialog" persistent scrollable>
    <v-container class="pa-0" fluid>
      <v-card color="grey lighten-4">
        <v-toolbar color="primary" dark>
          <v-toolbar-title class="mx-2">
            {{ date }}
          </v-toolbar-title>
          <v-btn icon text class="mx-2 pl-2" color="black darken-2" @click="prevDate"><v-icon>arrow_back_ios</v-icon></v-btn>
          <v-btn icon text class="mx-2 pl-2" color="black darken-2" @click="nextDate"><v-icon>arrow_forward_ios</v-icon></v-btn>
          <v-spacer></v-spacer>
          <v-btn class="mx-2 botton_size" color="yellow darken-4" @click="add_schedule">
            <v-icon>add</v-icon>登録
          </v-btn>
          <v-btn class="success mx-2 botton_size" @click="accept" :disabled="!valid">
            <v-icon>save</v-icon>保存
          </v-btn>
          <v-btn class="error mx-2 botton_size" @click="close">
            <v-icon>cancel</v-icon>キャンセル
          </v-btn>
        </v-toolbar>

        <v-tabs v-model="tab" grow>
          <v-tab v-for="item in tab_item" :key="item">
            {{ item }}
          </v-tab>
        </v-tabs>
        <v-tabs-items v-model="tab">
          <v-form ref="form" v-model="valid">
            <v-data-table 
              v-if="tab == 0"
              :headers="headers" 
              :items="items" 
              :item-class="admin_background"
              hide-default-footer 
              disable-pagination
              disable-sort
            >
              <template v-slot:item.agenda="{ item }">
                <v-textarea
                  v-model="item.agenda"
                  :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"
                  class="py-2 mt-0"
                  label="案件"
                  single-line
                  hide-details
                  rows="1"
                  no-resize
                  auto-grow
                ></v-textarea>
              </template>
              <template v-slot:item.overlap="{ item }">
                <v-text-field
                  v-model="item.overlap"
                  :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"
                  :rules="[rules.positive]"
                  class="py-3 my-4"
                  label=""
                  single-line
                  hide-details
                ></v-text-field>
              </template>
              <template v-slot:item.admin_total_time="{ item }">
                <v-text-field
                  v-model="item.admin_total_time"
                  :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"
                  :rules="[rules.positive]"
                  class="py-3 my-4"
                  label="時間"
                  single-line
                  hide-details
                ></v-text-field>
              </template>
              <template v-slot:item.admin_hour_salary="{ item }">
                <v-text-field
                  v-model="item.admin_hour_salary"
                  :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"
                  :rules="[rules.positive]"
                  :dense="item.admin_total_time == ''"
                  :filled="item.admin_total_time == ''"
                  class="py-3 my-4"
                  label="時給"
                  single-line
                  hide-details
                ></v-text-field>
              </template>
              <template v-slot:item.admin_day_salary="{ item }">
                <v-text-field
                  v-if="item.admin_total_time != ''"
                  :value="item.admin_day_salary = get_admin_day_salary(item)"
                  dense
                  filled
                  readonly
                  class="py-3 my-4"
                  label="日給"
                  single-line
                  hide-details
                ></v-text-field>
                <v-text-field
                  v-else
                  v-model="item.admin_day_salary"
                  :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"
                  :rules="[rules.positive]"
                  class="py-3 my-4"
                  label="日給"
                  single-line
                  hide-details
                ></v-text-field>
              </template>
              <template v-slot:item.admin_expense="{ item }">
                <v-text-field
                  v-model="item.admin_expense"
                  :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"
                  :rules="[rules.positive]"
                  class="py-3 my-4"
                  label="経費"
                  single-line
                  hide-details
                ></v-text-field>
              </template>
              <template v-slot:item.action="{ item }">
                <v-btn class="error" icon color="white" @click="remove_check(item)">
                  <v-icon>delete</v-icon>
                </v-btn>
              </template>
            </v-data-table>

            <v-data-table 
              v-else
              :headers="header" 
              :items="items" 
              :item-class="staff_background"
              hide-default-footer 
              disable-pagination
              disable-sort
            >
              <template v-slot:item.start_time="{ item }">
                <v-text-field
                  v-model="item.start_time"
                  :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"
                  :rules="[rules.required]"
                  :dense="item.total_time == ''"
                  :filled="item.total_time == ''"
                  class="py-3 my-4"
                  label="出勤"
                  placeholder="0900"
                  maxlength="5"
                  single-line
                  hide-details
                ></v-text-field>
              </template>
              <template v-slot:item.end_time="{ item }">
                <v-text-field
                  v-model="item.end_time"
                  :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"
                  :rules="[rules.required]"
                  :dense="item.total_time == ''"
                  :filled="item.total_time == ''"
                  class="py-3 my-4"
                  label="退勤"
                  placeholder="1800"
                  maxlength="5"
                  single-line
                  hide-details
                ></v-text-field>
              </template>
              <template v-slot:item.total_time="{ item }">
                <v-text-field
                  v-model="item.total_time"
                  :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"
                  :rules="[rules.positive]"
                  class="py-3 my-4"
                  label="時間"
                  single-line
                  hide-details
                ></v-text-field>
              </template>
              <template v-slot:item.staff_hour_salary="{ item }">
                <v-text-field
                  v-model="item.staff_hour_salary"
                  :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"
                  :rules="[rules.positive]"
                  :dense="item.total_time == ''"
                  :filled="item.total_time == ''"
                  class="py-3 my-4"
                  label="時給"
                  single-line
                  hide-details
                ></v-text-field>
              </template>
              <template v-slot:item.staff_day_salary="{ item }">
                <v-text-field
                  v-if="item.total_time != ''"
                  :value="item.staff_day_salary = get_staff_day_salary(item)"
                  dense
                  filled
                  class="py-3 my-4"
                  label="日給"
                  single-line
                  hide-details
                ></v-text-field>
                <v-text-field
                  v-else
                  v-model="item.staff_day_salary"
                  :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"
                  :rules="[rules.positive]"
                  class="py-3 my-4"
                  label="日給"
                  single-line
                  hide-details
                ></v-text-field>
              </template>
              <template v-slot:item.staff_expense="{ item }">
                <v-text-field
                  v-model="item.staff_expense"
                  :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"
                  :rules="[rules.positive]"
                  class="py-3 my-4"
                  label="経費"
                  single-line
                  hide-details
                ></v-text-field>
              </template>
            </v-data-table>
          </v-form>
        </v-tabs-items>
      </v-card>
    </v-container>
    <v-dialog v-model="add_dialog" persistent width="95%">
      <v-container class="pa-0" fluid>
        <v-card color="grey lighten-4">
          <v-toolbar color="primary" dark>
            <v-toolbar-title class="px-2">
              スケジュール登録
            </v-toolbar-title>
            <v-spacer></v-spacer>
            <v-btn class="mx-2 botton_size" color="yellow darken-4" @click="add" :disabled="!add_valid || add_item.name == '' || add_item.agenda == ''">
              <v-icon>add</v-icon>登録
            </v-btn>
            <v-btn class="error mx-2 botton_size" @click="add_dialog = false">
              <v-icon>cancel</v-icon>キャンセル
            </v-btn>
          </v-toolbar>
            <v-row no-gutters class="mb-2">
              <v-col cols="12" class="name_agenda pt-3 pl-8">
                <div>{{ date }}</div>
              </v-col>
            </v-row>
          <v-tabs v-model="add_tab" grow>
          <v-tab v-for="item in tab_item" :key="item">
            {{ item }}
          </v-tab>
        </v-tabs>
        <v-tabs-items v-model="add_tab">
          <v-form ref="form" v-model="add_valid">
            <v-data-table 
              v-if="add_tab == 0"
              :headers="add_headers" 
              :items="[add_item]" 
              hide-default-footer 
              disable-pagination
              disable-sort
            >
              <template v-slot:item.name="{ item }">
                <v-autocomplete
                  v-model="item.name" 
                  class="mt-5"
                  :items="name_items"
                  :menu-props="{ maxHeight: '600' }"
                  label="名前"
                ></v-autocomplete>
              </template>
              <template v-slot:item.agenda="{ item }">
                <v-textarea
                  v-model="item.agenda"
                  :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"
                  class="py-2 mt-0"
                  label="案件"
                  single-line
                  hide-details
                  rows="1"
                  no-resize
                  auto-grow
                ></v-textarea>
              </template>
              <template v-slot:item.overlap="{ item }">
                <v-text-field
                  v-model="item.overlap"
                  :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"
                  :rules="[rules.positive]"
                  class="py-3 my-4"
                  label=""
                  single-line
                  hide-details
                ></v-text-field>
              </template>
              <template v-slot:item.admin_total_time="{ item }">
                <v-text-field
                  v-model="item.admin_total_time"
                  :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"
                  :rules="[rules.positive]"
                  class="py-3 my-4"
                  label="時間"
                  single-line
                  hide-details
                ></v-text-field>
              </template>
              <template v-slot:item.admin_hour_salary="{ item }">
                <v-text-field
                  v-model="item.admin_hour_salary"
                  :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"
                  :rules="[rules.positive]"
                  class="py-3 my-4"
                  label="時給"
                  single-line
                  hide-details
                ></v-text-field>
              </template>
              <template v-slot:item.admin_day_salary="{ item }">
                <v-text-field
                  v-model="item.admin_day_salary"
                  :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"
                  :rules="[rules.positive]"
                  class="py-3 my-4"
                  label="日給"
                  single-line
                  hide-details
                ></v-text-field>
              </template>
              <template v-slot:item.admin_expense="{ item }">
                <v-text-field
                  v-model="item.admin_expense"
                  :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"
                  :rules="[rules.positive]"
                  class="py-3 my-4"
                  label="経費"
                  single-line
                  hide-details
                ></v-text-field>
              </template>
            </v-data-table>

            <v-data-table 
              v-else
              :headers="add_header" 
              :items="[add_item]" 
              hide-default-footer 
              disable-pagination
              disable-sort
            >
              <template v-slot:item.start_time="{ item }">
                <v-text-field
                  v-model="item.start_time"
                  :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"
                  :rules="[rules.required]"
                  class="py-3 my-4"
                  label="出勤"
                  placeholder="0900"
                  maxlength="5"
                  single-line
                  hide-details
                ></v-text-field>
              </template>
              <template v-slot:item.end_time="{ item }">
                <v-text-field
                  v-model="item.end_time"
                  :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"
                  :rules="[rules.required]"
                  class="py-3 my-4"
                  label="退勤"
                  placeholder="1800"
                  maxlength="5"
                  single-line
                  hide-details
                ></v-text-field>
              </template>
              <template v-slot:item.total_time="{ item }">
                <v-text-field
                  v-model="item.total_time"
                  :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"
                  :rules="[rules.positive]"
                  class="py-3 my-4"
                  label="時間"
                  single-line
                  hide-details
                ></v-text-field>
              </template>
              <template v-slot:item.staff_hour_salary="{ item }">
                <v-text-field
                  v-model="item.staff_hour_salary"
                  :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"
                  :rules="[rules.positive]"
                  class="py-3 my-4"
                  label="時給"
                  single-line
                  hide-details
                ></v-text-field>
              </template>
              <template v-slot:item.staff_day_salary="{ item }">
                <v-text-field
                  v-model="item.staff_day_salary"
                  :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"
                  :rules="[rules.positive]"
                  class="py-3 my-4"
                  label="日給"
                  single-line
                  hide-details
                ></v-text-field>
              </template>
              <template v-slot:item.staff_expense="{ item }">
                <v-text-field
                  v-model="item.staff_expense"
                  :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"
                  :rules="[rules.positive]"
                  class="py-3 my-4"
                  label="経費"
                  single-line
                  hide-details
                ></v-text-field>
              </template>
            </v-data-table>
          </v-form>
        </v-tabs-items>
        </v-card>
      </v-container>
    </v-dialog>

    <alert
      v-if="alert_show"
      @close="alert_show = false"
      :text="alert_text"
    ></alert>
    <admin-dialog
      v-if="remove_dialog"
      @accept="remove"
      @close="remove_dialog = false"
      text="削除しますか？"
    ></admin-dialog>
  </v-dialog>
</template>
<script>
import AdminDialog from '@/components/AdminDialog.vue';
import axios from 'axios';
export default {
  name: "adminedit",
  components: {
    AdminDialog,
  },
  props: [
    'client', 'name', 'agenda', 'name_items', 'edit_date'
  ],
  data: () => ({
    headers: [
      { value:"name", text:"名前", width: "12%", align: 'start'},
      { value:"client", text:"顧客", width: "11%", align: 'start'},
      { value:"agenda", text:"案件", width: "20%", align: 'start'},
      { value:"overlap", text:"人", width: "5%", align: 'center'},
      { value:"admin_total_time", text:"時間", width: "12%", align: 'center'},
      { value:"admin_hour_salary", text:"時給", width: "12%", align: 'center'},
      { value:"admin_day_salary", text:"日給", width: "12%", align: 'center'},
      { value:"admin_expense", text:"経費", width: "11%", align: 'center'},
      { value:"action", text:"", width:"5%", align: 'center'}
    ],
    header: [
      { value:"name", text:"名前", width: "15%", align: 'start'},
      { value:"agenda", text:"案件", width: "20%", align: 'start'},
      { value:"start_time", text:"出勤", width: "11%", align: 'center'},
      { value:"end_time", text:"退勤", width: "11%", align: 'center'},
      { value:"total_time", text:"時間", width: "9%", align: 'center'},
      { value:"staff_hour_salary", text:"時給", width: "11%", align: 'center'},
      { value:"staff_day_salary", text:"日給", width: "12%", align: 'center'},
      { value:"staff_expense", text:"経費", width: "11%", align: 'center'}
    ],
    add_headers: [
      { value:"name", text:"名前", width: "18%", align: 'start'},
      { value:"agenda", text:"案件", width: "28%", align: 'start'},
      { value:"overlap", text:"人", width: "6%", align: 'center'},
      { value:"admin_total_time", text:"時間", width: "12%", align: 'center'},
      { value:"admin_hour_salary", text:"時給", width: "12%", align: 'center'},
      { value:"admin_day_salary", text:"日給", width: "12%", align: 'center'},
      { value:"admin_expense", text:"経費", width: "12%", align: 'center'},
    ],
    add_header: [
      { value:"start_time", text:"出勤", width: "16%", align: 'center'},
      { value:"end_time", text:"退勤", width: "16%", align: 'center'},
      { value:"total_time", text:"時間", width: "10%", align: 'center'},
      { value:"staff_hour_salary", text:"時給", width: "22%", align: 'center'},
      { value:"staff_day_salary", text:"日給", width: "22%", align: 'center'},
      { value:"staff_expense", text:"経費", width: "14%", align: 'center'}
    ],
    items: [],
    date: '',
    remove_target: {name:'', agenda:''},
    remove_item: [],
    remove_dialog: false,
    tab: null,
    tab_item: ['管理者', 'スタッフ'],
    valid: true,
    rules:{
      required: v => ((v.length == 4 || v.length == 5) && v > 0  && v < 2400) || v == '' || '時間入力',
      positive: v => v > 0 || v == '' || '正の整数を指定'
    },
    alert_text: '',
    alert_show: false,
    edit_condition: false,
    dialog: false,
    add_dialog: false,
    add_item: {
      name: '',
      date: '',
      client: '',
      agenda: '',
      overlap: 1,
      admin_total_time: '',
      admin_hour_salary: '',
      admin_day_salary: '',
      admin_expense: '',
      start_time: '',
      end_time: '',
      total_time: '',
      staff_hour_salary: '',
      staff_day_salary: '',
      staff_expense: ''
    },
    add_tab: null,
    add_valid: true,
    search: '',
    root_folder: '',
  }),
  created() {
    this.root_folder = this.$store.getters.root_folder
    this.date = this.edit_date
    this.get_event()
    this.dialog = true;
  },
  computed: {
  },
  methods: {
    get_admin_day_salary(item) {
      if (item.admin_hour_salary == '' || item.admin_total_time == '') {
        return ''
      }
      let salary = Math.floor(item.admin_hour_salary * item.admin_total_time)
      return String(salary)
    },
    get_staff_day_salary(item) {
      if (item.staff_hour_salary == '' || item.total_time == '') {
        return ''
      }
      let salary = Math.floor(item.staff_hour_salary * item.total_time)
      return String(salary)
    },
    async get_event() {
      const sort_list = [
        {key:'client', type: 1},
        {key:'agenda', type: 1},
        {key:'admin_day_salary', type: 1},
        {key:'staff_day_salary', type: 1},
      ]
      let data = JSON.parse(JSON.stringify(this.$store.getters.calendar_events))
      const empty_agenda = this.agenda.includes('空きスケジュール')
      const empty_staff = this.agenda.includes('スタッフ日給未入力')
      const empty_admin = this.agenda.includes('管理者日給未入力')
      const agenda = this.agenda.filter(element => element != '空きスケジュール' && element != 'スタッフ日給未入力' && element != '管理者日給未入力')
      var fetch_data = data.filter(element => (element.date == this.date)
                                                && (this.client.length === 0 ? true : this.client.includes(element.client)) 
                                                && (this.name.length === 0 ? true : this.name.includes(element.name)) 
                                                && (empty_agenda || empty_staff || empty_admin 
                                                    ? ((empty_agenda ? (element.agenda == '' ? true : false) : false) 
                                                      || (empty_staff ? (element.agenda != '' && element.staff_day_salary == '' ? true : false) : false) 
                                                      || (empty_admin ? (element.agenda != '' && element.admin_day_salary == '' ? true : false) : false)) 
                                                    : true) 
                                                && (agenda.length === 0 ? true : agenda.includes(element.agenda)))
      this.items = fetch_data.sort(this.sort_by(sort_list))
    },
    async edit() {
      const client = this.$store.getters.client_agenda
      this.items.map(element => {
        element.start_time = element.start_time == '' ? '' : this.make_colon(element.start_time)
        element.end_time = element.end_time == '' ? '' :  this.make_colon(element.end_time)
        element.admin_total_time = element.admin_total_time == '' ? '' : parseFloat(element.admin_total_time).toFixed(2)
        element.total_time = element.total_time == '' ? '' : parseFloat(element.total_time).toFixed(2)
        var find = client.find(obj => obj.agenda == element.agenda)
        if (find != undefined) {
          element.client = find.client
          element.admin_hour_salary = element.admin_hour_salary == '' ? find.hour_salary : element.admin_hour_salary
          element.admin_day_salary = element.admin_day_salary == '' ?  find.day_salary : element.admin_day_salary  
        }
      })
      const url = this.root_folder + "/app/adminEditSchedule.php";
      const data = {
        date: this.date,
        event: this.items,
        remove_event: this.remove_item
      }
      await axios.post(url, data).then(function(response) {
        if (response.data.status == false){
          this.edit_condition = false
          this.alert(response.data.message)
          if (this.remove_item.length !== 0) {
            this.items = this.items.concat(this.remove_item)
          }
        } else {
          this.edit_condition = true
          const empty_agenda = this.agenda.includes('空きスケジュール')
          const empty_staff = this.agenda.includes('スタッフ日給未入力')
          const empty_admin = this.agenda.includes('管理者日給未入力')
          const agenda = this.agenda.filter(element => element != '空きスケジュール' && element != 'スタッフ日給未入力' && element != '管理者日給未入力')
          var fetch_data = this.$store.getters.calendar_events.filter(element => !((element.date == this.date)
                                                                        && (this.client.length === 0 ? true : this.client.includes(element.client)) 
                                                                        && (this.name.length === 0 ? true : this.name.includes(element.name)) 
                                                                        && (empty_agenda || empty_staff || empty_admin 
                                                                            ? ((empty_agenda ? (element.agenda == '' ? true : false) : false) 
                                                                              || (empty_staff ? (element.agenda != '' && element.staff_day_salary == '' ? true : false) : false) 
                                                                              || (empty_admin ? (element.agenda != '' && element.admin_day_salary == '' ? true : false) : false)) 
                                                                            : true) 
                                                                        && (agenda.length === 0 ? true : agenda.includes(element.agenda))))
          if (this.items.length !== 0) {
            fetch_data = fetch_data.concat(this.items)
          }
          this.$store.commit('set_calendar_events', fetch_data)
        }
        this.remove_item = [];
      }.bind(this))
    },
    calculate_date(type) {
      var date = this.date.split("-")
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
    async prevDate() {
      await this.edit();
      this.date = this.calculate_date('prev')
      this.get_event()
    },
    async nextDate() {
      await this.edit();
      this.date = this.calculate_date('next')
      this.get_event()
    },
    async accept() {
      await this.edit();
      this.close()
    },
    remove_check(item) {
      this.remove_target = item
      this.remove_dialog = true
    },
    remove() {
      this.remove_item.push(this.remove_target)
      const index = this.items.findIndex(obj => obj.name === this.remove_target.name && obj.agenda === this.remove_target.agenda);
      this.items.splice(index, 1);
      this.remove_dialog = false
    },
    add_schedule() {
       this.add_item = {
        _id: '',
        name: '',
        date: this.date,
        client: '',
        agenda: '',
        overlap: 1,
        admin_total_time: '',
        admin_hour_salary: '',
        admin_day_salary: '',
        admin_expense: '',
        start_time: '',
        end_time: '',
        total_time: '',
        staff_hour_salary: '',
        staff_day_salary: '',
        staff_expense: ''
      }
      this.add_dialog = true
    },
    add() {
      const data = JSON.parse(JSON.stringify(this.add_item))
      this.items.push(data)
      this.add_dialog = false
    },
    alert(text) {
      this.alert_text = text;
      this.alert_show = true;
    },
    close() {
      this.dialog = false;
      this.$emit("close", this.edit_condition);
    },
    admin_background(item) {
      return item.agenda == '' || item.admin_day_salary == '' ? 'empty_salary' : 'filled_salary' ;
    },
    staff_background(item) {
      return item.agenda == '' || item.staff_day_salary == '' ? 'empty_salary' : 'filled_salary' ;
    },
    make_colon(time) {
      var hours = ''
      var minute = ''
      if (time.length == 4) {
        hours = time.substring(0, 2)
        minute = time.substring(2, 4)  
      } else if (time.length == 5) {
        hours = time.substring(0, 2)
        minute = time.substring(3, 5)  
      }
      if(isFinite(hours + minute) == false) {
        return false
      }
      if (hours > 23 ) {
        return false
      }
      if (minute > 59) {
        return false
      }
      return hours + ":" + minute;
    },
    sort_by: function(orderlist) {
        return (a, b) => {
          for (let i=0; i<orderlist.length; i++) {
            if (orderlist[i].type == 1) {
              if (a[orderlist[i].key] < b[orderlist[i].key]) return -1 ;
              if (a[orderlist[i].key] > b[orderlist[i].key]) return -1 * -1;
            } else {
              if (a[orderlist[i].key] < b[orderlist[i].key]) return 1 ;
              if (a[orderlist[i].key] > b[orderlist[i].key]) return 1 * -1;
            }
          }
          return 0;
        }
      // .sort(this.sort_by(this.sort_params))
    },
  },
}
</script>