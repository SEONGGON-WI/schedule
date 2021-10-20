<template>
  <v-dialog v-model="dialog" :max-width="$vuetify.breakpoint.mobile ? '100%' : '80%'" persistent>
    <v-container class="pa-0" fluid>
      <v-card color="grey lighten-4">
        <v-toolbar color="primary" dark>
          <v-toolbar-title class="mx-2 title_text">
            {{ date }}
          </v-toolbar-title>
          <v-btn fab text class="mx-2 pl-2" color="black darken-2" @click="prevDate"><v-icon>arrow_back_ios</v-icon></v-btn>
          <v-btn fab text class="mx-2 pl-2" color="black darken-2" @click="nextDate"><v-icon>arrow_forward_ios</v-icon></v-btn>
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
            <v-data-table 
              v-if="tab == 0"
              :headers="headers" 
              :items="items" 
              hide-default-footer 
              disable-pagination
              disable-sort
            >
              <template v-slot:item.agenda="{ item }">
                <v-textarea
                  v-model="item.agenda"
                  :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'" 
                  class="py-3"
                  label="案件"
                  single-line
                  hide-details
                  rows="1"
                  no-resize
                  auto-grow
                ></v-textarea>
              </template>
              <template v-slot:item.total_time="{ item }">
                <v-text-field
                  v-model="item.total_time"
                  :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'" 
                  class="py-3"
                  label="時間"
                  single-line
                  hide-details
                ></v-text-field>
              </template>
              <template v-slot:item.admin_hour_salary="{ item }">
                <v-text-field
                  v-model="item.admin_hour_salary"
                  :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"  
                  :dense="item.total_time == ''"
                  :filled="item.total_time == ''"
                  class="py-3"
                  label="時給"
                  single-line
                  hide-details
                ></v-text-field>
              </template>
              <template v-slot:item.admin_day_salary="{ item }">
                <v-text-field
                  v-if="item.total_time != ''"
                  :value="item.admin_day_salary = get_admin_day_salary(item)"
                  dense
                  filled
                  class="py-3"
                  label="日給"
                  single-line
                  hide-details
                ></v-text-field>
                <v-text-field
                  v-else
                  v-model="item.admin_day_salary"
                  :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"   
                  class="py-3"
                  label="日給"
                  single-line
                  hide-details
                ></v-text-field>
              </template>
              <template v-slot:item.action="{ item }">
                <v-btn class="error" icon color="white" @click="remove(item)">
                  <v-icon>delete</v-icon>
                </v-btn>
              </template>
            </v-data-table>

            <v-data-table 
              v-else
              :headers="header" 
              :items="items" 
              hide-default-footer 
              disable-pagination
              disable-sort
            >
              <template v-slot:item.start_time="{ item }">
                <v-text-field
                  v-model="item.start_time"
                  :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"  
                  :rules="rules"
                  :dense="item.total_time == ''"
                  :filled="item.total_time == ''"
                  class="py-3"
                  label="出勤"
                  placeholder="0900"
                  single-line
                  hide-details
                ></v-text-field>
              </template>
              <template v-slot:item.end_time="{ item }">
                <v-text-field
                  v-model="item.end_time"
                  :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"  
                  :rules="rules"
                  :dense="item.total_time == ''"
                  :filled="item.total_time == ''"
                  class="py-3"
                  label="退勤"
                  placeholder="1800"
                  single-line
                  hide-details
                ></v-text-field>
              </template>
              <template v-slot:item.total_time="{ item }">
                <v-text-field
                  v-model="item.total_time"
                  :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"  
                  class="py-3"
                  label="時間"
                  single-line
                  hide-details
                ></v-text-field>
              </template>
              <template v-slot:item.staff_hour_salary="{ item }">
                <v-text-field
                  v-model="item.staff_hour_salary"
                  :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"  
                  :dense="item.total_time == ''"
                  :filled="item.total_time == ''"
                  class="py-3"
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
                  class="py-3"
                  label="日給"
                  single-line
                  hide-details
                ></v-text-field>
                <v-text-field
                  v-else
                  v-model="item.staff_day_salary"
                  :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"  
                  class="py-3"
                  label="日給"
                  single-line
                  hide-details
                ></v-text-field>
              </template>
              <template v-slot:item.staff_expense="{ item }">
                <v-text-field
                  v-model="item.staff_expense"
                  :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"  
                  class="py-3"
                  label="経費"
                  single-line
                  hide-details
                ></v-text-field>
              </template>
            </v-data-table>
        </v-tabs-items>
      </v-card>
    </v-container>
  </v-dialog>
</template>
<style lang="scss">
.v-textarea .v-input__control .v-input__slot .v-text-field__slot textarea{
  font-size : 25px !important;
}
</style>
<script>
import axios from 'axios';
export default {
  name: "adminedit",
  components: {
  },
  props: [
    'items', 'date'
  ],
  data: () => ({
    headers: [
      { value:"name", text:"名前", width: "15%", align: 'start'},
      { value:"agenda", text:"案件", width: "30%", align: 'start'},
      { value:"total_time", text:"時間", width: "10%", align: 'center'},
      { value:"admin_hour_salary", text:"時給", width: "15%", align: 'center'},
      { value:"admin_day_salary", text:"日給", width: "20%", align: 'center'},
      { value:"action", text:"削除", width:"10%", align: 'center'}
    ],
    header: [
      { value:"name", text:"名前", width: "14%", align: 'start'},
      { value:"agenda", text:"案件", width: "16%", align: 'start'},
      { value:"start_time", text:"出勤時間", width: "13%", align: 'center'},
      { value:"end_time", text:"退勤時間", width: "13%", align: 'center'},
      { value:"total_time", text:"時間", width: "11%", align: 'center'},
      { value:"staff_hour_salary", text:"時給", width: "11%", align: 'center'},
      { value:"staff_day_salary", text:"日給", width: "13%", align: 'center'},
      { value:"staff_expense", text:"経費", width: "10%", align: 'center'}
    ],
    remove_item: [],
    tab: null,
    tab_item: ['管理者', 'スタッフ'],
    rules: [v => v.length == 4 || v == '' || '4桁入力'],
    dialog: false,
  }),
  created() {
    this.dialog = true;
  },
  computed: {
  },
  methods: {
    get_admin_day_salary(item) {
      if (item.admin_hour_salary == '' || item.total_time == '') {
        return ''
      }
      return String(item.admin_hour_salary * item.total_time)
    },
    get_staff_day_salary(item) {
      if (item.staff_hour_salary == '' || item.total_time == '') {
        return ''
      }
      return String(item.staff_hour_salary * item.total_time)
    },
    async prevDate() {
      this.edit();
      this.$emit("prev", this.items);
    },
    async nextDate() {
      this.edit();
      this.$emit("next", this.items);
    },
    remove(item) {
      this.remove_item.push({name: item.name})
      const index = this.items.findIndex(obj => obj.name == item.name);
      this.items.splice(index, 1);
    },
    async edit() {
      const url = "/schedule/app/adminEditSchedule.php";
      const data = {
        date: this.date,
        event: this.items,
        remove_event: this.remove_item
      }
      await axios.post(url, data).then(function(response) {
        this.remove_item = [];
        if (response.data.status != true){
          this.message = response.data.message
        }
        this.remove_event = [];
      }.bind(this))
    },
    async accept() {
      this.edit();
      this.dialog = false;
      this.$emit("accept", this.items);
    },
    close() {
      this.dialog = false;
      this.$emit("close");
    },
  },
}
</script>