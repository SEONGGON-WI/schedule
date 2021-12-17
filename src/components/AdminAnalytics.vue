<template>
  <v-dialog content-class="custom_dialog" v-model="dialog" persistent scrollable> 
    <v-card color="grey lighten-4">
      <v-toolbar color="primary" dark>
          <v-toolbar-title class="mx-2">
            {{ date }}
          </v-toolbar-title>
          <v-spacer></v-spacer>
          <v-toolbar-title class="mx-3">
            {{ tab == 1 ? total_salary : admin_total_salary }}
          </v-toolbar-title>
          <v-toolbar-title class="mx-3">
            {{ total_agenda }}
          </v-toolbar-title>
          <v-btn class="success mx-2 botton_size" @click="check_dialog = true">
            <v-icon>cloud_upload</v-icon>登録
          </v-btn>
          <v-btn class="error mx-2 botton_size" @click="close">
            <v-icon>cancel</v-icon>キャンセル
          </v-btn>
        </v-toolbar>
      <v-card-text class="pa-0">
      <v-row no-gutters class="mb-3">
        <v-col cols="2" class="name_agenda pt-3 pl-8">
          <v-select 
            height="30"
            class="pt-0"
            v-model="differ_name" 
            :items="name_items"
            @change="fetch_data"
            append-icon="arrow_drop_down"
            hide-details
          ></v-select>
        </v-col>
        <v-col cols="3" class="name_agenda pt-3 pl-8">
          <v-select 
            height="30"
            class="pt-0"
            v-model="differ_agenda" 
            :items="agenda_items"
            @change="fetch_data"
            append-icon="arrow_drop_down"
            hide-details
          >
            <template v-slot:append-outer>
              <v-btn icon @click.native="clear">
                <v-icon>clear</v-icon>
              </v-btn>
            </template>
          </v-select>
        </v-col>
        <v-spacer></v-spacer>
        <v-col cols="3" class="name_agenda pt-3 pl-8">
          {{ tab == 1 ? pay_total_salary : '' }}
        </v-col>
        <v-col cols="4" class="name_agenda pt-3 pr-2">
          {{ tab == 1 ? paid_total_salary : '' }}
          {{ tab == 1 ? paid_status : '' }}
        </v-col>
      </v-row>
      <v-tabs v-model="tab" grow>
          <v-tab v-for="item in tab_item" :key="item">
            {{ item }}
          </v-tab>
        </v-tabs>
        <v-tabs-items v-model="tab">
          <v-data-table 
            v-if="tab == 0"
            :headers="header"
            :items="analytics_data"
            :item-class="admin_background"
            class="mt-3 fixed-header2"
            hide-default-footer 
            disable-pagination
            disable-sort
          >
            <template v-slot:item.date="{ item }">
              <div>{{ get_date(item.date) }}</div>
            </template>
            <template v-slot:item.admin_day_salary="{ item }">
              <div>{{ item.admin_day_salary == '' ? '' : String(Math.floor(item.admin_day_salary * item.overlap)) }}</div>
            </template>
            <template v-slot:item.admin_expense="{ item }">
              <div>{{ item.admin_expense == '' ? '' : String(Math.floor(item.admin_expense * item.overlap)) }}</div>
            </template>
          </v-data-table>
          <v-data-table 
            v-else
            :headers="headers"
            :items="analytics_data"
            :item-class="staff_background"
            class="mt-3 fixed-header2"
            hide-default-footer 
            disable-pagination
            disable-sort
          >
            <template v-slot:item.status="{ item }">
              <v-checkbox 
                class="checkbox mt-4 mb-4"
                hide-details
               :input-value="item.status == '1' ? true : false" @change="set_status(item)"
              ></v-checkbox>
            </template>
            <template v-slot:item.date="{ item }">
              <div>{{ get_date(item.date) }}</div>
            </template>
            <template v-slot:item.staff_day_salary="{ item }">
              <div>{{ item.staff_day_salary == '' ? '' : String(Math.floor(item.staff_day_salary * item.overlap)) }}</div>
            </template>
            <template v-slot:item.staff_expense="{ item }">
              <div>{{ item.staff_expense == '' ? '' : String(Math.floor(item.staff_expense * item.overlap)) }}</div>
            </template>
          </v-data-table>
        </v-tabs-items>
      </v-card-text>
    </v-card>
    <admin-dialog
      v-if="check_dialog"
      @accept="upload"
      @close="check_dialog = false"
      text="登録しますか？"
    ></admin-dialog>
    <alert
      v-if="alert_show"
      @close="alert_show = false"
      :text="alert_text"
    ></alert>
  </v-dialog>
</template>
<style lang="scss">
.checkbox {
    transform: scale(1.5);
    transform-origin: left;
}
</style>
<script>
import AdminDialog from '@/components/AdminDialog.vue';
import alert from './alert.vue';
import axios from 'axios';
export default {
  name: "adminanalytics",
  components: {
    AdminDialog,
    alert,
  },
  props: [
    'client', 'name', 'agenda', 'date'
  ],
  data: () => ({
    header: [
      { value:"date", text:"日付", width: "10%", align: 'center'},
      { value:"client", text:"顧客", width: "10%", align: 'start'},
      { value:"agenda", text:"案件", width: "15%", align: 'start'},
      { value:"name", text:"名前", width: "15%", align: 'start'},
      { value:"overlap", text:"人数", width: "10%", align: 'center'},
      { value:"admin_total_time", text:"時間", width: "10%", align: 'center'},
      { value:"admin_hour_salary", text:"時給", width: "10%", align: 'center'},
      { value:"admin_day_salary", text:"合計", width: "10%", align: 'center'},
      { value:"admin_expense", text:"経費", width: "10%", align: 'center'},
    ],
    headers: [
      { value:"status", text:"", width: "5%", align: 'center'},
      { value:"date", text:"日付", width: "8%", align: 'center'},
      { value:"agenda", text:"案件", width: "14%", align: 'start'},
      { value:"name", text:"名前", width: "14%", align: 'start'},
      { value:"start_time", text:"出勤", width: "10%", align: 'center'},
      { value:"end_time", text:"退勤", width: "10%", align: 'center'},
      { value:"total_time", text:"時間", width: "10%", align: 'center'},
      { value:"staff_hour_salary", text:"時給", width: "10%", align: 'center'},
      { value:"staff_day_salary", text:"合計", width: "10%", align: 'center'},
      { value:"staff_expense", text:"経費", width: "9%", align: 'center'}
    ],
    tab: null,
    tab_item: ['管理者', 'スタッフ'],
    data: [],
    analytics_data: [],
    total_agenda: 0,
    differ_name: '',
    differ_agenda: '',
    alert_text: '',
    alert_show: false,
    check_dialog: false,
    dialog: false,
    root_folder: '',
    pay_total_salary: '',
    paid_total_salary: '',
    paid_status: '',
    total_salary: '',
    admin_total_salary: '',
    upload_condition: false,
  }),
  created() {
    this.root_folder = this.$store.getters.root_folder
    this.data = JSON.parse(JSON.stringify(this.$store.getters.calendar_events))
    if (this.client != '') {
      this.data = this.data.filter(obj => obj.client == this.client);
    }
    let data = this.data.filter(obj => obj.agenda != '' && (obj.staff_day_salary != '' || obj.admin_day_salary != ''));
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
    this.agenda_items = ['', ...agenda_items_set.sort(function (a, b) {
      return a.localeCompare(b, 'ja')
    })]
    if (this.agenda != '' && this.agenda != '空きスケジュール' && this.agenda != 'スタッフ日給未入力' && this.agenda != '管理者日給未入力') {
      this.differ_agenda = this.agenda
    } else {
      this.differ_agenda = ''
    }
    this.differ_name = this.name

    this.fetch_data()
    this.get_salary(this.items)
    this.get_pay_salary(this.items)
    this.dialog = true;
  },
  // watch: {
  //   analytics_data(element) {
  //     this.get_salary(element)
  //     this.get_pay_salary(element)
  //   },
  // },
  methods: {
    get_salary(){
      var total_salary = 0
      var admin_total_salary = 0
      this.analytics_data.map(item => {
        var expense = item.staff_expense ? parseInt(item.staff_expense) : 0
        if (item.staff_day_salary != '') {
          total_salary = total_salary + parseInt(Math.floor(item.staff_day_salary * item.overlap)) + parseInt(Math.floor(expense * item.overlap))
        }
        if (item.admin_day_salary != '') {
          admin_total_salary = admin_total_salary + parseInt(Math.floor(item.admin_day_salary * item.overlap)) + parseInt(Math.floor(expense * item.overlap))
        }
      })
      this.total_salary = "￥" + total_salary.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
      this.admin_total_salary = "￥" + admin_total_salary.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    },
    get_pay_salary() {
      var pay_total_salary = 0
      var paid_total_salary = 0
      var paid_status = 0
      this.analytics_data.map(item => {
        if (item.staff_day_salary != '') {
          var expense = item.staff_expense ? parseInt(item.staff_expense) : 0
          if (item.status == 0) {
            pay_total_salary = pay_total_salary + parseInt(Math.floor(item.staff_day_salary * item.overlap)) + parseInt(Math.floor(expense * item.overlap))  
          } else {
            paid_total_salary = paid_total_salary + parseInt(Math.floor(item.staff_day_salary * item.overlap)) + parseInt(Math.floor(expense * item.overlap))
            paid_status = paid_status + (1000 * parseInt(item.overlap))
          }
        }
      })
      this.pay_total_salary = "￥" + pay_total_salary.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
      this.paid_total_salary = "￥" + paid_total_salary.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
      this.paid_status = " - " + paid_status.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    },
    get_date(date) {
      const day = date.split("-")
      if (day[2] < 10) {
        return day[2][1]
      } else {
        return day[2]
      }
    },
    fetch_data() {
      this.analytics_data = JSON.parse(JSON.stringify(this.data))
      this.analytics_data = this.analytics_data.filter(obj => obj.agenda != '' && (obj.staff_day_salary != '' || obj.admin_day_salary != ''));
      if (this.differ_agenda != '') {
          this.analytics_data = this.analytics_data.filter(obj => obj.agenda == this.differ_agenda);
      }
      if (this.differ_name != '全員') {
        this.analytics_data = this.analytics_data.filter(obj => obj.name == this.differ_name);
      }
      const sort_list = [
        {key:'date', type: 1},
        {key:'agenda', type: 1},
        {key:'admin_day_salary', type: 2},
        {key:'staff_day_salary', type: 2},
      ]
      this.analytics_data.sort(this.sort_by(sort_list))
      this.total_agenda = this.analytics_data.length + "件"
      this.get_salary()
      this.get_pay_salary()
    },
    upload() {
      const url = this.root_folder + "/app/adminEditAnalytics.php";
      let data = []
      this.items.map((element, index) => {
        data[index] = {
          status: element.status,
          name: element.name,
          date: element.date
        }
      })
      axios.post(url, {event:data}).then(function(response) {
        if (response.data.status == true) {
          this.upload_condition = true
        } else {
          this.upload_condition = false
          this.alert(response.data.message);
        }
      }.bind(this))
    },
    clear() {
      this.differ_name = '全員'
      this.differ_agenda = ''
      this.fetch_data()
    },
    alert(text) {
      this.alert_text = text;
      this.alert_show = true;
    },
    close() {
      this.dialog = false;
      this.$emit("close", this.upload_condition);
    },
    set_status(item) {
      item.status = item.status == '1' ? '0' : '1'
      this.get_pay_salary(this.analytics_data)
    },
    admin_background(item) {
      return item.admin_day_salary == '' ? 'empty_salary' : 'filled_salary' ;
    },
    staff_background(item) {
      if (item.status == 1) {
        return 'paid_schedule'
      } else if (item.staff_day_salary == '') {
        return 'empty_salary'
      } else {
        return 'filled_salary'
      }
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