<template>
  <v-dialog v-model="dialog" :max-width="$vuetify.breakpoint.mobile ? '100%' : '80%'" persistent>
    <v-container class="pa-0" fluid>
      <v-card color="grey lighten-4">
        <v-toolbar color="primary" dark>
          <v-toolbar-title class="mx-2 title_text">
            スタッフ管理
          </v-toolbar-title>
          <v-spacer></v-spacer>
          <v-btn class="error mx-2 botton_size" @click="close">
            <v-icon>close</v-icon>
          </v-btn>
        </v-toolbar>
          <v-data-table 
            :headers="headers" 
            :items="items" 
            item-key="name"
            :search="search"
            :footer-props="footer"
            :options.sync="options"
            no-data-text="データがありません"
          > 
            <template v-slot:top>
              <v-text-field
                v-model="search"
                label="キーワードースタッフ検索"
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
  name: "stafflistdialog",
  components: {
  },
  props: [
  ],
  data: () => ({
    items: [],
    headers: [
      { value:"name", text:"名前", width: "50%", align: 'start'},
      { value:"access_time", text:"更新日", width: "30%", align: 'start'},
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
    search: '',
    dialog: false,
  }),
  created() {
    this.dialog = true;
    this.fetch_data()
  },
  computed: {
  },
  methods: {
    async fetch_data() {
      const url = "/schedule/app/adminGetStaff.php";
      const data = {}
      await axios.post(url, data).then(function(response) {
        if (response.data.status === 'success') {
          this.items = response.data.data
        } else {
          this.message = response.data.message
        }
      }.bind(this))
    },
    async remove(item) {
      const url = "/schedule/app/adminRemoveStaff.php";
      const data = {
        name: item.name,
      }
      await axios.post(url, data).then(function(response) {
        if (response.data.status === 'success') {
          this.fetch_data()
        } else {
          this.message = response.data.message
        }
      }.bind(this))
    },
    close() {
      this.dialog = false;
      this.$emit("close");
    },
  },
}
</script>