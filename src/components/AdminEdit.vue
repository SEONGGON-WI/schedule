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
          <v-btn class="success mx-2 botton_size" @click="accept">
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
                  class="py-2"
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
    'client', 'name', 'agenda', 'edit_date'
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
    items: [],
    date: '',
    remove_target: {name:''},
    remove_item: [],
    remove_dialog: false,
    tab: null,
    tab_item: ['管理者', 'スタッフ'],
    valid: false,
    rules:{
      required: v => ((v.length == 4 || v.length == 5) && v > 0  && v < 2400) || v == '' || '時間入力',
      positive: v => v > 0 || v == '' || '正の整数を指定'
    },
    alert_text: '',
    alert_show: false,
    edit_condition: false,
    dialog: false,
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
    fetch_data() {
      if (this.items == []) {
        return
      }
      if (this.client != '') {
        this.items = this.items.filter(obj => obj.client == this.client);
      }
      if (this.name != '全員') {
        this.items = this.items.filter(obj => obj.name == this.name);
      }
      if (this.agenda != '') {
        if (this.agenda == '空きスケジュール') {
          this.items = this.items.filter(obj => obj.agenda == '');
        } else if (this.agenda == 'スタッフ日給未入力') {
          this.items = this.items.filter(obj => obj.agenda != '' && obj.staff_day_salary == '');
        } else if (this.agenda == '管理者日給未入力') {
          this.items = this.items.filter(obj => obj.agenda != '' && obj.admin_day_salary == '');
        } else {
          this.items = this.items.filter(obj => obj.agenda == this.agenda);
        }
      }
    },
    async get_event() {
      const url = this.root_folder + "/app/adminGetEditSchedule.php";
      const data = {
        client: this.client,
        name: this.name,
        agenda: this.agenda,
        date: this.date
      }
      await axios.post(url, data).then(function(response) {
        if (response.data.status) {
          if (response.data.status == true && response.data.data != '') {
            this.items = response.data.data
          } else {
            this.items = []
            if (response.data.status == false) {
              this.alert(response.data.message);
            }
          }
        }
        this.fetch_data()
      }.bind(this))
    },
    async edit() {
      let event = JSON.parse(JSON.stringify(this.items))
      const client = this.$store.getters.client_agenda
      event.map(element => {
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
        event: event,
        remove_event: this.remove_item
      }
      await axios.post(url, data).then(function(response) {
        if (response.data.status == false){
          this.edit_condition = false
          this.alert(response.data.message)
        } else {
          this.edit_condition = true
        }
        this.remove_item = [];
        this.close()
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
    prevDate() {
      this.date = this.calculate_date('prev')
      this.edit();
      this.get_event()
    },
    nextDate() {
      this.date = this.calculate_date('next')
      this.edit();
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
      this.remove_item.push({name: this.remove_target.name})
      const index = this.items.findIndex(obj => obj.name == this.remove_target.name);
      this.items.splice(index, 1);
      this.remove_dialog = false
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
  },
}
</script>