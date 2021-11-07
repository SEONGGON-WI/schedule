<template>
  <v-dialog content-class="custom_dialog" v-model="dialog" :max-width="$vuetify.breakpoint.mobile ? '100%' : '80%'" persistent>
    <v-container class="pa-0" fluid>
      <v-card color="grey lighten-4">
        <v-toolbar color="primary" dark>
          <v-toolbar-title class="mx-2 title_text">
            クライアント管理
          </v-toolbar-title>
          <v-spacer></v-spacer>
          <v-btn class="error mx-2 botton_size" @click="close">
            <v-icon>close</v-icon>
          </v-btn>
        </v-toolbar>

        <v-card-text>
          <v-row>
            <v-col cols="3">
              <v-text-field
                v-model="client"
                label="クライアント名を入力"
                clearable
                hide-details
              ></v-text-field>
            </v-col>
            <v-col cols="6">
              <v-autocomplete
                v-model="agenda" 
                :items="agenda_list"
                :multiple="true"
                :hide-selected="true"
                :search-input.sync="search"
                @keydown="keyboardEvent"
                label="案件名を選択"
                hide-details
                clearable
                @change="reset"
              ></v-autocomplete>
            </v-col>
            <v-col cols="3">
              <v-btn outlined class="info ma-2" color="white" @click="setClient" :disabled="client == '' || agenda.length == 0"><v-icon>save</v-icon>登録</v-btn>
              <v-btn outlined class="success ma-2" color="white" @click="acceptClient"><v-icon>autorenew</v-icon>反映</v-btn>
              <v-btn outlined class="error ma-2" color="white" @click="deleteClient"><v-icon>delete</v-icon>削除</v-btn>
            </v-col>
          </v-row>
        </v-card-text>
          <v-data-table 
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
              <v-btn class="error" icon color="white" @click="remove(item)">
                <v-icon>delete</v-icon>
              </v-btn>
            </template>
          </v-data-table>
      </v-card>
    </v-container>
  </v-dialog>
</template>
<script>
import axios from 'axios';
export default {
  name: "clientlistdialog",
  components: {
  },
  props: [
    'agenda_items'
  ],
  data: () => ({
    items: [],
    headers: [
      { value:"client", text:"クライアント名", width: "40%", align: 'start'},
      { value:"agenda", text:"案件名", width: "40%", align: 'start'},
      { value:"action", text:"削除", width:"20%", align: 'center', sortable: false}
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
    search: '',
    search_table: '',
    dialog: false,
  }),
  created() {
    this.dialog = true;
    this.agenda_list = JSON.parse(JSON.stringify(this.agenda_items))
    this.agenda_list.shift()
    this.agenda_list.shift()
    this.agenda_list.shift()
    this.fetch_data()
  },
  computed: {
  },
  methods: {
    keyboardEvent: function(e) {
      if (e.which !== 13) {
        return
      }
      this.sy_search = null;
      this.setClient();
    },
    calculate_agenda() {
      let list = []
      this.agenda_list.map(element => {
        if (this.items.find(e => e.agenda == element) == undefined) {
          list.push(element)
        }
      })
      this.agenda_list = list
    },
    async fetch_data() {
      const url = "/schedule/app/adminGetClient.php";
      const data = {}
      await axios.post(url, data).then(function(response) {
        if (response.data.status == true && response.data.data != '') {
          this.items = response.data.data
          this.calculate_agenda()
        } else {
          this.items = [];
        }
      }.bind(this))
    },
    async setClient() {
      const url = "/schedule/app/adminUploadClient.php";
      const data = {
        client: this.client,
        agenda: this.agenda
      }
      await axios.post(url, data).then(function(response) {
        if (response.data.status == true) {
          this.fetch_data()
        } else {
          this.message = response.data.message
        }
      }.bind(this))
    },
    acceptClient() {
      this.$emit("accept")
    },
    async deleteClient() {
      const url = "/schedule/app/adminDeleteClient.php";
      const data = {
        client: this.client,
      }
      await axios.post(url, data).then(function(response) {
        if (response.data.status == true) {
          this.fetch_data()
        } else {
          this.message = response.data.message
        }
      }.bind(this))
    },
    async remove(item) {
      const url = "/schedule/app/adminRemoveClient.php";
      const data = {
        client: item.client,
        agenda: item.agenda,
      }
      await axios.post(url, data).then(function(response) {
        if (response.data.status == true) {
          this.fetch_data()
        } else {
          this.message = response.data.message
        }
      }.bind(this))
    },
    reset() {
      this.search = ''
    },
    close() {
      this.dialog = false;
      this.$emit("close");
    },
  },
}
</script>