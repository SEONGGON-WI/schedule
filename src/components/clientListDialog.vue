<template>
  <v-dialog content-class="custom_dialog" v-model="dialog" persistent>
    <v-container class="pa-0" fluid>
      <v-card color="grey lighten-4">
        <v-toolbar color="primary" dark>
          <v-toolbar-title class="mx-2 ">
            クライアント管理
          </v-toolbar-title>
          <v-spacer></v-spacer>
          <v-btn class="error mx-2 botton_size" @click="close">
            <v-icon>cancel</v-icon>キャンセル
          </v-btn>
        </v-toolbar>

        <v-card-text>
          <v-row>
            <v-col cols="3">
              <v-textarea
                class="pt-1"
                v-model="client"
                :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'" 
                label="クライアント名を入力"
                single-line
                hide-details
                rows="1"
                no-resize
                auto-grow
                clearable
              ></v-textarea>
            </v-col>
            <v-col cols="9">
              <v-autocomplete
                v-model="agenda" 
                ref="agenda_filed"
                :items="calculate_agenda"
                :menu-props="{ maxHeight: '800' }"
                :multiple="true"
                :hide-selected="true"
                :search-input.sync="search"
                @keydown.enter="autocomplete_enter"
                label="案件名を選択"
                hide-details
                clearable
                @change="search = ''"
              ></v-autocomplete>
            </v-col>
          </v-row>
          <v-form ref="form" v-model="valid">
          <v-row>
            <v-col cols="3">
              <v-text-field
                v-model="hour_salary"
                :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"
                :rules="[rules.positive]"
                label="管理者時給"
                single-line
                hide-details
              ></v-text-field>
            </v-col>
            <v-col cols="3">
              <v-text-field
                v-model="day_salary"
                :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"
                :rules="[rules.positive]"
                label="管理者日給"
                single-line
                hide-details
              ></v-text-field>              
            </v-col>
            <v-col cols="3">
              <v-text-field
                v-model="staff_hour_salary"
                :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"
                :rules="[rules.positive]"
                label="スタッフ時給"
                single-line
                hide-details
              ></v-text-field>
            </v-col>
            <v-col cols="3">
              <v-text-field
                v-model="staff_day_salary"
                :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"
                :rules="[rules.positive]"
                label="スタッフ日給"
                single-line
                hide-details
              ></v-text-field>              
            </v-col>
            <v-col cols="1"></v-col>
            <v-spacer></v-spacer>
            <v-btn outlined class="info ma-2" color="white" @click="confirm('set')" :disabled="client == '' || agenda.length == 0 || !valid"><v-icon>cloud_upload</v-icon>登録</v-btn>
            <v-btn outlined class="success ma-2" color="white" @click="confirm('apply')"><v-icon>autorenew</v-icon>反映</v-btn>
          </v-row>
          </v-form>
        </v-card-text>
          <v-data-table 
            :key="toggle_key"
            name="client-list-table"
            :headers="headers" 
            :items="items" 
            item-key="name"
            :search="search_table"
            :footer-props="footer"
            :options.sync="options"
            no-data-text="データがありません"
          > 
            <template v-slot:top>
              <v-text-field
                v-model="search_table"
                label="キーワードで検索"
                class="mx-4 pt-4"
                clearable
              ></v-text-field>
            </template>
            <template v-slot:item.action="{ item }">
              <v-btn class="info mx-1 my-3" icon color="white" @click="applyClient(item)">
                <v-icon>check</v-icon>
              </v-btn>
              <v-btn class="info mx-1 my-3" icon color="white" @click="edit(item)">
                <v-icon>edit</v-icon>
              </v-btn>
              <v-btn class="error mx-1 my-3" icon color="white" @click="remove_check(item)">
                <v-icon>delete</v-icon>
              </v-btn>
            </template>
          </v-data-table>
      </v-card>
    </v-container>
    <v-dialog v-model="edit_dialog" persistent width="70%">
      <v-container class="pa-0" fluid>
        <v-card color="grey lighten-4">
          <v-toolbar color="primary" dark>
            <v-toolbar-title class="px-2">
              クライアント管理
            </v-toolbar-title>
            <v-spacer></v-spacer>
            <v-btn class="success mx-2 botton_size" :disabled="!edit_valid" @click="edit_client">
              <v-icon>edit</v-icon>保存
            </v-btn>
            <v-btn class="error mx-2 botton_size" @click="edit_dialog = false">
              <v-icon>cancel</v-icon>キャンセル
            </v-btn>
          </v-toolbar>
            <v-row no-gutters class="mb-2">
              <v-col cols="12" class="name_agenda pt-3 pl-8">
                <div>{{ edit_item.agenda }}</div>
              </v-col>
            </v-row>
          <v-form ref="form" v-model="edit_valid">
          <v-data-table
            :headers="header" 
            :items="[edit_item]" 
            hide-default-footer 
            disable-pagination
            disable-sort
          >
            <template v-slot:item.client="{ item }">
              <v-text-field
                v-model="item.client"
                :rules="[rules.required]"
                :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"  
                class="py-3"
                label="クライアント"
                height="60"
                outlined
                single-line
                hide-details
              ></v-text-field>
            </template>
            <template v-slot:item.hour_salary="{ item }">
              <v-text-field
                v-model="item.hour_salary"
                :rules="[rules.positive]"
                :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"  
                class="py-3"
                label="管理者時給"
                height="60"
                outlined
                single-line
                hide-details
              ></v-text-field>
            </template>
            <template v-slot:item.day_salary="{ item }">
              <v-text-field
                v-model="item.day_salary"
                :rules="[rules.positive]"
                :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"  
                class="py-3"
                label="管理者日給"
                height="60"
                outlined
                single-line
                hide-details
              ></v-text-field>
            </template>
          </v-data-table>

          <v-data-table
            :headers="staff_header" 
            :items="[edit_item]" 
            hide-default-footer 
            disable-pagination
            disable-sort
          >
            <template v-slot:item.staff_hour_salary="{ item }">
              <v-text-field
                v-model="item.staff_hour_salary"
                :rules="[rules.positive]"
                :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"  
                class="py-3"
                label="スタッフ時給"
                height="60"
                outlined
                single-line
                hide-details
              ></v-text-field>
            </template>
            <template v-slot:item.staff_day_salary="{ item }">
              <v-text-field
                v-model="item.staff_day_salary"
                :rules="[rules.positive]"
                :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"  
                class="py-3"
                label="スタッフ日給"
                height="60"
                outlined
                single-line
                hide-details
              ></v-text-field>
            </template>
          </v-data-table>
          </v-form>
        </v-card>
      </v-container>
    </v-dialog>
    <alert
      v-if="alert_show"
      @close="alert_show = false"
      :text="alert_text"
    ></alert>
    <admin-dialog
      v-if="confirm_dialog"
      @accept="confirm_accept"
      @close="confirm_dialog = false"
      :text="confirm_text"
    ></admin-dialog>
  </v-dialog>
</template>
<style lang="scss">
[name=client-list-table] tbody tr:nth-of-type(odd){
  background: rgba(170, 200, 99,0.2); 
}
</style>
<script>
import AdminDialog from '@/components/AdminDialog.vue';
import alert from './alert.vue';
import axios from 'axios';
export default {
  name: "clientlistdialog",
  components: {
    AdminDialog,
    alert,
  },
  props: [
    'agenda_items', 'date'
  ],
  data: () => ({
    items: [],
    headers: [
      { value:"client", text:"顧客", width: "12%", align: 'start'},
      { value:"agenda", text:"案件名", width: "24%", align: 'start'},
      { value:"hour_salary", text:"時給", width: "11%", align: 'start'},
      { value:"day_salary", text:"日給", width: "11%", align: 'start'},
      { value:"staff_hour_salary", text:"スタッフ時給", width: "11%", align: 'start'},
      { value:"staff_day_salary", text:"スタッフ日給", width: "11%", align: 'start'},
      { value:"action", text:"編集", width:"20%", align: 'center', sortable: false}
    ],
    header: [
      { value:"client", text:"クライアント", width: "40%", align: 'start'},
      { value:"hour_salary", text:"管理者時給", width: "30%", align: 'start'},
      { value:"day_salary", text:"管理者日給", width: "30%", align: 'start'},
    ],
    staff_header: [
      { value:"", text:"", width: "40%", align: 'start'},
      { value:"staff_hour_salary", text:"スタッフ時給", width: "30%", align: 'start'},
      { value:"staff_day_salary", text:"スタッフ日給", width: "30%", align: 'start'},
    ],
    footer : {
      itemsPerPageText:"1ページあたりの行数",
      itemsPerPageOptions:[5,10,25,-1]
    },
    options : {
      page:1,
      itemsPerPage: 10,
    },
    client: '',
    agenda: [],
    agenda_list: [],
    hour_salary: '',
    day_salary: '',
    staff_hour_salary: '',
    staff_day_salary: '',
    search: '',
    search_table: '',
    edit_item: {},
    edit_dialog: false,
    alert_text: '',
    alert_show: false,
    confirm_dialog: false,
    confirm_type: '',
    confirm_text: '',
    remove_item: {client:'', agenda: ''},
    rules:{
      required: v => v != '' || '時間の様式に合わせてください。',
      positive: v => v >= 0 || v == '' || '正の整数を指定'
    },
    valid: true,
    edit_valid: true,
    toggle_key: 0,
    edit_condition: false,
    dialog: false,
    root_folder: '',
  }),
  created() {
    this.root_folder = this.$store.getters.root_folder
    this.agenda_list = JSON.parse(JSON.stringify(this.agenda_items))
    this.agenda_list.splice(0, 3);
    this.items = JSON.parse(JSON.stringify(this.$store.getters.client_agenda))
    this.dialog = true;
  },
  computed: {
    calculate_agenda() {
      let list = []
      this.agenda_list.map(element => {
        if (this.items.find(e => e.agenda == element) == undefined) {
          if (element != '') {
            list.push(element)  
          }
        }
      })
      return list.sort(function (a, b) {
        return a.localeCompare(b, 'ja')
      })
    },
  },
  methods: {
    autocomplete_enter() {
      this.sy_search = null;
      this.$refs.agenda_filed.isFocused = false;
    },
    confirm(type) {
      this.confirm_type = type
      switch (type) {
        case 'set':
          if (this.client != '' && this.agenda.length != 0) {
            this.confirm_text = "登録しますか？"
            this.confirm_dialog = true
          }
          break;

        case 'apply':
          this.confirm_text = "反映しますか？"
          this.confirm_dialog = true
          break

        case 'remove':
          this.confirm_text = "削除しますか？"
          this.confirm_dialog = true
          break

        default:
          break;
      }
    },
    confirm_accept() {
      switch (this.confirm_type) {
        case 'set':
          this.setClient()
          break

        case 'apply':
          this.applyClient()
          break

        case 'remove':
          this.removeClient()
          break

        default:
          break;
      }
      this.confirm_dialog = false
    },
    setClient() {
      const url = this.root_folder + "/app/adminUploadClient.php";
      const data = {
        start_date: this.date.start_date,
        client: this.client,
        agenda: this.agenda,
        hour_salary: this.hour_salary,
        day_salary: this.day_salary,
        staff_hour_salary: this.staff_hour_salary,
        staff_day_salary: this.staff_day_salary
      }
      axios.post(url, data).then(function(response) {
        if (response.data.status == true) {
          this.agenda.map(element => this.items.unshift({
            start_date: this.date.start_date,
            client: this.client,
            agenda: element,
            hour_salary: this.hour_salary,
            day_salary: this.day_salary,
            staff_hour_salary: this.staff_hour_salary,
            staff_day_salary: this.staff_day_salary
          }))
          this.agenda = []
          this.hour_salary = ''
          this.day_salary = ''
          this.staff_hour_salary = ''
          this.staff_day_salary = ''
        } else {
          this.alert(response.data.message)
        }
      }.bind(this))
    },
    async applyClient(client = {}) {
      const url = this.root_folder + "/app/adminUploadSchedule.php";
      const data = {
        start_date: this.date.start_date,
        end_date: this.date.end_date,
        client: client,
        select_mode: client['agenda'] !== undefined ? true : false 
      }
      await axios.post(url, data).then(function(response) {
        if (response.data.status == false) {
          this.alert(response.data.message);
        } else {
          this.edit_condition = true
        }
      }.bind(this))
    },
    edit(item) {
      this.edit_item = JSON.parse(JSON.stringify(item))
      this.edit_dialog = true
    },
    edit_client() {
      const url = this.root_folder + "/app/adminEditClient.php";
      const data = {
        start_date: this.date.start_date,
        client: this.edit_item.client,
        agenda: this.edit_item.agenda,
        hour_salary: this.edit_item.hour_salary,
        day_salary: this.edit_item.day_salary,
        staff_hour_salary: this.edit_item.staff_hour_salary,
        staff_day_salary: this.edit_item.staff_day_salary,
      }
      axios.post(url, data).then(function(response) {
        if (response.data.status == true) {
          const index = this.items.findIndex(element => element.agenda === this.edit_item.agenda)
          this.items[index] = this.edit_item
          this.edit_condition = true
          this.toggle()
        } else {
          this.alert(response.data.message)
        }
      }.bind(this))
      this.edit_dialog = false
    },
    remove_check(item) {
      this.remove_item = item
      this.confirm('remove')
    },  
    removeClient() {
      const url = this.root_folder + "/app/adminRemoveClient.php";
      const data = {
        start_date: this.date.start_date,
        end_date: this.date.end_date,
        client: this.remove_item.client,
        agenda: this.remove_item.agenda
      }
      axios.post(url, data).then(function(response) {
        if (response.data.status == true) {
          const index = this.items.findIndex(element => element.agenda === this.remove_item.agenda)
          this.items.splice(index, 1);
          this.edit_condition = true
          this.toggle()
        } else {
          this.alert(response.data.message)
        }
      }.bind(this))
      this.remove_dialog = false
    },
    alert(text) {
      this.alert_text = text;
      this.alert_show = true;
    },
    close() {
      this.$store.commit('set_client_agenda', this.items)
      this.dialog = false;
      this.$emit("close", this.edit_condition);
    },
    toggle() {
      this.toggle_key = this.toggle_key === 0 ? 1 : 0
    },
  },
}
</script>