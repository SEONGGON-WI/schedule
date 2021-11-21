<template>
  <v-dialog content-class="custom_dialog" v-model="dialog" :max-width="$vuetify.breakpoint.mobile ? '100%' : '80%'" persistent scrollable> 
    <v-card color="grey lighten-4">
      <v-toolbar color="primary" dark>
          <v-toolbar-title class="mx-2 title_text">
            {{ date }}
          </v-toolbar-title>
          <v-spacer></v-spacer>
          <v-toolbar-title class="mx-3">
            {{ total_agenda }}
          </v-toolbar-title>
          <v-btn class="success mx-2 botton_size" @click="upload">
            <v-icon>cloud_upload</v-icon>登録
          </v-btn>
          <v-btn class="error mx-2 botton_size" @click="close">
            <v-icon>cancel</v-icon>キャンセル
          </v-btn>
        </v-toolbar>
      <v-card-text class="pa-0">
      <v-row no-gutters class="mb-3">
        <v-col cols="4" class="name_agenda pt-3 pl-8">
          <v-select 
            height="30"
            class="pt-0"
            v-model="differ_name" 
            :items="name_items"
            @change="change"
            append-icon="arrow_drop_down"
            hide-details
          ></v-select>
        </v-col>
        <v-col cols="4" class="name_agenda pt-3 pl-8">
          <v-select 
            height="30"
            class="pt-0"
            v-model="differ_agenda" 
            :items="agenda_items"
            @change="change"
            append-icon="arrow_drop_down"
            hide-details
          ></v-select>
        </v-col>
        <v-col cols="4" class="name_agenda pt-3 pl-8">
          {{ tab == 0 ? get_admin_total_salary : get_total_salary }}
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
            :items="items"
            class="mt-3 fixed-header2"
            hide-default-footer 
            disable-pagination
            disable-sort
          >
            <template v-slot:item.date="{ item }">
              <div>{{ get_date(item.date) }}</div>
            </template>
          </v-data-table>
          <v-data-table 
            v-else
            :headers="headers"
            :items="items"
            :item-class="paid_background"
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
          </v-data-table>
        </v-tabs-items>
      </v-card-text>
    </v-card>
    <alert
      @close="alert_show = false"
      :text="alert_text"
      v-if="alert_show"
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
import alert from './alert.vue';
import axios from 'axios';
export default {
  name: "adminanalytics",
  components: {
    alert,
  },
  props: [
    'items', 'name_items', 'name', 'agenda_items', 'agenda', 'date'
  ],
  data: () => ({
    header: [
      { value:"date", text:"日付", width: "10%", align: 'center'},
      { value:"client", text:"顧客", width: "15%", align: 'start'},
      { value:"agenda", text:"案件", width: "20%", align: 'start'},
      { value:"name", text:"名前", width: "15%", align: 'start'},
      { value:"admin_total_time", text:"時給", width: "10%", align: 'center'},
      { value:"admin_hour_salary", text:"時給", width: "10%", align: 'center'},
      { value:"admin_day_salary", text:"日給", width: "10%", align: 'center'},
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
      { value:"staff_day_salary", text:"日給", width: "10%", align: 'center'},
      { value:"staff_expense", text:"経費", width: "9%", align: 'center'}
    ],
    tab: null,
    tab_item: ['管理者', 'スタッフ'],
    background_date: [],
    total_agenda: 0,
    differ_name: '',
    differ_agenda: '',
    alert_text: '',
    alert_show: false,
    dialog: false,
  }),
  created() {
    this.total_agenda = this.items.length + "件"
    this.differ_name = this.name
    this.differ_agenda = this.agenda
    this.dialog = true;
  },
  watch: {
    items(element) {
      this.total_agenda = element.length + "件"
    },
  },
  computed: {
    get_total_salary() {
      const salary = this.items.reduce((stack, obj) => {
        if (obj.staff_day_salary != '' && obj.status == 0) {
          var expense = obj.staff_expense ? parseInt(obj.staff_expense) : 0
          return stack + parseInt(obj.staff_day_salary) + expense
        } else {
          return stack
        }
      }, 0)
      return "￥" + salary.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    },
    get_admin_total_salary() {
      const salary = this.items.reduce((stack, obj) => {
        if (obj.admin_day_salary != '') {
          var expense = obj.admin_expense ? parseInt(obj.admin_expense) : 0
          return stack + parseInt(obj.admin_day_salary) + expense
        } else {
          return stack
        }
      }, 0)
      return "￥" + salary.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    },
  },
  methods: {
    set_status(item) {
      item.status = item.status == '1' ? '0' : '1'
    },
    paid_background(item) {
      return item.status == 1 ? 'paid_schedule' : 'not_piad_schedule' ;
    },
    get_date(date) {
      const day = date.split("-")
      if (day[2] < 10) {
        return day[2][1]
      } else {
        return day[2]
      }
    },
    change() {
      const condition = {
        name : this.differ_name,
        agenda: this.differ_agenda
      }
      this.$emit("change", condition);
    },
    upload() {
      const url = "/schedule/app/adminEditAnalytics.php";
      let data = []
      this.items.map(element => {
        data.push({
          status: element.status,
          name: element.name,
          date: element.date
        })
      })
      axios.post(url, {event:data}).then(function(response) {
        if (response.data.status == true) {
          this.$emit("reload");    
        } else {
          this.alert(response.data.message);
        }
      }.bind(this))
    },
    alert(text) {
      this.alert_text = text;
      this.alert_show = true;
    },
    close() {
      this.dialog = false;
      this.$emit("close");
    },
  },
}
</script>