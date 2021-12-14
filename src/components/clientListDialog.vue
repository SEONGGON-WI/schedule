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
                :items="calculate_agenda"
                :menu-props="{ maxHeight: '800' }"
                :multiple="true"
                :hide-selected="true"
                :search-input.sync="search"
                @keydown="keyboardEvent"
                label="案件名を選択"
                hide-details
                clearable
                @change="search = ''"
              ></v-autocomplete>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="3">
              <v-text-field
                ref="hour"
                v-model="hour_salary"
                :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"
                @keydown.enter="enter(1)"
                label="時給"
                single-line
                hide-details
              ></v-text-field>
            </v-col>
            <v-col cols="3">
              <v-text-field
                ref="day"
                v-model="day_salary"
                :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"
                @keydown.enter="enter(2)"
                label="日給"
                single-line
                hide-details
              ></v-text-field>              
            </v-col>
            <v-col cols="6">
              <v-btn outlined class="info ma-2" color="white" @click="setClient" :disabled="client == '' || agenda.length == 0"><v-icon>cloud_upload</v-icon>登録</v-btn>
              <v-btn outlined class="success ma-2" color="white" @click="acceptClient"><v-icon>autorenew</v-icon>反映</v-btn>
              <v-btn outlined class="error ma-2" color="white" @click="deleteClient" :disabled="client == ''"><v-icon>delete</v-icon>削除</v-btn>
            </v-col>
          </v-row>
        </v-card-text>
          <v-data-table 
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
              <v-btn class="info mx-2 my-3" icon color="white" @click="edit(item)">
                <v-icon>edit</v-icon>
              </v-btn>
              <v-btn class="error mx-2 my-3" icon color="white" @click="remove_check(item)">
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
            <v-btn class="success mx-2 botton_size" @click="edit_client">
              <v-icon>edit</v-icon>保存
            </v-btn>
            <v-btn class="error mx-2 botton_size" @click="edit_dialog = false">
              <v-icon>cancel</v-icon>キャンセル
            </v-btn>
          </v-toolbar>
            <v-row no-gutters class="mb-2">
              <v-col cols="3" class="name_agenda pt-3 pl-8">
                <div>{{ edit_item.client }}</div>
              </v-col>
              <v-col cols="9" class="name_agenda pt-3 pl-8">
                <div>{{ edit_item.agenda }}</div>
              </v-col>
            </v-row>
          <v-data-table
            :headers="header" 
            :items="[edit_item]" 
            hide-default-footer 
            disable-pagination
            disable-sort
          >
            <template v-slot:item.hour_salary="{ item }">
              <v-text-field
                type="number"
                ref="hour_salary"
                v-model="item.hour_salary"
                :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"  
                @keydown.enter="enter(3)"
                class="py-3"
                label="時給"
                height="60"
                outlined
                single-line
                hide-details
              ></v-text-field>
            </template>
            <template v-slot:item.day_salary="{ item }">
              <v-text-field
                type="number"
                v-model="item.day_salary"
                ref="day_salary"
                :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"  
                @keydown.enter="enter(4)"
                class="py-3"
                label="日給"
                height="60"
                outlined
                single-line
                hide-details
              ></v-text-field>
            </template>
          </v-data-table>
        </v-card>
      </v-container>
    </v-dialog>
    <alert
      @close="alert_show = false"
      :text="alert_text"
      v-if="alert_show"
    ></alert>
    <admin-dialog
      @accept="remove"
      @close="remove_dialog = false"
      v-if="remove_dialog"
      text="削除しますか？"
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
    'agenda_items', 'start_date'
  ],
  data: () => ({
    items: [],
    headers: [
      { value:"client", text:"クライアント名", width: "20%", align: 'start'},
      { value:"agenda", text:"案件名", width: "35%", align: 'start'},
      { value:"hour_salary", text:"時給", width: "15%", align: 'start'},
      { value:"day_salary", text:"日給", width: "15%", align: 'start'},
      { value:"action", text:"編集", width:"15%", align: 'center', sortable: false}
    ],
    header: [
      { value:"hour_salary", text:"時給", width: "50%", align: 'start'},
      { value:"day_salary", text:"日給", width: "50%", align: 'start'},
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
    agenda: '',
    agenda_list: [],
    hour_salary: '',
    day_salary: '',
    search: '',
    search_table: '',
    alert_text: '',
    alert_show: false,
    dialog: false,
    edit_item: {
      client: '',
      agenda: '',
      hour_salary: '',
      day_salary: ''
    },
    edit_dialog: false,
    remove_dialog: false,
    remove_item: {client:'', agenda: ''},
    root_folder: '',
  }),
  created() {
    this.root_folder = this.$store.getters.root_folder
    this.agenda_list = JSON.parse(JSON.stringify(this.agenda_items))
    this.agenda_list.shift()
    this.agenda_list.shift()
    this.agenda_list.shift()
    this.agenda_list.shift()
    this.fetch_data()
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
    keyboardEvent: function(e) {
      if (e.which !== 13 || this.client == '') {
        return
      }
      this.sy_search = null;
      this.setClient();
    },
    fetch_data() {
      const url = this.root_folder + "/app/adminGetClient.php";
      const data = {
        start_date: this.start_date,
      }
      axios.post(url, data).then(function(response) {
        if (response.data.status == true && response.data.data != '') {
          this.$store.commit('set_client_agenda', response.data.data)
          this.items = response.data.data
        } else {
          this.$store.commit('set_client_agenda', [])
          this.items = [];
        }
      }.bind(this))
    },
    setClient() {
      const url = this.root_folder + "/app/adminUploadClient.php";
      const data = {
        start_date: this.start_date,
        client: this.client,
        agenda: this.agenda,
        hour_salary: this.hour_salary,
        day_salary: this.day_salary,
      }
      axios.post(url, data).then(function(response) {
        this.client = ''
        this.agenda = []
        if (response.data.status == true) {
          this.fetch_data()
        } else {
          this.alert(response.data.message)
        }
      }.bind(this))
    },
    acceptClient() {
      this.$emit("accept")
    },
    deleteClient() {
      const url = this.root_folder + "/app/adminDeleteClient.php";
      const data = {
        start_date: this.start_date,
        client: this.client,
      }
      axios.post(url, data).then(function(response) {
        if (response.data.status == true) {
          this.fetch_data()
        } else {
          this.alert(response.data.message)
        }
      }.bind(this))
    },
    edit(item) {
      this.edit_item = item
      this.edit_dialog = true
    },
    edit_client() {
      const url = this.root_folder + "/app/adminEditClient.php";
      const data = {
        start_date: this.start_date,
        client: this.edit_item.client,
        agenda: this.edit_item.agenda,
        hour_salary: this.edit_item.hour_salary,
        day_salary: this.edit_item.day_salary,
      }
      axios.post(url, data).then(function(response) {
        if (response.data.status == true) {
          this.fetch_data()
        } else {
          this.alert(response.data.message)
        }
      }.bind(this))
      this.edit_dialog = false
    },
    remove_check(item) {
      this.remove_item = item
      this.remove_dialog = true
    },  
    remove() {
      const url = this.root_folder + "/app/adminRemoveClient.php";
      const data = {
        start_date: this.start_date,
        client: this.remove_item.client,
        agenda: this.remove_item.agenda,
      }
      axios.post(url, data).then(function(response) {
        if (response.data.status == true) {
          this.fetch_data()
        } else {
          this.alert(response.data.message)
        }
        this.remove_item = {client:'', agenda: ''}
      }.bind(this))
      this.remove_dialog = false
    },
    enter(index) {
      switch (index) {
        case 1:
          this.$refs.day.focus()
          break;

        case 2:
          if(this.client == '' || this.agenda.length == 0) {
            break
          } else {
            this.setClient()
            break
          }

        case 3:
          this.$refs.day_salary.focus()
          break;

        case 4:
          if(this.edit_item.client == '' || this.edit_item.agenda == '' || this.start_date == '') {
            break
          } else {
            this.edit_client()
            break
          }

        default:
          break;
      }
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